<?php
/**
 * Improve semantic markup, internal links, source links, and form labels on
 * the Programs, Professional Nursing, and PTA pages while preserving native
 * Elementor editability and all visible wording.
 *
 * Run with:
 *   wp eval-file /path/to/update-program-pages-semantic-markup.php --allow-root
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

function saber_programs_atomic_tag( $tag ) {
	return array(
		'$$type' => 'string',
		'value'  => $tag,
	);
}

function saber_programs_heading_link( $url ) {
	return array(
		'url'         => $url,
		'is_external' => '',
		'nofollow'    => '',
	);
}

function saber_programs_replace_once( $search, $replace, $subject, $widget_id ) {
	if ( false !== strpos( $subject, $replace ) ) {
		return $subject;
	}

	if ( false === strpos( $subject, $search ) ) {
		WP_CLI::error( "Expected text was not found in widget {$widget_id}." );
	}

	return preg_replace( '/' . preg_quote( $search, '/' ) . '/', str_replace( '$', '\\$', $replace ), $subject, 1 );
}

function saber_programs_repair_form_script( $html, $widget_id ) {
	$html = str_replace( '#form-field-name', '#form-field-fname', $html );
	if ( false === strpos( $html, 'function phoneFormat(input)' ) ) {
		$html = saber_programs_replace_once(
			'phone.addEventListener("input", function () {',
			"function phoneFormat(input) {\r\nreturn input;\r\n}\r\n\r\nphone.addEventListener(\"input\", function () {",
			$html,
			$widget_id
		);
	}
	$html = preg_replace(
		'/\/\/function zipFormat\(input\)\s*\{.*?return input;\s*\}/s',
		'// ZIP field is not used in this form.',
		$html,
		1
	);
	$html = preg_replace(
		'/\s*\/\/ Guardarlo en sessionStorage \(convirtiéndolo a JSON\)\s*sessionStorage\.setItem\("usuario", JSON\.stringify\(usuario\)\);/',
		'',
		$html,
		1
	);
	return $html;
}

function saber_programs_update_nodes( &$nodes, $page_id, &$changed_ids ) {
	$section_ids = array(
		30  => array( '833011f', '9fa37e0' ),
		870 => array( '4644a4b', 'e855555', 'b84586e', '7a1747a', '4a89383', 'a65f498', '6d43fa3', '08a8126' ),
		872 => array( '6a32f56', 'd6cc05b', '8ff5847', '703778b', '0813bf6', '3cbd760', 'b8d7193' ),
	);
	$strong_phrases = array(
		30 => array(
			'3d1b071' => array( 'skills to restore lives and launch your career' ),
			'906ad4d' => array( 'career in physical therapy and health sciences' ),
			'3f285e5' => array( 'career coaches are here to help you every step' ),
			'6088d8d' => array( 'real-world experience to the classroom' ),
			'40c1c6d' => array( 'career training and hands-on experience' ),
		),
		870 => array(
			'61874eb' => array( 'skills you need for a career in caregiving' ),
			'06211c2' => array( 'small class sizes', 'individualized attention' ),
			'33666c9' => array( 'safe and effective patient care' ),
			'f574666' => array( 'build a network of peers in your classes' ),
			'ecce8e5' => array( 'physical exams and take detailed health care histories' ),
			'60a38d7' => array( 'knowledge, skills, and abilities essential for the entry-level nurse' ),
			'767d06b' => array(
				'Associate of Science in Nursing (ASN)',
				'Associate Degree in Nursing (ADN)',
				'Associate of Applied Science in Nursing (AASN)',
			),
			'069e93f' => array( 'skills, knowledge, and attitude' ),
			'1df3105' => array( '80 credit hours/1,725 clock hours' ),
			'0cdd1bc' => array( 'Associate of Science Degree in Nursing (ASN)' ),
			'5d94031' => array( 'fully accredited' ),
			'f487102' => array( 'initial accreditation' ),
		),
		872 => array(
			'6267866' => array( 'career in physical therapy and health sciences' ),
			'8a111f5' => array( 'theory, skills, and clinical training' ),
			'0303db6' => array( 'knowledge and experience in a medical setting' ),
			'8590aa0' => array( 'hands-on clinical training' ),
			'cbe8c79' => array( 'prepared for anything that might happen during your session with a client' ),
			'06ed8e7' => array( 'regain movement and manage pain' ),
			'2c16049' => array( 'vary based on experience and employer' ),
			'09b98ba' => array( 'blend of theory, skills, and clinical training' ),
			'a0af50b' => array( 'entry-level physical therapist assistant' ),
			'913a540' => array( 'Students are responsible for their own transportation and housing as needed.' ),
		),
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

		if ( in_array( $id, $section_ids[ $page_id ] ?? array(), true ) ) {
			$settings['tag'] = saber_programs_atomic_tag( 'section' );
			$changed_ids[ $id ] = true;
		}

		if ( isset( $strong_phrases[ $page_id ][ $id ] ) ) {
			foreach ( $strong_phrases[ $page_id ][ $id ] as $phrase ) {
				$settings['title'] = saber_programs_replace_once(
					$phrase,
					'<strong>' . $phrase . '</strong>',
					$settings['title'],
					$id
				);
			}
			$changed_ids[ $id ] = true;
		}

		if ( 30 === $page_id ) {
			switch ( $id ) {
				case '5794b14':
				case '4a1f976':
					$settings['tag'] = saber_programs_atomic_tag( 'article' );
					unset( $settings['link'] );
					$changed_ids[ $id ] = true;
					break;

				case '63f56fc':
					$settings['link'] = saber_programs_heading_link( '/saber_college_programs/professional-nursing-program/' );
					$changed_ids[ $id ] = true;
					break;

				case 'a36e1e4':
					$settings['link'] = saber_programs_heading_link( '/saber_college_programs/physical-therapist-assistant-program/' );
					$changed_ids[ $id ] = true;
					break;

				case '6eeae57':
					$settings['header_size'] = 'h2';
					$changed_ids[ $id ] = true;
					break;

				case '3d8bc99':
					$settings['header_size'] = 'p';
					$changed_ids[ $id ] = true;
					break;

				case '3f285e5':
					$settings['title'] = saber_programs_replace_once(
						'career services',
						'<a href="/student-services/career-services/" style="color:#FFFFFF;text-decoration:underline">career services</a>',
						$settings['title'],
						$id
					);
					$changed_ids[ $id ] = true;
					break;
			}
		}

		if ( 870 === $page_id ) {
			switch ( $id ) {
				case '7ad5681':
					$settings['title'] = str_replace( array( '<strong>', '</strong>' ), array( '<b>', '</b>' ), $settings['title'] );
					$changed_ids[ $id ] = true;
					break;

				case '9e6ab60':
					$settings['title'] = saber_programs_replace_once(
						'NCLEX-RN',
						'<a href="https://www.nclex.com/index.page" style="color:#0070E0;text-decoration:underline">NCLEX-RN</a>',
						$settings['title'],
						$id
					);
					$changed_ids[ $id ] = true;
					break;

				case '5d94031':
					$settings['title'] = saber_programs_replace_once(
						'Council on Occupational Education (COE)',
						'<a href="https://council.org/" style="color:#0070E0;text-decoration:underline">Council on Occupational Education (COE)</a>',
						$settings['title'],
						$id
					);
					$changed_ids[ $id ] = true;
					break;

				case 'f487102':
					$settings['title'] = saber_programs_replace_once(
						'National League for Nursing Commission for Nursing Education Accreditation (NLN CNEA)',
						'<a href="https://cnea.nln.org/" style="color:#0070E0;text-decoration:underline">National League for Nursing Commission for Nursing Education Accreditation (NLN CNEA)</a>',
						$settings['title'],
						$id
					);
					$changed_ids[ $id ] = true;
					break;

				case 'a4f5da5':
					$settings['form_name'] = 'Request Information';
					$changed_ids[ $id ] = true;
					break;

				case 'a7dc4bb':
					$settings['html'] = saber_programs_repair_form_script( $settings['html'], $id );
					$changed_ids[ $id ] = true;
					break;
			}
		}

		if ( 872 === $page_id ) {
			switch ( $id ) {
				case '14ffb1a':
					$settings['title'] = str_replace( array( '<strong>', '</strong>' ), array( '<b>', '</b>' ), $settings['title'] );
					$changed_ids[ $id ] = true;
					break;

				case '0dabd28':
					$settings['title'] = saber_programs_replace_once(
						'United States Bureau of Labor Statistics',
						'<a href="https://www.bls.gov/ooh/healthcare/physical-therapist-assistants-and-aides.htm" style="color:#0070E0;text-decoration:underline">United States Bureau of Labor Statistics</a>',
						$settings['title'],
						$id
					);
					$changed_ids[ $id ] = true;
					break;

				case '2c16049':
					$settings['title'] = saber_programs_replace_once(
						'BLS',
						'<a href="https://www.bls.gov/ooh/healthcare/physical-therapist-assistants-and-aides.htm" style="color:#0070E0;text-decoration:underline">BLS</a>',
						$settings['title'],
						$id
					);
					$changed_ids[ $id ] = true;
					break;

				case '70ee288':
					$settings['title'] = saber_programs_replace_once(
						'National Physical Therapy Exam (NPTE)',
						'<a href="https://www.fsbpt.org/secondary-pages/exam-candidates/national-exam-npte" style="color:#FFFFFF;text-decoration:underline">National Physical Therapy Exam (NPTE)</a>',
						$settings['title'],
						$id
					);
					$changed_ids[ $id ] = true;
					break;

				case 'f6de1b6':
					$settings['title']       = strip_tags( $settings['title'] );
					$settings['header_size'] = 'h3';
					$changed_ids[ $id ] = true;
					break;
			}
		}

		if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
			saber_programs_update_nodes( $node['elements'], $page_id, $changed_ids );
		}
	}
	unset( $node );
}

function saber_programs_save_page( $page_id, $required_ids ) {
	$raw  = get_post_meta( $page_id, '_elementor_data', true );
	$data = json_decode( $raw, true );

	if ( ! is_array( $data ) ) {
		WP_CLI::error( "Could not decode Elementor data for post {$page_id}." );
	}

	$changed_ids = array();
	saber_programs_update_nodes( $data, $page_id, $changed_ids );

	$missing = array_diff( $required_ids, array_keys( $changed_ids ) );
	if ( $missing ) {
		WP_CLI::error( "Required Elementor IDs were not updated on post {$page_id}: " . implode( ', ', $missing ) );
	}

	$json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( false === update_post_meta( $page_id, '_elementor_data', wp_slash( $json ) ) ) {
		$current = get_post_meta( $page_id, '_elementor_data', true );
		if ( json_decode( $current, true ) !== $data ) {
			WP_CLI::error( "Elementor data was not saved for post {$page_id}." );
		}
	}

	WP_CLI::success( "Updated post {$page_id}: " . implode( ', ', array_keys( $changed_ids ) ) );
}

function saber_programs_update_form_template() {
	$template_id = 2015;
	$raw         = get_post_meta( $template_id, '_elementor_data', true );
	$data        = json_decode( $raw, true );

	if ( ! is_array( $data ) ) {
		WP_CLI::error( 'Could not decode Elementor data for template 2015.' );
	}

	$found        = false;
	$script_found = false;
	$walk         = function ( &$nodes ) use ( &$walk, &$found, &$script_found ) {
		foreach ( $nodes as &$node ) {
			if ( 'f698ff6' === ( $node['id'] ?? '' ) ) {
				$node['settings']['form_name'] = 'Request Information';
				$found = true;
			}
			if ( '518a0bf' === ( $node['id'] ?? '' ) ) {
				$node['settings']['html'] = saber_programs_repair_form_script( $node['settings']['html'], '518a0bf' );
				$script_found = true;
			}
			if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
				$walk( $node['elements'] );
			}
		}
		unset( $node );
	};
	$walk( $data );

	if ( ! $found ) {
		WP_CLI::error( 'Form widget f698ff6 was not found in template 2015.' );
	}
	if ( ! $script_found ) {
		WP_CLI::error( 'Form script widget 518a0bf was not found in template 2015.' );
	}

	$json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( false === update_post_meta( $template_id, '_elementor_data', wp_slash( $json ) ) ) {
		$current = get_post_meta( $template_id, '_elementor_data', true );
		if ( json_decode( $current, true ) !== $data ) {
			WP_CLI::error( 'Elementor data was not saved for template 2015.' );
		}
	}

	WP_CLI::success( 'Updated shared form template 2015: f698ff6, 518a0bf' );
}

saber_programs_save_page(
	30,
	array(
		'833011f', '5794b14', '4a1f976', '63f56fc', 'a36e1e4', '9fa37e0', '6eeae57', '3d8bc99',
		'3d1b071', '906ad4d', '3f285e5', '6088d8d', '40c1c6d',
	)
);

saber_programs_save_page(
	870,
	array(
		'4644a4b', 'e855555', 'b84586e', '7a1747a', '4a89383', 'a65f498', '6d43fa3', '08a8126',
		'7ad5681', '9e6ab60', '5d94031', 'f487102', 'a4f5da5', 'a7dc4bb',
		'61874eb', '06211c2', '33666c9', 'f574666', 'ecce8e5', '60a38d7', '767d06b', '069e93f',
		'1df3105', '0cdd1bc',
	)
);

saber_programs_save_page(
	872,
	array(
		'6a32f56', 'd6cc05b', '8ff5847', '703778b', '0813bf6', '3cbd760', 'b8d7193',
		'14ffb1a', '0dabd28', '2c16049', '70ee288', 'f6de1b6',
		'6267866', '8a111f5', '0303db6', '8590aa0', 'cbe8c79', '06ed8e7', '09b98ba', 'a0af50b', '913a540',
	)
);

saber_programs_update_form_template();

