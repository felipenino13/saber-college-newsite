<?php
/**
 * Apply SABER Design System 0.2 to the Leadership page (ID 859).
 * Existing copy, leader lists, Rank Math data and protected templates are preserved.
 */
if (!defined('ABSPATH')) { exit(1); }

$post_id = 859;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'saber_college_leadership' || (int) $page->post_parent !== 36) {
    WP_CLI::error('Page 859 is not the expected Leadership child of General Overview.');
}

$raw = get_post_meta($post_id, '_elementor_data', true);
$data = json_decode($raw, true);
if (!is_array($data)) { WP_CLI::error('Invalid Elementor data.'); }

$widgets = [];
$collect = function ($nodes) use (&$collect, &$widgets) {
    foreach ($nodes as $node) {
        if (($node['elType'] ?? '') === 'widget' && isset($node['id'])) { $widgets[$node['id']] = $node; }
        $collect($node['elements'] ?? []);
    }
};
$collect($data);

$content_fields = [
    '31f8e56' => ['shortcode'],
    'aa635eb' => ['title'], '0b28c51' => ['title'],
    '06a25ea' => ['editor'], 'a44fcfe' => ['editor'], '40bc1d0' => ['editor'],
    'd833d45' => ['title'], 'e7ee7b2' => ['editor'], '399fbed' => ['icon_list'],
    'bfc7d70' => ['title'], '3996cfd' => ['editor'], '20ad238' => ['icon_list'],
    'd9d6467' => ['editor'], '64e29da' => ['editor'], '1410108' => ['editor'],
    '64be9a7' => ['editor'], 'f03e57d' => ['editor'], 'dcc10d1' => ['editor'],
];
$original_content = [];
foreach ($content_fields as $id => $fields) {
    if (!isset($widgets[$id])) { WP_CLI::error("Original widget $id is missing. Nothing was changed."); }
    foreach ($fields as $field) { $original_content[$id][$field] = $widgets[$id]['settings'][$field] ?? null; }
}

