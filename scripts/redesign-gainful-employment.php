<?php
/**
 * Redesign /general/gainful-employment-programs/ with native Elementor containers.
 * Existing copy, list items, and links are preserved without modification.
 *
 * Run with: wp eval-file /tmp/redesign-gainful-employment.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 917;
$data = json_decode(get_post_meta($post_id, '_elementor_data', true), true);

if (!is_array($data)) {
    WP_CLI::error('The Elementor data for page 917 is not valid JSON.');
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
    '4059d19', '34a5245', '76881a0', '26afda1',
    '810cad7', '51d285a', '48cb375',
    '214d46d', '87c019c',
    '5a498ca', '7c1ea39', '8ea0714', 'ee6e528',
    'fd8ffd5', 'b3cd81d', '55943c1', '9d0f81e',
    '1da0cc7', '3705494', '6ccf731', '2d998c1',
    '09b155b', 'ad1b502', 'fe47019', 'd6bbc38', '9ddfbe0',
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

$decorate_icon_list = static function (array $widget): array {
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
        'size' => 18,
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
        'size' => 17,
        'sizes' => [],
    ];
    $widget['settings']['text_typography_font_weight'] = '700';
    return $widget;
};

$widgets['34a5245'] = $decorate_heading($widgets['34a5245'], 58, '#ffffff', 'h1');
$widgets['76881a0'] = $decorate_text($widgets['76881a0'], '#29435f', 17);
$widgets['26afda1'] = $decorate_text($widgets['26afda1'], '#29435f', 17);

$widgets['810cad7'] = $decorate_text($widgets['810cad7'], '#ffffff', 18);
$widgets['51d285a'] = $decorate_icon_list($widgets['51d285a']);
$widgets['48cb375'] = $decorate_text($widgets['48cb375'], '#082b55', 17);

$widgets['214d46d'] = $decorate_heading($widgets['214d46d'], 44, '#082b55', 'h2');
$widgets['87c019c'] = $decorate_text($widgets['87c019c'], '#29435f', 18);
$widgets['5a498ca'] = $decorate_heading($widgets['5a498ca'], 28, '#082b55', 'h3');
$widgets['7c1ea39'] = $decorate_text($widgets['7c1ea39'], '#29435f', 16);
$widgets['8ea0714'] = $decorate_heading($widgets['8ea0714'], 28, '#082b55', 'h3');
$widgets['ee6e528'] = $decorate_text($widgets['ee6e528'], '#29435f', 16);

$widgets['fd8ffd5'] = $decorate_heading($widgets['fd8ffd5'], 43, '#ffffff', 'h2');
$widgets['b3cd81d'] = $decorate_text($widgets['b3cd81d'], '#082b55', 17);
$widgets['55943c1'] = $decorate_text($widgets['55943c1'], '#29435f', 17);
$widgets['9d0f81e'] = $decorate_text($widgets['9d0f81e'], '#29435f', 16);

$widgets['1da0cc7'] = $decorate_heading($widgets['1da0cc7'], 42, '#082b55', 'h2');
$widgets['3705494'] = $decorate_text($widgets['3705494'], '#29435f', 17);
$widgets['6ccf731'] = $decorate_text($widgets['6ccf731'], '#29435f', 16);
$widgets['6ccf731']['settings']['editor'] = str_replace(
    '<ul>',
    '<ul style="overflow-wrap:anywhere;word-break:break-word;">',
    $widgets['6ccf731']['settings']['editor']
);
$widgets['2d998c1'] = $decorate_text($widgets['2d998c1'], '#29435f', 17);

$widgets['09b155b'] = $decorate_heading($widgets['09b155b'], 42, '#ffffff', 'h2');
$widgets['ad1b502'] = $decorate_text($widgets['ad1b502'], '#ffffff', 17);
$widgets['fe47019'] = $decorate_text($widgets['fe47019'], '#29435f', 16);
$widgets['d6bbc38'] = $decorate_text($widgets['d6bbc38'], '#29435f', 16);
$widgets['9ddfbe0'] = $decorate_text($widgets['9ddfbe0'], '#082b55', 18);

$breadcrumbs_inner = $container(
    'gebrin1',
    'e-ge-breadcrumbs-inner',
    [$widgets['4059d19']],
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
    'gebread',
    'e-ge-breadcrumbs',
    [$breadcrumbs_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$hero_title = $container(
    'gehtitl',
    'e-ge-hero-title',
    [$widgets['34a5245']],
    [
        'width' => $size(43, '%'),
        'flex-direction' => $typed('string', 'column'),
    ],
    ['width' => $size(100, '%')]
);
$hero_copy = $container(
    'gehcopy',
    'e-ge-hero-copy',
    [$widgets['76881a0'], $widgets['26afda1']],
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
    'geherin',
    'e-ge-hero-inner',
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
    'gehero1',
    'e-ge-hero',
    [$hero_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$questions_main = $container(
    'geqmain',
    'e-ge-questions-main',
    [$widgets['810cad7'], $widgets['51d285a']],
    [
        'width' => $size(66, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(24),
        'padding' => $size(42),
        'background' => $background('#082b55'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(26),
    ]
);
$questions_callout = $container(
    'geqcall',
    'e-ge-questions-callout',
    [$widgets['48cb375']],
    [
        'width' => $size(34, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'padding' => $size(34),
        'background' => $background('#f7c900'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(26),
    ]
);
$questions_inner = $container(
    'gequest',
    'e-ge-questions-inner',
    [$questions_main, $questions_callout],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(24),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 72,
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
$questions = $container(
    'geqwrap',
    'e-ge-questions',
    [$questions_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$programs_heading = $container(
    'geprogh',
    'e-ge-programs-heading',
    [$widgets['214d46d'], $widgets['87c019c']],
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
$pta_card = $container(
    'geptacd',
    'e-ge-program-pta',
    [$widgets['5a498ca'], $widgets['7c1ea39']],
    [
        'width' => $size(50, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(16),
        'padding' => $size(34),
        'background' => $background('#ffffff'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(24),
    ]
);
$pnp_card = $container(
    'gepnpca',
    'e-ge-program-pnp',
    [$widgets['8ea0714'], $widgets['ee6e528']],
    [
        'width' => $size(50, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(16),
        'padding' => $size(34),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(24),
    ]
);
$program_cards = $container(
    'geprogc',
    'e-ge-program-cards',
    [$pta_card, $pnp_card],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(24),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(18),
    ]
);
$programs_inner = $container(
    'geprogi',
    'e-ge-programs-inner',
    [$programs_heading, $program_cards],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'gap' => $size(38),
        'padding' => $dimensions([
            'block-start' => 72,
            'inline-end' => 24,
            'block-end' => 80,
            'inline-start' => 24,
        ]),
    ],
    [
        'align-items' => $typed('string', 'flex-start'),
        'gap' => $size(28),
        'padding' => $dimensions([
            'block-start' => 50,
            'inline-end' => 18,
            'block-end' => 54,
            'inline-start' => 18,
        ]),
    ]
);
$programs = $container(
    'geprogs',
    'e-ge-programs',
    [$programs_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f3f7fa'),
    ]
);

$disclosure_title = $container(
    'gedisti',
    'e-ge-disclosure-title',
    [$widgets['fd8ffd5']],
    [
        'width' => $size(42, '%'),
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
$disclosure_intro = $container(
    'gedisin',
    'e-ge-disclosure-intro',
    [$widgets['b3cd81d'], $widgets['55943c1']],
    [
        'width' => $size(58, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'gap' => $size(14),
        'padding' => $size(38),
        'background' => $background('#f7c900'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(26),
    ]
);
$disclosure_top = $container(
    'gedistp',
    'e-ge-disclosure-top',
    [$disclosure_title, $disclosure_intro],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(24),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(18),
    ]
);
$disclosure_details = $container(
    'gedisdt',
    'e-ge-disclosure-details',
    [$widgets['9d0f81e']],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $size(40),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(20),
    ],
    ['padding' => $size(25)]
);
$disclosure_inner = $container(
    'gediscl',
    'e-ge-disclosure-inner',
    [$disclosure_top, $disclosure_details],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(24),
        'padding' => $dimensions([
            'block-start' => 74,
            'inline-end' => 24,
            'block-end' => 78,
            'inline-start' => 24,
        ]),
    ],
    [
        'gap' => $size(18),
        'padding' => $dimensions([
            'block-start' => 50,
            'inline-end' => 18,
            'block-end' => 54,
            'inline-start' => 18,
        ]),
    ]
);
$disclosure = $container(
    'gediswr',
    'e-ge-disclosure',
    [$disclosure_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$career_heading = $container(
    'gecarti',
    'e-ge-career-heading',
    [$widgets['1da0cc7'], $widgets['3705494']],
    [
        'width' => $size(40, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'gap' => $size(16),
    ],
    ['width' => $size(100, '%')]
);
$career_resources = $container(
    'gecarre',
    'e-ge-career-resources',
    [$widgets['6ccf731'], $widgets['2d998c1']],
    [
        'width' => $size(60, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(14),
        'padding' => $size(38),
        'background' => $background('#ffffff'),
        'border-radius' => $size(20),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(25),
    ]
);
$career_inner = $container(
    'gecarin',
    'e-ge-career-inner',
    [$career_heading, $career_resources],
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
$career = $container(
    'gecaree',
    'e-ge-career',
    [$career_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f7c900'),
    ]
);

$commitment_intro = $container(
    'gecomin',
    'e-ge-commitment-intro',
    [$widgets['09b155b'], $widgets['ad1b502']],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(950),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(18),
    ]
);
$commitment_card_one = $container(
    'gecomc1',
    'e-ge-commitment-card-one',
    [$widgets['fe47019']],
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
$commitment_card_two = $container(
    'gecomc2',
    'e-ge-commitment-card-two',
    [$widgets['d6bbc38']],
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
$commitment_cards = $container(
    'gecomcs',
    'e-ge-commitment-cards',
    [$commitment_card_one, $commitment_card_two],
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
$commitment_contact = $container(
    'gecomct',
    'e-ge-commitment-contact',
    [$widgets['9ddfbe0']],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'text-align' => $typed('string', 'center'),
        'padding' => $size(25),
        'background' => $background('#f7c900'),
        'border-radius' => $size(18),
    ],
    [
        'align-items' => $typed('string', 'flex-start'),
        'text-align' => $typed('string', 'start'),
        'padding' => $size(22),
    ]
);
$commitment_inner = $container(
    'gecomwr',
    'e-ge-commitment-inner',
    [$commitment_intro, $commitment_cards, $commitment_contact],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(28),
        'padding' => $dimensions([
            'block-start' => 74,
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
$commitment = $container(
    'gecommi',
    'e-ge-commitment',
    [$commitment_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$new_data = [$breadcrumbs, $hero, $questions, $programs, $disclosure, $career, $commitment];
$encoded = wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

update_post_meta($post_id, '_elementor_data', $encoded);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '4.2.2');
clean_post_cache($post_id);

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success('The Gainful Employment page was redesigned with native Elementor containers.');
