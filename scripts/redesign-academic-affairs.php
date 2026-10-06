<?php
/**
 * Apply SABER Design System 0.2 to Academic Affairs (page 896).
 * Existing copy, list items, Rank Math data and protected templates are preserved.
 */
if (!defined('ABSPATH')) { exit(1); }

$post_id = 896;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'academic-affairs' || (int) $page->post_parent !== 34) {
    WP_CLI::error('Page 896 is not the Academic Affairs child of Student Services.');
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
    'a837d66' => ['shortcode'], 'fd18c46' => ['title'], '43363de' => ['editor'],
    '3fa4368' => ['icon_list'], '025db21' => ['editor'],
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
$backup = '/tmp/academic-affairs-896-before-' . $stamp . '.json';
$backup_json = wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (false === file_put_contents($backup, $backup_json)) {
    WP_CLI::error('Could not create backup. Nothing was changed.');
}
file_put_contents('/tmp/academic-affairs-896-before-latest.json', $backup_json);
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
$image_ids = [1317, 1699];
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
    $id = substr(md5('saber-aa-' . $name), 0, 7);
    $class = 'e-aa-' . $name;
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
        'padding' => $pad(72, 24, 76, 24), 'gap' => $size(28),
    ], $extra), $tablet, array_merge(['padding' => $pad(48, 20, 52, 20)], $mobile));
};
$section = static function ($name, $children, $color, $attachment = 0) use ($container, $size, $typed, $bg) {
    return $container($name, $children, [
        'width' => $size(100, '%'), 'align-items' => $typed('string', 'center'),
        'background' => $bg($color, $attachment),
    ]);
};
$icon = static function ($name, $glyph, $color = '#25377b', $font_size = 30) {
    return [
        'id' => substr(md5('aa-icon-' . $name), 0, 7), 'elType' => 'widget',
        'widgetType' => 'icon', 'elements' => [],
        'settings' => [
            'selected_icon' => ['value' => 'fas fa-' . $glyph, 'library' => 'fa-solid'],
            'view' => 'default', 'primary_color' => $color, 'align' => 'left',
            'size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
            '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
    ];
};
$image = static function ($name, $attachment_id, $alt, $height) {
    return [
        'id' => substr(md5('aa-image-' . $name), 0, 7), 'elType' => 'widget',
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
$heading = static function ($id, $desktop_size, $mobile_size, $color = '#001a39', $tag = '') use (&$widgets) {
    $widget = $widgets[$id];
    unset($widget['settings']['__globals__']);
    if ($tag !== '') { $widget['settings']['header_size'] = $tag; }
    $widget['settings'] = array_merge($widget['settings'], [
        'title_color' => $color, 'typography_typography' => 'custom',
        'typography_font_family' => 'Nunito', 'typography_font_weight' => '800',
        'typography_font_size' => ['unit' => 'px', 'size' => $desktop_size, 'sizes' => []],
        'typography_font_size_mobile' => ['unit' => 'px', 'size' => $mobile_size, 'sizes' => []],
        'typography_line_height' => ['unit' => 'em', 'size' => 1.1, 'sizes' => []],
        'typography_letter_spacing' => ['unit' => 'px', 'size' => -0.5, 'sizes' => []],
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
        'typography_line_height' => ['unit' => 'em', 'size' => 1.7, 'sizes' => []],
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
        'text_indent' => ['unit' => 'px', 'size' => 10, 'sizes' => []],
        'typography_typography' => 'custom', 'typography_font_family' => 'Nunito',
        'typography_font_weight' => '700',
        'typography_font_size' => ['unit' => 'px', 'size' => 17, 'sizes' => []],
        'typography_font_size_mobile' => ['unit' => 'px', 'size' => 16, 'sizes' => []],
        'typography_line_height' => ['unit' => 'em', 'size' => 1.55, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ]);
    return $widget;
};

$breadcrumbs = $section('breadcrumbs', [$inner('breadcrumbs-inner', [$widgets['a837d66']], [
    'padding' => $pad(20, 24, 20, 24), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'),
], [], ['padding' => $pad(68, 20, 18, 20)])], '#ffffff');

$rule = [
    'id' => 'aaffrule', 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [],
    'settings' => [
        'color' => '#fdc800', 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []],
        'width' => ['unit' => 'px', 'size' => 72, 'sizes' => []],
        'gap' => ['unit' => 'px', 'size' => 5, 'sizes' => []], 'align' => 'left',
    ],
];
$hero_copy = $container('hero-copy', [
    $rule, $heading('fd18c46', 49, 36, '#001a39', 'h1'), $text('43363de', '#29435f', 18),
], [
    'width' => $size(57, '%'), 'gap' => $size(20), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$hero_visual = $container('hero-visual', [
    $image('academic-leadership', 1317, 'Healthcare education leader at SABER College', 470),
], [
    'width' => $size(43, '%'), 'min-height' => $size(470), 'overflow' => $typed('string', 'hidden'),
    'border-radius' => $size(28), 'background' => $bg('#ffffff'),
], [], ['width' => $size(100, '%'), 'min-height' => $size(310)]);
$hero = $section('hero', [$inner('hero-inner', [$hero_copy, $hero_visual], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(50), 'padding' => $pad(46, 24, 66, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#f3f9ff', $assets['path-light']);

$responsibilities_header = $container('responsibilities-mark', [
    $icon('academic', 'graduation-cap', '#25377b', 38),
], [
    'width' => $size(100, '%'), 'align-items' => $typed('string', 'center'),
]);
$responsibilities_list = $container('responsibilities-list', [$list('3fa4368')], [
    'width' => $size(100, '%'),
]);
$responsibilities = $section('responsibilities', [$inner('responsibilities-inner', [
    $responsibilities_header, $responsibilities_list,
], [
    'gap' => $size(30), 'padding' => $pad(70, 24, 78, 24),
])], '#fff8e4', $assets['path-warm']);

$campus_visual = $container('campus-visual', [
    $image('campus', 1699, 'SABER College campus in Miami', 360),
], [
    'width' => $size(55, '%'), 'overflow' => $typed('string', 'hidden'),
    'border-radius' => $size(24), 'background' => $bg('#dfeefa'),
], [], ['width' => $size(100, '%')]);
$invitation = $container('invitation', [
    $icon('visit', 'map-marker-alt', '#25377b', 36), $text('025db21', '#001a39', 20, '800'),
], [
    'width' => $size(45, '%'), 'padding' => $pad(34, 36, 34, 36), 'gap' => $size(18),
    'justify-content' => $typed('string', 'center'), 'background' => $bg('#fdc800'),
    'border-radius' => $size(20),
], [], ['width' => $size(100, '%'), 'padding' => $size(26)]);
$closing = $section('closing', [$inner('closing-inner', [$campus_visual, $invitation], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(42), 'padding' => $pad(72, 24, 78, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(24)])], '#082b55', $assets['path-navy']);

$new_data = [$breadcrumbs, $hero, $responsibilities, $closing];
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
/* Academic Affairs: Design System 0.2 page polish. */
.elementor-896 .rank-math-breadcrumb p { margin: 0; }
.elementor-896 .elementor-widget-text-editor p { margin-top: 0; }
.elementor-896 .elementor-widget-text-editor p:last-child { margin-bottom: 0; }
.elementor-896 a:focus-visible { outline: 3px solid #005fbd; outline-offset: 5px; box-shadow: 0 0 0 7px #fff; border-radius: 3px; }
.elementor-896 .e-aa-hero-visual img,
.elementor-896 .e-aa-campus-visual img { display: block; }
.elementor-896 .e-aa-responsibilities-list .elementor-icon-list-items {
  display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px;
  counter-reset: academic-duty; margin: 0; padding: 0;
}
.elementor-896 .e-aa-responsibilities-list .elementor-icon-list-item {
  counter-increment: academic-duty; position: relative; display: flex; align-items: flex-start;
  min-height: 210px; margin: 0; padding: 78px 26px 28px;
  background: #fff; border: 1px solid #e4dbc4; border-radius: 18px;
  box-shadow: 0 12px 32px rgba(8,43,85,.045);
}
.elementor-896 .e-aa-responsibilities-list .elementor-icon-list-item::before {
  content: counter(academic-duty, decimal-leading-zero); position: absolute; left: 26px; top: 24px;
  display: grid; place-items: center; width: 42px; height: 42px; border-radius: 50%;
  background: #f3f9ff; border: 1px solid #fdc800; color: #082b55;
  font: 800 14px/1 Nunito, Arial, sans-serif;
}
.elementor-896 .e-aa-responsibilities-list .elementor-icon-list-icon { margin-top: 4px; }
.elementor-896 .e-aa-responsibilities-list .elementor-icon-list-text { padding-inline-start: 10px; }
@media (max-width: 767px) {
  .elementor-896 .e-aa-responsibilities-list .elementor-icon-list-items { grid-template-columns: 1fr; }
  .elementor-896 .e-aa-responsibilities-list .elementor-icon-list-item { min-height: 0; }
}
CSS;
$existing_css = $page_settings['custom_css'] ?? '';
$marker = '/* Academic Affairs: Design System 0.2 page polish. */';
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
file_put_contents('/tmp/academic-affairs-redesign-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('Academic Affairs redesigned. Original content and list items are preserved.');
