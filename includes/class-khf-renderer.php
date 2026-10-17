<?php
defined( 'ABSPATH' ) || exit;

/**
 * Front end: picks the active template, hides the theme header/footer and prints them.
 */
class KAOSHF_Renderer {

	/** @var array<string,array|null> */
	private static $active = array(
		'header' => null,
		'footer' => null,
	);

	private static $header_printed = false;
	private static $forced         = false;

	public static function init() {
		add_action( 'wp', array( __CLASS__, 'resolve' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_head', array( __CLASS__, 'print_head' ), 99 );
		add_action( 'wp_body_open', array( __CLASS__, 'print_header' ), 5 );
		add_action( 'wp_footer', array( __CLASS__, 'print_footer' ), 5 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
	}

	/* ------------------------------------------------------------------ */
	/* Template selection                                                  */
	/* ------------------------------------------------------------------ */

	private static function is_builder_preview() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['elementor-preview'] );
		// phpcs:enable
	}

	public static function resolve() {
		if ( is_admin() || is_feed() || is_embed() || is_robots() || is_trackback() ) {
			return;
		}
		if ( apply_filters( 'kaoshf_skip', self::is_builder_preview() ) || is_singular( KAOSHF_CPT::POST_TYPE ) ) {
			return;
		}

		$ids = get_posts(
			array(
				'post_type'      => KAOSHF_CPT::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);

		foreach ( $ids as $id ) {
			$o    = KAOSHF_Options::get( $id );
			$kind = $o['type'];
			if ( ! KAOSHF_Conditions::matches( $o ) ) {
				continue;
			}
			$cur = self::$active[ $kind ];
			// The highest priority wins; on a tie, the most recently created template.
			if ( ! $cur || $o['priority'] >= $cur['opts']['priority'] ) {
				self::$active[ $kind ] = array( 'id' => $id, 'opts' => $o );
			}
		}

		// Per-content deactivation.
		if ( is_singular() ) {
			$qid = get_queried_object_id();
			foreach ( array( 'header', 'footer' ) as $kind ) {
				if ( get_post_meta( $qid, '_kaoshf_off_' . $kind, true ) ) {
					self::$active[ $kind ] = null;
				}
			}
		}

		if ( self::$active['footer'] && 'custom' === self::$active['footer']['opts']['btt_mode'] ) {
			KAOSHF_Fx::request();
		}

		if ( self::$active['header'] && KAOSHF_Fx::enabled() ) {
			$cfg                           = self::header_fx( self::$active['header']['opts'] );
			self::$active['header']['fx']  = $cfg;
			if ( KAOSHF_Fx::has_effects( $cfg ) ) {
				KAOSHF_Fx::request();
			}
		}
	}

	/** Header effects configuration, with the adjustments specific to the presets. */
	private static function header_fx( $o ) {
		$fx    = $o['fx'];
		$extra = array();

		if ( 'preset' === $o['source'] ) {
			if ( $fx['overlay'] ) {
				if ( '' === $fx['fg0'] ) {
					$extra['fg0'] = '#ffffff';
				}
				if ( '' === $fx['fg1'] ) {
					$extra['fg1'] = 'var(--khf-fg)';
				}
			}
			// The floating "pill" has its own glass: the outer element stays transparent, so nothing is added for it.
			if ( 'floating' !== $o['preset_header'] && $fx['blur'] > 0 && '' === $fx['bg1'] && empty( $fx['glass'] ) && ! KAOSHF_Fx::is_gradient( $o['color_bg'] ) ) {
				$extra['bg1'] = 'color-mix(in srgb,var(--khf-bg) 82%,transparent)';
			}
			$fx['shrink'] = 0; // The presets handle the height with their own measures.
			if ( $o['logo_scroll_id'] && '' === $fx['logo1'] ) {
				$url = wp_get_attachment_url( $o['logo_scroll_id'] );
				if ( $url ) {
					$extra['logo1'] = $url;
				}
			}
		}
		return KAOSHF_Fx::config( $fx, $extra );
	}

	/** Preview: forces a template (even a draft) ignoring the display rules. */
	public static function force( $id, $opts, $thumb = false ) {
		self::$active         = array( 'header' => null, 'footer' => null );
		self::$header_printed = false;
		self::$forced         = true;

		if ( $thumb && 'footer' === $opts['type'] ) {
			$opts['footer_mode'] = 'normal';
		}
		self::$active[ $opts['type'] ] = array( 'id' => $id, 'opts' => $opts );

		if ( 'footer' === $opts['type'] && 'custom' === $opts['btt_mode'] ) {
			KAOSHF_Fx::request();
		}
		if ( 'header' === $opts['type'] ) {
			$cfg                          = self::header_fx( $opts );
			self::$active['header']['fx'] = $cfg;
			if ( KAOSHF_Fx::has_effects( $cfg ) ) {
				KAOSHF_Fx::request();
			}
		}
	}

	/** Does the active footer handle the "back to top" arrow itself (custom or hidden)? */
	public static function footer_controls_btt() {
		return self::$active['footer'] && 'global' !== self::$active['footer']['opts']['btt_mode'];
	}

	public static function active( $kind ) {
		return self::$active[ $kind ] ?? null;
	}

	/* ------------------------------------------------------------------ */
	/* Asset e CSS dinamico                                                */
	/* ------------------------------------------------------------------ */

	public static function enqueue() {
		if ( ! self::$active['header'] && ! self::$active['footer'] ) {
			return;
		}
		$s    = KAOSHF_Options::settings();
		$min  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$css  = file_exists( KAOSHF_DIR . "assets/css/frontend{$min}.css" ) ? $min : '';
		$js   = file_exists( KAOSHF_DIR . "assets/js/hf{$min}.js" ) ? $min : '';

		wp_enqueue_style( 'khf-frontend', KAOSHF_URL . "assets/css/frontend{$css}.css", array(), KAOSHF_VERSION );
		wp_enqueue_script( 'khf-hf', KAOSHF_URL . "assets/js/hf{$js}.js", array(), KAOSHF_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		self::enqueue_fonts();
		wp_add_inline_script( 'khf-hf', 'window.khfHf=' . wp_json_encode( array( 'content' => $s['content_selector'] ) ) . ';', 'before' );
	}

	/** Loads the chosen fonts: hosted on the site (default), Bunny Fonts or Google Fonts, see the plugin settings. */
	private static function enqueue_fonts() {
		$templates = array();
		foreach ( array( 'header', 'footer' ) as $kind ) {
			if ( self::$active[ $kind ] ) {
				$templates[] = self::$active[ $kind ]['opts'];
			}
		}
		$url = KAOSHF_Fonts::stylesheet_url( KAOSHF_Fonts::collect( $templates ), self::$forced );
		if ( '' !== $url ) {
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_style( 'khf-fonts', $url, array(), null );
		}
	}

	/** Theme selectors to hide, based on the active theme. */
	private static function theme_selectors( $kind ) {
		$slug = strtolower( get_template() );

		$known = array(
			'hello-elementor' => array( '#site-header, .site-header', '#site-footer, .site-footer' ),
			'astra'          => array( '#masthead', '#colophon' ),
			'generatepress'  => array( '#masthead', '.site-footer' ),
			'kadence'        => array( '#masthead', '#colophon' ),
			'oceanwp'        => array( '#site-header', '#footer-widgets, #footer-bottom' ),
			'blocksy'        => array( '#header', '#footer' ),
		);

		$common_header = '.elementor-location-header';
		$common_footer = '.elementor-location-footer';

		if ( isset( $known[ $slug ] ) ) {
			$sel = $known[ $slug ][ 'header' === $kind ? 0 : 1 ];
		} elseif ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			$sel = 'header' === $kind
				? '.wp-site-blocks > header.wp-block-template-part'
				: '.wp-site-blocks > footer.wp-block-template-part';
		} else {
			$sel = 'header' === $kind
				? 'body > header:not(.khf-header), #masthead, #site-header, .site-header, #header, #main-header'
				: 'body > footer:not(.khf-footer), #colophon, #site-footer, .site-footer, #footer, #main-footer';
		}

		return $sel . ', ' . ( 'header' === $kind ? $common_header : $common_footer );
	}

	public static function print_head() {
		$h = self::$active['header'];
		$f = self::$active['footer'];
		if ( ! $h && ! $f ) {
			return;
		}
		$s   = KAOSHF_Options::settings();
		$css = ':root{--khf-z:' . (int) $s['z_index'] . '}';

		if ( $h ) {
			if ( ! empty( $s['hide_theme_header'] ) ) {
				$sel  = '' !== $s['header_selectors'] ? $s['header_selectors'] : self::theme_selectors( 'header' );
				$css .= $sel . '{display:none!important}';
			}
			if ( 'preset' === $h['opts']['source'] ) {
				$file = KAOSHF_DIR . 'assets/css/mobile.css';
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$mobile = is_readable( $file ) ? (string) file_get_contents( $file ) : '';
				$css   .= 'minimal' === $h['opts']['preset_header']
					? $mobile
					: '@media (max-width:' . (int) $s['breakpoint'] . 'px){' . $mobile . '}';
			}
		}

		if ( $f && ! empty( $s['hide_theme_footer'] ) ) {
			$sel  = '' !== $s['footer_selectors'] ? $s['footer_selectors'] : self::theme_selectors( 'footer' );
			$css .= $sel . '{display:none!important}';
		}

		echo "<script>document.documentElement.classList.add('khf-js');</script>\n";
		echo '<style id="khf-dynamic">' . wp_strip_all_tags( $css ) . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public static function body_class( $classes ) {
		if ( self::$active['header'] ) {
			$o         = self::$active['header']['opts'];
			$classes[] = 'khf-has-header';
			$classes[] = 'khf-header-' . $o['fx']['mode'];
			if ( $o['fx']['overlay'] ) {
				$classes[] = 'khf-header-overlay';
			}
		}
		if ( self::$active['footer'] ) {
			$classes[] = 'khf-has-footer';
			$classes[] = 'khf-footer-' . self::$active['footer']['opts']['footer_mode'];
		}
		return $classes;
	}

	/* ------------------------------------------------------------------ */
	/* Output                                                              */
	/* ------------------------------------------------------------------ */

	public static function print_header() {
		if ( self::$active['header'] && ! self::$header_printed ) {
			self::$header_printed = true;
			echo self::render( 'header' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup built with escaping functions.
		}
	}

	public static function print_footer() {
		// The theme does not call wp_body_open(): we print the header here and move it to the top of the body with JS.
		if ( self::$active['header'] && ! self::$header_printed && ! did_action( 'wp_body_open' ) ) {
			self::$header_printed = true;
			echo self::render( 'header', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "<script>(function(){var e=document.getElementById('khf-header');if(e){document.body.insertBefore(e,document.body.firstChild);}})();</script>\n";
		}
		if ( self::$active['footer'] ) {
			echo self::render( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$fo = self::$active['footer']['opts'];
			if ( 'custom' === $fo['btt_mode'] && 'fixed' === $fo['fb_place'] ) {
				echo KAOSHF_Presets::btt( $fo ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}

	/** Variabili CSS (colori/dimensioni) dell'elemento. */
	private static function vars( $kind, $o ) {
		$moods = KAOSHF_Presets::moods();
		$mood  = $moods[ $o['mood'] ] ?? reset( $moods );
		$v     = array();

		if ( 'header' === $kind ) {
			$builder = 'builder' === $o['source'];
			$bg      = $o['color_bg'] ? $o['color_bg'] : ( $builder ? 'transparent' : $mood['hbg'] );
			$solid   = KAOSHF_Fx::is_gradient( $bg ) ? $mood['hbg'] : ( 'transparent' === $bg ? '#ffffff' : $bg );
			$fg      = $o['color_fg'] ? $o['color_fg'] : $mood['hfg'];
			$accent  = $o['color_accent'] ? $o['color_accent'] : $mood['accent'];
			$v       = array(
				'--khf-bg'        => $bg,
				'--khf-bg-solid'  => $solid,
				'--khf-fg'        => $fg,
				'--khf-accent'    => $accent,
				'--khf-accent-fg' => KAOSHF_Presets::contrast( $accent ),
				'--khf-h'         => (int) $o['height'] . 'px',
				'--khf-hs'        => (int) $o['height_shrunk'] . 'px',
			);
		} else {
			$builder = 'builder' === $o['source'];
			$bg      = $o['color_bg'] ? $o['color_bg'] : ( $builder ? 'transparent' : $mood['fbg'] );
			$bg2     = $o['color_bg2'] ? $o['color_bg2'] : ( $builder ? $bg : $mood['fbg2'] );
			$custom  = KAOSHF_Fx::is_gradient( $bg ); // gradiente scelto dall'utente: sostituisce quello automatico.
			$fg      = $o['color_fg'] ? $o['color_fg'] : $mood['ffg'];
			$accent  = $o['color_accent'] ? $o['color_accent'] : $mood['accent'];
			$v       = array(
				'--khf-bg'        => $custom ? $mood['fbg'] : $bg,
				'--khf-bg2'       => KAOSHF_Fx::is_gradient( $bg2 ) ? $mood['fbg2'] : $bg2,
				'--khf-fbg'       => $custom ? $bg : 'linear-gradient(' . (int) $o['grad_angle'] . 'deg,' . $bg . ',' . $bg2 . ')',
				'--khf-fg'        => $fg,
				'--khf-accent'    => $accent,
				'--khf-accent-fg' => KAOSHF_Presets::contrast( $accent ),
				'--khf-cpad'      => (int) $o['cell_pad'] . 'px',
				'--khf-cr'        => (int) $o['cell_r'] . 'px',
				'--khf-cblur'     => (int) $o['cell_blur'] . 'px',
				'--khf-cgap'      => (int) $o['grid_gap'] . 'px',
				'--khf-bargap'    => (int) $o['bar_gap'] . 'px',
				'--khf-mgap'      => (int) $o['menu_gap'] . 'px',
			);
			if ( $o['headline_fs'] > 0 ) {
				$v['--khf-hfs'] = (int) $o['headline_fs'] . 'px';
			}
			if ( '' !== $o['headline_fw'] ) {
				$v['--khf-hfw'] = (int) $o['headline_fw'];
			}
			list( $hstack ) = KAOSHF_Presets::font_source( $o['headline_ff'], '' );
			if ( '' !== $hstack ) {
				$v['--khf-hff'] = $hstack;
			}
			if ( '' !== $o['cell_tint'] ) {
				$v['--khf-ctint'] = $o['cell_tint'];
			}
			if ( $o['menu_fs'] > 0 ) {
				$v['--khf-mfs'] = (int) $o['menu_fs'] . 'px';
			}
			if ( '' !== $o['menu_fw'] ) {
				$v['--khf-mfw'] = (int) $o['menu_fw'];
			}
		}

		$map = 'header' === $kind
			? array( 'c_menu' => '--khf-menu', 'c_menu_h' => '--khf-menu-h', 'c_pill' => '--khf-pill', 'c_cta_bg' => '--khf-cta-bg', 'c_cta_fg' => '--khf-cta-fg', 'c_burger' => '--khf-burger', 'c_sub_bg' => '--khf-sub-bg', 'c_sub_fg' => '--khf-sub-fg', 'c_panel_bg' => '--khf-panel-bg', 'c_panel_fg' => '--khf-panel-fg', 'c_burger_bg' => '--khf-bbg', 'menu_fx_c' => '--khf-mfx-c', 'c_sep' => '--khf-sep', 'c_mega_head' => '--khf-mh', 'c_mega_line' => '--khf-mline' )
			: array( 'c_link' => '--khf-link', 'c_link_h' => '--khf-link-h', 'c_heading' => '--khf-heading', 'c_line' => '--khf-line', 'c_cta_bg' => '--khf-cta-bg', 'c_cta_fg' => '--khf-cta-fg' );
		foreach ( $map as $key => $var ) {
			if ( '' !== $o[ $key ] ) {
				$v[ $var ] = $o[ $key ];
			}
		}
		$v['--khf-logo-h'] = (int) $o['logo_h'] . 'px';
		list( $stack ) = KAOSHF_Presets::font_source( $o['font_family'], $o['font_custom'] );
		list( $head )  = KAOSHF_Presets::font_source( $o['font_head'], $o['font_head_custom'] );
		if ( '' !== $stack ) {
			$v['--khf-ff'] = $stack;
		}
		if ( '' !== $head ) {
			$v['--khf-fh'] = $head;
		}
		if ( $o['font_size'] > 0 ) {
			$v['--khf-fsz'] = (int) $o['font_size'] . 'px';
		}
		if ( 'header' === $kind ) {
			$radius            = array( 'none' => '0', 'circle' => '50%', 'rounded' => '12px', 'square' => '0' );
			$v['--khf-bsize']  = (int) $o['burger_size'] . 'px';
			$v['--khf-bw']     = (int) $o['burger_w'] . 'px';
			$v['--khf-bradius'] = $radius[ $o['burger_shape'] ];
			$v['--khf-mfx-d']  = (int) $o['mfx_ms'] . 'ms';
			$v['--khf-hi']     = (int) $o['hover_img_size'] . 'px';
			$v['--khf-zgap']   = (int) $o['zone_gap'] . 'px';
			$v['--khf-zcgap']  = (int) $o['zone_cgap'] . 'px';
			$v['--khf-lgap']   = (int) $o['logo_gap'] . 'px';
			$v['--khf-mwid']   = (int) $o['hover_img_w'] . '%';
			$v['--khf-mcols']  = (int) $o['menu_cols'];
			if ( $o['panel_fs'] > 0 ) {
				$v['--khf-pfs'] = (int) $o['panel_fs'] . 'px';
			}
			if ( '' !== $o['panel_fw'] ) {
				$v['--khf-pfw'] = (int) $o['panel_fw'];
			}
			$v['--khf-mgap']   = (int) $o['menu_gap'] . 'px';
			$v['--khf-mw']     = (int) $o['mega_w'] . 'px';
			if ( $o['menu_fs'] > 0 ) {
				$v['--khf-mfs'] = (int) $o['menu_fs'] . 'px';
			}
			if ( '' !== $o['menu_fw'] ) {
				$v['--khf-mfw'] = (int) $o['menu_fw'];
			}
			if ( $o['wrap_w'] > 0 ) {
				$v['--khf-wrapw'] = (int) $o['wrap_w'] . 'px';
			}
			$v['--khf-pw']     = (int) $o['panel_w'] . ( '%' === $o['panel_wu'] ? 'vw' : 'px' );
			$v['--khf-ph']     = $o['panel_h'] > 0 ? (int) $o['panel_h'] . $o['panel_hu'] : 'auto';
			$v['--khf-pr']     = (int) $o['panel_r'] . 'px';
			$v['--khf-po']     = (int) $o['panel_off'] . 'px';
			$v['--khf-pdim']   = round( (int) $o['panel_dim'] / 100, 2 );
		}
		if ( 'footer' === $kind ) {
			$v['--khf-angle'] = (int) $o['grad_angle'] . 'deg';
		}

		$out = '';
		foreach ( $v as $k => $val ) {
			$out .= $k . ':' . $val . ';';
		}
		return $out;
	}

	private static function render( $kind, $move = false ) {
		$t = self::$active[ $kind ];
		if ( ! $t ) {
			return '';
		}
		$o       = $t['opts'];
		$builder = 'builder' === $o['source'];
		$classes = array( 'khf-' . $kind, 'khf-source-' . $o['source'] );
		$attrs   = array();
		$spacer  = '';

		if ( 'header' === $kind ) {
			$classes[] = 'khf-h--' . ( $builder ? 'builder' : $o['preset_header'] );
			if ( ! $builder && $o['p_shrink'] ) {
				$classes[] = 'has-shrink';
			}
			if ( ! $builder ) {
				$classes[] = 'khf-mfx-' . $o['menu_fx'];
				$classes[] = 'khf-sep-' . $o['menu_sep'];
				$classes[] = 'khf-sub-' . $o['sub_style'];
				if ( 'none' !== $o['hover_img'] ) {
					$classes[] = 'khf-hm-' . $o['hover_img_pos'];
					if ( $o['hover_img_bleed'] ) {
						$classes[] = 'khf-hm-bleed';
					}
				}
				$classes[] = 'khf-ma-' . $o['panel_m_al'];
				if ( 'panel' === $o['hover_img'] && 'bg' === $o['panel_m_img'] ) {
					$classes[] = 'khf-mi-bg';
				}
				if ( $o['caret'] ) {
					$classes[] = 'khf-has-caret';
				}
				if ( $o['menu_up'] ) {
					$classes[] = 'khf-m-up';
				}
				if ( 'center' === $o['zone_align'] ) {
					$classes[] = 'khf-za-center';
				}
				if ( 'custom' === $o['panel_mode'] ) {
					$classes[] = 'khf-pn-custom';
					$classes[] = 'khf-pn-' . $o['panel_pos'];
					$classes[] = $o['panel_h'] > 0 ? 'khf-pn-hset' : 'khf-pn-hfull';
				}
				if ( in_array( $o['preset_header'], array( 'split', 'custom' ), true ) ) {
					$classes[] = 'khf-h--zones';
				}
			}

			// Scroll effects: classes and modes are already in the markup, so the first paint is correct.
			$cfg = $t['fx'] ?? null;
			if ( $cfg && KAOSHF_Fx::has_effects( $cfg ) ) {
				$classes[]          = 'khf-fx';
				$attrs['data-mode'] = $cfg['mode'];
				$attrs['data-khf-fx'] = wp_json_encode( $cfg );
				if ( ! $builder && 'floating' === $o['preset_header'] ) {
					$attrs['data-vt'] = 'bar'; // shadow, glass and background apply to the pill, not to the full width.
				}
				if ( ! empty( $cfg['overlay'] ) ) {
					$classes[] = 'fx-ov';
				} elseif ( 'none' !== $cfg['mode'] && ! $move && ! $builder ) {
					$gap    = 'floating' === $o['preset_header'] ? 14 : 0;
					$spacer = '<div class="khf-spacer" aria-hidden="true" style="height:' . ( (int) $o['height'] + $gap ) . 'px"></div>';
				}
			}
			$tag = 'header';
		} else {
			$classes[] = 'khf-f--' . ( $builder ? 'builder' : $o['preset_footer'] );
			if ( $o['animated_bg'] ) {
				$classes[] = 'khf-anim';
			}
			$classes[] = 'khf-fmfx-' . $o['fmenu_fx'];
			$classes[] = 'khf-sep-' . $o['menu_sep'];
			if ( $o['menu_up'] ) {
				$classes[] = 'khf-m-up';
			}
			$attrs['data-mode'] = $o['footer_mode'];
			if ( 'none' !== $o['footer_fx'] ) {
				$attrs['data-fx'] = $o['footer_fx'];
			}
			$classes[]          = 'khf-ma-' . $o['footer_m_al'];
			$tag                = 'footer';
		}

		if ( $builder ) {
			$content = self::builder_content( $t['id'] );
			if ( self::$forced && '' === trim( wp_strip_all_tags( $content ) ) && false === strpos( $content, '<img' ) ) {
				$content = '<div style="padding:28px;text-align:center;font:14px system-ui;color:#9aa1ab;border:2px dashed #cfd3d9;margin:12px">' . esc_html__( 'Empty template: press "Edit with Elementor" to design it.', 'kaos-header-footer' ) . '</div>';
			}
			$inner = 'footer' === $kind
				? '<div class="khf-footer__inner khf-builder">' . $content . '</div>'
				: '<div class="khf-builder">' . $content . '</div>';
		} else {
			$inner = 'header' === $kind ? KAOSHF_Presets::render_header( $o ) : KAOSHF_Presets::render_footer( $o );
		}

		$html = '<' . $tag . ' id="khf-' . esc_attr( $kind ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( self::vars( $kind, $o ) ) . '"';
		foreach ( $attrs as $k => $val ) {
			$html .= ' ' . esc_attr( $k ) . '="' . esc_attr( $val ) . '"';
		}
		return $html . '>' . $inner . '</' . $tag . '>' . $spacer;
	}

	/** Template content when the source is a builder (Elementor, Divi, Gutenberg). */
	public static function builder_content( $id ) {
		static $busy = array();
		if ( isset( $busy[ $id ] ) ) {
			return '';
		}
		$busy[ $id ] = true;
		$html        = '';

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->documents ) ) {
			$doc = \Elementor\Plugin::$instance->documents->get( $id );
			if ( $doc && $doc->is_built_with_elementor() ) {
				$html = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, true );
			}
		}

		if ( '' === $html ) {
			global $post;
			$prev = $post;
			$tpl  = get_post( $id );
			if ( $tpl ) {
				$post = $tpl; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- needed for Divi/shortcodes, restored below.
				setup_postdata( $post );
				$html = apply_filters( 'the_content', $tpl->post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core filter.
				$post = $prev; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				if ( $prev ) {
					setup_postdata( $prev );
				} else {
					wp_reset_postdata();
				}
			}
		}

		unset( $busy[ $id ] );
		return $html;
	}
}
