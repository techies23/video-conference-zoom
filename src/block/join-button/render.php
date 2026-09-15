<?php
/**
 * Dynamic Render Template for Zoom Join Button
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Sanitize Inputs
$action_type = \Codemanas\VczApi\Blocks\ButtonHelper::sanitize_action_type( $attributes['actionType'] ?? 'app' );
$source_type = \Codemanas\VczApi\Blocks\ButtonHelper::sanitize_source_type( $attributes['sourceType'] ?? 'current' );
$open_in_new = ! empty( $attributes['openInNewTab'] );

$url = \Codemanas\VczApi\Blocks\ButtonHelper::get_url( $action_type, $source_type, $attributes );

// 2. Fallback Label Logic
$default_text_map = [
	'app'     => __( 'Join via Zoom App', 'video-conferencing-with-zoom-api' ),
	'browser' => __( 'Join via Web Browser', 'video-conferencing-with-zoom-api' ),
	'start'   => __( 'Start Meeting', 'video-conferencing-with-zoom-api' ),
];

if ( ! empty( $attributes['buttonText'] ) ) {
	$display_text = sanitize_text_field( $attributes['buttonText'] );
} elseif ( isset( $default_text_map[ $action_type ] ) ) {
	$display_text = $default_text_map[ $action_type ];
} else {
	$display_text = __( 'Join Meeting', 'video-conferencing-with-zoom-api' );
}

// 3. CSS Variable Mapping for States
$styles = [];

if ( ! empty( $attributes['backgroundColor'] ) ) {
	$value = \Codemanas\VczApi\Blocks\ButtonHelper::sanitize_css_color( $attributes['backgroundColor'] );

	if ( '' !== $value ) {
		$styles[] = sprintf( '--vczapi-btn-bg: %s', $value );
	}
}

if ( ! empty( $attributes['textColor'] ) ) {
	$value = \Codemanas\VczApi\Blocks\ButtonHelper::sanitize_css_color( $attributes['textColor'] );

	if ( '' !== $value ) {
		$styles[] = sprintf( '--vczapi-btn-color: %s', $value );
	}
}

if ( ! empty( $attributes['bgHoverColor'] ) ) {
	$value = \Codemanas\VczApi\Blocks\ButtonHelper::sanitize_css_color( $attributes['bgHoverColor'] );

	if ( '' !== $value ) {
		$styles[] = sprintf( '--vczapi-btn-bg-hover: %s', $value );
	}
}

if ( ! empty( $attributes['textHoverColor'] ) ) {
	$value = \Codemanas\VczApi\Blocks\ButtonHelper::sanitize_css_color( $attributes['textHoverColor'] );

	if ( '' !== $value ) {
		$styles[] = sprintf( '--vczapi-btn-color-hover: %s', $value );
	}
}

if ( ! empty( $attributes['bgVisitedColor'] ) ) {
	$value = \Codemanas\VczApi\Blocks\ButtonHelper::sanitize_css_color( $attributes['bgVisitedColor'] );

	if ( '' !== $value ) {
		$styles[] = sprintf( '--vczapi-btn-bg-visited: %s', $value );
	}
}

if ( ! empty( $attributes['textVisitedColor'] ) ) {
	$value = \Codemanas\VczApi\Blocks\ButtonHelper::sanitize_css_color( $attributes['textVisitedColor'] );

	if ( '' !== $value ) {
		$styles[] = sprintf( '--vczapi-btn-color-visited: %s', $value );
	}
}

// Map Spacing (Padding & Margin)
foreach ( [ 'padding', 'margin' ] as $type ) {
	if ( ! empty( $attributes[ $type ] ) && is_array( $attributes[ $type ] ) ) {
		foreach ( [ 'top', 'right', 'bottom', 'left' ] as $side ) {
			if ( isset( $attributes[ $type ][ $side ] ) && '' !== $attributes[ $type ][ $side ] ) {
				$val = \Codemanas\VczApi\Blocks\ButtonHelper::sanitize_css_size( $attributes[ $type ][ $side ] );

				if ( '' !== $val ) {
					$styles[] = sprintf( '%s-%s: %s', $type, $side, $val );
				}
			}
		}
	}
}

$inline_style = ! empty( $styles ) ? implode( '; ', $styles ) : '';

// 4. Native Block Wrapper Attributes
$wrapper_attributes = get_block_wrapper_attributes( [
	'class' => 'vczapi-button-block',
] );
?>

<div <?php echo $wrapper_attributes; ?>>
    <a
            href="<?php echo esc_url( $url ); ?>"
            class="vczapi-btn"
		<?php if ( ! empty( $inline_style ) ) : ?>
            style="<?php echo esc_attr( $inline_style ); ?>"
		<?php endif; ?>
		<?php if ( $open_in_new ) : ?>
            target="_blank"
            rel="noopener noreferrer"
		<?php endif; ?>
    >
		<?php echo esc_html( $display_text ); ?>
    </a>
</div>