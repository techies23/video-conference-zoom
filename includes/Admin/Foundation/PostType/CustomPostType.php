<?php

namespace Codemanas\VczApi\Admin\Foundation\PostType;

use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Date;
use Codemanas\VczApi\Helpers\MeetingType;

class CustomPostType {

	private string $postType;

	public function __construct( string $postType ) {
		$this->postType = $postType;
	}

	public function register(): void {
		$definition = new ZoomPostTypeDefinition( $this->postType );
		register_post_type( $this->postType, $definition->getArgs() );
	}

	public function showFilterOptions( $postType ): void {
		if ( $this->postType !== $postType ) {
			return;
		}

		$slug     = 'zoom-meeting';
		$taxonomy = get_taxonomy( $slug );
		$selected = $_REQUEST[ $slug ] ?? '';
		wp_dropdown_categories( array(
			'show_option_all' => $taxonomy->labels->all_items,
			'taxonomy'        => $slug,
			'name'            => $slug,
			'orderby'         => 'name',
			'value_field'     => 'slug',
			'selected'        => $selected,
			'hierarchical'    => true,
			'hide_if_empty'   => true,
		) );
	}

	/**
	 * Add New Start Link column
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function addColumns( $columns ): mixed {
		$columns['type']          = __( 'Type', 'video-conferencing-with-zoom-api' );
		$columns['start_meeting'] = __( 'Start Meeting', 'video-conferencing-with-zoom-api' );
		$columns['start_date']    = __( 'Start Date', 'video-conferencing-with-zoom-api' );
		$columns['meeting_id']    = __( 'Meeting ID', 'video-conferencing-with-zoom-api' );

		return $columns;
	}

	/**
	 * Sortable Data Column
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function sortableData( $columns ): mixed {
		$columns['start_date'] = 'zoom_meeting_startdate';

		return $columns;
	}

	/**
	 * Render HTML
	 *
	 * @param $column
	 * @param $post_id
	 */
	public function columnData( $column, $post_id ): void {
		$meeting = Metastore::getPostMeta( $post_id, 'meeting_zoom_details' );
		switch ( $column ) {
			case 'type':
				if ( ! empty( $meeting ) && ! empty( $meeting['type'] ) && MeetingType::is_webinar( $meeting['type'] ) ) {
					_e( 'Webinar', 'video-conferencing-with-zoom-api' );
				} else {
					_e( 'Meeting', 'video-conferencing-with-zoom-api' );
				}
				break;
			case 'start_meeting':
				if ( ! empty( $meeting ) && ! empty( $meeting['start_url'] ) ) {
					echo '<a href="' . esc_url( $meeting['start_url'] ) . '" target="_blank">Start</a>';
				} else {
					_e( 'Meeting not created yet.', 'video-conferencing-with-zoom-api' );
				}
				break;
			case 'start_date':
				if ( ! empty( $meeting ) && ! empty( $meeting['type'] ) && MeetingType::is_scheduled_meeting_or_webinar( $meeting['type'] ) && ! empty( $meeting['start_time'] ) ) {
					echo esc_html( Date::dateConverter( $meeting['start_time'], $meeting['timezone'], 'F j, Y, g:i a' ) );
				} elseif ( ! empty( $meeting ) && vczapi_pro_check_type( $meeting['type'] ) ) {
					_e( 'Recurring Meeting', 'video-conferencing-with-zoom-api' );
				} else {
					_e( 'Meeting not created yet.', 'video-conferencing-with-zoom-api' );
				}
				break;
			case 'meeting_id':
				if ( ! empty( $meeting ) && ! empty( $meeting['id'] ) ) {
					echo $meeting['id'];
				} else {
					_e( 'Meeting not created yet.', 'video-conferencing-with-zoom-api' );
				}
				break;
		}
	}

	/**
	 * Add Filters on SUB SUB SUB column
	 *
	 * @param $views
	 *
	 * @return mixed
	 */
	public function addFiltersOnSubSubSub( $views ): mixed {
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] !== $this->postType ) {
			return $views;
		}

		$upcoming          = isset( $_GET['type'] ) && $_GET['type'] === "upcoming" ? 'class="current"' : '';
		$past              = isset( $_GET['type'] ) && $_GET['type'] === "past" ? 'class="current"' : '';
		$views['upcoming'] = sprintf( '<a href="%s" ' . $upcoming . '>' . __( "Upcoming", "video-conferencing-with-zoom-api" ) . '</a>', admin_url( '/edit.php?post_type=zoom-meetings&type=upcoming' ) );
		$views['past']     = sprintf( '<a href="%s" ' . $past . '>' . __( "Past", "video-conferencing-with-zoom-api" ) . '</a>', admin_url( '/edit.php?post_type=zoom-meetings&type=past' ) );

		return $views;
	}

	public function filterPosts( $query ) {
		global $pagenow;

		if ( 'edit.php' != $pagenow || ! $query->is_admin || ( ! empty( $query->query['post_type'] ) && $query->query['post_type'] != $this->postType ) ) {
			return $query;
		}

		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === $this->postType && $query->query['post_type'] === $this->postType ) {
			$type = $_GET['type'] ?? false;
			$now  = Date::dateConverter( 'now', 'UTC', 'Y-m-d H:i:s', false );
			if ( $type === "upcoming" ) {
				$meta_query = [
					[
						'key'     => 'vczapi_meeting_start_date_utc',
						'value'   => $now,
						'compare' => '>=',
						'type'    => 'DATETIME',
					],
				];

				$query->set( 'meta_query', $meta_query );
			} elseif ( $type === "past" ) {
				$meta_query = [
					[
						'key'     => 'vczapi_meeting_start_date_utc',
						'value'   => $now,
						'compare' => '<=',
						'type'    => 'DATETIME',
					],
				];

				$query->set( 'meta_query', $meta_query );
			}
		}

		return $query;
	}
}