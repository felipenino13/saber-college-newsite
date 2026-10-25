<?php
/**
 * Apply idempotent local AI-indexation improvements.
 * Run with: wp eval-file /tmp/apply-ai-indexation-improvements.php --allow-root
 */

defined( 'ABSPATH' ) || exit;

$result = array(
	'post_headings_updated'       => array(),
	'post_descriptions_updated'   => array(),
	'category_descriptions_set'   => array(),
	'attachment_alt_text_updated' => array(),
	'featured_image_alt_updated'  => array(),
	'content_image_alt_updated'   => array(),
	'elementor_links_updated'     => array(),
	'options_updated'             => array(),
	'errors'                      => array(),
);

$heading_posts = array(
	1174, 1105, 1069, 1060, 1034, 1010, 1003, 1001, 998, 995, 992, 986,
	983, 974, 971, 968, 962, 959, 953, 950, 947, 944, 941,
);

foreach ( $heading_posts as $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || 'post' !== $post->post_type || 'publish' !== $post->post_status ) {
		$result['errors'][] = "Post {$post_id} was not a published post.";
		continue;
	}

	$count = preg_match_all( '/<h1\b[^>]*>/i', $post->post_content );
	if ( 0 === $count ) {
		continue;
	}
	if ( 1 !== $count ) {
		$result['errors'][] = "Post {$post_id} had {$count} content H1 elements; skipped.";
		continue;
	}

	$content = preg_replace( '/<h1\b([^>]*)>/i', '<h2$1>', $post->post_content, 1 );
	$content = preg_replace( '/<\/h1>/i', '</h2>', $content, 1 );
	$updated = wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => $content,
		),
		true
	);
	if ( is_wp_error( $updated ) ) {
		$result['errors'][] = "Post {$post_id}: " . $updated->get_error_message();
	} else {
		$result['post_headings_updated'][] = $post_id;
	}
}

$post_descriptions = array(
	1072 => "Explore SABER College's Physical Therapist Assistant Associate of Science program in Miami, including curriculum, admissions and career preparation.",
	1081 => 'Learn about Physical Therapist Assistant earnings in Florida, factors that affect pay and the career outlook for PTA graduates.',
);

foreach ( $post_descriptions as $post_id => $description ) {
	if ( get_post_meta( $post_id, 'rank_math_description', true ) !== $description ) {
		update_post_meta( $post_id, 'rank_math_description', $description );
		$result['post_descriptions_updated'][] = $post_id;
	}
}

$category_descriptions = array(
	'healthcare-career-resources' => 'Healthcare career resources from SABER College covering education, professional preparation and employment topics.',
	'college-news'                => 'News and institutional updates from SABER College in Miami, Florida.',
	'financial-aid'               => 'SABER College articles about financial aid concepts, tuition planning and resources for eligible students.',
	'healthcare-program'          => 'Articles about healthcare education and career-focused programs at SABER College.',
	'higher-education'            => 'Guidance from SABER College about higher education, student preparation and college planning.',
	'physical-therapist'          => 'Physical Therapist Assistant education, career and licensure information from SABER College.',
	'professional-nursing'        => 'Professional Nursing education, career and licensure information from SABER College.',
	'sabi'                        => 'Stories and updates featuring Sabi, the SABER College mascot and community symbol.',
);

foreach ( $category_descriptions as $slug => $description ) {
	$term = get_term_by( 'slug', $slug, 'category' );
	if ( ! $term ) {
		$result['errors'][] = "Category {$slug} was not found.";
		continue;
	}
	if ( get_term_meta( $term->term_id, 'rank_math_description', true ) !== $description ) {
		update_term_meta( $term->term_id, 'rank_math_description', $description );
		$result['category_descriptions_set'][] = $term->term_id;
	}
}

$attachment_alt_text = array(
	572 => 'Sabi, SABER College mascot',
	457 => 'NLN CNEA accreditation',
	455 => 'Florida Board of Nursing',
	692 => 'Florida Commission for Independent Education',
	1183 => 'SABER College Physical Therapist Assistant student testimonial',
);

foreach ( $attachment_alt_text as $attachment_id => $alt_text ) {
	if ( 'attachment' !== get_post_type( $attachment_id ) ) {
		$result['errors'][] = "Attachment {$attachment_id} was not found.";
		continue;
	}
	if ( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) !== $alt_text ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
		$result['attachment_alt_text_updated'][] = $attachment_id;
	}
}

$published_posts = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);
foreach ( $published_posts as $post_id ) {
	$thumbnail_id = get_post_thumbnail_id( $post_id );
	if ( ! $thumbnail_id || get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ) ) {
		continue;
	}
	$alt_text = wp_strip_all_tags( get_the_title( $post_id ) );
	if ( $alt_text ) {
		update_post_meta( $thumbnail_id, '_wp_attachment_image_alt', $alt_text );
		$result['featured_image_alt_updated'][] = $thumbnail_id;
	}
}

