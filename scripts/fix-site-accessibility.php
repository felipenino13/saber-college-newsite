<?php
/**
 * Apply small, Elementor-native accessibility corrections found in the
 * 2026-10-21 audit. The script is idempotent and preserves widget structure.
 *
 * Run with:
 * wp eval-file /tmp/fix-site-accessibility.php --allow-root
 */

defined( 'ABSPATH' ) || exit;

function saber_a11y_walk_nodes( &$nodes, $callback ) {
	foreach ( $nodes as &$node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}

		$callback( $node );

		if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
			saber_a11y_walk_nodes( $node['elements'], $callback );
		}
	}
	unset( $node );
}

function saber_a11y_replace_color( &$value, $from, $to, &$count ) {
	if ( is_string( $value ) ) {
		$value = str_ireplace( $from, $to, $value, $replacements );
		$count += $replacements;
		return;
	}

	if ( is_array( $value ) ) {
		foreach ( $value as &$child ) {
			saber_a11y_replace_color( $child, $from, $to, $count );
		}
		unset( $child );
	}
}

$tasks = array(
	30   => 'program headings',
	312  => 'blog card heading',
	904  => 'survey form contrast',
	1225 => 'admissions link contrast',
	1238 => 'campus map title',
	1638 => 'legacy landing form and contrast',
	1639 => 'campus landing semantics and contrast',
	2066 => 'design system form contrast',
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

	if ( 30 === $post_id ) {
		$target_ids = array( '63f56fc', 'a36e1e4' );
		saber_a11y_walk_nodes(
			$data,
			function ( &$node ) use ( $target_ids, &$changes ) {
				if ( in_array( isset( $node['id'] ) ? $node['id'] : '', $target_ids, true ) && 'h2' !== ( isset( $node['settings']['header_size'] ) ? $node['settings']['header_size'] : '' ) ) {
					$node['settings']['header_size'] = 'h2';
					++$changes;
				}
			}
		);
	}

	if ( 312 === $post_id ) {
		saber_a11y_walk_nodes(
			$data,
			function ( &$node ) use ( &$changes ) {
				if ( '9b093ba' === ( isset( $node['id'] ) ? $node['id'] : '' ) && 'h2' !== ( isset( $node['settings']['header_size'] ) ? $node['settings']['header_size'] : '' ) ) {
					$node['settings']['header_size'] = 'h2';
					++$changes;
				}
			}
		);
	}

	if ( 1225 === $post_id ) {
		saber_a11y_replace_color( $data, '#0070E0', '#003F7D', $changes );
	}

	if ( 1238 === $post_id ) {
		saber_a11y_walk_nodes(
			$data,
			function ( &$node ) use ( &$changes ) {
				if ( '58ff18d' !== ( isset( $node['id'] ) ? $node['id'] : '' ) || empty( $node['settings']['html'] ) ) {
					return;
				}

				$html = $node['settings']['html'];
				if ( false === stripos( $html, ' title=' ) ) {
					$node['settings']['html'] = preg_replace(
						'/<iframe\b/i',
						'<iframe title="SABER College campus location map"',
						$html,
						1,
						$replacements
					);
					$changes += $replacements;
				}
			}
		);
	}

	if ( 904 === $post_id ) {
		saber_a11y_walk_nodes(
			$data,
			function ( &$node ) use ( &$changes ) {
				if ( '4df5919' === ( isset( $node['id'] ) ? $node['id'] : '' ) && '#595959' !== ( isset( $node['settings']['field_text_color'] ) ? $node['settings']['field_text_color'] : '' ) ) {
					$node['settings']['field_text_color'] = '#595959';
					if ( isset( $node['settings']['__globals']['field_text_color'] ) ) {
						$node['settings']['__globals']['field_text_color'] = '';
					}
					++$changes;
				}
			}
		);
	}

	if ( 1638 === $post_id ) {
		$text_ids   = array( '1ab1005', '1ab1008', '1ab1015', '1ab1042', '1ab1035' );
		$button_ids = array( '1ab1016', '1ab1043', '1ab1036' );
		saber_a11y_walk_nodes(
			$data,
			function ( &$node ) use ( $text_ids, $button_ids, &$changes ) {
				$id = isset( $node['id'] ) ? $node['id'] : '';
				if ( in_array( $id, $text_ids, true ) && '#595959' !== ( isset( $node['settings']['text_color'] ) ? $node['settings']['text_color'] : '' ) ) {
					$node['settings']['text_color'] = '#595959';
					++$changes;
				}
				if ( in_array( $id, $button_ids, true ) && '#001A39' !== ( isset( $node['settings']['button_text_color'] ) ? $node['settings']['button_text_color'] : '' ) ) {
					$node['settings']['button_text_color'] = '#001A39';
					++$changes;
				}
				if ( '1ab1020' === $id && ! empty( $node['settings']['form_fields'] ) ) {
					foreach ( $node['settings']['form_fields'] as &$field ) {
						if ( 'b04a69a' === ( isset( $field['_id'] ) ? $field['_id'] : '' ) && 'last_name' !== ( isset( $field['custom_id'] ) ? $field['custom_id'] : '' ) ) {
							$field['custom_id'] = 'last_name';
							++$changes;
						}
					}
					unset( $field );
				}
			}
		);
	}

	if ( 1639 === $post_id ) {
		$text_ids = array( 'bbdb06d', 'f68a661', 'df935c6', '4d3d5c1', '29b8500', 'cb28192', '9d6fc8f', 'c895f31', '00781ca', '1e9d2b3', '0bd4da2' );
		saber_a11y_walk_nodes(
			$data,
			function ( &$node ) use ( $text_ids, &$changes ) {
				$id = isset( $node['id'] ) ? $node['id'] : '';
				if ( in_array( $id, $text_ids, true ) && '#595959' !== ( isset( $node['settings']['text_color'] ) ? $node['settings']['text_color'] : '' ) ) {
					$node['settings']['text_color'] = '#595959';
					++$changes;
				}
				if ( '88e2230' === $id && 'h2' !== ( isset( $node['settings']['header_size'] ) ? $node['settings']['header_size'] : '' ) ) {
					$node['settings']['header_size'] = 'h2';
					++$changes;
				}
				if ( '3d1ed48' === $id && ! empty( $node['settings']['form_fields'] ) ) {
					foreach ( $node['settings']['form_fields'] as &$field ) {
						if ( 'b04a69a' === ( isset( $field['_id'] ) ? $field['_id'] : '' ) && 'last_name' !== ( isset( $field['custom_id'] ) ? $field['custom_id'] : '' ) ) {
							$field['custom_id'] = 'last_name';
							++$changes;
						}
					}
					unset( $field );
				}
			}
		);
	}

	if ( 2066 === $post_id ) {
		saber_a11y_walk_nodes(
			$data,
			function ( &$node ) use ( &$changes ) {
				if ( '27e9a1e' === ( isset( $node['id'] ) ? $node['id'] : '' ) && '#595959' !== ( isset( $node['settings']['label_color'] ) ? $node['settings']['label_color'] : '' ) ) {
					$node['settings']['label_color'] = '#595959';
					++$changes;
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

