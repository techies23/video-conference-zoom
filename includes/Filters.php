<?php

namespace Codemanas\VczApi;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filters Controller
 *
 * @since   3.6.0
 * @author  Deepen Bajracharya
 */
class Filters {

	private static $_instance = null;

	/**
	 * Create only one instance so that it may not Repeat
	 *
	 * @since 2.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Define post type
	 *
	 * @var string
	 */
	private string $post_type = 'zoom-meetings';

	/**
	 * Zoom_Video_Conferencing_Filters constructor.
	 */
	public function __construct() {
		add_action( 'vczapi_before_main_content_post_loop', [ $this, 'filters' ], 10 );
		add_action( 'vczapi_before_shortcode_content_post_loop', [ $this, 'shortcode_filter' ] );
		add_action( 'pre_get_posts', [ $this, 'filter_meetings' ] );
	}

	/**
	 * Shorcode Filter hook
	 *
	 * @param $query
	 */
	public function shortcode_filter( $query ) {
		if ( ! empty( $query->query ) && ! empty( $query->query['caller'] ) && $query->query['caller'] === "vczapi" ) {
			$this->show_filters_html( $query );
		}
	}

	/**
	 * Filters
	 */
	public function filters() {
		global $wp_query;
		$this->show_filters_html( $wp_query );
	}

	/**
	 * Render HTML filter VIEW CONTENTS
	 *
	 * @param $query
	 */
	public function show_filters_html( $query ) {
		unset( $GLOBALS['vczapi'] );
		$GLOBALS['vczapi'] = array();
		//Get all TERMS
		$terms = get_terms( array(
			'taxonomy'   => 'zoom-meeting',
			'hide_empty' => false
		) );

		if ( ! empty( $terms ) ) {
			$GLOBALS['vczapi']['terms'] = $terms;
		}

		$query_strings = $this->intercept_globals();
		if ( ! empty( $query_strings ) ) {
			$GLOBALS['vczapi']['query'] = $query_strings;
		}

		$GLOBALS['vczapi']['found_posts'] = $query->found_posts;

		vczapi_get_template( 'fragments/filters.php', true, false );
	}

	/**
	 * Filter meetings based on search pattern
	 *
	 * @param $query \WP_Query
	 *
	 * @return mixed
	 */
	public function filter_meetings( $query ) {
		//If admin - Abort operation filteration
		if ( is_admin() ) {
			return $query;
		}

		//Set Custom Variable for this sepecific call
		if ( is_post_type_archive( $this->post_type ) && $query->is_main_query() ) {
			//$query->set( 'caller', 'vczapi' );
			$query->query['caller'] = 'vczapi';
		}

		//Check conditiosn - abort if it does not match
		if ( ! empty( $query->query ) && ! empty( $query->query['caller'] ) && $query->query['caller'] === "vczapi" && ! empty( $query->query['post_type'] ) && $query->query['post_type'] === $this->post_type ) {

			$query_args = $this->intercept_globals();

			//Search by string
			if ( ! empty( $query_args['s'] ) ) {
				$query->set( 's', $query_args['s'] );
			}

			//Search by Taxonomy
			if ( ! empty( $query_args['tax'] ) && $query_args['tax'] !== "category_order" ) {
				$tax_query = $this->sortByTaxonomy( $query_args['tax'] );
				$query->set( 'tax_query', $tax_query );
			}

			//Search by Order By
			if ( ! empty( $query_args['order'] ) && $query_args['order'] !== "show_all" ) {
				$orderby = ( $query_args['order'] === "past" ) ? 'ASC' : 'DESC';
				$query->set( 'orderby', 'meta_value' );
				$query->set( 'meta_key', '_meeting_field_start_date_utc' );
				$query->set( 'order', $orderby );
			}
		}

		return $query;
	}

	/**
	 * Intercept Global Variables
	 *
	 * @return array
	 */
	private function intercept_globals() {
		$taxonomy = ! empty( $_GET['taxonomy'] ) ? esc_attr( $_GET['taxonomy'] ) : false;
		$orderby  = ! empty( $_GET['orderby'] ) ? esc_attr( $_GET['orderby'] ) : false;
		$search   = ! empty( $_GET['search'] ) ? esc_attr( $_GET['search'] ) : false;

		$result = array(
			'tax'   => $taxonomy,
			'order' => $orderby,
			's'     => $search,
		);

		return $result;
	}

	/**
	 * Sort by Taxonomy
	 *
	 * @param $slug
	 *
	 * @return array
	 */
	private function sortByTaxonomy( $slug ) {
		$tax_query = [
			[
				'taxonomy' => 'zoom-meeting',
				'field'    => 'slug',
				'terms'    => $slug,
				'operator' => 'IN'
			]
		];

		return $tax_query;
	}
}