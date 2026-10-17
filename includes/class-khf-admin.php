<?php
defined( 'ABSPATH' ) || exit;

/**
 * Admin page (dashboard and editor) and the "hide on this page" option.
 */
class KAOSHF_Admin {

	const SLUG = 'kaos-hf';

	public static function capability() {
		return apply_filters( 'kaoshf_capability', 'manage_options' );
	}

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'admin_body_class', array( __CLASS__, 'body_class' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( KAOSHF_FILE ), array( __CLASS__, 'action_links' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_off' ), 10, 2 );
	}

	private static function is_editor() {
		return isset( $_GET['edit'] ) && absint( $_GET['edit'] ) > 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	public static function body_class( $classes ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 'toplevel_page_' . self::SLUG === $screen->id ) {
			$classes .= ' khf-dark';
		}
		return $classes;
	}

	public static function menu() {
		$icon = 'data:image/svg+xml;base64,' . base64_encode( (string) file_get_contents( KAOSHF_DIR . 'assets/img/menu-icon.svg' ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		add_menu_page( 'KaosH&F', 'KaosH&F', self::capability(), self::SLUG, array( __CLASS__, 'page' ), $icon, 58 );
	}

	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=' . self::SLUG ) ) . '">' . esc_html__( 'Dashboard', 'kaos-header-footer' ) . '</a>' );
		return $links;
	}

	private static function menus() {
		$out = array();
		foreach ( wp_get_nav_menus() as $m ) {
			$out[] = array( 'id' => (int) $m->term_id, 'name' => $m->name );
		}
		return $out;
	}

	/** Items of each menu (id => list), used to pick the hover images. */
	private static function menu_items() {
		$out = array();
		foreach ( wp_get_nav_menus() as $m ) {
			$items = wp_get_nav_menu_items( $m->term_id );
			$list  = array();
			foreach ( (array) $items as $item ) {
				if ( 0 === (int) $item->menu_item_parent ) {
					$list[] = array( 'id' => (int) $item->ID, 'title' => $item->title );
				}
			}
			$out[ $m->term_id ] = $list;
		}
		return $out;
	}

	private static function conditions() {
		$out = array();
		foreach ( KAOSHF_Conditions::available() as $key => $label ) {
			$out[] = array( 'key' => (string) $key, 'label' => $label );
		}
		return $out;
	}

	private static function ver( $rel ) {
		$file = KAOSHF_DIR . $rel;
		return is_readable( $file ) ? (string) filemtime( $file ) : KAOSHF_VERSION;
	}

	public static function enqueue( $hook ) {
		if ( 'toplevel_page_' . self::SLUG !== $hook ) {
			return;
		}

		$config = array(
			'restUrl'   => esc_url_raw( rest_url( KAOSHF_Rest::NS ) ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'adminUrl'  => admin_url( 'admin.php?page=' . self::SLUG ),
			'version'   => KAOSHF_VERSION,
			'markUrl'   => KAOSHF_URL . 'assets/img/mark-white.svg?v=' . self::ver( 'assets/img/mark-white.svg' ),
			'logoUrl'   => KAOSHF_URL . 'assets/img/logo-white.svg?v=' . self::ver( 'assets/img/logo-white.svg' ),
			'elementor' => KAOSHF_Integrations::active(),
		);

		wp_enqueue_style( 'khf-admin', KAOSHF_URL . 'assets/css/admin.css', array(), self::ver( 'assets/css/admin.css' ) );

		if ( self::is_editor() ) {
			wp_enqueue_media();
			$settings = KAOSHF_Options::settings();
			$config  += array(
				'templateId' => absint( $_GET['edit'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'schema'     => KAOSHF_Fx::schema(),
				'presets'    => array(
					'header' => KAOSHF_Presets::presets( 'header' ),
					'footer' => KAOSHF_Presets::presets( 'footer' ),
				),
				'moods'      => KAOSHF_Presets::moods(),
				'menus'      => self::menus(),
				'conditions' => self::conditions(),
				'menuItems'  => self::menu_items(),
				'menuFx'     => KAOSHF_Presets::menu_effects(),
				'dividers'   => KAOSHF_Presets::dividers(),
				'fmenuFx'    => KAOSHF_Presets::footer_link_effects(),
				'barDefaults' => array(
					'columns' => KAOSHF_Presets::bar_defaults( 'columns' ),
					'cta-big' => KAOSHF_Presets::bar_defaults( 'cta-big' ),
					'minimal' => KAOSHF_Presets::bar_defaults( 'minimal' ),
					'grid'    => KAOSHF_Presets::bar_defaults( 'grid' ),
				),
				'fonts'      => array_map(
					function ( $f ) {
						return $f[0];
					},
					KAOSHF_Presets::fonts()
				),
				'burgers'    => KAOSHF_Presets::burgers(),
				'bp'         => array( 'tablet' => (int) $settings['bp_tablet'], 'mobile' => (int) $settings['bp_mobile'] ),
			);
			wp_enqueue_script( 'khf-editor', KAOSHF_URL . 'assets/js/editor.js', array( 'wp-i18n' ), self::ver( 'assets/js/editor.js' ), true );
			wp_set_script_translations( 'khf-editor', 'kaos-header-footer', KAOSHF_DIR . 'languages' );
			wp_add_inline_script( 'khf-editor', 'window.KhfConfig = ' . wp_json_encode( $config ) . ';', 'before' );
		} else {
			wp_enqueue_script( 'khf-list', KAOSHF_URL . 'assets/js/admin-list.js', array( 'wp-i18n' ), self::ver( 'assets/js/admin-list.js' ), true );
			wp_set_script_translations( 'khf-list', 'kaos-header-footer', KAOSHF_DIR . 'languages' );
			wp_add_inline_script( 'khf-list', 'window.KhfConfig = ' . wp_json_encode( $config ) . ';', 'before' );
		}
	}

	public static function page() {
		if ( ! current_user_can( self::capability() ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'kaos-header-footer' ) );
		}
		if ( self::is_editor() ) {
			echo '<div id="khf-editor" class="khe-root"><div class="khe-loading">' . esc_html__( 'Loading the editor…', 'kaos-header-footer' ) . '</div></div>';
			return;
		}
		echo '<div class="khl-wrap"><h1 class="screen-reader-text">KaosH&amp;F</h1><div id="khl-app"><div class="khl-loading">' . esc_html__( 'Loading…', 'kaos-header-footer' ) . '</div></div><input type="file" id="khl-import-file" accept="application/json,.json" hidden></div>';
	}

	/* ------------------------------------------------------------------ */
	/* Hide header/footer on a single page                              */
	/* ------------------------------------------------------------------ */

	public static function add_boxes() {
		foreach ( get_post_types( array( 'public' => true ) ) as $pt ) {
			if ( ! in_array( $pt, array( KAOSHF_CPT::POST_TYPE, 'attachment' ), true ) ) {
				add_meta_box( 'khf-off', 'KaosH&F', array( __CLASS__, 'render_off_box' ), $pt, 'side', 'low' );
			}
		}
	}

	private static function check_meta( $key, $post_id, $label ) {
		return '<label><input type="checkbox" name="' . esc_attr( $key ) . '" value="1" ' . checked( (bool) get_post_meta( $post_id, $key, true ), true, false ) . ' /> ' . esc_html( $label ) . '</label>';
	}

	public static function render_off_box( $post ) {
		wp_nonce_field( 'kaoshf_off', 'kaoshf_off_nonce' );
		echo '<p>' . self::check_meta( '_kaoshf_off_header', $post->ID, __( 'Hide the Kaos header', 'kaos-header-footer' ) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<p>' . self::check_meta( '_kaoshf_off_footer', $post->ID, __( 'Hide the Kaos footer', 'kaos-header-footer' ) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public static function save_off( $post_id, $post ) {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['kaoshf_off_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kaoshf_off_nonce'] ) ), 'kaoshf_off' ) ) {
			return;
		}
		foreach ( array( '_kaoshf_off_header', '_kaoshf_off_footer' ) as $key ) {
			if ( empty( $_POST[ $key ] ) ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, 1 );
			}
		}
	}
}
