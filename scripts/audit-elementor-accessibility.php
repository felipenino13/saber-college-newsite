<?php
/**
 * Inventory Elementor forms and interactive widgets for accessibility QA.
 * Run with: wp eval-file /tmp/audit-elementor-accessibility.php --allow-root
 */

defined( 'ABSPATH' ) || exit;

$interactive_types = array(
	'form',
	'accordion',
	'nested-accordion',
	'tabs',
	'nested-tabs',
	'image-carousel',
	'media-carousel',
	'testimonial-carousel',
	'loop-carousel',
	'menu-anchor',
	'nav-menu',
	'nested-carousel',
	'video',
	'popup',
);

function saber_accessibility_walk_elementor( $nodes, $post, $interactive_types, &$output ) {
	foreach ( $nodes as $node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}

		$type = isset( $node['widgetType'] ) ? $node['widgetType'] : '';
		if ( $type && in_array( $type, $interactive_types, true ) ) {
			$item = array(
				'post_id'     => $post->ID,
				'post_type'   => $post->post_type,
				'post_title'  => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
				'permalink'   => get_permalink( $post ),
				'widget_id'   => isset( $node['id'] ) ? $node['id'] : '',
				'widget_type' => $type,
			);

			if ( 'form' === $type ) {
				$settings          = isset( $node['settings'] ) && is_array( $node['settings'] ) ? $node['settings'] : array();
				$item['form_name'] = isset( $settings['form_name'] ) ? $settings['form_name'] : '';
				$item['fields']    = array();
				foreach ( isset( $settings['form_fields'] ) && is_array( $settings['form_fields'] ) ? $settings['form_fields'] : array() as $field ) {
					$item['fields'][] = array(
						'id'          => isset( $field['custom_id'] ) ? $field['custom_id'] : '',
						'label'       => isset( $field['field_label'] ) ? $field['field_label'] : '',
						'type'        => isset( $field['field_type'] ) ? $field['field_type'] : '',
						'required'    => isset( $field['required'] ) && 'true' === $field['required'],
						'placeholder' => isset( $field['placeholder'] ) ? $field['placeholder'] : '',
					);
				}
			}

			$output[] = $item;
		}

		if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
			saber_accessibility_walk_elementor( $node['elements'], $post, $interactive_types, $output );
		}
	}
}

$posts = get_posts(
	array(
		'post_type'      => array( 'page', 'elementor_library', 'e-floating-buttons' ),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
	)
);

$widgets = array();
foreach ( $posts as $post ) {
	$json = get_post_meta( $post->ID, '_elementor_data', true );
	if ( ! $json ) {
		continue;
	}
	$data = json_decode( $json, true );
	if ( is_array( $data ) ) {
		saber_accessibility_walk_elementor( $data, $post, $interactive_types, $widgets );
	}
}

$result = array(
	'generated_at'        => gmdate( 'c' ),
	'interactive_widgets' => $widgets,
	'counts'              => array_count_values( array_column( $widgets, 'widget_type' ) ),
);

echo wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
