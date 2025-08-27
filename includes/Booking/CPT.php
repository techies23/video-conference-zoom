<?php

namespace Codemanas\VczApi\Booking;

use WP_Error;

class CPT {
	private static ?CPT $_instance = null;
	private string $post_type = 'vczapi_booking';

	public static function get_instance(): ?CPT {
		return ( self::$_instance == null ) ? self::$_instance = new self() : self::$_instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
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
			'show_in_rest'       => false
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
	public function add_booking( string $name, string $date, string $timezone ) {

		$post_data = [
			'post_title'  => $name . ' - ' . $date,
			'post_status' => 'publish',
			'post_type'   => 'vczapi_booking',
			'meta_input'  => [
				'booking_date' => $date,
				'timezone'     => $timezone,
				'name'         => $name
			]
		];

		return wp_insert_post( $post_data );
	}

	public function get_post_type(): string {
		return $this->post_type;
	}
}