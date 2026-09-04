<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="zoom-metabox-wrapper">
    <div class="zoom-metabox-content">
        <p><?php _e( 'Enable Debug?', 'video-conferencing-with-zoom-api' ); ?>
            <input type="checkbox" name="option_enable_debug_logs" value="1" <?php ! empty( $meeting_fields['site_option_enable_debug_log'] ) ? checked( '1', $meeting_fields['site_option_enable_debug_log'] ) : false; ?> class="regular-text">
        </p>
    </div>
</div>
<style>
    pre {
        position: relative;
        width: 100%;
        padding: 0;
        margin: 0;
        overflow: auto;
        overflow-y: hidden;
        font-size: 12px;
        line-height: 20px;
        background: #efefef;
        border: 1px solid #777;
    }
</style>
<?php
if ( ! empty( $meeting_fields['site_option_enable_debug_log'] ) && ! empty( $meeting_details ) ) {
	if ( ! empty( $meeting_details->id ) ) {
		if ( ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 2 ) {
			dump( json_decode( zoom_conference()->getWebinarInfo( $meeting_details->id ) ) );
		} else {
			dump( json_decode( zoom_conference()->getMeetingInfo( $meeting_details->id ) ) );
		}
	} else {
		dump( $meeting_details );
	}
}