<?php
/**
 * Evidence-backed content/compliance corrections for the local SABER site.
 * Preserves native Elementor structures and is safe to run repeatedly.
 */

defined( 'ABSPATH' ) || exit;

function saber_cct_walk( &$nodes, $callback ) {
	foreach ( $nodes as &$node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}
		$callback( $node );
		if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
			saber_cct_walk( $node['elements'], $callback );
		}
	}
	unset( $node );
}

function saber_cct_replace( &$value, $from, $to, &$count ) {
	if ( is_string( $value ) ) {
		$value = str_replace( $from, $to, $value, $hits );
		$count += $hits;
		return;
	}
	if ( is_array( $value ) ) {
		foreach ( $value as &$child ) {
			saber_cct_replace( $child, $from, $to, $count );
		}
		unset( $child );
	}
}

function saber_cct_notice( &$node, $id, $html, &$count ) {
	if ( 'form' !== ( $node['widgetType'] ?? '' ) || empty( $node['settings']['form_fields'] ) ) {
		return;
	}
	foreach ( $node['settings']['form_fields'] as $field ) {
		if ( $id === ( $field['_id'] ?? '' ) ) {
			return;
		}
	}
	$node['settings']['form_fields'][] = array(
		'_id'         => $id,
		'field_type'  => 'html',
		'field_html'  => $html,
		'width'       => '100',
		'css_classes' => 'saber-form-privacy-notice',
	);
	++$count;
}

$consent = '<p>By submitting this form, you give your express written consent, without obligation to purchase, to be contacted by SABER College by text, voice call, mobile device, or email, even if your telephone number appears on a corporate, state, or National Do Not Call Registry. See our <a href="/privacy-policy/">Privacy Policy</a>.</p>';
$survey  = '<p>SABER College uses this information to estimate student cost-of-living needs. Review our <a href="/privacy-policy/">Privacy Policy</a> before submitting.</p>';
$ids     = array( 7, 25, 30, 53, 870, 872, 877, 881, 885, 887, 898, 902, 904, 1225, 1638, 1639, 1640, 1641, 2015 );
$result  = array( 'changed_posts' => array(), 'counts' => array(), 'errors' => array(), 'drafted' => array() );

