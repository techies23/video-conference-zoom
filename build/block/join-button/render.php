<?php
/**
 * Dynamic Render Template for Zoom Join Button
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

// 1. Sanitize Inputs
$action_type = ! empty( $attributes['actionType'] ) ? sanitize_key( $attributes['actionType'] ) : 'app';
$source_type = ! empty( $attributes['sourceType'] ) ? sanitize_key( $attributes['sourceType'] ) : 'current';
$open_in_new = ! empty( $attributes['openInNewTab'] );


$url = '#';
//Logic to get join url
switch ( $action_type ) {
	case 'app':
		//join via app
		if ( $source_type == 'current' ) {
			$post_id         = get_the_ID();
			$meeting_details = get_post_meta( $post_id, '_meeting_zoom_details', true );
			if ( is_object( $meeting_details ) and isset( $meeting_details->join_url ) and isset( $meeting_details->encrypted_password ) ) {
				$url = \Codemanas\VczApi\Helpers\Links::getPwdEmbeddedJoinLink( $meeting_details->join_url, $meeting_details->encrypted_password );
			}
		} elseif ( $source_type == 'post_type' ) {
			$meeting_post_id = ! empty( $attributes['selectedMeetingPostId'] ) ? sanitize_key( $attributes['selectedMeetingPostId'] ) : '';
			if ( $meeting_post_id ) {
				$meeting_details = get_post_meta( $meeting_post_id, '_meeting_zoom_details', true );
				if ( is_object( $meeting_details ) and isset( $meeting_details->join_url ) and isset( $meeting_details->encrypted_password ) ) {
					$url = \Codemanas\VczApi\Helpers\Links::getPwdEmbeddedJoinLink( $meeting_details->join_url, $meeting_details->encrypted_password );
				}
			}
		} elseif ( $source_type == 'custom' ) {
			$meeting_id = ! empty( $attributes['meetingId'] ) ? sanitize_text_field( $attributes['meetingId'] ) : '';
			if ( ! empty( $meeting_id ) ) {
				$meeting = zoom_conference_v2()->meetings()->get( $meeting_id );
				$url     = \Codemanas\VczApi\Zoom\Helpers\MeetingHelper::getJoinUrl( $meeting );
			}
		}
		break;
	case 'browser':
		if ( $source_type == 'current' ) {
			$post_id         = get_the_ID();
			$meeting_details = get_post_meta( $post_id, '_meeting_zoom_details', true );
			if ( is_object( $meeting_details ) and isset( $meeting_details->id ) ) {
				$url = \Codemanas\VczApi\Helpers\Links::getJoinViaBrowserJoinLinks( [
					'link_only' => true,
					'post_id'   => $post_id,
					'password'  => $meeting_details->password ?? ''
				], $meeting_details->id );
			}
		} elseif ( $source_type == 'post_type' ) {
			$meeting_post_id = ! empty( $attributes['selectedMeetingPostId'] ) ? sanitize_key( $attributes['selectedMeetingPostId'] ) : '';
			if ( $meeting_post_id ) {
				$meeting_details = get_post_meta( $meeting_post_id, '_meeting_zoom_details', true );
				if ( is_object( $meeting_details ) and isset( $meeting_details->id ) ) {
					$url = \Codemanas\VczApi\Helpers\Links::getJoinViaBrowserJoinLinks( [
						'link_only' => true,
						'post_id'   => $meeting_post_id,
						'password'  => $meeting_details->password ?? ''
					], $meeting_details->id );
				}
			}
		}
}

// 2. Fallback Label Logic
$default_text_map = [
	'app'     => __( 'Join via Zoom App', 'video-conferencing-with-zoom-api' ),
	'browser' => __( 'Join via Web Browser', 'video-conferencing-with-zoom-api' ),
	'start'   => __( 'Start Meeting', 'video-conferencing-with-zoom-api' ),
];

if ( ! empty( $attributes['buttonText'] ) ) {
	$display_text = $attributes['buttonText'];
} elseif ( isset( $default_text_map[ $action_type ] ) ) {
	$display_text = $default_text_map[ $action_type ];
} else {
	$display_text = __( 'Join Meeting', 'video-conferencing-with-zoom-api' );
}

// 3. CSS Variable Mapping for States
$styles = [];

if ( ! empty( $attributes['backgroundColor'] ) ) {
	$styles[] = sprintf( '--vczapi-btn-bg: %s', sanitize_text_field( $attributes['backgroundColor'] ) );
}
if ( ! empty( $attributes['textColor'] ) ) {
	$styles[] = sprintf( '--vczapi-btn-color: %s', sanitize_text_field( $attributes['textColor'] ) );
}
if ( ! empty( $attributes['bgHoverColor'] ) ) {
	$styles[] = sprintf( '--vczapi-btn-bg-hover: %s', sanitize_text_field( $attributes['bgHoverColor'] ) );
}
if ( ! empty( $attributes['textHoverColor'] ) ) {
	$styles[] = sprintf( '--vczapi-btn-color-hover: %s', sanitize_text_field( $attributes['textHoverColor'] ) );
}
if ( ! empty( $attributes['bgVisitedColor'] ) ) {
	$styles[] = sprintf( '--vczapi-btn-bg-visited: %s', sanitize_text_field( $attributes['bgVisitedColor'] ) );
}
if ( ! empty( $attributes['textVisitedColor'] ) ) {
	$styles[] = sprintf( '--vczapi-btn-color-visited: %s', sanitize_text_field( $attributes['textVisitedColor'] ) );
}

// Map Spacing (Padding & Margin)
foreach ( [ 'padding', 'margin' ] as $type ) {
	if ( ! empty( $attributes[ $type ] ) && is_array( $attributes[ $type ] ) ) {
		foreach ( [ 'top', 'right', 'bottom', 'left' ] as $side ) {
			if ( isset( $attributes[ $type ][ $side ] ) && '' !== $attributes[ $type ][ $side ] ) {
				$val      = sanitize_text_field( $attributes[ $type ][ $side ] );
				$styles[] = sprintf( '%s-%s: %s', $type, $side, $val );
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
		<?php echo wp_kses_post( $display_text ); ?>
    </a>
</div>