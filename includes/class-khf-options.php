<?php
defined( 'ABSPATH' ) || exit;

/**
 * Default values, reading and sanitization of the options (per template and global).
 */
class KHF_Options {

	const META_KEY     = '_khf_opts';
	const SETTINGS_KEY = 'khf_settings';

	public static function defaults() {
		return array(
			'type'          => 'header',
			'source'        => 'preset',
			'preset_header' => 'classic',
			'preset_footer' => 'columns',
			'mood'          => 'sunset',

			// Condizioni.
			'cond_rules'    => array(),
			'priority'      => 10,

			// Scroll effects (schema in KHF_Fx).
			'fx'            => array_merge(
				KHF_Fx::defaults(),
				array(
					'mode'   => 'fixed',
					'shadow' => 'medium',
					'blur'   => 14,
					'sat'    => 140,
				)
			),

			// Header preset measures.
			'height'        => 84,
			'height_shrunk' => 60,
			'p_shrink'      => 1,

			// Footer.
			'footer_mode'   => 'reveal',
			'footer_fx'     => 'none',
			'footer_m_al'   => 'l',
			'animated_bg'   => 0,

			// Footer big headline.
			'headline_fs'   => 0,
			'headline_fw'   => '',
			'headline_al'   => 'l',
			'headline_ff'   => '',

			// Footer top divider.
			'divider'       => 'wave',
			'divider_h'     => 60,
			'divider_c'     => '',
			'divider_op'    => 100,
			'divider_flip'  => 0,

			// Custom grid (rows x columns) and cell style.
			'grid_cols'     => 4,
			'grid_rows'     => 1,
			'grid_gap'      => 28,
			'grid_valign'   => 'start',
			'grid_hero'     => 1,
			'cell_style'    => 'none',
			'cell_r'        => 18,
			'cell_pad'      => 22,
			'cell_tint'     => '',
			'cell_blur'     => 16,
			'grid_cells'    => array(),

			// Zoned bottom bar and footer links.
			'bar_layout'    => array(),
			'bar_gap'       => 24,
			'fmenu_fx'      => 'color',

			// Footer "back to top" arrow.
			'btt_mode'      => 'global',
			'fb_place'      => 'fixed',
			'fb_pos'        => 'r',
			'fb_x'          => 22,
			'fb_y'          => 22,
			'fb_icon'       => 'arrow',
			'fb_text'       => '',
			'fb_size'       => 46,
			'fb_r'          => 50,
			'fb_bg'         => '',
			'fb_fg'         => '',
			'fb_bd'         => '',
			'fb_after'      => 500,
			'fb_hover'      => 'lift',

			// Contenuti (preset).
			'logo_id'       => 0,
			'logo_text'     => '',
			'menu'          => 0,
			'menu2'         => 0,
			'menu3'         => 0,
			'cta_text'      => '',
			'cta_url'       => '',
			'tagline'       => '',
			'headline'      => '',
			'socials'       => '',
			'email'         => '',
			'phone'         => '',
			'address'       => '',
			'address_map'   => 1,
			'address_url'   => '',
			'copyright'     => '',

			// Custom colors (empty = use the mood).
			'color_bg'      => '',
			'color_bg2'     => '',
			'color_fg'      => '',
			'color_accent'  => '',

			// Detail colors of the presets and shape (empty = default).
			'c_menu'        => '',
			'c_menu_h'      => '',
			'c_pill'        => '',
			'c_cta_bg'      => '',
			'c_cta_fg'      => '',
			'c_burger'      => '',
			'c_sub_bg'      => '',
			'c_sub_fg'      => '',
			'c_panel_bg'    => '',
			'c_panel_fg'    => '',
			'c_link'        => '',
			'c_link_h'      => '',
			'c_heading'     => '',
			'c_line'        => '',
			'logo_h'        => 40,
			'logo_scroll_id' => 0,

			// Menu: effetto al passaggio, icona burger e immagini.
			'menu_fx'       => 'pill',
			'menu_fx_c'     => '',
			'mfx_ms'        => 300,
			'burger'        => 'lines',
			'burger_size'   => 44,
			'burger_w'      => 2,
			'burger_shape'  => 'none',
			'c_burger_bg'   => '',
			'burger_label'  => '',
			'burger_label_open' => '',
			'hover_img'     => 'none',
			'hover_img_size' => 280,
			'menu_images'   => array(),
			'custom_layout' => array(),

			// Hover media (panel) and full-screen menu content.
			'hover_img_pos'   => 'left',
			'hover_img_w'     => 40,
			'hover_img_bleed' => 1,
			'panel_m_al'      => 'c',
			'panel_m_img'     => 'bg',
			'menu_cols'       => 1,
			'panel_fs'        => 0,
			'panel_fw'        => '',
			'panel_info'      => 0,
			'panel_t_menu'    => '',
			'panel_t_contact' => '',
			'panel_t_social'  => '',
			'panel_legal'     => '',
			'panel_legal_url' => '',

			// Spacing, menu items and submenus.
			'zone_align'    => 'edges',
			'logo_gap'      => 40,
			'menu_gap'      => 4,
			'menu_sep'      => 'none',
			'c_sep'         => '',
			'menu_fs'       => 0,
			'menu_fw'       => '',
			'menu_up'       => 0,
			'wrap_w'        => 0,
			'sub_style'     => 'dropdown',
			'mega_w'        => 1000,
			'caret'         => 1,
			'c_mega_head'   => '',
			'c_mega_line'   => '',

			// Menu window opened with the burger.
			'panel_mode'    => 'full',
			'panel_pos'     => 'right',
			'panel_w'       => 420,
			'panel_wu'      => 'px',
			'panel_h'       => 0,
			'panel_hu'      => 'vh',
			'panel_r'       => 24,
			'panel_off'     => 0,
			'panel_dim'     => 40,
			'zone_gap'      => 24,
			'zone_cgap'     => 24,
			'social_style'  => 'icons',
			'font_family'   => '',
			'font_custom'   => '',
			'font_head'     => '',
			'font_head_custom' => '',
			'font_size'     => 0,
			'grad_angle'    => 160,
		);
	}

