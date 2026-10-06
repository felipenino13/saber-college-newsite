<?php
/**
 * Adds the calculator shortcode to Elementor and registers its legacy redirect.
 *
 * Run with:
 * wp eval-file /tmp/integrate-net-price-calculator.php --allow-root
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "This script must run through WP-CLI.\n" );
}

$page_id = 911;
$post    = get_post( $page_id );

if ( ! $post || 'page' !== $post->post_type ) {
	WP_CLI::error( 'The canonical Net Price Calculator page was not found.' );
}

$raw_data = get_post_meta( $page_id, '_elementor_data', true );
$data     = json_decode( $raw_data, true );

if ( ! is_array( $data ) || empty( $data ) ) {
	WP_CLI::error( 'The page does not contain valid Elementor data.' );
}

if ( ! class_exists( '\\RankMath\\Redirections\\Redirection' ) ) {
	WP_CLI::error( 'Rank Math Redirections is not available. Nothing was changed.' );
}

global $wpdb;
$redirect_table = $wpdb->prefix . 'rank_math_redirections';
$snapshot       = array(
	'post'         => (array) $post,
	'elementor'    => $raw_data,
	'redirections' => $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$redirect_table} WHERE sources LIKE %s",
			'%' . $wpdb->esc_like( 'Net-Price-Calculator.html' ) . '%'
		),
		ARRAY_A
	),
);
$stamp          = gmdate( 'Ymd-His' );
$backup         = '/tmp/saber-net-price-integration-before-' . $stamp . '.json';

if ( false === file_put_contents( $backup, wp_json_encode( $snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) ) {
	WP_CLI::error( 'Could not create the integration snapshot. Nothing was changed.' );
}

function saber_npc_set_anchor_link( &$nodes, $id ) {
	foreach ( $nodes as &$node ) {
		if ( ( $node['id'] ?? '' ) === $id ) {
			$node['settings']['link'] = array(
				'url'               => '#net-price-calculator',
				'is_external'       => '',
				'nofollow'          => '',
				'custom_attributes' => '',
			);
			return true;
		}

		if ( ! empty( $node['elements'] ) && saber_npc_set_anchor_link( $node['elements'], $id ) ) {
			return true;
		}
	}

	return false;
}

foreach ( array( '4f1a53b', 'npcimg2' ) as $link_element_id ) {
	if ( ! saber_npc_set_anchor_link( $data, $link_element_id ) ) {
		WP_CLI::error( "Elementor element {$link_element_id} was not found. Nothing was changed." );
	}
}

$embed_index = null;
foreach ( $data as $index => $element ) {
	if ( 'npcembd' === ( $element['id'] ?? '' ) ) {
		$embed_index = $index;
		break;
	}
}

$typed = static function ( $type, $value ) {
	return array(
		'$$type' => $type,
		'value'  => $value,
	);
};
$size = static function ( $value, $unit = 'px' ) use ( $typed ) {
	return $typed(
		'size',
		array(
			'size' => $value,
			'unit' => $unit,
		)
	);
};
$dimensions = static function ( $values ) use ( $typed, $size ) {
	$normalized = array();
	foreach ( $values as $side => $value ) {
		$normalized[ $side ] = $size( $value );
	}

	return $typed( 'dimensions', $normalized );
};
$background = static function ( $color ) use ( $typed ) {
	return $typed(
		'background',
		array(
			'color' => $typed( 'color', $color ),
		)
	);
};
$styles = static function ( $class_name, $desktop, $mobile = null ) {
	$variants = array(
		array(
			'meta'       => array(
				'breakpoint' => 'desktop',
				'state'      => null,
			),
			'props'      => $desktop,
			'custom_css' => null,
		),
	);

	if ( is_array( $mobile ) ) {
		$variants[] = array(
			'meta'       => array(
				'breakpoint' => 'mobile',
				'state'      => null,
			),
			'props'      => $mobile,
			'custom_css' => null,
		);
	}

	return array(
		$class_name => array(
			'id'       => $class_name,
			'label'    => 'local',
			'type'     => 'class',
			'variants' => $variants,
		),
	);
};

$shortcode_widget = array(
	'id'         => 'npcshcd',
	'elType'     => 'widget',
	'settings'   => array(
		'shortcode' => '[saber_net_price_calculator]',
	),
	'elements'   => array(),
	'widgetType' => 'shortcode',
);
$embed_inner      = array(
	'id'              => 'npcembi',
	'elType'          => 'e-flexbox',
	'settings'        => array(
		'classes' => array(
			'$$type' => 'classes',
			'value'  => array( 'e-npc-embed-inner' ),
		),
	),
	'elements'        => array( $shortcode_widget ),
	'isInner'         => false,
	'styles'          => $styles(
		'e-npc-embed-inner',
		array(
			'width'          => $size( 100, '%' ),
			'max-width'      => $size( 1248 ),
			'flex-direction' => $typed( 'string', 'column' ),
			'padding'        => $dimensions(
				array(
					'block-start' => 72,
					'inline-end'  => 24,
					'block-end'   => 84,
					'inline-start' => 24,
				)
			),
		),
		array(
			'padding' => $dimensions(
				array(
					'block-start' => 42,
					'inline-end'  => 16,
					'block-end'   => 52,
					'inline-start' => 16,
				)
			),
		)
	),
	'interactions'    => array(),
	'editor_settings' => array(),
	'version'         => '0.0',
);
$embed_section    = array(
	'id'              => 'npcembd',
	'elType'          => 'e-flexbox',
	'settings'        => array(
		'classes' => array(
			'$$type' => 'classes',
			'value'  => array( 'e-npc-embed-section' ),
		),
	),
	'elements'        => array( $embed_inner ),
	'isInner'         => false,
	'styles'          => $styles(
		'e-npc-embed-section',
		array(
			'width'          => $size( 100, '%' ),
			'flex-direction' => $typed( 'string', 'column' ),
			'align-items'    => $typed( 'string', 'center' ),
			'background'     => $background( '#f5f9fd' ),
		)
	),
	'interactions'    => array(),
	'editor_settings' => array(),
	'version'         => '0.0',
);

if ( null === $embed_index ) {
	$data[] = $embed_section;
} else {
	$data[ $embed_index ] = $embed_section;
}

$redirect = \RankMath\Redirections\Redirection::from(
	array(
		'sources'     => array(
			array(
				'ignore'     => 'case',
				'pattern'    => 'Net-Price-Calculator.html',
				'comparison' => 'exact',
			),
		),
		'url_to'      => '/general/net-price-calculator/',
		'header_code' => 301,
		'status'      => 'active',
	)
);
$redirect_id = $redirect->save();

if ( ! $redirect_id ) {
	WP_CLI::error( 'Rank Math could not save the legacy redirect. Elementor was not changed.' );
}

$desired_json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
$updated      = update_post_meta( $page_id, '_elementor_data', wp_slash( $desired_json ) );

if ( $desired_json !== get_post_meta( $page_id, '_elementor_data', true ) ) {
	WP_CLI::error( 'Elementor data could not be updated.' );
}

update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
clean_post_cache( $page_id );

if ( class_exists( 'Elementor\\Plugin' ) ) {
	Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::line( 'BACKUP=' . $backup );
WP_CLI::line( 'PAGE=' . get_permalink( $page_id ) );
WP_CLI::line( 'SHORTCODE=[saber_net_price_calculator]' );
WP_CLI::line( 'REDIRECT_ID=' . $redirect_id );
WP_CLI::success( 'Calculator shortcode and Rank Math redirect were configured.' );
