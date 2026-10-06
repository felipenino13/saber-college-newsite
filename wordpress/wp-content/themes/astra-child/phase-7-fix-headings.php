<?php
/**
 * One-time Elementor heading migration for Phase 7.
 */

defined( 'ABSPATH' ) || exit;

function saber_update_elementor_widget( array &$elements, $widget_id, callable $callback ) {
	foreach ( $elements as &$element ) {
		if ( ( $element['id'] ?? '' ) === $widget_id ) {
			$callback( $element );
			return true;
		}
		if ( ! empty( $element['elements'] ) && saber_update_elementor_widget( $element['elements'], $widget_id, $callback ) ) {
			return true;
		}
	}
	return false;
}

function saber_save_elementor_data( $post_id, $widget_id, callable $callback ) {
	$data = json_decode( get_post_meta( $post_id, '_elementor_data', true ), true );
	if ( ! is_array( $data ) || ! saber_update_elementor_widget( $data, $widget_id, $callback ) ) {
		WP_CLI::error( "Widget {$widget_id} was not found in post {$post_id}." );
	}
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
	WP_CLI::line( "Updated post {$post_id}, widget {$widget_id}." );
}

saber_save_elementor_data(
	312,
	'9b093ba',
	function ( &$widget ) {
		$widget['settings']['header_size'] = 'h3';
	}
);

saber_save_elementor_data(
	30,
	'fd7df63',
	function ( &$widget ) {
		$widget['settings']['header_size'] = 'h1';
	}
);

saber_save_elementor_data(
	1639,
	'b2f052a',
	function ( &$widget ) {
		$widget['settings']['title']       = 'Call or Email to Arrange A Tour of Our Facilities';
		$widget['settings']['header_size'] = 'h1';
	}
);

WP_CLI::success( 'Elementor heading fixes applied.' );
