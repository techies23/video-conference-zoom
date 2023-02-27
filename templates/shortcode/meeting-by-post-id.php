<?php

defined( 'ABSPATH' ) || exit;

global $zoom;

if ( ! vczapi_pro_version_active() && vczapi_pro_check_type( $zoom['api']->type ) || empty( $zoom ) ) {
	return;
}
?>

<div class="vczapi-show-by-postid">
    <div class="vczapi-show-by-postid-contents">
		<?php do_action( 'vczoom_single_content_right' );
		?>
    </div>
</div>
