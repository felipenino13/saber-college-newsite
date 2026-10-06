<?php
/**
 * Apply SABER Design System 0.2 to Apply To SABER College (page 883).
 * Existing copy, lists, Rank Math data and protected templates are preserved.
 */
if (!defined('ABSPATH')) { exit(1); }

$post_id = 883;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'apply-to-saber-college') {
    WP_CLI::error('Page 883 is not Apply To SABER College.');
}
$raw = get_post_meta($post_id, '_elementor_data', true);
$data = json_decode($raw, true);
if (!is_array($data)) { WP_CLI::error('Invalid Elementor data.'); }

$widgets = [];
$collect = function ($nodes) use (&$collect, &$widgets) {
    foreach ($nodes as $node) {
        if (($node['elType'] ?? '') === 'widget') { $widgets[$node['id']] = $node; }
        $collect($node['elements'] ?? []);
    }
};
$collect($data);

$content_fields = [
    '71b8471' => ['shortcode'], '0d5b90d' => ['title'], '4a076a9' => ['editor'],
    'f4dd20f' => ['title'], '54993c7' => ['editor'], '5faa943' => ['title'],
    '21670a6' => ['editor'], '07062f8' => ['icon_list'], 'dbf6d69' => ['editor'],
    'ba40f70' => ['title'], '51ccffc' => ['editor'], '1530653' => ['editor'],
    '427b09c' => ['icon_list'], '9390b03' => ['title'], '43dcca2' => ['editor'],
    '3e2a333' => ['editor'], 'b8311e0' => ['icon_list'], '8aeac69' => ['editor'],
    '16ba6a7' => ['editor'], 'a3cc0a1' => ['icon_list'], '2deec22' => ['editor'],
    '612a4b5' => ['title'], '19b57a4' => ['editor'], '9c619c7' => ['editor'],
    'd5dbe5b' => ['editor'], '33ce315' => ['title'], '366a581' => ['editor'],
    '59bc269' => ['icon_list'], '6f67d05' => ['editor'], 'b66aaf4' => ['title'],
    '8b04218' => ['editor'], 'b108ce4' => ['editor'], '769aa6e' => ['title'],
    '1801b59' => ['editor'], 'ab134a4' => ['editor'],
];
$original_content = [];
foreach ($content_fields as $id => $fields) {
    if (!isset($widgets[$id])) { WP_CLI::error("Original widget $id is missing. Nothing was changed."); }
    foreach ($fields as $field) { $original_content[$id][$field] = $widgets[$id]['settings'][$field] ?? null; }
}

$snapshot = [
    'page_id' => $post_id, 'created_utc' => gmdate('c'), 'post' => (array) $page,
    'meta' => get_post_meta($post_id), 'protected_hashes' => [],
];
foreach ([7, 16, 53] as $protected) {
    $snapshot['protected_hashes'][$protected] = hash('sha256', get_post_meta($protected, '_elementor_data', true));
}
$stamp = gmdate('Ymd-His');
$backup = '/tmp/apply-saber-883-before-' . $stamp . '.json';
$backup_json = wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (false === file_put_contents($backup, $backup_json)) {
    WP_CLI::error('Could not create backup. Nothing was changed.');
}
file_put_contents('/tmp/apply-saber-883-before-latest.json', $backup_json);
WP_CLI::line('BACKUP=' . $backup);

$assets = [];
foreach (['path-light', 'path-warm', 'path-navy'] as $asset) {
    $existing = get_posts([
        'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1,
        'fields' => 'ids', 'meta_key' => '_saber_design_asset', 'meta_value' => $asset . '-v02',
    ]);
    if (!$existing) { WP_CLI::error("Design asset $asset-v02 is not registered."); }
    $assets[$asset] = $existing[0];
}
if (!wp_get_attachment_url(1699)) { WP_CLI::error('Campus image 1699 is unavailable.'); }

