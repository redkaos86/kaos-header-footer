<?php
defined( 'ABSPATH' ) || exit;

/**
 * Display rules: where a template should show.
 *
 * The template shows everywhere, unless "Only on…" rules exist: in that case it shows only
 * where one of them matches. "Except on…" rules always remove it.
 */
class KHF_Conditions {

	/** Generic selectable targets: key => label (the specific page is handled separately). */
	public static function available() {
		$list = array(
			'front'   => __( 'Home page (static or latest posts)', 'kaos-header-footer' ),
			'blog'    => __( 'Blog page', 'kaos-header-footer' ),
			'archive' => __( 'Archives (categories, tags, authors…)', 'kaos-header-footer' ),
			'search'  => __( 'Search results', 'kaos-header-footer' ),
			'404'     => __( '404 page', 'kaos-header-footer' ),
		);
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $pt ) {
			if ( KHF_CPT::POST_TYPE === $pt->name || 'attachment' === $pt->name ) {
				continue;
			}
			/* translators: %s: content type name */
			$list[ 'pt:' . $pt->name ] = sprintf( __( 'All content: %s', 'kaos-header-footer' ), $pt->labels->name );
		}
		return $list;
	}

	private static function hit( $rule ) {
		switch ( $rule['t'] ) {
			case 'front':
				return is_front_page();
			case 'blog':
				return is_home();
			case 'archive':
				return is_archive();
			case 'search':
				return is_search();
			case '404':
				return is_404();
			case 'id':
				$qid = get_queried_object_id();
				return $qid && (int) $rule['id'] === (int) $qid && ( is_singular() || is_home() || is_front_page() );
			default:
				return 0 === strpos( $rule['t'], 'pt:' ) && is_singular( substr( $rule['t'], 3 ) );
		}
	}

	public static function matches( $o ) {
		$rules = is_array( $o['cond_rules'] ) ? $o['cond_rules'] : array();

		$only = array();
		foreach ( $rules as $rule ) {
			if ( 'except' === $rule['m'] && self::hit( $rule ) ) {
				return false;
			}
			if ( 'only' === $rule['m'] ) {
				$only[] = $rule;
			}
		}
		if ( ! $only ) {
			return true;
		}
		foreach ( $only as $rule ) {
			if ( self::hit( $rule ) ) {
				return true;
			}
		}
		return false;
	}

	private static function label( $rule ) {
		if ( 'id' === $rule['t'] ) {
			return '' !== $rule['title'] ? $rule['title'] : '#' . $rule['id'];
		}
		$all = self::available();
		return $all[ $rule['t'] ] ?? $rule['t'];
	}

	/** Human-readable summary for the dashboard. */
	public static function summary( $o ) {
		$only   = array();
		$except = array();
		foreach ( (array) $o['cond_rules'] as $rule ) {
			if ( 'only' === $rule['m'] ) {
				$only[] = self::label( $rule );
			} else {
				$except[] = self::label( $rule );
			}
		}
		$txt = $only ? sprintf( /* translators: %s: elenco */ __( 'Only: %s', 'kaos-header-footer' ), implode( ', ', $only ) ) : __( 'Entire site', 'kaos-header-footer' );
		if ( $except ) {
			$txt .= ' — ' . sprintf( /* translators: %s: elenco */ __( 'except: %s', 'kaos-header-footer' ), implode( ', ', $except ) );
		}
		return $txt;
	}
}
