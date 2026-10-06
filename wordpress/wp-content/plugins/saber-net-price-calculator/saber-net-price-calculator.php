<?php
/**
 * Plugin Name: SABER Net Price Calculator
 * Description: Hosts and embeds the legacy SABER College Net Price Calculator during the staged migration.
 * Version: 1.2.0
 * Author: SABER College
 * Requires at least: 6.9
 * Requires PHP: 8.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const SABER_NPC_QUERY_VAR = 'saber_net_price_calculator_app';
const SABER_NPC_ROUTE     = 'saber-net-price-calculator-app';
const SABER_NPC_VERSION   = '1.2.0';

/**
 * Registers the private implementation route used to serve the legacy app.
 */
function saber_npc_register_route() {
	add_rewrite_rule(
		'^' . SABER_NPC_ROUTE . '/?$',
		'index.php?' . SABER_NPC_QUERY_VAR . '=1',
		'top'
	);
}
add_action( 'init', 'saber_npc_register_route' );

/**
 * Allows WordPress to recognize the calculator route query variable.
 *
 * @param string[] $query_vars Public query variables.
 * @return string[]
 */
function saber_npc_register_query_var( $query_vars ) {
	$query_vars[] = SABER_NPC_QUERY_VAR;

	return $query_vars;
}
add_filter( 'query_vars', 'saber_npc_register_query_var' );

/**
 * Streams the original self-contained calculator without altering its markup.
 */
function saber_npc_serve_application() {
	if ( '1' !== get_query_var( SABER_NPC_QUERY_VAR ) ) {
		return;
	}

	$application_file = plugin_dir_path( __FILE__ ) . 'app/Net-Price-Calculator.html';
	$stylesheet_file  = plugin_dir_path( __FILE__ ) . 'assets/saber-design-system.css';

	if ( ! is_readable( $application_file ) || ! is_readable( $stylesheet_file ) ) {
		wp_die(
			esc_html__( 'The Net Price Calculator application assets are unavailable.', 'saber-net-price-calculator' ),
			esc_html__( 'Net Price Calculator', 'saber-net-price-calculator' ),
			array( 'response' => 500 )
		);
	}

	$application_html = file_get_contents( $application_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$stylesheet       = file_get_contents( $stylesheet_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$stylesheet       = str_replace(
		'{{SABER_NPC_PLUGIN_URL}}',
		esc_url_raw( plugin_dir_url( __FILE__ ) ),
		$stylesheet
	);
	$styled_html      = preg_replace(
		'/<\/head>/i',
		'<style id="saber-net-price-design-system">' . $stylesheet . '</style></head>',
		$application_html,
		1
	);

	$is_embedded = isset( $_GET['embed'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['embed'] ) );
	if ( $is_embedded ) {
		$styled_html = preg_replace(
			'/<body>/i',
			'<body class="saber-npc-embedded">',
			$styled_html,
			1
		);
	}

	status_header( 200 );
	header( 'Content-Type: text/html; charset=UTF-8' );
	header( 'X-Robots-Tag: noindex, nofollow', true );
	header( 'X-Content-Type-Options: nosniff', true );

	echo $styled_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'template_redirect', 'saber_npc_serve_application', 0 );

/**
 * Loads the small presentation layer used by the Elementor shortcode.
 */
function saber_npc_enqueue_embed_assets() {
	wp_enqueue_style(
		'saber-net-price-calculator-embed',
		plugin_dir_url( __FILE__ ) . 'assets/saber-embed.css',
		array(),
		SABER_NPC_VERSION
	);

	wp_enqueue_script(
		'saber-net-price-calculator-embed',
		plugin_dir_url( __FILE__ ) . 'assets/saber-embed.js',
		array(),
		SABER_NPC_VERSION,
		true
	);
}

/**
 * Renders the calculator in an isolated, same-origin frame.
 *
 * @return string
 */
function saber_npc_shortcode() {
	saber_npc_enqueue_embed_assets();

	$frame_id = wp_unique_id( 'saber-npc-frame-' );
	$src      = add_query_arg( 'embed', '1', home_url( '/' . SABER_NPC_ROUTE . '/' ) );

	return sprintf(
		'<div id="net-price-calculator" class="saber-npc-embed" data-saber-npc-embed><iframe id="%1$s" class="saber-npc-embed__frame" src="%2$s" title="%3$s" loading="eager"></iframe><noscript><p><a href="%4$s">%5$s</a></p></noscript></div>',
		esc_attr( $frame_id ),
		esc_url( $src ),
		esc_attr__( 'SABER College Net Price Calculator', 'saber-net-price-calculator' ),
		esc_url( home_url( '/' . SABER_NPC_ROUTE . '/' ) ),
		esc_html__( 'Open the Net Price Calculator', 'saber-net-price-calculator' )
	);
}
add_shortcode( 'saber_net_price_calculator', 'saber_npc_shortcode' );

/**
 * Creates the rewrite rule immediately when the plugin is activated.
 */
function saber_npc_activate() {
	saber_npc_register_route();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'saber_npc_activate' );

/**
 * Removes the rewrite rule from WordPress when the plugin is deactivated.
 */
function saber_npc_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'saber_npc_deactivate' );
