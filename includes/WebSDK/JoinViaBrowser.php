<?php

namespace Codemanas\VczApi\WebSDK;

use Codemanas\VczApi\Admin\Foundation\PostType\PostTypeTemplates;
use Codemanas\VczApi\Admin\Repository\SettingsRepository;
use Codemanas\VczApi\Helpers\Templates;

/**
 * Join-via-Browser front controller.
 *
 * This owns the whole feature: the rewrite rule, the join page's document
 * lifecycle, and the legacy link migration. Before this existed the feature was
 * smeared across a procedural template-functions file, two template_include
 * filters, a hooks file and a REST class.
 *
 * The page used to be smuggled in by hijacking `template_include` for both
 * single meeting posts and the post type archive. That was awkward for two
 * reasons: the join page inherited the theme's full template (so the plugin had
 * to inject and then close its own `<html>` document from inside footer
 * callbacks), and the archive branch had no post to anchor itself to.
 *
 * It is now a dedicated endpoint on its own URL:
 *
 *     /zoom-join/<token>/
 *
 * Nothing about the request is trusted until `JoinRequest` has verified the
 * token, and the document is emitted here rather than assembled by hooks
 * firing at unrelated points in the theme's lifecycle.
 *
 * @since 4.9.0
 */
final class JoinViaBrowser {

    /**
     * Query var that identifies a join request.
     */
    public const QUERY_VAR = 'vczapi_join';

    /**
     * Path segment the rewrite rule hangs off.
     */
    public const ROUTE_SLUG = 'zoom/join-event-web';

    /**
     * Option that records which revision of the rewrite rules are in the database.
     *
     * Bump this whenever the rules themselves change.
     */
    private const REWRITE_VERSION = '1';

    /**
     * Option name holding the flushed rewrite version.
     */
    private const REWRITE_OPTION = 'vczapi_websdk_rewrite_version';

    /**
     * Singleton.
     *
     * @var JoinViaBrowser|null
     */
    private static ?JoinViaBrowser $instance = null;

    /**
     * The validated request for the current page load, if any.
     *
     * @var JoinRequest|null
     */
    private ?JoinRequest $request = null;

    /**
     * Whether the endpoint has already rendered.
     *
     * @var bool
     */
    private bool $rendered = false;

    /**
     * Boot the feature.
     */
    public static function instance(): JoinViaBrowser {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register hooks.
     *
     * @param bool $with_rewrite Whether to register the rewrite rule. Skipped in
     *                           REST/admin contexts and during activation, where
     *                           `init` may not be the right place.
     */
    public function boot( bool $with_rewrite = true ): void {
        if ( $with_rewrite ) {
            add_action( 'init', array( $this, 'add_rewrite_rules' ) );
            add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 99 );
            add_filter( 'query_vars', array( $this, 'register_query_var' ) );
        }

        add_action( 'template_redirect', array( $this, 'maybe_render' ), 1 );
        add_filter( 'wp_headers', array( $this, 'add_isolation_headers' ) );

        Rest\SignatureEndpoint::register_routes();
    }

    /**
     * Add the rewrite rule for the dedicated endpoint.
     */
    public function add_rewrite_rules(): void {
        add_rewrite_rule(
                '^' . self::ROUTE_SLUG . '/([A-Za-z0-9._-]+)/?$',
                'index.php?' . self::QUERY_VAR . '=$matches[1]',
                'top'
        );

        // Bare /zoom-join/ so the route 404s with our own error page rather
        // than WordPress' "nothing found".
        add_rewrite_rule(
                '^' . self::ROUTE_SLUG . '/?$',
                'index.php?' . self::QUERY_VAR . '=1',
                'top'
        );
    }

    /**
     * Flush the rewrite rules once after they are added or changed.
     *
     * `register_activation_hook()` fires before `init`, so the flush that happens
     * on activation never sees this rule and `/zoom-join/<token>/` 404s until
     * somebody visits the permalinks screen. Comparing a stored version against
     * a constant here covers activation and every future upgrade, and costs one
     * option read per request.
     *
     * Runs late on `init` so every plugin has had a chance to add its own rules.
     */
    public function maybe_flush_rewrite_rules(): void {
        if ( get_option( self::REWRITE_OPTION ) === self::REWRITE_VERSION ) {
            return;
        }

        flush_rewrite_rules( false );

        update_option( self::REWRITE_OPTION, self::REWRITE_VERSION, false );
    }

