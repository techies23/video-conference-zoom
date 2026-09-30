<?php

namespace Codemanas\VczApi\Helpers;

use Codemanas\VczApi\Browser\JoinToken;
use Codemanas\VczApi\Browser\JoinViaBrowser;
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
	 * Builds a Join-via-Browser link on the plugin's dedicated endpoint.
	 *
	 * The link used to be built as `?type=meeting&join=<aes ciphertext>` with an
	 * optional `pak=<aes ciphertext>` alongside it. Those values were encrypted
	 * but *unbound*: nothing tied them to a post or an expiry, so any ciphertext
	 * lifted from any published join link could be replayed against any other
	 * meeting. They are now packed into a single signed, expiring, post-bound
	 * token, and the link is `/zoom-join/<token>/`.
	 *
	 * The password is included in the token encrypted, so the one-click join
	 * still works, but it is never rendered into page source. It is released
	 * only by the signature endpoint, to a caller that presented this token.
	 *
	 * @param array $args {
	 *
	 * @type int $post_id Meeting post id. Empty for shortcode links.
	 * @type string $password Meeting passcode.
	 * @type string $seperator HTML inserted before the link.
	 * @type string $redirect Where Zoom sends the visitor after the
	 *                               meeting. Same host only; anything else is
	 *                               discarded by wp_validate_redirect().
	 * @type bool $link_only Return the URL instead of an anchor.
	 * @type bool $direct_join Skip the join form.
	 * @type string $tk Zoom registrant token.
	 * }
	 *
	 * @param string $meeting_id Zoom meeting number.
	 *
	 * @return string Anchor markup, a URL when `link_only`, or '' when unavailable.
	 */
	public static function getJoinViaBrowserJoinLinks( array $args, string $meeting_id ): string {
		if ( ! Common::validateSDKCredentials() ) {
			return '';
		}

		if ( Metastore::checkDisableJoinViaBrowser() ) {
			return '';
		}

		$defaults = array(
			'post_id'     => 0,
			'password'    => '',
			'seperator'   => '',
			'redirect'    => '',
			'link_only'   => false,
			'direct_join' => false,
			'tk'          => '',
		);

		$args = wp_parse_args( $args, $defaults );

		// Shortcode and archive links arrive without a post id, which would leave
		// the token unbound to any meeting. Look the post up from the meeting
		// number so every token we mint is bound to something, not just the ones
		// that happened to be rendered on a single meeting page.
		$post_id = absint( $args['post_id'] );

		if ( 0 === $post_id ) {
			$post_id = self::find_post_for_meeting( $meeting_id );
		}

		$token = JoinToken::issue(
			$post_id,
			$meeting_id,
			(string) $args['password'],
			array(
				'tk' => $args['tk'],
				'dj' => $args['direct_join'],
			)
		);

		if ( '' === $token ) {
			return '';
		}

		$query = JoinViaBrowser::url_for( $token );

		if ( ! empty( $args['redirect'] ) ) {
			// add_query_arg() URL encodes the value itself. Pre-encoding it here
			// would double-encode and produce a URL that no longer decodes back to
			// the requested destination.
			$query = add_query_arg( 'redirect', $args['redirect'], $query );
		}

		if ( $args['link_only'] ) {
			return $query;
		}

		$seperator = ! empty( $args['seperator'] ) ? '<span class="vczapi-seperator">' . esc_html( $args['seperator'] ) . '</span>' : '';

		return $seperator . '<a target="_blank" rel="nofollow noopener" href="' . esc_url( $query ) . '" class="btn btn-join-link btn-join-via-browser">' . esc_html( apply_filters( 'vczapi_join_meeting_via_browser_text', __( 'Join via Web Browser', 'video-conferencing-with-zoom-api' ) ) ) . '</a>';
	}

	/**
	 * Find the meeting post a Zoom meeting number belongs to.
	 *
	 * Used to bind shortcode-issued join tokens to a post. Returns 0 when there
	 * is no matching post, in which case the token is still signed and usable,
	 * it just is not invalidated when a meeting is deleted.
	 *
	 * @param string $meeting_id Zoom meeting number.
	 */
	private static function find_post_for_meeting( string $meeting_id ): int {
		/**
		 * Filter whether a shortcode join link should be bound to a meeting post.
		 *
		 * Return false to skip the lookup. This costs one meta query per link
		 * render, so it can be disabled on sites with very many shortcodes.
		 *
		 * @param bool $bind Whether to perform the lookup.
		 * @param string $meeting_id Zoom meeting number.
		 *
		 * @since 4.9.0
		 */
		if ( ! apply_filters( 'vczapi_bind_shortcode_join_token', true, $meeting_id ) ) {
			return 0;
		}

		$meeting_id = Signature::sanitize_meeting_number( $meeting_id );

		if ( '' === $meeting_id ) {
			return 0;
		}

		$posts = get_posts(
			array(
				'post_type'              => 'zoom-meetings',
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array(
						'key'   => '_meeting_zoom_details',
						'value' => '"id":"' . $meeting_id . '"',
					),
					array(
						'key'   => '_meeting_zoom_details',
						'value' => '"pmi":"' . $meeting_id . '"',
					),
				),
			)
		);

		return empty( $posts ) ? 0 : absint( $posts[0] );
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