<?php
defined( 'ABSPATH' ) || exit;

/**
 * Emotional palettes ("mood") and markup of the header/footer presets.
 */
class KAOSHF_Presets {

	/** hbg/hfg = header, fbg/fbg2/ffg = footer (gradiente), accent condiviso. */
	/** hbg/hfg = header, fbg/fbg2/ffg = footer (gradiente), accent condiviso. */
	public static function moods() {
		$m = array(
			// key => label, header bg, header text, footer bg, footer bg2, footer text, accent
			'sunset'   => array( __( 'Sunset', 'kaos-header-footer' ), '#fff6ee', '#3b1f2e', '#2d1b3d', '#c2416b', '#fff1ea', '#ff7a59' ),
			'ocean'    => array( __( 'Ocean', 'kaos-header-footer' ), '#f2fbff', '#0b2a3d', '#062a40', '#0f7ea6', '#eaf9ff', '#19b5c9' ),
			'midnight' => array( __( 'Midnight', 'kaos-header-footer' ), '#0e1020', '#eef0ff', '#0a0b18', '#2a1f5c', '#e8eaff', '#8b7bff' ),
			'forest'   => array( __( 'Forest', 'kaos-header-footer' ), '#f4f8f1', '#1d2b1f', '#14261b', '#3b6b3f', '#eef6ea', '#7bc86c' ),
			'rose'     => array( __( 'Antique rose', 'kaos-header-footer' ), '#fff5f7', '#4a1f2c', '#4a1f2c', '#d4577b', '#fff0f3', '#ff5c8a' ),
			'cream'    => array( __( 'Cream & gold', 'kaos-header-footer' ), '#fbf7ef', '#2a2418', '#2a2418', '#6d5a35', '#f7f1e3', '#c8963e' ),
			'mono'     => array( __( 'Monochrome', 'kaos-header-footer' ), '#ffffff', '#111111', '#111111', '#333333', '#f5f5f5', '#ff3d00' ),
			'lavender' => array( __( 'Lavender', 'kaos-header-footer' ), '#f7f3ff', '#2a2140', '#2a2140', '#7c5cd6', '#f4efff', '#8b6bff' ),
			'aurora'   => array( __( 'Aurora', 'kaos-header-footer' ), '#f0fff9', '#0e2a2a', '#0b1f2e', '#1fbf9f', '#e9fffa', '#2ee6b8' ),
			'coral'    => array( __( 'Coral', 'kaos-header-footer' ), '#fff4f1', '#40201c', '#40201c', '#ff6f61', '#fff3f0', '#ff6f61' ),
			'wine'     => array( __( 'Wine', 'kaos-header-footer' ), '#fbf1f3', '#3a0f1c', '#3a0f1c', '#8a1c3a', '#ffeef2', '#d6336c' ),
			'sand'     => array( __( 'Sand', 'kaos-header-footer' ), '#faf5ec', '#3a3020', '#3a3020', '#b08d57', '#faf3e6', '#d9a441' ),
			'graphite' => array( __( 'Graphite', 'kaos-header-footer' ), '#f1f2f4', '#1c1f24', '#15171b', '#3a3f47', '#eceef1', '#5b8cff' ),
			'neon'     => array( __( 'Neon', 'kaos-header-footer' ), '#0b0b14', '#f4f4ff', '#0b0b14', '#3a0ca3', '#f4f4ff', '#00f5d4' ),
			'mint'     => array( __( 'Mint', 'kaos-header-footer' ), '#effcf6', '#123a2b', '#0f3d2e', '#34d399', '#ecfff6', '#10b981' ),
			'sky'      => array( __( 'Sky', 'kaos-header-footer' ), '#eef6ff', '#10304f', '#0d2a4a', '#4aa3ff', '#eaf4ff', '#2f8cff' ),
			'peach'    => array( __( 'Peach', 'kaos-header-footer' ), '#fff6ef', '#4a2a1a', '#4a2a1a', '#ff9a76', '#fff4ec', '#ff8a5c' ),
			'olive'    => array( __( 'Olive', 'kaos-header-footer' ), '#f6f7ee', '#2c3016', '#2c3016', '#7d8a3a', '#f7f8ea', '#9aa63a' ),
			'ink'      => array( __( 'Ink', 'kaos-header-footer' ), '#ffffff', '#0a0a23', '#0a0a23', '#1b1b4b', '#f0f0ff', '#4f46e5' ),
			'gold'     => array( __( 'Black gold', 'kaos-header-footer' ), '#14120d', '#f5e9c8', '#0c0b08', '#4a3a12', '#f5e9c8', '#e0b84a' ),
			'cherry'   => array( __( 'Cherry', 'kaos-header-footer' ), '#fff0f2', '#4a0f1a', '#4a0f1a', '#e11d48', '#fff0f2', '#f43f5e' ),
			'ice'      => array( __( 'Ice', 'kaos-header-footer' ), '#f4fafe', '#153047', '#153047', '#7dd3fc', '#f1faff', '#38bdf8' ),
			'sunrise'  => array( __( 'Dawn', 'kaos-header-footer' ), '#fffaf0', '#3b2a10', '#3b2a10', '#f59e0b', '#fff7e6', '#f97316' ),
			'custom'   => array( __( 'Custom', 'kaos-header-footer' ), '#ffffff', '#111111', '#111827', '#111827', '#f5f5f5', '#5c00e6' ),
		);
		$out = array();
		foreach ( $m as $key => $v ) {
			$out[ $key ] = array( 'label' => $v[0], 'hbg' => $v[1], 'hfg' => $v[2], 'fbg' => $v[3], 'fbg2' => $v[4], 'ffg' => $v[5], 'accent' => $v[6] );
		}
		return $out;
	}

