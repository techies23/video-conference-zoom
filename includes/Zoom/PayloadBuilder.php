<?php

namespace Codemanas\VczApi\Zoom;

use DateTime;
use DateTimeZone;
use WP_Error;

/**
 * Builds a Zoom-ready payload from:
 * - raw $input
 * - an operation schema (supports 'target' and 'invertOnWrite' to map canonical -> legacy field names)
 *
 * Returns array on success, WP_Error on failure.
 */
class PayloadBuilder
{
	/**
	 * Builds an output array by processing an operation schema with input values.
	 *
	 * @param  string  $schemaFor  The schema key, e.g., 'meeting', 'webinar', 'user'.
	 * @param  string  $operation  The operation name within the schema.
	 * @param  array   $input      The input data to be processed based on the schema.
	 *
	 * @return array|WP_Error
	 */
    public function build($schemaFor, string $operation, array $input)
    {
        $schema = (new SchemaRegistry())->get($schemaFor, $operation);
        if (empty($schema)) {
            return $this->error('unknown_operation', 'Unknown schema operation: ' . $operation);
        }

        $fields = isset($schema['fields']) && is_array($schema['fields']) ? $schema['fields'] : [];
        $output = [];

        // 1) Pre-fill defaults for non-dotted fields (dotted will be merged later)
        foreach ($fields as $name => $def) {
            if (isset($def['default'])) {
                $this->setDotted($output, $name, $def['default']);
            }
        }

        // 2) Resolve values (mapFrom/invertFrom or direct input)
        $resolved = [];
        foreach ($fields as $name => $def) {
            $value = $this->resolveValue($name, $def, $input);
            if ($value !== null) {
                $resolved[$name] = $value;
            }
        }

        // 3) Apply sanitization and validation
        foreach ($resolved as $name => $value) {
            $def = $fields[$name];

            // sanitize
            $value = $this->applySanitize($value, $def);

            // validate
            $err = $this->validateValue($name, $value, $def);
            if ($err instanceof WP_Error) {
                return $this->error($err->get_error_code(), $err->get_error_message(), [
                    'field' => $name,
                ]);
            }

            $resolved[$name] = $value;
        }

        // 4) Apply transforms
        $transformed = $this->applyTransforms($resolved, $fields);

        // 5) Merge into output using dotted keys -> nested structure
        foreach ($transformed as $name => $value) {
            $this->setDotted($output, $name, $value);
        }

        // 6) Required fields check (post-transform) using target if present and ensure non-empty
        foreach ($fields as $name => $def) {
            if (!empty($def['required'])) {
                $requiredKey = isset($def['target']) && is_string($def['target']) ? $def['target'] : $name;
                if (!$this->hasDotted($output, $requiredKey)) {
                    return $this->error('missing_required', sprintf('Missing required field: %s', $name), [
                        'field' => $name,
                    ]);
                }
                // Non-empty check for required fields
                $val = $this->getDotted($output, $requiredKey);
                if ($this->isEffectivelyEmpty($val, $def)) {
                    return $this->error('empty_required', sprintf('Required field cannot be empty: %s', $name), [
                        'field' => $name,
                    ]);
                }
            }
        }

        // Final cleanup: remove helper-only inputs (e.g., start_date if start_time is produced)
        $this->finalizeCleanup($output, $fields);
        return $output;
    }

	/**
	 * Resolves a value based on the provided name, definition, and input data.
	 *
	 * @param  string  $name  The name of the key to resolve the value for.
	 * @param  array  $def  The definition array which may include keys like 'mapFrom', 'invertFrom', or 'default'.
	 * @param  array  $input  The input array containing potential data for resolution.
	 *
	 * @return mixed Returns the resolved value from the input or definition, or null if no applicable value is found.
	 */
	private function resolveValue(string $name, array $def, array $input)
    {
        // Direct name first
        if (array_key_exists($name, $input)) {
            return $input[$name];
        }

        // mapFrom legacy keys
        if (!empty($def['mapFrom']) && is_array($def['mapFrom'])) {
            foreach ($def['mapFrom'] as $legacyKey) {
                if (array_key_exists($legacyKey, $input) && $input[$legacyKey] !== null && $input[$legacyKey] !== '') {
                    return $input[$legacyKey];
                }
            }
        }

        // invertFrom: if provided and value is truthy, invert into boolean
        if (!empty($def['invertFrom']) && is_array($def['invertFrom'])) {
            foreach ($def['invertFrom'] as $legacyKey) {
                if (array_key_exists($legacyKey, $input)) {
                    $legacyVal = $this->toBool($input[$legacyKey]);
                    return !$legacyVal;
                }
            }
        }

        // Fallback to default if explicitly declared
        if (array_key_exists('default', $def)) {
            return $def['default'];
        }

        // No value found
        return null;
    }