$typed = static fn($type, $value) => ['$$type' => $type, 'value' => $value];
$size = static fn($value, $unit = 'px') => ['$$type' => 'size', 'value' => ['size' => $value, 'unit' => $unit]];
$pad = static function ($top, $right, $bottom, $left) use ($typed, $size) {
    return $typed('dimensions', [
        'block-start' => $size($top), 'inline-end' => $size($right),
        'block-end' => $size($bottom), 'inline-start' => $size($left),
    ]);
};
$bg = static function ($color, $attachment = 0) use ($typed) {
    $value = ['color' => $typed('color', $color)];
    if ($attachment) {
        $value['background-overlay'] = $typed('background-overlay', [$typed('background-image-overlay', [
            'image' => $typed('image', [
                'src' => $typed('image-src', [
                    'id' => $typed('image-attachment-id', $attachment), 'url' => null,
                ]),
                'size' => $typed('string', 'full'),
            ]),
            'repeat' => $typed('string', 'no-repeat'), 'size' => $typed('string', 'cover'),
            'position' => $typed('string', 'center center'),
        ])]);
    }
    return $typed('background', $value);
};
$container = static function ($name, $children, $desktop = [], $tablet = [], $mobile = [], $hover = []) use ($typed, $size) {
    $id = substr(md5('saber-apply-' . $name), 0, 7);
    $class = 'e-apply-' . $name;
    $desktop = array_merge([
        'display' => $typed('string', 'flex'), 'flex-direction' => $typed('string', 'column'),
        'min-width' => $size(0),
    ], $desktop);
    $variants = [[
        'meta' => ['breakpoint' => 'desktop', 'state' => null], 'props' => $desktop, 'custom_css' => null,
    ]];
    foreach (['tablet' => $tablet, 'mobile' => $mobile] as $breakpoint => $props) {
        if ($props) { $variants[] = ['meta' => ['breakpoint' => $breakpoint, 'state' => null], 'props' => $props, 'custom_css' => null]; }
    }
    if ($hover) {
        $variants[] = ['meta' => ['breakpoint' => 'desktop', 'state' => 'hover'], 'props' => $hover, 'custom_css' => null];
    }
    return [
        'id' => $id, 'elType' => 'e-flexbox',
        'settings' => ['classes' => $typed('classes', [$class])], 'elements' => $children,
        'isInner' => false,
        'styles' => [$class => [
            'id' => $class, 'label' => $class, 'type' => 'class', 'variants' => $variants,
        ]],
        'interactions' => [], 'editor_settings' => [], 'version' => '0.0',
    ];
};
$inner = static function ($name, $children, $extra = [], $tablet = [], $mobile = []) use ($container, $size, $pad) {
    return $container($name, $children, array_merge([
        'width' => $size(100, '%'), 'max-width' => $size(1200),
        'padding' => $pad(72, 24, 72, 24), 'gap' => $size(28),
    ], $extra), $tablet, array_merge(['padding' => $pad(48, 20, 52, 20)], $mobile));
};
$section = static function ($name, $children, $color, $attachment = 0) use ($container, $size, $typed, $bg) {
    return $container($name, $children, [
        'width' => $size(100, '%'), 'align-items' => $typed('string', 'center'),
        'background' => $bg($color, $attachment),
    ]);
};
$icon = static function ($name, $glyph, $color = '#25377b', $font_size = 28) {
    return [
        'id' => substr(md5('apply-icon-' . $name), 0, 7), 'elType' => 'widget',
        'widgetType' => 'icon', 'elements' => [],
        'settings' => [
            'selected_icon' => ['value' => 'fas fa-' . $glyph, 'library' => 'fa-solid'],
            'view' => 'default', 'primary_color' => $color, 'align' => 'left',
            'size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
            '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
    ];
};
$heading = static function ($id, $desktop_size, $mobile_size, $color = '#001a39', $tag = '') use (&$widgets) {
    $widget = $widgets[$id];
    unset($widget['settings']['__globals__']);
    if ($tag !== '') { $widget['settings']['header_size'] = $tag; }
    $widget['settings'] = array_merge($widget['settings'], [
        'title_color' => $color, 'typography_typography' => 'custom',
        'typography_font_family' => 'Nunito', 'typography_font_weight' => '800',
        'typography_font_size' => ['unit' => 'px', 'size' => $desktop_size, 'sizes' => []],
        'typography_font_size_mobile' => ['unit' => 'px', 'size' => $mobile_size, 'sizes' => []],
        'typography_line_height' => ['unit' => 'em', 'size' => 1.12, 'sizes' => []],
        'typography_letter_spacing' => ['unit' => 'px', 'size' => -0.5, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ]);
    return $widget;
};
$text_widget = static function ($id, $color = '#29435f', $font_size = 17, $weight = '500') use (&$widgets) {
    $widget = $widgets[$id];
    unset($widget['settings']['__globals__']);
    $widget['settings'] = array_merge($widget['settings'], [
        'text_color' => $color, 'link_color' => '#005fbd', 'link_hover_color' => '#003f7d',
        'typography_typography' => 'custom', 'typography_font_family' => 'Nunito',
        'typography_font_weight' => $weight,
        'typography_font_size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
        'typography_font_size_mobile' => ['unit' => 'px', 'size' => 16, 'sizes' => []],
        'typography_line_height' => ['unit' => 'em', 'size' => 1.68, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ]);
    return $widget;
};
$list_widget = static function ($id, $color = '#29435f', $icon_color = '#25377b') use (&$widgets) {
    $widget = $widgets[$id];
    unset($widget['settings']['__globals__']);
    $widget['settings'] = array_merge($widget['settings'], [
        'selected_icon' => ['value' => 'fas fa-check', 'library' => 'fa-solid'],
        'icon_color' => $icon_color, 'text_color' => $color,
        'typography_typography' => 'custom', 'typography_font_family' => 'Nunito',
        'typography_font_weight' => '600',
        'typography_font_size' => ['unit' => 'px', 'size' => 16, 'sizes' => []],
        'typography_font_size_mobile' => ['unit' => 'px', 'size' => 15, 'sizes' => []],
        'typography_line_height' => ['unit' => 'em', 'size' => 1.5, 'sizes' => []],
        'space_between' => ['unit' => 'px', 'size' => 14, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ]);
    return $widget;
};
$image = static function ($name, $attachment_id, $alt, $height) {
    return [
        'id' => substr(md5('apply-image-' . $name), 0, 7), 'elType' => 'widget',
        'widgetType' => 'image', 'elements' => [],
        'settings' => [
            'image' => ['id' => $attachment_id, 'url' => wp_get_attachment_url($attachment_id), 'alt' => $alt, 'source' => 'library'],
            'image_size' => 'full', 'width' => ['unit' => '%', 'size' => 100, 'sizes' => []],
            'height' => ['unit' => 'px', 'size' => $height, 'sizes' => []],
            'height_mobile' => ['unit' => 'px', 'size' => 310, 'sizes' => []],
            'object-fit' => 'cover', 'object-position' => 'center center',
            '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
    ];
};

$breadcrumbs = $section('breadcrumbs', [$inner('breadcrumbs-inner', [$widgets['71b8471']], [
    'padding' => $pad(20, 24, 20, 24), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'),
], [], ['padding' => $pad(68, 20, 18, 20)])], '#ffffff');

$rule = [
    'id' => 'apprule1', 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [],
    'settings' => [
        'color' => '#fdc800', 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []],
        'width' => ['unit' => 'px', 'size' => 72, 'sizes' => []],
        'gap' => ['unit' => 'px', 'size' => 5, 'sizes' => []], 'align' => 'left',
    ],
];
$hero_copy = $container('hero-copy', [
    $rule, $heading('0d5b90d', 50, 38, '#001a39', 'h1'), $text_widget('4a076a9', '#29435f', 19),
], [
    'width' => $size(48, '%'), 'gap' => $size(18), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$hero_visual = $container('hero-visual', [$image('campus', 1699, 'SABER College campus in Miami', 460)], [
    'width' => $size(52, '%'), 'min-height' => $size(460), 'overflow' => $typed('string', 'hidden'),
    'border-radius' => $size(30), 'background' => $bg('#dceaf5'),
], [], ['width' => $size(100, '%'), 'min-height' => $size(310)]);
$hero = $section('hero', [$inner('hero-inner', [$hero_copy, $hero_visual], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(48), 'padding' => $pad(48, 24, 68, 24),
], [], [
    'flex-direction' => $typed('string', 'column'), 'align-items' => $typed('string', 'stretch'),
    'gap' => $size(30), 'padding' => $pad(34, 20, 48, 20),
])], '#f3f9ff', $assets['path-light']);

$intro = $section('intro', [$inner('intro-inner', [
    $heading('f4dd20f', 38, 31), $text_widget('54993c7', '#29435f', 18),
], [
    'max-width' => $size(900), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'), 'gap' => $size(16),
    'padding' => $pad(70, 24, 70, 24),
])], '#ffffff');

$step_one = $container('step-one-card', [
    $container('step-one-title', [
        $icon('step-one', 'clipboard-check', '#25377b', 34),
        $heading('5faa943', 31, 26, '#001a39', 'h2'),
    ], ['width' => $size(34, '%'), 'gap' => $size(18)], [], ['width' => $size(100, '%')]),
    $container('step-one-copy', [
        $text_widget('21670a6'), $list_widget('07062f8'), $text_widget('dbf6d69'),
    ], ['width' => $size(66, '%'), 'gap' => $size(18)], [], ['width' => $size(100, '%')]),
], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'flex-start'), 'gap' => $size(46),
    'padding' => $pad(40, 42, 40, 42), 'background' => $bg('#ffffff'),
    'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#dce5ec'),
    'border-width' => $size(1), 'border-radius' => $size(22),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(24), 'padding' => $size(25)]);
$step_one_section = $section('step-one', [$inner('step-one-inner', [$step_one], [
    'padding' => $pad(70, 24, 74, 24),
])], '#fffcf4', $assets['path-warm']);

$step_two_title = $container('step-two-title', [
    $icon('step-two', 'comments', '#fdc800', 35),
    $heading('ba40f70', 32, 27, '#ffffff', 'h2'),
], ['width' => $size(38, '%'), 'gap' => $size(18)], [], ['width' => $size(100, '%')]);
$step_two_copy = $container('step-two-copy', [
    $text_widget('51ccffc', '#ffffff', 17), $text_widget('1530653', '#ffffff', 17),
    $list_widget('427b09c', '#ffffff', '#fdc800'),
], [
    'width' => $size(62, '%'), 'gap' => $size(18), 'padding' => $pad(34, 36, 34, 36),
    'background' => $bg('rgba(255,255,255,.075)'), 'border-style' => $typed('string', 'solid'),
    'border-color' => $typed('color', 'rgba(255,255,255,.18)'), 'border-width' => $size(1),
    'border-radius' => $size(20),
], [], ['width' => $size(100, '%'), 'padding' => $size(24)]);
$step_two = $section('step-two', [$inner('step-two-inner', [$step_two_title, $step_two_copy], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(54), 'padding' => $pad(74, 24, 78, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(28)])], '#082b55', $assets['path-navy']);

$step_three_header = $container('step-three-header', [
    $icon('step-three', 'school', '#25377b', 34),
    $heading('9390b03', 36, 29, '#001a39', 'h2'),
    $text_widget('43dcca2', '#29435f', 18),
    $text_widget('3e2a333', '#25377b', 17, '800'),
], [
    'width' => $size(100, '%'), 'max-width' => $size(940), 'align-self' => $typed('string', 'center'),
    'align-items' => $typed('string', 'center'), 'text-align' => $typed('string', 'center'), 'gap' => $size(15),
]);
$step_three_primary = $container('step-three-primary', [
    $list_widget('b8311e0'), $text_widget('8aeac69', '#29435f', 17),
], [
    'width' => $size(100, '%'), 'gap' => $size(20), 'padding' => $pad(34, 36, 34, 36),
    'background' => $bg('#f3f9ff'), 'border-radius' => $size(20),
]);
$step_three_secondary = $container('step-three-secondary', [
    $text_widget('16ba6a7', '#25377b', 18, '800'), $list_widget('a3cc0a1'),
    $text_widget('2deec22', '#29435f', 17),
], [
    'width' => $size(100, '%'), 'gap' => $size(20), 'padding' => $pad(34, 36, 34, 36),
    'background' => $bg('#fff8e4'), 'border-radius' => $size(20),
]);
$step_three = $section('step-three', [$inner('step-three-inner', [
    $step_three_header, $step_three_primary, $step_three_secondary,
], ['gap' => $size(24), 'padding' => $pad(76, 24, 80, 24)])], '#ffffff');

$step_four = $container('step-four-card', [
    $icon('step-four', 'hand-holding-usd', '#25377b', 32),
    $heading('612a4b5', 29, 25, '#001a39', 'h2'),
    $text_widget('19b57a4'), $text_widget('9c619c7'), $text_widget('d5dbe5b', '#25377b', 17, '800'),
], [
    'width' => $size(49, '%'), 'padding' => $pad(34, 34, 34, 34), 'gap' => $size(16),
    'background' => $bg('#ffffff'), 'border-radius' => $size(20),
    'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#dce5ec'),
    'border-width' => $size(1),
], [], ['width' => $size(100, '%'), 'padding' => $size(25)]);
$step_five = $container('step-five-card', [
    $icon('step-five', 'flag-checkered', '#25377b', 32),
    $heading('33ce315', 29, 25, '#001a39', 'h2'),
    $text_widget('366a581'), $list_widget('59bc269'), $text_widget('6f67d05'),
], [
    'width' => $size(49, '%'), 'padding' => $pad(34, 34, 34, 34), 'gap' => $size(16),
    'background' => $bg('#f3f9ff'), 'border-radius' => $size(20),
    'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#dce5ec'),
    'border-width' => $size(1),
], [], ['width' => $size(100, '%'), 'padding' => $size(25)]);
$final_steps = $section('final-steps', [$inner('final-steps-inner', [$step_four, $step_five], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'stretch'),
    'gap' => $size(24), 'padding' => $pad(74, 24, 78, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(20)])], '#fffcf4', $assets['path-warm']);

