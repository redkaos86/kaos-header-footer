<?php
defined( 'ABSPATH' ) || exit;

/**
 * REST API used by the dashboard and the editor.
 */
class KAOSHF_Rest {

	const NS = 'kaos-hf/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function can() {
		return current_user_can( KAOSHF_Admin::capability() );
	}

	public static function routes() {
		$base = array( 'permission_callback' => array( __CLASS__, 'can' ) );

		register_rest_route(
			self::NS,
			'/templates',
			array(
				array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'index' ) ) + $base,
				array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'create' ) ) + $base,
			)
		);
		register_rest_route(
			self::NS,
			'/templates/(?P<id>\d+)',
			array(
				array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'show' ) ) + $base,
				array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'save' ) ) + $base,
				array( 'methods' => 'DELETE', 'callback' => array( __CLASS__, 'destroy' ) ) + $base,
			)
		);
		register_rest_route( self::NS, '/templates/(?P<id>\d+)/duplicate', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'duplicate' ) ) + $base );
		register_rest_route( self::NS, '/templates/(?P<id>\d+)/draft', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'draft' ) ) + $base );
		register_rest_route( self::NS, '/search', array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'search' ) ) + $base );
		register_rest_route( self::NS, '/import', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'import' ) ) + $base );
		register_rest_route(
			self::NS,
			'/settings',
			array(
				array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'get_settings' ) ) + $base,
				array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'save_settings' ) ) + $base,
			)
		);
	}

	/* ------------------------------------------------------------------ */
	/* Utilities                                                           */
	/* ------------------------------------------------------------------ */

	private static function post( $request ) {
		$post = get_post( (int) $request['id'] );
		if ( ! $post || KAOSHF_CPT::POST_TYPE !== $post->post_type || ! current_user_can( 'edit_post', $post->ID ) ) {
			return new WP_Error( 'kaoshf_not_found', __( 'Template not found.', 'kaos-header-footer' ), array( 'status' => 404 ) );
		}
		return $post;
	}

	private static function card( $post ) {
		$o = KAOSHF_Options::get( $post->ID );
		return array(
			'id'         => $post->ID,
			'title'      => '' !== $post->post_title ? $post->post_title : __( '(untitled)', 'kaos-header-footer' ),
			'type'       => $o['type'],
			'source'     => $o['source'],
			'status'     => 'publish' === $post->post_status ? 'publish' : 'draft',
			'summary'    => KAOSHF_Conditions::summary( $o ),
			'modified'   => get_post_modified_time( 'c', true, $post ),
			'thumbUrl'   => KAOSHF_Preview::url( $post->ID, false, true ),
			'liveUrl'    => KAOSHF_Preview::url( $post->ID ),
			'elementor'  => KAOSHF_Integrations::edit_url( $post->ID ),
		);
	}

	private static function detail( $post ) {
		$o = KAOSHF_Options::get( $post->ID );
		return self::card( $post ) + array(
			'opts'    => $o,
			'logoUrl' => $o['logo_id'] ? (string) wp_get_attachment_image_url( $o['logo_id'], 'medium' ) : '',
		);
	}

	private static function insert( $title, $opts, $status = 'publish' ) {
		$id = wp_insert_post(
			array(
				'post_type'   => KAOSHF_CPT::POST_TYPE,
				'post_status' => $status,
				'post_title'  => $title,
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		update_post_meta( $id, KAOSHF_Options::META_KEY, $opts );
		if ( 'builder' === $opts['source'] ) {
			update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		}
		return get_post( $id );
	}

	/* ------------------------------------------------------------------ */
	/* Template                                                            */
	/* ------------------------------------------------------------------ */

	public static function index() {
		$posts = get_posts(
			array(
				'post_type'      => KAOSHF_CPT::POST_TYPE,
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 200,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);
		return array_map( array( __CLASS__, 'card' ), $posts );
	}

	public static function show( $request ) {
		$post = self::post( $request );
		return is_wp_error( $post ) ? $post : self::detail( $post );
	}

	public static function create( $request ) {
		$body = (array) $request->get_json_params();
		$opts = KAOSHF_Options::defaults();

		$opts['type']   = 'footer' === ( $body['type'] ?? '' ) ? 'footer' : 'header';
		$opts['source'] = 'builder' === ( $body['source'] ?? '' ) && KAOSHF_Integrations::active() ? 'builder' : 'preset';
		if ( 'footer' === $opts['type'] ) {
			$opts['fx']['mode'] = 'none';
		}
		$opts = KAOSHF_Options::sanitize( $opts );

		$title = sanitize_text_field( $body['title'] ?? '' );
		$post  = self::insert( '' !== $title ? $title : ( 'footer' === $opts['type'] ? __( 'New footer', 'kaos-header-footer' ) : __( 'New header', 'kaos-header-footer' ) ), $opts, 'draft' );
		return is_wp_error( $post ) ? $post : self::detail( $post );
	}

	public static function save( $request ) {
		$post = self::post( $request );
		if ( is_wp_error( $post ) ) {
			return $post;
		}
		$body = (array) $request->get_json_params();

		$update = array( 'ID' => $post->ID );
		if ( isset( $body['title'] ) ) {
			$update['post_title'] = sanitize_text_field( $body['title'] );
		}
		if ( isset( $body['status'] ) ) {
			$update['post_status'] = 'publish' === $body['status'] ? 'publish' : 'draft';
		}
		wp_update_post( $update );

		if ( isset( $body['opts'] ) && is_array( $body['opts'] ) ) {
			$opts = KAOSHF_Options::sanitize( $body['opts'] );
			update_post_meta( $post->ID, KAOSHF_Options::META_KEY, $opts );
			KAOSHF_Fonts::prepare_for( $opts );
			if ( 'builder' === $opts['source'] ) {
				update_post_meta( $post->ID, '_elementor_edit_mode', 'builder' );
			}
		}
		delete_transient( KAOSHF_Preview::draft_key( $post->ID ) );

		return self::detail( get_post( $post->ID ) );
	}

	public static function destroy( $request ) {
		$post = self::post( $request );
		if ( is_wp_error( $post ) ) {
			return $post;
		}
		wp_delete_post( $post->ID, true );
		return array( 'deleted' => true );
	}

	public static function duplicate( $request ) {
		$post = self::post( $request );
		if ( is_wp_error( $post ) ) {
			return $post;
		}
		$copy = self::insert( $post->post_title . ' ' . __( '(copy)', 'kaos-header-footer' ), KAOSHF_Options::get( $post->ID ), 'draft' );
		if ( is_wp_error( $copy ) ) {
			return $copy;
		}
		$copy_id = $copy->ID;
		// Content designed with Elementor is copied together with its settings.
		wp_update_post( array( 'ID' => $copy_id, 'post_content' => $post->post_content ) );
		foreach ( array( '_elementor_data', '_elementor_edit_mode', '_elementor_template_type', '_elementor_version', '_elementor_page_settings' ) as $key ) {
			$value = get_post_meta( $post->ID, $key, true );
			if ( '' !== $value ) {
				update_post_meta( $copy_id, $key, wp_slash( $value ) );
			}
		}
		return self::card( get_post( $copy_id ) );
	}

	/** Saves a temporary draft and returns the URL of the preview that uses it. */
	public static function draft( $request ) {
		$post = self::post( $request );
		if ( is_wp_error( $post ) ) {
			return $post;
		}
		$body = (array) $request->get_json_params();
		set_transient( KAOSHF_Preview::draft_key( $post->ID ), KAOSHF_Options::sanitize( (array) ( $body['opts'] ?? array() ) ), HOUR_IN_SECONDS );
		return array( 'url' => KAOSHF_Preview::url( $post->ID, true ) );
	}

	public static function import( $request ) {
		$body = (array) $request->get_json_params();
		if ( empty( $body['khf'] ) || ! is_array( $body['opts'] ?? null ) ) {
			return new WP_Error( 'kaoshf_import', __( 'The file is not a valid KaosH&F template.', 'kaos-header-footer' ), array( 'status' => 400 ) );
		}
		$opts = KAOSHF_Options::sanitize( $body['opts'] );
		if ( 'builder' === $opts['source'] && ! KAOSHF_Integrations::active() ) {
			$opts['source'] = 'preset';
		}
		$post = self::insert( sanitize_text_field( $body['title'] ?? __( 'Template imported', 'kaos-header-footer' ) ), $opts, 'draft' );
		return is_wp_error( $post ) ? $post : self::card( $post );
	}

	/** Search for pages, posts and other content for the display rules. */
	public static function search( $request ) {
		$q     = sanitize_text_field( (string) $request->get_param( 'q' ) );
		$types = array_values( array_diff( get_post_types( array( 'public' => true ) ), array( KAOSHF_CPT::POST_TYPE, 'attachment' ) ) );
		$args  = array(
			'post_type'      => $types,
			'post_status'    => 'publish',
			'posts_per_page' => 12,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		);
		if ( ctype_digit( $q ) && '' !== $q ) {
			$args['p'] = (int) $q;
		} elseif ( '' !== $q ) {
			$args['s'] = $q;
		}
		$out = array();
		foreach ( get_posts( $args ) as $p ) {
			$obj   = get_post_type_object( $p->post_type );
			$out[] = array(
				'id'    => $p->ID,
				'title' => '' !== $p->post_title ? $p->post_title : '#' . $p->ID,
				'type'  => $obj ? $obj->labels->singular_name : $p->post_type,
			);
		}
		return $out;
	}

	/* ------------------------------------------------------------------ */
	/* Impostazioni globali                                                */
	/* ------------------------------------------------------------------ */

	public static function get_settings() {
		return KAOSHF_Options::settings();
	}

	public static function save_settings( $request ) {
		$clean = KAOSHF_Options::sanitize_settings( (array) $request->get_json_params() );
		update_option( KAOSHF_Options::SETTINGS_KEY, $clean );
		return $clean;
	}
}
