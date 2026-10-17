<?php
defined( 'ABSPATH' ) || exit;

/**
 * Scroll effects engine.
 *
 * A single schema describes every effect; from it come the sanitization, the
 * JSON config passed to the front end, the Elementor panel, the template box
 * and the selector-based rules (Divi or any other theme).
 */
class KHF_Fx {

	private static $wanted      = false;
	private static $css_printed = false;

	/* ------------------------------------------------------------------ */
	/* Schema                                                              */
	/* ------------------------------------------------------------------ */

	public static function schema() {
		static $schema = null;
		if ( null !== $schema ) {
			return $schema;
		}

		$devices = array(
			'desktop' => __( 'Desktop', 'kaos-header-footer' ),
			'tablet'  => __( 'Tablet', 'kaos-header-footer' ),
			'mobile'  => __( 'Mobile', 'kaos-header-footer' ),
		);
		$sticky  = array( 'mode' => array( 'fixed', 'smart' ) );
		$tones   = array(
			'none'  => __( 'Unchanged', 'kaos-header-footer' ),
			'dark'  => __( 'All black', 'kaos-header-footer' ),
			'light' => __( 'All white', 'kaos-header-footer' ),
		);

		$schema = array(
			'behavior' => array(
				'label'  => __( 'Behavior', 'kaos-header-footer' ),
				'fields' => array(
					'mode'    => array(
						'type'    => 'select',
						'label'   => __( 'Sticky mode', 'kaos-header-footer' ),
						'default' => 'fixed',
						'options' => array(
							'none'  => __( 'None (scrolls with the page)', 'kaos-header-footer' ),
							'fixed' => __( 'Always fixed at the top', 'kaos-header-footer' ),
							'smart' => __( 'Smart: hides on scroll down, returns on scroll up', 'kaos-header-footer' ),
						),
					),
					'devices' => array(
						'type'    => 'multi',
						'label'   => __( 'Active on', 'kaos-header-footer' ),
						'default' => array_keys( $devices ),
						'options' => $devices,
					),
					'at'      => array(
						'type'    => 'number',
						'label'   => __( 'Scroll threshold (px)', 'kaos-header-footer' ),
						'default' => 40,
						'min'     => 0,
						'max'     => 3000,
						'help'    => __( 'After how many pixels the effects kick in (class .is-stuck).', 'kaos-header-footer' ),
					),
					'overlay' => array(
						'type'    => 'check',
						'label'   => __( 'Overlaid on the content', 'kaos-header-footer' ),
						'inline'  => __( 'Transparent over the hero until you scroll', 'kaos-header-footer' ),
						'default' => 0,
					),
					'stack'   => array(
						'type'    => 'check',
						'label'   => __( 'Stack', 'kaos-header-footer' ),
						'inline'  => __( 'Placed below the other sticky elements', 'kaos-header-footer' ),
						'default' => 1,
						'when'    => $sticky,
					),
					'until'   => array(
						'type'    => 'text',
						'label'   => __( 'Sticky until…', 'kaos-header-footer' ),
						'default' => '',
						'help'    => __( 'CSS selector (e.g. #contacts): when the page scrolls past that element the header leaves the stage.', 'kaos-header-footer' ),
						'when'    => $sticky,
					),
				),
			),
			'colors'   => array(
				'label'  => __( 'Colors', 'kaos-header-footer' ),
				'fields' => array(
					'bg0'    => array( 'type' => 'paint', 'label' => __( 'Background at the top', 'kaos-header-footer' ), 'default' => '' ),
					'bg1'    => array( 'type' => 'paint', 'label' => __( 'Background on scroll', 'kaos-header-footer' ), 'default' => '' ),
					'fg0'    => array( 'type' => 'color', 'label' => __( 'Text at the top', 'kaos-header-footer' ), 'default' => '' ),
					'fg1'    => array( 'type' => 'color', 'label' => __( 'Text on scroll', 'kaos-header-footer' ), 'default' => '' ),
					'tone'   => array(
						'type'    => 'check',
						'label'   => __( 'Adaptive colors', 'kaos-header-footer' ),
						'inline'  => __( 'Changes the text depending on the section under the header', 'kaos-header-footer' ),
						'default' => 0,
						'help'    => __( 'Give your sections the class khf-on-dark (dark background) or khf-on-light (light background).', 'kaos-header-footer' ),
					),
					'tone_d' => array( 'type' => 'color', 'label' => __( 'Text on a dark section', 'kaos-header-footer' ), 'default' => '#ffffff', 'when' => array( 'tone' => array( '1' ) ) ),
					'tone_l' => array( 'type' => 'color', 'label' => __( 'Text on a light section', 'kaos-header-footer' ), 'default' => '#111111', 'when' => array( 'tone' => array( '1' ) ) ),
					'op0'    => array( 'type' => 'number', 'label' => __( 'Opacity at the top (%)', 'kaos-header-footer' ), 'default' => 100, 'min' => 0, 'max' => 100 ),
					'op1'    => array( 'type' => 'number', 'label' => __( 'Opacity on scroll (%)', 'kaos-header-footer' ), 'default' => 100, 'min' => 0, 'max' => 100 ),
				),
			),
			'edges'    => array(
				'label'  => __( 'Border, shadow and shape', 'kaos-header-footer' ),
				'fields' => array(
					'shadow'   => array(
						'type'    => 'select',
						'label'   => __( 'Shadow on scroll', 'kaos-header-footer' ),
						'default' => 'none',
						'options' => array(
							'none'   => __( 'None', 'kaos-header-footer' ),
							'soft'   => __( 'Light', 'kaos-header-footer' ),
							'medium' => __( 'Medium', 'kaos-header-footer' ),
							'strong' => __( 'Strong', 'kaos-header-footer' ),
						),
					),
					'border'   => array( 'type' => 'number', 'label' => __( 'Bottom border (px)', 'kaos-header-footer' ), 'default' => 0, 'min' => 0, 'max' => 20 ),
					'border_c' => array( 'type' => 'color', 'label' => __( 'Border color', 'kaos-header-footer' ), 'default' => '' ),
					'inset'    => array( 'type' => 'number', 'label' => __( 'Side margin on scroll (px)', 'kaos-header-footer' ), 'default' => 0, 'min' => 0, 'max' => 400, 'help' => __( 'With a margin, a top distance and a radius the header becomes a floating "island".', 'kaos-header-footer' ), 'when' => $sticky ),
					'gap'      => array( 'type' => 'number', 'label' => __( 'Distance from the top (px)', 'kaos-header-footer' ), 'default' => 0, 'min' => 0, 'max' => 200, 'when' => $sticky ),
					'radius'   => array( 'type' => 'number', 'label' => __( 'Corner radius (px)', 'kaos-header-footer' ), 'default' => 0, 'min' => 0, 'max' => 200 ),
				),
			),
			'size'     => array(
				'label'  => __( 'Size and logo', 'kaos-header-footer' ),
				'fields' => array(
					'shrink'  => array( 'type' => 'number', 'label' => __( 'Final height on scroll (px)', 'kaos-header-footer' ), 'default' => 0, 'min' => 0, 'max' => 400, 'help' => __( '0 = no shrinking. It reduces the vertical padding until the given height is reached.', 'kaos-header-footer' ), 'preset_off' => true ),
					'logo'    => array( 'type' => 'text', 'label' => __( 'Logo selector', 'kaos-header-footer' ), 'default' => '', 'help' => __( 'Empty = automatic detection (WordPress, Elementor, Divi logos).', 'kaos-header-footer' ) ),
					'logo_s'  => array( 'type' => 'number', 'label' => __( 'Logo scale on scroll (%)', 'kaos-header-footer' ), 'default' => 100, 'min' => 20, 'max' => 100 ),
					'logo0'   => array( 'type' => 'image', 'label' => __( 'Logo at the top', 'kaos-header-footer' ), 'default' => '' ),
					'logo1'   => array( 'type' => 'image', 'label' => __( 'Logo on scroll', 'kaos-header-footer' ), 'default' => '' ),
					'logo_f0' => array( 'type' => 'select', 'label' => __( 'Logo tint at the top', 'kaos-header-footer' ), 'default' => 'none', 'options' => $tones ),
					'logo_f1' => array( 'type' => 'select', 'label' => __( 'Logo tint on scroll', 'kaos-header-footer' ), 'default' => 'none', 'options' => $tones ),
				),
			),
			'glass'    => array(
				'label'  => __( 'Glass and blur', 'kaos-header-footer' ),
				'fields' => array(
					'blur'      => array( 'type' => 'number', 'label' => __( 'Blur (px)', 'kaos-header-footer' ), 'default' => 0, 'min' => 0, 'max' => 150, 'help' => __( 'To be visible, the background on scroll must be semi-transparent.', 'kaos-header-footer' ) ),
					'sat'       => array( 'type' => 'number', 'label' => __( 'Saturation (%)', 'kaos-header-footer' ), 'default' => 100, 'min' => 0, 'max' => 300 ),
					'bright'    => array( 'type' => 'number', 'label' => __( 'Brightness (%)', 'kaos-header-footer' ), 'default' => 100, 'min' => 0, 'max' => 300 ),
					'gray'      => array( 'type' => 'number', 'label' => __( 'Grayscale (%)', 'kaos-header-footer' ), 'default' => 0, 'min' => 0, 'max' => 100 ),
					'glass'     => array(
						'type'    => 'check',
						'label'   => __( 'Liquid glass', 'kaos-header-footer' ),
						'inline'  => __( 'Glass with reflections, shine and depth', 'kaos-header-footer' ),
						'default' => 0,
					),
					'g_tint'    => array( 'type' => 'color', 'label' => __( 'Glass tint', 'kaos-header-footer' ), 'default' => '', 'when' => array( 'glass' => array( '1' ) ), 'help' => __( 'Color with transparency; empty = light white.', 'kaos-header-footer' ) ),
					'g_edge'    => array( 'type' => 'number', 'label' => __( 'Edge reflection', 'kaos-header-footer' ), 'default' => 45, 'min' => 0, 'max' => 100, 'when' => array( 'glass' => array( '1' ) ) ),
					'g_shine'   => array( 'type' => 'number', 'label' => __( 'Shine', 'kaos-header-footer' ), 'default' => 25, 'min' => 0, 'max' => 100, 'when' => array( 'glass' => array( '1' ) ) ),
					'g_grain'   => array( 'type' => 'number', 'label' => __( 'Grain', 'kaos-header-footer' ), 'default' => 0, 'min' => 0, 'max' => 100, 'when' => array( 'glass' => array( '1' ) ) ),
					'g_depth'   => array( 'type' => 'number', 'label' => __( 'Depth (shadow)', 'kaos-header-footer' ), 'default' => 30, 'min' => 0, 'max' => 100, 'when' => array( 'glass' => array( '1' ) ) ),
					'g_refract' => array( 'type' => 'number', 'label' => __( 'Liquid refraction', 'kaos-header-footer' ), 'default' => 0, 'min' => 0, 'max' => 100, 'when' => array( 'glass' => array( '1' ) ), 'help' => __( 'Distorts what is behind it like thick glass. Chromium browsers only (Chrome, Edge, Opera).', 'kaos-header-footer' ) ),
					'g_always'  => array( 'type' => 'check', 'label' => __( 'Also at the top', 'kaos-header-footer' ), 'inline' => __( 'Apply the glass before scrolling too', 'kaos-header-footer' ), 'default' => 0, 'when' => array( 'glass' => array( '1' ) ) ),
				),
			),
			'motion'   => array(
				'label'  => __( 'Animations', 'kaos-header-footer' ),
				'fields' => array(
					'anim'  => array(
						'type'    => 'select',
						'label'   => __( 'Exit / re-entry', 'kaos-header-footer' ),
						'default' => 'slide',
						'options' => array(
							'slide' => __( 'Slide', 'kaos-header-footer' ),
							'fade'  => __( 'Fade', 'kaos-header-footer' ),
							'scale' => __( 'Zoom', 'kaos-header-footer' ),
							'blur'  => __( 'Blur', 'kaos-header-footer' ),
							'flip'  => __( 'Flip', 'kaos-header-footer' ),
						),
						'when'    => array( 'mode' => array( 'smart' ) ),
					),
					'intro' => array(
						'type'    => 'select',
						'label'   => __( 'Entrance when it becomes sticky', 'kaos-header-footer' ),
						'default' => 'none',
						'options' => array(
							'none' => __( 'None', 'kaos-header-footer' ),
							'fade' => __( 'Fade', 'kaos-header-footer' ),
							'drop' => __( 'Soft drop', 'kaos-header-footer' ),
							'zoom' => __( 'Zoom', 'kaos-header-footer' ),
						),
					),
					'dur'   => array( 'type' => 'number', 'label' => __( 'Duration (ms)', 'kaos-header-footer' ), 'default' => 350, 'min' => 0, 'max' => 3000 ),
					'ease'  => array(
						'type'    => 'select',
						'label'   => __( 'Easing', 'kaos-header-footer' ),
						'default' => 'smooth',
						'options' => array(
							'smooth' => __( 'Smooth', 'kaos-header-footer' ),
							'snappy' => __( 'Snappy', 'kaos-header-footer' ),
							'bounce' => __( 'Elastic', 'kaos-header-footer' ),
							'linear' => __( 'Linear', 'kaos-header-footer' ),
						),
					),
				),
			),
			'progress' => array(
				'label'  => __( 'Reading progress', 'kaos-header-footer' ),
				'fields' => array(
					'progress'   => array(
						'type'    => 'select',
						'label'   => __( 'Progress bar', 'kaos-header-footer' ),
						'default' => 'none',
						'options' => array(
							'none'   => __( 'None', 'kaos-header-footer' ),
							'header' => __( 'On the header edge', 'kaos-header-footer' ),
							'top'    => __( 'On the top edge of the screen', 'kaos-header-footer' ),
						),
					),
					'progress_c' => array( 'type' => 'color', 'label' => __( 'Bar color', 'kaos-header-footer' ), 'default' => '#5c00e6', 'when' => array( 'progress' => array( 'header', 'top' ) ) ),
					'progress_h' => array( 'type' => 'number', 'label' => __( 'Bar thickness (px)', 'kaos-header-footer' ), 'default' => 3, 'min' => 1, 'max' => 20, 'when' => array( 'progress' => array( 'header', 'top' ) ) ),
				),
			),
		);

		return $schema;
	}