	public static function dividers() {
		return array(
			'wave'     => __( 'Wave', 'kaos-header-footer' ),
			'waves'    => __( 'Layered waves', 'kaos-header-footer' ),
			'curve'    => __( 'Curve (smile)', 'kaos-header-footer' ),
			'arch'     => __( 'Arch', 'kaos-header-footer' ),
			'tilt'     => __( 'Diagonal', 'kaos-header-footer' ),
			'tilt-r'   => __( 'Reverse diagonal', 'kaos-header-footer' ),
			'triangle' => __( 'Triangle', 'kaos-header-footer' ),
			'zigzag'   => __( 'Zigzag', 'kaos-header-footer' ),
			'clouds'   => __( 'Clouds', 'kaos-header-footer' ),
			'none'     => __( 'None', 'kaos-header-footer' ),
		);
	}

	public static function footer_link_effects() {
		return array(
			'color'            => __( 'Color change', 'kaos-header-footer' ),
			'underline'        => __( 'Sliding underline', 'kaos-header-footer' ),
			'underline-center' => __( 'Underline from the center', 'kaos-header-footer' ),
			'arrow'            => __( 'Arrow ↗ in front', 'kaos-header-footer' ),
			'shift'            => __( 'Slide to the right', 'kaos-header-footer' ),
			'dim'              => __( 'Dim the other items', 'kaos-header-footer' ),
			'glow'             => __( 'Glow', 'kaos-header-footer' ),
			'scale'            => __( 'Scale up', 'kaos-header-footer' ),
			'none'             => __( 'No effect', 'kaos-header-footer' ),
		);
	}

	/** Default arrangement of the bottom bar, per preset. */
	public static function bar_defaults( $preset ) {
		$off = array( 'on' => 0, 'z' => 'c', 'o' => 0, 'dx' => 0 );
		$set = array(
			'columns' => array( 'copy' => array( 'on' => 1, 'z' => 'l', 'o' => 0, 'dx' => 0 ) ),
			'cta-big' => array( 'logo' => array( 'on' => 1, 'z' => 'l', 'o' => 0, 'dx' => 0 ), 'menu' => array( 'on' => 1, 'z' => 'c', 'o' => 0, 'dx' => 0 ), 'social' => array( 'on' => 1, 'z' => 'r', 'o' => 0, 'dx' => 0 ), 'copy' => array( 'on' => 1, 'z' => 'r', 'o' => 1, 'dx' => 0 ) ),
			'minimal' => array( 'copy' => array( 'on' => 1, 'z' => 'l', 'o' => 0, 'dx' => 0 ), 'menu' => array( 'on' => 1, 'z' => 'c', 'o' => 0, 'dx' => 0 ), 'social' => array( 'on' => 1, 'z' => 'r', 'o' => 0, 'dx' => 0 ) ),
			'grid'    => array( 'logo' => array( 'on' => 1, 'z' => 'l', 'o' => 0, 'dx' => 0 ), 'menu' => array( 'on' => 1, 'z' => 'c', 'o' => 0, 'dx' => 0 ), 'copy' => array( 'on' => 1, 'z' => 'r', 'o' => 0, 'dx' => 0 ) ),
		);
		$out = array();
		foreach ( array( 'logo', 'menu', 'menu2', 'social', 'copy' ) as $el ) {
			$out[ $el ] = $set[ $preset ][ $el ] ?? $off;
		}
		return $out;
	}

	/** Fonts: key => [label, CSS stack, name on Bunny Fonts (empty = local)]. */
	public static function fonts() {
		$bunny = array( 'Inter', 'Poppins', 'Montserrat', 'DM Sans', 'Manrope', 'Space Grotesk', 'Outfit', 'Raleway', 'Nunito', 'Work Sans', 'Urbanist', 'Sora', 'Josefin Sans', 'Archivo', 'Syne', 'Oswald', 'Bebas Neue', 'Playfair Display', 'Lora', 'Merriweather', 'Cormorant Garamond', 'Libre Baskerville', 'Fraunces', 'Unbounded' );
		$out   = array(
			''       => array( __( 'Same as the theme', 'kaos-header-footer' ), '', '' ),
			'system' => array( __( 'System (fast, no download)', 'kaos-header-footer' ), 'system-ui,-apple-system,"Segoe UI",Roboto,sans-serif', '' ),
			'serif'  => array( __( 'Classic serif', 'kaos-header-footer' ), 'Georgia,"Times New Roman",serif', '' ),
			'mono'   => array( __( 'Monospace', 'kaos-header-footer' ), 'ui-monospace,SFMono-Regular,Menlo,Consolas,monospace', '' ),
		);
		foreach ( $bunny as $name ) {
			$out[ 'b:' . $name ] = array( $name, '"' . $name . '",sans-serif', $name );
		}
		return $out;
	}

	/** Name of the font to load from Bunny Fonts for a key (+ optional custom name). */
	public static function font_source( $key, $custom ) {
		if ( 'custom' === $key && '' !== $custom ) {
			return array( '"' . $custom . '",sans-serif', $custom );
		}
		$fonts = self::fonts();
		return isset( $fonts[ $key ] ) && '' !== $key ? array( $fonts[ $key ][1], $fonts[ $key ][2] ) : array( '', '' );
	}

	public static function presets( $type ) {
		if ( 'footer' === $type ) {
			return array(
				'columns' => __( 'Columns (brand, menu, contacts)', 'kaos-header-footer' ),
				'cta-big' => __( 'Big emotional call to action', 'kaos-header-footer' ),
				'minimal' => __( 'Minimal on one line', 'kaos-header-footer' ),
				'grid'    => __( 'Custom grid (choose rows and columns)', 'kaos-header-footer' ),
			);
		}
		return array(
			'classic'  => __( 'Classic (logo on the left, menu on the right)', 'kaos-header-footer' ),
			'split'    => __( 'Split (menu 1 on the left, logo in the center, menu 2 on the right)', 'kaos-header-footer' ),
			'custom'   => __( 'Custom (you decide the arrangement)', 'kaos-header-footer' ),
			'centered' => __( 'Centered (logo above, menu below)', 'kaos-header-footer' ),
			'minimal'  => __( 'Minimal (hamburger + full-screen menu)', 'kaos-header-footer' ),
			'floating' => __( 'Floating (glass pill)', 'kaos-header-footer' ),
		);
	}

