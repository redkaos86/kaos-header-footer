<?php
// Removes the global settings and the downloaded font files: the templates (headers/footers) created by the user are kept.
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'khf_settings' );

$khf_up  = wp_upload_dir( null, false );
$khf_dir = trailingslashit( $khf_up['basedir'] ) . 'kaos-hf-fonts';
if ( is_dir( $khf_dir ) ) {
	$khf_it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $khf_dir, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
	foreach ( $khf_it as $khf_file ) {
		$khf_file->isDir() ? rmdir( $khf_file->getPathname() ) : wp_delete_file( $khf_file->getPathname() ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	rmdir( $khf_dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions
}
