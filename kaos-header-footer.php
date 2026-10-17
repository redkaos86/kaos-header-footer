<?php
/**
 * Plugin Name:       Kaos Header & Footer
 * Description:       Headers and footers for Elementor: emotional presets or layouts designed in Elementor, scroll effects (sticky, smart, transparent, glass, floating island…) and a "reveal" footer.
 * Version:           0.9.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Requires Plugins:  elementor
 * Author:            DrKaos
 * License:           GPL-2.0-or-later
 * Text Domain:       kaos-header-footer
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'KHF_VERSION', '0.9.0' );
define( 'KHF_FILE', __FILE__ );
define( 'KHF_DIR', plugin_dir_path( __FILE__ ) );
define( 'KHF_URL', plugin_dir_url( __FILE__ ) );

foreach ( array( 'fx', 'options', 'fonts', 'presets', 'cpt', 'conditions', 'integrations', 'renderer', 'preview', 'rest', 'admin', 'adminbar', 'elementor' ) as $khf_file ) {
	require_once KHF_DIR . 'includes/class-khf-' . $khf_file . '.php';
}
unset( $khf_file );

final class KHF_Plugin {

	public static function init() {
		KHF_CPT::init();
		KHF_Integrations::init();
		KHF_Fx::init();
		KHF_Renderer::init();
		KHF_Preview::init();
		KHF_Rest::init();
		KHF_Adminbar::init();
		add_action( 'elementor/init', array( 'KHF_Elementor', 'init' ) );

		if ( is_admin() ) {
			KHF_Admin::init();
		}
	}

	public static function activate() {
		KHF_CPT::register();
		flush_rewrite_rules();
		if ( false === get_option( KHF_Options::SETTINGS_KEY ) ) {
			add_option( KHF_Options::SETTINGS_KEY, KHF_Options::settings_defaults() );
		}
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}
}

register_activation_hook( __FILE__, array( 'KHF_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'KHF_Plugin', 'deactivate' ) );
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'kaos-header-footer', false, dirname( plugin_basename( KHF_FILE ) ) . '/languages' );
	},
	0
);
add_action( 'plugins_loaded', array( 'KHF_Plugin', 'init' ) );
