<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Pre-process Template Data
 */
$meeting_fields  = ! empty( $args['meeting_fields'] ) && is_array( $args['meeting_fields'] ) ? $args['meeting_fields'] : [];
$meeting_details = ! empty( $args['meeting_details'] ) ? $args['meeting_details'] : [];
$field_sections  = ! empty( $args['field_sections'] ) && is_array( $args['field_sections'] ) ? $args['field_sections'] : [];
$post            = ! empty( $args['post'] ) ? $args['post'] : null;

$is_published = ! empty( $post ) && $post->post_status === 'publish';
$has_zoom_id  = ! empty( $meeting_details ) && is_object( $meeting_details ) && ! empty( $meeting_details->id );
$text_domain  = 'video-conferencing-with-zoom-api';
?>

<div class="vczapi-metabox-wrapper">
    <?php /* ---------------- Shortcode Display ---------------- */ ?>
    <?php if ( $is_published && $has_zoom_id ) : ?>
        <div class="vczapi-shortcode-notice">
            <span class="dashicons dashicons-admin-page"></span>
            <code>[zoom_meeting_post post_id="<?php echo esc_attr( $post->ID ); ?>" template="boxed"]</code>
            <p class="description">
                <?php esc_html_e( 'Use this shortcode to display this meeting on any page or post.', $text_domain ); ?>
            </p>
        </div>
    <?php endif; ?>

    <?php /* ---------------- Pure CSS Accordion Loop ---------------- */ ?>
    <div class="vczapi-admin-accordion">
        <?php
        $index = 0;
        foreach ( $field_sections as $section_key => $section ) :
            $index ++;
            $input_id = 'vczapi-tab-' . esc_attr( $section_key );
            ?>
            <div class="vczapi-admin-accordion__item">
                <input type="checkbox" id="<?php echo $input_id; ?>" class="vczapi-admin-accordion__toggle" <?php checked( 1, 1 ); ?>>
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
                            ?>
                            <tr class="<?php echo $wrapper_class; ?>">
                                <th scope="row">
                                    <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $label ); ?></label>
                                </th>
                                <td>
                                    <?php if ( isset( $field_config['type'] ) && $field_config['type'] === 'composite' ) {
                                        $duration = vczapi_convertMinutesToHM( $meeting_fields['duration'] ?? 40, false );

                                        foreach ( $field_config['fields'] as $sub_key => $sub_config ) {
                                            $sub_val = ( $sub_key === 'hour' ) ? ( $duration['hr'] ?? 0 ) : ( $duration['min'] ?? 40 );
                                            ?>
                                            <span style="margin-right: 10px;">
                                                <?php \Codemanas\VczApi\Helpers\FormHelper::fields( $sub_key, $sub_config, $sub_val ); ?>
                                            </span>
                                            <?php
                                        }
                                    } else {
                                        $value = $meeting_fields[ $field_key ] ?? ( $field_config['default'] ?? '' );
                                        unset( $field_config['label'], $field_config['wrapper_class'] );
                                        \Codemanas\VczApi\Helpers\FormHelper::fields(
                                                $field_key,
                                                $field_config,
                                                $value
                                        );

                                        // If host is disabled for existing meeting, submit host ID via hidden field
                                        if ( $field_key === 'userId' && ! empty( $field_config['custom_attributes']['disabled'] ) ) {
                                            echo '<input type="hidden" name="userId" value="' . esc_attr( $value ) . '">';
                                        }
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
</div>
