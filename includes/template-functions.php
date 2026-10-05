<?php
/**
 * @author     Deepen.
 * @created_on 11/20/19
 */

// If this file is called directly, abort.
use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Date;
use Codemanas\VczApi\Helpers\Links;
use Codemanas\VczApi\Helpers\MeetingType;
use Codemanas\VczApi\Shortcodes\Assets;
use Codemanas\VczApi\Shortcodes\Support\ZoomResponse;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Function to check if a user is logged in or not
 *
 * @author Deepen
 * @since  3.0.0
 */
function video_conference_zoom_check_login(): bool {
    global $zoom;
    if ( ! empty( $zoom ) && ! empty( $zoom['site_option_logged_in'] ) ) {
        if ( is_user_logged_in() ) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

/**
 * Function to view featured image on the post
 *
 * @author Deepen
 * @since  3.0.0
 */
if ( ! function_exists( 'video_conference_zoom_featured_image' ) ) {
    function video_conference_zoom_featured_image() {
        vczapi_get_template( 'fragments/image.php', true );
    }
}

/**
 * Function to view main content i.e title and main content
 *
 * @author Deepen
 * @since  3.0.0
 */
if ( ! function_exists( 'video_conference_zoom_main_content' ) ) {
    function video_conference_zoom_main_content() {
        vczapi_get_template( 'fragments/content.php', true );
    }
}


/**
 * Function to add in the counter
 *
 * @author Deepen
 * @since  3.0.0
 */
if ( ! function_exists( 'video_conference_zoom_countdown_timer' ) ) {
    function video_conference_zoom_countdown_timer() {
        vczapi_get_template( 'fragments/countdown-timer.php', true );
    }
}

/**
 * Function to show meeting details
 *
 * @author Deepen
 * @since  3.0.0
 */
if ( ! function_exists( 'video_conference_zoom_meeting_details' ) ) {
    function video_conference_zoom_meeting_details() {
        vczapi_get_template( 'fragments/meeting-details.php', true );
    }
}

/**
 * Control State of the meeting by author from frontend
 */
function video_conference_zoom_meeting_end_author() {
    global $post;
    //Metastore so meetings saved by the 4.7.0 admin (`vczapi_*` meta) resolve too.
    $meeting = ZoomResponse::to_array( Metastore::getPostMeta( $post->ID, 'meeting_zoom_details' ) );
    $author  = vczapi_check_author( $post->ID );
    if ( ! $author ) {
        return;
    }

    $data = array(
            'ajaxurl'      => admin_url( 'admin-ajax.php' ),
            'zvc_security' => wp_create_nonce( "_nonce_zvc_security" ),
            'lang'         => array(
                    'confirm_end' => __( "Are you sure you want to end this meeting ? Users won't be able to join this meeting shown from the shortcode.", "video-conferencing-with-zoom-api" )
            )
    );
    wp_localize_script( Assets::SCRIPT_HANDLE, 'vczapi_state', $data );

    if ( ! empty( $meeting['code'] ) ) {
        return;
    }
    ?>
    <div class="dpn-zvc-sidebar-state">
        <?php if ( empty( $meeting['state'] ) ) { ?>
            <a href="javascript:void(0);" class="vczapi-meeting-state-change" data-type="post_type" data-state="end"
               data-postid="<?php echo $post->ID; ?>"
               data-id="<?php echo esc_attr( $meeting['id'] ?? '' ); ?>"><?php _e( 'End Meeting ?', 'video-conferencing-with-zoom-api' ); ?></a>
        <?php } else { ?>
            <a href="javascript:void(0);" class="vczapi-meeting-state-change" data-type="post_type" data-state="resume"
               data-postid="<?php echo $post->ID; ?>"
               data-id="<?php echo esc_attr( $meeting['id'] ?? '' ); ?>"><?php _e( 'Enable Meeting Join ?', 'video-conferencing-with-zoom-api' ); ?></a>
        <?php } ?>
        <p><?php _e( 'You are seeing this because you are the author of this post.', 'video-conferencing-with-zoom-api' ); ?></p>
    </div>
    <?php
}

/**
 * Function to show meeting join links
 *
 * @author Deepen
 * @since  3.0.0
 */
function video_conference_zoom_meeting_join() {
    global $zoom;
    if ( ! vczapi_pro_version_active() && ( ! empty( $zoom['api']->type ) && MeetingType::is_recurring_meeting_or_webinar( $zoom['api']->type ) ) || empty( $zoom ) ) {
        return;
    }

    if ( empty( $zoom['api']->state ) && video_conference_zoom_check_login() ) {
        if ( ! empty( $zoom['api']->code ) ) {
            echo '<p>' . $zoom['api']->message . '</p>';
        } else {
            $post_id            = get_the_id();
            $meeting_start_date = get_post_meta( $post_id, '_meeting_field_start_date_utc', true );
            $data               = array(
                    'ajaxurl'      => admin_url( 'admin-ajax.php' ),
                    'start_date'   => $meeting_start_date,
                    'timezone'     => $zoom['timezone'],
                    'post_id'      => $post_id,
                    'meeting_type' => $zoom['api']->type,
                    'page'         => 'single-meeting'
            );
            $data               = apply_filters( 'vczapi_single_meeting_localized_data', $data );
            wp_localize_script( Assets::SCRIPT_HANDLE, 'mtg_data', $data );
        }
    } elseif ( ! empty( $zoom['api']->state ) && $zoom['api']->state == "ended" ) {
        echo "<p>" . __( 'This meeting has ended.', 'video-conferencing-with-zoom-api' ) . "</p>";
    } else {
        echo "<p>" . __( 'Please login to join this meeting.', 'video-conferencing-with-zoom-api' ) . "</p>";
    }
}

/**
 * Generate join links
 *
 * @param $zoom_meeting
 *
 * @since  3.0.0
 *
 * @author Deepen
 */
function video_conference_zoom_meeting_join_link( $zoom_meeting ) {
    $disable_app_join = apply_filters( 'vczoom_join_meeting_via_app_disable', false );
    if ( ! empty( $zoom_meeting->join_url ) && ! $disable_app_join ) {
        $join_url = ! empty( $zoom_meeting->encrypted_password ) ? Links::getPwdEmbeddedJoinLink( $zoom_meeting->join_url, $zoom_meeting->encrypted_password ) : $zoom_meeting->join_url;
        ?>
        <a target="_blank" href="<?php echo esc_url( $join_url ); ?>"
           class="btn btn-join-link btn-join-via-app"><?php echo apply_filters( 'vczapi_join_meeting_via_app_text', __( 'Join Meeting via Zoom App', 'video-conferencing-with-zoom-api' ) ); ?></a>
        <?php
    }

    if ( wp_doing_ajax() ) {
        $post_id         = filter_input( INPUT_POST, 'post_id' );
        $meeting_details = get_post_meta( $post_id, '_meeting_fields', true );
        if ( ! empty( $zoom_meeting->id ) && ! empty( $post_id ) && empty( $meeting_details['site_option_browser_join'] ) && ! vczapi_check_disable_joinViaBrowser() ) {
            $meeting_id = ! empty( $zoom_meeting->pmi ) ? $zoom_meeting->pmi : $zoom_meeting->id;

            $args = [
                    'post_id' => $post_id,
            ];

            if ( ! empty( $zoom_meeting->password ) ) {
                $args['password'] = $zoom_meeting->password;
            }

            echo Links::getJoinViaBrowserJoinLinks( $args, $meeting_id );
        }
    }
}

/**
 * Generate join links for webinar
 *
 * @param $zoom_webinars
 *
 * @throws Exception
 * @since  3.4.0
 *
 * @author Deepen
 */
function video_conference_zoom_shortcode_join_link_webinar( $zoom_webinars ) {
    if ( empty( $zoom_webinars ) ) {
        echo "<p>" . __( 'Webinar is not defined. Try updating this Webinar', 'video-conferencing-with-zoom-api' ) . "</p>";

        return;
    }

    $webinar             = ZoomResponse::to_array( $zoom_webinars );
    $webinar_timezone    = $webinar['timezone'] ?? 'UTC';
    $now                 = new DateTime( 'now -1 hour', new DateTimeZone( $webinar_timezone ) );
    $closest_occurrence = false;
    $webinar_start_time  = $webinar['start_time'] ?? false;

    if ( ! empty( $webinar['type'] ) && MeetingType::is_recurring_fixed_time_webinar( $webinar['type'] ) && ! empty( $webinar['occurrences'] ) ) {
        $closest_occurrence = video_conference_zoom_closest_occurrence( $webinar['occurrences'], $webinar_timezone, $now );
    } elseif ( empty( $webinar['occurrences'] ) || ( ! empty( $webinar['type'] ) && MeetingType::is_recurring_no_fixed_time_webinar( $webinar['type'] ) ) ) {
        $webinar_start_time = false;
    }

    $start_time = ! empty( $closest_occurrence ) ? $closest_occurrence : $webinar_start_time;
    $start_time = new DateTime( $start_time, new DateTimeZone( $webinar_timezone ) );
    $start_time->setTimezone( new DateTimeZone( $webinar_timezone ) );
    if ( $now <= $start_time ) {
        unset( $GLOBALS['webinars'] );

        $args = [
                'link_only' => true
        ];

        if ( ! empty( $webinar['password'] ) ) {
            $args['password'] = $webinar['password'];
        }

        $browser_join = Links::getJoinViaBrowserJoinLinks( $args, $webinar['id'] ?? '' );
        $join_url     = ! empty( $webinar['encrypted_password'] ) ? Links::getPwdEmbeddedJoinLink( $webinar['join_url'] ?? '', $webinar['encrypted_password'] ) : ( $webinar['join_url'] ?? '' );
        $GLOBALS['webinars'] = array(
                'join_uri'    => apply_filters( 'vczoom_join_webinar_via_app_shortcode', $join_url, $webinar ),
                'browser_url' => ! vczapi_check_disable_joinViaBrowser() ? apply_filters( 'vczoom_join_webinar_via_browser_disable', $browser_join ) : false
        );
        vczapi_get_template( 'shortcode/webinar-join-links.php', true, false );
    }
}

/**
 * Find the next available occurrence of a recurring meeting or webinar.
 *
 * @param array|DateTimeInterface $occurrences Occurrence list.
 * @param string                  $timezone    Occurrence timezone.
 * @param DateTimeInterface       $now         Reference point, one hour ago by default.
 *
 * @return string|false Start time of the next occurrence, or false when there is none.
 */
function video_conference_zoom_closest_occurrence( $occurrences, string $timezone, DateTimeInterface $now ) {
    foreach ( (array) $occurrences as $occurrence ) {
        $occurrence = ZoomResponse::to_array( $occurrence );

        if ( empty( $occurrence['start_time'] ) || 'available' !== ( $occurrence['status'] ?? '' ) ) {
            continue;
        }

        $start_date = new DateTime( $occurrence['start_time'], new DateTimeZone( $timezone ) );

        if ( $start_date >= $now ) {
            return $occurrence['start_time'];
        }
    }

    return false;
}

/**
 * Generate join links
 *
 * @param $zoom_meetings
 *
 * @throws Exception
 * @since  3.0.0
 *
 * @author Deepen
 */
function video_conference_zoom_shortcode_join_link( $zoom_meetings ) {
    if ( empty( $zoom_meetings ) ) {
        echo "<p>" . __( 'Meeting is not defined. Try updating this meeting', 'video-conferencing-with-zoom-api' ) . "</p>";

        return;
    }

    $meeting              = ZoomResponse::to_array( $zoom_meetings );
    $meeting_timezone     = ! empty( $meeting['timezone'] ) ? $meeting['timezone'] : Date::get_timezone_offset();
    $now                  = new DateTime( 'now -1 hour', new DateTimeZone( $meeting_timezone ) );
    $closest_occurrence  = false;
    $meeting_start_time   = $meeting['start_time'] ?? false;
    $meeting_type         = $meeting['type'] ?? 0;

    if ( $meeting_type && MeetingType::is_recurring_meeting_or_webinar( $meeting_type ) && ! empty( $meeting['occurrences'] ) ) {
        $closest_occurrence = video_conference_zoom_closest_occurrence( $meeting['occurrences'], $meeting_timezone, $now );
    } elseif ( empty( $meeting['occurrences'] ) || MeetingType::is_recurring_no_fixed_time_meeting( $meeting_type ) || MeetingType::is_pmi( $meeting_type ) ) {
        $meeting_start_time = false;
    }

    $start_time = ! empty( $closest_occurrence ) ? $closest_occurrence : $meeting_start_time;
    $start_time = new DateTime( $start_time, new DateTimeZone( $meeting_timezone ) );
    $start_time->setTimezone( new DateTimeZone( $meeting_timezone ) );
    if ( $now <= $start_time ) {
        unset( $GLOBALS['meetings'] );

        $args = [
                'link_only' => true
        ];

        if ( ! empty( $meeting['password'] ) ) {
            $args['password'] = $meeting['password'];
        }

        $browser_join = Links::getJoinViaBrowserJoinLinks( $args, $meeting['id'] ?? '' );
        $join_url     = ! empty( $meeting['encrypted_password'] ) ? Links::getPwdEmbeddedJoinLink( $meeting['join_url'] ?? '', $meeting['encrypted_password'] ) : ( $meeting['join_url'] ?? '' );
        $GLOBALS['meetings'] = array(
                'join_uri'    => apply_filters( 'vczoom_join_meeting_via_app_shortcode', $join_url, $meeting ),
                'browser_url' => ! Metastore::checkDisableJoinViaBrowser() ? apply_filters( 'vczoom_join_meeting_via_browser_disable', $browser_join ) : false
        );
        vczapi_get_template( 'shortcode/join-links.php', true, false );
    }
}

if ( ! function_exists( 'video_conference_zoom_shortcode_table' ) ) {
    /**
     *  * Render Zoom Meeting ShortCode table in frontend
     *
     * @param $zoom_meetings
     *
     * @throws Exception
     * @since  3.0.0
     *
     * @author Deepen
     */
    function video_conference_zoom_shortcode_table( $zoom_meetings ) {
        if ( empty( $zoom_meetings ) ) {
            echo "<p>" . __( 'Meeting is not defined. Try updating this meeting', 'video-conferencing-with-zoom-api' ) . "</p>";

            return;
        }

        $meeting          = ZoomResponse::to_array( $zoom_meetings );
        $hide_join_link_nloggedusers = get_option( 'zoom_api_hide_shortcode_join_links' );
        $meeting_timezone = $meeting['timezone'] ?? 'UTC';
        ?>
        <table class="vczapi-shortcode-meeting-table">
            <tr class="vczapi-shortcode-meeting-table--row1">
                <td><?php _e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?></td>
                <td><?php echo esc_html( $meeting['id'] ?? '' ); ?></td>
            </tr>
            <tr class="vczapi-shortcode-meeting-table--row2">
                <td><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></td>
                <td><?php echo esc_html( $meeting['topic'] ?? '' ); ?></td>
            </tr>
            <tr class="vczapi-shortcode-meeting-table--row3">
                <td><?php _e( 'Meeting Status', 'video-conferencing-with-zoom-api' ); ?></td>
                <td>
                    <?php
                    $status = $meeting['status'] ?? '';
                    if ( 'waiting' === $status ) {
                        _e( 'Waiting - Not started', 'video-conferencing-with-zoom-api' );
                    } elseif ( 'started' === $status ) {
                        _e( 'Meeting is in Progress', 'video-conferencing-with-zoom-api' );
                    } else {
                        echo esc_html( $status );
                    }
                    ?>
                    <p class="small-description"><?php _e( 'Refresh is needed to change status.', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
            <?php
            $meeting_type = $meeting['type'] ?? 0;
            if ( $meeting_type && MeetingType::is_recurring_fixed_time_meeting( $meeting_type ) ) {
                if ( ! empty( $meeting['occurrences'] ) ) {
                    ?>
                    <tr class="vczapi-shortcode-meeting-table--row4">
                        <td><?php _e( 'Type', 'video-conferencing-with-zoom-api' ); ?></td>
                        <td><?php _e( 'Recurring Meeting', 'video-conferencing-with-zoom-api' ); ?></td>
                    </tr>
                    <tr class="vczapi-shortcode-meeting-table--row4">
                        <td><?php _e( 'Occurrences', 'video-conferencing-with-zoom-api' ); ?></td>
                        <td><?php echo count( (array) $meeting['occurrences'] ); ?></td>
                    </tr>
                    <tr class="vczapi-shortcode-meeting-table--row5">
                        <td><?php _e( 'Next Start Time', 'video-conferencing-with-zoom-api' ); ?></td>
                        <td>
                            <?php
                            $now                 = new DateTime( 'now -1 hour', new DateTimeZone( $meeting_timezone ) );
                            $closest_occurrence = video_conference_zoom_closest_occurrence( $meeting['occurrences'], $meeting_timezone, $now );

                            if ( $closest_occurrence ) {
                                echo esc_html( Date::dateConverter( $closest_occurrence, $meeting_timezone, 'F j, Y @ g:i a' ) );
                            } else {
                                _e( 'Meeting has ended !', 'video-conferencing-with-zoom-api' );
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                } else {
                    ?>
                    <tr class="vczapi-shortcode-meeting-table--row6">
                        <td><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></td>
                        <td><?php _e( 'Meeting has ended !', 'video-conferencing-with-zoom-api' ); ?></td>
                    </tr>
                    <?php
                }
            } elseif ( $meeting_type && MeetingType::is_recurring_no_fixed_time_meeting( $meeting_type ) ) {
                ?>
                <tr class="vczapi-shortcode-meeting-table--row6">
                    <td><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php _e( 'This is a meeting with no Fixed Time.', 'video-conferencing-with-zoom-api' ); ?></td>
                </tr>
                <?php
            } elseif ( $meeting_type && MeetingType::is_pmi( $meeting_type ) ) {
                ?>
                <tr class="vczapi-shortcode-meeting-table--row6">
                    <td><?php _e( 'Type', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php _e( 'Personal Meeting Room', 'video-conferencing-with-zoom-api' ); ?></td>
                </tr>
                <?php
            } elseif ( ! empty( $meeting['start_time'] ) ) {
                ?>
                <tr class="vczapi-shortcode-meeting-table--row6">
                    <td><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php echo esc_html( Date::dateConverter( $meeting['start_time'], $meeting_timezone, 'F j, Y @ g:i a' ) ); ?></td>
                </tr>
            <?php } ?>
            <?php if ( ! empty( $meeting_timezone ) ) { ?>
                <tr class="vczapi-shortcode-meeting-table--row7">
                    <td><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php echo esc_html( $meeting_timezone ); ?></td>
                </tr>
            <?php } ?>
            <?php if ( ! empty( $meeting['duration'] ) ) { ?>
                <tr class="zvc-table-shortcode-duration">
                    <td><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php echo esc_html( $meeting['duration'] ); ?></td>
                </tr>
                <?php
            }

            do_action( 'vczoom_meeting_shortcode_additional_fields', $meeting );

            if ( $hide_join_link_nloggedusers ) {
                $show_join_links = is_user_logged_in();
            } else {
                $show_join_links = true;
            }

            if ( $show_join_links ) {
                /**
                 * Hook: vczoom_meeting_shortcode_join_links
                 *
                 * @video_conference_zoom_shortcode_join_link - 10
                 *
                 */
                do_action( 'vczoom_meeting_shortcode_join_links', $meeting );
            }
            ?>
        </table>
        <?php
    }
}

if ( ! function_exists( 'video_conference_zoom_output_content_start' ) ) {
    function video_conference_zoom_output_content_start() {
        vczapi_get_template( 'global/wrap-start.php', true );
    }
}

if ( ! function_exists( 'video_conference_zoom_output_content_end' ) ) {
    function video_conference_zoom_output_content_end() {
        vczapi_get_template( 'global/wrap-end.php', true );
    }
}

/**
 * Get a slug identifying the current theme.
 *
 * @return string
 * @since 3.0.2
 */
function video_conference_zoom_get_current_theme_slug() {
    return apply_filters( 'video_conference_zoom_theme_slug_for_templates', get_option( 'template' ) );
}

/**
 * Before POST LOOP hook
 */
function video_conference_zoom_before_post_loop() {
    global $zoom_meetings;
    unset( $GLOBALS['zoom'] );
    $post_id               = get_the_id();
    $show_zoom_author_name = get_option( 'zoom_show_author' );
    //Read through Metastore so meetings saved by the 4.7.0 admin
    //(`vczapi_meeting_fields`) resolve as well as legacy `_meeting_fields` posts.
    $GLOBALS['zoom']       = ZoomResponse::to_array( Metastore::getPostMeta( $post_id, 'meeting_fields' ) );
    $meeting_details       = Metastore::getPostMeta( $post_id, 'meeting_zoom_details' );
    $meeting_author        = get_the_author();
    if ( ! empty( $show_zoom_author_name ) ) {
        $meeting_author = vczapi_get_meeting_author( $post_id, $meeting_details, $meeting_author );
    }
    $GLOBALS['zoom']['host_name'] = $meeting_author;

    $GLOBALS['zoom']['api'] = $meeting_details;
    $terms                  = get_the_terms( get_the_id(), 'zoom-meeting' );
    if ( ! empty( $terms ) ) {
        $set_terms = array();
        foreach ( $terms as $term ) {
            $set_terms[] = $term->name;
        }
        $GLOBALS['zoom']['terms'] = $set_terms;
    }

    if ( ! empty( $zoom_meetings ) && ! empty( $zoom_meetings->columns ) ) {
        $columns = 'vczapi-col-4';
        switch ( $zoom_meetings->columns ) {
            case 3:
                $columns = 'vczapi-col-4';
                break;
            case 2:
                $columns = 'vczapi-col-6';
                break;
            case 4:
                $columns = 'vczapi-col-3';
                break;
            case 1:
                $columns = 'vczapi-col-12';
                break;
        }

        $GLOBALS['zoom']['columns'] = $columns;
    }
}

/**
 * Display template for single pages.
 *
 * @param $post
 * @param $template
 *
 * @return bool|mixed|string
 */
function vczapi_get_single_or_zoom_template( $post, $template = false ) {
    if ( empty( $post ) && $post->post_type != 'zoom-meetings' ) {
        return false;
    }

    unset( $GLOBALS['zoom'] );

    $show_zoom_author_name = get_option( 'zoom_show_author' );

    $GLOBALS['zoom'] = Metastore::getPostMeta( $post->ID, 'meeting_fields' );
    $meeting_details = Metastore::getPostMeta( $post->ID, 'meeting_zoom_details' );

    if ( ! empty( $show_zoom_author_name ) ) {
        $meeting_author = vczapi_get_meeting_author( $post->ID, $meeting_details );
    } else {
        $meeting_author = get_userdata( $post->post_author );
        $meeting_author = ! empty( $meeting_author ) && ! empty( $meeting_author->first_name ) ? $meeting_author->first_name . ' ' . $meeting_author->last_name : $meeting_author->display_name;
    }

    if ( empty( $GLOBALS['zoom'] ) ) {
        $GLOBALS['zoom'] = array();
    }
    $GLOBALS['zoom']['host_name'] = ! empty( $meeting_author ) ? $meeting_author : false;
    if ( ! empty( $meeting_details ) ) {
//Legacy meta is stored as stdClass, 4.7.0 meta as an array. Wrapping it keeps
    //both `$zoom['api']['type']` and `$zoom['api']->type` working for templates.
    $GLOBALS['zoom']['api'] = \Codemanas\VczApi\Shortcodes\Support\ZoomResponse::make( $meeting_details );
    }

    $terms = get_the_terms( $post->ID, 'zoom-meeting' );
    if ( ! empty( $terms ) ) {
        $set_terms = array();
        foreach ( $terms as $term ) {
            $set_terms[] = $term->name;
        }
        $GLOBALS['zoom']['terms'] = $set_terms;
    }

    if ( ! empty( $template ) && vczapi_is_fse_theme() ) {
        return $template;
    }

    //Render View
    $template = vczapi_get_template( 'single-meeting.php' );

    return $template;
}