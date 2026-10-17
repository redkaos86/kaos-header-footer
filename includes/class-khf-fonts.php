<?php
/**
 * Web fonts from the Google Fonts catalog (open source), served in a privacy-friendly way:
 * - local  (default): the files are downloaded once to the site's server, visitors never contact a third party;
 * - bunny: European CDN without tracking (fonts.bunny.net), same catalog;
 * - google: direct link to Google Fonts (not recommended in the EU).
 *
 * @package kaos-header-footer
 */

defined( 'ABSPATH' ) || exit;

class KHF_Fonts {

	const DIR = 'kaos-hf-fonts';

	/**
	 * Weights available for the bundled families (the rest of the catalog is assumed to have 400 and 700).
	 *
	 * @return array
	 */
	private static function weights() {
		$all = static function ( $from, $to ) {
			return range( $from, $to, 100 );
		};
		return array(
			'Inter'              => $all( 100, 900 ),
			'Poppins'            => $all( 100, 900 ),
			'Montserrat'         => $all( 100, 900 ),
			'DM Sans'            => $all( 100, 900 ),
			'Manrope'            => $all( 200, 800 ),
			'Space Grotesk'      => $all( 300, 700 ),
			'Outfit'             => $all( 100, 900 ),
			'Raleway'            => $all( 100, 900 ),
			'Nunito'             => $all( 200, 900 ),
			'Work Sans'          => $all( 100, 900 ),
			'Urbanist'           => $all( 100, 900 ),
			'Sora'               => $all( 100, 800 ),
			'Josefin Sans'       => $all( 100, 700 ),
			'Archivo'            => $all( 100, 900 ),
			'Syne'               => $all( 400, 800 ),
			'Oswald'             => $all( 200, 700 ),
			'Bebas Neue'         => array( 400 ),
			'Playfair Display'   => $all( 400, 900 ),
			'Lora'               => $all( 400, 700 ),
			'Merriweather'       => array( 300, 400, 700, 900 ),
			'Cormorant Garamond' => $all( 300, 700 ),
			'Libre Baskerville'  => array( 400, 700 ),
			'Fraunces'           => $all( 100, 900 ),
			'Unbounded'          => $all( 200, 900 ),
		);
	}

	public static function mode() {
		$s = KHF_Options::settings();
		return in_array( $s['font_mode'], array( 'local', 'bunny', 'google' ), true ) ? $s['font_mode'] : 'local';
	}

	private static function nearest( $weights, $w ) {
		$best = $weights[0];
		foreach ( $weights as $x ) {
			if ( abs( $x - $w ) < abs( $best - $w ) ) {
				$best = $x;
			}
		}
		return $best;
	}

	/**
	 * Fonts used by a list of templates: family => sorted weights.
	 *
	 * @param array $templates Template options (as returned by KHF_Options::get()).
	 * @return array
	 */
	public static function collect( $templates ) {
		$cat  = self::weights();
		$used = array();
		$add  = static function ( $family, $wanted ) use ( &$used, $cat ) {
			if ( '' === $family ) {
				return;
			}
			$avail = isset( $cat[ $family ] ) ? $cat[ $family ] : array( 400, 700 );
			foreach ( $wanted as $w ) {
				$n                    = self::nearest( $avail, (int) $w );
				$used[ $family ][ $n ] = $n;
			}
		};
		foreach ( $templates as $o ) {
			$base = array( 400, 500, 600, 700, 800 );
			foreach ( array( 'menu_fw', 'panel_fw', 'headline_fw' ) as $k ) {
				if ( ! empty( $o[ $k ] ) ) {
					$base[] = (int) $o[ $k ];
				}
			}
			list( , $name ) = KHF_Presets::font_source( $o['headline_ff'] ?? '', '' );
			$add( $name, $base );
			foreach ( (array) ( $o['grid_cells'] ?? array() ) as $cell ) {
				list( , $name ) = KHF_Presets::font_source( $cell['ff'] ?? '', '' );
				$add( $name, array_merge( $base, empty( $cell['fw'] ) ? array() : array( (int) $cell['fw'] ) ) );
			}
			foreach ( array( array( 'font_family', 'font_custom' ), array( 'font_head', 'font_head_custom' ) ) as $pair ) {
				list( , $name ) = KHF_Presets::font_source( $o[ $pair[0] ] ?? '', $o[ $pair[1] ] ?? '' );
				$add( $name, $base );
			}
		}
		foreach ( $used as &$weights ) {
			sort( $weights );
		}
		unset( $weights );
		ksort( $used );
		return $used;
	}

	public static function google_url( $fonts ) {
		$parts = array();
		foreach ( $fonts as $family => $weights ) {
			$parts[] = 'family=' . str_replace( ' ', '+', $family ) . ':wght@' . implode( ';', $weights );
		}
		return 'https://fonts.googleapis.com/css2?' . implode( '&', $parts ) . '&display=swap';
	}

	public static function bunny_url( $fonts ) {
		$parts = array();
		foreach ( $fonts as $family => $weights ) {
			$parts[] = strtolower( str_replace( ' ', '-', $family ) ) . ':' . implode( ',', $weights );
		}
		return 'https://fonts.bunny.net/css?family=' . implode( '|', $parts ) . '&display=swap';
	}

