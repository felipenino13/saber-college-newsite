<?php
/**
 * Redesign /general/saber-college-faculty/ with native Elementor containers.
 * Existing copy, faculty names, credentials, and list order are preserved.
 *
 * Run with: wp eval-file /tmp/redesign-faculty-page.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 919;
$data = json_decode(get_post_meta($post_id, '_elementor_data', true), true);

if (!is_array($data)) {
    WP_CLI::error('The Elementor data for page 919 is not valid JSON.');
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
    '01b216b', 'e082322', 'a8ba405', '8917fbf', '9eeb6ec',
    'd69eecc', '859c2c0', '8010149',
    '8a61e34', '96c6c6f', 'f381bca',
    '3182130', 'a20a532', '1349a68',
    '481dcb9', '31d42ca', '7ed6b67', '20e9ff4',
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

$decorate_heading = static function (
    array $widget,
    int $desktop_size,
    string $color = '#082b55',
    string $tag = 'h2'
): array {
    $widget['settings']['header_size'] = $tag;
    $widget['settings']['title_color'] = $color;
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_weight'] = '800';
    $widget['settings']['typography_font_size'] = [
        'unit' => 'px',
        'size' => $desktop_size,
        'sizes' => [],
    ];
    $mobile_size = $desktop_size >= 50 ? 42 : ($desktop_size >= 38 ? 32 : 25);
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

$decorate_icon_list = static function (array $widget, string $text_color, string $icon_color): array {
    unset($widget['settings']['__globals__']);
    $widget['settings']['icon_color'] = $icon_color;
    $widget['settings']['text_color'] = $text_color;
    $widget['settings']['text_indent'] = [
        'unit' => 'px',
        'size' => 11,
        'sizes' => [],
    ];
    $widget['settings']['space_between'] = [
        'unit' => 'px',
        'size' => 15,
        'sizes' => [],
    ];
    $widget['settings']['icon_size'] = [
        'unit' => 'px',
        'size' => 17,
        'sizes' => [],
    ];
    $widget['settings']['text_typography_typography'] = 'custom';
    $widget['settings']['text_typography_font_family'] = 'Nunito';
    $widget['settings']['text_typography_font_size'] = [
        'unit' => 'px',
        'size' => 16,
        'sizes' => [],
    ];
    $widget['settings']['text_typography_line_height'] = [
        'unit' => 'em',
        'size' => 1.5,
        'sizes' => [],
    ];
    return $widget;
};

$widgets['e082322'] = $decorate_heading($widgets['e082322'], 58, '#ffffff', 'h1');
$widgets['a8ba405'] = $decorate_text($widgets['a8ba405'], '#29435f', 17);
$widgets['8917fbf'] = $decorate_text($widgets['8917fbf'], '#29435f', 17);
$widgets['9eeb6ec'] = $decorate_heading($widgets['9eeb6ec'], 44, '#082b55', 'h2');

$widgets['d69eecc'] = $decorate_heading($widgets['d69eecc'], 34, '#082b55', 'h3');
$widgets['8010149'] = $decorate_text($widgets['8010149'], '#29435f', 16);
$widgets['859c2c0'] = $decorate_icon_list($widgets['859c2c0'], '#ffffff', '#f7c900');

$widgets['8a61e34'] = $decorate_heading($widgets['8a61e34'], 34, '#082b55', 'h3');
$widgets['f381bca'] = $decorate_text($widgets['f381bca'], '#29435f', 16);
$widgets['96c6c6f'] = $decorate_icon_list($widgets['96c6c6f'], '#082b55', '#f7c900');

$widgets['3182130'] = $decorate_heading($widgets['3182130'], 34, '#ffffff', 'h3');
$widgets['1349a68'] = $decorate_text($widgets['1349a68'], '#ffffff', 16);
$widgets['a20a532'] = $decorate_icon_list($widgets['a20a532'], '#29435f', '#082b55');

$widgets['481dcb9'] = $decorate_heading($widgets['481dcb9'], 34, '#082b55', 'h3');
$widgets['7ed6b67'] = $decorate_text($widgets['7ed6b67'], '#29435f', 16);
$widgets['31d42ca'] = $decorate_icon_list($widgets['31d42ca'], '#082b55', '#f7c900');

$widgets['20e9ff4'] = $decorate_text($widgets['20e9ff4'], '#082b55', 18);

$breadcrumbs_inner = $container(
    'facbrin',
    'e-fac-breadcrumbs-inner',
    [$widgets['01b216b']],
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
    'facbred',
    'e-fac-breadcrumbs',
    [$breadcrumbs_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$hero_title = $container(
    'fachtit',
    'e-fac-hero-title',
    [$widgets['e082322']],
    [
        'width' => $size(42, '%'),
        'flex-direction' => $typed('string', 'column'),
    ],
    ['width' => $size(100, '%')]
);
$hero_copy = $container(
    'fachcpy',
    'e-fac-hero-copy',
    [$widgets['a8ba405'], $widgets['8917fbf']],
    [
        'width' => $size(58, '%'),
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
    'fachrin',
    'e-fac-hero-inner',
    [$hero_title, $hero_copy],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(56),
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
        'gap' => $size(28),
        'padding' => $dimensions([
            'block-start' => 46,
            'inline-end' => 20,
            'block-end' => 50,
            'inline-start' => 20,
        ]),
    ]
);
$hero = $container(
    'fachero',
    'e-fac-hero',
    [$hero_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$faculty_heading = $container(
    'facovhd',
    'e-fac-overview-heading',
    [$widgets['9eeb6ec']],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'align-items' => $typed('string', 'center'),
        'justify-content' => $typed('string', 'center'),
        'text-align' => $typed('string', 'center'),
        'padding' => $dimensions([
            'block-start' => 66,
            'inline-end' => 24,
            'block-end' => 34,
            'inline-start' => 24,
        ]),
    ],
    [
        'align-items' => $typed('string', 'flex-start'),
        'text-align' => $typed('string', 'start'),
        'padding' => $dimensions([
            'block-start' => 46,
            'inline-end' => 18,
            'block-end' => 26,
            'inline-start' => 18,
        ]),
    ]
);
$faculty_overview = $container(
    'facover',
    'e-fac-overview',
    [$faculty_heading],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f3f7fa'),
    ]
);

$nursing_intro = $container(
    'facnurti',
    'e-fac-nursing-intro',
    [$widgets['d69eecc'], $widgets['8010149']],
    [
        'width' => $size(38, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'gap' => $size(16),
        'padding' => $size(38),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(26),
    ]
);
$nursing_list = $container(
    'facnurlt',
    'e-fac-nursing-list',
    [$widgets['859c2c0']],
    [
        'width' => $size(62, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(38),
        'background' => $background('#082b55'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(26),
    ]
);
$nursing_inner = $container(
    'facnurin',
    'e-fac-nursing-inner',
    [$nursing_intro, $nursing_list],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(24),
        'padding' => $dimensions([
            'block-start' => 0,
            'inline-end' => 24,
            'block-end' => 76,
            'inline-start' => 24,
        ]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(18),
        'padding' => $dimensions([
            'block-start' => 0,
            'inline-end' => 18,
            'block-end' => 52,
            'inline-start' => 18,
        ]),
    ]
);
$nursing = $container(
    'facnurs',
    'e-fac-nursing',
    [$nursing_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f3f7fa'),
    ]
);

$pta_intro = $container(
    'facptain',
    'e-fac-pta-intro',
    [$widgets['8a61e34'], $widgets['f381bca']],
    [
        'width' => $size(48, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'gap' => $size(16),
    ],
    ['width' => $size(100, '%')]
);
$pta_list = $container(
    'facptalt',
    'e-fac-pta-list',
    [$widgets['96c6c6f']],
    [
        'width' => $size(52, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(34),
        'background' => $background('#ffffff'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(25),
    ]
);
$pta_inner = $container(
    'facptawr',
    'e-fac-pta-inner',
    [$pta_intro, $pta_list],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(48),
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
        'gap' => $size(26),
        'padding' => $dimensions([
            'block-start' => 50,
            'inline-end' => 18,
            'block-end' => 54,
            'inline-start' => 18,
        ]),
    ]
);
$pta = $container(
    'facptase',
    'e-fac-pta',
    [$pta_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f7c900'),
    ]
);

$general_intro = $container(
    'facgedin',
    'e-fac-general-intro',
    [$widgets['3182130'], $widgets['1349a68']],
    [
        'width' => $size(40, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'gap' => $size(16),
        'padding' => $size(38),
        'background' => $background('#082b55'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(27),
    ]
);
$general_list = $container(
    'facgedlt',
    'e-fac-general-list',
    [$widgets['a20a532']],
    [
        'width' => $size(60, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(36),
        'background' => $background('#ffffff'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(25),
    ]
);
$general_inner = $container(
    'facgedwr',
    'e-fac-general-inner',
    [$general_intro, $general_list],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(24),
        'padding' => $dimensions([
            'block-start' => 74,
            'inline-end' => 24,
            'block-end' => 74,
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
$general = $container(
    'facgedse',
    'e-fac-general',
    [$general_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f3f7fa'),
    ]
);

$vesl_intro = $container(
    'facvesin',
    'e-fac-vesl-intro',
    [$widgets['481dcb9'], $widgets['7ed6b67']],
    [
        'width' => $size(48, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'gap' => $size(16),
    ],
    ['width' => $size(100, '%')]
);
$vesl_list = $container(
    'facveslt',
    'e-fac-vesl-list',
    [$widgets['31d42ca']],
    [
        'width' => $size(52, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(34),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(25),
    ]
);
$vesl_inner = $container(
    'facveswr',
    'e-fac-vesl-inner',
    [$vesl_intro, $vesl_list],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(48),
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
        'gap' => $size(26),
        'padding' => $dimensions([
            'block-start' => 50,
            'inline-end' => 18,
            'block-end' => 54,
            'inline-start' => 18,
        ]),
    ]
);
$vesl = $container(
    'facvesls',
    'e-fac-vesl',
    [$vesl_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$closing_card = $container(
    'facclca',
    'e-fac-closing-card',
    [$widgets['20e9ff4']],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1050),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(38),
        'background' => $background('#f7c900'),
        'border-radius' => $size(20),
    ],
    ['padding' => $size(25)]
);
$closing_inner = $container(
    'facclin',
    'e-fac-closing-inner',
    [$closing_card],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'justify-content' => $typed('string', 'center'),
        'padding' => $dimensions([
            'block-start' => 68,
            'inline-end' => 24,
            'block-end' => 72,
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
$closing = $container(
    'facclose',
    'e-fac-closing',
    [$closing_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$new_data = [$breadcrumbs, $hero, $faculty_overview, $nursing, $pta, $general, $vesl, $closing];
$encoded = wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

update_post_meta($post_id, '_elementor_data', $encoded);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '4.2.2');
clean_post_cache($post_id);

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success('The Faculty page was redesigned with native Elementor containers.');
