<?php
/**
 * Plugin Name: SABER College Rank Math Integration
 * Description: Preserves Rank Math WebPage schema on indexable pages.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Keep global WebPage entities on regular pages without exposing noindex pages.
 *
 * @param bool $can_add Whether Rank Math should add its global schema entities.
 * @return bool
 */
function saber_rank_math_page_schema_entities( $can_add ) {
	if ( ! is_page() ) {
		return $can_add;
	}

	$robots = get_post_meta( get_queried_object_id(), 'rank_math_robots', true );
	if ( is_array( $robots ) && in_array( 'noindex', $robots, true ) ) {
		return false;
	}

	return true;
}

add_filter( 'rank_math/schema/add_global_entities', 'saber_rank_math_page_schema_entities' );