	public static function menu_effects() {
		return array(
			'pill'             => __( 'Pill (soft background)', 'kaos-header-footer' ),
			'underline'        => __( 'Sliding underline', 'kaos-header-footer' ),
			'underline-center' => __( 'Underline from the center', 'kaos-header-footer' ),
			'overline'         => __( 'Line above', 'kaos-header-footer' ),
			'fill'             => __( 'Sliding fill', 'kaos-header-footer' ),
			'bracket'          => __( 'Brackets [ ]', 'kaos-header-footer' ),
			'dot'              => __( 'Dot', 'kaos-header-footer' ),
			'roll'             => __( 'Text sliding vertically', 'kaos-header-footer' ),
			'dim'              => __( 'Dim the other items', 'kaos-header-footer' ),
			'scale'            => __( 'Scale up', 'kaos-header-footer' ),
			'spacing'          => __( 'Letter spacing', 'kaos-header-footer' ),
			'glow'             => __( 'Glow', 'kaos-header-footer' ),
			'strike'           => __( 'Strikethrough', 'kaos-header-footer' ),
			'arrow'            => __( 'Arrow ↗ in front of the item', 'kaos-header-footer' ),
			'none'             => __( 'No effect', 'kaos-header-footer' ),
		);
	}

	public static function burgers() {
		return array(
			'lines'  => __( 'Three lines', 'kaos-header-footer' ),
			'lines2' => __( 'Two lines', 'kaos-header-footer' ),
			'asym'   => __( 'Three asymmetric lines', 'kaos-header-footer' ),
			'dots'   => __( 'Three dots', 'kaos-header-footer' ),
			'grid'   => __( 'Dot grid', 'kaos-header-footer' ),
			'plus'   => __( 'Plus', 'kaos-header-footer' ),
			'text'   => __( 'Text only', 'kaos-header-footer' ),
		);
	}