$support_card = $container('support-card', [
    $icon('support', 'headset', '#fdc800', 34),
    $heading('b66aaf4', 32, 27, '#ffffff', 'h2'),
    $text_widget('8b04218', '#ffffff', 17),
    $text_widget('b108ce4', '#ffffff', 18, '800'),
], [
    'width' => $size(46, '%'), 'padding' => $pad(38, 38, 38, 38), 'gap' => $size(18),
    'background' => $bg('#082b55'), 'border-radius' => $size(22),
], [], ['width' => $size(100, '%'), 'padding' => $size(26)]);
$cta_card = $container('cta-card', [
    $icon('cta', 'arrow-right', '#25377b', 34),
    $heading('769aa6e', 34, 29, '#001a39', 'h2'),
    $text_widget('1801b59', '#001a39', 18, '700'),
    $text_widget('ab134a4', '#29435f', 17),
], [
    'width' => $size(54, '%'), 'padding' => $pad(38, 40, 38, 40), 'gap' => $size(18),
    'background' => $bg('#fdc800'), 'border-radius' => $size(22),
], [], ['width' => $size(100, '%'), 'padding' => $size(26)]);
$support = $section('support', [$inner('support-inner', [$support_card, $cta_card], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'stretch'),
    'gap' => $size(24), 'padding' => $pad(74, 24, 80, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(20)])], '#ffffff');

