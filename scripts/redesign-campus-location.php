<?php
/**
 * Apply SABER Design System 0.2 to Campus Location (page 861).
 * Existing copy, contact data, map embed, Rank Math data and global templates are preserved.
 */
if (!defined('ABSPATH')) { exit(1); }

$post_id = 861;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'campus-location' || (int) $page->post_parent !== 0) {
    WP_CLI::error('Page 861 is not the root Campus Location page.');
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
    '2f40154' => ['shortcode'], '0bdab84' => ['title'], 'd1de702' => ['title'],
    '9ca5e4c' => ['editor'], '753b6eb' => ['icon_list'], '0d43eb5' => ['editor'],
    'c937775' => ['icon_list'], 'fd6bced' => ['title'], 'eae607b' => ['editor'],
    '4a46000' => ['icon_list'], 'e6706dc' => ['editor'], 'a0f069c' => ['icon_list'],
    'e199f5a' => ['html'],
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
$backup = '/tmp/campus-location-861-before-' . $stamp . '.json';
$backup_json = wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (false === file_put_contents($backup, $backup_json)) { WP_CLI::error('Could not create backup. Nothing was changed.'); }
file_put_contents('/tmp/campus-location-861-before-latest.json', $backup_json);
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
$image_id = 1699;
if (!wp_get_attachment_url($image_id)) { WP_CLI::error('Campus image 1699 is unavailable.'); }

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
    $id = substr(md5('saber-campus-' . $name), 0, 7);
    $class = 'e-campus-' . $name;
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
        'id' => substr(md5('campus-icon-' . $name), 0, 7), 'elType' => 'widget',
        'widgetType' => 'icon', 'elements' => [],
        'settings' => [
            'selected_icon' => ['value' => 'fas fa-' . $glyph, 'library' => 'fa-solid'],
            'view' => 'default', 'primary_color' => $color, 'align' => 'left',
            'size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
            '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
    ];
};
$image = static function ($attachment_id) {
    return [
        'id' => 'campimg', 'elType' => 'widget', 'widgetType' => 'image', 'elements' => [],
        'settings' => [
            'image' => ['id' => $attachment_id, 'url' => wp_get_attachment_url($attachment_id), 'alt' => 'SABER College campus in Miami', 'source' => 'library'],
            'image_size' => 'full', 'width' => ['unit' => '%', 'size' => 100, 'sizes' => []],
            'height' => ['unit' => 'px', 'size' => 500, 'sizes' => []],
            'height_mobile' => ['unit' => 'px', 'size' => 300, 'sizes' => []],
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
        'typography_line_height' => ['unit' => 'em', 'size' => 1.65, 'sizes' => []],
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
        'space_between' => ['unit' => 'px', 'size' => 15, 'sizes' => []],
        'text_typography_typography' => 'custom', 'text_typography_font_family' => 'Nunito',
        'text_typography_font_weight' => '500',
        'text_typography_font_size' => ['unit' => 'px', 'size' => 16, 'sizes' => []],
        'text_typography_font_size_mobile' => ['unit' => 'px', 'size' => 15, 'sizes' => []],
        'text_typography_line_height' => ['unit' => 'em', 'size' => 1.55, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ]);
    return $widget;
};

$breadcrumbs = $section('breadcrumbs', [$inner('breadcrumbs-inner', [$widgets['2f40154']], [
    'padding' => $pad(20, 24, 20, 24), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'),
], [], ['padding' => $pad(68, 20, 18, 20)])], '#ffffff');

$rule = [
    'id' => 'camprule', 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [],
    'settings' => [
        'color' => '#fdc800', 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []],
        'width' => ['unit' => 'px', 'size' => 72, 'sizes' => []],
        'gap' => ['unit' => 'px', 'size' => 5, 'sizes' => []], 'align' => 'left',
    ],
];
$hero_copy = $container('hero-copy', [
    $rule, $heading('0bdab84', 52, 37, '#001a39', 'h1'),
    $heading('d1de702', 27, 23, '#25377b', 'h2'), $text('9ca5e4c', '#29435f', 19, '700'),
], [
    'width' => $size(53, '%'), 'gap' => $size(18), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$hero_visual = $container('hero-visual', [$image($image_id)], [
    'width' => $size(47, '%'), 'min-height' => $size(500), 'overflow' => $typed('string', 'hidden'),
    'border-radius' => $size(28), 'background' => $bg('#dfeefa'),
], [], ['width' => $size(100, '%'), 'min-height' => $size(300)]);
$hero = $section('hero', [$inner('hero-inner', [$hero_copy, $hero_visual], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(50), 'padding' => $pad(48, 24, 72, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#f3f9ff', $assets['path-light']);

$location_card = $container('location-card', [
    $icon('address', 'map-marker-alt', '#25377b', 38), $list('753b6eb'),
], [
    'width' => $size(40, '%'), 'padding' => $pad(36, 38, 36, 38), 'gap' => $size(24),
    'justify-content' => $typed('string', 'center'), 'background' => $bg('#fdc800'),
    'border-radius' => $size(22),
], [], ['width' => $size(100, '%'), 'padding' => $pad(28, 26, 28, 26)]);
$map_card = $container('map-card', [$widgets['e199f5a']], [
    'width' => $size(60, '%'), 'min-height' => $size(480), 'overflow' => $typed('string', 'hidden'),
    'background' => $bg('#ffffff'), 'border-radius' => $size(22),
], [], ['width' => $size(100, '%'), 'min-height' => $size(390)]);
$location = $section('location', [$inner('location-inner', [$location_card, $map_card], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'stretch'),
    'gap' => $size(24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(20)])], '#ffffff');

$learn_header = $container('learn-header', [
    $icon('learn', 'route', '#25377b', 38), $text('0d43eb5', '#001a39', 32, '800'),
], [
    'width' => $size(100, '%'), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'), 'gap' => $size(16),
]);
$learn_list = $container('learn-list', [$list('c937775')], ['width' => $size(100, '%')]);
$learn = $section('learn', [$inner('learn-inner', [$learn_header, $learn_list], [
    'align-items' => $typed('string', 'center'), 'gap' => $size(36),
])], '#fff8e4', $assets['path-warm']);

$admissions_header = $container('admissions-header', [
    $icon('admissions', 'address-card', '#fdc800', 38),
    $heading('fd6bced', 42, 32, '#ffffff', 'h2'),
], [
    'width' => $size(100, '%'), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'), 'gap' => $size(16),
]);
$healthcare_card = $container('healthcare-card', [
    $text('eae607b', '#001a39', 24, '800'), $list('4a46000'),
], [
    'width' => $size(50, '%'), 'padding' => $pad(36, 38, 36, 38), 'gap' => $size(20),
    'background' => $bg('#ffffff'), 'border-radius' => $size(22),
], [], ['width' => $size(100, '%'), 'padding' => $pad(28, 26, 28, 26)]);
$vesl_card = $container('vesl-card', [
    $text('e6706dc', '#001a39', 24, '800'), $list('a0f069c'),
], [
    'width' => $size(50, '%'), 'padding' => $pad(36, 38, 36, 38), 'gap' => $size(20),
    'background' => $bg('#fdc800'), 'border-radius' => $size(22),
], [], ['width' => $size(100, '%'), 'padding' => $pad(28, 26, 28, 26)]);
$admissions_cards = $container('admissions-cards', [$healthcare_card, $vesl_card], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'stretch'), 'gap' => $size(24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)]);
$admissions = $section('admissions', [$inner('admissions-inner', [$admissions_header, $admissions_cards], [
    'align-items' => $typed('string', 'center'), 'gap' => $size(40),
    'padding' => $pad(78, 24, 84, 24),
])], '#082b55', $assets['path-navy']);

$new_data = [$breadcrumbs, $hero, $location, $learn, $admissions];
$widgets = [];
$collect($new_data);
foreach ($original_content as $id => $fields) {
    foreach ($fields as $field => $value) {
        if (($widgets[$id]['settings'][$field] ?? null) !== $value) { WP_CLI::error("Content preservation failed for $id:$field."); }
    }
}

$page_settings = get_post_meta($post_id, '_elementor_page_settings', true) ?: [];
$page_css = <<<'CSS'
/* Campus Location: Design System 0.2 page polish. */
.elementor-861 .rank-math-breadcrumb p { margin: 0; }
.elementor-861 .elementor-widget-text-editor p { margin-top: 0; }
.elementor-861 .elementor-widget-text-editor p:last-child { margin-bottom: 0; }
.elementor-861 a:focus-visible { outline: 3px solid #005fbd; outline-offset: 5px; box-shadow: 0 0 0 7px #fff; border-radius: 3px; }
.elementor-861 .e-campus-hero-visual img { display: block; }
.elementor-861 .e-campus-map-card .elementor-widget-html,
.elementor-861 .e-campus-map-card .elementor-widget-container,
.elementor-861 .e-campus-map-card iframe { width: 100%; height: 100%; min-height: 480px; display: block; }
.elementor-861 .e-campus-location-card .elementor-icon-list-item { align-items: flex-start; }
.elementor-861 .e-campus-location-card strong,
.elementor-861 .e-campus-healthcare-card strong,
.elementor-861 .e-campus-vesl-card strong { color: #001a39; font-weight: 800; }
.elementor-861 .e-campus-learn-list .elementor-icon-list-items {
  display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; margin: 0; padding: 0;
}
.elementor-861 .e-campus-learn-list .elementor-icon-list-item {
  display: flex; align-items: flex-start; min-height: 170px; margin: 0; padding: 32px 30px;
  background: #fff; border: 1px solid #eadfb9; border-radius: 18px;
  box-shadow: 0 12px 30px rgba(8,43,85,.05);
}
.elementor-861 .e-campus-learn-list .elementor-icon-list-icon { margin-top: 4px; }
.elementor-861 .e-campus-learn-list .elementor-icon-list-text { padding-inline-start: 10px; }
.elementor-861 .e-campus-learn-list strong { display: block; margin-bottom: 7px; color: #001a39; font-weight: 800; }
@media (max-width: 767px) {
  .elementor-861 .e-campus-map-card .elementor-widget-html,
  .elementor-861 .e-campus-map-card .elementor-widget-container,
  .elementor-861 .e-campus-map-card iframe { min-height: 390px; }
  .elementor-861 .e-campus-learn-list .elementor-icon-list-items { grid-template-columns: 1fr; }
  .elementor-861 .e-campus-learn-list .elementor-icon-list-item { min-height: 0; padding: 26px 23px; }
}
CSS;
$existing_css = $page_settings['custom_css'] ?? '';
$marker = '/* Campus Location: Design System 0.2 page polish. */';
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
    'design_assets' => $assets, 'image' => $image_id,
    'native_widget_types' => array_values(array_unique($widget_types)), 'elementor_widgets' => count($widgets),
];
file_put_contents('/tmp/campus-location-redesign-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('Campus Location redesigned. Original copy, contacts and map are preserved.');
