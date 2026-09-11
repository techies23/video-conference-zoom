<?php

namespace Codemanas\VczApi\Admin\Controller\PostType\Schema;

use Codemanas\VczApi\Helpers\Date;
use WP_Post;

class MeetingFieldSchema {

	/**
	 * Meeting field extendable schema
	 *
	 * @param WP_Post $post
	 * @param $meeting_details
	 * @param array $users
	 *
	 * @return array
	 */
	public static function getMeetingFieldsSchema( \WP_Post $post, $meeting_details, array $users ): array {
		$text_domain = 'video-conferencing-with-zoom-api';
		$tzlists     = Date::timezone_list();
		$wp_timezone = Date::get_timezone_offset();
		$has_zoom_id = ! empty( $meeting_details ) && ! empty( $meeting_details['id'] );

		// Build host choices
		$host_options = [ '' => __( 'Type to search host...', $text_domain ) ];
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$host_options[ $user->id ] = sprintf( '%s %s (%s)', $user->first_name ?? '', $user->last_name ?? '', $user->email ?? '' );
			}
		}

		// Lock host selection if meeting is already published or has a Zoom ID
		$host_field_config = [
			'label'       => __( 'Meeting Host *', $text_domain ),
			'type'        => 'select',
			'description' => __( 'This is host ID for the meeting (Required).', $text_domain ),
			'required'    => true,
			'options'     => $host_options,
			'input_class' => [ 'vczapi-choices' ],
//			'custom_attributes' => [
//				'data-api-action'  => 'postTypeFetchHosts',
//				'data-placeholder' => __( 'Search host by name or email...', $text_domain ),
//				'data-min-search'  => '3',
//				'data-searchable'  => 'true',
//			],
		];

		if ( $has_zoom_id ) {
			$host_field_config['type']                          = 'placeholder';
			$host_field_config['custom_attributes']['disabled'] = 'disabled';
			$host_field_config['description']                   = __( 'Host cannot be changed once the event has been created.', $text_domain );
		}

		$schema = [
			'general' => [
				'title'  => __( 'General Settings', $text_domain ),
				'fields' => [
					'user_id'    => $host_field_config,
					'agenda' => [
						'label'             => __( 'Agenda', $text_domain ),
						'type'              => 'textarea',
						'description'       => __( 'Agenda for the Event.', $text_domain ),
					],
					'type'       => [
						'label'       => __( 'Type *', $text_domain ),
						'required'    => true,
						'type'        => 'select',
						'description' => __( 'Type of Event.', $text_domain ),
						'options'     => [
							1 => __( 'Meeting', $text_domain ),
							2 => __( 'Webinar', $text_domain ),
						],
					],
					'start_time' => [
						'label'             => __( 'Start Date/Time *', $text_domain ),
						'type'              => 'text',
						'description'       => __( 'Starting Date and Time of the Meeting (Required).', $text_domain ),
						'required'          => true,
						'input_class'       => [ 'vczapi-datetimepicker' ],
						'custom_attributes' => [ 'data-enable-time' => 'true' ],
					],
					'timezone'   => [
						'label'       => __( 'Timezone', $text_domain ),
						'type'        => 'select',
						'options'     => $tzlists,
						'default'     => ! empty( $tzlists[ $wp_timezone ] ) ? $wp_timezone : '',
						'input_class' => [ 'vczapi-choices' ],
					],
					'duration'   => [
						'label'  => __( 'Duration', $text_domain ),
						'type'   => 'composite',
						'fields' => [
							'hour'   => [
								'type'       => 'select',
								'options'    => range( 0, 24 ),
								'after_html' => '&nbsp;' . __( 'hours', $text_domain ),
							],
							'minute' => [
								'type'       => 'select',
								'options'    => [ 0 => 0, 10 => 10, 15 => 15, 20 => 20, 30 => 30, 40 => 40, 45 => 45 ],
								'after_html' => '&nbsp;' . __( 'minutes', $text_domain ),
							],
						],
					],
				],
			],

			'security' => [
				'title'  => __( 'Security & Room Rules', $text_domain ),
				'fields' => [
					'password'               => [
						'label'       => __( 'Password', $text_domain ),
						'type'        => 'text',
						'maxlength'   => 10,
						'description' => __( 'Password to join the meeting. Max 10 characters. (Leave blank to auto generate).', $text_domain ),
					],
					'waiting_room'           => [
						'label'       => __( 'Disable Waiting Room', $text_domain ),
						'type'        => 'checkbox',
						'description' => __( 'Anyone with the link can join without host authorization.', $text_domain ),
					],
					'meeting_authentication' => [
						'label'       => __( 'Meeting Authentication', $text_domain ),
						'type'        => 'checkbox',
						'description' => __( 'Only logged-in users in Zoom App can join this Meeting.', $text_domain ),
					],
				],
			],

			'advanced' => [
				'title'  => __( 'Advanced Settings', $text_domain ),
				'fields' => [
					'join_before_host'  => [
						'label'         => __( 'Join Before Host', $text_domain ),
						'type'          => 'checkbox',
						'description'   => __( 'Allow users to join before host starts/joins.', $text_domain ),
						'wrapper_class' => 'vczapi-admin-hide-on-webinar',
					],
					'jbh_time'          => [
						'label'         => __( 'Join Before Host Time', $text_domain ),
						'type'          => 'select',
						'options'       => [
							0  => __( 'Allow participant to join anytime.', $text_domain ),
							5  => __( 'Allow participant to join 5 minutes before start.', $text_domain ),
							10 => __( 'Allow participant to join 10 minutes before start.', $text_domain ),
							15 => __( 'Allow participant to join 15 minutes before start.', $text_domain ),
						],
						'wrapper_class' => 'vczapi-admin-hide-on-webinar',
					],
					'host_video'        => [
						'label'       => __( 'Start Host Video', $text_domain ),
						'type'        => 'checkbox',
						'description' => __( 'Start video when the host joins the meeting.', $text_domain ),
					],
					'participant_video' => [
						'label'         => __( 'Start Participant Video', $text_domain ),
						'type'          => 'checkbox',
						'description'   => __( 'Start video when participants join meeting.', $text_domain ),
						'wrapper_class' => 'vczapi-admin-hide-on-webinar',
					],
					'mute_upon_entry'   => [
						'label'         => __( 'Mute Participants upon entry', $text_domain ),
						'type'          => 'checkbox',
						'description'   => __( 'Mutes participants when entering the meeting.', $text_domain ),
						'wrapper_class' => 'vczapi-admin-hide-on-webinar',
					],
					'auto_recording'    => [
						'label'   => __( 'Auto Recording', $text_domain ),
						'type'    => 'select',
						'options' => [
							'none'  => __( 'No Recordings', $text_domain ),
							'local' => __( 'Local', $text_domain ),
							'cloud' => __( 'Cloud', $text_domain ),
						],
					],
					'alternative_hosts' => [
						'label'             => __( 'Alternative Hosts', $text_domain ),
						'type'              => 'select',
						'options'           => $host_options,
						'multiple'          => true,
						'description'       => __( 'Paid Zoom Account is required for alternative hosts.', $text_domain ),
						'input_class'       => [ 'vczapi-choices' ],
						'custom_attributes' => [ 'style' => 'width: 50%;' ],
					],
				],
			],

			'webinar' => [
				'title'  => __( 'Webinar Specific', $text_domain ),
				'fields' => [
					'panelists_video'        => [
						'label'         => __( 'When Panelists Join', $text_domain ),
						'type'          => 'checkbox',
						'description'   => __( 'Start video when panelists join webinar.', $text_domain ),
						'wrapper_class' => 'vczapi-admin-show-on-webinar',
					],
					'practice_session'       => [
						'label'         => __( 'Practice Session', $text_domain ),
						'type'          => 'checkbox',
						'description'   => __( 'Enable Practice Session.', $text_domain ),
						'wrapper_class' => 'vczapi-admin-show-on-webinar',
					],
					'hd_video'               => [
						'label'         => __( 'HD Video', $text_domain ),
						'type'          => 'checkbox',
						'description'   => __( 'Defaults to HD video.', $text_domain ),
						'wrapper_class' => 'vczapi-admin-show-on-webinar',
					],
					'allow_multiple_devices' => [
						'label'         => __( 'Allow Multiple Devices', $text_domain ),
						'type'          => 'checkbox',
						'description'   => __( 'Allow attendees to join from multiple devices.', $text_domain ),
						'wrapper_class' => 'vczapi-admin-show-on-webinar',
					],
				],
			],
		];

		if ( $has_zoom_id ) {
			$schema['general']['fields']['type']['type'] = 'placeholder';
		}

		return apply_filters( 'vczapi_admin_metabox_fields_schema', $schema, $post, $meeting_details );
	}
}