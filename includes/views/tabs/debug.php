<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="zvc-row">
	<section class="zvc-position-floater-left">
		<?php
		$uploads_dir = wp_upload_dir();
		$log_filePath = $uploads_dir['basedir'] . '/vczapi-logs/vczapi-logs.txt';
		echo "<pre>";
		echo file_get_contents($log_filePath);
		echo "</pre>";
		?>
	</section>
</div>