$testimonial_post = get_post( 1179 );
if ( $testimonial_post && preg_match( '/<img\b[^>]*\bwp-image-1183\b[^>]*>/i', $testimonial_post->post_content ) ) {
	$testimonial_content = preg_replace_callback(
		'/<img\b[^>]*\bwp-image-1183\b[^>]*>/i',
		function ( $match ) {
			return preg_replace(
				'/\balt=([' . "'\"" . '])\s*\1/i',
				'alt="SABER College Physical Therapist Assistant student testimonial"',
				$match[0]
			);
		},
		$testimonial_post->post_content,
		1
	);
	if ( $testimonial_content !== $testimonial_post->post_content ) {
		$updated = wp_update_post(
			array(
				'ID'           => $testimonial_post->ID,
				'post_content' => $testimonial_content,
			),
			true
		);
		if ( is_wp_error( $updated ) ) {
			$result['errors'][] = 'Post 1179 image alt: ' . $updated->get_error_message();
		} else {
			$result['content_image_alt_updated'][] = 1183;
		}
	}
}

function saber_update_elementor_links( $elements, $targets, &$changed ) {
	foreach ( $elements as &$element ) {
		if ( isset( $element['id'], $targets[ $element['id'] ] ) ) {
			$target = $targets[ $element['id'] ];
			$link   = array(
				'url'               => $target['url'],
				'is_external'       => 'on',
				'nofollow'          => '',
				'custom_attributes' => 'aria-label|' . $target['label'],
			);
			$old_link_to = isset( $element['settings']['link_to'] ) ? $element['settings']['link_to'] : '';
			$old_link    = isset( $element['settings']['link'] ) ? $element['settings']['link'] : array();
			if ( 'custom' !== $old_link_to || $old_link !== $link ) {
				$element['settings']['link_to'] = 'custom';
				$element['settings']['link']    = $link;
				$changed[]                      = $element['id'];
			}
		}
		if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
			$element['elements'] = saber_update_elementor_links( $element['elements'], $targets, $changed );
		}
	}
	unset( $element );

	return $elements;
}

$accreditation_page_id = 887;
$elementor_json        = get_post_meta( $accreditation_page_id, '_elementor_data', true );
$elementor_data        = json_decode( $elementor_json, true );
if ( is_array( $elementor_data ) ) {
	$targets = array(
		'7d91883' => array(
			'url'   => 'https://web02.fldoe.org/CIE/SearchSchools/SchoolSearchDetail.aspx?schoolID=1400',
			'label' => 'Verify SABER College with the Florida Commission for Independent Education',
		),
		'84c8484' => array(
			'url'   => 'https://council.org/member-directory/',
			'label' => 'View the Council on Occupational Education member directory',
		),
		'965171a' => array(
			'url'   => 'https://www.capteonline.org/programs/master-list-pta',
			'label' => 'View CAPTE accredited Physical Therapist Assistant programs',
		),
		'mktnlni' => array(
			'url'   => 'https://cnea.nln.org/accredited-programs',
			'label' => 'View NLN CNEA accredited nursing programs',
		),
	);
	$changed_widgets = array();
	$elementor_data  = saber_update_elementor_links( $elementor_data, $targets, $changed_widgets );
	if ( $changed_widgets ) {
		update_post_meta( $accreditation_page_id, '_elementor_data', wp_slash( wp_json_encode( $elementor_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) );
		$result['elementor_links_updated'] = $changed_widgets;
	}
} else {
	$result['errors'][] = 'Accreditation page Elementor data could not be decoded.';
}

$titles = get_option( 'rank-math-options-titles', array() );
if ( ! is_array( $titles ) ) {
	$titles = array();
}
$title_updates = array(
	'local_business_type'      => 'CollegeOrUniversity',
	'organization_description' => 'SABER College is a nonprofit postsecondary institution in Miami, Florida, offering career-focused healthcare education.',
);
foreach ( $title_updates as $key => $value ) {
	if ( ! isset( $titles[ $key ] ) || $titles[ $key ] !== $value ) {
		$titles[ $key ] = $value;
		$result['options_updated'][] = 'rank-math-options-titles.' . $key;
	}
}
update_option( 'rank-math-options-titles', $titles );

$general = get_option( 'rank-math-options-general', array() );
if ( ! is_array( $general ) ) {
	$general = array();
}
$general_updates = array(
	'llms_post_types' => array( 'page' ),
	'llms_taxonomies' => array(),
	'llms_limit'      => '50',
);
foreach ( $general_updates as $key => $value ) {
	if ( ! array_key_exists( $key, $general ) || $general[ $key ] !== $value ) {
		$general[ $key ] = $value;
		$result['options_updated'][] = 'rank-math-options-general.' . $key;
	}
}
update_option( 'rank-math-options-general', $general );

echo wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
