<?php
/**
 * The template for displaying shortcode join links
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/join-links.php
 *
 * @author Deepen Bajracharya
 * @created_on 02/19/2020
 * @since 3.1.2
 * @modified 3.3.1
 */

global $webinars;

if ( ! empty( $webinars['join_uri'] ) ) {
	?>
    <tr>
        <td><?php _e( 'Join via Zoom App', 'video-conferencing-with-zoom-api' ); ?></td>
        <td>
            <a class="btn-join-link-shortcode" target="_blank" href="<?php echo $webinars['join_uri']; ?>" title="Join via App"><?php _e( 'Join', 'video-conferencing-with-zoom-api' ); ?></a>
        </td>
    </tr>
<?php } ?>

<?php if ( ! empty( $webinars['browser_url'] ) ) { ?>
    <tr>
        <td><?php _e( 'Join via Web Browser', 'video-conferencing-with-zoom-api' ); ?></td>
        <td>
            <a class="btn-join-link-shortcode" target="_blank" href="<?php echo $webinars['browser_url']; ?>" title="Join via Browser"><?php _e( 'Join', 'video-conferencing-with-zoom-api' ); ?></a>
        </td>
    </tr>
<?php } ?>