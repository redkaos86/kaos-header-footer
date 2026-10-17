<?php
/**
 * Demo page for the header and footer preview.
 *
 * @package kaos-header-footer
 */

defined( 'ABSPATH' ) || exit;

$thumb  = ! empty( $GLOBALS['kaoshf_preview_thumb'] );
$kind   = $GLOBALS['kaoshf_preview_kind'] ?? 'header';
$blocks = $thumb ? 1 : 6;
$lorem  = __( 'Demo content: it only shows how the header and footer behave while you scroll the page. Scroll to see sticky, transparency, adaptive colors and the footer coming in.', 'kaos-header-footer' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
	<style>
		html,body{margin:0}
		body.khf-preview{background:#fff;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;color:#1b1d21}
		.khf-demo{display:block;padding:clamp(48px,8vw,110px) clamp(20px,6vw,80px);min-height:60vh}
		.khf-demo h2{margin:0 0 .5em;font-size:clamp(1.6rem,4vw,3rem);line-height:1.1}
		.khf-demo p{max-width:56ch;margin:0;font-size:1.05rem;line-height:1.7;opacity:.8}
		.khf-demo--hero{min-height:92vh;display:flex;flex-direction:column;justify-content:center;color:#fff;background:linear-gradient(135deg,#1b1d21,#2a1559 60%,#5c00e6)}
		.khf-demo--alt{background:#f3f4f6}
		.khf-demo--dark{background:#1b1d21;color:#fff}
		<?php if ( $thumb ) : ?>
		.khf-demo{min-height:0;padding-top:140px}.khf-demo--hero{min-height:620px}
		html{overflow:hidden}*{animation-duration:0s!important;transition-duration:0s!important}
		<?php endif; ?>
	</style>
</head>
<body <?php body_class( 'khf-preview' ); ?>>
<?php wp_body_open(); ?>
<div id="page">
	<?php if ( $thumb && 'footer' === $kind ) : ?>
		<section class="khf-demo khf-demo--alt khf-on-light" style="min-height:260px"><h2><?php esc_html_e( 'Page content', 'kaos-header-footer' ); ?></h2><p><?php echo esc_html( $lorem ); ?></p></section>
	<?php else : ?>
		<section class="khf-demo khf-demo--hero khf-on-dark">
			<h2><?php esc_html_e( 'An opening that moves you', 'kaos-header-footer' ); ?></h2>
			<p><?php echo esc_html( $lorem ); ?></p>
		</section>
		<?php for ( $i = 1; $i <= $blocks; $i++ ) : ?>
			<?php
			$dark = 0 === $i % 3;
			$alt  = 0 === $i % 2;
			?>
			<section class="khf-demo <?php echo $dark ? 'khf-demo--dark khf-on-dark' : ( $alt ? 'khf-demo--alt khf-on-light' : 'khf-on-light' ); ?>">
				<h2><?php /* translators: %d: numero sezione */ echo esc_html( sprintf( __( 'Section %d', 'kaos-header-footer' ), $i ) ); ?></h2>
				<p><?php echo esc_html( $lorem ); ?></p>
			</section>
		<?php endfor; ?>
	<?php endif; ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
