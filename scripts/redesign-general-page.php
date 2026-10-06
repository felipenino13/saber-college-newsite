<?php
/**
 * Reorganize the existing /general/ Elementor widgets without rewriting copy.
 *
 * Run with: wp eval-file /var/www/html/scripts/redesign-general-page.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 36;
$raw = get_post_meta($post_id, '_elementor_data', true);
$data = json_decode($raw, true);

if (!is_array($data)) {
    WP_CLI::error('The Elementor data for page 36 is not valid JSON.');
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
    '056d01a', 'aeb90b9', '5ca85ba', '3394df8', '6674787',
    '5d4254b', '2a185ba', 'd4099c8', 'd7e9843',
    'fb42768', '5a9b428', '80184d4',
    '2238748', 'a3aefa2', '5c031cd',
    'c13076d', '74e014a', '4fe7b44', 'c6fa327',
    'd548f02', '31b8f0a', 'f721d77',
    '31a4b04', 'b4d82f5', '852d8c4',
    'ed2c0a6', 'f3cc63b', '167336b',
    '72a7afe', '0a406ff', '51ad417', '13c1cad',
    '58686cd', 'c33bbaf', 'd5230c7',
    '21c3234', 'e83d8dd', '056de7a',
];

foreach ($required as $id) {
    if (!isset($widgets[$id])) {
        WP_CLI::error("Required Elementor widget {$id} was not found; no changes were made.");
    }
}

$typed = static function (string $type, $value): array {
    return ['$$type' => $type, 'value' => $value];
};

$size = static function ($value, string $unit = 'px') use ($typed): array {
    return $typed('size', ['size' => $value, 'unit' => $unit]);
};

$dimensions = static function (array $values) use ($typed, $size): array {
    $sides = [];
    foreach ($values as $side => $value) {
        $sides[$side] = $size($value);
    }
    return $typed('dimensions', $sides);
};

$background = static function (string $color) use ($typed): array {
    return $typed('background', ['color' => $typed('color', $color)]);
};

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
    return [
        $class => [
            'id' => $class,
            'label' => 'local',
            'type' => 'class',
            'variants' => $variants,
        ],
    ];
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

$decorate_heading = static function (array $widget, int $size_px, string $color = '#082b55'): array {
    $widget['settings']['title_color'] = $color;
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_weight'] = '800';
    $widget['settings']['typography_font_size'] = [
        'unit' => 'px',
        'size' => $size_px,
        'sizes' => [],
    ];
    $mobile_size = $size_px >= 50 ? 42 : ($size_px >= 35 ? 32 : 25);
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

$decorate_text = static function (array $widget, string $color = '#29435f'): array {
    unset($widget['settings']['__globals__']['text_color']);
    $widget['settings']['text_color'] = $color;
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_size'] = [
        'unit' => 'px',
        'size' => 17,
        'sizes' => [],
    ];
    $widget['settings']['typography_line_height'] = [
        'unit' => 'em',
        'size' => 1.65,
        'sizes' => [],
    ];
    return $widget;
};

$decorate_button = static function (array $widget, string $url): array {
    unset($widget['settings']['__globals__']);
    $widget['settings']['link'] = [
        'url' => $url,
        'is_external' => '',
        'nofollow' => '',
        'custom_attributes' => '',
    ];
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_weight'] = '800';
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
        'right' => '24',
        'bottom' => '13',
        'left' => '24',
        'isLinked' => false,
    ];
    $widget['settings']['align'] = 'left';
    return $widget;
};

$widgets['aeb90b9'] = $decorate_heading($widgets['aeb90b9'], 54);
$widgets['5ca85ba'] = $decorate_text($widgets['5ca85ba']);
$widgets['3394df8'] = $decorate_heading($widgets['3394df8'], 38, '#ffffff');
$widgets['6674787'] = $decorate_text($widgets['6674787'], '#ffffff');

$card_specs = [
    ['5d4254b', ['2a185ba', 'd4099c8'], 'd7e9843', '/general/tuition-payment-options/'],
    ['fb42768', ['5a9b428'], '80184d4', '/general/explore-saber-college-gallery/'],
    ['2238748', ['a3aefa2'], '5c031cd', '/general/net-price-calculator/'],
    ['c13076d', ['74e014a', '4fe7b44'], 'c6fa327', '/general/manuals/'],
    ['d548f02', ['31b8f0a'], 'f721d77', '/general/graduation-requirements/'],
    ['31a4b04', ['b4d82f5'], '852d8c4', '/general/gainful-employment-programs/'],
    ['ed2c0a6', ['f3cc63b'], '167336b', '/general/saber-college-faculty/'],
    ['72a7afe', ['0a406ff', '51ad417'], '13c1cad', '/general/saber-college-distance-education/'],
    ['58686cd', ['c33bbaf'], 'd5230c7', '/general/saber_college_leadership/'],
    ['21c3234', ['e83d8dd'], '056de7a', '/general/safety-plan/'],
];

$cards = [];
foreach ($card_specs as $index => [$heading_id, $text_ids, $button_id, $url]) {
    $children = [$decorate_heading($widgets[$heading_id], 27)];
    foreach ($text_ids as $text_id) {
        $children[] = $decorate_text($widgets[$text_id]);
    }
    $children[] = $decorate_button($widgets[$button_id], $url);

    $card_class = 'e-g36card-' . ($index + 1);
    $card_color = in_array($index, [0, 3, 6, 9], true) ? '#fff9df' : '#ffffff';
    $cards[] = $container(
        'g36c' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
        $card_class,
        $children,
        [
            'width' => $size(48, '%'),
            'flex-direction' => $typed('string', 'column'),
            'justify-content' => $typed('string', 'flex-start'),
            'gap' => $size(16),
            'padding' => $dimensions([
                'block-start' => 34,
                'inline-end' => 34,
                'block-end' => 34,
                'inline-start' => 34,
            ]),
            'background' => $background($card_color),
            'border-radius' => $size(20),
        ],
        [
            'width' => $size(100, '%'),
            'padding' => $size(24),
        ]
    );
}

$hero_image = [
    'id' => 'g36img1',
    'elType' => 'widget',
    'settings' => [
        'image' => [
            'url' => 'http://localhost:8082/wp-content/uploads/2026/09/location-hero.webp',
            'id' => 1699,
            'size' => '',
            'alt' => 'SABER College campus',
            'source' => 'library',
        ],
        'image_size' => 'large',
        'border_radius' => [
            'unit' => 'px',
            'top' => '24',
            'right' => '24',
            'bottom' => '24',
            'left' => '24',
            'isLinked' => true,
        ],
    ],
    'elements' => [],
    'widgetType' => 'image',
];

$breadcrumbs = $container(
    'g36brd1',
    'e-g36-breadcrumbs',
    [$widgets['056d01a']],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'padding' => $dimensions([
            'block-start' => 18,
            'inline-end' => 24,
            'block-end' => 18,
            'inline-start' => 24,
        ]),
    ]
);

$hero_copy = $container(
    'g36hcpy',
    'e-g36-hero-copy',
    [$widgets['aeb90b9'], $widgets['5ca85ba']],
    [
        'width' => $size(56, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(18),
    ],
    ['width' => $size(100, '%')]
);

$hero_visual = $container(
    'g36hvis',
    'e-g36-hero-visual',
    [$hero_image],
    [
        'width' => $size(44, '%'),
        'flex-direction' => $typed('string', 'column'),
    ],
    ['width' => $size(100, '%')]
);

$hero_inner = $container(
    'g36hinr',
    'e-g36-hero-inner',
    [$hero_copy, $hero_visual],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(54),
        'padding' => $dimensions([
            'block-start' => 70,
            'inline-end' => 24,
            'block-end' => 76,
            'inline-start' => 24,
        ]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(30),
        'padding' => $dimensions([
            'block-start' => 44,
            'inline-end' => 22,
            'block-end' => 50,
            'inline-start' => 22,
        ]),
    ]
);

$hero = $container(
    'g36hero',
    'e-g36-hero',
    [$hero_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#edf6fb'),
    ]
);

$overview_heading = $container(
    'g36ovhd',
    'e-g36-overview-heading',
    [$widgets['3394df8']],
    [
        'width' => $size(28, '%'),
        'flex-direction' => $typed('string', 'column'),
    ],
    ['width' => $size(100, '%')]
);

$overview_copy = $container(
    'g36ovcp',
    'e-g36-overview-copy',
    [$widgets['6674787']],
    [
        'width' => $size(72, '%'),
        'flex-direction' => $typed('string', 'column'),
    ],
    ['width' => $size(100, '%')]
);

$overview_inner = $container(
    'g36ovin',
    'e-g36-overview-inner',
    [$overview_heading, $overview_copy],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(70),
        'padding' => $dimensions([
            'block-start' => 58,
            'inline-end' => 24,
            'block-end' => 58,
            'inline-start' => 24,
        ]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'flex-start'),
        'gap' => $size(18),
        'padding' => $dimensions([
            'block-start' => 42,
            'inline-end' => 22,
            'block-end' => 42,
            'inline-start' => 22,
        ]),
    ]
);

$overview = $container(
    'g36ovrw',
    'e-g36-overview',
    [$overview_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$resources_inner = $container(
    'g36grid',
    'e-g36-resource-grid',
    $cards,
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'flex-wrap' => $typed('string', 'wrap'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(24),
        'padding' => $dimensions([
            'block-start' => 64,
            'inline-end' => 24,
            'block-end' => 76,
            'inline-start' => 24,
        ]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'padding' => $dimensions([
            'block-start' => 42,
            'inline-end' => 18,
            'block-end' => 50,
            'inline-start' => 18,
        ]),
    ]
);

$resources = $container(
    'g36rsrc',
    'e-g36-resources',
    [$resources_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f3f7fa'),
    ]
);

$new_data = [$breadcrumbs, $hero, $overview, $resources];
$encoded = wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

update_post_meta($post_id, '_elementor_data', $encoded);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '4.2.2');
clean_post_cache($post_id);

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success('The /general/ page was redesigned with editable Elementor containers.');