	/** Readable text (black/white) over a hex color. */
	public static function contrast( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = preg_replace( '/(.)/', '$1$1', $hex );
		}
		if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
			return '#ffffff';
		}
		$lum = ( 0.299 * hexdec( substr( $hex, 0, 2 ) ) + 0.587 * hexdec( substr( $hex, 2, 2 ) ) + 0.114 * hexdec( substr( $hex, 4, 2 ) ) ) / 255;
		return $lum > 0.62 ? '#111111' : '#ffffff';
	}

	/* ------------------------------------------------------------------ */
	/* Blocchi riutilizzabili                                              */
	/* ------------------------------------------------------------------ */

	public static function logo( $o ) {
		$inner = '';
		$id    = (int) $o['logo_id'];
		if ( $id ) {
			// Raster: WordPress tag (with srcset). SVG and similar: hand-built tag, because WordPress
			// does not generate it without metadata and an SVG with only a viewBox has no size of its own.
			$inner = wp_get_attachment_image( $id, 'full', false, array( 'class' => 'khf-logo__img', 'alt' => get_bloginfo( 'name' ), 'loading' => 'eager' ) );
			if ( '' === $inner ) {
				$url = wp_get_attachment_url( $id );
				if ( $url ) {
					$inner = '<img class="khf-logo__img" src="' . esc_url( $url ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" decoding="async">';
				}
			}
		}
		if ( '' === $inner ) {
			$inner = esc_html( '' !== $o['logo_text'] ? $o['logo_text'] : get_bloginfo( 'name' ) );
		}
		return '<a class="khf-logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . $inner . '</a>';
	}

	/** Hover images (item id => URL) active while rendering a menu. */
	private static $item_images = array();

	public static function menu( $menu_id, $class = 'khf-menu', $depth = 2, $images = array() ) {
		if ( ! $menu_id ) {
			return '';
		}
		self::$item_images = $images;
		add_filter( 'nav_menu_link_attributes', array( __CLASS__, 'link_attributes' ), 10, 2 );
		add_filter( 'nav_menu_item_title', array( __CLASS__, 'item_title' ), 10, 1 );
		add_filter( 'nav_menu_item_id', '__return_empty_string' );
		add_filter( 'walker_nav_menu_start_el', array( __CLASS__, 'caret' ), 10, 3 );

		$html = wp_nav_menu(
			array(
				'menu'        => $menu_id,
				'container'   => false,
				'menu_class'  => $class,
				'depth'       => $depth,
				'fallback_cb' => false,
				'echo'        => false,
				'items_wrap'  => '<ul class="%2$s">%3$s</ul>',
			)
		);

		remove_filter( 'nav_menu_link_attributes', array( __CLASS__, 'link_attributes' ), 10 );
		remove_filter( 'nav_menu_item_title', array( __CLASS__, 'item_title' ), 10 );
		remove_filter( 'nav_menu_item_id', '__return_empty_string' );
		remove_filter( 'walker_nav_menu_start_el', array( __CLASS__, 'caret' ), 10 );
		self::$item_images = array();
		return $html ? $html : '';
	}

	/** Small arrow (hidden by CSS if the option is off) on top-level items with a submenu. */
	public static function caret( $output, $item, $depth ) {
		if ( 0 === $depth && in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
			$pos = strrpos( $output, '</a>' );
			if ( false !== $pos ) {
				$output = substr_replace( $output, '<span class="khf-caret" aria-hidden="true"></span>', $pos, 0 );
			}
		}
		return $output;
	}

	public static function link_attributes( $atts, $item ) {
		if ( isset( self::$item_images[ $item->ID ] ) ) {
			$atts['data-khf-img'] = self::$item_images[ $item->ID ];
		}
		return $atts;
	}

	/** The text is in an element with a copy in data-t: used by the effects that duplicate it. */
	public static function item_title( $title ) {
		return '<span class="khf-ml" data-t="' . esc_attr( wp_strip_all_tags( $title ) ) . '">' . $title . '</span>';
	}

	private static function menu_title( $menu_id ) {
		$obj = $menu_id ? wp_get_nav_menu_object( $menu_id ) : null;
		return $obj ? esc_html( $obj->name ) : '';
	}

	private static function social_label( $host ) {
		$map = array(
			'instagram.com' => 'Instagram', 'facebook.com' => 'Facebook', 'x.com' => 'X', 'twitter.com' => 'X',
			'linkedin.com'  => 'LinkedIn',  'youtube.com'  => 'YouTube',  'youtu.be' => 'YouTube', 'tiktok.com' => 'TikTok',
			'pinterest.com' => 'Pinterest', 'wa.me'        => 'WhatsApp', 'whatsapp.com' => 'WhatsApp', 't.me' => 'Telegram',
			'github.com'    => 'GitHub',    'behance.net'  => 'Behance',  'dribbble.com' => 'Dribbble', 'spotify.com' => 'Spotify',
		);
		if ( isset( $map[ $host ] ) ) {
			return $map[ $host ];
		}
		$parts = explode( '.', $host );
		return ucfirst( $parts[0] );
	}

	/** Social icons (stroke, 24x24). Returns the content of the <svg> or empty. */
	private static function social_icon( $name ) {
		$icons = array(
			'Instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r=".6" fill="currentColor"/>',
			'Facebook'  => '<path d="M17 2.5h-2.5A4.500 4.500 0 0 0 10 7v3H7v4h3v7.500h4V14h3l1-4h-4V7.500c0-.6.400-1 1-1H17z"/>',
			'X'         => '<path d="M4 4l16 16M20 4L4 20"/>',
			'LinkedIn'  => '<path d="M16 8.500a5.500 5.500 0 0 1 5.500 5.500v7h-4v-7a1.500 1.500 0 0 0-3 0v7h-4v-7A5.500 5.500 0 0 1 16 8.500z"/><rect x="2.500" y="9" width="4" height="12"/><circle cx="4.500" cy="4.500" r="2"/>',
			'YouTube'   => '<path d="M21.500 7a2.700 2.700 0 0 0-1.900-1.900C17.900 4.700 12 4.700 12 4.700s-5.900 0-7.600.4A2.700 2.700 0 0 0 2.500 7 28 28 0 0 0 2.100 12a28 28 0 0 0 .4 5 2.700 2.700 0 0 0 1.900 1.900c1.700.4 7.600.4 7.600.4s5.900 0 7.600-.4a2.700 2.700 0 0 0 1.900-1.900 28 28 0 0 0 .4-5 28 28 0 0 0-.4-5z"/><path d="M10 9.500l5 2.500-5 2.500z" fill="currentColor"/>',
			'TikTok'    => '<path d="M14 3v11.500a3.500 3.500 0 1 1-3.500-3.500"/><path d="M14 3c.4 2.600 2.100 4.200 4.500 4.300"/>',
			'Pinterest' => '<circle cx="12" cy="12" r="9"/><path d="M10.500 17.500l1.800-7.500m-2.300-.5c.6-2 4.500-2 4.500 1 0 2.500-2.500 3.500-3.500 2"/>',
			'WhatsApp'  => '<path d="M12 3a9 9 0 0 0-7.700 13.600L3 21l4.500-1.200A9 9 0 1 0 12 3z"/><path d="M9 8.500c0 3.500 3 6.500 6.500 6.500l1-1.500-2-1-1 .8c-1-.4-2-1.400-2.400-2.400l.8-1-1-2z"/>',
			'Telegram'  => '<path d="M21.500 3L2.500 10.500l6 2.200 2.300 6.800 3.200-4.200 5 3.700z"/><path d="M8.500 12.700L21.500 3"/>',
			'GitHub'    => '<path d="M9 19c-4.300 1.400-4.300-2.500-6-3m12 5v-3.500c0-1 .1-1.400-.5-2 2.800-.3 5.500-1.400 5.500-6a4.600 4.600 0 0 0-1.300-3.200 4.200 4.200 0 0 0-.1-3.200s-1.100-.3-3.500 1.300a12 12 0 0 0-6.200 0C6.500 2.800 5.400 3.100 5.400 3.100a4.200 4.200 0 0 0-.1 3.200A4.600 4.600 0 0 0 4 9.500c0 4.600 2.700 5.700 5.500 6-.6.600-.6 1.200-.5 2V21"/>',
			'Spotify'   => '<circle cx="12" cy="12" r="9"/><path d="M7.500 9.500c3-1 6.500-.8 9 .8M8 12.500c2.500-.7 5-.5 7.500.8M8.500 15.300c2-.5 4-.3 5.700.7"/>',
			'Behance'   => '<path d="M3 6h5a2.500 2.500 0 0 1 0 5H3zm0 5h5.500a2.500 2.500 0 0 1 0 5H3zM14 8h6m-6 6.500a3 3 0 0 0 6 0 3 3 0 0 0-6 0z"/>',
			'Dribbble'  => '<circle cx="12" cy="12" r="9"/><path d="M8.500 3.500c3.400 4.600 4.800 7.500 6.300 14M4 11c6 0 10-1 13.500-5M20.500 13c-3-.8-5.600-.7-7.800 0-2 .7-4 2.300-5.700 5"/>',
		);
		return $icons[ $name ] ?? '';
	}

	public static function socials( $o ) {
		$lines = array_filter( array_map( 'trim', explode( "\n", (string) $o['socials'] ) ) );
		if ( ! $lines ) {
			return '';
		}
		$style = $o['social_style'] ?? 'icons';
		$out   = '<ul class="khf-socials khf-socials--' . esc_attr( $style ) . '">';
		foreach ( $lines as $url ) {
			$host  = preg_replace( '/^www\./', '', (string) wp_parse_url( $url, PHP_URL_HOST ) );
			$label = self::social_label( $host );
			$icon  = self::social_icon( $label );
			$show_icon = 'text' !== $style && '' !== $icon;
			$inner = ( $show_icon ? '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icon . '</svg>' : '' )
				. ( ! $show_icon || 'both' === $style ? '<span class="khf-soc-t">' . esc_html( $label ) . '</span>' : '' );
			$out  .= '<li><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr( $label ) . '">' . $inner . '</a></li>';
		}
		return $out . '</ul>';
	}

	private static function copyright( $o ) {
		$text = '' !== $o['copyright'] ? $o['copyright'] : '© {year} ' . get_bloginfo( 'name' );
		return '<p class="khf-copy">' . esc_html( str_replace( '{year}', gmdate( 'Y' ), $text ) ) . '</p>';
	}

	private static function cta( $o, $class ) {
		$url = $o['cta_url'];
		if ( '' === $url && '' !== $o['email'] ) {
			$url = 'mailto:' . $o['email'];
		}
		if ( '' === $o['cta_text'] || '' === $url ) {
			return '';
		}
		return '<a class="khf-cta ' . esc_attr( $class ) . '" href="' . esc_url( $url, array( 'http', 'https', 'mailto', 'tel' ) ) . '"><span>' . esc_html( $o['cta_text'] ) . '</span></a>';
	}

	private static function contacts( $o ) {
		$items = '';
		if ( '' !== $o['email'] ) {
			$items .= '<li><a href="' . esc_url( 'mailto:' . $o['email'] ) . '">' . esc_html( $o['email'] ) . '</a></li>';
		}
		if ( '' !== $o['phone'] ) {
			$items .= '<li><a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $o['phone'] ), array( 'tel' ) ) . '">' . esc_html( $o['phone'] ) . '</a></li>';
		}
		if ( '' !== $o['address'] ) {
			if ( ! empty( $o['address_map'] ) ) {
				$map    = '' !== $o['address_url'] ? $o['address_url'] : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $o['address'] );
				$items .= '<li><a class="khf-addr" href="' . esc_url( $map, array( 'http', 'https' ) ) . '" data-q="' . esc_attr( $o['address'] ) . '" data-l-maps="' . esc_attr__( 'Maps', 'kaos-header-footer' ) . '" data-l-google="' . esc_attr__( 'Google Maps', 'kaos-header-footer' ) . '" data-l-cancel="' . esc_attr__( 'Cancel', 'kaos-header-footer' ) . '" target="_blank" rel="noopener">' . esc_html( $o['address'] ) . '</a></li>';
			} else {
				$items .= '<li>' . esc_html( $o['address'] ) . '</li>';
			}
		}
		return $items ? '<ul class="khf-contacts">' . $items . '</ul>' : '';
	}

	/* ------------------------------------------------------------------ */
	/* Header                                                              */
	/* ------------------------------------------------------------------ */

	/** Mobile menu icon: the lines are <i> elements, drawn and animated by CSS. */
	private static function burger( $o ) {
		$style = $o['burger'];
		$open  = '' !== $o['burger_label_open'] ? $o['burger_label_open'] : $o['burger_label'];
		$label = 'text' === $style && '' === $o['burger_label'] ? __( 'Menu', 'kaos-header-footer' ) : $o['burger_label'];
		$html  = '<button type="button" class="khf-burger" data-bi="' . esc_attr( $style ) . '" data-shape="' . esc_attr( $o['burger_shape'] ) . '" aria-expanded="false" aria-controls="khf-nav" aria-label="' . esc_attr__( 'Open the menu', 'kaos-header-footer' ) . '">';
		if ( 'text' !== $style ) {
			$html .= '<span class="khf-bi" aria-hidden="true"><i></i><i></i><i></i></span>';
		}
		if ( '' !== $label ) {
			$html .= '<span class="khf-bl" data-closed="' . esc_attr( $label ) . '" data-open="' . esc_attr( '' !== $open ? $open : $label ) . '">' . esc_html( $label ) . '</span>';
		}
		return $html . '</button>';
	}

	public static function is_video( $url ) {
		return (bool) preg_match( '/\.(mp4|webm|ogv|mov|m4v)(\?.*)?$/i', (string) $url );
	}

	/** Panel/card with the media (image or short video) of the menu item being hovered. */
	private static function menu_media( $o, $images ) {
		if ( 'none' === $o['hover_img'] || ! $images ) {
			return '';
		}
		$html = '<div class="khf-menu-media" aria-hidden="true">';
		foreach ( array_values( array_unique( $images ) ) as $url ) {
			if ( self::is_video( $url ) ) {
				$html .= '<video data-u="' . esc_url( $url ) . '" src="' . esc_url( $url ) . '" muted loop playsinline preload="metadata"></video>';
			} else {
				$html .= '<img data-u="' . esc_url( $url ) . '" src="' . esc_url( $url ) . '" alt="" loading="lazy" decoding="async">';
			}
		}
		return $html . '</div>';
	}

	/** Label above the menu (full-screen menu only). */
	private static function nav_pre( $o ) {
		return '' !== $o['panel_t_menu'] ? '<p class="khf-nav__label khf-nav__pre">' . esc_html( $o['panel_t_menu'] ) . '</p>' : '';
	}

	/** Contacts, social links and legal link under the menu (full-screen menu only). */
	private static function nav_post( $o ) {
		if ( empty( $o['panel_info'] ) ) {
			return '';
		}
		$html     = '<div class="khf-nav__post">';
		$contacts = '';
		if ( '' !== $o['email'] ) {
			$contacts .= '<a href="' . esc_url( 'mailto:' . $o['email'] ) . '">' . esc_html( $o['email'] ) . '</a>';
		}
		if ( '' !== $o['phone'] ) {
			$contacts .= ( $contacts ? '<span class="khf-sepdot" aria-hidden="true">|</span>' : '' ) . '<a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $o['phone'] ), array( 'tel' ) ) . '">' . esc_html( $o['phone'] ) . '</a>';
		}
		if ( $contacts ) {
			$html .= ( '' !== $o['panel_t_contact'] ? '<p class="khf-nav__label">' . esc_html( $o['panel_t_contact'] ) . '</p>' : '' ) . '<p class="khf-nav__contacts">' . $contacts . '</p>';
		}
		$socials = self::socials( $o );
		if ( $socials ) {
			$html .= ( '' !== $o['panel_t_social'] ? '<p class="khf-nav__label">' . esc_html( $o['panel_t_social'] ) . '</p>' : '' ) . $socials;
		}
		if ( '' !== $o['panel_legal'] ) {
			$html .= '<p class="khf-nav__legal">' . ( $o['panel_legal_url'] ? '<a href="' . esc_url( $o['panel_legal_url'] ) . '">' . esc_html( $o['panel_legal'] ) . '</a>' : esc_html( $o['panel_legal'] ) ) . '</p>';
		}
		return $html . '</div>';
	}

	/** Zone (l/c/r) and order of each element in the "split" and "custom" layouts. */
	public static function layout( $o ) {
		$lay = array(
			'logo'  => array( 'z' => 'c', 'o' => 0 ),
			'menu'  => array( 'z' => 'l', 'o' => 0 ),
			'menu2' => array( 'z' => 'r', 'o' => 0 ),
			'cta'   => array( 'z' => 'r', 'o' => 1 ),
		);
		if ( 'custom' === $o['preset_header'] && is_array( $o['custom_layout'] ) ) {
			foreach ( $lay as $key => $def ) {
				if ( isset( $o['custom_layout'][ $key ] ) ) {
					$lay[ $key ] = $o['custom_layout'][ $key ] + $def;
				}
			}
		}
		return $lay;
	}

	private static function first_menu() {
		$menus = wp_get_nav_menus();
		return $menus ? $menus[0]->term_id : 0;
	}

	/** Zoned header: left / center / right, with a separate panel for mobile. */
	private static function render_zones( $o ) {
		$m1     = $o['menu'] ? $o['menu'] : self::first_menu();
		$m2     = $o['menu2'];
		$images = 'none' !== $o['hover_img'] ? $o['menu_images'] : array();
		$lay    = self::layout( $o );
		$label  = esc_attr__( 'Main menu', 'kaos-header-footer' );

		$shift = function ( $key, $html ) use ( $lay ) {
			$dx = (int) ( $lay[ $key ]['dx'] ?? 0 );
			return '' !== $html && 0 !== $dx ? '<span class="khf-zi" style="--dx:' . $dx . 'px">' . $html . '</span>' : $html;
		};
		$pieces = array(
			'logo'  => self::logo( $o ),
			'menu'  => $m1 ? '<nav class="khf-dnav" aria-label="' . $label . '">' . self::menu( $m1, 'khf-menu', 3, $images ) . '</nav>' : '',
			'menu2' => $m2 ? '<nav class="khf-dnav" aria-label="' . $label . ' 2">' . self::menu( $m2, 'khf-menu', 3, $images ) . '</nav>' : '',
			'cta'   => self::cta( $o, 'khf-cta--zone' ),
		);

		$zones = array( 'l' => array(), 'c' => array(), 'r' => array() );
		foreach ( $lay as $key => $pos ) {
			if ( '' !== $pieces[ $key ] ) {
				$zones[ $pos['z'] ][] = array( (int) $pos['o'], $shift( $key, $pieces[ $key ] ) );
			}
		}

		$html = '<div class="khf-bar"><div class="khf-wrap khf-zones">';
		foreach ( $zones as $z => $list ) {
			usort(
				$list,
				function ( $a, $b ) {
					return $a[0] <=> $b[0];
				}
			);
			$html .= '<div class="khf-zone khf-zone--' . $z . '">' . implode( '', array_column( $list, 1 ) ) . '</div>';
		}

		// Full-screen panel for mobile: it holds all the menus and the button.
		$html .= '<nav id="khf-nav" class="khf-nav" aria-label="' . $label . '" data-hi="' . esc_attr( $o['hover_img'] ) . '"><div class="khf-nav__main">';
		$html .= self::nav_pre( $o ) . ( $m1 ? self::menu( $m1, 'khf-menu', 3, $images ) : '' ) . ( $m2 ? self::menu( $m2, 'khf-menu', 3, $images ) : '' );
		$html .= self::cta( $o, 'khf-cta--in-nav' );
		if ( '' !== $o['tagline'] ) {
			$html .= '<p class="khf-nav__tagline">' . esc_html( $o['tagline'] ) . '</p>';
		}
		$html .= self::nav_post( $o ) . '</div>' . self::menu_media( $o, $images ) . '</nav>';
		$html .= self::burger( $o );
		return $html . '</div></div>';
	}

	public static function render_header( $o ) {
		if ( in_array( $o['preset_header'], array( 'split', 'custom' ), true ) ) {
			return self::render_zones( $o );
		}
		$menu_id = $o['menu'];
		if ( ! $menu_id ) {
			$menus   = wp_get_nav_menus();
			$menu_id = $menus ? $menus[0]->term_id : 0;
		}
		$images = 'none' !== $o['hover_img'] ? $o['menu_images'] : array();

		$html  = '<div class="khf-bar"><div class="khf-wrap">';
		$html .= self::logo( $o );
		$html .= '<nav id="khf-nav" class="khf-nav" aria-label="' . esc_attr__( 'Main menu', 'kaos-header-footer' ) . '" data-hi="' . esc_attr( $o['hover_img'] ) . '">';
		$html .= '<div class="khf-nav__main">' . self::nav_pre( $o ) . self::menu( $menu_id, 'khf-menu', 3, $images );
		$html .= self::cta( $o, 'khf-cta--in-nav' );
		if ( '' !== $o['tagline'] ) {
			$html .= '<p class="khf-nav__tagline">' . esc_html( $o['tagline'] ) . '</p>';
		}
		$html .= self::nav_post( $o ) . '</div>' . self::menu_media( $o, $images );
		$html .= '</nav>';
		$html .= self::cta( $o, 'khf-cta--bar' );
		$html .= self::burger( $o );
		$html .= '</div></div>';
		return $html;
	}

	/* ------------------------------------------------------------------ */
	/* Footer                                                              */
	/* ------------------------------------------------------------------ */

	/* Top divider: a shape in the page color that "bites" into the top edge of the footer. */
	private static function divider_paths( $shape ) {
		switch ( $shape ) {
			case 'waves':
				return array( array( 'M0,0H1440V62C1260,100 1020,20 780,58C540,96 240,30 0,70Z', .45 ), array( 'M0,0H1440V38C1200,92 960,2 720,32C480,62 240,8 0,52Z', 1 ) );
			case 'curve':
				return array( array( 'M0,0H1440V16Q720,150 0,16Z', 1 ) );
			case 'arch':
				return array( array( 'M0,0H1440V100Q720,-45 0,100Z', 1 ) );
			case 'tilt':
				return array( array( 'M0,0H1440L0,100Z', 1 ) );
			case 'tilt-r':
				return array( array( 'M0,0H1440V100Z', 1 ) );
			case 'triangle':
				return array( array( 'M0,0H1440L760,0 720,100 680,0Z', 1 ) );
			case 'zigzag':
				return array( array( 'M0,0H1440V30' . str_repeat( 'l-30,60l-30,-60', 24 ) . 'Z', 1 ) );
			case 'clouds':
				return array( array( 'M0,0H1440V42' . str_repeat( 'a30,30 0 0 1 -60,0', 24 ) . 'Z', 1 ) );
			default:
				return array( array( 'M0,0H1440V38C1200,92 960,2 720,32C480,62 240,8 0,52Z', 1 ) );
		}
	}

	private static function divider( $o ) {
		$shape = $o['divider'];
		if ( 'none' === $shape ) {
			return '';
		}
		$color = '' !== $o['divider_c'] ? $o['divider_c'] : 'var(--khf-page-bg,#fff)';
		$style = 'height:' . (int) $o['divider_h'] . 'px;color:' . $color . ';opacity:' . round( (int) $o['divider_op'] / 100, 2 ) . ( $o['divider_flip'] ? ';transform:scaleX(-1)' : '' );
		$paths = '';
		foreach ( self::divider_paths( $shape ) as $path ) {
			$paths .= '<path fill="currentColor"' . ( $path[1] < 1 ? ' opacity="' . $path[1] . '"' : '' ) . ' d="' . esc_attr( $path[0] ) . '"/>';
		}
		return '<svg class="khf-wave" viewBox="0 0 1440 100" preserveAspectRatio="none" aria-hidden="true" focusable="false" style="' . esc_attr( $style ) . '">' . $paths . '</svg>';
	}

	/* Footer "back to top" arrow. */
	private static function btt_icon( $kind ) {
		$icons = array(
			'arrow'   => '<path d="M12 19V5M5 12l7-7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
			'chevron' => '<path d="M6 15l6-6 6 6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>',
			'double'  => '<path d="M6 11l6-6 6 6M6 19l6-6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
			'caret'   => '<path d="M12 6l8 12H4z" fill="currentColor"/>',
		);
		return isset( $icons[ $kind ] ) ? '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">' . $icons[ $kind ] . '</svg>' : '';
	}

	/** HTML of the footer custom button ('' if not needed). */
	public static function btt( $o ) {
		if ( 'custom' !== $o['btt_mode'] ) {
			return '';
		}
		$vars = array(
			'--bt-s' => (int) $o['fb_size'] . 'px',
			'--bt-x' => (int) $o['fb_x'] . 'px',
			'--bt-y' => (int) $o['fb_y'] . 'px',
			'--bt-r' => (int) $o['fb_r'] . '%',
		);
		foreach ( array( 'fb_bg' => '--bt-bg', 'fb_fg' => '--bt-fg', 'fb_bd' => '--bt-bd' ) as $k => $var ) {
			if ( '' !== $o[ $k ] ) {
				$vars[ $var ] = $o[ $k ];
			}
		}
		$style = '';
		foreach ( $vars as $k => $v ) {
			$style .= $k . ':' . $v . ';';
		}
		$icon  = self::btt_icon( $o['fb_icon'] );
		$text  = '' !== $o['fb_text'] ? '<span class="khf-top__t">' . esc_html( $o['fb_text'] ) . '</span>' : '';
		$text  = '' === $icon && '' === $text ? '<span class="khf-top__t">' . esc_html__( 'Up', 'kaos-header-footer' ) . '</span>' : $text;
		$class = 'khf-top khf-top--f is-' . $o['fb_pos'] . ' is-' . $o['fb_place'] . ' hov-' . $o['fb_hover'] . ( '' !== $text ? ' has-t' : '' );
		return '<button type="button" class="' . esc_attr( $class ) . '" style="' . esc_attr( $style ) . '" data-after="' . (int) $o['fb_after'] . '" aria-label="' . esc_attr__( 'Back to top', 'kaos-header-footer' ) . '">' . $icon . $text . '</button>';
	}

	/* Content of the grid cells. */
	private static function cell_html( $o, $c ) {
		$title = '' !== $c['title'] ? '<h3 class="khf-fh">' . esc_html( $c['title'] ) . '</h3>' : '';
		switch ( $c['t'] ) {
			case 'text':
				return $title . ( '' !== $c['text'] ? '<div class="khf-cell__text">' . wpautop( $c['text'] ) . '</div>' : '' );
			case 'menu':
				return $title . self::menu( $c['menu'], 'khf-fmenu', 1 );
			case 'contacts':
				return $title . self::contacts( $o );
			case 'socials':
				return $title . self::socials( $o );
			case 'image':
				if ( '' === $c['img'] ) {
					return '';
				}
				$img = '<img src="' . esc_url( $c['img'] ) . '" alt="' . esc_attr( $c['title'] ) . '" loading="lazy" decoding="async">';
				return '' !== $c['link'] ? '<a class="khf-cell__img" href="' . esc_url( $c['link'] ) . '">' . $img . '</a>' : '<span class="khf-cell__img">' . $img . '</span>';
			case 'logo':
				return self::logo( $o );
			case 'cta':
				return '' !== $c['title'] && '' !== $c['link'] ? '<a class="khf-cta" href="' . esc_url( $c['link'] ) . '"><span>' . esc_html( $c['title'] ) . '</span></a>' : '';
			default:
				return '';
		}
	}

	private static function grid( $o, $rv ) {
		$cols  = (int) $o['grid_cols'];
		$total = $cols * (int) $o['grid_rows'];
		$html  = '<div class="khf-cells khf-cells--' . esc_attr( $o['cell_style'] ) . ' khf-cells--v-' . esc_attr( $o['grid_valign'] ) . '" style="grid-template-columns:repeat(' . $cols . ',minmax(0,1fr))">';
		for ( $i = 0; $i < $total; $i++ ) {
			$c = $o['grid_cells'][ $i ] ?? array( 't' => 'empty' );
			if ( 'empty' === ( $c['t'] ?? 'empty' ) ) {
				$html .= '<div class="khf-cell khf-cell--empty" aria-hidden="true"></div>';
				continue;
			}
			$style = 'text-align:' . ( 'c' === $c['al'] ? 'center' : ( 'r' === $c['al'] ? 'right' : 'left' ) ) . ';';
			list( $stack ) = self::font_source( $c['ff'], '' );
			if ( '' !== $stack ) {
				$style .= '--cell-ff:' . $stack . ';';
			}
			if ( $c['fs'] > 0 ) {
				$style .= '--cell-fs:' . (int) $c['fs'] . 'px;';
			}
			if ( '' !== $c['fw'] ) {
				$style .= '--cell-fw:' . (int) $c['fw'] . ';';
			}
			if ( '' !== $c['c'] ) {
				$style .= '--cell-c:' . $c['c'] . ';';
			}
			// The entrance-delay closure already returns a style attribute: we merge it with ours.
			$delay = $rv();
			if ( preg_match( '/--i:(d+)/', $delay, $mm ) ) {
				$style = '--i:' . $mm[1] . ';' . $style;
			}
			$html .= '<div class="khf-cell khf-cell--' . esc_attr( $c['t'] ) . ' khf-rv" style="' . esc_attr( $style ) . '">' . self::cell_html( $o, $c ) . '</div>';
		}
		return $html . '</div>';
	}

	/* Barra inferiore a zone (logo, menu, social, copyright). */
	private static function bar_html( $o, $rv ) {
		$lay = self::bar_layout( $o );
		$p   = array(
			'logo'   => self::logo( $o ),
			'menu'   => $o['menu'] ? self::menu( $o['menu'], 'khf-fmenu khf-fmenu--inline', 1 ) : '',
			'menu2'  => $o['menu2'] ? self::menu( $o['menu2'], 'khf-fmenu khf-fmenu--inline', 1 ) : '',
			'social' => self::socials( $o ),
			'copy'   => self::copyright( $o ),
		);
		$zones = array( 'l' => array(), 'c' => array(), 'r' => array() );
		foreach ( $lay as $key => $pos ) {
			if ( empty( $pos['on'] ) || '' === $p[ $key ] ) {
				continue;
			}
			$dx   = (int) $pos['dx'];
			$html = 0 !== $dx ? '<span class="khf-zi" style="--dx:' . $dx . 'px">' . $p[ $key ] . '</span>' : $p[ $key ];
			$zones[ $pos['z'] ][] = array( (int) $pos['o'], $html );
		}
		if ( ! array_filter( $zones ) ) {
			return '';
		}
		$out = '<div class="khf-fbottom khf-fbar' . $rv() . '>';
		foreach ( $zones as $z => $list ) {
			usort(
				$list,
				function ( $a, $b ) {
					return $a[0] <=> $b[0];
				}
			);
			$out .= '<div class="khf-zone khf-zone--' . $z . '">' . implode( '', array_column( $list, 1 ) ) . '</div>';
		}
		return $out . '</div>';
	}

	public static function bar_layout( $o ) {
		$lay = self::bar_defaults( $o['preset_footer'] );
		foreach ( $lay as $key => $def ) {
			if ( isset( $o['bar_layout'][ $key ] ) ) {
				$lay[ $key ] = $o['bar_layout'][ $key ];
			}
		}
		return $lay;
	}

	public static function render_footer( $o ) {
		$preset = $o['preset_footer'];
		$html   = self::divider( $o ) . '<div class="khf-footer__inner"><div class="khf-wrap">';
		$i      = 0;
		$rv     = function () use ( &$i ) {
			return ' khf-rv" style="--i:' . ( $i++ ) . '"';
		};

		if ( 'cta-big' === $preset || ( 'grid' === $preset && $o['grid_hero'] ) ) {
			$headline = '' !== $o['headline'] ? $o['headline'] : esc_html__( 'Let\'s create something beautiful, together.', 'kaos-header-footer' );
			$cta      = self::cta( $o, 'khf-cta--xl' );
			$html    .= '<div class="khf-big khf-big--' . esc_attr( $o['headline_al'] ) . '">';
			$html    .= '<h2 class="khf-headline' . $rv() . '>' . $headline . '</h2>';
			if ( '' !== $o['tagline'] ) {
				$html .= '<p class="khf-lead' . $rv() . '>' . esc_html( $o['tagline'] ) . '</p>';
			}
			if ( $cta ) {
				$html .= '<div class="khf-big__cta' . $rv() . '>' . $cta . '</div>';
			}
			$html .= '</div>';
		}

		if ( 'grid' === $preset ) {
			$html .= self::grid( $o, $rv );
		} elseif ( 'columns' === $preset ) {
			$html .= '<div class="khf-fgrid">';
			$html .= '<div class="khf-fcol khf-fcol--brand' . $rv() . '>' . self::logo( $o );
			if ( '' !== $o['tagline'] ) {
				$html .= '<p class="khf-lead">' . esc_html( $o['tagline'] ) . '</p>';
			}
			$html .= self::socials( $o ) . '</div>';
			foreach ( array( 'menu', 'menu2', 'menu3' ) as $key ) {
				if ( $o[ $key ] ) {
					$html .= '<div class="khf-fcol' . $rv() . '><h3 class="khf-fh">' . self::menu_title( $o[ $key ] ) . '</h3>' . self::menu( $o[ $key ], 'khf-fmenu', 1 ) . '</div>';
				}
			}
			$contacts = self::contacts( $o );
			if ( $contacts ) {
				$html .= '<div class="khf-fcol' . $rv() . '><h3 class="khf-fh">' . esc_html__( 'Contacts', 'kaos-header-footer' ) . '</h3>' . $contacts . '</div>';
			}
			$html .= '</div>';
		}

		$html .= self::bar_html( $o, $rv );
		$html .= '</div>';
		if ( 'footer' === $o['fb_place'] ) {
			$html .= self::btt( $o );
		}
		return $html . '</div>';
	}
}