foreach ( $ids as $post_id ) {
	$data = json_decode( get_post_meta( $post_id, '_elementor_data', true ), true );
	if ( ! is_array( $data ) ) {
		$result['errors'][] = "Post {$post_id}: invalid Elementor JSON";
		continue;
	}
	$count = 0;

	if ( in_array( $post_id, array( 7, 870, 872, 1640, 1641, 2015 ), true ) ) {
		saber_cct_replace( $data, 'Program of interes', 'Program of interest', $count );
	}

	if ( 7 === $post_id ) {
		saber_cct_replace( $data, 'SABER College prepares you to work under the direct supervision of a physical therapist, giving you the skills to restore lives and launch your career. Witness New Levels of Perseverance and Will Power.', 'The Professional Nursing Program is an intensive, full-time program that combines theory, laboratory instruction, and clinical rotation experience to prepare graduates for entry-level nursing practice and eligibility to take the NCLEX-RN.', $count );
		saber_cct_replace( $data, 'clinical requeriments', 'clinical requirements', $count );
		saber_cct_replace( $data, 'Career Outlook For Registered Nurses', 'Career Outlook for Registered Nurses', $count );
		saber_cct_replace( $data, 'the median annual wage for registered nurses was <strong>$77,600 in May 2021</strong> and is expected to grow.', 'the median annual wage for registered nurses was <strong>$93,600 in May 2024</strong>.', $count );
		saber_cct_replace( $data, 'Over the next decade, registered nurse employment is projected to grow 9 percent from 2020 to 2030, about as fast as the average for all occupations. <strong>About 194,500 openings for registered nurses</strong> are projected each year, on average, over the decade.', 'Employment of registered nurses is projected to grow 5 percent from 2024 to 2034, faster than the average for all occupations. <strong>About 189,100 openings for registered nurses</strong> are projected each year, on average, over the decade.', $count );
		saber_cct_walk( $data, function ( &$node ) use ( $consent, &$count ) {
			if ( 'b7159a9' === ( $node['id'] ?? '' ) ) {
				saber_cct_notice( $node, 'c0a7e01', $consent, $count );
			}
		} );
	}

	if ( 25 === $post_id ) {
		saber_cct_replace( $data, 'licensed by the Commission for Independent Education (CIE) since 1991', 'licensed by the Commission for Independent Education (CIE) since 1992', $count );
	}

	if ( 30 === $post_id || 898 === $post_id || 1225 === $post_id ) {
		saber_cct_replace( $data, 'Saber College', 'SABER College', $count );
	}

	if ( 53 === $post_id ) {
		saber_cct_walk( $data, function ( &$node ) use ( &$count ) {
			if ( 'icon-list' === ( $node['widgetType'] ?? '' ) && ! empty( $node['settings']['icon_list'] ) ) {
				foreach ( $node['settings']['icon_list'] as &$item ) {
					if ( 'Terms & Condition' === ( $item['text'] ?? '' ) ) {
						$item['text'] = 'Terms & Conditions';
						++$count;
					}
					if ( 'Manual' === ( $item['text'] ?? '' ) ) {
						$item['text'] = 'Manuals';
						++$count;
					}
				}
				unset( $item );
			}
			if ( '39d889e' === ( $node['id'] ?? '' ) ) {
				saber_cct_replace( $node['settings'], 'Follow US', 'Follow Us', $count );
			}
			if ( '01679b6' === ( $node['id'] ?? '' ) ) {
				$new = 'SABER College is licensed by the Florida Department of Education\'s Commission for Independent Education (CIE), License #1400. The Physical Therapist Assistant Program is accredited by the Commission on Accreditation in Physical Therapy Education (CAPTE).<br>';
				$old = $node['settings']['paragraph']['value']['content'][0]['value'] ?? '';
				if ( $new !== $old ) {
					$node['settings']['paragraph']['value']['content'][0]['value'] = $new;
					++$count;
				}
			}
			if ( 'c2612d2' === ( $node['id'] ?? '' ) ) {
				$new = '© 2026 <a href="/">SABER College</a>. All rights reserved.<br>';
				$old = $node['settings']['paragraph']['value']['content'][0]['value'] ?? '';
				if ( $new !== $old ) {
					$node['settings']['paragraph']['value']['content'][0]['value'] = $new;
					++$count;
				}
			}
		} );
	}

	if ( 870 === $post_id ) {
		saber_cct_walk( $data, function ( &$node ) use ( $consent, &$count ) {
			if ( isset( $node['settings']['title'] ) && false !== strpos( $node['settings']['title'], 'The ASN program is designed to help registered nurses' ) ) {
				$node['settings']['title'] = 'The Professional Nursing Program is an intensive, full-time Associate of Science program that combines classroom instruction, laboratory practice, and clinical rotation experience. It prepares graduates to apply the nursing process across diverse care settings and, after successful completion of the curriculum and comprehensive exit exam, to be eligible to take the NCLEX-RN.';
				++$count;
			}
			if ( isset( $node['settings']['title'] ) && false !== strpos( $node['settings']['title'], 'holds pre-accreditation status' ) ) {
				$node['settings']['title'] = 'SABER College is <strong>institutionally accredited</strong> by the <a href="https://council.org/" style="color:#0070E0;text-decoration:underline">Council on Occupational Education (COE)</a> and licensed by the Commission for Independent Education (CIE). The Associate Degree in Nursing Program holds initial accreditation from the National League for Nursing Commission for Nursing Education Accreditation (NLN CNEA).';
				++$count;
			}
			if ( isset( $node['settings']['editor'] ) && false !== strpos( $node['settings']['editor'], 'Clicking the request form button above constitutes' ) && $consent !== $node['settings']['editor'] ) {
				$node['settings']['editor'] = $consent;
				++$count;
			}
		} );
		saber_cct_replace( $data, 'SABER College of Nursing', 'SABER College', $count );
		saber_cct_replace( $data, 'Saber College of Nursing', 'SABER College', $count );
	}

	if ( 872 === $post_id || 2015 === $post_id ) {
		saber_cct_walk( $data, function ( &$node ) use ( $consent, &$count ) {
			if ( isset( $node['settings']['editor'] ) && false !== strpos( $node['settings']['editor'], 'Clicking the request form button above constitutes' ) && $consent !== $node['settings']['editor'] ) {
				$node['settings']['editor'] = $consent;
				++$count;
			}
		} );
	}

	if ( 877 === $post_id ) {
		saber_cct_walk( $data, function ( &$node ) use ( &$count ) {
			$editor = $node['settings']['editor'] ?? '';
			if ( false === strpos( $editor, 'Score 11.0 in math, language, and reading on the TABE Test' ) ) {
				return;
			}
			$editor = str_replace( 'Applicant must be <strong>18 years of age</strong>.', 'Applicant must be <strong>18 years of age by program completion</strong>. Applicants who are 16 or 17 years old require parent or guardian approval for enrollment.', $editor, $hits );
			$count += $hits;
			$editor = preg_replace( '/<li>\s*<strong>Kaplan \(65\)<\/strong>.*?Score 11\.0 in math, language, and reading on the TABE Test\.\s*<\/li>/s', '<li><strong>Kaplan (65)</strong> is SABER College\'s official entrance exam. It may be taken no more than three times, with a 72-hour wait after the first attempt and a 30-day wait before the third attempt. Equivalent qualifying scores include ACT (18), SAT (1330 before 3/5/2016), SAT (980 on or after 3/6/2016), HESI A2 (80), TEAS (58), or Kaplan (65). Applicants who completed all general education courses or hold an Associate or Bachelor\'s degree or higher may be exempt from Kaplan.</li>', $editor, -1, $hits );
			$count += $hits;
			$editor = preg_replace( '/<li>\s*For Students who are 16-17 years old,.*?<\/li>/s', '', $editor, -1, $hits );
			$count += $hits;
			$node['settings']['editor'] = $editor;
		} );
	}

	if ( 881 === $post_id ) {
		saber_cct_walk( $data, function ( &$node ) use ( &$count ) {
			$editor = $node['settings']['editor'] ?? '';
			if ( false === strpos( $editor, 'Applicant must score at the 11.0 level' ) ) {
				return;
			}
			$editor = str_replace( 'SABER college application', 'SABER College application', $editor, $hits );
			$count += $hits;
			$editor = preg_replace( '/<li>Applicant must score at the 11\.0 level.*?<\/li>/s', '<li><strong>Kaplan (65)</strong> is SABER College\'s official entrance exam. It may be taken no more than three times, with a 72-hour wait after the first attempt and a 30-day wait before the third attempt. Equivalent qualifying scores include ACT (18), SAT (1330 before 3/5/2016), SAT (980 on or after 3/6/2016), HESI A2 (80), TEAS (58), or Kaplan (65). Students with an AA or higher degree are exempt.</li>', $editor, -1, $hits );
			$count += $hits;
			$node['settings']['editor'] = $editor;
		} );
	}

	if ( 885 === $post_id ) {
		saber_cct_replace( $data, 'paid in full in full', 'paid in full', $count );
	}
	if ( 887 === $post_id || 902 === $post_id ) {
		saber_cct_replace( $data, 'karocha@sabercollege.edu', 'karenf@sabercollege.edu', $count );
	}

	if ( 904 === $post_id ) {
		saber_cct_walk( $data, function ( &$node ) use ( $survey, &$count ) {
			if ( 'form' === ( $node['widgetType'] ?? '' ) ) {
				saber_cct_notice( $node, 'c0a7e04', $survey, $count );
			}
		} );
	}

	if ( 1638 === $post_id ) {
		saber_cct_walk( $data, function ( &$node ) use ( $consent, &$count ) {
			if ( '1ab1020' === ( $node['id'] ?? '' ) ) {
				saber_cct_notice( $node, 'c0a7e02', $consent, $count );
			}
		} );
		saber_cct_replace( $data, 'Fully Accredited BON, COE, and CIE', 'Institutionally accredited by COE; licensed by CIE; nursing program accredited by NLN CNEA', $count );
		saber_cct_replace( $data, 'Fully Accredited CAPTE, COE, and CIE', 'Institutionally accredited by COE; licensed by CIE; PTA program accredited by CAPTE', $count );
	}

	if ( 1639 === $post_id ) {
		saber_cct_walk( $data, function ( &$node ) use ( $consent, &$count ) {
			if ( '3d1ed48' === ( $node['id'] ?? '' ) ) {
				saber_cct_notice( $node, 'c0a7e03', $consent, $count );
			}
		} );
	}

	if ( 1640 === $post_id || 1641 === $post_id ) {
		saber_cct_walk( $data, function ( &$node ) use ( $post_id, $consent, &$count ) {
			if ( isset( $node['settings']['editor'] ) && false !== strpos( $node['settings']['editor'], 'Clicking the request form button above constitutes' ) && $consent !== $node['settings']['editor'] ) {
				$node['settings']['editor'] = $consent;
				++$count;
			}
			if ( isset( $node['settings']['editor'] ) && false !== strpos( $node['settings']['editor'], 'Occupational Occupation (COE)' ) ) {
				$program = 1640 === $post_id
					? 'The Associate Degree in Nursing Program holds initial accreditation from the National League for Nursing Commission for Nursing Education Accreditation (NLN CNEA).'
					: 'The Physical Therapist Assistant Program is accredited by the Commission on Accreditation in Physical Therapy Education (CAPTE).';
				$node['settings']['editor'] = '<p class="text-white text-center" style="font-size: 12px">SABER College is institutionally accredited by the Council on Occupational Education (COE) and licensed by the Florida Commission for Independent Education (CIE), License #1400. ' . $program . '</p>';
				++$count;
			}
		} );
		saber_cct_replace( $data, 'Secure your sport today.', 'Secure your spot today.', $count );
	}

	if ( $count > 0 ) {
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) );
		$result['changed_posts'][] = $post_id;
	}
	$result['counts'][ (string) $post_id ] = $count;
}

$privacy_id = 1232;
$content    = get_post_field( 'post_content', $privacy_id );
$updated    = str_replace( '<a href="/"></a>', '<a href="/">sabercollege.edu</a>', $content, $links );
$updated    = str_replace( '<a>saber@sabercollege.edu</a>', '<a href="mailto:saber@sabercollege.edu">saber@sabercollege.edu</a>', $updated, $emails );
if ( $updated !== $content ) {
	wp_update_post( array( 'ID' => $privacy_id, 'post_content' => wp_slash( $updated ) ) );
	$result['changed_posts'][] = $privacy_id;
}
$result['counts']['1232'] = $links + $emails;

foreach ( array( 1328, 2066 ) as $draft_id ) {
	if ( 'draft' !== get_post_status( $draft_id ) ) {
		wp_update_post( array( 'ID' => $draft_id, 'post_status' => 'draft' ) );
		$result['drafted'][] = $draft_id;
	}
}

$result['changed_posts'] = array_values( array_unique( $result['changed_posts'] ) );
echo wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