	public static function get( $post_id ) {
		$saved = get_post_meta( $post_id, self::META_KEY, true );
		$saved = is_array( $saved ) ? $saved : array();
		$o     = wp_parse_args( $saved, self::defaults() );
		// Partially saved effects (e.g. after an update) are completed with the defaults.
		$o['fx'] = array_merge( self::defaults()['fx'], is_array( $saved['fx'] ?? null ) ? $saved['fx'] : array() );
		if ( ! isset( $saved['divider'] ) ) {
			$o['divider'] = ( $saved['wave'] ?? 1 ) ? 'wave' : 'none'; // old "wavy edge" switch
		}
		if ( ! is_array( $saved['cond_rules'] ?? null ) ) {
			$o['cond_rules'] = self::legacy_rules( $saved );
		}
		return $o;
	}

	/** Converts the old conditions (entire site / types / IDs / exclusions) into the new rules. */
	private static function legacy_rules( $saved ) {
		$rules = array();
		if ( isset( $saved['cond_all'] ) && empty( $saved['cond_all'] ) ) {
			foreach ( (array) ( $saved['cond_types'] ?? array() ) as $t ) {
				$rules[] = array( 'm' => 'only', 't' => (string) $t, 'id' => 0, 'title' => '' );
			}
			foreach ( array_filter( array_map( 'absint', explode( ',', (string) ( $saved['cond_ids'] ?? '' ) ) ) ) as $id ) {
				$rules[] = array( 'm' => 'only', 't' => 'id', 'id' => $id, 'title' => get_the_title( $id ) );
			}
		}
		foreach ( array_filter( array_map( 'absint', explode( ',', (string) ( $saved['excl_ids'] ?? '' ) ) ) ) as $id ) {
			$rules[] = array( 'm' => 'except', 't' => 'id', 'id' => $id, 'title' => get_the_title( $id ) );
		}
		return $rules;
	}