    /**
     * Forget the flushed rewrite version.
     *
     * Called on deactivation so reactivating forces a flush, since the rules are
     * removed from the option table along with everything else.
     */
    public static function forget_rewrite_version(): void {
        delete_option( self::REWRITE_OPTION );
    }

    /**
     * Register the query var so WordPress populates it.
     *
     * @param array $vars Registered query vars.
     */
    public function register_query_var( array $vars ): array {
        $vars[] = self::QUERY_VAR;

        return $vars;
    }

    /**
     * Build the canonical join URL for a token.
     *
     * @param string $token Signed token.
     */
    public static function url_for( string $token ): string {
        return home_url( user_trailingslashit( self::ROUTE_SLUG . '/' . rawurlencode( $token ) ) );
    }

    /**
     * Whether the current request is for the join endpoint.
     */
    public function is_join_request(): bool {
        $value = get_query_var( self::QUERY_VAR );

        return '' !== $value && null !== $value && false !== $value;
    }

    /**
     * Entry point: validate, then render or redirect.
     *
     * Runs on `template_redirect` at priority 1 so we own the response before
     * WordPress starts resolving a template.
     */
    public function maybe_render(): void {
        if ( $this->rendered ) {
            return;
        }

        // Two ways in: the dedicated endpoint, and a pre-4.9 `?type=meeting&join=`
        // link pointing at a meeting post or the meetings archive. The second is
        // scoped to those two contexts so a stray query string on an unrelated
        // page can never hijack that page's output.
        $is_endpoint = $this->is_join_request();
        $is_legacy   = ! $is_endpoint && PostTypeTemplates::is_legacy_join_request();

        if ( ! $is_endpoint && ! $is_legacy ) {
            return;
        }

        $this->rendered = true;
        $this->request  = JoinRequest::from_request();

        // A pre-4.9 link that we can still honour: send the visitor to the
        // canonical endpoint so the next request is a normal token request.
        $token = $this->request->token();

        if ( null !== $token && $is_legacy ) {
            wp_safe_redirect( self::url_for( $token->to_string() ), 301 );
            exit;
        }

        Assets::register();

        $this->send_headers();

        $this->render_document();

        //Exit out.
        exit;
    }

    /**
     * Add the headers the Zoom Web SDK requires.
     *
     * The SDK uses SharedArrayBuffer, which the browser only exposes to a
     * cross-origin isolated document. Without these two headers the SDK falls
     * back to a single-threaded path and, on some browsers, fails outright.
     *
     * @param array $headers Response headers.
     */
    public function add_isolation_headers( array $headers ): array {
        if ( ! $this->is_join_request() && ! PostTypeTemplates::is_legacy_join_request() ) {
            return $headers;
        }

        $headers['Cross-Origin-Embedder-Policy'] = 'require-corp';
        $headers['Cross-Origin-Opener-Policy']   = 'same-origin';

        return $headers;
    }

    /**
     * Set the response status.
     *
     * The join token is a capability and it lives in the URL, so the response is
     * never cacheable and never sends a Referer onwards: a shared link, a CDN or
     * a third party asset referenced from the page would otherwise be able to
     * harvest it.
     */
    private function send_headers(): void {
        nocache_headers();

        header( 'Referrer-Policy: no-referrer', true );

        if ( $this->request->is_valid() ) {
            return;
        }

        // No token, or an unverifiable one, means we are not looking at a real
        // meeting request. Reporting 404 keeps expired and forged links
        // indistinguishable from links that never existed.
        status_header( 404 );
    }

