<?php
/**
 * Redesign /general/net-price-calculator/ with native Elementor containers.
 * Existing editorial copy is reused without rewriting it.
 *
 * Run with: wp eval-file /tmp/redesign-net-price-calculator.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 911;
$raw = get_post_meta($post_id, '_elementor_data', true);
$data = json_decode($raw, true);

if (!is_array($data)) {
    WP_CLI::error('The Elementor data for page 911 is not valid JSON.');
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
    'af2d9dc', '39eb452', '03b1e12', 'a8a8ca9', 'b745575', 'd9f77af',
    'd6723d2', 'f1a93e7', '5bc4e84', 'a23e4b5', '43629b9', 'afcaebb',
    'aff13d8', 'c0064eb', 'b5c64be', 'c94ef21', '6d6c6af', '67998da',
    '6e43898', '7b2e977', 'd6cbb26', '4a1b9ba', '92bbda9', 'c6e7caf',
    '4f1a53b',
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
    'value' => ['color' => ['$' . '$type' => 'color', 'value' => $color]],
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
    $mobile_size = $desktop_size >= 50 ? 42 : ($desktop_size >= 36 ? 32 : 26);
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

$decorate_text = static function (array $widget, string $color = '#29435f', int $size_px = 17): array {
    unset($widget['settings']['__globals__']['text_color']);
    $widget['settings']['text_color'] = $color;
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_size'] = [
        'unit' => 'px',
        'size' => $size_px,
        'sizes' => [],
    ];
    $widget['settings']['typography_line_height'] = [
        'unit' => 'em',
        'size' => 1.65,
        'sizes' => [],
    ];
    return $widget;
};

$decorate_list = static function (array $widget): array {
    unset($widget['settings']['__globals__']);
    $widget['settings']['icon_color'] = '#f7c900';
    $widget['settings']['text_color'] = '#ffffff';
    $widget['settings']['text_indent'] = [
        'unit' => 'px',
        'size' => 12,
        'sizes' => [],
    ];
    $widget['settings']['space_between'] = [
        'unit' => 'px',
        'size' => 16,
        'sizes' => [],
    ];
    $widget['settings']['icon_size'] = [
        'unit' => 'px',
        'size' => 18,
        'sizes' => [],
    ];
    $widget['settings']['text_typography_typography'] = 'custom';
    $widget['settings']['text_typography_font_family'] = 'Nunito';
    $widget['settings']['text_typography_font_size'] = [
        'unit' => 'px',
        'size' => 18,
        'sizes' => [],
    ];
    $widget['settings']['text_typography_font_weight'] = '700';
    return $widget;
};

$extract_link = static function (string $html): array {
    preg_match('/href=["\']([^"\']+)["\']/', $html, $href_match);
    return [
        'url' => $href_match[1] ?? '',
        'is_external' => '',
        'nofollow' => '',
        'custom_attributes' => '',
    ];
};

$button_from_text = static function (array $widget) use ($extract_link): array {
    $editor = (string) ($widget['settings']['editor'] ?? '');
    $text = trim(wp_strip_all_tags($editor));
    $link = $extract_link($editor);
    return [
        'id' => $widget['id'],
        'elType' => 'widget',
        'settings' => [
            'text' => $text,
            'link' => $link,
            'align' => 'center',
            'typography_typography' => 'custom',
            'typography_font_family' => 'Nunito',
            'typography_font_weight' => '800',
            'button_text_color' => '#082b55',
            'background_color' => '#f7c900',
            'hover_color' => '#ffffff',
            'button_background_hover_color' => '#0e3b6d',
            'border_radius' => [
                'unit' => 'px',
                'top' => '999',
                'right' => '999',
                'bottom' => '999',
                'left' => '999',
                'isLinked' => true,
            ],
            'text_padding' => [
                'unit' => 'px',
                'top' => '15',
                'right' => '28',
                'bottom' => '15',
                'left' => '28',
                'isLinked' => false,
            ],
        ],
        'elements' => [],
        'widgetType' => 'button',
    ];
};

$image_widget = static function (
    string $id,
    int $attachment_id,
    string $url,
    string $alt,
    string $link_url = ''
): array {
    $settings = [
        'image' => [
            'url' => $url,
            'id' => $attachment_id,
            'size' => '',
            'alt' => $alt,
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
    ];
    if ($link_url !== '') {
        $settings['link_to'] = 'custom';
        $settings['link'] = [
            'url' => $link_url,
            'is_external' => '',
            'nofollow' => '',
            'custom_attributes' => '',
        ];
    }
    return [
        'id' => $id,
        'elType' => 'widget',
        'settings' => $settings,
        'elements' => [],
        'widgetType' => 'image',
    ];
};

$widgets['39eb452'] = $decorate_heading($widgets['39eb452'], 56);
foreach (['03b1e12', 'a8a8ca9', 'd9f77af', 'd6723d2', '5bc4e84', '43629b9', 'afcaebb', 'c0064eb', 'b5c64be', '6d6c6af', '67998da', '7b2e977', 'd6cbb26', '4a1b9ba', '92bbda9', 'c6e7caf'] as $id) {
    $widgets[$id] = $decorate_text($widgets[$id]);
}
foreach (['b745575', 'f1a93e7', 'aff13d8', 'c94ef21', '6e43898'] as $id) {
    $widgets[$id] = $decorate_heading($widgets[$id], 38);
}
$widgets['a23e4b5'] = $decorate_list($widgets['a23e4b5']);

$hero_image = $image_widget(
    'npcimg1',
    1026,
    'http://localhost:8082/wp-content/uploads/2026/08/how-to-pay-for-college.jpg',
    'Financial aid planning'
);

$calculator_source = $widgets['4f1a53b'];
if (($calculator_source['widgetType'] ?? '') === 'button') {
    $calculator_link = $calculator_source['settings']['link'] ?? [
        'url' => '',
        'is_external' => '',
        'nofollow' => '',
        'custom_attributes' => '',
    ];
    $calculator_button = $calculator_source;
} else {
    $calculator_link = $extract_link((string) ($calculator_source['settings']['editor'] ?? ''));
    $calculator_button = $button_from_text($calculator_source);
}
$calculator_image = $image_widget(
    'npcimg2',
    2063,
    'http://localhost:8082/wp-content/uploads/2026/10/net-price-calculator.png',
    'Net Price Calculator',
    $calculator_link['url']
);

$breadcrumbs_inner = $container(
    'npcbrin',
    'e-npc-breadcrumbs-inner',
    [$widgets['af2d9dc']],
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
    'npcbrdc',
    'e-npc-breadcrumbs',
    [$breadcrumbs_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$hero_copy = $container(
    'npchcpy',
    'e-npc-hero-copy',
    [$widgets['39eb452'], $widgets['03b1e12'], $widgets['a8a8ca9']],
    [
        'width' => $size(58, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(16),
    ],
    ['width' => $size(100, '%')]
);
$hero_visual = $container(
    'npchvis',
    'e-npc-hero-visual',
    [$hero_image],
    [
        'width' => $size(42, '%'),
        'flex-direction' => $typed('string', 'column'),
    ],
    ['width' => $size(100, '%')]
);
$hero_inner = $container(
    'npchrin',
    'e-npc-hero-inner',
    [$hero_copy, $hero_visual],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(56),
        'padding' => $dimensions([
            'block-start' => 68,
            'inline-end' => 24,
            'block-end' => 74,
            'inline-start' => 24,
        ]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(30),
        'padding' => $dimensions([
            'block-start' => 42,
            'inline-end' => 22,
            'block-end' => 48,
            'inline-start' => 22,
        ]),
    ]
);
$hero = $container(
    'npchero',
    'e-npc-hero',
    [$hero_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#edf6fb'),
    ]
);

$what_heading = $container(
    'npcwhd1',
    'e-npc-what-heading',
    [$widgets['b745575']],
    [
        'width' => $size(34, '%'),
        'flex-direction' => $typed('string', 'column'),
    ],
    ['width' => $size(100, '%')]
);
$what_copy = $container(
    'npcwcp1',
    'e-npc-what-copy',
    [$widgets['d9f77af'], $widgets['d6723d2']],
    [
        'width' => $size(66, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(12),
        'padding' => $size(34),
        'background' => $background('#f3f7fa'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(24),
    ]
);
$what_inner = $container(
    'npcwinr',
    'e-npc-what-inner',
    [$what_heading, $what_copy],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(54),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 72,
            'inline-start' => 24,
        ]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'flex-start'),
        'gap' => $size(24),
        'padding' => $dimensions([
            'block-start' => 46,
            'inline-end' => 20,
            'block-end' => 46,
            'inline-start' => 20,
        ]),
    ]
);
$what = $container(
    'npcwhat',
    'e-npc-what',
    [$what_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$widgets['f1a93e7'] = $decorate_heading($widgets['f1a93e7'], 42, '#ffffff');
$widgets['5bc4e84'] = $decorate_text($widgets['5bc4e84'], '#ffffff');
$why_left = $container(
    'npcwylf',
    'e-npc-why-left',
    [$widgets['f1a93e7'], $widgets['5bc4e84'], $widgets['a23e4b5']],
    [
        'width' => $size(48, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(18),
    ],
    ['width' => $size(100, '%')]
);
$why_right = $container(
    'npcwyrt',
    'e-npc-why-right',
    [$widgets['43629b9'], $widgets['afcaebb']],
    [
        'width' => $size(52, '%'),
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
$why_inner = $container(
    'npcwyin',
    'e-npc-why-inner',
    [$why_left, $why_right],
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
            'block-start' => 48,
            'inline-end' => 20,
            'block-end' => 48,
            'inline-start' => 20,
        ]),
    ]
);
$why = $container(
    'npcwhy1',
    'e-npc-why',
    [$why_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$how_heading = $container(
    'npchwhd',
    'e-npc-how-heading',
    [$widgets['aff13d8']],
    [
        'width' => $size(31, '%'),
        'flex-direction' => $typed('string', 'column'),
    ],
    ['width' => $size(100, '%')]
);
$how_copy = $container(
    'npchwcp',
    'e-npc-how-copy',
    [$widgets['c0064eb'], $widgets['b5c64be']],
    [
        'width' => $size(69, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(12),
    ],
    ['width' => $size(100, '%')]
);
$how_inner = $container(
    'npchwin',
    'e-npc-how-inner',
    [$how_heading, $how_copy],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(60),
        'padding' => $dimensions([
            'block-start' => 68,
            'inline-end' => 24,
            'block-end' => 68,
            'inline-start' => 24,
        ]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'flex-start'),
        'gap' => $size(22),
        'padding' => $dimensions([
            'block-start' => 46,
            'inline-end' => 20,
            'block-end' => 46,
            'inline-start' => 20,
        ]),
    ]
);
$how = $container(
    'npchow1',
    'e-npc-how',
    [$how_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#edf6fb'),
    ]
);

$help_copy = $container(
    'npchlp1',
    'e-npc-help-copy',
    [$widgets['c94ef21'], $widgets['6d6c6af']],
    [
        'width' => $size(61, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(14),
    ],
    ['width' => $size(100, '%')]
);
$help_contact = $container(
    'npcctct',
    'e-npc-help-contact',
    [$widgets['67998da']],
    [
        'width' => $size(39, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(34),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(24),
    ]
);
$help_inner = $container(
    'npchlpi',
    'e-npc-help-inner',
    [$help_copy, $help_contact],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(52),
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
        'gap' => $size(26),
        'padding' => $dimensions([
            'block-start' => 46,
            'inline-end' => 20,
            'block-end' => 46,
            'inline-start' => 20,
        ]),
    ]
);
$help = $container(
    'npchelp',
    'e-npc-help',
    [$help_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$legal_card = $container(
    'npclgcd',
    'e-npc-legal-card',
    [$widgets['6e43898'], $widgets['7b2e977'], $widgets['d6cbb26'], $widgets['4a1b9ba'], $widgets['92bbda9']],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1050),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(12),
        'padding' => $size(42),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(20),
    ],
    [
        'padding' => $size(24),
    ]
);
$legal_inner = $container(
    'npclgin',
    'e-npc-legal-inner',
    [$legal_card],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'padding' => $dimensions([
            'block-start' => 64,
            'inline-end' => 24,
            'block-end' => 64,
            'inline-start' => 24,
        ]),
    ],
    [
        'padding' => $dimensions([
            'block-start' => 42,
            'inline-end' => 18,
            'block-end' => 42,
            'inline-start' => 18,
        ]),
    ]
);
$legal = $container(
    'npclegal',
    'e-npc-legal',
    [$legal_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f3f7fa'),
    ]
);

$widgets['c6e7caf'] = $decorate_text($widgets['c6e7caf'], '#ffffff', 20);
$cta_copy = $container(
    'npcctcp',
    'e-npc-cta-copy',
    [$widgets['c6e7caf'], $calculator_button],
    [
        'width' => $size(42, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'flex-start'),
        'gap' => $size(22),
    ],
    [
        'width' => $size(100, '%'),
        'align-items' => $typed('string', 'center'),
    ]
);
$cta_visual = $container(
    'npcctvs',
    'e-npc-cta-visual',
    [$calculator_image],
    [
        'width' => $size(58, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'padding' => $size(28),
        'background' => $background('#ffffff'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(18),
    ]
);
$cta_inner = $container(
    'npcctin',
    'e-npc-cta-inner',
    [$cta_copy, $cta_visual],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1100),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(54),
        'padding' => $dimensions([
            'block-start' => 66,
            'inline-end' => 24,
            'block-end' => 66,
            'inline-start' => 24,
        ]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(28),
        'padding' => $dimensions([
            'block-start' => 46,
            'inline-end' => 20,
            'block-end' => 46,
            'inline-start' => 20,
        ]),
    ]
);
$cta = $container(
    'npccta1',
    'e-npc-cta',
    [$cta_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$new_data = [$breadcrumbs, $hero, $what, $why, $how, $help, $legal, $cta];
$encoded = wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

update_post_meta($post_id, '_elementor_data', $encoded);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '4.2.2');
clean_post_cache($post_id);

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success('The Net Price Calculator page was redesigned with native Elementor containers.');