$new_data = [$breadcrumbs, $hero, $intro, $step_one_section, $step_two, $step_three, $final_steps, $support];
$widgets = [];
$collect($new_data);
foreach ($original_content as $id => $fields) {
    foreach ($fields as $field => $value) {
        if (($widgets[$id]['settings'][$field] ?? null) !== $value) {
            WP_CLI::error("Content preservation failed for $id:$field.");
        }
    }
}

$page_settings = get_post_meta($post_id, '_elementor_page_settings', true) ?: [];
$page_css = <<<'CSS'
/* Apply To SABER: Design System 0.2 page polish. */
.elementor-883 .rank-math-breadcrumb p { margin: 0; }
.elementor-883 .elementor-widget-text-editor p { margin-top: 0; }
.elementor-883 .elementor-widget-text-editor p:last-child { margin-bottom: 0; }
.elementor-883 a:focus-visible { outline: 3px solid #005fbd; outline-offset: 5px; box-shadow: 0 0 0 7px #fff; border-radius: 3px; }
.elementor-883 .e-apply-hero-visual img { display: block; }
.elementor-883 .e-apply-step-one-copy .elementor-icon-list-items,
.elementor-883 .e-apply-step-two-copy .elementor-icon-list-items,
.elementor-883 .e-apply-step-three-primary .elementor-icon-list-items,
.elementor-883 .e-apply-step-three-secondary .elementor-icon-list-items,
.elementor-883 .e-apply-step-five-card .elementor-icon-list-items {
  display: grid; gap: 12px; margin: 0;
}
.elementor-883 .e-apply-step-one-copy .elementor-icon-list-items { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.elementor-883 .e-apply-step-two-copy .elementor-icon-list-items,
.elementor-883 .e-apply-step-three-primary .elementor-icon-list-items { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.elementor-883 .e-apply-step-three-secondary .elementor-icon-list-items,
.elementor-883 .e-apply-step-five-card .elementor-icon-list-items { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.elementor-883 .e-apply-step-one-copy .elementor-icon-list-item,
.elementor-883 .e-apply-step-two-copy .elementor-icon-list-item,
.elementor-883 .e-apply-step-three-primary .elementor-icon-list-item,
.elementor-883 .e-apply-step-three-secondary .elementor-icon-list-item,
.elementor-883 .e-apply-step-five-card .elementor-icon-list-item {
  align-items: flex-start; margin: 0; padding: 16px 18px; border-radius: 12px;
  border: 1px solid #dce5ec; background: #fff;
}
.elementor-883 .e-apply-step-two-copy .elementor-icon-list-item {
  border-color: rgba(255,255,255,.17); background: rgba(255,255,255,.07);
}
.elementor-883 .elementor-icon-list-icon { margin-top: 4px; }
.elementor-883 .e-apply-step-one-title .elementor-widget-icon,
.elementor-883 .e-apply-step-two-title .elementor-widget-icon,
.elementor-883 .e-apply-step-three-header .elementor-widget-icon,
.elementor-883 .e-apply-step-four-card > .elementor-widget-icon,
.elementor-883 .e-apply-step-five-card > .elementor-widget-icon,
.elementor-883 .e-apply-support-card > .elementor-widget-icon,
.elementor-883 .e-apply-cta-card > .elementor-widget-icon { width: 38px; flex: 0 0 38px; }
@media (max-width: 767px) {
  .elementor-883 .e-apply-step-one-copy .elementor-icon-list-items,
  .elementor-883 .e-apply-step-two-copy .elementor-icon-list-items,
  .elementor-883 .e-apply-step-three-primary .elementor-icon-list-items,
  .elementor-883 .e-apply-step-three-secondary .elementor-icon-list-items,
  .elementor-883 .e-apply-step-five-card .elementor-icon-list-items { grid-template-columns: 1fr; }
}
CSS;
$existing_css = $page_settings['custom_css'] ?? '';
$marker = '/* Apply To SABER: Design System 0.2 page polish. */';
if (str_contains($existing_css, $marker)) { $existing_css = strstr($existing_css, $marker, true); }
$page_settings['custom_css'] = rtrim($existing_css) . "\n" . $page_css;

wp_save_post_revision($post_id);
update_post_meta($post_id, '_elementor_data', wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
update_post_meta($post_id, '_elementor_page_settings', $page_settings);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_version', ELEMENTOR_VERSION);
delete_post_meta($post_id, '_elementor_element_cache');
do_action('elementor/atomic-widgets/styles/clear', ['local', $post_id]);
Elementor\Plugin::$instance->documents->get($post_id, false);
Elementor\Core\Files\CSS\Post::create($post_id)->update();
clean_post_cache($post_id);

$protected_unchanged = true;
foreach ($snapshot['protected_hashes'] as $id => $hash) {
    $protected_unchanged = $protected_unchanged && hash_equals($hash, hash('sha256', get_post_meta($id, '_elementor_data', true)));
}
$widget_types = [];
foreach ($widgets as $widget) {
    if (!empty($widget['widgetType'])) { $widget_types[] = $widget['widgetType']; }
}
$report = [
    'page_id' => $post_id, 'url' => get_permalink($post_id), 'backup' => $backup,
    'content_fields_preserved' => count($original_content),
    'protected_templates_unchanged' => $protected_unchanged,
    'design_assets' => $assets, 'images' => [1699],
    'native_widget_types' => array_values(array_unique($widget_types)),
    'elementor_widgets' => count($widgets),
];
file_put_contents('/tmp/apply-saber-redesign-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('Apply To SABER College redesigned. Original content and lists are preserved.');
