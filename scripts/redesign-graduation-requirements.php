<?php
/**
 * Redesign /general/graduation-requirements/ with native Elementor containers.
 * Existing copy and links are preserved without modification.
 *
 * Run with: wp eval-file /tmp/redesign-graduation-requirements.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 915;
$raw = get_post_meta($post_id, '_elementor_data', true);
$data = json_decode($raw, true);

if (!is_array($data)) {
    WP_CLI::error('The Elementor data for page 915 is not valid JSON.');
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
    '33b853a', '2776387', '5efea74', 'bac7a6b',
    '414d320', '218ab10',
    'e5902e6', 'b421af9',
    '42721d9', '4618302',
    '9cbbf90', '56ad851',
    '232cbfe', '17c4bba', 'baa985f',
    '914b2be', 'eacde34', '95af1ae',
    '72290df', '54bfb20', 'c0f962d', '236bc69',
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

$widgets['2776387'] = $decorate_heading($widgets['2776387'], 58, '#ffffff', 'h1');
$widgets['5efea74'] = $decorate_text($widgets['5efea74'], '#29435f', 17);
$widgets['bac7a6b'] = $decorate_text($widgets['bac7a6b'], '#29435f', 17);

$widgets['414d320'] = $decorate_heading($widgets['414d320'], 44, '#082b55', 'h2');
$widgets['218ab10'] = $decorate_text($widgets['218ab10'], '#29435f', 18);

$requirements_data = [
    ['e5902e6', 'b421af9'],
    ['42721d9', '4618302'],
    ['9cbbf90', '56ad851'],
];
$requirement_cards = [];
foreach ($requirements_data as $index => [$heading_id, $text_id]) {
    $heading = $decorate_heading($widgets[$heading_id], 27, '#082b55', 'h3');
    $text = $decorate_text($widgets[$text_id], '#29435f', 16);
    $card_color = $index === 1 ? '#fff8d8' : '#ffffff';
    $requirement_cards[] = $container(
        'grcard' . ($index + 1),
        'e-gr-card-' . ($index + 1),
        [$heading, $text],
        [
            'width' => $size(31.5, '%'),
            'flex-direction' => $typed('string', 'column'),
            'justify-content' => $typed('string', 'flex-start'),
            'gap' => $size(16),
            'padding' => $size(32),
            'background' => $background($card_color),
            'border-radius' => $size(20),
        ],
        [
            'width' => $size(100, '%'),
            'padding' => $size(24),
        ]
    );
}

$widgets['232cbfe'] = $decorate_heading($widgets['232cbfe'], 42, '#ffffff', 'h2');
$widgets['17c4bba'] = $decorate_text($widgets['17c4bba'], '#29435f', 17);
$widgets['baa985f'] = $decorate_text($widgets['baa985f'], '#29435f', 17);

$widgets['914b2be'] = $decorate_heading($widgets['914b2be'], 42, '#082b55', 'h2');
$widgets['eacde34'] = $decorate_text($widgets['eacde34'], '#29435f', 17);
$widgets['95af1ae'] = $decorate_text($widgets['95af1ae'], '#29435f', 17);

$widgets['72290df'] = $decorate_heading($widgets['72290df'], 42, '#ffffff', 'h2');
$widgets['54bfb20'] = $decorate_text($widgets['54bfb20'], '#ffffff', 17);
$widgets['c0f962d'] = $decorate_text($widgets['c0f962d'], '#ffffff', 17);
$widgets['236bc69'] = $decorate_text($widgets['236bc69'], '#082b55', 17);

$breadcrumbs_inner = $container(
    'grbrin1',
    'e-gr-breadcrumbs-inner',
    [$widgets['33b853a']],
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
    'grbread',
    'e-gr-breadcrumbs',
    [$breadcrumbs_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$hero_title = $container(
    'grhtitl',
    'e-gr-hero-title',
    [$widgets['2776387']],
    [
        'width' => $size(43, '%'),
        'flex-direction' => $typed('string', 'column'),
    ],
    ['width' => $size(100, '%')]
);
$hero_copy = $container(
    'grhcopy',
    'e-gr-hero-copy',
    [$widgets['5efea74'], $widgets['bac7a6b']],
    [
        'width' => $size(57, '%'),
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
    'grherin',
    'e-gr-hero-inner',
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
    'grhero1',
    'e-gr-hero',
    [$hero_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$requirements_heading = $container(
    'grcoreh',
    'e-gr-core-heading',
    [$widgets['414d320'], $widgets['218ab10']],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(850),
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
$requirements_grid = $container(
    'grcards',
    'e-gr-requirements-grid',
    $requirement_cards,
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'row'),
        'flex-wrap' => $typed('string', 'wrap'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(22),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(18),
    ]
);
$requirements_inner = $container(
    'grcorin',
    'e-gr-core-inner',
    [$requirements_heading, $requirements_grid],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(38),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 82,
            'inline-start' => 24,
        ]),
    ],
    [
        'align-items' => $typed('string', 'flex-start'),
        'gap' => $size(28),
        'padding' => $dimensions([
            'block-start' => 48,
            'inline-end' => 18,
            'block-end' => 54,
            'inline-start' => 18,
        ]),
    ]
);
$requirements = $container(
    'grcore1',
    'e-gr-core',
    [$requirements_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f3f7fa'),
    ]
);

$expectations_title = $container(
    'grexptl',
    'e-gr-expectations-title',
    [$widgets['232cbfe']],
    [
        'width' => $size(38, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'padding' => $size(42),
        'background' => $background('#082b55'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(28),
    ]
);
$expectations_copy = $container(
    'grexpcp',
    'e-gr-expectations-copy',
    [$widgets['17c4bba'], $widgets['baa985f']],
    [
        'width' => $size(62, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'gap' => $size(14),
        'padding' => $size(42),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(26),
    ]
);
$expectations_inner = $container(
    'grexpin',
    'e-gr-expectations-inner',
    [$expectations_title, $expectations_copy],
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
            'block-end' => 50,
            'inline-start' => 18,
        ]),
    ]
);
$expectations = $container(
    'grexpec',
    'e-gr-expectations',
    [$expectations_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$readiness_copy_one = $container(
    'grrdyc1',
    'e-gr-readiness-copy-one',
    [$widgets['eacde34']],
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
$readiness_copy_two = $container(
    'grrdyc2',
    'e-gr-readiness-copy-two',
    [$widgets['95af1ae']],
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
$readiness_copies = $container(
    'grrdycp',
    'e-gr-readiness-copies',
    [$readiness_copy_one, $readiness_copy_two],
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
$readiness_inner = $container(
    'grrdyin',
    'e-gr-readiness-inner',
    [$widgets['914b2be'], $readiness_copies],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(30),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 78,
            'inline-start' => 24,
        ]),
    ],
    [
        'gap' => $size(24),
        'padding' => $dimensions([
            'block-start' => 50,
            'inline-end' => 18,
            'block-end' => 54,
            'inline-start' => 18,
        ]),
    ]
);
$readiness = $container(
    'grready',
    'e-gr-readiness',
    [$readiness_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f7c900'),
    ]
);

$next_copy = $container(
    'grnextc',
    'e-gr-next-copy',
    [$widgets['54bfb20'], $widgets['c0f962d']],
    [
        'width' => $size(62, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(14),
    ],
    ['width' => $size(100, '%')]
);
$next_callout = $container(
    'grnexto',
    'e-gr-next-callout',
    [$widgets['236bc69']],
    [
        'width' => $size(38, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'padding' => $size(32),
        'background' => $background('#f7c900'),
        'border-radius' => $size(18),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(24),
    ]
);
$next_body = $container(
    'grnextb',
    'e-gr-next-body',
    [$next_copy, $next_callout],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(32),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(24),
    ]
);
$next_inner = $container(
    'grnexti',
    'e-gr-next-inner',
    [$widgets['72290df'], $next_body],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(30),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 78,
            'inline-start' => 24,
        ]),
    ],
    [
        'gap' => $size(24),
        'padding' => $dimensions([
            'block-start' => 50,
            'inline-end' => 18,
            'block-end' => 54,
            'inline-start' => 18,
        ]),
    ]
);
$next_steps = $container(
    'grnexts',
    'e-gr-next-steps',
    [$next_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$new_data = [$breadcrumbs, $hero, $requirements, $expectations, $readiness, $next_steps];
$encoded = wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

update_post_meta($post_id, '_elementor_data', $encoded);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '4.2.2');
clean_post_cache($post_id);

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success('The Graduation Requirements page was redesigned with native Elementor containers.');