	/** All fields in a single key => definition list. */
	public static function fields() {
		$all = array();
		foreach ( self::schema() as $group ) {
			$all += $group['fields'];
		}
		return $all;
	}

	public static function defaults() {
		$d = array();
		foreach ( self::fields() as $k => $def ) {
			$d[ $k ] = $def['default'];
		}
		return $d;
	}

	/* ------------------------------------------------------------------ */
	/* Sanificazione e configurazione                                      */
	/* ------------------------------------------------------------------ */

	public static function color( $value ) {
		$value = trim( (string) $value );
		if ( 'transparent' === $value ) {
			return $value;
		}
		if ( sanitize_hex_color( $value ) ) {
			return $value;
		}
		return preg_match( '/^(rgba?|hsla?)\(\s*[\d.\s,%\/-]+\)$/i', $value ) ? $value : '';
	}

	/**
	 * Color or multi-stop CSS gradient (linear, radial, conic). Accepts only harmless
	 * innocui: niente url(), niente punti e virgola, niente graffe.
	 */
	public static function paint( $value ) {
		$value = trim( (string) $value );
		if ( preg_match( '/^(repeating-)?(linear|radial|conic)-gradient\(([a-z0-9#%.,()\s\/-]+)\)$/i', $value ) && false === stripos( $value, 'url' ) && strlen( $value ) < 600 ) {
			return preg_replace( '/\s+/', ' ', $value );
		}
		return self::color( $value );
	}

