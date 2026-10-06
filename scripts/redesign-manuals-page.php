<?php
/**
 * Redesign /general/manuals/ with native Elementor containers.
 * Existing copy, button labels, and destinations are preserved.
 *
 * Run with: wp eval-file /tmp/redesign-manuals-page.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 913;
$raw = get_post_meta($post_id, '_elementor_data', true);
$data = json_decode($raw, true);

if (!is_array($data)) {
    WP_CLI::error('The Elementor data for page 913 is not valid JSON.');
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
    '813b6d4', '2324649', 'feae963', '91d9ef5', '4296053',
    'fbef416', 'cc49c4f', 'e2382f8',
    'ca6de3a', 'e26fbdf', '8cb99cb',
    '37279a0', 'cf5a425', '1703538',
    '245a4dd', 'a9b2211', 'b04a2f9',
    '68876de', '39815a8', '14705df',
    'b0ff0db', 'aa8b75e', 'a91a97e',
    '40efcc7', 'b29c2b7', '40c0c33',
    'bef317f', '5768b50', 'f7c93a5',
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

$decorate_heading = static function (array $widget, int $desktop_size, string $color = '#082b55'): array {
    $widget['settings']['title_color'] = $color;
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_weight'] = '800';
    $widget['settings']['typography_font_size'] = [
        'unit' => 'px',
        'size' => $desktop_size,
        'sizes' => [],
    ];
    $mobile_size = $desktop_size >= 50 ? 42 : ($desktop_size >= 35 ? 31 : 25);
    $widget['settings']['typography_font_size_mobile'] = [
        'unit' => 'px',
        'size' => $mobile_size,
        'sizes' => [],
    ];
    $widget['settings']['typography_line_height'] = [
        'unit' => 'em',
        'size' => 1.12,
        'sizes' => [],
    ];
    return $widget;
};

$decorate_text = static function (array $widget, string $color = '#29435f', int $font_size = 17): array {
    unset($widget['settings']['__globals__']['text_color']);
    $widget['settings']['text_color'] = $color;
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_size'] = [
        'unit' => 'px',
        'size' => $font_size,
        'sizes' => [],
    ];
    $widget['settings']['typography_line_height'] = [
        'unit' => 'em',
        'size' => 1.65,
        'sizes' => [],
    ];
    return $widget;
};

$decorate_button = static function (array $widget): array {
    unset($widget['settings']['__globals__']);
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_weight'] = '800';
    $widget['settings']['typography_font_size'] = [
        'unit' => 'px',
        'size' => 15,
        'sizes' => [],
    ];
    $widget['settings']['button_text_color'] = '#082b55';
    $widget['settings']['background_color'] = '#f7c900';
    $widget['settings']['hover_color'] = '#ffffff';
    $widget['settings']['button_background_hover_color'] = '#082b55';
    $widget['settings']['border_radius'] = [
        'unit' => 'px',
        'top' => '999',
        'right' => '999',
        'bottom' => '999',
        'left' => '999',
        'isLinked' => true,
    ];
    $widget['settings']['text_padding'] = [
        'unit' => 'px',
        'top' => '13',
        'right' => '22',
        'bottom' => '13',
        'left' => '22',
        'isLinked' => false,
    ];
    $widget['settings']['align'] = 'left';
    return $widget;
};

$widgets['2324649'] = $decorate_heading($widgets['2324649'], 56, '#ffffff');
$widgets['feae963'] = $decorate_heading($widgets['feae963'], 34, '#ffffff');
$widgets['91d9ef5'] = $decorate_text($widgets['91d9ef5']);
$widgets['4296053'] = $decorate_text($widgets['4296053']);

$cards_data = [
    ['fbef416', 'cc49c4f', 'e2382f8'],
    ['ca6de3a', 'e26fbdf', '8cb99cb'],
    ['37279a0', 'cf5a425', '1703538'],
    ['245a4dd', 'a9b2211', 'b04a2f9'],
    ['68876de', '39815a8', '14705df'],
    ['b0ff0db', 'aa8b75e', 'a91a97e'],
    ['40efcc7', 'b29c2b7', '40c0c33'],
    ['bef317f', '5768b50', 'f7c93a5'],
];

$cards = [];
foreach ($cards_data as $index => [$heading_id, $text_id, $button_id]) {
    $heading = $decorate_heading($widgets[$heading_id], 27);
    $text = $decorate_text($widgets[$text_id], '#29435f', 16);
    $button = $decorate_button($widgets[$button_id]);
    $card_color = in_array($index, [0, 3, 4, 7], true) ? '#fff8d8' : '#ffffff';
    $cards[] = $container(
        'mnlcard' . ($index + 1),
        'e-mnl-card-' . ($index + 1),
        [$heading, $text, $button],
        [
            'width' => $size(48, '%'),
            'flex-direction' => $typed('string', 'column'),
            'justify-content' => $typed('string', 'flex-start'),
            'gap' => $size(16),
            'padding' => $size(34),
            'background' => $background($card_color),
            'border-radius' => $size(20),
        ],
        [
            'width' => $size(100, '%'),
            'padding' => $size(24),
        ]
    );
}

$breadcrumbs_inner = $container(
    'mnlbrin',
    'e-mnl-breadcrumbs-inner',
    [$widgets['813b6d4']],
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
    'mnlbrdc',
    'e-mnl-breadcrumbs',
    [$breadcrumbs_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$hero_titles = $container(
    'mnlhtit',
    'e-mnl-hero-titles',
    [$widgets['2324649'], $widgets['feae963']],
    [
        'width' => $size(44, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(18),
    ],
    ['width' => $size(100, '%')]
);
$hero_copy = $container(
    'mnlhcpy',
    'e-mnl-hero-copy',
    [$widgets['91d9ef5'], $widgets['4296053']],
    [
        'width' => $size(56, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(14),
        'padding' => $size(36),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(24),
    ]
);
$hero_inner = $container(
    'mnlhrin',
    'e-mnl-hero-inner',
    [$hero_titles, $hero_copy],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(56),
        'padding' => $dimensions([
            'block-start' => 70,
            'inline-end' => 24,
            'block-end' => 70,
            'inline-start' => 24,
        ]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'flex-start'),
        'gap' => $size(28),
        'padding' => $dimensions([
            'block-start' => 46,
            'inline-end' => 20,
            'block-end' => 48,
            'inline-start' => 20,
        ]),
    ]
);
$hero = $container(
    'mnlhero',
    'e-mnl-hero',
    [$hero_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$grid_inner = $container(
    'mnlgrid',
    'e-mnl-grid-inner',
    $cards,
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'flex-wrap' => $typed('string', 'wrap'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(24),
        'padding' => $dimensions([
            'block-start' => 68,
            'inline-end' => 24,
            'block-end' => 78,
            'inline-start' => 24,
        ]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'padding' => $dimensions([
            'block-start' => 44,
            'inline-end' => 18,
            'block-end' => 52,
            'inline-start' => 18,
        ]),
    ]
);
$grid = $container(
    'mnldocs',
    'e-mnl-documents',
    [$grid_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f3f7fa'),
    ]
);

$new_data = [$breadcrumbs, $hero, $grid];
$encoded = wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

update_post_meta($post_id, '_elementor_data', $encoded);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '4.2.2');
clean_post_cache($post_id);

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success('The Manuals page was redesigned with native Elementor containers.');
