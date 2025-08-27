<?php

namespace Codemanas\VczApi\Booking;

use WP_Error;
use WP_Post;

class CPT {
    private static ?CPT $_instance = null;
    private string $post_type = 'vczapi_booking';

    private array $statuses = [
            'pending'   => 'draft',
            'approved'  => 'publish',
            'cancelled' => 'trash',
    ];

    public static function get_instance(): CPT {
        return ( self::$_instance == null ) ? self::$_instance = new self() : self::$_instance;
    }

    private array $meta_fields = [
            'booking_date' => 'Booking Date',
            'timezone'     => 'Timezone',
            'name'         => 'Name',
            'host_id'      => 'Host ID',
            'user_email'   => 'Email',
            'user_phone'   => 'Phone'
    ];

    private function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        // Rename post states for this CPT in the list table rows (e.g., Draft -> Pending Approval).
        add_filter( 'display_post_states', array( $this, 'rename_display_post_states' ), 10, 2 );
        // Rename status labels in UI strings (scoped to this CPT screens).
        add_filter( 'gettext', array( $this, 'filter_status_texts' ), 10, 2 );
        // Hooks for status transitions with placeholders.
        add_action( 'transition_post_status', array( $this, 'handle_status_transition' ), 10, 3 );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
    }

    /**
     * Registers a custom post-type for bookings with specified labels and arguments.
     *
     * The method defines the labels and arguments required for the 'Booking' post type and
     * calls WordPress' `register_post_type` function to register it. This post type is intended
     * for managing Zoom Appointments.
     *
     * @return void
     */
    public function register_post_type() {
        $labels = array(
                'name'               => __( 'Booking', 'video-conferencing-with-zoom-api' ),
                'singular_name'      => __( 'Booking', 'video-conferencing-with-zoom-api' ),
                'menu_name'          => __( 'Bookings', 'video-conferencing-with-zoom-api' ),
                'name_admin_bar'     => __( 'Booking', 'video-conferencing-with-zoom-api' ),
                'add_new'            => false,
                'add_new_item'       => __( 'Add New Booking', 'video-conferencing-with-zoom-api' ),
                'new_item'           => __( 'New Booking', 'video-conferencing-with-zoom-api' ),
                'edit_item'          => __( 'Edit Booking', 'video-conferencing-with-zoom-api' ),
                'view_item'          => __( 'View Booking', 'video-conferencing-with-zoom-api' ),
                'all_items'          => __( 'All Bookings', 'video-conferencing-with-zoom-api' ),
                'search_items'       => __( 'Search Bookings', 'video-conferencing-with-zoom-api' ),
                'parent_item_colon'  => __( 'Parent Bookings:', 'video-conferencing-with-zoom-api' ),
                'not_found'          => __( 'No bookings found.', 'video-conferencing-with-zoom-api' ),
                'not_found_in_trash' => __( 'No bookings found in Trash.', 'video-conferencing-with-zoom-api' )
        );

        $args = array(
                'labels'             => $labels,
                'description'        => __( 'Zoom Appointments', 'video-conferencing-with-zoom-api' ),
                'public'             => false,
                'publicly_queryable' => false,
                'show_ui'            => true,
                'show_in_menu'       => 'edit.php?post_type=zoom-meetings',
                'query_var'          => true,
                'rewrite'            => false,
                'capability_type'    => 'post',
                'has_archive'        => false,
                'hierarchical'       => false,
                'supports'           => array( 'title', 'author' ),
                'show_in_rest'       => false,
                'map_meta_cap'       => true,
                'capabilities'       => array(
                        'create_posts' => 'do_not_allow',
                ),
        );

        register_post_type( $this->post_type, $args );
    }


    /**
     * Adds a new booking with the specified details.
     *
     * @param  string  $name  The name associated with the booking.
     * @param  string  $date  The date of the booking.
     * @param  string  $timezone  The timezone for the booking.
     *
     * @return int|WP_Error     The ID of the newly created booking post or a WP_Error object on failure.
     */
    public function add_entry( string $name, string $date, string $timezone, string $host_id, string $email, string $phone = '' ) {

        $post_data = [
                'post_title'  => $name . ' - ' . $date,
                'post_status' => 'draft',
                'post_type'   => 'vczapi_booking',
                'meta_input'  => [
                        'booking_date' => $date,
                        'timezone'     => $timezone,
                        'name'         => $name,
                        'host_id'      => $host_id,
                        'user_email'   => $email,
                        'user_phone'   => $phone

                ]
        ];

        $post_id = wp_insert_post( $post_data );
        if ( ! is_wp_error( $post_id ) ) {
            //All details are saved in post_meta - use that.
            do_action( 'vczapi_meeting_booker_booking_created', $post_id );
        }

        return $post_id;
    }

    public function get_post_type(): string {
        return $this->post_type;
    }

    /**
     * Rename row state labels for this CPT in the posts list table.
     * E.g., Draft -> Pending Approval.
     *
     * @param  array  $states  Existing states.
     * @param  WP_Post  $post  Current post.
     *
     * @return array
     */
    public function rename_display_post_states( array $states, WP_Post $post ): array {
        if ( $post->post_type !== $this->post_type ) {
            return $states;
        }

        // Draft -> Pending Approval
        if ( isset( $states['draft'] ) ) {
            $states['draft'] = __( 'Pending Approval', 'video-conferencing-with-zoom-api' );
        }

        return $states;
    }

    /**
     * Rename various UI strings related to statuses for this CPT screens only.
     * - Published -> Approved
     * - Trash -> Canceled
     * - Draft -> Pending Approval
     *
     * @param  string  $translated  Translated text.
     * @param  string  $text  Original text.
     *
     * @return string
     */
    public function filter_status_texts( string $translated, string $text ): string {
        // Scope to this CPT admin screens only.
        if ( function_exists( 'get_current_screen' ) ) {
            $screen = get_current_screen();
            if ( ! $screen || $screen->post_type !== $this->post_type ) {
                return $translated;
            }
        } else {
            // Fallback: if not in admin screen context, don't alter strings.
            return $translated;
        }

        switch ( $text ) {
            case 'Published':
                return __( 'Approved', 'video-conferencing-with-zoom-api' );
            case 'Trash':
                return __( 'Cancelled', 'video-conferencing-with-zoom-api' );
            case 'Draft':
                return __( 'Pending Approval', 'video-conferencing-with-zoom-api' );
        }

        // Also handle some common variations used in list views and submit box.
        switch ( $text ) {
            case 'Move to Trash':
            case 'Move to trash':
                return __( 'Cancel', 'video-conferencing-with-zoom-api' );
            case 'Delete permanently':
                return __( 'Cancel permanently', 'video-conferencing-with-zoom-api' );
            case 'Restore from Trash':
                return __( 'Restore from Cancelled', 'video-conferencing-with-zoom-api' );
            case 'Status: Draft':
                return __( 'Status: Pending Approval', 'video-conferencing-with-zoom-api' );
            case 'Status: Published':
                return __( 'Status: Approved', 'video-conferencing-with-zoom-api' );
            case 'Trash restored.':
                return __( 'Cancelled restored.', 'video-conferencing-with-zoom-api' );
            case 'Trash emptied.':
                return __( 'Cancelled emptied.', 'video-conferencing-with-zoom-api' );
        }

        return $translated;
    }

    /**
     * Handle status transitions for this CPT and run placeholder code.
     *
     * @param  string  $new_status
     * @param  string  $old_status
     * @param  WP_Post  $post
     *
     * @return void
     */
    public function handle_status_transition( string $new_status, string $old_status, WP_Post $post ) {
        if ( $post->post_type !== $this->post_type ) {
            return;
        }

        if ( $new_status === 'publish' && $old_status !== 'publish' ) {
            // Placeholder: booking approved.
            // Add your logic here (e.g., notify user, trigger external API, etc).
            if ( function_exists( 'error_log' ) ) {
                error_log( sprintf( '[%s] Booking %d approved (was %s).', $this->post_type, $post->ID, $old_status ) );
            }
        }

        // ... existing code ...

        if ( $new_status === 'trash' && $old_status !== 'trash' ) {
            // Placeholder: booking cancelled.
            // Add your logic here (e.g., release resources, notify user, etc).
            if ( function_exists( 'error_log' ) ) {
                error_log( sprintf( '[%s] Booking %d cancelled (was %s).', $this->post_type, $post->ID, $old_status ) );
            }
        }
    }

    /**
     * Retrieve the specified status from the statuses array.
     *
     * @param  string  $status  The status key to retrieve.
     *
     * @return string The corresponding data for the provided status.
     */
    public function get_status( string $status ): string {
        return $this->statuses[ $status ] ?? $status;
    }

    /**
     * Register meta boxes for the booking post type
     */
    public function add_meta_boxes() {
        add_meta_box(
                'booking_details',
                __( 'Booking Details', 'video-conferencing-with-zoom-api' ),
                array( $this, 'render_meta_box' ),
                $this->post_type,
                'normal',
                'high'
        );
    }

    /**
     * Render the meta box content
     *
     * @param  WP_Post  $post  The post object
     */
    public function render_meta_box( WP_Post $post ) {
        foreach ( $this->meta_fields as $key => $label ) {
            $value = get_post_meta( $post->ID, $key, true );
            ?>
            <p>
                <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?>:</label><br>
                <input type="text" id="<?php echo esc_attr( $key ); ?>"
                       name="<?php echo esc_attr( $key ); ?>"
                       value="<?php echo esc_attr( $value ); ?>"
                       style="width: 100%"
                       readonly/>
            </p>
            <?php
        }
    }
}