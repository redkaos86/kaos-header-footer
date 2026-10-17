<?php
/**
 * Updates from the releases of the GitHub repository, with signed packages (Ed25519).
 *
 * The repository is public: no configuration is needed. If it ever became private, add a read-only token
 * (fine-grained, Contents: Read-only) to wp-config.php:
 *     define( 'KAOSHF_GITHUB_TOKEN', 'github_pat_…' );
 *
 * A package is installed only if its signature matches PUBLIC_KEY: even someone who managed to publish a
 * fake release could not get it installed without the private key, which stays on the computer of whoever
 * makes the release (see bin/release.js).
 * This file is independent from the rest of the plugin and is removed from the WordPress.org build.
 */

defined( 'ABSPATH' ) || exit;

class KAOSHF_Updater {

	const REPO       = 'redkaos86/kaos-header-footer';
	const PUBLIC_KEY = 'qWWQ0+Ucld/01ucIrcjdaeKJo5nRtv3WrsBTtVkJDBU=';
	const CACHE      = 'kaoshf_release';
	const ERROR      = 'kaoshf_update_error';
	const API        = 'https://api.github.com/repos/';

	public static function init() {
		if ( self::is_dev_copy() ) {
			return;
		}
		// With the "Update URI: https://github.com/…" plugin header WordPress asks this filter, not WordPress.org.
		add_filter( 'update_plugins_github.com', array( __CLASS__, 'check' ), 10, 3 );
		add_filter( 'plugins_api', array( __CLASS__, 'info' ), 20, 3 );
		add_filter( 'upgrader_pre_download', array( __CLASS__, 'download' ), 10, 3 );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'flush' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'row_meta' ), 10, 2 );
		add_action( 'admin_init', array( __CLASS__, 'manual_check' ) );
	}

	/**
	 * Development copy (linked folder or one with the git repository): an update would delete the original sources.
	 */
	public static function is_dev_copy() {
		$dir = untrailingslashit( KAOSHF_DIR );
		return is_link( $dir ) || file_exists( $dir . '/.git' );
	}

	private static function token() {
		return defined( 'KAOSHF_GITHUB_TOKEN' ) ? trim( (string) KAOSHF_GITHUB_TOKEN ) : '';
	}

	private static function request( $url, $accept, $args = array() ) {
		$headers = array(
			'Accept'               => $accept,
			'X-GitHub-Api-Version' => '2022-11-28',
			'User-Agent'           => 'KaosH&F/' . KAOSHF_VERSION,
		);
		if ( self::token() ) {
			$headers['Authorization'] = 'Bearer ' . self::token();
		}
		return wp_safe_remote_get(
			$url,
			array_merge(
				array(
					'timeout' => 15,
					'headers' => $headers,
				),
				$args
			)
		);
	}

	/**
	 * Latest published release: version, attachment ids (zip and signature), notes, date.
	 * It stays cached for 6 hours (30 minutes after an error, so as not to insist on GitHub).
	 *
	 * @return array|null
	 */
	public static function release( $force = false ) {
		$cached = get_site_transient( self::CACHE );
		if ( ! $force && is_array( $cached ) ) {
			return ! empty( $cached['ok'] ) ? $cached : null;
		}
		$rel   = null;
		$error = '';
		$res   = self::request( self::API . self::REPO . '/releases/latest', 'application/vnd.github+json' );
		$code  = is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
		if ( 200 === $code ) {
			$body    = json_decode( wp_remote_retrieve_body( $res ), true );
			$version = isset( $body['tag_name'] ) ? ltrim( (string) $body['tag_name'], 'v' ) : '';
			$assets  = array();
			foreach ( isset( $body['assets'] ) && is_array( $body['assets'] ) ? $body['assets'] : array() as $a ) {
				if ( isset( $a['name'], $a['id'] ) ) {
					$assets[ (string) $a['name'] ] = (int) $a['id'];
				}
			}
			$zip = 'kaos-header-footer-' . $version . '.zip';
			if ( preg_match( '/^\d+\.\d+\.\d+$/', $version ) && isset( $assets[ $zip ], $assets[ $zip . '.sig' ] ) ) {
				$rel = array(
					'ok'      => true,
					'version' => $version,
					'zip'     => $assets[ $zip ],
					'sig'     => $assets[ $zip . '.sig' ],
					'notes'   => isset( $body['body'] ) ? (string) $body['body'] : '',
					'date'    => isset( $body['published_at'] ) ? (string) $body['published_at'] : '',
				);
			} else {
				$error = __( 'the latest GitHub release does not contain the signed zip.', 'kaos-header-footer' );
			}
		} elseif ( 401 === $code ) {
			$error = __( 'the GitHub token in wp-config.php is invalid or expired.', 'kaos-header-footer' );
		} elseif ( 403 === $code || 429 === $code ) {
			$error = __( 'GitHub has temporarily limited requests from this server: it will try again later.', 'kaos-header-footer' );
		} elseif ( 404 === $code ) {
			$error = __( 'no release found on GitHub (if the repository is private, a token is needed in wp-config.php).', 'kaos-header-footer' );
		} else {
			/* translators: %d: HTTP status code. */
			$error = is_wp_error( $res ) ? $res->get_error_message() : sprintf( __( 'unexpected response from GitHub (%d).', 'kaos-header-footer' ), $code );
		}

		set_site_transient( self::CACHE, $rel ? $rel : array( 'ok' => false ), $rel ? 6 * HOUR_IN_SECONDS : 30 * MINUTE_IN_SECONDS );
		if ( $error ) {
			set_site_transient( self::ERROR, $error, DAY_IN_SECONDS );
		} else {
			delete_site_transient( self::ERROR );
		}
		return $rel;
	}

	/**
	 * Package address: the GitHub API for the zip, recognized later by download().
	 */
	private static function package_url( $rel ) {
		return self::API . self::REPO . '/releases/assets/' . $rel['zip'];
	}

	public static function check( $update, $plugin_data, $plugin_file ) {
		if ( plugin_basename( KAOSHF_FILE ) !== $plugin_file ) {
			return $update;
		}
		$rel = self::release();
		if ( ! $rel ) {
			return $update;
		}
		return array(
			'slug'         => 'kaos-header-footer',
			'version'      => $rel['version'],
			'url'          => 'https://github.com/' . self::REPO,
			'package'      => self::package_url( $rel ),
			'requires_php' => '7.4',
			'icons'        => array(
				'1x' => KAOSHF_URL . 'assets/img/icon-128x128.png',
				'2x' => KAOSHF_URL . 'assets/img/icon-256x256.png',
			),
		);
	}

	/**
	 * "View details" window with the release notes.
	 */
	public static function info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || 'kaos-header-footer' !== $args->slug ) {
			return $result;
		}
		$rel = self::release();
		if ( ! $rel ) {
			return $result;
		}
		return (object) array(
			'name'          => 'KaosH&F',
			'slug'          => 'kaos-header-footer',
			'version'       => $rel['version'],
			'author'        => 'DrKaos',
			'requires'      => '6.8',
			'requires_php'  => '7.4',
			'last_updated'  => $rel['date'],
			'download_link' => self::package_url( $rel ),
			'sections'      => array(
				'changelog' => wpautop( esc_html( $rel['notes'] ) ),
			),
		);
	}

	/**
	 * Downloads a private attachment: GitHub answers with a redirect to a temporary address,
	 * which is followed without sending it the token.
	 *
	 * @return string|WP_Error Content (or the file path if $file is given).
	 */
	private static function fetch_asset( $id, $file = '' ) {
		$res = self::request( self::API . self::REPO . '/releases/assets/' . (int) $id, 'application/octet-stream', array( 'redirection' => 0 ) );
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		$code = (int) wp_remote_retrieve_response_code( $res );
		if ( in_array( $code, array( 301, 302, 303, 307, 308 ), true ) ) {
			$location = wp_remote_retrieve_header( $res, 'location' );
			if ( ! is_string( $location ) || 0 !== strpos( $location, 'https://' ) ) {
				return new WP_Error( 'kaoshf_download', __( 'Invalid download address.', 'kaos-header-footer' ) );
			}
			$args = array( 'timeout' => 300 );
			if ( $file ) {
				$args['stream']   = true;
				$args['filename'] = $file;
			}
			$res  = wp_safe_remote_get( $location, $args );
			$code = is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
		} elseif ( 200 === $code && $file ) {
			file_put_contents( $file, wp_remote_retrieve_body( $res ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		if ( 200 !== $code ) {
			return new WP_Error( 'kaoshf_download', sprintf( /* translators: %d: HTTP status code. */ __( 'Download from GitHub failed (%d).', 'kaos-header-footer' ), $code ) );
		}
		return $file ? $file : wp_remote_retrieve_body( $res );
	}

	/**
	 * Downloads the release zip and hands it to the updater only if the signature is valid.
	 */
	public static function download( $reply, $package, $upgrader ) {
		$prefix = self::API . self::REPO . '/releases/assets/';
		if ( false !== $reply || ! is_string( $package ) || 0 !== strpos( $package, $prefix ) ) {
			return $reply;
		}
		$rel = self::release();
		if ( ! $rel || (int) substr( $package, strlen( $prefix ) ) !== $rel['zip'] ) {
			$rel = self::release( true ); // the release may have changed in the meantime
		}
		if ( ! $rel || (int) substr( $package, strlen( $prefix ) ) !== $rel['zip'] ) {
			return new WP_Error( 'kaoshf_download', __( 'Unknown update package.', 'kaos-header-footer' ) );
		}

		if ( $upgrader && isset( $upgrader->skin ) ) {
			$upgrader->skin->feedback( __( 'Downloading the update from GitHub…', 'kaos-header-footer' ) );
		}
		$sig = self::fetch_asset( $rel['sig'] );
		if ( is_wp_error( $sig ) ) {
			return $sig;
		}
		$tmp = wp_tempnam( 'kaos-header-footer-' . $rel['version'] . '.zip' );
		$zip = self::fetch_asset( $rel['zip'], $tmp );
		if ( is_wp_error( $zip ) ) {
			wp_delete_file( $tmp );
			return $zip;
		}

		if ( ! self::verify( (string) file_get_contents( $tmp ), $sig ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
			wp_delete_file( $tmp );
			return new WP_Error( 'kaoshf_signature', __( 'The package signature is not valid: update cancelled for security.', 'kaos-header-footer' ) );
		}
		if ( $upgrader && isset( $upgrader->skin ) ) {
			$upgrader->skin->feedback( __( 'Package signature verified.', 'kaos-header-footer' ) );
		}
		return $tmp;
	}

	/**
	 * Ed25519 signature (libsodium, or its PHP version bundled with WordPress).
	 */
	public static function verify( $data, $signature_b64 ) {
		$sig = base64_decode( trim( (string) $signature_b64 ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		$key = base64_decode( self::PUBLIC_KEY, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		if ( ! function_exists( 'sodium_crypto_sign_verify_detached' ) || 64 !== strlen( (string) $sig ) || 32 !== strlen( (string) $key ) ) {
			return false;
		}
		try {
			return sodium_crypto_sign_verify_detached( $sig, $data, $key );
		} catch ( Exception $e ) {
			return false;
		}
	}

	public static function flush() {
		delete_site_transient( self::CACHE );
	}

	/**
	 * On the Plugins page: a "Check for updates" link and any problem with GitHub.
	 */
	public static function row_meta( $links, $file ) {
		if ( plugin_basename( KAOSHF_FILE ) !== $file || ! current_user_can( 'update_plugins' ) ) {
			return $links;
		}
		$links[] = '<a href="' . esc_url( wp_nonce_url( admin_url( 'plugins.php?kaoshf_check=1' ), 'kaoshf_check' ) ) . '">' . esc_html__( 'Check for updates', 'kaos-header-footer' ) . '</a>';
		$error   = get_site_transient( self::ERROR );
		if ( $error ) {
			$links[] = '<span style="color:#b32d2e">' . esc_html( sprintf( /* translators: %s: description of the update problem. */ __( 'Updates: %s', 'kaos-header-footer' ), $error ) ) . '</span>';
		}
		return $links;
	}

	public static function manual_check() {
		if ( empty( $_GET['kaoshf_check'] ) || ! current_user_can( 'update_plugins' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		check_admin_referer( 'kaoshf_check' );
		self::release( true );
		delete_site_transient( 'update_plugins' );
		wp_update_plugins();
		wp_safe_redirect( admin_url( 'plugins.php' ) );
		exit;
	}
}