	public static function is_gradient( $value ) {
		return (bool) preg_match( '/^(repeating-)?(linear|radial|conic)-gradient\(/i', (string) $value );
	}

	/** Chiavi assenti = valore predefinito. */
	public static function sanitize( $raw ) {
		$raw = is_array( $raw ) ? $raw : array();
		$out = array();

		foreach ( self::fields() as $k => $def ) {
			if ( ! array_key_exists( $k, $raw ) ) {
				$out[ $k ] = $def['default'];
				continue;
			}
			$v = $raw[ $k ];

			switch ( $def['type'] ) {
				case 'select':
					$out[ $k ] = is_scalar( $v ) && isset( $def['options'][ (string) $v ] ) ? (string) $v : $def['default'];
					break;
				case 'check':
					$out[ $k ] = empty( $v ) ? 0 : 1;
					break;
				case 'number':
					$out[ $k ] = max( $def['min'], min( $def['max'], (int) $v ) );
					break;
				case 'color':
					$out[ $k ] = self::color( $v );
					break;
				case 'paint':
					$out[ $k ] = self::paint( $v );
					break;
				case 'image':
					$out[ $k ] = esc_url_raw( (string) $v );
					break;
				case 'multi':
					$v         = is_array( $v ) ? $v : array();
					$out[ $k ] = array_values( array_intersect( array_keys( $def['options'] ), $v ) );
					break;
				default: // text.
					$out[ $k ] = trim( preg_replace( '/[{};<]/', '', wp_strip_all_tags( (string) $v ) ) );
			}
		}
		return $out;
	}