	/** Footer grid cells. */
	private static function cells( $raw ) {
		$types = array( 'empty', 'text', 'menu', 'contacts', 'socials', 'image', 'logo', 'cta' );
		$tags  = array( 'br' => array(), 'strong' => array(), 'em' => array(), 'b' => array(), 'i' => array(), 'p' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(), 'span' => array(), 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) );
		$out   = array();
		foreach ( array_slice( (array) $raw, 0, 36 ) as $c ) {
			$c     = is_array( $c ) ? $c : array();
			$font  = self::font_ok( $c['ff'] ?? '' );
			$out[] = array(
				't'     => in_array( $c['t'] ?? '', $types, true ) ? $c['t'] : 'empty',
				'title' => sanitize_text_field( $c['title'] ?? '' ),
				'text'  => wp_kses( (string) ( $c['text'] ?? '' ), $tags ),
				'menu'  => absint( $c['menu'] ?? 0 ),
				'img'   => esc_url_raw( (string) ( $c['img'] ?? '' ) ),
				'link'  => esc_url_raw( (string) ( $c['link'] ?? '' ) ),
				'al'    => in_array( $c['al'] ?? '', array( 'l', 'c', 'r' ), true ) ? $c['al'] : 'l',
				'ff'    => 'custom' === $font ? '' : $font,
				'fs'    => max( 0, min( 90, (int) ( $c['fs'] ?? 0 ) ) ),
				'fw'    => self::enum( (string) ( $c['fw'] ?? '' ), array( '', '300', '400', '500', '600', '700', '800' ), '' ),
				'c'     => KHF_Fx::color( $c['c'] ?? '' ),
			);
		}
		return $out;
	}

	/** Barra inferiore: elemento => acceso, zona, ordine, spostamento. */
	private static function bar( $raw ) {
		$out = array();
		foreach ( array( 'logo', 'menu', 'menu2', 'social', 'copy' ) as $el ) {
			$row = $raw[ $el ] ?? null;
			if ( is_array( $row ) ) {
				$out[ $el ] = array(
					'on' => empty( $row['on'] ) ? 0 : 1,
					'z'  => in_array( $row['z'] ?? '', array( 'l', 'c', 'r' ), true ) ? $row['z'] : 'c',
					'o'  => max( -9, min( 9, (int) ( $row['o'] ?? 0 ) ) ),
					'dx' => max( -800, min( 800, (int) ( $row['dx'] ?? 0 ) ) ),
				);
			}
		}
		return $out;
	}

	private static function font_ok( $value ) {
		return in_array( $value, array_merge( array_keys( KHF_Presets::fonts() ), array( 'custom' ) ), true ) ? $value : '';
	}

	private static function font_name( $value ) {
		return trim( preg_replace( '/[^A-Za-z0-9 \-]/', '', (string) $value ) );
	}

	private static function enum( $value, $allowed, $fallback ) {
		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}

	private static function ids( $value ) {
		$ids = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
		return implode( ',', array_unique( $ids ) );
	}

	public static function sanitize( $raw ) {
		$d   = self::defaults();
		$raw = is_array( $raw ) ? $raw : array();
		$o   = array();

		$o['type']          = self::enum( $raw['type'] ?? '', array( 'header', 'footer' ), $d['type'] );
		$o['source']        = self::enum( $raw['source'] ?? '', array( 'preset', 'builder' ), $d['source'] );
		$o['preset_header'] = self::enum( $raw['preset_header'] ?? '', array_keys( KHF_Presets::presets( 'header' ) ), $d['preset_header'] );
		$o['preset_footer'] = self::enum( $raw['preset_footer'] ?? '', array_keys( KHF_Presets::presets( 'footer' ) ), $d['preset_footer'] );
		$o['mood']          = self::enum( $raw['mood'] ?? '', array_keys( KHF_Presets::moods() ), $d['mood'] );
		$o['footer_fx']     = self::enum( $raw['footer_fx'] ?? '', array( 'none', 'zoom-in', 'zoom-out', 'zoom-top', 'zoom-bottom', 'zoom-left', 'zoom-right', 'fade-up' ), $d['footer_fx'] );
		$o['footer_m_al']   = self::enum( $raw['footer_m_al'] ?? '', array( 'l', 'c', 'r' ), $d['footer_m_al'] );
		$o['footer_mode']   = self::enum( $raw['footer_mode'] ?? '', array( 'normal', 'reveal', 'reveal-parallax' ), $d['footer_mode'] );

		foreach ( array( 'p_shrink', 'divider_flip', 'grid_hero', 'animated_bg', 'menu_up', 'caret', 'hover_img_bleed', 'panel_info', 'address_map' ) as $k ) {
			$o[ $k ] = empty( $raw[ $k ] ) ? 0 : 1;
		}

		$o['fx']            = KHF_Fx::sanitize( $raw['fx'] ?? array() );
		$o['logo_h']        = max( 16, min( 200, (int) ( $raw['logo_h'] ?? $d['logo_h'] ) ) );
		$o['grad_angle']    = max( 0, min( 360, (int) ( $raw['grad_angle'] ?? $d['grad_angle'] ) ) );
		$o['menu_fx']       = self::enum( $raw['menu_fx'] ?? '', array_keys( KHF_Presets::menu_effects() ), $d['menu_fx'] );
		$o['burger']        = self::enum( $raw['burger'] ?? '', array_keys( KHF_Presets::burgers() ), $d['burger'] );
		$o['burger_shape']  = self::enum( $raw['burger_shape'] ?? '', array( 'none', 'circle', 'rounded', 'square' ), $d['burger_shape'] );
		$o['hover_img']     = self::enum( $raw['hover_img'] ?? '', array( 'none', 'panel', 'cursor' ), $d['hover_img'] );
		$o['mfx_ms']        = max( 100, min( 1200, (int) ( $raw['mfx_ms'] ?? $d['mfx_ms'] ) ) );
		$o['burger_size']   = max( 28, min( 80, (int) ( $raw['burger_size'] ?? $d['burger_size'] ) ) );
		$o['burger_w']      = max( 1, min( 8, (int) ( $raw['burger_w'] ?? $d['burger_w'] ) ) );
		$o['hover_img_size'] = max( 120, min( 600, (int) ( $raw['hover_img_size'] ?? $d['hover_img_size'] ) ) );
		$o['panel_m_al']    = self::enum( $raw['panel_m_al'] ?? '', array( 'l', 'c', 'r' ), $d['panel_m_al'] );
		$o['panel_m_img']   = self::enum( $raw['panel_m_img'] ?? '', array( 'bg', 'none' ), $d['panel_m_img'] );
		$o['panel_mode']    = self::enum( $raw['panel_mode'] ?? '', array( 'full', 'custom' ), $d['panel_mode'] );
		$o['panel_pos']     = self::enum( $raw['panel_pos'] ?? '', array( 'left', 'right' ), $d['panel_pos'] );
		$o['panel_wu']      = self::enum( $raw['panel_wu'] ?? '', array( 'px', '%' ), $d['panel_wu'] );
		$o['panel_hu']      = self::enum( $raw['panel_hu'] ?? '', array( 'px', 'vh' ), $d['panel_hu'] );
		$o['panel_w']       = max( 200, min( 'px' === $o['panel_wu'] ? 1600 : 100, (int) ( $raw['panel_w'] ?? $d['panel_w'] ) ) );
		$o['panel_h']       = max( 0, min( 'px' === $o['panel_hu'] ? 2400 : 100, (int) ( $raw['panel_h'] ?? $d['panel_h'] ) ) );
		$o['panel_r']       = max( 0, min( 120, (int) ( $raw['panel_r'] ?? $d['panel_r'] ) ) );
		$o['panel_off']     = max( 0, min( 120, (int) ( $raw['panel_off'] ?? $d['panel_off'] ) ) );
		$o['panel_dim']     = max( 0, min( 90, (int) ( $raw['panel_dim'] ?? $d['panel_dim'] ) ) );
		$o['zone_align']    = self::enum( $raw['zone_align'] ?? '', array( 'edges', 'center' ), $d['zone_align'] );
		$o['menu_sep']      = self::enum( $raw['menu_sep'] ?? '', array( 'none', 'bar', 'dot', 'slash', 'dash' ), $d['menu_sep'] );
		$o['sub_style']     = self::enum( $raw['sub_style'] ?? '', array( 'dropdown', 'mega' ), $d['sub_style'] );
		$o['menu_fw']       = self::enum( (string) ( $raw['menu_fw'] ?? '' ), array( '', '300', '400', '500', '600', '700', '800' ), '' );
		$o['logo_gap']      = max( 0, min( 300, (int) ( $raw['logo_gap'] ?? $d['logo_gap'] ) ) );
		$o['menu_gap']      = max( 0, min( 80, (int) ( $raw['menu_gap'] ?? $d['menu_gap'] ) ) );
		$o['menu_fs']       = max( 0, min( 40, (int) ( $raw['menu_fs'] ?? $d['menu_fs'] ) ) );
		$o['wrap_w']        = max( 0, min( 2800, (int) ( $raw['wrap_w'] ?? $d['wrap_w'] ) ) );
		$o['mega_w']        = max( 400, min( 2000, (int) ( $raw['mega_w'] ?? $d['mega_w'] ) ) );
		$o['hover_img_pos'] = self::enum( $raw['hover_img_pos'] ?? '', array( 'left', 'right' ), $d['hover_img_pos'] );
		$o['hover_img_w']   = max( 20, min( 60, (int) ( $raw['hover_img_w'] ?? $d['hover_img_w'] ) ) );
		$o['panel_fs']      = max( 0, min( 90, (int) ( $raw['panel_fs'] ?? $d['panel_fs'] ) ) );
		$o['panel_fw']      = self::enum( (string) ( $raw['panel_fw'] ?? '' ), array( '', '300', '400', '500', '600', '700', '800' ), '' );
		$o['menu_cols']     = max( 1, min( 3, (int) ( $raw['menu_cols'] ?? $d['menu_cols'] ) ) );
		foreach ( array( 'panel_t_menu', 'panel_t_contact', 'panel_t_social', 'panel_legal' ) as $k ) {
			$o[ $k ] = sanitize_text_field( $raw[ $k ] ?? '' );
		}
		$o['panel_legal_url'] = esc_url_raw( $raw['panel_legal_url'] ?? '' );
		$o['zone_gap']      = max( 0, min( 300, (int) ( $raw['zone_gap'] ?? $d['zone_gap'] ) ) );
		$o['zone_cgap']     = max( 0, min( 600, (int) ( $raw['zone_cgap'] ?? $d['zone_cgap'] ) ) );
		$o['social_style']  = self::enum( $raw['social_style'] ?? '', array( 'icons', 'text', 'both' ), $d['social_style'] );
		$o['font_family']   = self::font_ok( $raw['font_family'] ?? '' );
		$o['font_head']     = self::font_ok( $raw['font_head'] ?? '' );
		$o['font_custom']   = self::font_name( $raw['font_custom'] ?? '' );
		$o['font_head_custom'] = self::font_name( $raw['font_head_custom'] ?? '' );
		$o['font_size']     = max( 0, min( 40, (int) ( $raw['font_size'] ?? 0 ) ) );

		// Disposizione personalizzata: elemento => zona (l/c/r) e ordine.
		$o['custom_layout'] = array();
		foreach ( array( 'logo', 'menu', 'menu2', 'cta' ) as $el ) {
			$row = $raw['custom_layout'][ $el ] ?? null;
			if ( is_array( $row ) ) {
				$o['custom_layout'][ $el ] = array(
					'z' => in_array( $row['z'] ?? '', array( 'l', 'c', 'r' ), true ) ? $row['z'] : 'c',
					'o'  => max( -9, min( 9, (int) ( $row['o'] ?? 0 ) ) ),
					'dx' => max( -800, min( 800, (int) ( $row['dx'] ?? 0 ) ) ),
				);
			}
		}

		$o['logo_scroll_id'] = absint( $raw['logo_scroll_id'] ?? 0 );
		$o['burger_label']      = sanitize_text_field( $raw['burger_label'] ?? '' );
		$o['burger_label_open'] = sanitize_text_field( $raw['burger_label_open'] ?? '' );

		// Hover images: menu item id => URL.
		$o['menu_images'] = array();
		foreach ( array_slice( (array) ( $raw['menu_images'] ?? array() ), 0, 80, true ) as $item_id => $url ) {
			$item_id = absint( $item_id );
			$url     = esc_url_raw( (string) $url );
			if ( $item_id && $url ) {
				$o['menu_images'][ $item_id ] = $url;
			}
		}

		$o['headline_fs']  = max( 0, min( 200, (int) ( $raw['headline_fs'] ?? 0 ) ) );
		$o['headline_fw']  = self::enum( (string) ( $raw['headline_fw'] ?? '' ), array( '', '300', '400', '500', '600', '700', '800', '900' ), '' );
		$o['headline_al']  = self::enum( (string) ( $raw['headline_al'] ?? 'l' ), array( 'l', 'c', 'r' ), 'l' );
		$hff               = self::font_ok( $raw['headline_ff'] ?? '' );
		$o['headline_ff']  = 'custom' === $hff ? '' : $hff;
		$o['divider']      = self::enum( $raw['divider'] ?? '', array_keys( KHF_Presets::dividers() ), $d['divider'] );
		$o['divider_h']    = max( 10, min( 320, (int) ( $raw['divider_h'] ?? $d['divider_h'] ) ) );
		$o['divider_op']   = max( 0, min( 100, (int) ( $raw['divider_op'] ?? $d['divider_op'] ) ) );
		$o['divider_c']    = KHF_Fx::color( $raw['divider_c'] ?? '' );
		$o['grid_cols']    = max( 1, min( 6, (int) ( $raw['grid_cols'] ?? $d['grid_cols'] ) ) );
		$o['grid_rows']    = max( 1, min( 6, (int) ( $raw['grid_rows'] ?? $d['grid_rows'] ) ) );
		$o['grid_gap']     = max( 0, min( 120, (int) ( $raw['grid_gap'] ?? $d['grid_gap'] ) ) );
		$o['grid_valign']  = self::enum( $raw['grid_valign'] ?? '', array( 'start', 'center', 'end' ), $d['grid_valign'] );
		$o['cell_style']   = self::enum( $raw['cell_style'] ?? '', array( 'none', 'card', 'glass' ), $d['cell_style'] );
		$o['cell_r']       = max( 0, min( 60, (int) ( $raw['cell_r'] ?? $d['cell_r'] ) ) );
		$o['cell_pad']     = max( 0, min( 80, (int) ( $raw['cell_pad'] ?? $d['cell_pad'] ) ) );
		$o['cell_blur']    = max( 0, min( 60, (int) ( $raw['cell_blur'] ?? $d['cell_blur'] ) ) );
		$o['cell_tint']    = KHF_Fx::paint( $raw['cell_tint'] ?? '' );
		$o['grid_cells']   = self::cells( $raw['grid_cells'] ?? array() );
		$o['bar_gap']      = max( 0, min( 200, (int) ( $raw['bar_gap'] ?? $d['bar_gap'] ) ) );
		$o['bar_layout']   = self::bar( $raw['bar_layout'] ?? array() );
		$o['fmenu_fx']     = self::enum( $raw['fmenu_fx'] ?? '', array_keys( KHF_Presets::footer_link_effects() ), $d['fmenu_fx'] );
		$o['btt_mode']     = self::enum( $raw['btt_mode'] ?? '', array( 'global', 'custom', 'off' ), $d['btt_mode'] );
		$o['fb_place']     = self::enum( $raw['fb_place'] ?? '', array( 'fixed', 'footer' ), $d['fb_place'] );
		$o['fb_pos']       = self::enum( $raw['fb_pos'] ?? '', array( 'l', 'c', 'r' ), $d['fb_pos'] );
		$o['fb_icon']      = self::enum( $raw['fb_icon'] ?? '', array( 'arrow', 'chevron', 'double', 'caret', 'text' ), $d['fb_icon'] );
		$o['fb_hover']     = self::enum( $raw['fb_hover'] ?? '', array( 'lift', 'rotate', 'none' ), $d['fb_hover'] );
		$o['fb_x']         = max( -800, min( 800, (int) ( $raw['fb_x'] ?? $d['fb_x'] ) ) );
		$o['fb_y']         = max( 0, min( 800, (int) ( $raw['fb_y'] ?? $d['fb_y'] ) ) );
		$o['fb_size']      = max( 28, min( 96, (int) ( $raw['fb_size'] ?? $d['fb_size'] ) ) );
		$o['fb_r']         = max( 0, min( 50, (int) ( $raw['fb_r'] ?? $d['fb_r'] ) ) );
		$o['fb_after']     = max( 0, min( 5000, (int) ( $raw['fb_after'] ?? $d['fb_after'] ) ) );
		$o['fb_text']      = sanitize_text_field( $raw['fb_text'] ?? '' );
		foreach ( array( 'fb_bg', 'fb_fg', 'fb_bd' ) as $k ) {
			$o[ $k ] = 'fb_bg' === $k ? KHF_Fx::paint( $raw[ $k ] ?? '' ) : KHF_Fx::color( $raw[ $k ] ?? '' );
		}
		$o['priority']      = max( 0, min( 999, (int) ( $raw['priority'] ?? $d['priority'] ) ) );
		$o['height']        = max( 40, min( 220, (int) ( $raw['height'] ?? $d['height'] ) ) );
		$o['height_shrunk'] = max( 30, min( 220, (int) ( $raw['height_shrunk'] ?? $d['height_shrunk'] ) ) );

		foreach ( array( 'logo_id', 'menu', 'menu2', 'menu3' ) as $k ) {
			$o[ $k ] = absint( $raw[ $k ] ?? 0 );
		}

		// Display rules: "only on" / "except on" + target.
		$o['cond_rules'] = array();
		foreach ( array_slice( (array) ( $raw['cond_rules'] ?? array() ), 0, 40 ) as $r ) {
			$t = is_array( $r ) && is_string( $r['t'] ?? null ) ? $r['t'] : '';
			if ( ! preg_match( '/^(front|blog|archive|search|404|id|pt:[a-z0-9_\-]+)$/', $t ) ) {
				continue;
			}
			$id = absint( $r['id'] ?? 0 );
			if ( 'id' === $t && ! $id ) {
				continue;
			}
			$o['cond_rules'][] = array(
				'm'     => 'except' === ( $r['m'] ?? '' ) ? 'except' : 'only',
				't'     => $t,
				'id'    => 'id' === $t ? $id : 0,
				'title' => 'id' === $t ? sanitize_text_field( $r['title'] ?? '' ) : '',
			);
		}

		$o['color_bg']  = KHF_Fx::paint( $raw['color_bg'] ?? '' );
		$o['color_bg2'] = KHF_Fx::paint( $raw['color_bg2'] ?? '' );
		foreach ( array( 'color_fg', 'color_accent', 'c_menu', 'c_menu_h', 'c_pill', 'c_cta_bg', 'c_cta_fg', 'c_burger', 'c_sub_bg', 'c_sub_fg', 'c_panel_bg', 'c_panel_fg', 'c_link', 'c_link_h', 'c_heading', 'c_line', 'menu_fx_c', 'c_burger_bg', 'c_sep', 'c_mega_head', 'c_mega_line' ) as $k ) {
			$o[ $k ] = in_array( $k, array( 'c_pill', 'c_cta_bg', 'c_sub_bg', 'c_panel_bg', 'c_burger_bg' ), true ) ? KHF_Fx::paint( $raw[ $k ] ?? '' ) : KHF_Fx::color( $raw[ $k ] ?? '' );
		}

		foreach ( array( 'logo_text', 'cta_text', 'tagline', 'phone', 'address', 'copyright' ) as $k ) {
			$o[ $k ] = sanitize_text_field( $raw[ $k ] ?? '' );
		}
		$o['address_url'] = esc_url_raw( (string) ( $raw['address_url'] ?? '' ), array( 'http', 'https' ) );
		$o['email']    = sanitize_email( $raw['email'] ?? '' );
		$o['cta_url']  = esc_url_raw( $raw['cta_url'] ?? '' );
		$o['headline'] = wp_kses( $raw['headline'] ?? '', array( 'br' => array(), 'em' => array(), 'strong' => array() ) );

		$urls = array();
		foreach ( preg_split( '/\r\n|\r|\n/', (string) ( $raw['socials'] ?? '' ) ) as $line ) {
			$url = esc_url_raw( trim( $line ) );
			if ( $url ) {
				$urls[] = $url;
			}
		}
		$o['socials'] = implode( "\n", $urls );

		return $o;
	}

	/* ------------------------------------------------------------------ */
	/* Impostazioni globali                                                */
	/* ------------------------------------------------------------------ */

	public static function settings_defaults() {
		return array(
			'hide_theme_header' => 1,
			'hide_theme_footer' => 1,
			'header_selectors'  => '',
			'footer_selectors'  => '',
			'content_selector'  => '',
			'breakpoint'        => 1024,
			'bp_tablet'         => 1024,
			'bp_mobile'         => 767,
			'z_index'           => 9990,
			'btt'               => 0,
			'btt_pos'           => 'right',
			'btt_size'          => 46,
			'btt_off'           => 22,
			'btt_after'         => 500,
			'btt_r'             => 50,
			'btt_bg'            => '#111111',
			'btt_fg'            => '#ffffff',
			'anchor'            => 0,
			'anchor_extra'      => 0,
			'smooth'            => 0,
			'font_mode'         => 'local',
		);
	}

	public static function settings() {
		static $cache = null;
		if ( null !== $cache ) {
			return $cache;
		}
		$saved = get_option( self::SETTINGS_KEY, array() );
		$saved = is_array( $saved ) ? $saved : array();
		$d     = self::settings_defaults();
		$s     = wp_parse_args( $saved, $d );

		$cache = $s;
		return $cache;
	}

	private static function selectors( $value ) {
		$value = wp_strip_all_tags( (string) $value );
		// The ">" combinator stays valid in CSS selectors; we only remove what could break the <style> block.
		return trim( preg_replace( '/[{};<]/', '', $value ) );
	}

	private static function bounded( $raw, $key, $min, $max, $fallback ) {
		return max( $min, min( $max, (int) ( $raw[ $key ] ?? $fallback ) ) );
	}

	public static function sanitize_settings( $raw ) {
		$raw = is_array( $raw ) ? $raw : array();
		$d   = self::settings_defaults();

		$bg = sanitize_hex_color( $raw['btt_bg'] ?? '' );
		$fg = sanitize_hex_color( $raw['btt_fg'] ?? '' );

		return array(
			'hide_theme_header' => empty( $raw['hide_theme_header'] ) ? 0 : 1,
			'hide_theme_footer' => empty( $raw['hide_theme_footer'] ) ? 0 : 1,
			'header_selectors'  => self::selectors( $raw['header_selectors'] ?? '' ),
			'footer_selectors'  => self::selectors( $raw['footer_selectors'] ?? '' ),
			'content_selector'  => self::selectors( $raw['content_selector'] ?? '' ),
			'breakpoint'        => self::bounded( $raw, 'breakpoint', 320, 1600, $d['breakpoint'] ),
			'bp_tablet'         => self::bounded( $raw, 'bp_tablet', 480, 1600, $d['bp_tablet'] ),
			'bp_mobile'         => self::bounded( $raw, 'bp_mobile', 320, 1200, $d['bp_mobile'] ),
			'z_index'           => self::bounded( $raw, 'z_index', 1, 2147483000, $d['z_index'] ),
			'btt'               => empty( $raw['btt'] ) ? 0 : 1,
			'btt_pos'           => 'left' === ( $raw['btt_pos'] ?? '' ) ? 'left' : 'right',
			'btt_size'          => self::bounded( $raw, 'btt_size', 28, 96, $d['btt_size'] ),
			'btt_off'           => self::bounded( $raw, 'btt_off', 0, 120, $d['btt_off'] ),
			'btt_after'         => self::bounded( $raw, 'btt_after', 0, 5000, $d['btt_after'] ),
			'btt_r'             => self::bounded( $raw, 'btt_r', 0, 50, $d['btt_r'] ),
			'btt_bg'            => $bg ? $bg : $d['btt_bg'],
			'btt_fg'            => $fg ? $fg : $d['btt_fg'],
			'anchor'            => empty( $raw['anchor'] ) ? 0 : 1,
			'anchor_extra'      => self::bounded( $raw, 'anchor_extra', 0, 400, $d['anchor_extra'] ),
			'smooth'            => empty( $raw['smooth'] ) ? 0 : 1,
			'font_mode'         => self::enum( $raw['font_mode'] ?? '', array( 'local', 'bunny', 'google' ), $d['font_mode'] ),
		);
	}
}
