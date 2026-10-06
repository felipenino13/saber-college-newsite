<?php
/**
 * Redesign /general/safety-plan/ with native Elementor containers.
 * All existing copy and the document link are preserved verbatim.
 *
 * Run with: wp eval-file /tmp/redesign-safety-plan.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 1633;
$data = json_decode(get_post_meta($post_id, '_elementor_data', true), true);

if (!is_array($data)) {
    WP_CLI::error('The Elementor data for page 1633 is not valid JSON.');
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
    'f5cbd16', '4956142', 'c74da61', '2890ac4',
    '931a458', 'adf5c6d', 'f02f968', 'e424848',
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

$widgets['4956142']['settings']['header_size'] = 'h1';
$widgets['4956142']['settings']['title_color'] = '#ffffff';
$widgets['4956142']['settings']['typography_typography'] = 'custom';
$widgets['4956142']['settings']['typography_font_family'] = 'Nunito';
$widgets['4956142']['settings']['typography_font_weight'] = '800';
$widgets['4956142']['settings']['typography_font_size'] = ['unit' => 'px', 'size' => 58, 'sizes' => []];
$widgets['4956142']['settings']['typography_font_size_mobile'] = ['unit' => 'px', 'size' => 40, 'sizes' => []];
$widgets['4956142']['settings']['typography_line_height'] = ['unit' => 'em', 'size' => 1.1, 'sizes' => []];

$widgets['c74da61']['settings']['text_color'] = '#082b55';
$widgets['c74da61']['settings']['typography_typography'] = 'custom';
$widgets['c74da61']['settings']['typography_font_family'] = 'Nunito';
$widgets['c74da61']['settings']['typography_font_size'] = ['unit' => 'px', 'size' => 23, 'sizes' => []];
$widgets['c74da61']['settings']['typography_font_weight'] = '800';
$widgets['c74da61']['settings']['typography_line_height'] = ['unit' => 'em', 'size' => 1.35, 'sizes' => []];
unset($widgets['c74da61']['settings']['__globals__']);

$widgets['2890ac4']['settings']['text_color'] = '#ffffff';
$widgets['2890ac4']['settings']['icon_color'] = '#f7c900';
$widgets['2890ac4']['settings']['icon_color_hover'] = '#ffffff';
$widgets['2890ac4']['settings']['text_color_hover'] = '#ffffff';
$widgets['2890ac4']['settings']['typography_typography'] = 'custom';
$widgets['2890ac4']['settings']['typography_font_family'] = 'Nunito';
$widgets['2890ac4']['settings']['typography_font_size'] = ['unit' => 'px', 'size' => 17, 'sizes' => []];
$widgets['2890ac4']['settings']['typography_font_weight'] = '800';
unset($widgets['2890ac4']['settings']['__globals__']);

$paragraph_ids = ['931a458', 'adf5c6d', 'f02f968', 'e424848'];
foreach ($paragraph_ids as $id) {
    $widgets[$id]['settings']['text_color'] = '#173653';
    $widgets[$id]['settings']['typography_typography'] = 'custom';
    $widgets[$id]['settings']['typography_font_family'] = 'Nunito';
    $widgets[$id]['settings']['typography_font_size'] = ['unit' => 'px', 'size' => 18, 'sizes' => []];
    $widgets[$id]['settings']['typography_font_size_mobile'] = ['unit' => 'px', 'size' => 16, 'sizes' => []];
    $widgets[$id]['settings']['typography_font_weight'] = '500';
    $widgets[$id]['settings']['typography_line_height'] = ['unit' => 'em', 'size' => 1.75, 'sizes' => []];
    unset($widgets[$id]['settings']['__globals__']);
}
$widgets['e424848']['settings']['text_color'] = '#082b55';
$widgets['e424848']['settings']['typography_font_size'] = ['unit' => 'px', 'size' => 21, 'sizes' => []];
$widgets['e424848']['settings']['typography_font_weight'] = '800';

$breadcrumbs_inner = $container(
    'spbrdin',
    'e-sp-breadcrumbs-inner',
    [$widgets['f5cbd16']],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'justify-content' => $typed('string', 'center'),
        'align-items' => $typed('string', 'center'),
        'padding' => $dimensions(['block-start' => 18, 'inline-end' => 24, 'block-end' => 18, 'inline-start' => 24]),
    ],
    [
        'padding' => $dimensions(['block-start' => 72, 'inline-end' => 16, 'block-end' => 18, 'inline-start' => 16]),
    ]
);
$breadcrumbs = $container(
    'spbread',
    'e-sp-breadcrumbs',
    [$breadcrumbs_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#ffffff'),
    ]
);

$hero_title = $container(
    'sphtitl',
    'e-sp-hero-title',
    [$widgets['4956142']],
    [
        'width' => $size(48, '%'),
        'flex-direction' => $typed('string', 'column'),
    ],
    ['width' => $size(100, '%')]
);
$download_button = $container(
    'spdlbtn',
    'e-sp-download-button',
    [$widgets['2890ac4']],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'padding' => $dimensions(['block-start' => 15, 'inline-end' => 18, 'block-end' => 15, 'inline-start' => 18]),
        'background' => $background('#082b55'),
        'border-radius' => $size(12),
    ]
);
$document_card = $container(
    'spdoccr',
    'e-sp-document-card',
    [$widgets['c74da61'], $download_button],
    [
        'width' => $size(52, '%'),
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(22),
        'padding' => $size(38),
        'background' => $background('#f7c900'),
        'border-radius' => $size(22),
    ],
    [
        'width' => $size(100, '%'),
        'padding' => $size(26),
    ]
);
$hero_inner = $container(
    'spherin',
    'e-sp-hero-inner',
    [$hero_title, $document_card],
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
        'gap' => $size(30),
        'padding' => $dimensions(['block-start' => 50, 'inline-end' => 20, 'block-end' => 54, 'inline-start' => 20]),
    ]
);
$hero = $container(
    'sphero1',
    'e-sp-hero',
    [$hero_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$overview_card = $container(
    'spovwcd',
    'e-sp-overview-card',
    [$widgets['931a458']],
    [
        'width' => $size(55, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'padding' => $size(42),
        'background' => $background('#ffffff'),
        'border-radius' => $size(22),
    ],
    ['width' => $size(100, '%'), 'padding' => $size(26)]
);
$protocol_card = $container(
    'spprccd',
    'e-sp-protocol-card',
    [$widgets['adf5c6d']],
    [
        'width' => $size(45, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'padding' => $size(42),
        'background' => $background('#fff8d8'),
        'border-radius' => $size(22),
    ],
    ['width' => $size(100, '%'), 'padding' => $size(26)]
);
$content_inner = $container(
    'spcntin',
    'e-sp-content-inner',
    [$overview_card, $protocol_card],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(24),
        'padding' => $dimensions(['block-start' => 74, 'inline-end' => 24, 'block-end' => 74, 'inline-start' => 24]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(20),
        'padding' => $dimensions(['block-start' => 50, 'inline-end' => 18, 'block-end' => 52, 'inline-start' => 18]),
    ]
);
$content = $container(
    'spcont1',
    'e-sp-content',
    [$content_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#f3f7fa'),
    ]
);

$details_card = $container(
    'spdetcd',
    'e-sp-details-card',
    [$widgets['f02f968']],
    [
        'width' => $size(64, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'padding' => $size(42),
        'background' => $background('#ffffff'),
        'border-radius' => $size(22),
    ],
    ['width' => $size(100, '%'), 'padding' => $size(26)]
);
$invitation_card = $container(
    'spinvcd',
    'e-sp-invitation-card',
    [$widgets['e424848']],
    [
        'width' => $size(36, '%'),
        'flex-direction' => $typed('string', 'column'),
        'justify-content' => $typed('string', 'center'),
        'padding' => $size(40),
        'background' => $background('#f7c900'),
        'border-radius' => $size(22),
    ],
    ['width' => $size(100, '%'), 'padding' => $size(26)]
);
$closing_inner = $container(
    'spclsin',
    'e-sp-closing-inner',
    [$details_card, $invitation_card],
    [
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'),
        'gap' => $size(24),
        'padding' => $dimensions(['block-start' => 70, 'inline-end' => 24, 'block-end' => 74, 'inline-start' => 24]),
    ],
    [
        'flex-direction' => $typed('string', 'column'),
        'gap' => $size(20),
        'padding' => $dimensions(['block-start' => 48, 'inline-end' => 18, 'block-end' => 52, 'inline-start' => 18]),
    ]
);
$closing = $container(
    'spclose',
    'e-sp-closing',
    [$closing_inner],
    [
        'width' => $size(100, '%'),
        'flex-direction' => $typed('string', 'column'),
        'align-items' => $typed('string', 'center'),
        'background' => $background('#082b55'),
    ]
);

$new_data = [$breadcrumbs, $hero, $content, $closing];
$encoded = wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

update_post_meta($post_id, '_elementor_data', $encoded);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '4.2.2');
clean_post_cache($post_id);

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success('The Safety Plan page was redesigned with native Elementor containers.');