$rank_math = [];
foreach (get_post_meta($post_id) as $key => $values) {
    if (str_starts_with($key, 'rank_math_')) { $rank_math[$key] = $values; }
}
$snapshot = [
    'page_id' => $post_id,
    'created_utc' => gmdate('c'),
    'post' => (array) $page,
    'meta' => get_post_meta($post_id),
    'original_content' => $original_content,
    'rank_math' => $rank_math,
    'post_content_hash' => hash('sha256', $page->post_content),
    'protected_hashes' => [],
];
foreach ([7, 16, 53] as $protected) {
    $snapshot['protected_hashes'][$protected] = hash('sha256', get_post_meta($protected, '_elementor_data', true));
}
$stamp = gmdate('Ymd-His');
$backup = '/tmp/saber-leadership-859-before-' . $stamp . '.json';
$backup_json = wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (false === file_put_contents($backup, $backup_json)) {
    WP_CLI::error('Could not create backup. Nothing was changed.');
}
file_put_contents('/tmp/saber-leadership-859-before-latest.json', $backup_json);
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
$image_ids = [1308, 1699];
foreach ($image_ids as $image_id) {
    if (!wp_get_attachment_url($image_id)) { WP_CLI::error("Image $image_id is unavailable."); }
}

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
$container = static function ($name, $children, $desktop = [], $tablet = [], $mobile = []) use ($typed, $size) {
    $id = substr(md5('saber-leadership-' . $name), 0, 7);
    $class = 'e-leadership-' . $name;
    $desktop = array_merge([
        'display' => $typed('string', 'flex'), 'flex-direction' => $typed('string', 'column'),
        'min-width' => $size(0),
    ], $desktop);
    $variants = [[
        'meta' => ['breakpoint' => 'desktop', 'state' => null], 'props' => $desktop, 'custom_css' => null,
    ]];
    foreach (['tablet' => $tablet, 'mobile' => $mobile] as $breakpoint => $props) {
        if ($props) {
            $variants[] = ['meta' => ['breakpoint' => $breakpoint, 'state' => null], 'props' => $props, 'custom_css' => null];
        }
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
        'padding' => $pad(72, 24, 78, 24), 'gap' => $size(28),
    ], $extra), $tablet, array_merge(['padding' => $pad(48, 20, 54, 20)], $mobile));
};
$section = static function ($name, $children, $color, $attachment = 0) use ($container, $size, $typed, $bg) {
    return $container($name, $children, [
        'width' => $size(100, '%'), 'align-items' => $typed('string', 'center'),
        'background' => $bg($color, $attachment),
    ]);
};
$icon = static function ($name, $glyph, $color = '#25377b', $font_size = 30) {
    return [
        'id' => substr(md5('leadership-icon-' . $name), 0, 7), 'elType' => 'widget',
        'widgetType' => 'icon', 'elements' => [],
        'settings' => [
            'selected_icon' => ['value' => 'fas fa-' . $glyph, 'library' => 'fa-solid'],
            'view' => 'default', 'primary_color' => $color, 'align' => 'left',
            'size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
            '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
    ];
};
$image = static function ($name, $attachment_id, $alt, $height, $mobile_height = 320) {
    return [
        'id' => substr(md5('leadership-image-' . $name), 0, 7), 'elType' => 'widget',
        'widgetType' => 'image', 'elements' => [],
        'settings' => [
            'image' => ['id' => $attachment_id, 'url' => wp_get_attachment_url($attachment_id), 'alt' => $alt, 'source' => 'library'],
            'image_size' => 'full', 'width' => ['unit' => '%', 'size' => 100, 'sizes' => []],
            'height' => ['unit' => 'px', 'size' => $height, 'sizes' => []],
            'height_mobile' => ['unit' => 'px', 'size' => $mobile_height, 'sizes' => []],
            'object-fit' => 'cover', 'object-position' => 'center center',
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
        'typography_line_height' => ['unit' => 'em', 'size' => 1.08, 'sizes' => []],
        'typography_letter_spacing' => ['unit' => 'px', 'size' => -0.6, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ]);
    return $widget;
};
$text = static function ($id, $color = '#29435f', $font_size = 17, $weight = '500') use (&$widgets) {
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
$list = static function ($id, $text_color = '#29435f', $icon_color = '#25377b') use (&$widgets) {
    $widget = $widgets[$id];
    unset($widget['settings']['__globals__']);
    $widget['settings'] = array_merge($widget['settings'], [
        'icon_color' => $icon_color, 'text_color' => $text_color,
        'icon_size' => ['unit' => 'px', 'size' => 18, 'sizes' => []],
        'text_indent' => ['unit' => 'px', 'size' => 10, 'sizes' => []],
        'space_between' => ['unit' => 'px', 'size' => 0, 'sizes' => []],
        'text_typography_typography' => 'custom', 'text_typography_font_family' => 'Nunito',
        'text_typography_font_weight' => '500',
        'text_typography_font_size' => ['unit' => 'px', 'size' => 16, 'sizes' => []],
        'text_typography_font_size_mobile' => ['unit' => 'px', 'size' => 15, 'sizes' => []],
        'text_typography_line_height' => ['unit' => 'em', 'size' => 1.5, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ]);
    return $widget;
};

$breadcrumbs = $section('breadcrumbs', [$inner('breadcrumbs-inner', [$widgets['31f8e56']], [
    'padding' => $pad(20, 24, 20, 24), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'),
], [], ['padding' => $pad(68, 20, 18, 20)])], '#ffffff');

$rule = [
    'id' => 'leadru1', 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [],
    'settings' => [
        'color' => '#fdc800', 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []],
        'width' => ['unit' => 'px', 'size' => 72, 'sizes' => []],
        'gap' => ['unit' => 'px', 'size' => 5, 'sizes' => []], 'align' => 'left',
    ],
];
$hero_copy = $container('hero-copy', [
    $rule, $heading('aa635eb', 52, 37, '#001a39', 'h1'), $text('06a25ea', '#29435f', 18),
], [
    'width' => $size(56, '%'), 'gap' => $size(20), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$hero_visual = $container('hero-visual', [
    $image('graduates', 1308, 'SABER College graduates', 500, 300),
], [
    'width' => $size(44, '%'), 'min-height' => $size(500), 'overflow' => $typed('string', 'hidden'),
    'border-radius' => $size(28), 'background' => $bg('#ffffff'),
], [], ['width' => $size(100, '%'), 'min-height' => $size(300)]);
$hero = $section('hero', [$inner('hero-inner', [$hero_copy, $hero_visual], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(52), 'padding' => $pad(48, 24, 72, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#f3f9ff', $assets['path-light']);

$overview_heading = $container('overview-heading', [
    $icon('compass', 'compass', '#25377b', 36), $heading('0b28c51', 39, 31, '#001a39', 'h2'),
], [
    'width' => $size(35, '%'), 'gap' => $size(18), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$overview_card_one = $container('overview-card-one', [$text('a44fcfe')], [
    'width' => $size(100, '%'), 'padding' => $pad(28, 30, 28, 30),
    'background' => $bg('#f3f9ff'), 'border-radius' => $size(18),
]);
$overview_card_two = $container('overview-card-two', [$text('40bc1d0')], [
    'width' => $size(100, '%'), 'padding' => $pad(28, 30, 28, 30),
    'background' => $bg('#fff8e4'), 'border-radius' => $size(18),
]);
$overview_copy = $container('overview-copy', [$overview_card_one, $overview_card_two], [
    'width' => $size(65, '%'), 'gap' => $size(18),
], [], ['width' => $size(100, '%')]);
$overview = $section('overview', [$inner('overview-inner', [$overview_heading, $overview_copy], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'stretch'),
    'gap' => $size(46),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(28)])], '#ffffff');

$executive_header = $container('executive-header', [
    $icon('executive', 'landmark', '#25377b', 37),
    $heading('d833d45', 40, 31, '#001a39', 'h2'),
    $text('e7ee7b2', '#29435f', 17),
], [
    'width' => $size(100, '%'), 'max-width' => $size(820), 'gap' => $size(16),
    'align-items' => $typed('string', 'center'), 'text-align' => $typed('string', 'center'),
]);
$executive_list = $container('executive-list', [$list('399fbed')], [
    'width' => $size(100, '%'),
]);
$executive = $section('executive', [$inner('executive-inner', [$executive_header, $executive_list], [
    'align-items' => $typed('string', 'center'), 'gap' => $size(38),
])], '#fff8e4', $assets['path-warm']);

$education_header = $container('education-header', [
    $icon('education', 'graduation-cap', '#fdc800', 38),
    $heading('bfc7d70', 40, 30, '#ffffff', 'h2'),
    $text('3996cfd', '#eaf3ff', 17),
], [
    'width' => $size(34, '%'), 'gap' => $size(18), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$education_list = $container('education-list', [$list('20ad238')], [
    'width' => $size(66, '%'),
], [], ['width' => $size(100, '%')]);
$education = $section('education', [$inner('education-inner', [$education_header, $education_list], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(46), 'padding' => $pad(82, 24, 84, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#082b55', $assets['path-navy']);

$mission_card_one = $container('mission-card-one', [
    $icon('student-success', 'users', '#25377b', 32), $text('d9d6467', '#001a39', 17),
], [
    'width' => $size(50, '%'), 'padding' => $pad(34, 36, 34, 36), 'gap' => $size(18),
    'background' => $bg('#fdc800'), 'border-radius' => $size(20),
], [], ['width' => $size(100, '%'), 'padding' => $pad(28, 26, 28, 26)]);
$mission_card_two = $container('mission-card-two', [
    $icon('compliance', 'shield-alt', '#25377b', 32), $text('64e29da', '#29435f', 17),
], [
    'width' => $size(50, '%'), 'padding' => $pad(34, 36, 34, 36), 'gap' => $size(18),
    'background' => $bg('#ffffff'), 'border-radius' => $size(20),
], [], ['width' => $size(100, '%'), 'padding' => $pad(28, 26, 28, 26)]);
$mission = $section('mission', [$inner('mission-inner', [$mission_card_one, $mission_card_two], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'stretch'),
    'gap' => $size(24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)])], '#f3f9ff', $assets['path-light']);

$community_card_one = $container('community-card-one', [
    $icon('languages', 'globe-americas', '#25377b', 34), $text('1410108'),
], [
    'width' => $size(50, '%'), 'padding' => $pad(36, 38, 36, 38), 'gap' => $size(20),
    'background' => $bg('#fff8e4'), 'border-radius' => $size(22),
], [], ['width' => $size(100, '%'), 'padding' => $pad(28, 26, 28, 26)]);
$community_card_two = $container('community-card-two', [
    $icon('partnerships', 'hands-helping', '#25377b', 34), $text('64be9a7'),
], [
    'width' => $size(50, '%'), 'padding' => $pad(36, 38, 36, 38), 'gap' => $size(20),
    'background' => $bg('#f3f9ff'), 'border-radius' => $size(22),
], [], ['width' => $size(100, '%'), 'padding' => $pad(28, 26, 28, 26)]);
$community = $section('community', [$inner('community-inner', [$community_card_one, $community_card_two], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'stretch'),
    'gap' => $size(24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)])], '#ffffff');

$closing_visual = $container('closing-visual', [
    $image('campus', 1699, 'SABER College campus in Miami', 420, 290),
], [
    'width' => $size(50, '%'), 'overflow' => $typed('string', 'hidden'),
    'border-radius' => $size(24), 'background' => $bg('#dfeefa'),
], [], ['width' => $size(100, '%')]);
$closing_statement = $container('closing-statement', [
    $icon('forward', 'arrow-right', '#fdc800', 32), $text('f03e57d', '#ffffff', 18, '700'),
], [
    'width' => $size(100, '%'), 'gap' => $size(16),
]);
$closing_invitation = $container('closing-invitation', [
    $text('dcc10d1', '#001a39', 18, '800'),
], [
    'width' => $size(100, '%'), 'padding' => $pad(26, 28, 26, 28),
    'background' => $bg('#fdc800'), 'border-radius' => $size(18),
]);
$closing_copy = $container('closing-copy', [$closing_statement, $closing_invitation], [
    'width' => $size(50, '%'), 'gap' => $size(28), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$closing = $section('closing', [$inner('closing-inner', [$closing_visual, $closing_copy], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(46), 'padding' => $pad(76, 24, 82, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#082b55', $assets['path-navy']);

$new_data = [$breadcrumbs, $hero, $overview, $executive, $education, $mission, $community, $closing];
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
/* SABER Leadership: Design System 0.2 page polish. */
.elementor-859 .rank-math-breadcrumb p { margin: 0; }
.elementor-859 .elementor-widget-text-editor p { margin-top: 0; }
.elementor-859 .elementor-widget-text-editor p:last-child { margin-bottom: 0; }
.elementor-859 a:focus-visible { outline: 3px solid #005fbd; outline-offset: 5px; box-shadow: 0 0 0 7px #fff; border-radius: 3px; }
.elementor-859 .e-leadership-hero-visual img,
.elementor-859 .e-leadership-closing-visual img { display: block; }
.elementor-859 .e-leadership-executive-list .elementor-icon-list-items,
.elementor-859 .e-leadership-education-list .elementor-icon-list-items {
  display: grid; gap: 18px; margin: 0; padding: 0;
}
.elementor-859 .e-leadership-executive-list .elementor-icon-list-items {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}
.elementor-859 .e-leadership-education-list .elementor-icon-list-items {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}
.elementor-859 .e-leadership-executive-list .elementor-icon-list-item,
.elementor-859 .e-leadership-education-list .elementor-icon-list-item {
  position: relative; display: flex; align-items: flex-start; margin: 0;
  padding: 28px 25px; border-radius: 18px;
}
.elementor-859 .e-leadership-executive-list .elementor-icon-list-item {
  min-height: 150px; background: #fff; border: 1px solid #eadfb9;
  box-shadow: 0 12px 30px rgba(8,43,85,.055);
}
.elementor-859 .e-leadership-education-list .elementor-icon-list-item {
  min-height: 124px; background: rgba(255,255,255,.98); border: 1px solid rgba(253,200,0,.4);
}
.elementor-859 .e-leadership-executive-list .elementor-icon-list-icon,
.elementor-859 .e-leadership-education-list .elementor-icon-list-icon { margin-top: 4px; }
.elementor-859 .e-leadership-executive-list .elementor-icon-list-text,
.elementor-859 .e-leadership-education-list .elementor-icon-list-text { padding-inline-start: 10px; }
.elementor-859 .e-leadership-executive-list strong,
.elementor-859 .e-leadership-education-list strong {
  display: block; margin-bottom: 6px; color: #001a39; font-weight: 800;
}
.elementor-859 .e-leadership-executive-header .elementor-widget-text-editor,
.elementor-859 .e-leadership-executive-header .elementor-widget-text-editor p { text-align: center; }
@media (max-width: 1024px) {
  .elementor-859 .e-leadership-executive-list .elementor-icon-list-items { grid-template-columns: 1fr; }
}
@media (max-width: 767px) {
  .elementor-859 .e-leadership-education-list .elementor-icon-list-items { grid-template-columns: 1fr; }
  .elementor-859 .e-leadership-executive-list .elementor-icon-list-item,
  .elementor-859 .e-leadership-education-list .elementor-icon-list-item { min-height: 0; padding: 24px 22px; }
}
CSS;
$existing_css = $page_settings['custom_css'] ?? '';
$marker = '/* SABER Leadership: Design System 0.2 page polish. */';
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
    'design_assets' => $assets, 'images' => $image_ids,
    'native_widget_types' => array_values(array_unique($widget_types)),
    'elementor_widgets' => count($widgets),
];
file_put_contents('/tmp/saber-leadership-redesign-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('SABER College Leadership redesigned. Original content and lists are preserved.');
