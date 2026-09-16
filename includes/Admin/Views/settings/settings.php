<?php

use Codemanas\VczApi\Helpers\FormHelper;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pre-process Template Data
 */
$args           = isset( $args ) && is_array( $args ) ? $args : [];
$settings       = ! empty( $args['settings'] ) && is_array( $args['settings'] ) ? $args['settings'] : [];
$field_sections = ! empty( $args['field_sections'] ) && is_array( $args['field_sections'] ) ? $args['field_sections'] : [];
?>
<div class="vczapi-settings">
    <div class="vczapi-settings__main">
        <form action="edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings&tab=api-settings" method="POST" class="vczapi-settings__form">
			<?php wp_nonce_field( 'vczapi_settings_update_action', 'vczapi_settings_nonce' ); ?>

            <div class="vczapi-admin-accordion">
				<?php foreach ( $field_sections as $section_key => $section ) :
					$input_id = 'vczapi-tab-' . esc_attr( $section_key );
					?>
                    <div class="vczapi-admin-accordion__item">
                        <input type="checkbox" id="<?php echo $input_id; ?>" class="vczapi-admin-accordion__toggle" checked>
                        <label for="<?php echo $input_id; ?>" class="vczapi-admin-accordion__header">
                            <span class="vczapi-admin-accordion__title"><?php echo esc_html( $section['title'] ); ?></span>
                            <span class="vczapi-admin-accordion__icon dashicons dashicons-arrow-down-alt2"></span>
                        </label>
                        <div class="vczapi-admin-accordion__content">
                            <table class="form-table">
                                <tbody>
								<?php foreach ( $section['fields'] as $field_key => $field_config ) :
									$wrapper_class = ! empty( $field_config['wrapper_class'] ) ? esc_attr( $field_config['wrapper_class'] ) : '';
									$label = $field_config['label'] ?? '';
									$storage_key = $field_config['storage_key'] ?? $field_key;
									?>
                                    <tr class="<?php echo $wrapper_class; ?>">
                                        <th scope="row">
                                            <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $label ); ?></label>
                                        </th>
                                        <td>
	                                        <?php if ( isset( $field_config['type'] ) && $field_config['type'] === 'composite' ) {
		                                        // 1. Fetch the value using the parent composite field's storage key
		                                        $composite_value = $settings[ $storage_key ] ?? '';

		                                        foreach ( $field_config['fields'] as $sub_key => $sub_config ) {
			                                        // 2. Fall back to sub_config default if no saved composite value exists
			                                        $sub_value = ! empty( $composite_value ) ? $composite_value : ( $sub_config['default'] ?? '' );

			                                        unset( $sub_config['storage_key'] );
			                                        FormHelper::fields(
				                                        $sub_key,
				                                        $sub_config,
				                                        $sub_value
			                                        );
		                                        }

		                                        if ( ! empty( $field_config['description'] ) ) {
			                                        echo '<p class="description">' . esc_html( $field_config['description'] ) . '</p>';
		                                        }

		                                        if ( ! empty( $field_config['help_html'] ) ) {
			                                        echo '<p class="description">' . wp_kses_post( $field_config['help_html'] ) . '</p>';
		                                        }
	                                        } else {
												$value = $settings[ $storage_key ] ?? $field_config['default'] ?? '';
												unset( $field_config['label'], $field_config['wrapper_class'], $field_config['storage_key'] );
												FormHelper::fields(
													$field_key,
													$field_config,
													$value
												);
											}
											?>
                                        </td>
                                    </tr>
								<?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
				<?php endforeach; ?>
            </div>

            <div class="vczapi-settings__actions">
                <input type="submit" name="save_zoom_settings" id="submit" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'video-conferencing-with-zoom-api' ); ?>">
            </div>
        </form>
    </div>
    <div class="vczapi-settings__sidebar">
		<?php require_once VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/settings/promotional.php'; ?>
    </div>
</div>