<?php
/** Apply SABER Design System 0.2 to Gallery (page 863), preserving all original content. */
if (!defined('ABSPATH')) { exit(1); }
$post_id = 863;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'explore-saber-college-gallery' || (int) $page->post_parent !== 36) {
    WP_CLI::error('Page 863 is not the expected Gallery child of General Overview.');
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
    '68de0ae' => ['shortcode'], '41aed00' => ['title'],
    '56a5b46' => ['editor'], '3b53306' => ['editor'], '0f57cca' => ['editor'],
    '2d9ba0d' => ['title'], '6381b51' => ['editor'], 'fa8488a' => ['editor'],
    'ad88002' => ['editor'], '50ad5bf' => ['editor'], 'fd9254d' => ['editor'],
    'c82c99e' => ['icon_list'], '959ed42' => ['editor'], 'fc96993' => ['editor'],
    'c847674' => ['editor'], 'b43dd4c' => ['editor'], '40f5634' => ['editor'],
    '0d7a72b' => ['editor'],
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
    'page_id' => $post_id, 'created_utc' => gmdate('c'), 'post' => (array) $page,
    'meta' => get_post_meta($post_id), 'original_content' => $original_content,
    'rank_math' => $rank_math, 'post_content_hash' => hash('sha256', $page->post_content),
    'protected_hashes' => [],
];
foreach ([7, 16, 53] as $protected) {
    $snapshot['protected_hashes'][$protected] = hash('sha256', get_post_meta($protected, '_elementor_data', true));
}
$stamp = gmdate('Ymd-His');
$backup = '/tmp/gallery-863-before-' . $stamp . '.json';
$backup_json = wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (false === file_put_contents($backup, $backup_json)) { WP_CLI::error('Could not create backup. Nothing was changed.'); }
file_put_contents('/tmp/gallery-863-before-latest.json', $backup_json);
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
$image_ids = [999, 993, 1699, 1088, 441];
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
                'src' => $typed('image-src', ['id' => $typed('image-attachment-id', $attachment), 'url' => null]),
                'size' => $typed('string', 'full'),
            ]),
            'repeat' => $typed('string', 'no-repeat'), 'size' => $typed('string', 'cover'),
            'position' => $typed('string', 'center center'),
        ])]);
    }
    return $typed('background', $value);
};
$container = static function ($name, $children, $desktop = [], $tablet = [], $mobile = []) use ($typed, $size) {
    $id = substr(md5('saber-gallery-' . $name), 0, 7);
    $class = 'e-gallery-' . $name;
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
    return [
        'id' => $id, 'elType' => 'e-flexbox', 'settings' => ['classes' => $typed('classes', [$class])],
        'elements' => $children, 'isInner' => false,
        'styles' => [$class => ['id' => $class, 'label' => $class, 'type' => 'class', 'variants' => $variants]],
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
        'id' => substr(md5('gallery-icon-' . $name), 0, 7), 'elType' => 'widget', 'widgetType' => 'icon', 'elements' => [],
        'settings' => [
            'selected_icon' => ['value' => 'fas fa-' . $glyph, 'library' => 'fa-solid'],
            'view' => 'default', 'primary_color' => $color, 'align' => 'left',
            'size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
            '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
    ];
};
$image = static function ($name, $attachment_id, $alt, $height, $mobile_height = 290, $position = 'center center') {
    return [
        'id' => substr(md5('gallery-image-' . $name), 0, 7), 'elType' => 'widget', 'widgetType' => 'image', 'elements' => [],
        'settings' => [
            'image' => ['id' => $attachment_id, 'url' => wp_get_attachment_url($attachment_id), 'alt' => $alt, 'source' => 'library'],
            'image_size' => 'full', 'width' => ['unit' => '%', 'size' => 100, 'sizes' => []],
            'height' => ['unit' => 'px', 'size' => $height, 'sizes' => []],
            'height_mobile' => ['unit' => 'px', 'size' => $mobile_height, 'sizes' => []],
            'object-fit' => 'cover', 'object-position' => $position,
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
$list = static function ($id) use (&$widgets) {
    $widget = $widgets[$id];
    unset($widget['settings']['__globals__']);
    $widget['settings'] = array_merge($widget['settings'], [
        'icon_color' => '#25377b', 'text_color' => '#29435f',
        'icon_size' => ['unit' => 'px', 'size' => 18, 'sizes' => []],
        'text_indent' => ['unit' => 'px', 'size' => 10, 'sizes' => []], 'space_between' => ['unit' => 'px', 'size' => 0, 'sizes' => []],
        'text_typography_typography' => 'custom', 'text_typography_font_family' => 'Nunito',
        'text_typography_font_weight' => '700',
        'text_typography_font_size' => ['unit' => 'px', 'size' => 16, 'sizes' => []],
        'text_typography_font_size_mobile' => ['unit' => 'px', 'size' => 15, 'sizes' => []],
        'text_typography_line_height' => ['unit' => 'em', 'size' => 1.5, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ]);
    return $widget;
};

$breadcrumbs = $section('breadcrumbs', [$inner('breadcrumbs-inner', [$widgets['68de0ae']], [
    'padding' => $pad(20, 24, 20, 24), 'align-items' => $typed('string', 'center'), 'text-align' => $typed('string', 'center'),
], [], ['padding' => $pad(68, 20, 18, 20)])], '#ffffff');
$rule = [
    'id' => 'galrule', 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [],
    'settings' => ['color' => '#fdc800', 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []], 'width' => ['unit' => 'px', 'size' => 72, 'sizes' => []], 'gap' => ['unit' => 'px', 'size' => 5, 'sizes' => []], 'align' => 'left'],
];
$hero_copy = $container('hero-copy', [$rule, $heading('41aed00', 49, 35, '#001a39', 'h1'), $text('56a5b46', '#29435f', 18)], [
    'width' => $size(56, '%'), 'gap' => $size(20), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$hero_large = $container('hero-large', [$image('nursing-community', 999, 'Healthcare students and professionals', 470, 300)], [
    'width' => $size(66, '%'), 'overflow' => $typed('string', 'hidden'), 'border-radius' => $size(24),
], [], ['width' => $size(100, '%')]);
$hero_small = $container('hero-small', [$image('pta-training', 993, 'Physical therapy training environment', 470, 260)], [
    'width' => $size(34, '%'), 'overflow' => $typed('string', 'hidden'), 'border-radius' => $size(24),
], [], ['width' => $size(100, '%')]);
$hero_visual = $container('hero-visual', [$hero_large, $hero_small], [
    'width' => $size(44, '%'), 'flex-direction' => $typed('string', 'row'), 'gap' => $size(14),
], [], ['width' => $size(100, '%'), 'flex-direction' => $typed('string', 'column')]);
$hero = $section('hero', [$inner('hero-inner', [$hero_copy, $hero_visual], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'), 'gap' => $size(48), 'padding' => $pad(48, 24, 72, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#f3f9ff', $assets['path-light']);

$foundation_copy = $container('foundation-copy', [
    $icon('legacy', 'university', '#25377b', 36), $text('3b53306'), $text('0f57cca'),
], [
    'width' => $size(52, '%'), 'gap' => $size(20), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$foundation_image = $container('foundation-image', [$image('campus', 1699, 'SABER College campus in Miami', 410, 280)], [
    'width' => $size(48, '%'), 'overflow' => $typed('string', 'hidden'), 'border-radius' => $size(24),
], [], ['width' => $size(100, '%')]);
$foundation = $section('foundation', [$inner('foundation-inner', [$foundation_copy, $foundation_image], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'), 'gap' => $size(48),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(28)])], '#ffffff');

$journey_visual = $container('journey-visual', [$image('graduation', 1088, 'SABER College graduation celebration', 450, 280)], [
    'width' => $size(48, '%'), 'overflow' => $typed('string', 'hidden'), 'border-radius' => $size(24),
], [], ['width' => $size(100, '%')]);
$journey_copy = $container('journey-copy', [
    $icon('instagram', 'camera-retro', '#fdc800', 38), $heading('2d9ba0d', 40, 30, '#ffffff', 'h2'), $text('6381b51', '#eaf3ff', 17),
], [
    'width' => $size(52, '%'), 'gap' => $size(20), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$journey = $section('journey', [$inner('journey-inner', [$journey_visual, $journey_copy], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'), 'gap' => $size(48), 'padding' => $pad(78, 24, 82, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#082b55', $assets['path-navy']);

$perspective_one = $container('perspective-one', [$icon('culture', 'users', '#25377b', 30), $text('fa8488a')], [
    'width' => $size(33.33, '%'), 'padding' => $pad(30, 28, 30, 28), 'gap' => $size(18), 'background' => $bg('#f3f9ff'), 'border-radius' => $size(20),
], [], ['width' => $size(100, '%')]);
$perspective_two = $container('perspective-two', [$icon('celebrations', 'award', '#25377b', 30), $text('ad88002')], [
    'width' => $size(33.33, '%'), 'padding' => $pad(30, 28, 30, 28), 'gap' => $size(18), 'background' => $bg('#fff8e4'), 'border-radius' => $size(20),
], [], ['width' => $size(100, '%')]);
$perspective_three = $container('perspective-three', [$icon('updates', 'calendar-alt', '#25377b', 30), $text('50ad5bf')], [
    'width' => $size(33.33, '%'), 'padding' => $pad(30, 28, 30, 28), 'gap' => $size(18), 'background' => $bg('#fdc800'), 'border-radius' => $size(20),
], [], ['width' => $size(100, '%')]);
$perspective = $section('perspective', [$inner('perspective-inner', [$perspective_one, $perspective_two, $perspective_three], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'stretch'), 'gap' => $size(20),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)])], '#ffffff');

$moments_header = $container('moments-header', [$icon('moments', 'images', '#25377b', 38), $text('fd9254d', '#001a39', 30, '800')], [
    'width' => $size(100, '%'), 'max-width' => $size(820), 'align-items' => $typed('string', 'center'), 'text-align' => $typed('string', 'center'), 'gap' => $size(16),
]);
$moments_list = $container('moments-list', [$list('c82c99e')], ['width' => $size(100, '%')]);
$moments = $section('moments', [$inner('moments-inner', [$moments_header, $moments_list], [
    'align-items' => $typed('string', 'center'), 'gap' => $size(36),
])], '#fff8e4', $assets['path-warm']);

$community_copy = $container('community-copy', [
    $icon('community', 'heart', '#fdc800', 35), $text('959ed42', '#ffffff', 17), $text('fc96993', '#eaf3ff', 17), $text('c847674', '#ffffff', 17, '700'),
], [
    'width' => $size(58, '%'), 'gap' => $size(20), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$community_visual = $container('community-visual', [$image('healthcare-community', 441, 'Healthcare training at SABER College', 430, 300)], [
    'width' => $size(42, '%'), 'padding' => $pad(18, 18, 0, 18), 'justify-content' => $typed('string', 'flex-end'),
], [], ['display' => $typed('string', 'none')]);
$community = $section('community', [$inner('community-inner', [$community_copy, $community_visual], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'), 'gap' => $size(46), 'padding' => $pad(76, 24, 0, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(28), 'padding' => $pad(54, 20, 0, 20)])], '#082b55', $assets['path-navy']);

$cta_statement = $container('cta-statement', [$text('b43dd4c', '#001a39', 22, '800')], [
    'width' => $size(58, '%'),
], [], ['width' => $size(100, '%')]);
$cta_action = $container('cta-action', [$widgets['40f5634']], [
    'width' => $size(42, '%'), 'align-items' => $typed('string', 'center'), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%'), 'align-items' => $typed('string', 'flex-start')]);
$cta_top = $container('cta-top', [$cta_statement, $cta_action], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'), 'gap' => $size(36),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(24)]);
$cta_close = $container('cta-close', [$text('0d7a72b', '#29435f', 17)], [
    'width' => $size(100, '%'), 'padding' => $pad(24, 28, 24, 28), 'background' => $bg('#ffffff'), 'border-radius' => $size(18),
]);
$cta = $section('cta', [$inner('cta-inner', [$cta_top, $cta_close], ['gap' => $size(32)])], '#fdc800');

$new_data = [$breadcrumbs, $hero, $foundation, $journey, $perspective, $moments, $community, $cta];
$widgets = [];
$collect($new_data);
foreach ($original_content as $id => $fields) {
    foreach ($fields as $field => $value) {
        if (($widgets[$id]['settings'][$field] ?? null) !== $value) { WP_CLI::error("Content preservation failed for $id:$field."); }
    }
}

$page_settings = get_post_meta($post_id, '_elementor_page_settings', true) ?: [];
$page_css = <<<'CSS'
/* Gallery: Design System 0.2 page polish. */
.elementor-863 .rank-math-breadcrumb p { margin: 0; }
.elementor-863 .elementor-widget-text-editor p { margin-top: 0; }
.elementor-863 .elementor-widget-text-editor p:last-child { margin-bottom: 0; }
.elementor-863 a:focus-visible { outline: 3px solid #005fbd; outline-offset: 5px; box-shadow: 0 0 0 7px #fff; border-radius: 3px; }
.elementor-863 .e-gallery-hero-visual img,
.elementor-863 .e-gallery-foundation-image img,
.elementor-863 .e-gallery-journey-visual img { display: block; }
.elementor-863 .e-gallery-community-visual img { display: block; object-fit: contain !important; object-position: center bottom !important; }
.elementor-863 .e-gallery-moments-list .elementor-icon-list-items {
  display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; margin: 0; padding: 0; counter-reset: gallery-moment;
}
.elementor-863 .e-gallery-moments-list .elementor-icon-list-item {
  counter-increment: gallery-moment; position: relative; display: flex; align-items: flex-start;
  min-height: 170px; margin: 0; padding: 68px 25px 26px; background: #fff;
  border: 1px solid #eadfb9; border-radius: 18px; box-shadow: 0 12px 30px rgba(8,43,85,.05);
}
.elementor-863 .e-gallery-moments-list .elementor-icon-list-item::before {
  content: counter(gallery-moment, decimal-leading-zero); position: absolute; left: 25px; top: 22px;
  display: grid; place-items: center; width: 38px; height: 38px; border-radius: 50%;
  background: #f3f9ff; border: 1px solid #fdc800; color: #082b55; font: 800 13px/1 Nunito, Arial, sans-serif;
}
.elementor-863 .e-gallery-moments-list .elementor-icon-list-icon { margin-top: 4px; }
.elementor-863 .e-gallery-moments-list .elementor-icon-list-text { padding-inline-start: 10px; }
.elementor-863 .e-gallery-cta-action .elementor-widget-text-editor p { margin: 0; }
.elementor-863 .e-gallery-cta-action a {
  display: inline-flex; align-items: center; justify-content: center; min-height: 54px; padding: 14px 24px;
  border-radius: 8px; background: #082b55; color: #fff !important; font: 800 16px/1.2 Nunito, Arial, sans-serif;
  text-decoration: none; box-shadow: 0 10px 24px rgba(8,43,85,.18); transition: transform .2s ease, background .2s ease;
}
.elementor-863 .e-gallery-cta-action a:hover { background: #25377b; transform: translateY(-2px); }
@media (max-width: 1024px) {
  .elementor-863 .e-gallery-moments-list .elementor-icon-list-items { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 767px) {
  .elementor-863 .e-gallery-moments-list .elementor-icon-list-items { grid-template-columns: 1fr; }
  .elementor-863 .e-gallery-moments-list .elementor-icon-list-item { min-height: 0; }
}
CSS;
$existing_css = $page_settings['custom_css'] ?? '';
$marker = '/* Gallery: Design System 0.2 page polish. */';
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
foreach ($widgets as $widget) { if (!empty($widget['widgetType'])) { $widget_types[] = $widget['widgetType']; } }
$report = [
    'page_id' => $post_id, 'url' => get_permalink($post_id), 'backup' => $backup,
    'content_fields_preserved' => count($original_content), 'protected_templates_unchanged' => $protected_unchanged,
    'design_assets' => $assets, 'images' => $image_ids,
    'native_widget_types' => array_values(array_unique($widget_types)), 'elementor_widgets' => count($widgets),
];
file_put_contents('/tmp/gallery-redesign-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('Gallery redesigned. Original copy, list and Instagram link are preserved.');
