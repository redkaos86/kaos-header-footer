<?php
defined( 'ABSPATH' ) || exit;

/**
 * Post type that holds the templates. It has no menu of its own: it is managed from the KaosH&F dashboard;
 * it can still be edited with Elementor.
 */
class KHF_CPT {

	const POST_TYPE = 'khf_template';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'wp_robots', array( __CLASS__, 'noindex' ) );
	}

	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'Header & Footer', 'kaos-header-footer' ),
					'singular_name' => __( 'Header/Footer templates', 'kaos-header-footer' ),
				),
				'public'              => true,
				'publicly_queryable'  => true, // Required for the Elementor editor and preview.
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => true,
				'has_archive'         => false,
				'rewrite'             => array( 'slug' => 'khf-template', 'with_front' => false ),
				'supports'            => array( 'title', 'editor', 'revisions' ),
			)
		);
	}

	public static function noindex( $robots ) {
		if ( is_singular( self::POST_TYPE ) || isset( $_GET['khf_preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}
}
