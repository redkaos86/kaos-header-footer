<?php
defined( 'ABSPATH' ) || exit;

/**
 * Enables Elementor on the templates post type.
 */
class KAOSHF_Integrations {

	public static function init() {
		// Elementor reads the "elementor_cpt_support" option to decide where to show the editor.
		add_filter( 'option_elementor_cpt_support', array( __CLASS__, 'elementor_cpt' ) );
		add_filter( 'default_option_elementor_cpt_support', array( __CLASS__, 'elementor_cpt' ) );
	}

	public static function active() {
		return did_action( 'elementor/loaded' ) || class_exists( '\Elementor\Plugin' );
	}

	public static function edit_url( $post_id ) {
		return self::active() ? add_query_arg( array( 'post' => (int) $post_id, 'action' => 'elementor' ), admin_url( 'post.php' ) ) : '';
	}

	public static function elementor_cpt( $value ) {
		if ( ! is_array( $value ) ) {
			$value = array( 'post', 'page' );
		}
		if ( ! in_array( KAOSHF_CPT::POST_TYPE, $value, true ) ) {
			$value[] = KAOSHF_CPT::POST_TYPE;
		}
		return $value;
	}
}
