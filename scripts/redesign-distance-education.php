<?php
/**
 * Redesign /general/saber-college-distance-education/ with native Elementor containers.
 * Existing copy and links are preserved without modification.
 *
 * Run with: wp eval-file /tmp/redesign-distance-education.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 921;
$data = json_decode(get_post_meta($post_id, '_elementor_data', true), true);

if (!is_array($data)) {
    WP_CLI::error('The Elementor data for page 921 is not valid JSON.');
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
    '02be7f3', 'a98d5f0', 'a36f8ea', '9ec9779', '860ae9c',
    'c3500ef', '8f06bbb', 'c2ebca9', '805783c',
    '6cdc4ce', '6ec9c9a', 'cc8ee6e',
    'dbdf57f', 'c9e9468', '03a0599', 'c649688',
    '5d67d18', '29b6e66', '6fb0f6c', '320da85', '755779b',
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

$widgets['a98d5f0'] = $decorate_heading($widgets['a98d5f0'], 56, '#ffffff', 'h1');
$widgets['a36f8ea'] = $decorate_text($widgets['a36f8ea'], '#29435f', 17);
$widgets['9ec9779'] = $decorate_text($widgets['9ec9779'], '#29435f', 17);
$widgets['860ae9c'] = $decorate_text($widgets['860ae9c'], '#082b55', 18);

$widgets['c3500ef'] = $decorate_heading($widgets['c3500ef'], 34, '#082b55', 'h2');
$widgets['8f06bbb'] = $decorate_text($widgets['8f06bbb'], '#29435f', 16);
$widgets['c2ebca9'] = $decorate_heading($widgets['c2ebca9'], 34, '#082b55', 'h2');
$widgets['805783c'] = $decorate_text($widgets['805783c'], '#29435f', 16);

$widgets['6cdc4ce'] = $decorate_heading($widgets['6cdc4ce'], 43, '#ffffff', 'h2');
$widgets['6ec9c9a'] = $decorate_text($widgets['6ec9c9a'], '#29435f', 17);
$widgets['cc8ee6e'] = $decorate_text($widgets['cc8ee6e'], '#29435f', 17);

$widgets['dbdf57f'] = $decorate_heading($widgets['dbdf57f'], 43, '#082b55', 'h2');
$widgets['c9e9468'] = $decorate_text($widgets['c9e9468'], '#29435f', 16);
$widgets['03a0599'] = $decorate_text($widgets['03a0599'], '#29435f', 16);
$widgets['c649688'] = $decorate_text($widgets['c649688'], '#29435f', 17);

$widgets['5d67d18'] = $decorate_heading($widgets['5d67d18'], 43, '#ffffff', 'h2');
$widgets['29b6e66'] = $decorate_text($widgets['29b6e66'], '#29435f', 16);
$widgets['6fb0f6c'] = $decorate_text($widgets['6fb0f6c'], '#29435f', 16);
$widgets['320da85'] = $decorate_text($widgets['320da85'], '#ffffff', 16);
$widgets['755779b'] = $decorate_text($widgets['755779b'], '#082b55', 18);

$breadcrumbs_inner = $container(
    'debrdin',
    'e-de-breadcrumbs-inner',
    [$widgets['02be7f3']],
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
    'debread',
    'e-de-breadcrumbs',
    [$breadcrumbs_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$hero_title = $container(
    'dehtitl',
    'e-de-hero-title',
    [$widgets['a98d5f0']],
    [
        'width' => $size(40, '%'),
        'flex-direction' => $typed('string', 'column'),
    ],
    ['width' => $size(100, '%')]
);
$hero_copy = $container(
    'dehcopy',
    'e-de-hero-copy',
    [$widgets['a36f8ea'], $widgets['9ec9779']],
    [
        'width' => $size(60, '%'),
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
$hero_top = $container(
    'dehertp',
    'e-de-hero-top',
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
$hero_callout = $container(
    'dehcall',
    'e-de-hero-callout',
    [$widgets['860ae9c']],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(26),
        'background' => $background('#f7c900'),
        'border-radius' => $size(18),
    ],
    ['padding' => $size(22)]
);
$hero_inner = $container(
    'deherin',
    'e-de-hero-inner',
    [$hero_top, $hero_callout],
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
    'dehero1',
    'e-de-hero',
    [$hero_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$vesl_card = $container(
    'deveslc',
    'e-de-vesl-card',
    [$widgets['c3500ef'], $widgets['8f06bbb']],
    [
        'width' => $size(50, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(16),
        'padding' => $size(36),
        'background' => $background('#ffffff'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(25),
    ]
);
$healthcare_card = $container(
    'dehealth',
    'e-de-healthcare-card',
    [$widgets['c2ebca9'], $widgets['805783c']],
    [
        'width' => $size(50, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(16),
        'padding' => $size(36),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(25),
    ]
);
$pathways_inner = $container(
    'depathin',
    'e-de-pathways-inner',
    [$vesl_card, $healthcare_card],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(24),
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
$pathways = $container(
    'depaths',
    'e-de-pathways',
    [$pathways_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f3f7fa'),
    ]
);

$learning_title = $container(
    'delearnt',
    'e-de-learning-title',
    [$widgets['6cdc4ce']],
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
$learning_copy = $container(
    'delearnc',
    'e-de-learning-copy',
    [$widgets['6ec9c9a'], $widgets['cc8ee6e']],
    [
        'width' => $size(60, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(14),
        'padding' => $size(40),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(26),
    ]
);
$learning_inner = $container(
    'delearnw',
    'e-de-learning-inner',
    [$learning_title, $learning_copy],
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
$learning = $container(
    'delearns',
    'e-de-learning',
    [$learning_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$support_card_one = $container(
    'desupc1',
    'e-de-support-card-one',
    [$widgets['c9e9468']],
    [
        'width' => $size(50, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(30),
        'background' => $background('#ffffff'),
        'border-radius' => $size(18),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(24),
    ]
);
$support_card_two = $container(
    'desupc2',
    'e-de-support-card-two',
    [$widgets['03a0599']],
    [
        'width' => $size(50, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(30),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(18),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(24),
    ]
);
$support_cards = $container(
    'desupcs',
    'e-de-support-cards',
    [$support_card_one, $support_card_two],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(22),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(16),
    ]
);
$support_closing = $container(
    'desupcl',
    'e-de-support-closing',
    [$widgets['c649688']],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(28),
        'background' => $background('#082b55'),
        'border-radius' => $size(18),
    ],
    ['padding' => $size(23)]
);
$widgets['c649688'] = $decorate_text($widgets['c649688'], '#ffffff', 17);
$support_closing['elements'] = [$widgets['c649688']];
$support_inner = $container(
    'desupin',
    'e-de-support-inner',
    [$widgets['dbdf57f'], $support_cards, $support_closing],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(28),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 78,
            'inline-start' => 24,
        ]),
    ],
    [
        'gap' => $size(22),
        'padding' => $dimensions([
            'block-start' => 50,
            'inline-end' => 18,
            'block-end' => 54,
            'inline-start' => 18,
        ]),
    ]
);
$support = $container(
    'desuppo',
    'e-de-support',
    [$support_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f7c900'),
    ]
);

$impact_card_one = $container(
    'deimpc1',
    'e-de-impact-card-one',
    [$widgets['29b6e66']],
    [
        'width' => $size(50, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(30),
        'background' => $background('#ffffff'),
        'border-radius' => $size(18),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(24),
    ]
);
$impact_card_two = $container(
    'deimpc2',
    'e-de-impact-card-two',
    [$widgets['6fb0f6c']],
    [
        'width' => $size(50, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(30),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(18),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(24),
    ]
);
$impact_cards = $container(
    'deimpcs',
    'e-de-impact-cards',
    [$impact_card_one, $impact_card_two],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(22),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(16),
    ]
);
$accreditation = $container(
    'deaccre',
    'e-de-accreditation',
    [$widgets['320da85']],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(28),
    ],
    ['padding' => $size(18)]
);
$impact_cta = $container(
    'deimpct',
    'e-de-impact-cta',
    [$widgets['755779b']],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'text-align' => $typed('string', 'center'),
        'padding' => $size(26),
        'background' => $background('#f7c900'),
        'border-radius' => $size(18),
    ],
    [
        'align-items' => $typed('string', 'flex-start'),
        'text-align' => $typed('string', 'start'),
        'padding' => $size(22),
    ]
);
$impact_inner = $container(
    'deimpwr',
    'e-de-impact-inner',
    [$widgets['5d67d18'], $impact_cards, $accreditation, $impact_cta],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(26),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 78,
            'inline-start' => 24,
        ]),
    ],
    [
        'gap' => $size(20),
        'padding' => $dimensions([
            'block-start' => 50,
            'inline-end' => 18,
            'block-end' => 54,
            'inline-start' => 18,
        ]),
    ]
);
$impact = $container(
    'deimpact',
    'e-de-impact',
    [$impact_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$new_data = [$breadcrumbs, $hero, $pathways, $learning, $support, $impact];
$encoded = wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

update_post_meta($post_id, '_elementor_data', $encoded);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '4.2.2');
clean_post_cache($post_id);

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success('The Distance Education page was redesigned with native Elementor containers.');
