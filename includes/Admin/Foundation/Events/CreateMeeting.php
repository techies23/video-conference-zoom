<?php

namespace Codemanas\VczApi\Admin\Foundation\Events;

use Codemanas\VczApi\Admin\Foundation\Utils\MeetingFormHandler;
use Codemanas\VczApi\Admin\Foundation\Utils\MeetingValidator;
use Codemanas\VczApi\Admin\Schema\MeetingFieldSchema;
use Codemanas\VczApi\Admin\Service\PostTypeSyncService;
use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Common;
use Codemanas\VczApi\Helpers\Config;
use Codemanas\VczApi\Helpers\Templates;
use WP_Post;

class CreateMeeting {

	public const MENU_SLUG = 'zoom-video-conferencing-add-meeting';
	private static ?self $instance = null;

	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	public function __construct() {
		add_action( 'current_screen', [ $this, 'redirectAddNewToAddMeetingPage' ] );
		add_action( 'wp_ajax_vczapi_create_meeting_event', [ $this, 'createMeetingEvent' ] );
	}

	/**
	 * Redirect the post type add new page to Create new page.
	 *
	 * @return void
	 */
	public function redirectAddNewToAddMeetingPage(): void {
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== Config::get( 'post_type' ) || $screen->action !== 'add' || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		wp_safe_redirect( add_query_arg( [
			'post_type' => Config::get( 'post_type' ),
			'page'      => self::MENU_SLUG,
		], admin_url( 'edit.php' ) ) );
		exit;
	}

	/**
	 * Render the form fields
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to create meetings.', 'video-conferencing-with-zoom-api' ), '', [ 'response' => 403 ] );
		}

		wp_enqueue_script( 'vczapi-script' );
		wp_enqueue_script( 'vczapi-vendors-js' );
		wp_enqueue_style( 'vczapi-choices' );
		wp_enqueue_style( 'vczapi-flatpickr' );
		wp_enqueue_style( 'vczapi-admin' );

		$empty_post = new WP_Post( (object) [
			'ID'          => 0,
			'post_status' => 'draft',
			'post_type'   => Config::get( 'post_type' ),
		] );

		$field_sections                                                                         = MeetingFieldSchema::getMeetingFieldsSchema( $empty_post, [], Common::getDefaultHostList() );
		$field_sections['general']['fields']['user_id']['custom_attributes']['data-api-action'] = 'vczapi_get_zoom_hosts';

		Templates::includeFile( VCZAPI_PLUGIN_ADMIN_VIEWS_PATH . '/post-type/events/create-meeting.php', [
			'post'            => $empty_post,
			'meeting_details' => [],
			'meeting_fields'  => [],
			'field_sections'  => $field_sections,
		] );
	}

	/**
	 * Handle meeting creation process from here.
	 *
	 * @return void
	 */
	public function createMeetingEvent(): void {
		check_ajax_referer( '_nonce_vczapi_security', 'security' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'You do not have permission to do this.', 'video-conferencing-with-zoom-api' ) ], 403 );
		}

		$title  = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$fields = MeetingFormHandler::gatherFromPost();

		$errors = MeetingValidator::validate( $fields );
		if ( empty( $title ) ) {
			$errors['title'] = __( 'Meeting title is required.', 'video-conferencing-with-zoom-api' );
		}

		if ( ! empty( $errors ) ) {
			wp_send_json_error( [
				'message' => __( 'Please fix the highlighted fields and try again.', 'video-conferencing-with-zoom-api' ),
				'errors'  => $errors,
			], 400 );
		}

		$post_id = wp_insert_post( [
			'post_type'   => Config::get( 'post_type' ),
			'post_status' => 'draft',
			'post_title'  => $title,
			'post_author' => get_current_user_id(),
		], true );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( [ 'message' => $post_id->get_error_message() ], 500 );
		}

		$post         = get_post( $post_id );
		$meeting_type = (int) ( $fields['type'] ?? 1 );

		( new PostTypeSyncService() )->sync( $post_id, $post, $fields, $meeting_type );

		$meeting_created = ! empty( Metastore::getPostMeta( $post_id, 'meeting_id' ) );

		wp_send_json_success( [
			'post_id'         => (int) $post_id,
			'edit_url'        => admin_url( 'post.php?post=' . (int) $post_id . '&action=edit' ),
			'meeting_created' => $meeting_created,
		] );
	}
}