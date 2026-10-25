<?php
/**
 * Fix the Lighthouse accessibility and paste audit findings on the home page.
 *
 * The changes remain in native Elementor widgets/templates:
 * - page 7: remove paste-blocking behavior while retaining submit validation;
 * - header 16: turn the phone separator into plain text;
 * - footer 53: add accessible labels to the social icon links;
 * - attachment 21: provide the site logo alt text used by the Theme Site Logo widget.
 *
 * Run with:
 *   wp eval-file /tmp/fix-home-lighthouse-accessibility.php --allow-root
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

function saber_lighthouse_load_elementor_data( $post_id ) {
	$raw  = get_post_meta( $post_id, '_elementor_data', true );
	$data = json_decode( $raw, true );

	if ( ! is_array( $data ) ) {
		WP_CLI::error( "Could not decode Elementor data for post {$post_id}." );
	}

	return $data;
}

function saber_lighthouse_save_elementor_data( $post_id, $data ) {
	$encoded = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( ! $encoded ) {
		WP_CLI::error( "Could not encode Elementor data for post {$post_id}." );
	}

	update_post_meta( $post_id, '_elementor_data', wp_slash( $encoded ) );
	clean_post_cache( $post_id );
}

function saber_lighthouse_update_nodes( &$nodes, $post_id, &$found, &$changed ) {
	foreach ( $nodes as &$node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}

		$id = isset( $node['id'] ) ? $node['id'] : '';
		if ( ! isset( $node['settings'] ) || ! is_array( $node['settings'] ) ) {
			$node['settings'] = array();
		}
		$settings =& $node['settings'];

		if ( 7 === $post_id && '7947857' === $id ) {
			$found[ $id ] = true;
			$html         = isset( $settings['html'] ) ? $settings['html'] : '';
			$marker       = 'Paste remains enabled; US phone validation runs during input and submit.';
			$widget_changed = false;

			if ( false === strpos( $html, $marker ) ) {
				$pattern = '~\R\s*/\*\s*\R\s*\* Bloquea pegado de indicativos distintos a \+1\s*\R\s*\*/\s*\R\s*input\.addEventListener\("paste", function \(event\) \{.*?\R\s*\}\);\s*\R~su';
				$html    = preg_replace(
					$pattern,
					"\r\n  /* Paste remains enabled; US phone validation runs during input and submit. */\r\n",
					$html,
					1,
					$count
				);

				if ( 1 !== $count ) {
					WP_CLI::error( 'Could not locate the phone paste-blocking listener in widget 7947857.' );
				}

				$widget_changed = true;
			}

			$html = preg_replace(
				'~\R\s*strictMode:\s*true,\s*~u',
				"\r\n",
				$html,
				1,
				$strict_mode_count
			);
			if ( 1 === $strict_mode_count ) {
				$widget_changed = true;
			}

			if ( $widget_changed ) {
				$settings['html'] = $html;
				$changed[ $id ]   = true;
			}
		}

		if ( 16 === $post_id && '54fc4e9' === $id ) {
			$found[ $id ] = true;
			$current = $settings['paragraph']['value']['content']['value'] ?? '';
			if ( '| ' !== $current ) {
				$settings['paragraph']['value']['content']['value'] = '| ';
				$changed[ $id ] = true;
			}
		}

		$social_labels = array(
			'b15c9ae' => 'Facebook',
			'e46950f' => 'Instagram',
			'6bc2b1d' => 'TikTok',
		);
		if ( 53 === $post_id && isset( $social_labels[ $id ] ) ) {
			$found[ $id ] = true;
			$expected     = 'aria-label|' . $social_labels[ $id ];
			$current      = $settings['link']['custom_attributes'] ?? '';
			if ( $expected !== $current ) {
				$settings['link']['custom_attributes'] = $expected;
				$changed[ $id ] = true;
			}
		}

		if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
			saber_lighthouse_update_nodes( $node['elements'], $post_id, $found, $changed );
		}
	}
	unset( $node );
}

$targets = array(
	7  => array( '7947857' ),
	16 => array( '54fc4e9' ),
	53 => array( 'b15c9ae', 'e46950f', '6bc2b1d' ),
);

$all_changed = array();
foreach ( $targets as $post_id => $required_ids ) {
	$data    = saber_lighthouse_load_elementor_data( $post_id );
	$found   = array();
	$changed = array();

	saber_lighthouse_update_nodes( $data, $post_id, $found, $changed );

	$missing = array_diff( $required_ids, array_keys( $found ) );
	if ( $missing ) {
		WP_CLI::error( "Expected Elementor nodes were not found in post {$post_id}: " . implode( ', ', $missing ) );
	}

	if ( $changed ) {
		saber_lighthouse_save_elementor_data( $post_id, $data );
	}
	$all_changed[ $post_id ] = array_keys( $changed );
}

$logo_id  = 21;
$logo_alt = 'SABER College home';
if ( $logo_alt !== get_post_meta( $logo_id, '_wp_attachment_image_alt', true ) ) {
	update_post_meta( $logo_id, '_wp_attachment_image_alt', $logo_alt );
	$all_changed['attachment_21'] = array( '_wp_attachment_image_alt' );
} else {
	$all_changed['attachment_21'] = array();
}

WP_CLI::success( 'Elementor accessibility fixes applied: ' . wp_json_encode( $all_changed ) );
