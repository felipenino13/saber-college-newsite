<?php
/**
 * Redesign /general/institutional-plans/ with native Elementor containers.
 * Existing plan labels, PDF links, and Instagram copy are preserved.
 *
 * Run with: wp eval-file /tmp/redesign-institutional-plans.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 925;
$data = json_decode(get_post_meta($post_id, '_elementor_data', true), true);

if (!is_array($data)) {
    WP_CLI::error('The Elementor data for page 925 is not valid JSON.');
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

$required = [
    '492d6a2', '8e4dd14', '79e9dcf', 'd95ae41', 'a0fc769',
    '676d319', 'f034f70', 'c9ce924', '27f5a1e', 'a3b5c28',
];

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
        'settings' => [
            'classes' => ['$$type' => 'classes', 'value' => [$class]],
        ],
        'elements' => $children,
        'isInner' => false,
        'styles' => $style($class, $desktop, $mobile),
        'interactions' => [],
        'editor_settings' => [],
        'version' => '0.0',
    ];
};

$decorate_heading = static function (array $widget): array {
    $widget['settings']['header_size'] = 'h1';
    $widget['settings']['title_color'] = '#ffffff';
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_weight'] = '800';
    $widget['settings']['typography_font_size'] = [
        'unit' => 'px',
        'size' => 58,
        'sizes' => [],
    ];
    $widget['settings']['typography_font_size_mobile'] = [
        'unit' => 'px',
        'size' => 42,
        'sizes' => [],
    ];
    $widget['settings']['typography_line_height'] = [
        'unit' => 'em',
        'size' => 1.12,
        'sizes' => [],
    ];
    return $widget;
};

$decorate_plan = static function (array $widget, string $color = '#082b55', int $font_size = 21): array {
    unset($widget['settings']['__globals__']);
    $widget['settings']['text_color'] = $color;
    $widget['settings']['link_color'] = $color;
    $widget['settings']['link_hover_color'] = '#0b4f91';
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_size'] = [
        'unit' => 'px',
        'size' => $font_size,
        'sizes' => [],
    ];
    $widget['settings']['typography_font_weight'] = '800';
    $widget['settings']['typography_line_height'] = [
        'unit' => 'em',
        'size' => 1.35,
        'sizes' => [],
    ];
    return $widget;
};

$widgets['8e4dd14'] = $decorate_heading($widgets['8e4dd14']);
$widgets['79e9dcf'] = $decorate_plan($widgets['79e9dcf'], '#082b55', 25);

$plan_ids = ['d95ae41', 'a0fc769', '676d319', 'f034f70', 'c9ce924', '27f5a1e'];
foreach ($plan_ids as $id) {
    $widgets[$id] = $decorate_plan($widgets[$id]);
}

$widgets['a3b5c28'] = $decorate_plan($widgets['a3b5c28'], '#082b55', 18);
$widgets['a3b5c28']['settings']['typography_font_weight'] = '700';

$breadcrumbs_inner = $container(
    'ipbrdin',
    'e-ip-breadcrumbs-inner',
    [$widgets['492d6a2']],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'justify-content' => $typed('string', 'center'),
        'align-items' => $typed('string', 'center'),
        'padding' => $dimensions([
            'block-start' => 18,
            'inline-end' => 24,
            'block-end' => 18,
            'inline-start' => 24,
        ]),
    ],
    [
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 16,
            'block-end' => 18,
            'inline-start' => 16,
        ]),
    ]
);
$breadcrumbs = $container(
    'ipbread',
    'e-ip-breadcrumbs',
    [$breadcrumbs_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$hero_title = $container(
    'iphtitl',
    'e-ip-hero-title',
    [$widgets['8e4dd14']],
    [
        'width' => $size(45, '%'),
        'flex-direction' => $typed('string', 'column'),
    ],
    ['width' => $size(100, '%')]
);
$featured_plan = $container(
    'ipfeatr',
    'e-ip-featured-plan',
    [$widgets['79e9dcf']],
    [
        'width' => $size(55, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'padding' => $size(38),
        'background' => $background('#f7c900'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(26),
    ]
);
$hero_inner = $container(
    'ipherin',
    'e-ip-hero-inner',
    [$hero_title, $featured_plan],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(52),
        'padding' => $dimensions([
            'block-start' => 82,
            'inline-end' => 24,
            'block-end' => 82,
            'inline-start' => 24,
        ]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'flex-start'),
        'gap' => $size(28),
        'padding' => $dimensions([
            'block-start' => 50,
            'inline-end' => 20,
            'block-end' => 52,
            'inline-start' => 20,
        ]),
    ]
);
$hero = $container(
    'iphero1',
    'e-ip-hero',
    [$hero_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$cards = [];
foreach ($plan_ids as $index => $id) {
    $cards[] = $container(
        'ipcard' . ($index + 1),
        'e-ip-plan-card-' . ($index + 1),
        [$widgets[$id]],
        [
            'width' => $size(31.8, '%'),
            'flex-direction' => $typed('string', 'column'),
            'justify-content' => $typed('string', 'center'),
            'padding' => $size(32),
            'background' => $background(in_array($index, [1, 4], true) ? '#fff8d8' : '#ffffff'),
            'border-radius' => $size(20),
        ],
        [
            'width' => $size(100, '%'),
            'padding' => $size(25),
        ]
    );
}

$grid_inner = $container(
    'ipgridi',
    'e-ip-grid-inner',
    $cards,
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'flex-wrap' => $typed('string', 'wrap'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(22),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 78,
            'inline-start' => 24,
        ]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(18),
        'padding' => $dimensions([
            'block-start' => 50,
            'inline-end' => 18,
            'block-end' => 54,
            'inline-start' => 18,
        ]),
    ]
);
$grid = $container(
    'ipgrid1',
    'e-ip-grid',
    [$grid_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f3f7fa'),
    ]
);

$instagram_card = $container(
    'ipinstc',
    'e-ip-instagram-card',
    [$widgets['a3b5c28']],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(920),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'text-align' => $typed('string', 'center'),
        'padding' => $size(30),
        'background' => $background('#f7c900'),
        'border-radius' => $size(18),
    ],
    [
        'align-items' => $typed('string', 'flex-start'),
        'text-align' => $typed('string', 'start'),
        'padding' => $size(24),
    ]
);
$instagram_inner = $container(
    'ipinsti',
    'e-ip-instagram-inner',
    [$instagram_card],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'justify-content' => $typed('string', 'center'),
        'padding' => $dimensions([
            'block-start' => 66,
            'inline-end' => 24,
            'block-end' => 70,
            'inline-start' => 24,
        ]),
    ],
    [
        'padding' => $dimensions([
            'block-start' => 48,
            'inline-end' => 18,
            'block-end' => 52,
            'inline-start' => 18,
        ]),
    ]
);
$instagram = $container(
    'ipinsta',
    'e-ip-instagram',
    [$instagram_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$new_data = [$breadcrumbs, $hero, $grid, $instagram];
$encoded = wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

update_post_meta($post_id, '_elementor_data', $encoded);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '4.2.2');
clean_post_cache($post_id);

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success('The Institutional Plans page was redesigned with native Elementor containers.');