    /**
     * Emit the join page.
     *
     * This deliberately does not use `get_header()`/`get_footer()`. The join
     * page is a full-screen application surface, not a theme page, and letting
     * a theme wrap it is what previously forced the plugin to open and close its
     * own HTML document from inside hook callbacks.
     */
    private function render_document(): void {
        $request = $this->request;
        $valid   = $request->is_valid();

        Assets::enqueue_styles();

        if ( $valid ) {
            // Populate the legacy globals bfore anything hooks in, so add-ons
            // that hooked `vczoom_jbh_before_content` still receive a `$zoom`
            // array with the meeting details rather than null.
            $this->prime_legacy_globals( $request );

            // Only a real request has a token to hand to the client. Building the
            // configuration for a rejected one would dereference a null token and
            // take the whole site down on a forged URL.
            Assets::enqueue( $this->build_config( $request ) );
        }

        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo( 'charset' ); ?>">
            <meta name="format-detection" content="telephone=no">
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
            <meta name="robots" content="noindex, nofollow">
            <meta name="referrer" content="no-referrer">
            <title><?php echo esc_html( $this->document_title( $request ) ); ?></title>
            <?php
            do_action( 'wp_enqueue_scripts' );
            wp_print_styles();
            Assets::print_styles();

            /**
             * Fires in the join page `<head>`, after the plugin's own styles.
             *
             * @since 4.9.0
             */
            do_action( 'vczapi_join_via_browser_head' );
            ?>
        </head>
        <body class="vczapi-join-via-browser">
        <?php
        /**
         * Fires at the top of the join page body, before any plugin content.
         *
         * @param JoinRequest $request Validated request.
         *
         * @since 4.9.0
         */
        do_action( 'vczapi_join_via_browser_before_content', $request );

        /**
         * Fires at the top of the join page body, before any plugin content.
         *
         * @param array|null $zoom Legacy meeting details, or null on a rejected link.
         *
         * @deprecated 4.9.0 Use `vczapi_join_via_browser_before_content` instead.
         */
        do_action( 'vczoom_jbh_before_content', $GLOBALS['zoom'] ?? null );

        $this->render_body( $request );

        /**
         * Fires at the end of the join page body, after all plugin content.
         *
         * @param JoinRequest $request Validated request.
         *
         * @since 4.9.0
         */
        do_action( 'vczapi_join_via_browser_after_content', $request );

        /**
         * Fires at the end of the join page body, after all plugin content.
         *
         * @param array|null $zoom Legacy meeting details, or null on a rejected link.
         *
         * @deprecated 4.9.0 Use `vczapi_join_via_browser_after_content` instead.
         */
        do_action( 'vczoom_jbh_after_content', $GLOBALS['zoom'] ?? null );
        ?>
        </body>
        </html>
        <?php
    }

    /**
     * Emit the join page body.
     *
     * @param JoinRequest $request Validated request.
     */
    private function render_body( JoinRequest $request ): void {
        if ( ! $request->is_valid() ) {
            $this->render_error( $request->error() );

            return;
        }

        $template = $request->token()->direct_join()
                ? 'join-web-browser-directly.php'
                : 'join-web-browser.php';

        /**
         * Filter the template used for the join page.
         *
         * @param string $template Template file name, relative to templates/.
         * @param JoinRequest $request Validated request.
         *
         * @since 4.9.0
         */
        $template = (string) apply_filters( 'vczapi_join_via_browser_template', $template, $request );

        $located = Templates::getTemplate( $template );

        if ( ! $located ) {
            $this->render_error( __( 'The join page could not be loaded. Please use the Zoom app or desktop client instead.', 'video-conferencing-with-zoom-api' ) );

            return;
        }

        Templates::includeFile( $located, array( 'join_request' => $request ) );

        $this->print_footer_scripts();
    }

    /**
     * Print the footer scripts and fire the legacy footer hooks.
     *
     * `vczapi_join_via_browser_footer` and
     * `vczapi_join_via_browser_after_script_load` used to fire from
     * `video_conference_zoom_after_jbh_html()`. They are still fired, in the same
     * order, so anything an add-on appended to the join page keeps working.
     */
    private function print_footer_scripts(): void {
        /**
         * Fires before the join page scripts are printed.
         *
         * @since 3.4.0
         */
        do_action( 'vczapi_join_via_browser_footer' );

        Assets::print_scripts();

        /**
         * Fires after the join page scripts have been printed.
         *
         * @since 3.4.0
         */
        do_action( 'vczapi_join_via_browser_after_script_load' );
    }

