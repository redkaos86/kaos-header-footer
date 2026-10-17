<?php
defined( 'ABSPATH' ) || exit;

/**
 * "KaosH&F" button in the site admin bar: it leads straight to the editor
 * of the header and footer active on the page you are viewing.
 */
class KHF_Adminbar {

	public static function init() {
		add_action( 'admin_bar_menu', array( __CLASS__, 'menu' ), 91 );
		add_action( 'wp_head', array( __CLASS__, 'css' ) );
		add_action( 'admin_head', array( __CLASS__, 'css' ) );
	}

	private static function allowed() {
		return is_admin_bar_showing() && current_user_can( KHF_Admin::capability() ) && ! isset( $_GET['khf_preview'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	private static function edit_url( $id ) {
		return admin_url( 'admin.php?page=' . KHF_Admin::SLUG . '&edit=' . (int) $id );
	}

	/** Explains why there is no header/footer on this page (template turned off or excluded by the rules). */
	private static function why_missing( $kind ) {
		$ids = get_posts(
			array(
				'post_type'      => KHF_CPT::POST_TYPE,
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		$off      = 0;
		$excluded = 0;
		foreach ( $ids as $id ) {
			$o = KHF_Options::get( $id );
			if ( $o['type'] !== $kind ) {
				continue;
			}
			'publish' === get_post_status( $id ) ? $excluded++ : $off++;
		}
		$label = 'header' === $kind ? __( 'header', 'kaos-header-footer' ) : __( 'footer', 'kaos-header-footer' );
		if ( $excluded ) {
			/* translators: %s: header o footer */
			return sprintf( __( 'No %s here: the "Where to show it" rules exclude it', 'kaos-header-footer' ), $label );
		}
		if ( $off ) {
			/* translators: %s: header o footer */
			return sprintf( __( 'No %s here: the template is turned off (turn it on in the editor)', 'kaos-header-footer' ), $label );
		}
		return '';
	}

	public static function menu( $bar ) {
		if ( ! self::allowed() ) {
			return;
		}
		$bar->add_node(
			array(
				'id'    => 'khf',
				'title' => '<span class="khf-ab-icon" aria-hidden="true"></span><span class="ab-label">KaosH&amp;F</span>',
				'href'  => admin_url( 'admin.php?page=' . KHF_Admin::SLUG ),
			)
		);

		if ( ! is_admin() ) {
			foreach ( array( 'header' => __( 'Edit header', 'kaos-header-footer' ), 'footer' => __( 'Edit footer', 'kaos-header-footer' ) ) as $kind => $label ) {
				$t = KHF_Renderer::active( $kind );
				if ( $t ) {
					$title = get_the_title( $t['id'] );
					$bar->add_node(
						array(
							'parent' => 'khf',
							'id'     => 'khf-edit-' . $kind,
							'title'  => esc_html( $label . ': ' . ( '' !== $title ? $title : '#' . $t['id'] ) ),
							'href'   => self::edit_url( $t['id'] ),
						)
					);
				} else {
					$why = self::why_missing( $kind );
					if ( '' !== $why ) {
						$bar->add_node(
							array(
								'parent' => 'khf',
								'id'     => 'khf-why-' . $kind,
								'title'  => esc_html( $why ),
								'href'   => admin_url( 'admin.php?page=' . KHF_Admin::SLUG ),
							)
						);
					}
				}
			}
		}

		$bar->add_node( array( 'parent' => 'khf', 'id' => 'khf-all', 'title' => esc_html__( 'All templates', 'kaos-header-footer' ), 'href' => admin_url( 'admin.php?page=' . KHF_Admin::SLUG ) ) );
	}

	/** Brand icon in front of the button text. */
	public static function css() {
		if ( ! self::allowed() ) {
			return;
		}
		$icon = esc_url( KHF_URL . 'assets/img/mark-white.svg' );
		echo '<style>#wp-admin-bar-khf>.ab-item::before{content:"";display:inline-block;width:20px;height:20px;margin-right:6px;vertical-align:middle;background:url(' . $icon . ') center/contain no-repeat;top:0}#wp-admin-bar-khf>.ab-item{display:flex;align-items:center}</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
