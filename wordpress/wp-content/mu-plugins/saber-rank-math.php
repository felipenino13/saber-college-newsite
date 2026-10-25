<?php
/**
 * Plugin Name: SABER College Rank Math Integration
 * Description: Adds stable institutional, program, language, and llms.txt signals to Rank Math.
 */

defined( 'ABSPATH' ) || exit;

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

/**
 * Use Rank Math's database sitemap cache so CLI and web requests behave alike
 * in the local container, where the direct filesystem constants are not loaded
 * by WP-CLI.
 */
add_filter(
	'rank_math/sitemap/cache_mode',
	function () {
		return 'db';
	}
);

function saber_schema_has_type( $entity, $type ) {
	if ( ! is_array( $entity ) || ! isset( $entity['@type'] ) ) {
		return false;
	}

	return in_array( $type, (array) $entity['@type'], true );
}

function saber_college_schema_id() {
	return home_url( '/#organization' );
}

function saber_rank_math_json_ld( $data ) {
	if ( ! is_array( $data ) ) {
		return $data;
	}

	$organization_id = saber_college_schema_id();
	$page_id         = get_queried_object_id();
	$page_slug       = $page_id ? get_post_field( 'post_name', $page_id ) : '';
	$language        = saber_post_language( $page_id );

	foreach ( $data as $key => $entity ) {
		if ( saber_schema_has_type( $entity, 'Organization' ) || saber_schema_has_type( $entity, 'CollegeOrUniversity' ) ) {
			$data[ $key ]['@type']         = 'CollegeOrUniversity';
			$data[ $key ]['@id']           = $organization_id;
			$data[ $key ]['name']          = 'SABER College';
			$data[ $key ]['legalName']     = 'Spanish American Basic Education and Rehabilitation, Inc.';
			$data[ $key ]['alternateName'] = array( 'SABER', 'SABER College' );
			$data[ $key ]['description']   = 'SABER College is a nonprofit postsecondary institution in Miami, Florida, offering career-focused healthcare education.';
			$data[ $key ]['foundingDate']  = '1972';
			$data[ $key ]['telephone']     = '+1-305-443-9170';
			$data[ $key ]['email']         = 'saber@sabercollege.edu';
			$data[ $key ]['address']       = array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => '3990 West Flagler Street, Suite 103',
				'addressLocality' => 'Miami',
				'addressRegion'   => 'FL',
				'postalCode'      => '33134',
				'addressCountry'  => 'US',
			);
			$data[ $key ]['sameAs']        = array(
				'https://www.facebook.com/mysabercollege',
				'https://www.instagram.com/sabercollege/',
				'https://www.tiktok.com/@sabercollege',
			);
			$data[ $key ]['identifier']    = array(
				array(
					'@type'      => 'PropertyValue',
					'propertyID' => 'Florida Commission for Independent Education School ID',
					'value'      => '1400',
					'url'        => 'https://web02.fldoe.org/CIE/SearchSchools/SchoolSearchDetail.aspx?schoolID=1400',
				),
				array(
					'@type'      => 'PropertyValue',
					'propertyID' => 'Council on Occupational Education Institution ID',
					'value'      => '220500',
					'url'        => 'https://council.org/member-directory/',
				),
			);
			$data[ $key ]['subjectOf']     = array(
				array(
					'@type' => 'WebPage',
					'url'   => 'https://web02.fldoe.org/CIE/SearchSchools/SchoolSearchDetail.aspx?schoolID=1400',
				),
				array(
					'@type' => 'WebPage',
					'url'   => 'https://council.org/member-directory/',
				),
			);
		}

		if ( isset( $entity['inLanguage'] ) && 'es-US' === $language ) {
			$data[ $key ]['inLanguage'] = 'es-US';
		}

		if ( is_singular( 'post' ) && saber_schema_has_type( $entity, 'BlogPosting' ) ) {
			$data[ $key ]['author'] = array( '@id' => $organization_id );
		}
	}

	if ( is_singular( 'post' ) ) {
		foreach ( $data as $key => $entity ) {
			if ( saber_schema_has_type( $entity, 'Person' ) && isset( $entity['name'] ) && 'cristiannino' === strtolower( (string) $entity['name'] ) ) {
				unset( $data[ $key ] );
			}
		}
	}

	$program = array();
	if ( 'professional-nursing-program' === $page_slug ) {
		$program = saber_program_schema(
			$page_id,
			'Professional Nursing Program',
			'An Associate of Science nursing program at SABER College that prepares students for entry-level registered nursing practice.',
			'Associate of Science Degree in Nursing',
			80,
			'PT1725H',
			'Professional Nursing',
			'29-1141 Registered Nurses',
			'https://cnea.nln.org/accredited-programs'
		);
	} elseif ( 'physical-therapist-assistant-program' === $page_slug ) {
		$program = saber_program_schema(
			$page_id,
			'Physical Therapist Assistant Program',
			'An Associate of Science Physical Therapist Assistant program at SABER College that prepares students for entry-level PTA practice.',
			'Associate of Science Degree',
			76,
			'PT1785H',
			'Physical Therapist Assistant',
			'31-2021 Physical Therapist Assistants',
			'https://www.capteonline.org/programs/master-list-pta'
		);
	}

	if ( $program ) {
		$data['saber_educational_program'] = $program;
		foreach ( $data as $key => $entity ) {
			if ( saber_schema_has_type( $entity, 'WebPage' ) ) {
				$data[ $key ]['mainEntity'] = array( '@id' => $program['@id'] );
				$data[ $key ]['about']      = array( '@id' => $program['@id'] );
			}
		}
	}

	if ( 'saber_college_programs' === $page_slug ) {
		$nursing = get_page_by_path( 'saber_college_programs/professional-nursing-program' );
		$pta     = get_page_by_path( 'saber_college_programs/physical-therapist-assistant-program' );
		$items   = array();
		foreach ( array_filter( array( $nursing, $pta ) ) as $position => $page ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $position + 1,
				'name'     => get_the_title( $page ),
				'url'      => get_permalink( $page ),
			);
		}
		if ( $items ) {
			$data['saber_program_list'] = array(
				'@type'           => 'ItemList',
				'@id'             => get_permalink( $page_id ) . '#program-list',
				'name'            => 'SABER College academic programs',
				'itemListElement' => $items,
			);
		}
	}

	if ( 'saber-college-accreditation' === $page_slug ) {
		foreach ( $data as $key => $entity ) {
			if ( saber_schema_has_type( $entity, 'WebPage' ) ) {
				$data[ $key ]['about']    = array( '@id' => $organization_id );
				$data[ $key ]['citation'] = array(
					'https://web02.fldoe.org/CIE/SearchSchools/SchoolSearchDetail.aspx?schoolID=1400',
					'https://council.org/member-directory/',
					'https://cnea.nln.org/accredited-programs',
					'https://www.capteonline.org/programs/master-list-pta',
				);
			}
		}
	}

	return $data;
}