	/**
	 * Compact config for the front end: only the values that differ from the default.
	 * The values in $extra are internal (already trusted) and are not filtered.
	 */
	public static function config( $fx, $extra = array() ) {
		$fx  = array_merge( self::defaults(), is_array( $fx ) ? $fx : array() );
		$cfg = array();

		foreach ( self::fields() as $k => $def ) {
			$v = $fx[ $k ];
			if ( '' === $v || $v == $def['default'] ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
				continue;
			}
			$cfg[ $k ] = 'check' === $def['type'] ? (int) $v : $v;
		}

		$cfg         = array_merge( $cfg, $extra );
		$cfg['mode'] = $fx['mode'];
		return $cfg;
	}

	/** A config with no effect needs neither CSS nor JS. */
	public static function has_effects( $cfg ) {
		return $cfg && ( count( $cfg ) > 1 || 'none' !== $cfg['mode'] );
	}

	/* ------------------------------------------------------------------ */
	/* Loading the assets                                                 */
	/* ------------------------------------------------------------------ */

	public static function enabled() {
		return true;
	}

	public static function init() {
		add_action( 'wp', array( __CLASS__, 'queue_global' ) );
		add_action( 'wp_head', array( __CLASS__, 'print_css' ), 98 );
		add_action( 'wp_footer', array( __CLASS__, 'print_tools' ), 4 );
		add_action( 'wp_footer', array( __CLASS__, 'enqueue_script' ), 5 );
	}

