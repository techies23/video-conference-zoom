<?php

/**
 * Meeting Post Type Controller
 *
 * @since      3.0.0
 * @author     Deepen.
 * @created_on 11/18/19
 */
class Zoom_Video_Conferencing_Admin_PostType {

	/**
	 * Instance
	 *
	 * @var null
	 */
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
	 * Post Type Flag
	 *
	 * @var string
	 */
	private $post_type = 'zoom-meetings';

	/**
	 * Zoom_Video_Conferencing_Admin_PostType constructor.
	 */
	public function __construct() {
		add_action( 'restrict_manage_posts', [ $this, 'filtering' ], 10 );
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'admin_menu', [ $this, 'hide_post_type' ] );
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post_' . $this->post_type, array( $this, 'save_metabox' ), 10, 2 );
		add_filter( 'single_template', array( $this, 'single' ), 20 );
		add_filter( 'archive_template', array( $this, 'archive' ), 20 );
		add_filter( 'template_include', [ $this, 'template_filter' ], 99 );
		add_action( 'before_delete_post', array( $this, 'delete' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'add_columns' ), 20 );
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'column_data' ), 20, 2 );
		add_action( 'manage_edit-' . $this->post_type . '_sortable_columns', array( $this, 'sortable_data' ), 30 );
		add_filter( 'views_edit-' . $this->post_type, [ $this, 'addFiltersOnSubSubSub' ] );
		add_filter( 'pre_get_posts', [ $this, 'filter_posts' ] );

		//use classic editor instead of gutenberg
		add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_block_editor' ], 10, 2 );
	}

	public function disable_block_editor( $enabled, $post_type ) {
		if ( $post_type == $this->post_type ) {
			$enabled = apply_filters( 'vczapi_enable_block_editor', false );
		}

		return $enabled;
	}

	/**
	 * Filter posts based on query strings
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	public function filter_posts( $query ) {
		global $pagenow;

		if ( 'edit.php' != $pagenow || ! $query->is_admin || ( ! empty( $query->query['post_type'] ) && $query->query['post_type'] != $this->post_type ) ) {
			return $query;
		}

		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === $this->post_type && $query->query['post_type'] === $this->post_type ) {
			$type = isset( $_GET['type'] ) ? $_GET['type'] : false;
			$now  = vczapi_dateConverter( 'now', 'UTC', 'Y-m-d H:i:s', false );
			if ( $type === "upcoming" ) {
				$meta_query = [
					[
						'key'     => '_meeting_field_start_date_utc',
						'value'   => $now,
						'compare' => '>=',
						'type'    => 'DATETIME',
					],
				];

				$query->set( 'meta_query', $meta_query );
			} elseif ( $type === "past" ) {
				$meta_query = [
					[
						'key'     => '_meeting_field_start_date_utc',
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

	/**
	 * Add Filters on SUB SUB SUB column
	 *
	 * @param $views
	 *
	 * @return mixed
	 */
	public function addFiltersOnSubSubSub( $views ) {
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] !== "zoom-meetings" ) {
			return $views;
		}

		$upcoming          = isset( $_GET['type'] ) && $_GET['type'] === "upcoming" ? 'class="current"' : '';
		$past              = isset( $_GET['type'] ) && $_GET['type'] === "past" ? 'class="current"' : '';
		$views['upcoming'] = sprintf( '<a href="%s" ' . $upcoming . '>' . __( "Upcoming", "video-conferencing-with-zoom-api" ) . '</a>', admin_url( '/edit.php?post_type=zoom-meetings&type=upcoming' ) );
		$views['past']     = sprintf( '<a href="%s" ' . $past . '>' . __( "Past", "video-conferencing-with-zoom-api" ) . '</a>', admin_url( '/edit.php?post_type=zoom-meetings&type=past' ) );

		return $views;
	}

	/**
	 * Hide Post Type page
	 */
	public function hide_post_type() {
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] !== $this->post_type ) {
			return;
		}

		if ( ! vczapi_is_zoom_activated() ) {
			global $submenu;
//			unset( $submenu['edit.php?post_type=zoom-meetings'][5] );
			unset( $submenu['edit.php?post_type=zoom-meetings'][10] );
			unset( $submenu['edit.php?post_type=zoom-meetings'][15] );
		}
	}

	/**
	 * Filters
	 *
	 * @param $post_type
	 */
	public function filtering( $post_type ) {
		if ( $this->post_type !== $post_type ) {
			return;
		}

		$taxnomy  = 'zoom-meeting';
		$taxonomy = get_taxonomy( $taxnomy );
		$selected = isset( $_REQUEST[ $taxnomy ] ) ? $_REQUEST[ $taxnomy ] : '';
		wp_dropdown_categories( array(
			'show_option_all' => $taxonomy->labels->all_items,
			'taxonomy'        => $taxnomy,
			'name'            => $taxnomy,
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
	public function add_columns( $columns ) {
		$columns['type']          = __( 'Type', 'video-conferencing-with-zoom-api' );
		$columns['start_meeting'] = __( 'Start Meeting', 'video-conferencing-with-zoom-api' );
		$columns['start_date']    = __( 'Start Date', 'video-conferencing-with-zoom-api' );
		$columns['meeting_id']    = __( 'Meeting ID', 'video-conferencing-with-zoom-api' );
		$columns['meeting_state'] = __( 'Meeting State', 'video-conferencing-with-zoom-api' );
		unset( $columns['author'] );

		return $columns;
	}

	/**
	 * Sortable Data Column
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function sortable_data( $columns ) {
		$columns['start_date'] = 'zoom_meeting_startdate';

//		$columns['meeting_id'] = 'zoom_meeting_id';

		return $columns;
	}

	/**
	 * Render HTML
	 *
	 * @param $column
	 * @param $post_id
	 */
	public function column_data( $column, $post_id ) {
		$meeting = get_post_meta( $post_id, '_meeting_zoom_details', true );
		switch ( $column ) {
			case 'type':
				if ( ! empty( $meeting ) && ! empty( $meeting->type ) && ( $meeting->type === 5 || $meeting->type === 6 || $meeting->type === 9 ) ) {
					_e( 'Webinar', 'video-conferencing-with-zoom-api' );
				} else {
					_e( 'Meeting', 'video-conferencing-with-zoom-api' );
				}
				break;
			case 'start_meeting' :
				if ( ! empty( $meeting ) && ! empty( $meeting->start_url ) ) {
					echo '<a href="' . esc_url( $meeting->start_url ) . '" target="_blank">Start</a>';
				} else {
					_e( 'Meeting not created yet.', 'video-conferencing-with-zoom-api' );
				}
				break;
			case 'start_date' :
				if ( ! empty( $meeting ) && ! empty( $meeting->code ) && ! empty( $meeting->message ) ) {
					echo $meeting->message;
				} elseif ( ! empty( $meeting ) && ! empty( $meeting->type ) && ( $meeting->type === 2 || $meeting->type === 5 ) && ! empty( $meeting->start_time ) ) {
					echo vczapi_dateConverter( $meeting->start_time, $meeting->timezone, 'F j, Y, g:i a' );
				} elseif ( ! empty( $meeting ) && vczapi_pro_check_type( $meeting->type ) ) {
					_e( 'Recurring Meeting', 'video-conferencing-with-zoom-api' );
				} else {
					_e( 'Meeting not created yet.', 'video-conferencing-with-zoom-api' );
				}
				break;
			case 'meeting_id' :
				if ( ! empty( $meeting ) && ! empty( $meeting->code ) && ! empty( $meeting->message ) ) {
					echo $meeting->message;
				} elseif ( ! empty( $meeting ) && ! empty( $meeting->id ) ) {
					echo $meeting->id;
				} else {
					_e( 'Meeting not created yet.', 'video-conferencing-with-zoom-api' );
				}
				break;
			case 'meeting_state' :
				wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );
				if ( ! empty( $meeting ) ) {
					if ( ! empty( $meeting->code ) && ! empty( $meeting->message ) ) {
						echo $meeting->message;
					} elseif ( empty( $meeting->state ) ) { ?>
                        <a href="javascript:void(0);" class="vczapi-meeting-state-change" data-type="post_type"
                           data-state="end" data-postid="<?php echo $post_id; ?>"
                           data-id="<?php echo $meeting->id ?>"><?php _e( 'Disable Join', 'video-conferencing-with-zoom-api' ); ?></a>
                        <p class="description"><?php _e( 'Restrict users to join this meeting before the start time or after the meeting is completed.', 'video-conferencing-with-zoom-api' ); ?></p>
					<?php } else { ?>
                        <a href="javascript:void(0);" class="vczapi-meeting-state-change" data-type="post_type"
                           data-state="resume" data-postid="<?php echo $post_id; ?>"
                           data-id="<?php echo $meeting->id ?>"><?php _e( 'Enable Join', 'video-conferencing-with-zoom-api' ); ?></a>
                        <p class="description"><?php _e( 'Resuming this will enable users to join this meeting.', 'video-conferencing-with-zoom-api' ); ?></p>
					<?php }

					//If there isnt an error doesn't matter if meeting is disabled or enabled we can end meeting
					if ( empty( $meeting->code ) ) {
						?>
                        <a href="javascript:void(0);"
                           class="vczapi-meeting-state-end_meeting"
                           data-id="<?php echo $meeting->id; ?>"
                        ><?php _e( 'End Meeting', 'video-conferencing-with-zoom-api' ); ?></a>
                        <p class="description"><?php _e( 'This will end the on-going meeting', 'video-conferencing-with-zoom-api' ); ?></p>
						<?php
					}

				} else {
					_e( 'Meeting not created yet.', 'video-conferencing-with-zoom-api' );
				}
				break;
		}
	}

	/**
	 * Register Post Type
	 *
	 * @since  3.0.0
	 * @author Deepen
	 */
	public function register() {
		$this->register_post_type();
		$this->register_taxonomy();
	}

	/**
	 * Register Taxonomy
	 */
	public function register_taxonomy() {
		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name' => _x( 'Category', 'Zoom Category Name', 'video-conferencing-with-zoom-api' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rest_base'         => 'zoom_meeting_cats',
			'query_var'         => true,
		);

		register_taxonomy( 'zoom-meeting', array( $this->post_type ), $args );
	}

	/**
	 * Register Post Type
	 */
	public function register_post_type() {
		$labels = apply_filters( 'vczapi_admin_cpt_labels', array(
			'name'               => _x( 'Zoom Meetings and Webinars', 'Zoom Meetings and Webinars', 'video-conferencing-with-zoom-api' ),
			'singular_name'      => _x( 'Zoom Events', 'Zoom Events', 'video-conferencing-with-zoom-api' ),
			'menu_name'          => _x( 'Zoom Events', 'Zoom Events', 'video-conferencing-with-zoom-api' ),
			'name_admin_bar'     => _x( 'Zoom Events', 'Zoom Events', 'video-conferencing-with-zoom-api' ),
			'add_new'            => __( 'Add New', 'video-conferencing-with-zoom-api' ),
			'add_new_item'       => __( 'Add New Event', 'video-conferencing-with-zoom-api' ),
			'new_item'           => __( 'New Zoom Event', 'video-conferencing-with-zoom-api' ),
			'edit_item'          => __( 'Edit Zoom Event', 'video-conferencing-with-zoom-api' ),
			'view_item'          => __( 'View meetings', 'video-conferencing-with-zoom-api' ),
			'all_items'          => __( 'All Events', 'video-conferencing-with-zoom-api' ),
			'search_items'       => __( 'Search meetings', 'video-conferencing-with-zoom-api' ),
			'parent_item_colon'  => __( 'Parent meetings:', 'video-conferencing-with-zoom-api' ),
			'not_found'          => __( 'No zoom events found.', 'video-conferencing-with-zoom-api' ),
			'not_found_in_trash' => __( 'No zoom events found in Trash.', 'video-conferencing-with-zoom-api' ),
		) );

		$settings = get_option( '_vczapi_zoom_settings' );
		$settings = ! empty( $settings ) ? $settings : false;

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'menu_icon'          => 'dashicons-video-alt2',
			'capability_type'    => apply_filters( 'vczapi_cpt_capabilities_type', 'post' ),
			'capabilities'       => apply_filters( 'vczapi_cpt_capabilities', array() ),
			'has_archive'        => true,
			'hierarchical'       => apply_filters( 'vczapi_cpt_hierarchical', false ),
			'show_in_rest'       => apply_filters( 'vczapi_cpt_show_in_rest', true ),
			'rest_base'          => 'zoom_meetings',
			'menu_position'      => apply_filters( 'vczapi_cpt_menu_position', 5 ),
			'map_meta_cap'       => apply_filters( 'vczapi_cpt_meta_cap', null ),
			'supports'           => array(
				'title',
				'editor',
				'author',
				'thumbnail',
			),
			'rewrite'            => array( 'slug' => apply_filters( 'vczapi_cpt_slug', $this->post_type ) ),
		);

		register_post_type( $this->post_type, $args );
	}

	/**
	 * Adds the meta box.
	 */
	public function add_metabox() {
		add_meta_box( 'zoom-meeting-meta', __( 'Zoom Details', 'video-conferencing-with-zoom-api' ), array(
			$this,
			'render_metabox',
		), $this->post_type, 'normal' );
		add_meta_box( 'zoom-meeting-meta-side', __( 'Meeting Details', 'video-conferencing-with-zoom-api' ), array(
			$this,
			'rendor_sidebox',
		), $this->post_type, 'side', 'high' );
		add_meta_box( 'zoom-meeting-debug-meta', __( 'Debug Log', 'video-conferencing-with-zoom-api' ), array(
			$this,
			'debug_metabox',
		), $this->post_type, 'normal' );
		if ( is_plugin_inactive( 'vczapi-woo-addon/vczapi-woo-addon.php' ) && is_plugin_inactive( 'vczapi-woocommerce-addon/vczapi-woocommerce-addon.php' ) ) {
			add_meta_box( 'zoom-meeting-woo-integration-info', __( 'WooCommerce Integration?', 'video-conferencing-with-zoom-api' ), array(
				$this,
				'render_woo_sidebox',
			), $this->post_type, 'side', 'normal' );
		}
	}

	public function render_woo_sidebox() {
		echo "<p>Enable this meeting to be purchased by your users ? </p><p>Check out <a href='" . admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-addons' ) . "'>WooCommerce addon</a> for this plugin.</p>";
	}

	/**
	 * Renders the meta box.
	 */
	public function render_metabox( $post ) {
		// Add nonce for security and authentication.
		wp_nonce_field( '_zvc_meeting_save', '_zvc_nonce' );

		wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-select2-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-timepicker-js' );

		//Check if any transient by name is available
		$users = video_conferencing_zoom_api_get_user_transients();

		$meeting_fields = get_post_meta( $post->ID, '_meeting_fields', true );

		do_action( 'vczapi_before_fields_admin', $post );

		//Get Template
		require_once ZVC_PLUGIN_VIEWS_PATH . '/post-type/tpl-meeting-fields.php';
	}

	/**
	 * Rendor SideBox field
	 *
	 * @param $post
	 */
	function rendor_sidebox( $post ) {
		$meeting_fields = get_post_meta( $post->ID, '_meeting_fields', true );
		// Add nonce for security and authentication.
		wp_nonce_field( '_zvc_meeting_save', '_zvc_nonce' );
		$meeting_details = get_post_meta( $post->ID, '_meeting_zoom_details', true );
		?>
        <div class="zoom-metabox-wrapper">
			<?php
			if ( ! empty( $meeting_details ) ) {
				if ( ! empty( $meeting_details->code ) && ! empty( $meeting_details->message ) ) {
					?>
                    <p>
                        <strong><?php _e( 'Meeting has not been created for this post yet. Publish your meeting or hit update to create a new one for this post !', 'video-conferencing-with-zoom-api' ) ?></strong>
                    </p>
					<?php
					echo '<p style="color:red;font-size:18px;"><strong>Zoom Error:</strong> ' . $meeting_details->message . '</p>';
				} else {
					$zoom_host_url = 'https://zoom.us' . '/wc/' . $meeting_details->id . '/start';
					$zoom_host_url = apply_filters( 'video_conferencing_zoom_join_url_host', $zoom_host_url );

					$join_url = ! empty( $meeting_details->encrypted_password ) ? vczapi_get_pwd_embedded_join_link( $meeting_details->join_url, $meeting_details->encrypted_password ) : $meeting_details->join_url;
					?>
                    <div class="zoom-metabox-content">
                        <p><a target="_blank" href="<?php echo esc_url( $meeting_details->start_url ); ?>"
                              title="Start URL"><?php _e( 'Start Meeting', 'video-conferencing-with-zoom-api' ) ?></a>
                        </p>
                        <p><a target="_blank" href="<?php echo esc_url( $join_url ); ?>"
                              title="Start URL"><?php _e( 'Join Meeting', 'video-conferencing-with-zoom-api' ) ?></a>
                        </p>
                        <p><a target="_blank" href="<?php echo esc_url( $zoom_host_url ); ?>"
                              title="Start URL"><?php _e( 'Start via Browser', 'video-conferencing-with-zoom-api' ) ?></a>
                        </p>
                        <p><strong><?php _e( 'Meeting ID', 'video-conferencing-with-zoom-api' ) ?>
                                :</strong> <?php echo $meeting_details->id; ?></p>
						<?php do_action( 'vczapi_meeting_details_admin', $meeting_details ); ?>
                    </div>
                    <hr>
					<?php
				}
			} else { ?>
                <p>
                    <strong><?php _e( 'Meeting has not been created for this post yet. Publish your meeting or hit update to create a new one for this post !', 'video-conferencing-with-zoom-api' ); ?></strong>
                </p>
			<?php } ?>
            <div class="zoom-metabox-content">
                <p><?php _e( 'Requires Login?', 'video-conferencing-with-zoom-api' ); ?>
                    <input type="checkbox" name="option_logged_in"
                           value="1" <?php ! empty( $meeting_fields['site_option_logged_in'] ) ? checked( '1', $meeting_fields['site_option_logged_in'] ) : false; ?>
                           class="regular-text">
                </p>
                <p class="description"><?php _e( 'Only logged in users of this site will be able to join this meeting.', 'video-conferencing-with-zoom-api' ); ?></p>
                <p><?php _e( 'Hide Join via browser link ?', 'video-conferencing-with-zoom-api' ); ?>
                    <input type="checkbox" name="option_browser_join"
                           value="1" <?php ! empty( $meeting_fields['site_option_browser_join'] ) ? checked( '1', $meeting_fields['site_option_browser_join'] ) : false; ?>
                           class="regular-text">
                </p>
                <p class="description"><?php _e( 'This will disable join via browser link in frontend page.', 'video-conferencing-with-zoom-api' ); ?></p>
            </div>
        </div>
		<?php
	}

	/**
	 * Debug FUNCTION
	 *
	 * @param $post
	 */
	public function debug_metabox( $post ) {
		$meeting_fields  = get_post_meta( $post->ID, '_meeting_fields', true );
		$meeting_details = get_post_meta( $post->ID, '_meeting_zoom_details', true );
		?>
        <div class="zoom-metabox-wrapper">
            <div class="zoom-metabox-content">
                <p><?php _e( 'Enable Debug?', 'video-conferencing-with-zoom-api' ); ?>
                    <input type="checkbox" name="option_enable_debug_logs"
                           value="1" <?php ! empty( $meeting_fields['site_option_enable_debug_log'] ) ? checked( '1', $meeting_fields['site_option_enable_debug_log'] ) : false; ?>
                           class="regular-text">
                </p>
            </div>
        </div>
        <style>
            pre {
                position: relative;
                width: 100%;
                padding: 0;
                margin: 0;
                overflow: auto;
                overflow-y: hidden;
                font-size: 12px;
                line-height: 20px;
                background: #efefef;
                border: 1px solid #777;
            }
        </style>
		<?php
		if ( ! empty( $meeting_fields['site_option_enable_debug_log'] ) ) {
			if ( ! empty( $meeting_details->id ) ) {
				if ( ! empty( $meeting_fields['meeting_type'] ) && $meeting_fields['meeting_type'] === 2 ) {
					dump( json_decode( zoom_conference()->getWebinarInfo( $meeting_details->id ) ) );
				} else {
					dump( json_decode( zoom_conference()->getMeetingInfo( $meeting_details->id ) ) );
				}
			} else {
				dump( $meeting_details );
			}
		}
	}

	/**
	 * Handles saving the meta box.
	 *
	 * @param  int  $post_id  Post ID.
	 * @param  \WP_Post  $post  Post object.
	 */
	public function save_metabox( $post_id, $post ) {
		// Add nonce for security and authentication.
		$nonce_name   = isset( $_POST['_zvc_nonce'] ) ? $_POST['_zvc_nonce'] : '';
		$nonce_action = '_zvc_meeting_save';

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$pwd = sanitize_text_field( filter_input( INPUT_POST, 'password' ) );
		if ( ! get_option( 'zoom_api_disable_auto_meeting_pwd' ) ) {
			$pwd = ! empty( $pwd ) ? $pwd : $post_id;
		}
		$duration_hour      = sanitize_text_field( filter_input( INPUT_POST, 'option_duration_hour' ) );
		$duration_minutes   = sanitize_text_field( filter_input( INPUT_POST, 'option_duration_minutes' ) );
		$duration           = ! empty( $duration_hour ) || ! empty( $duration_minutes ) ? vczapi_convert_to_minutes( $duration_hour, $duration_minutes ) : 40;
		$create_meeting_arr = array(
			'userId'                 => sanitize_text_field( filter_input( INPUT_POST, 'userId' ) ),
			'meeting_type'           => absint( sanitize_text_field( filter_input( INPUT_POST, 'meeting_type' ) ) ),
			'start_date'             => sanitize_text_field( filter_input( INPUT_POST, 'start_date' ) ),
			'timezone'               => sanitize_text_field( filter_input( INPUT_POST, 'timezone' ) ),
			'duration'               => $duration,
			'password'               => $pwd,
			'disable_waiting_room'   => filter_input( INPUT_POST, 'disable-waiting-room' ),
			'meeting_authentication' => filter_input( INPUT_POST, 'meeting_authentication' ),
			'option_host_video'      => filter_input( INPUT_POST, 'option_host_video' ),
			'option_auto_recording'  => filter_input( INPUT_POST, 'option_auto_recording' ),
			'alternative_host_ids'   => filter_input( INPUT_POST, 'alternative_host_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ),
		);

		//If Webinar
		if ( ! empty( $create_meeting_arr['meeting_type'] ) && $create_meeting_arr['meeting_type'] === 2 ) {
			$create_meeting_arr['panelists_video']        = filter_input( INPUT_POST, 'panelists_video' );
			$create_meeting_arr['practice_session']       = filter_input( INPUT_POST, 'practice_session' );
			$create_meeting_arr['hd_video']               = filter_input( INPUT_POST, 'hd_video' );
			$create_meeting_arr['allow_multiple_devices'] = filter_input( INPUT_POST, 'allow_multiple_devices' );
		} else {
			$create_meeting_arr['join_before_host']          = filter_input( INPUT_POST, 'join_before_host' );
			$create_meeting_arr['option_participants_video'] = filter_input( INPUT_POST, 'option_participants_video' );
			$create_meeting_arr['option_mute_participants']  = filter_input( INPUT_POST, 'option_mute_participants' );
		}

		$create_meeting_arr['site_option_logged_in']        = filter_input( INPUT_POST, 'option_logged_in' );
		$create_meeting_arr['site_option_browser_join']     = filter_input( INPUT_POST, 'option_browser_join' );
		$create_meeting_arr['site_option_enable_debug_log'] = filter_input( INPUT_POST, 'option_enable_debug_logs' );

		//Call before meeting is created.
		do_action( 'vczapi_admin_before_zoom_meeting_is_created', $create_meeting_arr );

		//Update Post Meta Values
		update_post_meta( $post_id, '_meeting_fields', $create_meeting_arr );
		$meeting_type = ! empty( $create_meeting_arr['meeting_type'] ) && $create_meeting_arr['meeting_type'] === 2 ? 'webinar' : 'meeting';
		update_post_meta( $post_id, '_vczapi_meeting_type', $meeting_type );

		try {
			//converted saved time from the timezone provided for meeting to UTC timezone so meetings can be better queried
			$savedDateTime     = new DateTime( $create_meeting_arr['start_date'], new DateTimeZone( $create_meeting_arr['timezone'] ) );
			$startDateTimezone = $savedDateTime->setTimezone( new DateTimeZone( 'UTC' ) );
			update_post_meta( $post_id, '_meeting_field_start_date_utc', $startDateTimezone->format( 'Y-m-d H:i:s' ) );
		} catch ( Exception $e ) {
			update_post_meta( $post_id, '_meeting_field_start_date_utc', $e->getMessage() );
		}

		$create_meeting_arr = apply_filters( 'vczapi_admin_meeting_fields', $create_meeting_arr );

		//Create Zoom Meeting Now
		$meeting_id = get_post_meta( $post_id, '_meeting_zoom_meeting_id', true );
		if ( empty( $meeting_id ) ) {
			//Create new Zoom Meeting
			$this->create_zoom_meeting( $post, $create_meeting_arr );
		} else {
			//Update Zoom Meeting
			$this->update_zoom_meeting( $post, $create_meeting_arr, $meeting_id );
		}

		//Call this action after the Zoom Meeting completion created.
		do_action( 'vczapi_admin_after_zoom_meeting_is_created', $post_id, $post );
	}

	/**
	 * Create real time zoom meetings
	 *
	 * @param $post
	 * @param $create_meeting_arr
	 *
	 * @since    3.0.0
	 * @modified 3.5.3
	 *
	 * @author   Deepen
	 */
	private function create_zoom_meeting( $post, $create_meeting_arr ) {
		//Prepare Webinar Insert Data
		if ( ! empty( $create_meeting_arr['meeting_type'] ) && $create_meeting_arr['meeting_type'] === 2 ) {
			$webinar_arrr    = Zoom_Video_Conferencing_Admin_Webinars::prepare_webinar( $create_meeting_arr, $post );
			$webinar_created = json_decode( zoom_conference()->createAWebinar( $create_meeting_arr['userId'], $webinar_arrr ) );
			if ( empty( $webinar_created->code ) ) {
				update_post_meta( $post->ID, '_meeting_zoom_details', $webinar_created );
				update_post_meta( $post->ID, '_meeting_zoom_join_url', $webinar_created->join_url );
				update_post_meta( $post->ID, '_meeting_zoom_start_url', $webinar_created->start_url );
				update_post_meta( $post->ID, '_meeting_zoom_meeting_id', $webinar_created->id );
			} else {
				//Store Error Message
				update_post_meta( $post->ID, '_meeting_zoom_details', $webinar_created );
			}
		} else {
			$mtg_param       = Zoom_Video_Conferencing_Admin_Meetings::prepare_create( $create_meeting_arr, $post );
			$meeting_created = json_decode( zoom_conference()->createAMeeting( $mtg_param ) );
			if ( empty( $meeting_created->code ) ) {
				update_post_meta( $post->ID, '_meeting_zoom_details', $meeting_created );
				update_post_meta( $post->ID, '_meeting_zoom_join_url', $meeting_created->join_url );
				update_post_meta( $post->ID, '_meeting_zoom_start_url', $meeting_created->start_url );
				update_post_meta( $post->ID, '_meeting_zoom_meeting_id', $meeting_created->id );
			} else {
				//Store Error Message
				update_post_meta( $post->ID, '_meeting_zoom_details', $meeting_created );
			}
		}

	}

	/**
	 * Update real time zoom meetings
	 *
	 * @param $post
	 * @param $updated_meeting_arr
	 * @param $meeting_id
	 *
	 * @author   Deepen
	 * @since    3.0.0
	 * @modified 3.5.3
	 */
	private function update_zoom_meeting( $post, $updated_meeting_arr, $meeting_id ) {
		if ( ! empty( $updated_meeting_arr['meeting_type'] ) && $updated_meeting_arr['meeting_type'] === 2 ) {
			//Prepare Webinar update data
			$webinar_arrr    = Zoom_Video_Conferencing_Admin_Webinars::prepare_webinar( $updated_meeting_arr, $post );
			$webinar_updated = json_decode( zoom_conference()->updateWebinar( $meeting_id, $webinar_arrr ) );
			if ( empty( $webinar_updated->code ) ) {
				$webinar_info = json_decode( zoom_conference()->getWebinarInfo( $meeting_id ) );
				if ( ! empty( $webinar_info ) ) {
					update_post_meta( $post->ID, '_meeting_zoom_details', $webinar_info );
					update_post_meta( $post->ID, '_meeting_zoom_join_url', $webinar_info->join_url );
					update_post_meta( $post->ID, '_meeting_zoom_start_url', $webinar_info->start_url );
					update_post_meta( $post->ID, '_meeting_zoom_meeting_id', $webinar_info->id );
				}
			} else {
				update_post_meta( $post->ID, '_meeting_zoom_details', $webinar_updated );
			}
		} else {
			$mtg_param       = Zoom_Video_Conferencing_Admin_Meetings::prepare_update( $meeting_id, $updated_meeting_arr, $post );
			$meeting_updated = json_decode( zoom_conference()->updateMeetingInfo( $mtg_param ) );
			if ( empty( $meeting_updated->code ) ) {
				$meeting_info = json_decode( zoom_conference()->getMeetingInfo( $meeting_id ) );
				if ( ! empty( $meeting_info ) ) {
					update_post_meta( $post->ID, '_meeting_zoom_details', $meeting_info );
					update_post_meta( $post->ID, '_meeting_zoom_join_url', $meeting_info->join_url );
					update_post_meta( $post->ID, '_meeting_zoom_start_url', $meeting_info->start_url );
					update_post_meta( $post->ID, '_meeting_zoom_meeting_id', $meeting_info->id );
				}
			} else {
				//Store Error Message
				update_post_meta( $post->ID, '_meeting_zoom_details', $meeting_updated );
			}
		}
	}

	/**
	 * Single Page Template
	 *
	 * @param $template
	 *
	 * @return string
	 * @since  3.0.0
	 *
	 * @author Deepen
	 */
	public function single( $template ): string {
		global $post;

		if ( ! empty( $post ) && $post->post_type == $this->post_type ) {
			$template = vczapi_get_single_or_zoom_template( $post, $template );
		}

		//Call before single template file is loaded
		do_action( 'vczapi_before_single_template_load' );

		return $template;
	}

	/**
	 * Archive page template
	 *
	 * @param $template
	 *
	 * @return bool|string
	 * @return bool|string|void
	 * @since  3.0.0
	 *
	 * @author Deepen
	 */
	public function archive( $template ) {
		if ( ! is_post_type_archive( $this->post_type ) ) {
			return $template;
		}

		if ( isset( $_GET['type'] ) && $_GET['type'] === "meeting" && isset( $_GET['join'] ) ) {
			$template = vczapi_get_template( 'join-web-browser.php' );
		} else {
			$template = vczapi_get_template( 'archive-meetings.php' );
		}

		return $template;
	}

	/**
	 * Delete the meeting
	 *
	 * @param $post_id
	 *
	 * @since  3.0.0
	 *
	 * @author Deepen
	 */
	public function delete( $post_id ) {
		$donot_delete_zoom = get_option( 'zoom_api_donot_delete_on_zoom' );
		if ( ! empty( $donot_delete_zoom ) ) {
			return;
		}

		if ( get_post_type( $post_id ) === $this->post_type ) {
			$meeting_details = get_post_meta( $post_id, '_meeting_fields', true );
			$meeting_id      = get_post_meta( $post_id, '_meeting_zoom_meeting_id', true );
			if ( ! empty( $meeting_id ) ) {
				do_action( 'vczapi_before_delete_meeting', $meeting_id );

				if ( ! empty( $meeting_details ) && $meeting_details['meeting_type'] === 2 ) {
					zoom_conference()->deleteAWebinar( $meeting_id );
				} else {
					zoom_conference()->deleteAMeeting( $meeting_id );
				}

				do_action( 'vczapi_after_delete_meeting' );
			}
		}
	}

	public function admin_notices() {
		$screen = get_current_screen();

		//If not on the screen with ID 'edit-post' abort.
		if ( $screen->id === 'edit-zoom-meetings' || $screen->id === $this->post_type ) {
			if ( !vczapi_is_zoom_activated() ) {
				?>
                <div id="message" class="notice notice-error is-dismissible">
                    <h3><?php esc_html_e( 'Zoom API Connection is not established yet.', 'video-conferencing-with-zoom-api' ); ?></h3>
                    <p>
						<?php
						printf( esc_html__( 'Add your Zoom API keys and check if your Zoom Account is properly connected or not by going to %s page and click on "Check Connection" button. You can follow this %s to setup your API keys.' ), '<a href="' . admin_url( '/edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' ) . '">settings</a>', '<a target="_blank" href="https://zoomdocs.codemanas.com/setup/">guide</a>' );
						?>
                    </p>
                </div>
				<?php
			}
			video_conferencing_zoom_api_show_like_popup();
		}
	}

	/**
	 * Change Filter Name to Override Page Builders overridng join via browser window.
	 *
	 * @param $template_name
	 *
	 * @return string
	 */
	public function template_filter( $template_name ) {
		if ( is_post_type_archive( $this->post_type ) && isset( $_GET['type'] ) && $_GET['type'] === "meeting" && isset( $_GET['join'] ) ) {
			$template_name = vczapi_get_template( 'join-web-browser.php' );
		}

		return $template_name;
	}
}

Zoom_Video_Conferencing_Admin_PostType::get_instance();