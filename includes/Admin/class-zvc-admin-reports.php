<?php

/**
 * Class Reports
 *
 * @author  Deepen
 * @since   2.0.0
 */
class Zoom_Video_Conferencing_Reports {

	private static $instance;

	public function __construct() {
	}

	static function getInstance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Zoom Rerports View
	 *
	 * @since   1.0.0
	 * @changes in CodeBase
	 * @author  Deepen Bajracharya <dpen.connectify@gmail.com>
	 */
	public static function zoom_reports() {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );

		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'zoom_daily_report';

		//Get Template
		require_once ZVC_PLUGIN_VIEWS_PATH . '/live/tpl-reports.php';
	}

	/**
	 * Generate daily report
	 *
	 * @author Deepen
	 * @return array|bool|mixed|null|object|string
	 */
	public function get_daily_report_html() {
		$return_result = false;
		$months        = array(
			1  => 'January',
			2  => 'February',
			3  => 'March',
			4  => 'April',
			5  => 'May',
			6  => 'June',
			7  => 'July',
			8  => 'August',
			9  => 'September',
			10 => 'October',
			11 => 'November',
			12 => 'December'
		);

		if ( isset( $_POST['zoom_check_month_year'] ) ) {
			$zoom_monthyear = $_POST['zoom_month_year'];
			if ( $zoom_monthyear == null || $zoom_monthyear == "" ) {
				$return_result = __( "Date field cannot be Empty !!", "video-conferencing-with-zoom-api" );
			} else {
				$exploded_data = explode( ' ', $zoom_monthyear );
				foreach ( $months as $key => $month ) {
					if ( $exploded_data[0] == $month ) {
						$month_int = $key;
					}
				}
				$year          = $exploded_data[1];
				$result        = zoom_conference()->getDailyReport( $month_int, $year );
				$return_result = json_decode( $result );
			}
		}

		return $return_result;
	}

	/**
	 * Generate Account Report
	 *
	 * @author Deepen
	 * @return array|mixed|null|object|string
	 */
	public function get_account_report_html() {
		$return_result = false;
		if ( isset( $_POST['zoom_account_from'] ) && isset( $_POST['zoom_account_to'] ) ) {
			$zoom_account_from = $_POST['zoom_account_from'];
			$zoom_account_to   = $_POST['zoom_account_to'];
			if ( $zoom_account_from == null || $zoom_account_to == null ) {
				$return_result = __( "The fields cannot be Empty !!", "video-conferencing-with-zoom-api" );
			} else {
				$result        = zoom_conference()->getAccountReport( $zoom_account_from, $zoom_account_to );
				$return_result = json_decode( $result );
			}
		}

		return $return_result;
	}
}