function saber_program_schema( $page_id, $name, $description, $credential, $credits, $duration, $program_type, $occupation, $specialty_source ) {
	$url = get_permalink( $page_id );
	return array(
		'@type'                        => 'EducationalOccupationalProgram',
		'@id'                          => $url . '#educational-program',
		'name'                         => $name,
		'description'                  => $description,
		'url'                          => $url,
		'educationalCredentialAwarded' => array(
			'@type' => 'EducationalOccupationalCredential',
			'name'  => $credential,
		),
		'numberOfCredits'              => $credits,
		'timeToComplete'               => $duration,
		'programType'                  => $program_type,
		'occupationalCategory'         => $occupation,
		'provider'                     => array( '@id' => saber_college_schema_id() ),
		'mainEntityOfPage'             => array( '@id' => $url . '#webpage' ),
		'inLanguage'                   => 'en-US',
		'subjectOf'                    => array(
			array( '@type' => 'WebPage', 'url' => 'https://web02.fldoe.org/CIE/SearchSchools/SchoolSearchDetail.aspx?schoolID=1400' ),
			array( '@type' => 'WebPage', 'url' => $specialty_source ),
		),
	);
}

add_filter( 'rank_math/json_ld', 'saber_rank_math_json_ld', 100 );

function saber_llms_page_paths() {
	return array(
		'home',
		'about_saber_college',
		'saber_college_programs',
		'saber_college_programs/professional-nursing-program',
		'saber_college_programs/professional-nursing-program/nursing-admissions',
		'saber_college_programs/physical-therapist-assistant-program',
		'saber_college_programs/physical-therapist-assistant-program/admissions-pta',
		'admissions-and-tuition',
		'apply-to-saber-college',
		'general/tuition-payment-options',
		'saber-college-accreditation',
		'student-services',
		'student-services/academic-affairs',
		'student-services/career-services',
		'general',
		'campus-location',
		'general/saber-college-faculty',
		'general/manuals',
		'general/graduation-requirements',
		'general/gainful-employment-programs',
		'general/saber-college-distance-education',
		'general/net-price-calculator',
		'about_saber_college/contact',
		'privacy-policy',
		'terms-conditions',
		'general/safety-plan',
	);
}

function saber_rank_math_llms_posts( $posts, $args ) {
	if ( empty( $args['post_type'] ) || 'page' !== $args['post_type'] ) {
		return array();
	}

	$curated = array();
	foreach ( saber_llms_page_paths() as $path ) {
		$page = get_page_by_path( $path );
		if ( ! $page || 'publish' !== $page->post_status ) {
			continue;
		}

		$description = get_post_meta( $page->ID, 'rank_math_description', true );
		if ( $description ) {
			$page->post_excerpt = wp_strip_all_tags( $description );
		}
		$curated[] = $page;
	}

	return $curated;
}