	/**
	 * Applies sanitization rules to the given value based on the provided field definitions.
	 *
	 * @param  mixed  $value  The input value to be sanitized. It can be of any type but is typically a string.
	 * @param  array  $def  An associative array defining the sanitization rules, including optional rules such as
	 *                   'sanitize' (array of sanitization steps) and 'maxLength' (maximum length for string values).
	 *
	 * @return mixed The sanitized value. If the input value is a string and sanitization rules are defined,
	 *               the value will be modified accordingly. Otherwise, the original value is returned.
	 */
	private function applySanitize($value, array $def)
    {
        if (!isset($def['sanitize']) || !is_array($def['sanitize'])) {
            return $value;
        }

        foreach ($def['sanitize'] as $rule) {
            switch ($rule) {
                case 'trim':
                    if (is_string($value)) {
                        $value = trim($value);
                    }
                    break;
                case 'strip_tags_soft':
                    if (is_string($value)) {
                        // Allow basic text without tags but preserve entities
                        $value = strip_tags($value);
                    }
                    break;
                case 'strip_all_html':
                    if (is_string($value)) {
                        $value = strip_tags(html_entity_decode($value));
                    }
                    break;
                default:
                    // no-op for unknown rule
                    break;
            }
        }

        // Enforce maxLength after sanitize
        if (isset($def['maxLength']) && is_int($def['maxLength']) && is_string($value)) {
            if (mb_strlen($value) > $def['maxLength']) {
                $value = mb_substr($value, 0, $def['maxLength']);
            }
        }

        return $value;
    }

	/**
	 * Validates the provided value against the specified field definition rules.
	 *
	 * @param  string  $name  The name of the field being validated.
	 * @param  mixed  $value  The value of the field to validate.
	 * @param  array  $def  The field definition specifying validation rules, including type checks, enums, and custom validators.
	 *
	 * @return mixed|null Returns a WP_Error object with specific error codes and messages if validation fails, or null if validation succeeds.
	 */
	private function validateValue(string $name, $value, array $def)
    {
        // type checks
        if (isset($def['type'])) {
            switch ($def['type']) {
                case 'string':
                    if ($value !== null && !is_string($value)) {
                        return $this->wpError('invalid_type', "$name must be string");
                    }
                    break;
                case 'int':
                    if ($value !== null && !is_int($value)) {
                        // allow numeric string to int-cast
                        if (is_numeric($value)) {
                            $value = (int)$value;
                        } else {
                            return $this->wpError('invalid_type', "$name must be int");
                        }
                    }
                    // min
                    if (isset($def['min']) && is_int($def['min']) && $value < $def['min']) {
                        return $this->wpError('min_violation', "$name must be >= {$def['min']}");
                    }
                    break;
                case 'bool':
                    // normalize to bool
                    break;
                case 'array[string]':
                    if ($value !== null && !is_array($value)) {
                        return $this->wpError('invalid_type', "$name must be array of strings");
                    }
                    break;
                case 'datetime-local':
                    if ($value !== null && !is_string($value)) {
                        return $this->wpError('invalid_type', "$name must be datetime-local string");
                    }
                    break;
            }
        }

        // enums
        if (isset($def['enum']) && is_array($def['enum']) && $value !== null) {
            if (!in_array($value, $def['enum'], true)) {
                return $this->wpError('invalid_enum', "$name must be one of: " . implode(', ', $def['enum']));
            }
        }

        // custom validators
        if (isset($def['validate']) && is_array($def['validate'])) {
            foreach ($def['validate'] as $rule) {
                if ($rule === 'olson_timezone' && $value) {
                    if (!$this->isValidTimezone($value)) {
                        return $this->wpError('invalid_timezone', "$name must be a valid timezone");
                    }
                }
            }
        }

        return null;
    }

