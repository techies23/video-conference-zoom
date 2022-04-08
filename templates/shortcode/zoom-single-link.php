<?php
/**
 * Template for showing single link with join url.
 *
 * @author Deepen.
 * @since 3.0.0
 * @version 3.9.0
 */

global $zoom_meetings;
?>

<a href="<?php echo $zoom_meetings->join_url; ?>" title="Join Meeting">Join Meeting</a>
