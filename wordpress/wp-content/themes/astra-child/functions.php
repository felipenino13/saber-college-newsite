<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );
	wp_enqueue_style( 'saber-landing-pages', get_stylesheet_directory_uri() . '/landing-pages.css', array( 'astra-child-theme-css' ), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/** Canvas templates bypass Astra's normal style queue, including child styles. */
function saber_enqueue_landing_styles() {
	if ( is_page( array( 1640, 1641, 1643, 1649 ) ) ) {
		wp_enqueue_style( 'saber-landing-pages', get_stylesheet_directory_uri() . '/landing-pages.css', array(), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );
	}
}

add_action( 'elementor/frontend/after_enqueue_styles', 'saber_enqueue_landing_styles' );

