<?php
/**
 * Redesign /general/heerf-quarterly-reports/ with native Elementor containers.
 * Existing copy, report labels, file links, and list order are preserved.
 *
 * Run with: wp eval-file /tmp/redesign-heerf-reports.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 923;
$data = json_decode(get_post_meta($post_id, '_elementor_data', true), true);

if (!is_array($data)) {
    WP_CLI::error('The Elementor data for page 923 is not valid JSON.');
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
    '9321147', '30258fc', '2805445', 'e0f6d67', '87dbc0d',
    '0d07af1', '176c6ee', '6d7ae8b', 'c8e5ca9', '2fb0a28', 'dbb9cd3', '7298355',
    '4b6ff83', 'db063c6', '2a9adf3', '7deb439', '39fa93c', '1c3d880',
    'ba36c0b', 'e9588a3', 'a4cc125', '131b862', '83b4ee0',
    '027efba', 'e529625', 'fab7b8b', '6c70e30',
    '2415f21', 'eeefe67', '49deeb7',
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
    $mobile_size = $desktop_size >= 50 ? 42 : ($desktop_size >= 38 ? 32 : 26);
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

$decorate_icon_list = static function (
    array $widget,
    string $text_color = '#29435f',
    string $icon_color = '#f7c900',
    int $font_size = 16
): array {
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
        'size' => $font_size,
        'sizes' => [],
    ];
    $widget['settings']['text_typography_line_height'] = [
        'unit' => 'em',
        'size' => 1.5,
        'sizes' => [],
    ];
    return $widget;
};

$widgets['30258fc'] = $decorate_heading($widgets['30258fc'], 58, '#ffffff', 'h1');
$widgets['e0f6d67'] = $decorate_heading($widgets['e0f6d67'], 34, '#082b55', 'h2');
$widgets['87dbc0d'] = $decorate_text($widgets['87dbc0d'], '#29435f', 17);
$widgets['2805445'] = $decorate_icon_list($widgets['2805445'], '#082b55', '#082b55', 17);

$widgets['0d07af1'] = $decorate_heading($widgets['0d07af1'], 43, '#082b55', 'h2');
$widgets['176c6ee'] = $decorate_text($widgets['176c6ee'], '#29435f', 17);
$widgets['6d7ae8b'] = $decorate_heading($widgets['6d7ae8b'], 42, '#ffffff', 'h2');
$widgets['c8e5ca9'] = $decorate_text($widgets['c8e5ca9'], '#29435f', 17);
$widgets['2fb0a28'] = $decorate_text($widgets['2fb0a28'], '#29435f', 17);
$widgets['dbb9cd3'] = $decorate_icon_list($widgets['dbb9cd3'], '#ffffff', '#f7c900', 16);
$widgets['7298355'] = $decorate_text($widgets['7298355'], '#29435f', 16);

$widgets['4b6ff83'] = $decorate_heading($widgets['4b6ff83'], 43, '#082b55', 'h2');
$widgets['db063c6'] = $decorate_text($widgets['db063c6'], '#29435f', 17);
$widgets['2a9adf3'] = $decorate_heading($widgets['2a9adf3'], 31, '#082b55', 'h3');
$widgets['7deb439'] = $decorate_text($widgets['7deb439'], '#29435f', 16);
$widgets['39fa93c'] = $decorate_text($widgets['39fa93c'], '#082b55', 16);
$widgets['1c3d880'] = $decorate_icon_list($widgets['1c3d880'], '#082b55', '#082b55', 16);
$widgets['ba36c0b'] = $decorate_heading($widgets['ba36c0b'], 31, '#ffffff', 'h3');
$widgets['e9588a3'] = $decorate_text($widgets['e9588a3'], '#ffffff', 16);
$widgets['a4cc125'] = $decorate_text($widgets['a4cc125'], '#ffffff', 16);
$widgets['131b862'] = $decorate_icon_list($widgets['131b862'], '#ffffff', '#f7c900', 16);
$widgets['83b4ee0'] = $decorate_text($widgets['83b4ee0'], '#ffffff', 16);

$widgets['027efba'] = $decorate_heading($widgets['027efba'], 42, '#082b55', 'h2');
$widgets['e529625'] = $decorate_text($widgets['e529625'], '#29435f', 17);
$widgets['fab7b8b'] = $decorate_icon_list($widgets['fab7b8b'], '#29435f', '#082b55', 16);
$widgets['6c70e30'] = $decorate_text($widgets['6c70e30'], '#29435f', 17);
$widgets['2415f21'] = $decorate_heading($widgets['2415f21'], 42, '#ffffff', 'h2');
$widgets['eeefe67'] = $decorate_text($widgets['eeefe67'], '#ffffff', 17);
$widgets['49deeb7'] = $decorate_icon_list($widgets['49deeb7'], '#082b55', '#f7c900', 16);

$breadcrumbs_inner = $container(
    'hrbrdin',
    'e-hr-breadcrumbs-inner',
    [$widgets['9321147']],
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
    'hrbread',
    'e-hr-breadcrumbs',
    [$breadcrumbs_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$hero_title = $container(
    'hrhtitl',
    'e-hr-hero-title',
    [$widgets['30258fc']],
    [
        'width' => $size(38, '%'),
        'flex-direction' => $typed('string', 'column'),
    ],
    ['width' => $size(100, '%')]
);
$hero_copy = $container(
    'hrhcopy',
    'e-hr-hero-copy',
    [$widgets['e0f6d67'], $widgets['87dbc0d']],
    [
        'width' => $size(62, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(16),
        'padding' => $size(36),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(24),
    ]
);
$hero_top = $container(
    'hrhertp',
    'e-hr-hero-top',
    [$hero_title, $hero_copy],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(52),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'flex-start'),
        'gap' => $size(28),
    ]
);
$featured_report = $container(
    'hrfeatr',
    'e-hr-featured-report',
    [$widgets['2805445']],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(25),
        'background' => $background('#f7c900'),
        'border-radius' => $size(18),
    ],
    ['padding' => $size(22)]
);
$hero_inner = $container(
    'hrherin',
    'e-hr-hero-inner',
    [$hero_top, $featured_report],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(28),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 72,
            'inline-start' => 24,
        ]),
    ],
    [
        'gap' => $size(22),
        'padding' => $dimensions([
            'block-start' => 46,
            'inline-end' => 20,
            'block-end' => 50,
            'inline-start' => 20,
        ]),
    ]
);
$hero = $container(
    'hrhero1',
    'e-hr-hero',
    [$hero_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$transparency_inner = $container(
    'hrtransi',
    'e-hr-transparency-inner',
    [$widgets['0d07af1'], $widgets['176c6ee']],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1050),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'text-align' => $typed('string', 'center'),
        'gap' => $size(18),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 72,
            'inline-start' => 24,
        ]),
    ],
    [
        'align-items' => $typed('string', 'flex-start'),
        'text-align' => $typed('string', 'start'),
        'padding' => $dimensions([
            'block-start' => 50,
            'inline-end' => 18,
            'block-end' => 54,
            'inline-start' => 18,
        ]),
    ]
);
$transparency = $container(
    'hrtransp',
    'e-hr-transparency',
    [$transparency_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$definition_title = $container(
    'hrdeftl',
    'e-hr-definition-title',
    [$widgets['6d7ae8b']],
    [
        'width' => $size(40, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'padding' => $size(40),
        'background' => $background('#082b55'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(28),
    ]
);
$definition_copy = $container(
    'hrdefcp',
    'e-hr-definition-copy',
    [$widgets['c8e5ca9'], $widgets['2fb0a28'], $widgets['7298355']],
    [
        'width' => $size(60, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(14),
        'padding' => $size(38),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(26),
    ]
);
$definition_list = $container(
    'hrdefls',
    'e-hr-definition-list',
    [$widgets['dbb9cd3']],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(30),
        'background' => $background('#082b55'),
        'border-radius' => $size(18),
    ],
    ['padding' => $size(24)]
);
$definition_inner = $container(
    'hrdefin',
    'e-hr-definition-inner',
    [$definition_title, $definition_copy, $definition_list],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'flex-wrap' => $typed('string', 'wrap'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(24),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 76,
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
$definition = $container(
    'hrdefwr',
    'e-hr-definition',
    [$definition_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f3f7fa'),
    ]
);

$access_heading = $container(
    'hracchd',
    'e-hr-access-heading',
    [$widgets['4b6ff83'], $widgets['db063c6']],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(900),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'text-align' => $typed('string', 'center'),
        'gap' => $size(14),
    ],
    [
        'align-items' => $typed('string', 'flex-start'),
        'text-align' => $typed('string', 'start'),
    ]
);
$progress_card = $container(
    'hrprogc',
    'e-hr-progress-card',
    [$widgets['2a9adf3'], $widgets['7deb439'], $widgets['39fa93c'], $widgets['1c3d880']],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(14),
        'padding' => $size(34),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(20),
    ],
    ['padding' => $size(25)]
);
$quarterly_intro = $container(
    'hrquint',
    'e-hr-quarterly-intro',
    [$widgets['ba36c0b'], $widgets['e9588a3'], $widgets['a4cc125']],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(14),
    ]
);
$quarterly_list = $container(
    'hrqulis',
    'e-hr-quarterly-list',
    [$widgets['131b862'], $widgets['83b4ee0']],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(16),
    ]
);
$quarterly_card = $container(
    'hrquarc',
    'e-hr-quarterly-card',
    [$quarterly_intro, $quarterly_list],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(24),
        'padding' => $size(38),
        'background' => $background('#082b55'),
        'border-radius' => $size(20),
    ],
    ['padding' => $size(26)]
);
$access_inner = $container(
    'hraccin',
    'e-hr-access-inner',
    [$access_heading, $progress_card, $quarterly_card],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(30),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 78,
            'inline-start' => 24,
        ]),
    ],
    [
        'align-items' => $typed('string', 'flex-start'),
        'gap' => $size(22),
        'padding' => $dimensions([
            'block-start' => 50,
            'inline-end' => 18,
            'block-end' => 54,
            'inline-start' => 18,
        ]),
    ]
);
$access = $container(
    'hraccess',
    'e-hr-access',
    [$access_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$importance_copy = $container(
    'hrimpcp',
    'e-hr-importance-copy',
    [$widgets['027efba'], $widgets['e529625'], $widgets['6c70e30']],
    [
        'width' => $size(46, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'gap' => $size(16),
    ],
    ['width' => $size(100, '%')]
);
$importance_list = $container(
    'hrimpls',
    'e-hr-importance-list',
    [$widgets['fab7b8b']],
    [
        'width' => $size(54, '%'),
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
$importance_inner = $container(
    'hrimpin',
    'e-hr-importance-inner',
    [$importance_copy, $importance_list],
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
$importance = $container(
    'hrimport',
    'e-hr-importance',
    [$importance_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f7c900'),
    ]
);

$resources_copy = $container(
    'hrrescp',
    'e-hr-resources-copy',
    [$widgets['2415f21'], $widgets['eeefe67']],
    [
        'width' => $size(44, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'gap' => $size(16),
    ],
    ['width' => $size(100, '%')]
);
$resources_list = $container(
    'hrresls',
    'e-hr-resources-list',
    [$widgets['49deeb7']],
    [
        'width' => $size(56, '%'),
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
$resources_inner = $container(
    'hrresin',
    'e-hr-resources-inner',
    [$resources_copy, $resources_list],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(48),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 78,
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
$resources = $container(
    'hrresou',
    'e-hr-resources',
    [$resources_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$new_data = [$breadcrumbs, $hero, $transparency, $definition, $access, $importance, $resources];
$encoded = wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

update_post_meta($post_id, '_elementor_data', $encoded);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '4.2.2');
clean_post_cache($post_id);

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success('The HEERF reports page was redesigned with native Elementor containers.');
