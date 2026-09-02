<?php

namespace Codemanas\VczApi\admin;

class Cron {

	private static ?Cron $_instance = null;

	public static function get_instance(): ?Cron {
		return ( self::$_instance == null ) ? self::$_instance = new self() : self::$_instance;
	}

	protected function __construct() {
		// Hook into meeting save/creation
		add_action( 'save_post_zoom-meetings', [ $this, 'schedule_long_term_refresh' ], 20, 2 );

		// Register the actual cron execution action
		add_action( 'vczapi_refresh_long_term_meeting_cron', [ $this, 'execute_meeting_refresh' ], 10, 2 );
	}

	/**
	 * Schedules a long-term refresh for a meeting if applicable.
	 *
	 * This method ensures that long-term meetings synchronize with Zoom by
	 * scheduling a refresh event. It considers the meeting date and proximity
	 * to the 75-day safety window, clearing unnecessary refresh crons if the
	 * meeting date is adjusted inward to be within the window.
	 *
	 * @param   int      $post_id  The ID of the post associated with the meeting.
	 * @param   WP_Post  $post     The WordPress post object representing the meeting.
	 *
	 * @return void This method does not return a value.
	 */
	public function schedule_long_term_refresh( $post_id, $post ): void {
		if ( wp_is_post_revision( $post_id ) || $post->post_status !== 'publish' ) {
			return;
		}

		// Fetch meeting details array
		$meeting_fields = get_post_meta( $post_id, '_meeting_fields', true );
		if ( empty( $meeting_fields ) || empty( $meeting_fields['start_date'] ) ) {
			return;
		}

		$start_timestamp = strtotime( $meeting_fields['start_date'] );
		if ( false === $start_timestamp ) {
			return;
		}

		$current_time   = current_time( 'timestamp' );
		$days_until_mtg = ( $start_timestamp - $current_time ) / DAY_IN_SECONDS;

		$hook_name = 'vczapi_refresh_long_term_meeting_cron';
		$args      = [ $post_id, $meeting_fields['meeting_type'] ?? 1 ];
		$last_refresh_time = absint( get_post_meta( $post_id, '_vczapi_last_zoom_refresh_timestamp', true ) );


		/*
		 * If the meeting is now within the 75-day safety window, no future refresh is needed.
		 *
		 * If this meeting previously had a successful Zoom sync and has a refresh cron queued,
		 * clear that cron. This covers the case where an admin moves a long-term meeting
		 * inward, e.g. from 250 days away to 40 days away.
		 */
		if ( $days_until_mtg <= 75 ) {
			if( $last_refresh_time && wp_next_scheduled( $hook_name, $args )){
				wp_clear_scheduled_hook( $hook_name, $args );
				delete_post_meta( $post_id, '_vczapi_last_zoom_refresh_timestamp' );
			}
			return;
		}


		if ( empty( $last_refresh_time ) ) {
			$last_refresh_time = $current_time - ( 75 * DAY_IN_SECONDS );
		}


		$next_refresh_time = $last_refresh_time + ( 75 * DAY_IN_SECONDS );

		if ( $next_refresh_time <= $current_time ) {
			$next_refresh_time = $current_time + HOUR_IN_SECONDS;
		}

		$next_scheduled = wp_next_scheduled( $hook_name, $args );


		// Task 1: Check for your Tertiary condition (Admin pulled the date inward)
		// This needs to run regardless of whether a cron is found or not.
		if ( ! empty( $last_refresh_time ) ) {
			$days_since_last_sync = ( $start_timestamp - $last_refresh_time ) / DAY_IN_SECONDS;

			if ( $days_since_last_sync <= 75 ) {
				wp_clear_scheduled_hook( $hook_name, $args );
				return; // Drop out completely; token is perfectly safe until the new meeting date.
			}
		}

		if ( ! $next_scheduled ) {
			wp_schedule_single_event( $next_refresh_time, $hook_name, $args );
		}
	}

	/**
	 * The callback method that runs when the scheduled cron triggers.
	 */
	public function execute_meeting_refresh( $post_id, $meeting_type ): void {
		$meeting_id = get_post_meta( $post_id, '_meeting_zoom_meeting_id', true );
		$updated_meeting_arr = get_post_meta( $post_id, '_meeting_fields', true );
		$post = get_post( $post_id );

		if ( empty( $meeting_id ) || empty( $updated_meeting_arr ) || ! $post ) {
			return;
		}

		// Repurposed Core logic safely separated for Cron context
		if ( ! empty( $meeting_type ) && intval( $meeting_type ) === 2 ) {
			// WEBINAR REFRESH
			if ( class_exists( 'Zoom_Video_Conferencing_Admin_Webinars' ) ) {
				$webinar_arrr    = \Zoom_Video_Conferencing_Admin_Webinars::prepare_webinar( $updated_meeting_arr, $post );
				$webinar_updated = json_decode( zoom_conference()->updateWebinar( $meeting_id, $webinar_arrr ) );

				if ( empty( $webinar_updated->code ) ) {
					$webinar_info = json_decode( zoom_conference()->getWebinarInfo( $meeting_id ) );
					if ( ! empty( $webinar_info ) ) {
						update_post_meta( $post_id, '_meeting_zoom_details', $webinar_info );
						update_post_meta( $post_id, '_meeting_zoom_join_url', $webinar_info->join_url );
						update_post_meta( $post_id, '_meeting_zoom_start_url', $webinar_info->start_url );
						update_post_meta( $post_id, '_vczapi_last_zoom_refresh_timestamp', current_time( 'timestamp' ) );
					}
				}
			}
		} else {
			// MEETING REFRESH
			if ( class_exists( 'Zoom_Video_Conferencing_Admin_Meetings' ) ) {
				$mtg_param       = \Zoom_Video_Conferencing_Admin_Meetings::prepare_update( $meeting_id, $updated_meeting_arr, $post );
				$meeting_updated = json_decode( zoom_conference()->updateMeetingInfo( $mtg_param ) );

				if ( empty( $meeting_updated->code ) ) {
					$meeting_info = json_decode( zoom_conference()->getMeetingInfo( $meeting_id ) );
					if ( ! empty( $meeting_info ) ) {
						update_post_meta( $post_id, '_meeting_zoom_details', $meeting_info );
						update_post_meta( $post_id, '_meeting_zoom_join_url', $meeting_info->join_url );
						update_post_meta( $post_id, '_meeting_zoom_start_url', $meeting_info->start_url );
						update_post_meta( $post_id, '_vczapi_last_zoom_refresh_timestamp', current_time( 'timestamp' ) );
					}
				}
			}
		}

		// Self-perpetuating loop logic:
		// Now that it updated, check if the meeting is STILL more than 75 days away.
		// If it is (e.g. scheduled 7 months out), it schedules the next 75-day hop.
		$this->schedule_long_term_refresh( $post_id, $post );
	}
}