<?php
defined( 'ABSPATH' ) || exit;

/**
 * Real preview: a page of the site (with the real theme, Elementor and assets) that shows the template
 * on demo content. It serves both the editor (unsaved draft) and the thumbnails.
 */
class KAOSHF_Preview {

	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'maybe_render' ), 0 );
		// It must be decided before 'init', otherwise WordPress has already added the admin bar margin.
		if ( isset( $_GET['kaoshf_preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_filter( 'show_admin_bar', '__return_false' );
		}
	}

	public static function url( $id, $draft = false, $thumb = false ) {
		$args = array(
			'kaoshf_preview' => (int) $id,
			'_wpnonce'    => wp_create_nonce( 'kaoshf_preview_' . (int) $id ),
		);
		if ( $draft ) {
			$args['draft'] = 1;
		}
		if ( $thumb ) {
			$args['thumb'] = 1;
		}
		return add_query_arg( $args, home_url( '/' ) );
	}

	public static function draft_key( $id ) {
		return 'kaoshf_draft_' . get_current_user_id() . '_' . (int) $id;
	}

	public static function maybe_render() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- the check is right below.
		if ( ! isset( $_GET['kaoshf_preview'] ) ) {
			return;
		}
		$id    = absint( $_GET['kaoshf_preview'] );
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		$draft = ! empty( $_GET['draft'] );
		$thumb = ! empty( $_GET['thumb'] );
		// phpcs:enable

		if ( ! $id || ! wp_verify_nonce( $nonce, 'kaoshf_preview_' . $id ) || ! current_user_can( 'edit_post', $id ) || KAOSHF_CPT::POST_TYPE !== get_post_type( $id ) ) {
			wp_die( esc_html__( 'Preview not allowed.', 'kaos-header-footer' ), '', array( 'response' => 403 ) );
		}

		$opts = KAOSHF_Options::get( $id );
		if ( $draft ) {
			// The editor posts the unsaved draft straight to the preview: nothing has to be stored in between.
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce checked above; the JSON is decoded and sanitized by KAOSHF_Options::sanitize().
			$posted = isset( $_POST['kaoshf_opts'] ) ? json_decode( wp_unslash( $_POST['kaoshf_opts'] ), true ) : null;
			if ( is_array( $posted ) ) {
				$opts = KAOSHF_Options::sanitize( $posted );
			} else {
				$saved = get_transient( self::draft_key( $id ) );
				if ( is_array( $saved ) ) {
					$opts = $saved;
				}
			}
		}

		KAOSHF_Renderer::force( $id, $opts, $thumb );
		nocache_headers();
		header( 'X-Robots-Tag: noindex' );

		$GLOBALS['kaoshf_preview_thumb'] = $thumb;
		$GLOBALS['kaoshf_preview_kind']  = $opts['type'];
		include KAOSHF_DIR . 'templates/preview.php';
		exit;
	}
}