	/**
	 * Transforms and normalizes the provided data based on the given field definitions and directives.
	 *
	 * @param  array  $resolved  An associative array containing the resolved input data to be transformed.
	 * @param  array  $fields  An associative array defining the field transformation rules, including type normalization and target key adjustments.
	 *
	 * @return array The transformed and normalized data as an associative array, where keys may be modified based on field definitions.
	 */
	private function applyTransforms(array $resolved, array $fields): array
    {
        $out = [];

        foreach ($resolved as $name => $value) {
            $def = $fields[$name] ?? [];

            // type normalizations
            if (($def['type'] ?? null) === 'bool') {
                $value = $this->toBool($value);
            } elseif (($def['type'] ?? null) === 'int') {
                if ($value !== null && !is_int($value) && is_numeric($value)) {
                    $value = (int)$value;
                }
            }

            // No special time conversion here: legacy API expects 'start_date' and converts internally.
            // If any generic transform directives exist, handle here (kept for extensibility).

            // Write to target name if provided; apply invertOnWrite if requested
            $target = isset($def['target']) && is_string($def['target']) ? $def['target'] : $name;

            if (!empty($def['invertOnWrite'])) {
                $value = !$this->toBool($value);
            }

            $out[$target] = $value;
        }

        return $out;
    }

    private function finalizeCleanup(array &$output, array $fields): void
    {
        // If any canonical-only helper fields were produced, clean them here.
        // For meeting payloads we intentionally keep 'start_date' (legacy class uses it),
        // so no removal is needed.
    }

    // Utilities unchanged below
    private function toUtcIso8601(string $localDateTime, string $tz): string
    {
        try {
            $dt = new DateTime($localDateTime, new DateTimeZone($tz));
        } catch (\Exception $e) {
            $dt = new DateTime($localDateTime, new DateTimeZone('UTC'));
        }
        $dt->setTimezone(new DateTimeZone('UTC'));
        return $dt->format('Y-m-d\TH:i:s');
    }

    private function toBool($val): bool
    {
        if (is_bool($val)) {
            return $val;
        }
        if (is_int($val)) {
            return $val !== 0;
        }
        if (is_string($val)) {
            $v = strtolower(trim($val));
            return in_array($v, ['1', 'true', 'yes', 'on'], true);
        }
        return (bool)$val;
    }

    private function isValidTimezone(string $tz): bool
    {
        return in_array($tz, \timezone_identifiers_list(), true);
    }

    private function setDotted(array &$arr, string $path, $value): void
    {
        $parts = explode('.', $path);
        $ref = &$arr;
        foreach ($parts as $p) {
            if (!isset($ref[$p]) || !is_array($ref[$p])) {
                $ref[$p] = [];
            }
            $ref = &$ref[$p];
        }
        $ref = $value;
    }

    private function hasDotted(array $arr, string $path): bool
    {
        $parts = explode('.', $path);
        $ref = $arr;
        foreach ($parts as $p) {
            if (!is_array($ref) || !array_key_exists($p, $ref)) {
                return false;
            }
            $ref = $ref[$p];
        }
        return true;
    }

    private function getDotted(array $arr, string $path) {
        $parts = explode('.', $path);
        $ref = $arr;
        foreach ($parts as $p) {
            if (!is_array($ref) || !array_key_exists($p, $ref)) {
                return null;
            }
            $ref = $ref[$p];
        }
        return $ref;
    }

    private function unsetDotted(array &$arr, string $path): void
    {
        $parts = explode('.', $path);
        $last = array_pop($parts);
        $ref = &$arr;
        foreach ($parts as $p) {
            if (!isset($ref[$p]) || !is_array($ref[$p])) {
                return;
            }
            $ref = &$ref[$p];
        }
        if (isset($ref[$last])) {
            unset($ref[$last]);
        }
    }

    /**
     * Determine if a required field's value should be considered empty.
     * Rules:
     * - null is empty
     * - string: empty after trim is empty (but '0' is NOT empty)
     * - array: empty() is empty
     * - bool: never empty (false is allowed)
     * - int/float: never empty (0 is allowed)
     */
    private function isEffectivelyEmpty($val, array $def): bool
    {
        if ($val === null) {
            return true;
        }

        // Respect explicit types if provided.
        $type = isset($def['type']) ? $def['type'] : null;

        if ($type === 'bool') {
            return false; // false is acceptable for required booleans
        }

        if ($type === 'int') {
            return false; // 0 is acceptable
        }

        if (is_string($val)) {
            return trim($val) === '';
        }

        if (is_array($val)) {
            return empty($val);
        }

        return false;
    }

    private function error(string $code, string $message, array $data = [])
    {
        if (class_exists('\WP_Error')) {
            return new WP_Error($code, $message, $data);
        }
        return [
            'error' => [
                'code'    => $code,
                'message' => $message,
                'data'    => $data,
            ],
        ];
    }

    private function wpError(string $code, string $message): WP_Error
    {
        return new WP_Error($code, $message);
    }
}