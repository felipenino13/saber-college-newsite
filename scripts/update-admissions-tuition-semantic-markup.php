<?php
/**
 * Improve the semantic structure, links, and selective text emphasis of
 * Elementor page 1225 while preserving all visible wording and editability.
 *
 * Run with:
 *   wp eval-file /path/to/update-admissions-tuition-semantic-markup.php --allow-root
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

$page_id = 1225;
$raw     = get_post_meta( $page_id, '_elementor_data', true );
$data    = json_decode( $raw, true );

if ( ! is_array( $data ) ) {
	WP_CLI::error( 'Could not decode Elementor data for page 1225.' );
}

function saber_at_atomic_tag( $tag ) {
	return array(
		'$$type' => 'string',
		'value'  => $tag,
	);
}

function saber_at_link( $url ) {
	return array(
		'url'         => $url,
		'is_external' => '',
		'nofollow'    => '',
	);
}

function saber_at_replace_once( $search, $replace, $subject, $widget_id ) {
	if ( false !== strpos( $subject, $replace ) ) {
		return $subject;
	}
	if ( false === strpos( $subject, $search ) ) {
		WP_CLI::error( "Expected text was not found in widget {$widget_id}: {$search}" );
	}
	return preg_replace( '/' . preg_quote( $search, '/' ) . '/', str_replace( '$', '\\$', $replace ), $subject, 1 );
}

function saber_at_update_nodes( &$nodes, &$changed_ids ) {
	$section_ids = array( 'athero1', 'atpath1', 'atrequs', 'atfinan', 'ataccre', 'atcta01' );
	$article_ids = array( 'atnursg', 'atptapr', 'attuitn', 'atfinaid' );
	$visual_b_ids = array( '6247bbb', '1a3cd74', 'aae96b1', 'ed9505c', 'f124f72', '8346bdb', 'f3da86e' );

	$strong_phrases = array(
		'83aeafb' => array( 'investment in your future', 'clear, personal, and manageable' ),
		'259d71d' => array( 'guide you through each step' ),
		'f86e4de' => array( 'Admissions requirements vary by program' ),
		'bfb8746' => array( 'financial aid and payment options' ),
		'ca6c4c6' => array( 'eligibility, required documentation, entrance requirements' ),
		'9550f88' => array( 'becoming a Registered Nurse' ),
		'463f8b8' => array( 'Prepare for your path' ),
		'8e6879e' => array( 'financial assistance that may be available to those who qualify' ),
		'51e7171' => array( 'help eligible students understand available financial assistance' ),
		'964fd8a' => array( 'Depending on eligibility' ),
		'a6093de' => array( 'educational quality and accountability' ),
		'1a70ff1' => array( 'institutional and program-specific accreditations and approvals' ),
		'b1a655b' => array( 'determine your next step toward enrollment' ),
	);

	$inline_links = array(
		'045ff1c' => array(
			array( 'how to apply', '/apply-to-saber-college/' ),
			array( 'tuition and payment options', '/general/tuition-payment-options/' ),
		),
		'9550f88' => array(
			array( 'Professional Nursing Program', '/saber_college_programs/professional-nursing-program/' ),
		),
		'463f8b8' => array(
			array( 'Physical Therapist Assistant', '/saber_college_programs/physical-therapist-assistant-program/' ),
		),
		'a6093de' => array(
			array( 'Accreditation and regulatory approvals', '/saber-college-accreditation/' ),
		),
	);

	$button_links = array(
		'11c5d50' => '/apply-to-saber-college/',
		'fd6d8ed' => '/request-info/',
		'56b4755' => '/apply-to-saber-college/',
		'd087e0e' => '/saber_college_programs/professional-nursing-program/nursing-admissions/',
		'92c78d7' => '/saber_college_programs/physical-therapist-assistant-program/admissions-pta/',
		'bf9ebd7' => '/general/tuition-payment-options/',
		'e84d16a' => '/general/tuition-payment-options/',
		'69f20b3' => '/saber-college-accreditation/',
		'29a7814' => '/apply-to-saber-college/',
		'218b9a7' => '/request-info/',
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

		if ( '463f8b8' === $id ) {
			$linked_phrase = '<a href="/saber_college_programs/physical-therapist-assistant-program/" style="color:#0070E0;text-decoration:underline">Physical Therapist Assistant</a>';
			$settings['editor'] = str_replace(
				'<strong>becoming a ' . $linked_phrase . '</strong>',
				'becoming a ' . $linked_phrase,
				$settings['editor']
			);
		}

		if ( in_array( $id, $section_ids, true ) ) {
			$settings['tag'] = saber_at_atomic_tag( 'section' );
			$changed_ids[ $id ] = true;
		}

		if ( in_array( $id, $article_ids, true ) ) {
			$settings['tag'] = saber_at_atomic_tag( 'article' );
			$changed_ids[ $id ] = true;
		}

		if ( in_array( $id, $visual_b_ids, true ) ) {
			$settings['editor'] = str_replace( array( '<strong>', '</strong>' ), array( '<b>', '</b>' ), $settings['editor'] );
			$changed_ids[ $id ] = true;
		}

		if ( isset( $strong_phrases[ $id ] ) ) {
			foreach ( $strong_phrases[ $id ] as $phrase ) {
				$settings['editor'] = saber_at_replace_once(
					$phrase,
					'<strong>' . $phrase . '</strong>',
					$settings['editor'],
					$id
				);
			}
			$changed_ids[ $id ] = true;
		}

		if ( isset( $inline_links[ $id ] ) ) {
			foreach ( $inline_links[ $id ] as $link_data ) {
				list( $phrase, $url ) = $link_data;
				$settings['editor'] = saber_at_replace_once(
					$phrase,
					'<a href="' . $url . '" style="color:#0070E0;text-decoration:underline">' . $phrase . '</a>',
					$settings['editor'],
					$id
				);
			}
			$changed_ids[ $id ] = true;
		}

		if ( isset( $button_links[ $id ] ) ) {
			$settings['link'] = saber_at_link( $button_links[ $id ] );
			$changed_ids[ $id ] = true;
		}

		if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
			saber_at_update_nodes( $node['elements'], $changed_ids );
		}
	}
	unset( $node );
}

$changed_ids = array();
saber_at_update_nodes( $data, $changed_ids );

$required_ids = array(
	'athero1', 'atpath1', 'atrequs', 'atfinan', 'ataccre', 'atcta01',
	'atnursg', 'atptapr', 'attuitn', 'atfinaid',
	'6247bbb', '1a3cd74', 'aae96b1', 'ed9505c', 'f124f72', '8346bdb', 'f3da86e',
	'83aeafb', '259d71d', 'f86e4de', 'bfb8746', 'ca6c4c6', '9550f88', '463f8b8',
	'8e6879e', '51e7171', '964fd8a', 'a6093de', '1a70ff1', 'b1a655b',
	'045ff1c', '11c5d50', 'fd6d8ed', '56b4755', 'd087e0e', '92c78d7', 'bf9ebd7',
	'e84d16a', '69f20b3', '29a7814', '218b9a7',
);
$missing = array_diff( $required_ids, array_keys( $changed_ids ) );
if ( $missing ) {
	WP_CLI::error( 'Required Elementor IDs were not updated: ' . implode( ', ', $missing ) );
}

$json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
if ( false === update_post_meta( $page_id, '_elementor_data', wp_slash( $json ) ) ) {
	$current = get_post_meta( $page_id, '_elementor_data', true );
	if ( json_decode( $current, true ) !== $data ) {
		WP_CLI::error( 'Elementor data was not saved for page 1225.' );
	}
}

WP_CLI::success( 'Updated page 1225: ' . implode( ', ', array_keys( $changed_ids ) ) );