    /**
     * Render a standalone error page.
     *
     * @param string $message Reason.
     */
    private function render_error( string $message ): void {
        ?>
        <div class="vczapi-jvb__container vczapi-jvb__container--standalone">
            <div class="vczapi-jvb__card">
                <div class="vczapi-jvb__notice vczapi-jvb__notice--error" role="alert">
                    <strong class="vczapi-jvb__notice-title"><?php esc_html_e( 'Unable to join', 'video-conferencing-with-zoom-api' ); ?></strong>
                    <p class="vczapi-jvb__notice-message"><?php echo esc_html( $message ); ?></p>
                </div>
                <a class="vczapi-jvb__submit vczapi-jvb__submit--link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php esc_html_e( 'Back to home', 'video-conferencing-with-zoom-api' ); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Set the legacy `$zoom` global for theme template overrides.
     *
     * @param JoinRequest $request Validated request.
     */
    private function prime_legacy_globals( JoinRequest $request ): void {
        $post = $request->post();

        $zoom = is_array( $GLOBALS['zoom'] ?? null ) ? $GLOBALS['zoom'] : array();

        $zoom['api']          = (object) array(
                'id'      => $request->token()->meeting_number(),
                'code'    => '',
                'topic'   => $request->topic(),
                'type'    => 2,
                'state'   => 'started',
                'message' => '',
        );
        $zoom['meeting_type'] = 2;
        $zoom['password']     = $request->token()->password();
        $zoom['post_id']      = $post ? $post->ID : 0;

        $GLOBALS['zoom'] = $zoom;
    }

    /**
     * Document title.
     *
     * @param JoinRequest $request Validated request.
     */
    private function document_title( JoinRequest $request ): string {
        $topic = $request->topic();

        if ( '' === $topic ) {
            $topic = __( 'Join Meeting', 'video-conferencing-with-zoom-api' );
        }

        return $topic . ' - ' . get_bloginfo( 'name' );
    }

    /**
     * Build the configuration handed to the browser client.
     *
     * Note what is *not* here: the meeting password. It stays server-side and is
     * only released, inside the signature response, to a caller that has already
     * proven it holds this token. The old implementation put the password in
     * page source and in a global JS variable, which leaked it to anyone who
     * viewed source, to browser history, and to Referer headers.
     *
     * @param JoinRequest $request Validated request.
     */
    private function build_config( JoinRequest $request ): array {
        $token = $request->token();

        $config = array(
                'joinToken'    => $token->to_string(),
                'signatureUrl' => esc_url_raw( rest_url( Rest\SignatureEndpoint::NAMESPACE . '/' . Rest\SignatureEndpoint::ROUTE ) ),
                'restNonce'    => wp_create_nonce( 'wp_rest' ),
                'clientUrl'    => Assets::client_url(),
                'helperUrl'    => Assets::helper_url(),
                'leaveUrl'     => $this->leave_url( $request ),
                'lang'         => $request->default_lang(),
                'directJoin'   => $token->direct_join(),
                'userName'     => $request->get_user_name(),
                'userEmail'    => $this->suggested_email(),
                'hasPassword'  => '' !== $token->password(),
                'sdkVersion'   => defined( 'VCZAPI_PLUGIN_ZOOM_WEBSDK_VERSION' ) ? VCZAPI_PLUGIN_ZOOM_WEBSDK_VERSION : '',
        );

        /**
         * Filter the Join-via-Browser client configuration.
         *
         * @param array $config Client configuration.
         * @param JoinRequest $request Validated request.
         *
         * @since 4.9.0
         */
        return (array) apply_filters( 'vczapi_join_via_browser_config', $config, $request );
    }

    /**
     * Where Zoom should send the visitor when they leave.
     *
     * @param JoinRequest $request Validated request.
     */
    private function leave_url( JoinRequest $request ): string {
        $post = $request->post();

        $default = $post ? get_permalink( $post->ID ) : home_url( '/' );

        // A `redirect` argument on the join link wins over the meeting post, so a
        // site can send visitors to a custom thank-you page after they leave.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public join URL; it only ever changes where Zoom sends the visitor afterwards.
        $requested = isset( $_GET['redirect'] ) && is_scalar( $_GET['redirect'] )
                ? esc_url_raw( wp_unslash( (string) $_GET['redirect'] ) )
                : '';

        /**
         * Filter the post-join redirect target.
         *
         * @param string $url Redirect URL.
         * @param JoinRequest $request Validated request.
         *
         * @since 4.9.0
         */
        $url = (string) apply_filters( 'vczapi_api_redirect_join_browser', '' !== $requested ? $requested : $default, $request );

        // wp_validate_redirect is the only safe thing to do with a URL that came
        // from a query string. Without it this is an open redirect. Anything
        // pointing off-site falls back to the meeting post.
        $url = wp_validate_redirect( $url, $default );

        return $url ?: $default;
    }

    /**
     * Pre-fill the email from the current user.
     */
    private function suggested_email(): string {
        if ( ! is_user_logged_in() ) {
            return '';
        }

        return wp_get_current_user()->user_email;
    }
}