	private static function paths( $fonts ) {
		$up  = wp_upload_dir( null, false );
		$key = substr( md5( wp_json_encode( $fonts ) ), 0, 12 );
		return array(
			'dir'     => trailingslashit( $up['basedir'] ) . self::DIR,
			'url'     => trailingslashit( $up['baseurl'] ) . self::DIR,
			'css'     => trailingslashit( $up['basedir'] ) . self::DIR . '/fonts-' . $key . '.css',
			'css_url' => trailingslashit( $up['baseurl'] ) . self::DIR . '/fonts-' . $key . '.css',
		);
	}

	/** Fetches the CSS of a single family from Google (falls back to the default weight if some are missing). */
	private static function fetch_css( $family, $weights ) {
		$tries = array(
			array( $family => $weights ),
			array( $family => array( 400 ) ),
		);
		foreach ( $tries as $try ) {
			$res = wp_safe_remote_get(
				self::google_url( $try ),
				array(
					'timeout'    => 15,
					// A modern user agent makes Google return WOFF2 files, the lightest ones.
					'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
				)
			);
			if ( ! is_wp_error( $res ) && 200 === (int) wp_remote_retrieve_response_code( $res ) ) {
				return wp_remote_retrieve_body( $res );
			}
		}
		return '';
	}

	/**
	 * Downloads the fonts to the server (once) and builds a CSS file that points to the local files.
	 *
	 * @param array $fonts family => weights.
	 * @return string URL of the local CSS, '' if the download failed.
	 */
	public static function prepare_local( $fonts ) {
		if ( ! $fonts ) {
			return '';
		}
		$p = self::paths( $fonts );
		if ( file_exists( $p['css'] ) ) {
			return $p['css_url'];
		}
		$css = '';
		foreach ( $fonts as $family => $weights ) {
			$css .= self::fetch_css( $family, $weights );
		}
		if ( '' === $css ) {
			return '';
		}

		// Latin alphabets only (Italian and other European languages): smaller files.
		$blocks = preg_split( '~(?=/\*\s*[\w-]+\s*\*/)~', $css );
		$keep   = '';
		foreach ( $blocks as $b ) {
			if ( preg_match( '~^/\*\s*(latin|latin-ext)\s*\*/~', trim( $b ) ) ) {
				$keep .= $b;
			}
		}
		if ( '' === trim( $keep ) ) {
			$keep = $css;
		}

		if ( ! wp_mkdir_p( $p['dir'] . '/files' ) ) {
			return '';
		}
		$ok  = true;
		$out = preg_replace_callback(
			'~url\((https://fonts\.gstatic\.com/[^)\s]+\.woff2)\)~',
			static function ( $m ) use ( $p, &$ok ) {
				$name = substr( md5( $m[1] ), 0, 16 ) . '.woff2';
				$file = $p['dir'] . '/files/' . $name;
				if ( ! file_exists( $file ) ) {
					$r    = wp_safe_remote_get( $m[1], array( 'timeout' => 20 ) );
					$font = is_wp_error( $r ) ? '' : wp_remote_retrieve_body( $r );
					// Only a real WOFF2 file (signature "wOF2") of reasonable size is saved.
					if ( 200 !== (int) wp_remote_retrieve_response_code( $r ) || 'wOF2' !== substr( $font, 0, 4 ) || strlen( $font ) > 2 * MB_IN_BYTES ) {
						$ok = false;
						return $m[0];
					}
					file_put_contents( $file, $font ); // phpcs:ignore WordPress.WP.AlternativeFunctions
				}
				return 'url(' . $p['url'] . '/files/' . $name . ')';
			},
			$keep
		);
		if ( ! $ok || false !== strpos( $out, 'fonts.gstatic.com' ) ) {
			return ''; // Never link the visitor to Google by mistake: it will be retried on the next save.
		}
		// Every url() must point to the files just saved and the CSS must contain nothing but @font-face rules.
		preg_match_all( '~url\(([^)]*)\)~', $out, $urls );
		foreach ( $urls[1] as $u ) {
			if ( 0 !== strpos( $u, $p['url'] . '/files/' ) ) {
				return '';
			}
		}
		if ( preg_match( '~@import|<|expression\(~i', $out ) ) {
			return '';
		}
		file_put_contents( $p['css'], "/* Kaos Header & Footer: locally hosted fonts */\n" . $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		return $p['css_url'];
	}

	/**
	 * Stylesheet URL for a set of fonts, according to the chosen mode.
	 *
	 * @param array $fonts   family => weights.
	 * @param bool  $preview In the editor preview: if the local files are not there yet, Bunny is used instead of downloading.
	 * @return string
	 */
	public static function stylesheet_url( $fonts, $preview = false ) {
		if ( ! $fonts ) {
			return '';
		}
		$mode = self::mode();
		if ( 'google' === $mode ) {
			return self::google_url( $fonts );
		}
		if ( 'bunny' === $mode ) {
			return self::bunny_url( $fonts );
		}
		$p = self::paths( $fonts );
		if ( file_exists( $p['css'] ) ) {
			return $p['css_url'];
		}
		return $preview ? self::bunny_url( $fonts ) : self::prepare_local( $fonts );
	}

	/** Downloads the fonts a template needs (local mode only); called when the template is saved. */
	public static function prepare_for( $opts ) {
		if ( 'local' !== self::mode() ) {
			return;
		}
		self::prepare_local( self::collect( array( $opts ) ) );
	}
}
