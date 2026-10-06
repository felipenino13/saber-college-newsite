<?php
/**
 * Redesign /45-day-report-on-ed-grants/ with native Elementor containers.
 * Existing text, report labels, links, and order are preserved verbatim.
 *
 * Run with: wp eval-file /tmp/redesign-cares-act.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 1628;
$data = json_decode(get_post_meta($post_id, '_elementor_data', true), true);

if (!is_array($data)) {
    WP_CLI::error('The Elementor data for page 1628 is not valid JSON.');
}

$widgets = [];
$collect_widgets = static function (array $nodes) use (&$collect_widgets, &$widgets): void {
    foreach ($nodes as $node) {
        if (($node['elType'] ?? '') === 'widget' && isset($node['id'])) {
            $widgets[$node['id']] = $node;
        }
        if (!empty($node['elements']) && is_array($node['elements'])) {
            $collect_widgets($node['elements']);
        }
    }
};
$collect_widgets($data);

$required = ['61abc84', '58edad2', '58d02ab', '916a971', 'd167879', 'e4dd2ff'];
foreach ($required as $id) {
    if (!isset($widgets[$id])) {
        WP_CLI::error("Required Elementor widget {$id} was not found; no changes were made.");
    }
}

$typed = static fn(string $type, $value): array => ['$$type' => $type, 'value' => $value];
$size = static fn($value, string $unit = 'px'): array => [
    '$$type' => 'size',
    'value' => ['size' => $value, 'unit' => $unit],
];
$dimensions = static function (array $values) use ($typed, $size): array {
    $sides = [];
    foreach ($values as $side => $value) {
        $sides[$side] = $size($value);
    }
    return $typed('dimensions', $sides);
};
$background = static fn(string $color): array => [
    '$$type' => 'background',
    'value' => ['color' => ['$$type' => 'color', 'value' => $color]],
];

$style = static function (string $class, array $desktop, array $mobile = []): array {
    $variants = [[
        'meta' => ['breakpoint' => 'desktop', 'state' => null],
        'props' => $desktop,
        'custom_css' => null,
    ]];
    if ($mobile !== []) {
        $variants[] = [
            'meta' => ['breakpoint' => 'mobile', 'state' => null],
            'props' => $mobile,
            'custom_css' => null,
        ];
    }
    return [$class => [
        'id' => $class,
        'label' => 'local',
        'type' => 'class',
        'variants' => $variants,
    ]];
};

$container = static function (
    string $id,
    string $class,
    array $children,
    array $desktop,
    array $mobile = []
) use ($style): array {
    return [
        'id' => $id,
        'elType' => 'e-flexbox',
        'settings' => ['classes' => ['$$type' => 'classes', 'value' => [$class]]],
        'elements' => $children,
        'isInner' => false,
        'styles' => $style($class, $desktop, $mobile),
        'interactions' => [],
        'editor_settings' => [],
        'version' => '0.0',
    ];
};

$decorate_heading = static function (array $widget, int $desktop_size, string $color, string $tag): array {
    $widget['settings']['header_size'] = $tag;
    $widget['settings']['title_color'] = $color;
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_weight'] = '800';
    $widget['settings']['typography_font_size'] = ['unit' => 'px', 'size' => $desktop_size, 'sizes' => []];
    $widget['settings']['typography_font_size_mobile'] = [
        'unit' => 'px',
        'size' => $desktop_size > 50 ? 42 : 30,
        'sizes' => [],
    ];
    $widget['settings']['typography_line_height'] = ['unit' => 'em', 'size' => 1.12, 'sizes' => []];
    return $widget;
};

$decorate_text = static function (array $widget, string $color, int $font_size, string $weight): array {
    unset($widget['settings']['__globals__']);
    $widget['settings']['text_color'] = $color;
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_size'] = ['unit' => 'px', 'size' => $font_size, 'sizes' => []];
    $widget['settings']['typography_font_size_mobile'] = ['unit' => 'px', 'size' => max(16, $font_size - 2), 'sizes' => []];
    $widget['settings']['typography_font_weight'] = $weight;
    $widget['settings']['typography_line_height'] = ['unit' => 'em', 'size' => 1.55, 'sizes' => []];
    return $widget;
};

$widgets['58edad2'] = $decorate_heading($widgets['58edad2'], 60, '#ffffff', 'h1');
$widgets['58d02ab'] = $decorate_text($widgets['58d02ab'], '#082b55', 22, '800');
$widgets['916a971'] = $decorate_heading($widgets['916a971'], 40, '#082b55', 'h2');
$widgets['d167879'] = $decorate_text($widgets['d167879'], '#ffffff', 18, '800');

unset($widgets['e4dd2ff']['settings']['__globals__']);
$widgets['e4dd2ff']['settings']['icon_color'] = '#f7c900';
$widgets['e4dd2ff']['settings']['icon_color_hover'] = '#082b55';
$widgets['e4dd2ff']['settings']['text_color'] = '#173653';
$widgets['e4dd2ff']['settings']['text_color_hover'] = '#0b4f91';
$widgets['e4dd2ff']['settings']['icon_size'] = ['unit' => 'px', 'size' => 20, 'sizes' => []];
$widgets['e4dd2ff']['settings']['text_indent'] = ['unit' => 'px', 'size' => 14, 'sizes' => []];
$widgets['e4dd2ff']['settings']['space_between'] = ['unit' => 'px', 'size' => 20, 'sizes' => []];
$widgets['e4dd2ff']['settings']['divider'] = 'yes';
$widgets['e4dd2ff']['settings']['divider_style'] = 'solid';
$widgets['e4dd2ff']['settings']['divider_color'] = '#dce5ec';
$widgets['e4dd2ff']['settings']['divider_weight'] = ['unit' => 'px', 'size' => 1, 'sizes' => []];
$widgets['e4dd2ff']['settings']['text_typography_typography'] = 'custom';
$widgets['e4dd2ff']['settings']['text_typography_font_family'] = 'Nunito';
$widgets['e4dd2ff']['settings']['text_typography_font_size'] = ['unit' => 'px', 'size' => 17, 'sizes' => []];
$widgets['e4dd2ff']['settings']['text_typography_font_size_mobile'] = ['unit' => 'px', 'size' => 16, 'sizes' => []];
$widgets['e4dd2ff']['settings']['text_typography_font_weight'] = '700';
$widgets['e4dd2ff']['settings']['text_typography_line_height'] = ['unit' => 'em', 'size' => 1.45, 'sizes' => []];

$breadcrumbs_inner = $container(
    'cabr-in',
    'e-ca-breadcrumbs-inner',
    [$widgets['61abc84']],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'justify-content' => $typed('string', 'center'),
        'align-items' => $typed('string', 'center'),
        'padding' => $dimensions(['block-start' => 18, 'inline-end' => 24, 'block-end' => 18, 'inline-start' => 24]),
    ],
    ['padding' => $dimensions(['block-start' => 72, 'inline-end' => 16, 'block-end' => 18, 'inline-start' => 16])]
);
$breadcrumbs = $container(
    'cabread',
    'e-ca-breadcrumbs',
    [$breadcrumbs_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$hero_title = $container(
    'cahtitl',
    'e-ca-hero-title',
    [$widgets['58edad2']],
    ['width' => $size(54, '%'), 'flex-direction' => $typed('string', 'column')],
    ['width' => $size(100, '%')]
);
$hero_label = $container(
    'cahlabl',
    'e-ca-hero-label',
    [$widgets['58d02ab']],
    [
        'width' => $size(46, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'padding' => $size(36),
        'background' => $background('#f7c900'),
        'border-radius' => $size(22),
    ],
    ['width' => $size(100, '%'), 'padding' => $size(26)]
);
$hero_inner = $container(
    'caherin',
    'e-ca-hero-inner',
    [$hero_title, $hero_label],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(54),
        'padding' => $dimensions(['block-start' => 82, 'inline-end' => 24, 'block-end' => 82, 'inline-start' => 24]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'flex-start'),
        'gap' => $size(28),
        'padding' => $dimensions(['block-start' => 50, 'inline-end' => 20, 'block-end' => 54, 'inline-start' => 20]),
    ]
);
$hero = $container(
    'cahero1',
    'e-ca-hero',
    [$hero_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$report_heading = $container(
    'carhead',
    'e-ca-report-heading',
    [$widgets['916a971']],
    ['width' => $size(100, '%'), 'flex-direction' => $typed('string', 'column')]
);
$report_label = $container(
    'carlabl',
    'e-ca-report-label',
    [$widgets['d167879']],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $dimensions(['block-start' => 16, 'inline-end' => 22, 'block-end' => 16, 'inline-start' => 22]),
        'background' => $background('#082b55'),
        'border-radius' => $size(12),
    ]
);
$report_intro = $container(
    'carntr0',
    'e-ca-report-intro',
    [$report_heading, $report_label],
    [
        'width' => $size(34, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'gap' => $size(28),
        'padding' => $size(42),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(22),
    ],
    ['width' => $size(100, '%'), 'padding' => $size(27)]
);
$report_list = $container(
    'carlst0',
    'e-ca-report-list',
    [$widgets['e4dd2ff']],
    [
        'width' => $size(66, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'padding' => $size(42),
        'background' => $background('#ffffff'),
        'border-radius' => $size(22),
    ],
    ['width' => $size(100, '%'), 'padding' => $size(26)]
);
$reports_inner = $container(
    'carepin',
    'e-ca-reports-inner',
    [$report_intro, $report_list],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(24),
        'padding' => $dimensions(['block-start' => 76, 'inline-end' => 24, 'block-end' => 82, 'inline-start' => 24]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(20),
        'padding' => $dimensions(['block-start' => 50, 'inline-end' => 18, 'block-end' => 54, 'inline-start' => 18]),
    ]
);
$reports = $container(
    'carepts',
    'e-ca-reports',
    [$reports_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f3f7fa'),
    ]
);

$new_data = [$breadcrumbs, $hero, $reports];
$encoded = wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

update_post_meta($post_id, '_elementor_data', $encoded);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '4.2.2');
clean_post_cache($post_id);

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success('The CARES Act page was redesigned with native Elementor containers.');
