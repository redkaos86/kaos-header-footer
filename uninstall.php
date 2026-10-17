<?php
// Removes the global settings and the downloaded font files: the templates (headers/footers) created by the user are kept.
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'kaoshf_settings' );

$kaoshf_up  = wp_upload_dir( null, false );
$kaoshf_dir = trailingslashit( $kaoshf_up['basedir'] ) . 'kaos-hf-fonts';
if ( is_dir( $kaoshf_dir ) ) {
	$kaoshf_it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $kaoshf_dir, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
	foreach ( $kaoshf_it as $kaoshf_file ) {
		$kaoshf_file->isDir() ? rmdir( $kaoshf_file->getPathname() ) : wp_delete_file( $kaoshf_file->getPathname() ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	rmdir( $kaoshf_dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions
}
