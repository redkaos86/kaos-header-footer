<?php
/**
 * Plugin Name:       KaosH&F
 * Description:       Headers and footers for Elementor: emotional presets or layouts designed in Elementor, scroll effects (sticky, smart, transparent, glass, floating island…) and a "reveal" footer.
 * Version:           0.9.2
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Requires Plugins:  elementor
 * Author:            DrKaos
 * License:           GPL-2.0-or-later
 * Update URI:        https://github.com/redkaos86/kaos-header-footer
 * Text Domain:       kaos-header-footer
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'KAOSHF_VERSION', '0.9.2' );
define( 'KAOSHF_FILE', __FILE__ );
define( 'KAOSHF_DIR', plugin_dir_path( __FILE__ ) );
define( 'KAOSHF_URL', plugin_dir_url( __FILE__ ) );

foreach ( array( 'fx', 'options', 'fonts', 'presets', 'cpt', 'conditions', 'integrations', 'renderer', 'preview', 'rest', 'admin', 'adminbar', 'elementor', 'updater' ) as $kaoshf_file ) {
	require_once KAOSHF_DIR . 'includes/class-khf-' . $kaoshf_file . '.php';
}
unset( $kaoshf_file );

final class KAOSHF_Plugin {

	/** Moves the data saved before 0.9.0 (when the prefix was khf_, too short for WordPress.org) to the new names. */
	private static function migrate() {
		if ( get_option( 'kaoshf_migrated' ) ) {
			return;
		}
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update( $wpdb->posts, array( 'post_type' => 'kaoshf_template' ), array( 'post_type' => 'khf_template' ) );
		foreach ( array( '_khf_opts' => '_kaoshf_opts', '_khf_off_header' => '_kaoshf_off_header', '_khf_off_footer' => '_kaoshf_off_footer' ) as $old => $new ) {
			$wpdb->update( $wpdb->postmeta, array( 'meta_key' => $new ), array( 'meta_key' => $old ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		}
		// phpcs:enable
		$old_settings = get_option( 'khf_settings' );
		if ( false !== $old_settings && false === get_option( 'kaoshf_settings' ) ) {
			add_option( 'kaoshf_settings', $old_settings );
		}
		delete_option( 'khf_settings' );
		update_option( 'kaoshf_migrated', 1, false );
	}

	public static function init() {
		KAOSHF_Updater::init();
		self::migrate();
		KAOSHF_CPT::init();
		KAOSHF_Integrations::init();
		KAOSHF_Fx::init();
		KAOSHF_Renderer::init();
		KAOSHF_Preview::init();
		KAOSHF_Rest::init();
		KAOSHF_Adminbar::init();
		add_action( 'elementor/init', array( 'KAOSHF_Elementor', 'init' ) );

		if ( is_admin() ) {
			KAOSHF_Admin::init();
		}
	}

	public static function activate() {
		KAOSHF_CPT::register();
		flush_rewrite_rules();
		if ( false === get_option( KAOSHF_Options::SETTINGS_KEY ) ) {
			add_option( KAOSHF_Options::SETTINGS_KEY, KAOSHF_Options::settings_defaults() );
		}
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}
}

register_activation_hook( __FILE__, array( 'KAOSHF_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'KAOSHF_Plugin', 'deactivate' ) );
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'kaos-header-footer', false, dirname( plugin_basename( KAOSHF_FILE ) ) . '/languages' );
	},
	0
);
add_action( 'plugins_loaded', array( 'KAOSHF_Plugin', 'init' ) );
