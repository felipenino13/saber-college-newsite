<?php
/**
 * Improve the semantic markup and link structure of Elementor page 7.
 *
 * Run with:
 *   wp eval-file /path/to/update-home-semantic-markup.php --allow-root
 *
 * The script only updates Elementor widget/container settings. It does not
 * replace the page with static HTML, so the full page remains editable in
 * Elementor.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

$page_id = 7;
$raw     = get_post_meta( $page_id, '_elementor_data', true );
$data    = json_decode( $raw, true );

if ( ! is_array( $data ) ) {
	WP_CLI::error( 'Could not decode Elementor data for page 7.' );
}

$changed_ids = array();

function saber_atomic_tag( $tag ) {
	return array(
		'$$type' => 'string',
		'value'  => $tag,
	);
}

function saber_atomic_query_link( $post_id, $label ) {
	return array(
		'$$type' => 'link',
		'value'  => array(
			'destination'   => array(
				'$$type' => 'query',
				'value'  => array(
					'id'    => array(
						'$$type' => 'number',
						'value'  => $post_id,
					),
					'label' => array(
						'$$type' => 'string',
						'value'  => $label,
					),
				),
			),
			'isTargetBlank' => null,
		),
	);
}

function saber_replace_once( $search, $replace, $subject, $widget_id ) {
	if ( false !== strpos( $subject, $replace ) ) {
		return $subject;
	}

	if ( false === strpos( $subject, $search ) ) {
		WP_CLI::error( "Expected text was not found in widget {$widget_id}." );
	}

	return preg_replace( '/' . preg_quote( $search, '/' ) . '/', str_replace( '$', '\\$', $replace ), $subject, 1 );
}

function saber_update_home_nodes( &$nodes, &$changed_ids ) {
	$section_tags = array(
		'b477088' => 'aside',
		'6cc418c' => 'section',
		'657ca59' => 'section',
		'3c4b306' => 'section',
		'a48f67b' => 'section',
		'd0e11ec' => 'section',
		'bcbe8de' => 'section',
		'eb0dc33' => 'section',
		'f55cb58' => 'section',
		'd2ec402' => 'section',
		'322b5cc' => 'section',
		'95eeaef' => 'section',
	);

	foreach ( $nodes as &$node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}

		$id = isset( $node['id'] ) ? $node['id'] : '';
		if ( ! isset( $node['settings'] ) || ! is_array( $node['settings'] ) ) {
			$node['settings'] = array();
		}
		$settings =& $node['settings'];

		if ( isset( $section_tags[ $id ] ) ) {
			$settings['tag'] = saber_atomic_tag( $section_tags[ $id ] );
			$changed_ids[ $id ] = true;
		}

		switch ( $id ) {
			case 'ad7bb12':
			case '8b7e229':
				$settings['tag'] = saber_atomic_tag( 'article' );
				unset( $settings['link'] );
				$changed_ids[ $id ] = true;
				break;

			case '35f8898':
				$settings['link'] = array(
					'url'         => '/saber_college_programs/professional-nursing-program/',
					'is_external' => '',
					'nofollow'    => '',
				);
				$changed_ids[ $id ] = true;
				break;

			case 'eb66d31':
				$settings['link'] = array(
					'url'         => '/saber_college_programs/physical-therapist-assistant-program/',
					'is_external' => '',
					'nofollow'    => '',
				);
				$changed_ids[ $id ] = true;
				break;

			case 'dd3e903':
				$settings['header_size'] = 'h2';
				$changed_ids[ $id ] = true;
				break;

			case 'c4b3daa':
				$settings['header_size'] = 'p';
				$changed_ids[ $id ] = true;
				break;

			case '5dcc3c6':
				$settings['title'] = str_replace( array( '<strong>', '</strong>' ), array( '<b>', '</b>' ), $settings['title'] );
				$changed_ids[ $id ] = true;
				break;

			case 'e9e7681':
			case '5da9e8d':
				$settings['title'] = str_replace( array( '<strong>', '</strong>' ), array( '<b>', '</b>' ), $settings['title'] );
				$changed_ids[ $id ] = true;
				break;

			case 'e660525':
				$settings['title'] = saber_replace_once(
					'Our career services',
					'Our <a href="/student-services/career-services/" style="color:#0070E0;text-decoration:underline">career services</a>',
					$settings['title'],
					$id
				);
				$changed_ids[ $id ] = true;
				break;

			case '6756b4a':
				$settings['title'] = saber_replace_once(
					'academic advising',
					'<a href="/student-services/academic-affairs/" style="color:#0070E0;text-decoration:underline">academic advising</a>',
					$settings['title'],
					$id
				);
				$changed_ids[ $id ] = true;
				break;

			case '96ae2b1':
				$settings['title'] = saber_replace_once(
					'student life',
					'<a href="/general/explore-saber-college-gallery/" style="color:#0070E0;text-decoration:underline">student life</a>',
					$settings['title'],
					$id
				);
				$changed_ids[ $id ] = true;
				break;

			case 'd2e328a':
				$settings['title'] = saber_replace_once(
					'U.S. Bureau of Labor Statistics',
					'<a href="https://www.bls.gov/ooh/healthcare/registered-nurses.htm" style="color:#0070E0;text-decoration:underline">U.S. Bureau of Labor Statistics</a>',
					$settings['title'],
					$id
				);
				$changed_ids[ $id ] = true;
				break;

			case '94168d3':
				$settings['title'] = saber_replace_once(
					'U.S. Bureau of Labor Statistics Employment Projections',
					'<a href="https://www.bls.gov/ooh/healthcare/physical-therapist-assistants-and-aides.htm" style="color:#0070E0;text-decoration:underline">U.S. Bureau of Labor Statistics Employment Projections</a>',
					$settings['title'],
					$id
				);
				$changed_ids[ $id ] = true;
				break;

			case '148676c':
				$settings['title'] = saber_replace_once( 'href="#"', 'href="/admissions-and-tuition/"', $settings['title'], $id );
				$changed_ids[ $id ] = true;
				break;

			case '34944c0':
				$settings['title'] = saber_replace_once( 'href="#"', 'href="/saber_college_programs/physical-therapist-assistant-program/"', $settings['title'], $id );
				$settings['title'] = saber_replace_once(
					'Commission on Accreditation in Physical Therapy Education (CAPTE)',
					'<a href="https://www.capteonline.org/" style="color:#0070E0;text-decoration:underline">Commission on Accreditation in Physical Therapy Education (CAPTE)</a>',
					$settings['title'],
					$id
				);
				$changed_ids[ $id ] = true;
				break;

			case '7d74e5a':
				$settings['title'] = saber_replace_once( 'href="#"', 'href="/saber_college_programs/physical-therapist-assistant-program/"', $settings['title'], $id );
				$changed_ids[ $id ] = true;
				break;

			case '970d9df':
				$settings['title'] = saber_replace_once( 'href="#"', 'href="/saber_college_programs/physical-therapist-assistant-program/"', $settings['title'], $id );
				$settings['title'] = saber_replace_once( 'href="#"', 'href="https://www.onetonline.org/link/details/31-2021.00"', $settings['title'], $id );
				$changed_ids[ $id ] = true;
				break;

			case '0ad5596':
				$settings['link'] = saber_atomic_query_link( 1192, 'Blog' );
				$changed_ids[ $id ] = true;
				break;

			case '1831d5e':
				$settings['link'] = saber_atomic_query_link( 883, 'Apply To SABER College' );
				$changed_ids[ $id ] = true;
				break;

			case '6640b9e':
				$settings['form_name'] = 'Request Information';
				foreach ( $settings['form_fields'] as &$field ) {
					if ( 'First Name' === ( $field['field_label'] ?? '' ) ) {
						$field['custom_id'] = 'first_name';
					}
					if ( 'Last Name' === ( $field['field_label'] ?? '' ) ) {
						$field['custom_id'] = 'last_name';
					}
				}
				unset( $field );
				$changed_ids[ $id ] = true;
				break;
		}

		if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
			saber_update_home_nodes( $node['elements'], $changed_ids );
		}
	}
	unset( $node );
}

saber_update_home_nodes( $data, $changed_ids );

$required_ids = array(
	'b477088', '6cc418c', '657ca59', '3c4b306', 'a48f67b', 'd0e11ec',
	'bcbe8de', 'eb0dc33', 'f55cb58', 'd2ec402', '322b5cc', '95eeaef',
	'ad7bb12', '8b7e229', '35f8898', 'eb66d31', 'dd3e903', 'c4b3daa',
	'5dcc3c6', 'e9e7681', '5da9e8d', 'e660525', '6756b4a', '96ae2b1',
	'd2e328a', '94168d3', '148676c', '34944c0', '7d74e5a', '970d9df',
	'0ad5596', '1831d5e', '6640b9e',
);

$missing_ids = array_diff( $required_ids, array_keys( $changed_ids ) );
if ( $missing_ids ) {
	WP_CLI::error( 'Expected Elementor nodes were not found: ' . implode( ', ', $missing_ids ) );
}

$encoded = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
if ( ! $encoded ) {
	WP_CLI::error( 'Could not encode updated Elementor data.' );
}

update_post_meta( $page_id, '_elementor_data', wp_slash( $encoded ) );
clean_post_cache( $page_id );

WP_CLI::success( 'Updated page 7 using native Elementor settings. Changed nodes: ' . count( $changed_ids ) );