	/** Requests loading of CSS and JS; it also works when the element is already in the body. */
	public static function request() {
		self::$wanted = true;
		if ( did_action( 'wp_head' ) ) {
			self::print_css();
		}
	}

	public static function queue_global() {
		if ( is_admin() || is_feed() ) {
			return;
		}
		$s = KHF_Options::settings();
		if ( ! empty( $s['btt'] ) || ! empty( $s['anchor'] ) || ! empty( $s['smooth'] ) ) {
			self::$wanted = true;
		}
	}

	private static function asset( $name, $ext ) {
		$min = KHF_DIR . 'assets/' . $ext . '/' . $name . '.min.' . $ext;
		$src = KHF_DIR . 'assets/' . $ext . '/' . $name . '.' . $ext;
		$use = ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) && is_readable( $min ) ) ? $min : $src;
		return array( $use, KHF_URL . 'assets/' . $ext . '/' . basename( $use ) );
	}

	/** The CSS is small: we print it inline, with no extra request. */
	public static function print_css() {
		if ( ! self::$wanted || self::$css_printed ) {
			return;
		}
		self::$css_printed = true;
		list( $file )      = self::asset( 'fx', 'css' );
		$css               = is_readable( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$z                 = (int) KHF_Options::settings()['z_index'];
		echo '<style id="khf-fx-css">:root{--khf-z:' . $z . '}' . wp_strip_all_tags( $css ) . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public static function enqueue_script() {
		if ( ! self::$wanted ) {
			return;
		}
		list( , $url ) = self::asset( 'fx', 'js' );
		wp_enqueue_script( 'khf-fx', $url, array(), KHF_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );

		$s     = KHF_Options::settings();
		$conf = array(
			'bp'    => array( (int) $s['bp_tablet'], (int) $s['bp_mobile'] ),
			'tools' => array(
				'a' => (int) $s['anchor'],
				'x' => (int) $s['anchor_extra'],
				's' => (int) $s['smooth'],
				'b' => (int) $s['btt'],
				'n' => (int) $s['btt_after'],
			),
		);
		wp_add_inline_script( 'khf-fx', 'window.khfCfg=' . wp_json_encode( $conf ) . ';', 'before' );
	}

	/** "Back to top" button (global settings). */
	public static function print_tools() {
		$s = KHF_Options::settings();
		if ( empty( $s['btt'] ) || KHF_Renderer::footer_controls_btt() ) {
			return;
		}
		$style = sprintf(
			'--bt-s:%dpx;--bt-o:%dpx;--bt-r:%d%%;--bt-bg:%s;--bt-fg:%s',
			(int) $s['btt_size'],
			(int) $s['btt_off'],
			(int) $s['btt_r'],
			esc_attr( $s['btt_bg'] ),
			esc_attr( $s['btt_fg'] )
		);
		echo '<button type="button" class="khf-top' . ( 'left' === $s['btt_pos'] ? ' is-left' : '' ) . '" style="' . $style . '" aria-label="' . esc_attr__( 'Back to top', 'kaos-header-footer' ) . '"><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- valori già sanificati e numerici.
	}
}
