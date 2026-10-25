<?php
/**
 * Plugin Name: SABER College Frontend Performance
 * Description: Local font delivery, critical hero preloads, and deferred loading for the below-fold home video.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Elementor already stores the chosen font families in native widget settings.
 * Keep those settings editable while serving the same families from this site.
 */
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

/**
 * Elementor marks Loop Grid containers as ARIA lists but does not mark each
 * generated loop item as a list item. Add the missing role to the rendered
 * wrappers while keeping the Loop Grid and Loop Item templates fully editable.
 *
 * @param string                 $content Rendered widget content.
 * @param \Elementor\Widget_Base $widget  Elementor widget instance.
 * @return string
 */
function saber_performance_accessible_loop_grid_items( $content, $widget ) {
	if ( 'loop-grid' !== $widget->get_name() || false === strpos( $content, 'data-elementor-type="loop-item"' ) ) {
		return $content;
	}

	return preg_replace_callback(
		'~<div\b([^>]*\bdata-elementor-type=["\']loop-item["\'][^>]*)>~i',
		static function ( $matches ) {
			if ( preg_match( '~\srole=["\'][^"\']*["\']~i', $matches[1] ) ) {
				return $matches[0];
			}

			return '<div role="listitem"' . $matches[1] . '>';
		},
		$content
	);
}
add_filter( 'elementor/widget/render_content', 'saber_performance_accessible_loop_grid_items', 20, 2 );

/**
 * Print the two small font-face declarations inline to avoid another blocking CSS request.
 */
function saber_performance_local_fonts() {
	$base = content_url( '/uploads/saber-performance/fonts/' );
	?>
	<style id="saber-performance-local-fonts">
		@font-face{font-family:'Nunito';font-style:normal;font-weight:100 900;font-display:swap;src:url('<?php echo esc_url( $base . 'nunito-latin-variable.woff2' ); ?>') format('woff2')}
		@font-face{font-family:'Nunito Sans';font-style:normal;font-weight:100 900;font-display:swap;src:url('<?php echo esc_url( $base . 'nunito-sans-latin-variable.woff2' ); ?>') format('woff2')}
	</style>
	<?php
}
add_action( 'wp_head', 'saber_performance_local_fonts', 2 );

/**
 * Preload local fonts and the actual background image used by each critical hero.
 */
function saber_performance_preloads() {
	$uploads = content_url( '/uploads/' );
	$fonts   = $uploads . 'saber-performance/fonts/';
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( $fonts . 'nunito-sans-latin-variable.woff2' )
	);
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( $fonts . 'nunito-latin-variable.woff2' )
	);

	$page_id = get_queried_object_id();
	$heroes  = [
		7   => '2026/08/backgrounnd-banner-hero-home-1024x576.jpg',
		30  => '2026/08/call_lKJ4lRMzKDwgMzLXVhEeMFWG.png',
		34  => '2026/10/student-services-hero-web.png',
		36  => '2026/10/general-overview-hero-web.png',
		870 => '2026/08/backgrounnd-banner-hero-home-1024x576.jpg',
		872 => '2026/08/backgrounnd-banner-hero-home-1024x576.jpg',
	];

	if ( isset( $heroes[ $page_id ] ) ) {
		printf(
			'<link rel="preload" href="%s" as="image" fetchpriority="high">' . "\n",
			esc_url( $uploads . $heroes[ $page_id ] )
		);
	}
}
add_action( 'wp_head', 'saber_performance_preloads', 1 );

/**
 * Keep the home video editable in Elementor while preventing its multi-megabyte
 * file from downloading before the visitor approaches that section.
 *
 * @param string $content Rendered page content.
 * @return string
 */
function saber_performance_defer_home_video( $content ) {
	if ( ! is_front_page() || false === strpos( $content, 'data-id="d01d8ea"' ) ) {
		return $content;
	}

	$poster = wp_get_attachment_image_url( 126, 'large' );
	$content = preg_replace_callback(
		'/<video\b[^>]*data-id="d01d8ea"[^>]*>/i',
		static function ( $matches ) use ( $poster ) {
			$tag = preg_replace( '/\sautoplay(?:="")?/i', '', $matches[0], 1 );
			$tag = preg_replace( '/\spreload="[^"]*"/i', ' preload="none"', $tag, 1, $preload_count );
			if ( 0 === $preload_count ) {
				$tag = preg_replace( '/>$/', ' preload="none">', $tag, 1 );
			}
			$attributes = ' data-saber-lazy-video="true"';
			if ( $poster ) {
				$attributes .= ' poster="' . esc_url( $poster ) . '"';
			}
			return preg_replace( '/>$/', $attributes . '>', $tag, 1 );
		},
		$content,
		1
	);
	$content = preg_replace(
		'/<source\s+src="([^"]*NURSING-IA-VIDEO-1\.webm[^"]*)"\s*>/i',
		'<source data-src="$1">',
		$content,
		1
	);

	return $content;
}
add_filter( 'the_content', 'saber_performance_defer_home_video', 1000 );

/**
 * Restore the video source near the viewport and preserve its prior autoplay behavior.
 */
function saber_performance_home_video_loader() {
	if ( ! is_front_page() ) {
		return;
	}
	?>
	<script id="saber-performance-video-loader">
	(() => {
		const video = document.querySelector('video[data-saber-lazy-video="true"]');
		if (!video) return;
		const source = video.querySelector('source[data-src]');
		if (!source) return;
		let loaded = false;
		const loadVideo = () => {
			if (loaded) return;
			loaded = true;
			source.src = source.dataset.src;
			video.load();
			if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
				video.play().catch(() => {});
			}
		};
		if (!('IntersectionObserver' in window)) {
			loadVideo();
			return;
		}
		const observer = new IntersectionObserver((entries) => {
			if (!entries.some((entry) => entry.isIntersecting)) return;
			observer.disconnect();
			loadVideo();
		}, { rootMargin: '500px 0px' });
		observer.observe(video);
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'saber_performance_home_video_loader', 30 );