add_filter( 'rank_math/llms_txt/posts', 'saber_rank_math_llms_posts', 20, 2 );
add_filter( 'rank_math/llms_txt/terms', '__return_empty_array', 20 );

function saber_rank_math_llms_extra_content( $extra ) {
	$lines = array(
		'## Institution facts',
		'- SABER College is a nonprofit postsecondary institution founded in 1972 in Miami, Florida.',
		'- Address: 3990 West Flagler Street, Suite 103, Miami, FL 33134.',
		'- Contact: +1-305-443-9170; saber@sabercollege.edu.',
		'',
		'## Official verification sources',
		'- Florida Commission for Independent Education: https://web02.fldoe.org/CIE/SearchSchools/SchoolSearchDetail.aspx?schoolID=1400',
		'- Council on Occupational Education directory: https://council.org/member-directory/',
		'- NLN CNEA accredited programs: https://cnea.nln.org/accredited-programs',
		'- CAPTE accredited PTA programs: https://www.capteonline.org/programs/master-list-pta',
	);

	if ( $extra ) {
		$lines[] = '';
		$lines[] = trim( $extra );
	}

	return implode( "\n", $lines );
}

add_filter( 'rank_math/llms_txt/extra_content', 'saber_rank_math_llms_extra_content', 20 );

function saber_language_pairs() {
	return array(
		'7-reasons-to-study-nursing-in-miami-fl'          => '7_razones_para_estudiar_enfermeria',
		'registered_nurse_in_miami_florida'               => 'programa_de_enfermeria_en_miami',
		'nursing-program-earn-nln-cnea-accreditation'     => 'programa-de-enfermeria-nln-cnea',
		'saber-college-pta-students-join-moving-day-miami' => 'saber-college-pta-camina-moving-day-miami',
		'get_ready_npte_pta_exam'                          => 'preparacion-examen-npte-pta-saber-college',
		'npte-exam-pta-saber-college'                      => 'examen-npte-pta-saber-college',
		'clinical-simulation-in-nursing-saber-college'     => 'simulacion-clinica-en-enfermeria-saber-college',
		'study-nursing-in-miami-2026'                      => 'estudiar-enfermeria-en-miami-2026',
		'physical-therapist-assistant-role-explained'       => 'rol-del-physical-therapist-assistant',
		'pavel_pugh_nln_cnea_appointment'                   => 'saber-college-dr-pavel-pugh-nombramiento-nln-cnea',
		'weekend-nursing-program'                           => 'programa-de-enfermeria-de-fin-de-semana-miami',
		'weekend-nursing-programs'                          => 'programas-enfermeria-fines-de-semana',
	);
}

function saber_post_language( $post_id ) {
	if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
		return 'en-US';
	}

	$slug = get_post_field( 'post_name', $post_id );
	return in_array( $slug, array_values( saber_language_pairs() ), true ) ? 'es-US' : 'en-US';
}

function saber_language_attributes( $output ) {
	if ( is_singular( 'post' ) && 'es-US' === saber_post_language( get_queried_object_id() ) ) {
		$output = preg_replace( '/lang=([\'\"])[^\'\"]*\1/i', 'lang="es-US"', $output );
	}

	return $output;
}

add_filter( 'language_attributes', 'saber_language_attributes', 20 );

function saber_print_hreflang_links() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$current = get_post_field( 'post_name', get_queried_object_id() );
	$pairs   = saber_language_pairs();
	$english = '';
	$spanish = '';

	if ( isset( $pairs[ $current ] ) ) {
		$english = $current;
		$spanish = $pairs[ $current ];
	} else {
		$english = array_search( $current, $pairs, true );
		$spanish = $english ? $current : '';
	}

	if ( ! $english || ! $spanish ) {
		return;
	}

	$english_post = get_page_by_path( $english, OBJECT, 'post' );
	$spanish_post = get_page_by_path( $spanish, OBJECT, 'post' );
	if ( ! $english_post || ! $spanish_post ) {
		return;
	}

	$english_url = get_permalink( $english_post );
	$spanish_url = get_permalink( $spanish_post );
	echo '<link rel="alternate" hreflang="en-US" href="' . esc_url( $english_url ) . '" />' . "\n";
	echo '<link rel="alternate" hreflang="es-US" href="' . esc_url( $spanish_url ) . '" />' . "\n";
	echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $english_url ) . '" />' . "\n";
}

add_action( 'wp_head', 'saber_print_hreflang_links', 2 );
