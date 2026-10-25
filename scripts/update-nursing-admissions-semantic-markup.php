<?php
/**
 * Improve semantic structure, internal links, and selective emphasis on
 * Elementor page 877 while preserving every visible word.
 *
 * Run with:
 *   wp eval-file /path/to/update-nursing-admissions-semantic-markup.php --allow-root
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

$page_id = 877;
$raw     = get_post_meta( $page_id, '_elementor_data', true );
$data    = json_decode( $raw, true );

if ( ! is_array( $data ) ) {
	WP_CLI::error( 'Could not decode Elementor data for page 877.' );
}

function saber_na_atomic_tag( $tag ) {
	return array(
		'$$type' => 'string',
		'value'  => $tag,
	);
}

function saber_na_replace_once( $search, $replace, $subject, $widget_id ) {
	if ( false !== strpos( $subject, $replace ) ) {
		return $subject;
	}
	if ( false === strpos( $subject, $search ) ) {
		WP_CLI::error( "Expected text was not found in widget {$widget_id}: {$search}" );
	}
	return preg_replace( '/' . preg_quote( $search, '/' ) . '/', str_replace( '$', '\\$', $replace ), $subject, 1 );
}

function saber_na_update_nodes( &$nodes, &$changed_ids ) {
	$section_ids = array( '0d674c3', 'f6197d8', 'a5bfb6f', '65634ff' );
	$article_ids = array( '2393047', '5c73f6c', '4577f08' );

	$strong_phrases = array(
		'06ff86b' => array(
			'make the best decision for your future',
			'resources and support needed to succeed in your educational endeavors',
		),
		'2681e7b' => array(
			'High School Diploma',
			'18 years of age',
			'Kaplan (65)',
			'72 hrs. after the first exam and 30 days for the third exam',
			'minimum GPA of 2.0 (C)',
			'minimum GPA of 3.0 in a 4.0 scale',
			'personal oral and written interview',
			'parent or guardian approval',
		),
		'85abbc6' => array(
			'choose a payment plan, obtain loans and grants to pay for school',
			'different types of available financial aid',
		),
		'caa7f81' => array(
			'safe and healthy campus environment',
			'confidential, professional counseling services',
		),
		'cb237b4' => array(
			'without additional charge',
			'No placement guarantee shall be implied',
			'help students find positions in their field of study',
		),
	);

	$visual_b_phrases = array(
		'2681e7b' => array( 'Student Orientation' ),
		'85abbc6' => array( 'Financial aid advisors' ),
	);

	$inline_links = array(
		'06ff86b' => array(
			array( 'financial aid advisement', '<a href="/general/tuition-payment-options/">financial aid advisement</a>' ),
			array( 'placement services', '<a href="/student-services/career-services/">placement services</a>' ),
		),
		'2681e7b' => array(
			array( 'academic advisement', '<a href="/student-services/academic-affairs/"><b>academic advisement</b></a>' ),
		),
		'85abbc6' => array(
			array(
				'Free Application for Federal Student Aid (FAFSA)',
				'<a href="https://studentaid.gov/h/apply-for-aid/fafsa"><strong>Free Application for Federal Student Aid (FAFSA)</strong></a>'
			),
		),
		'cb237b4' => array(
			array( 'career center', '<a href="/student-services/career-services/">career center</a>' ),
		),
	);

	foreach ( $nodes as &$node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}

		$id = $node['id'] ?? '';
		if ( ! isset( $node['settings'] ) || ! is_array( $node['settings'] ) ) {
			$node['settings'] = array();
		}
		$settings =& $node['settings'];

		if ( in_array( $id, $section_ids, true ) ) {
			$settings['tag'] = saber_na_atomic_tag( 'section' );
			$changed_ids[ $id ] = true;
		}

		if ( in_array( $id, $article_ids, true ) ) {
			$settings['tag'] = saber_na_atomic_tag( 'article' );
			$changed_ids[ $id ] = true;
		}

		if ( '89b8e2f' === $id ) {
			$settings['header_size'] = 'h2';
			$changed_ids[ $id ] = true;
		}

		if ( isset( $strong_phrases[ $id ] ) ) {
			foreach ( $strong_phrases[ $id ] as $phrase ) {
				$settings['editor'] = saber_na_replace_once(
					$phrase,
					'<strong>' . $phrase . '</strong>',
					$settings['editor'],
					$id
				);
			}
			$changed_ids[ $id ] = true;
		}

		if ( isset( $visual_b_phrases[ $id ] ) ) {
			foreach ( $visual_b_phrases[ $id ] as $phrase ) {
				$settings['editor'] = saber_na_replace_once(
					$phrase,
					'<b>' . $phrase . '</b>',
					$settings['editor'],
					$id
				);
			}
			$changed_ids[ $id ] = true;
		}

		if ( isset( $inline_links[ $id ] ) ) {
			foreach ( $inline_links[ $id ] as $link_data ) {
				list( $phrase, $replacement ) = $link_data;
				$settings['editor'] = saber_na_replace_once( $phrase, $replacement, $settings['editor'], $id );
			}
			$changed_ids[ $id ] = true;
		}

		if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
			saber_na_update_nodes( $node['elements'], $changed_ids );
		}
	}
	unset( $node );
}

$changed_ids = array();
saber_na_update_nodes( $data, $changed_ids );

$required_ids = array(
	'0d674c3', 'f6197d8', 'a5bfb6f', '65634ff',
	'2393047', '5c73f6c', '4577f08', '89b8e2f',
	'06ff86b', '2681e7b', '85abbc6', 'caa7f81', 'cb237b4',
);
$missing = array_diff( $required_ids, array_keys( $changed_ids ) );
if ( $missing ) {
	WP_CLI::error( 'Required Elementor IDs were not updated: ' . implode( ', ', $missing ) );
}

$json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
if ( false === update_post_meta( $page_id, '_elementor_data', wp_slash( $json ) ) ) {
	$current = get_post_meta( $page_id, '_elementor_data', true );
	if ( json_decode( $current, true ) !== $data ) {
		WP_CLI::error( 'Elementor data was not saved for page 877.' );
	}
}

WP_CLI::success( 'Updated page 877: ' . implode( ', ', array_keys( $changed_ids ) ) );
