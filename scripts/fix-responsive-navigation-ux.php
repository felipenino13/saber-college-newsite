<?php
/**
 * Apply Elementor-native navigation and link corrections from the 2026-10-21
 * responsive/UX audit. The script is idempotent and preserves widget structure.
 *
 * Run with:
 * wp eval-file /tmp/fix-responsive-navigation-ux.php --allow-root
 */

defined( 'ABSPATH' ) || exit;

function saber_ux_walk_nodes( &$nodes, $callback ) {
	foreach ( $nodes as &$node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}

		$callback( $node );

		if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
			saber_ux_walk_nodes( $node['elements'], $callback );
		}
	}
	unset( $node );
}

function saber_ux_replace_string( &$value, $from, $to, &$count ) {
	if ( is_string( $value ) ) {
		$value = str_replace( $from, $to, $value, $replacements );
		$count += $replacements;
		return;
	}

	if ( is_array( $value ) ) {
		foreach ( $value as &$child ) {
			saber_ux_replace_string( $child, $from, $to, $count );
		}
		unset( $child );
	}
}

$tasks = array(
	25   => 'Remove a dead external destination while preserving its visible text',
	34   => 'Update the Federal Student Aid feedback destination',
	898  => 'Update the SEVP school search destination',
	902  => 'Name the transcript PDF icon link',
	911  => 'Update the FAFSA destination',
	1638 => 'Add the missing request-information anchor target',
);

$result = array(
	'changed_posts' => array(),
	'changes'       => array(),
	'errors'        => array(),
);

foreach ( $tasks as $post_id => $description ) {
	$json = get_post_meta( $post_id, '_elementor_data', true );
	$data = json_decode( $json, true );

	if ( ! is_array( $data ) ) {
		$result['errors'][] = "Post {$post_id}: Elementor data could not be decoded.";
		continue;
	}

	$changes = 0;

	if ( 25 === $post_id ) {
		saber_ux_walk_nodes(
			$data,
			function ( &$node ) use ( &$changes ) {
				if ( '7e8743b' !== ( isset( $node['id'] ) ? $node['id'] : '' ) || empty( $node['settings']['title'] ) ) {
					return;
				}

				$node['settings']['title'] = preg_replace(
					'/<a\s+href="https:\/\/dltrainingsolutions\.com"[^>]*>(.*?)<\/a>/i',
					'$1',
					$node['settings']['title'],
					-1,
					$replacements
				);
				$changes += $replacements;
			}
		);
	}

	if ( 34 === $post_id ) {
		saber_ux_replace_string(
			$data,
			'http://studentaid.ed.gov/sa/repay-loans/disputes/prepare/contact-ombudsman',
			'https://studentaid.gov/submit-feedback/',
			$changes
		);
	}

	if ( 898 === $post_id ) {
		saber_ux_replace_string(
			$data,
			'https://studyinthestates.dhs.gov/certified-school/9690',
			'https://studyinthestates.dhs.gov/school-search?field_school_name_value=SABER%20College',
			$changes
		);
	}

	if ( 902 === $post_id ) {
		saber_ux_walk_nodes(
			$data,
			function ( &$node ) use ( &$changes ) {
				if ( 'd549956' !== ( isset( $node['id'] ) ? $node['id'] : '' ) ) {
					return;
				}

				if ( empty( $node['settings']['link'] ) || ! is_array( $node['settings']['link'] ) ) {
					$node['settings']['link'] = array();
				}

				$attribute = 'aria-label|Download the SABER College transcript request form (PDF)';
				if ( $attribute !== ( isset( $node['settings']['link']['custom_attributes'] ) ? $node['settings']['link']['custom_attributes'] : '' ) ) {
					$node['settings']['link']['custom_attributes'] = $attribute;
					++$changes;
				}
			}
		);
	}

	if ( 911 === $post_id ) {
		saber_ux_replace_string(
			$data,
			'http://www.fafsa.ed.gov/',
			'https://studentaid.gov/h/apply-for-aid/fafsa',
			$changes
		);
	}

	if ( 1638 === $post_id ) {
		saber_ux_walk_nodes(
			$data,
			function ( &$node ) use ( &$changes ) {
				if ( '1ab1020' === ( isset( $node['id'] ) ? $node['id'] : '' ) ) {
					if ( 'request-info' !== ( isset( $node['settings']['_element_id'] ) ? $node['settings']['_element_id'] : '' ) ) {
						$node['settings']['_element_id'] = 'request-info';
						++$changes;
					}
					if ( isset( $node['settings']['_css_id'] ) ) {
						unset( $node['settings']['_css_id'] );
						++$changes;
					}
				}
			}
		);
	}

	if ( $changes > 0 ) {
		$encoded = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		update_post_meta( $post_id, '_elementor_data', wp_slash( $encoded ) );
		$result['changed_posts'][] = $post_id;
	}

	$result['changes'][] = array(
		'post_id'     => $post_id,
		'description' => $description,
		'count'       => $changes,
	);
}

if ( ! empty( $result['changed_posts'] ) && did_action( 'elementor/loaded' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}

echo wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
