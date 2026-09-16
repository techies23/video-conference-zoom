<?php

namespace Codemanas\VczApi\Admin\Schema;

use Codemanas\VczApi\Helpers\Locales;

class SettingsFieldSchema {

	/**
	 * Settings field extendable schema
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function getSettingsFieldsSchema( array $settings = [] ): array {
		$text_domain = 'video-conferencing-with-zoom-api';

		$schema = [
			'general' => [
				'title'  => __( 'General Settings', $text_domain ),
				'fields' => [
					'delete_zom_meeting_also'      => [
						'label'          => __( 'Meetings Deletion ?', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'on',
						'storage_key'    => 'delete_zoom_meeting',
						'description'    => __( 'Do not delete your meetings on Zoom, when you delete your meeting from Zoom Meetings > All Meetings page.', $text_domain ),
					],
					'disable_countdown_timer'            => [
						'label'          => __( 'Disable Countdown Timer', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'on',
						'input_class'    => [ 'form-control' ],
						'description'    => __( 'This setting will disable countdown timer on single Zoom Events page. Check this option if you want to disable the countdown.', $text_domain ),
					],
					'disable_auto_pwd_generation'        => [
						'label'          => __( 'Disable Auto Password Generation ?', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'on',
						'description'    => __( 'Checking this option will disable auto password generation for new meetings which are created from Zoom meeting > Add new section.', $text_domain ),
					],
					'hide_join_links_non_loggedin_users' => [
						'label'          => __( 'Hide Join Links for Non-Loggedin ?', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'on',
						'description'    => __( 'Checking this option will hide join links from your shortcode for non-loggedin users.', $text_domain ),
					],
					'embed_password_join_link'           => [
						'label'          => __( 'Disable Embed password in Link ?', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'on',
						'storage_key'    => 'embed_pwd_in_join_link',
						'description'    => __( 'Meeting password will not be included in the invite link to allow participants to join with just one click without having to enter the password.', $text_domain ),
					],
					'meeting_end_join_link'              => [
						'label'          => __( 'Show Past Join Link ?', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'on',
						'storage_key'    => 'join_links',
						'wrapper_class'  => 'enabled-join-links-after-mtg-end',
						'description'    => __( 'This will show join meeting links on frontend even after meeting time is already past.', $text_domain ),
					],
					'meeting_show_zoom_author_original'  => [
						'label'          => __( 'Show Zoom Author ?', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'on',
						'storage_key'    => 'zoom_author_show',
						'wrapper_class'  => 'show-zoom-authors',
						'description'    => __( 'Checking this show Zoom original Author in single meetings page which are created from', $text_domain ),
						'after_html'     => '<a href="' . esc_url( admin_url( '/edit.php?post_type=zoom-meetings' ) ) . '">' . __( 'Zoom Meetings', $text_domain ) . '</a>',
					],
					'zoom_api_meeting_goingtostart_text' => [
						'label'             => __( 'Meeting going to start Text', $text_domain ),
						'type'              => 'text',
						'storage_key'       => 'going_to_start',
						'placeholder'       => 'Click join button below to join the meeting now !',
						'custom_attributes' => [ 'style' => 'width: 400px;' ],
					],
					'zoom_api_meeting_ended_text'        => [
						'label'             => __( 'Meeting Ended Text', $text_domain ),
						'type'              => 'text',
						'storage_key'       => 'ended_mtg',
						'placeholder'       => 'This meeting has been ended by the host.',
						'custom_attributes' => [ 'style' => 'width: 400px;' ],
					],
					'zoom_api_debugger_logs'             => [
						'label'          => __( 'Enable Logs', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'on',
						'storage_key'    => 'debugger_logs',
						'input_class'    => [ 'zoom_api_debugger_logs' ],
						'description'    => __( 'This can be helpful in finding issues related to Zoom.', $text_domain ),
						'after_html'     => '<a href="' . esc_url( admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings&tab=debug' ) ) . '">' . __( 'Logs are here.', $text_domain ) . '</a>',
					],
				],
			],

			'join_via_browser' => [
				'title'  => __( 'Join via Web Browser Settings', $text_domain ),
				'fields' => [
					'meeting_disable_join_via_browser' => [
						'label'          => __( 'Disable Join via browser ?', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'on',
						'storage_key'    => 'disable_join_via_browser',
						'description'    => __( 'Checking this will hide all Join via Browser Buttons.', $text_domain ),
					],
					'meeting_show_email_field'         => [
						'label'          => __( 'Disable Email field when join via browser ?', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'on',
						'storage_key'    => 'hide_email_jvb',
						'description'    => __( 'Checking this show will hide email field in Join via Browser window. Email field is shown if the event is a webinar because email field is required in order to join a webinar.', $text_domain ),
					],
					'vczapi_disable_invite'            => [
						'label'          => __( 'Disable Invite field when join via browser ?', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'yes',
						'description'    => __( 'Checking this will disable invite button when user joins meeting via Join via Browser window.', $text_domain ),
					],
					'vczapi_enable_direct_join'        => [
						'label'          => __( 'Enable direct join via web browser?', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'yes',
						'storage_key'    => 'enable_direct_join_via_browser',
						'description'    => __( 'Checking this will allow users to join via web browser directly. Without needing to enter any names or passwords.', $text_domain ),
					],
					'meeting-lang'                     => [
						'label'       => __( 'Default Language for Join via browser page ?', $text_domain ),
						'type'        => 'select',
						'storage_key' => 'join_via_browser_default_lang',
						'default'     => 'all',
						'options'     => array_merge( [ 'all' => __( 'Show All', $text_domain ) ], Locales::getSupportedTranslationsForWeb() ),
						'description' => __( 'Select a default language for your join meeting via browser page.', $text_domain ),
					],
				],
			],

			'date' => [
				'title'  => __( 'Date Settings', $text_domain ),
				'fields' => [
					'zoom_api_date_time_format'       => [
						'label'       => __( 'DateTime Format', $text_domain ),
						'type'        => 'composite',
						'storage_key' => 'locale_format',
						'description' => __( 'Change date time formats according to your choice. Please edit this properly. Failure to correctly put value will result in failure to show date in frontend.', $text_domain ),
						'help_html'   => sprintf(
							__( 'Please see %s on how to format date', $text_domain ),
							'<a href="https://www.php.net/manual/en/datetime.format.php" target="_blank" rel="nofollow noopener">https://www.php.net/manual/en/datetime.format.php</a>'
						),
						'fields'      => [
							'zoom_api_date_time_format'        => [
								'type'    => 'radio',
								'default' => 'LLLL',
								'options' => [
									'LLLL'   => 'Wednesday, May 6, 2020 05:00 PM',
									'lll'    => 'May 6, 2020 05:00 AM',
									'llll'   => 'Wed, May 6, 2020 05:00 AM',
									'L LT'   => '05/06/2020 03:00 PM',
									'l LT'   => '5/6/2020 03:00 PM',
									'custom' => __( 'Custom', $text_domain ),
								],
							],
							'zoom_api_custom_date_time_format' => [
								'type'        => 'text',
								'storage_key' => 'custom_date_time_format',
								'placeholder' => 'Y-m-d',
								'input_class' => [ 'regular-text' ],
							],
						],
					],
					'zoom_api_twenty_fourhour_format' => [
						'label'          => __( 'Use 24-hour format', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'on',
						'storage_key'    => 'twentyfour_format',
						'input_class'    => [ 'zoom_api_date_time_format' ],
						'description'    => __( 'Checking this option will show 24 hour time format in all event dates.', $text_domain ),
					],
					'zoom_api_full_month_format'      => [
						'label'          => __( 'Use full month label format ?', $text_domain ),
						'type'           => 'checkbox',
						'checkbox_value' => 'on',
						'storage_key'    => 'full_month_format',
						'input_class'    => [ 'zoom_api_date_time_format' ],
						'description'    => __( 'Checking this option will show full month label for example: June, July, August etc.', $text_domain ),
					],
				],
			],
		];

		return apply_filters( 'vczapi_admin_settings_fields_schema', $schema, $settings );
	}
}