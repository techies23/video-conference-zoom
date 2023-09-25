<?php

namespace Codemanas\VczApi\Helpers;

use Codemanas\VczApi\Data\Metastore;

/**
 * Generate Links and Something else?..
 *
 * @since 4.2.2
 * @author Deepen Bajracharya
 */
class Links {

	/**
	 * Get Browser join links
	 *
	 * @param      $meeting_id
	 * @param      $args
	 *
	 * @return string
	 */
	public static function getJoinViaBrowserJoinLinks( $args, $meeting_id ): string {
		if ( ! vczapi_is_sdk_enabled() ) {
			return false;
		}

		if ( \Codemanas\VczApi\Data\Metastore::checkDisableJoinViaBrowser() ) {
			return false;
		}

		$defaults = array(
			'post_id'   => '',
			'password'  => '',
			'seperator' => '',
			'redirect'  => '',
			'link_only' => false
		);

		$args = wp_parse_args( $args, $defaults );

		if ( ! empty( $args['post_id'] ) ) {
			$link = get_permalink( $args['post_id'] );
		} else {
			$link = get_post_type_archive_link( 'zoom-meetings' );
		}

		$encrypted_meeting_id     = Encryption::encrypt( $meeting_id );
		$embed_password_join_link = Metastore::get_plugin_settings( 'embed_pwd_in_join_link' );
		$seperator                = ! empty( $args['seperator'] ) ? '<span class="vczapi-seperator">' . $args['seperator'] . '</span>' : '';
		$query                    = [
			'join'        => $encrypted_meeting_id,
			'type'        => 'meeting',
			'redirect'    => ! empty( $args['redirect'] ) ? esc_url( $args['redirect'] ) : '',
		];

		if ( ! empty( $args['direct_join'] ) ) {
			$query['direct_join'] = 1;
		}

		if ( ! empty( $args['password'] ) && empty( $embed_password_join_link ) ) {
			$encrypted_password = Encryption::encrypt( $args['password'] );
			$query['pak']       = $encrypted_password;
		}

		$query = add_query_arg( $query, $link );

		if ( $args['link_only'] ) {
			return $query;
		}

		return $seperator . '<a target="_blank" rel="nofollow" href="' . esc_url( $query ) . '" class="btn btn-join-link btn-join-via-browser">' . apply_filters( 'vczapi_join_meeting_via_browser_text', __( 'Join via Web Browser', 'video-conferencing-with-zoom-api' ) ) . '</a>';
	}

	/**
	 * Get Join link with Password Embedded
	 *
	 * @param $join_url
	 * @param $encrpyted_pwd
	 *
	 * @return string
	 */
	public static function getPwdEmbeddedJoinLink( $join_url, $encrpyted_pwd ): string {
		if ( ! empty( $encrpyted_pwd ) ) {
			$explode_pwd              = array_map( 'trim', explode( '?pwd', $join_url ) );
			$embed_password_join_link = Metastore::get_plugin_settings( 'embed_pwd_in_join_link' );
			$password_exists          = count( $explode_pwd ) > 1;
			if ( $password_exists ) {
				if ( ! empty( $embed_password_join_link ) ) {
					$join_url = $explode_pwd[0];
				}
			} else {
				$join_url = esc_url( add_query_arg( array( 'pwd' => $encrpyted_pwd ), $join_url ) );
			}
		}

		return $join_url;
	}
}