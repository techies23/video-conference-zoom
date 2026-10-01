<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add New zoom meeting modal page.
 *
 * Renders all meeting meta box fields (plus title) inside a fixed,
 * non-closable modal layout. Submitting creates a draft post + a Zoom
 * meeting via AJAX and redirects to the post editor.
 */

$post            = $args['post'] ?? null;
$meeting_details = ! empty( $args['meeting_details'] ) ? $args['meeting_details'] : [];
$meeting_fields  = ! empty( $args['meeting_fields'] ) && is_array( $args['meeting_fields'] ) ? $args['meeting_fields'] : [];
$field_sections  = ! empty( $args['field_sections'] ) && is_array( $args['field_sections'] ) ? $args['field_sections'] : [];

$text_domain = 'video-conferencing-with-zoom-api';
?>
<div class="vczapi-create-meeting">
    <h1><?php esc_html_e( 'Create Zoom Event', $text_domain ); ?></h1>

    <form id="vczapi-create-meeting-form" class="vczapi-create-meeting__modal" aria-labelledby="vczapi-create-meeting-title">
        <header class="vczapi-create-meeting__header">
            <p class="description">
                <?php esc_html_e( 'Complete the meeting details below. A new Zoom meeting is created immediately and a draft post is saved to your editor.', $text_domain ); ?>
            </p>
        </header>
        <div class="vczapi-create-meeting__body">
            <div class="vczapi-create-meeting__body--meeting-title">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="vczapi-meeting-title"><?php esc_html_e( 'Meeting Title *', $text_domain ); ?></label>
                        </th>
                        <td>
                            <input type="text" name="title" id="vczapi-meeting-title" class="regular-text vczapi-required-validation" data-required="true" autofocus/>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div id="vczapi-admin-meeting-fields-meta">
                <div class="inside">
                    <?php
                    Codemanas\VczApi\Helpers\Templates::includeFile( VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/post-type/meta-box/meeting-fields.php', [
                            'post'            => $post,
                            'meeting_details' => $meeting_details,
                            'meeting_fields'  => $meeting_fields,
                            'field_sections'  => $field_sections,
                    ] );
                    ?>
                </div>
            </div>
        </div>

        <div class="vczapi-create-meeting__feedback" hidden></div>

        <footer class="vczapi-create-meeting__footer">
            <button type="submit" class="button button-primary vczapi-create-meeting__submit">
                <?php esc_html_e( 'Create Meeting', $text_domain ); ?>
                <span class="spinner vczapi-modal__spinner"></span>
            </button>
        </footer>
    </form>
</div>
