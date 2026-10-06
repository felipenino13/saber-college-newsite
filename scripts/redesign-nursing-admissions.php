<?php
/**
 * Apply SABER Design System 0.2 to Nursing Admissions (page 877).
 * Existing copy, links, Rank Math data and protected templates are preserved.
 */
if (!defined('ABSPATH')) { exit(1); }

$post_id = 877;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'nursing-admissions') {
    WP_CLI::error('Page 877 is not Nursing Admissions.');
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
    'd7f3000' => ['shortcode'],
    'd0eeed3' => ['title'],
    '5e4d671' => ['title'],
    '06ff86b' => ['editor'],
    '2681e7b' => ['editor'],
    '8c93c3d' => ['title'],
    '85abbc6' => ['editor'],
    '2999fe5' => ['title'],
    'caa7f81' => ['editor'],
    '8bad809' => ['title'],
    'cb237b4' => ['editor'],
    '89b8e2f' => ['title'],
    '31e6daf' => ['icon_list'],
];
$original_content = [];
foreach ($content_fields as $id => $fields) {
    if (!isset($widgets[$id])) { WP_CLI::error("Original widget $id is missing. Nothing was changed."); }
    foreach ($fields as $field) { $original_content[$id][$field] = $widgets[$id]['settings'][$field] ?? null; }
}

$snapshot = [
    'page_id' => $post_id,
    'created_utc' => gmdate('c'),
    'post' => (array) $page,
    'meta' => get_post_meta($post_id),
    'protected_hashes' => [],
];
foreach ([7, 16, 53] as $protected) {
    $snapshot['protected_hashes'][$protected] = hash('sha256', get_post_meta($protected, '_elementor_data', true));
}
$backup = '/tmp/nursing-admissions-877-before-' . gmdate('Ymd-His') . '.json';
if (false === file_put_contents($backup, wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) {
    WP_CLI::error('Could not create backup. Nothing was changed.');
}
WP_CLI::line('BACKUP=' . $backup);

$assets = [];
foreach (['path-light', 'path-warm'] as $asset) {
    $existing = get_posts([
        'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1,
        'fields' => 'ids', 'meta_key' => '_saber_design_asset', 'meta_value' => $asset . '-v02',
    ]);
    if (!$existing) { WP_CLI::error("Design asset $asset-v02 is not registered."); }
    $assets[$asset] = $existing[0];
}
if (!wp_get_attachment_url(1317)) { WP_CLI::error('Nursing hero image 1317 is unavailable.'); }

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
    $id = substr(md5('saber-nursing-admissions-' . $name), 0, 7);
    $class = 'e-na-' . $name;
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
        'id' => substr(md5('na-icon-' . $name), 0, 7), 'elType' => 'widget',
        'widgetType' => 'icon', 'elements' => [],
        'settings' => [
            'selected_icon' => ['value' => 'fas fa-' . $glyph, 'library' => 'fa-solid'],
            'view' => 'default', 'primary_color' => $color, 'align' => 'left',
            'size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
            '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
    ];
};
$heading = static function ($id, $desktop_size, $mobile_size, $color = '#001a39') use (&$widgets) {
    $widget = $widgets[$id];
    unset($widget['settings']['__globals__']);
    $widget['settings'] = array_merge($widget['settings'], [
        'title_color' => $color, 'typography_typography' => 'custom',
        'typography_font_family' => 'Nunito', 'typography_font_weight' => '800',
        'typography_font_size' => ['unit' => 'px', 'size' => $desktop_size, 'sizes' => []],
        'typography_font_size_mobile' => ['unit' => 'px', 'size' => $mobile_size, 'sizes' => []],
        'typography_line_height' => ['unit' => 'em', 'size' => 1.12, 'sizes' => []],
        'typography_letter_spacing' => ['unit' => 'px', 'size' => -0.6, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ]);
    return $widget;
};
$text_widget = static function ($id, $color = '#29435f', $font_size = 17) use (&$widgets) {
    $widget = $widgets[$id];
    unset($widget['settings']['__globals__']);
    $widget['settings'] = array_merge($widget['settings'], [
        'text_color' => $color, 'link_color' => '#005fbd', 'link_hover_color' => '#003f7d',
        'typography_typography' => 'custom', 'typography_font_family' => 'Nunito',
        'typography_font_weight' => '500',
        'typography_font_size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
        'typography_font_size_mobile' => ['unit' => 'px', 'size' => 16, 'sizes' => []],
        'typography_line_height' => ['unit' => 'em', 'size' => 1.68, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ]);
    return $widget;
};

$breadcrumbs = $section('breadcrumbs', [$inner('breadcrumbs-inner', [$widgets['d7f3000']], [
    'padding' => $pad(20, 24, 20, 24), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'),
], [], ['padding' => $pad(68, 20, 18, 20)])], '#ffffff');

$rule = [
    'id' => 'narule1', 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [],
    'settings' => [
        'color' => '#fdc800', 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []],
        'width' => ['unit' => 'px', 'size' => 72, 'sizes' => []],
        'gap' => ['unit' => 'px', 'size' => 5, 'sizes' => []], 'align' => 'left',
    ],
];
$hero_title = $heading('d0eeed3', 42, 35);
$hero_copy = $container('hero-copy', [$rule, $hero_title], [
    'width' => $size(65, '%'), 'gap' => $size(18), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$hero_image = [
    'id' => 'naphoto', 'elType' => 'widget', 'widgetType' => 'image', 'elements' => [],
    'settings' => [
        'image' => ['id' => 1317, 'url' => wp_get_attachment_url(1317), 'alt' => 'SABER College nursing student', 'source' => 'library'],
        'image_size' => 'large', 'width' => ['unit' => '%', 'size' => 100, 'sizes' => []],
        'height' => ['unit' => 'px', 'size' => 440, 'sizes' => []],
        'height_mobile' => ['unit' => 'px', 'size' => 330, 'sizes' => []],
        'object-fit' => 'contain', 'object-position' => 'center bottom',
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '-20', 'left' => '0', 'isLinked' => false],
    ],
];
$hero_visual = $container('hero-visual', [$hero_image], [
    'width' => $size(35, '%'), 'min-height' => $size(430),
    'justify-content' => $typed('string', 'flex-end'), 'align-items' => $typed('string', 'center'),
    'background' => $bg('#fff8e4'), 'border-radius' => $size(28), 'overflow' => $typed('string', 'hidden'),
], [], ['width' => $size(100, '%'), 'min-height' => $size(310)]);
$hero = $section('hero', [$inner('hero-inner', [$hero_copy, $hero_visual], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(36), 'padding' => $pad(42, 24, 62, 24),
], [], [
    'flex-direction' => $typed('string', 'column'), 'align-items' => $typed('string', 'stretch'),
    'gap' => $size(28), 'padding' => $pad(32, 20, 44, 20),
])], '#f3f9ff', $assets['path-light']);

$intro = $container('intro-card', [
    $container('intro-title', [$heading('5e4d671', 36, 30)], ['width' => $size(31, '%')], [], ['width' => $size(100, '%')]),
    $container('intro-copy', [$text_widget('06ff86b', '#29435f', 18)], ['width' => $size(69, '%')], [], ['width' => $size(100, '%')]),
], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'flex-start'), 'gap' => $size(44),
    'padding' => $pad(36, 38, 36, 38), 'background' => $bg('#ffffff'),
    'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#dce5ec'),
    'border-width' => $size(1), 'border-radius' => $size(20),
], [], [
    'flex-direction' => $typed('string', 'column'), 'gap' => $size(18), 'padding' => $size(24),
]);
$requirements = $text_widget('2681e7b', '#29435f', 17);
$admissions = $section('admissions', [$inner('admissions-inner', [$intro, $container('requirements', [$requirements], [
    'width' => $size(100, '%'), 'max-width' => $size(1040), 'align-self' => $typed('string', 'center'),
])], ['gap' => $size(34)])], '#fffcf4', $assets['path-warm']);

$service_specs = [
    ['8c93c3d', '85abbc6', 'credit-card', true, '#082b55'],
    ['2999fe5', 'caa7f81', 'hands-helping', false, '#ffffff'],
    ['8bad809', 'cb237b4', 'briefcase', false, '#f3f9ff'],
];
$service_cards = [];
foreach ($service_specs as $index => [$title_id, $copy_id, $glyph, $dark, $color]) {
    $service_cards[] = $container('service-' . ($index + 1), [
        $icon('service-' . $index, $glyph, $dark ? '#fdc800' : '#25377b', 31),
        $heading($title_id, 25, 23, $dark ? '#ffffff' : '#001a39'),
        $text_widget($copy_id, $dark ? '#ffffff' : '#29435f', 16),
    ], [
        'width' => $size(31.8, '%'), 'padding' => $size(30), 'gap' => $size(20),
        'background' => $bg($color), 'border-style' => $typed('string', 'solid'),
        'border-color' => $typed('color', $dark ? '#082b55' : '#dce5ec'),
        'border-width' => $size(1), 'border-radius' => $size(18),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(25)], [
        'border-color' => $typed('color', $dark ? '#25377b' : '#9eb9d2'),
    ]);
}
$services = $section('services', [$inner('services-inner', [$container('service-grid', $service_cards, [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'stretch'), 'gap' => $size(24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)])], [
    'padding' => $pad(72, 24, 78, 24),
])], '#ffffff');

$clinical_title = $heading('89b8e2f', 36, 29, '#ffffff');
$clinical_list = $widgets['31e6daf'];
unset($clinical_list['settings']['__globals__']);
$clinical_list['settings'] = array_merge($clinical_list['settings'], [
    'icon_color' => '#fdc800', 'text_color' => '#ffffff',
    'typography_typography' => 'custom', 'typography_font_family' => 'Nunito',
    'typography_font_weight' => '600',
    'typography_font_size' => ['unit' => 'px', 'size' => 16, 'sizes' => []],
    'typography_font_size_mobile' => ['unit' => 'px', 'size' => 15, 'sizes' => []],
    'typography_line_height' => ['unit' => 'em', 'size' => 1.55, 'sizes' => []],
    'space_between' => ['unit' => 'px', 'size' => 16, 'sizes' => []],
    '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
]);
$clinical = $section('clinical', [$inner('clinical-inner', [
    $container('clinical-title', [$clinical_title], ['width' => $size(34, '%')], [], ['width' => $size(100, '%')]),
    $container('clinical-list', [$clinical_list], ['width' => $size(66, '%')], [], ['width' => $size(100, '%')]),
], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'flex-start'),
    'gap' => $size(56), 'padding' => $pad(72, 24, 78, 24),
], [], [
    'flex-direction' => $typed('string', 'column'), 'gap' => $size(28),
])], '#082b55');

$new_data = [$breadcrumbs, $hero, $admissions, $services, $clinical];
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
/* Nursing Admissions: SABER Design System 0.2 page polish. */
.elementor-877 .rank-math-breadcrumb p { margin: 0; }
.elementor-877 .elementor-widget-text-editor p { margin-top: 0; }
.elementor-877 .e-na-intro-copy .elementor-widget-text-editor p:last-child,
.elementor-877 .e-na-service-grid .elementor-widget-text-editor p:last-child { margin-bottom: 0; }
.elementor-877 a:focus-visible { outline: 3px solid #005fbd; outline-offset: 5px; box-shadow: 0 0 0 7px #fff; border-radius: 3px; }
.elementor-877 .e-na-hero-visual img { display: block; }
.elementor-877 .e-na-requirements .elementor-widget-text-editor > ol {
  list-style: none; counter-reset: admissions-step; display: grid; gap: 16px; margin: 0; padding: 0;
}
.elementor-877 .e-na-requirements .elementor-widget-text-editor > ol > li {
  counter-increment: admissions-step; position: relative; margin: 0; padding: 25px 30px 25px 86px;
  background: #fff; border: 1px solid #dce5ec; border-radius: 16px;
  box-shadow: 0 12px 34px rgba(8,43,85,.045);
}
.elementor-877 .e-na-requirements .elementor-widget-text-editor > ol > li::before {
  content: counter(admissions-step, decimal-leading-zero); position: absolute; left: 24px; top: 22px;
  display: grid; place-items: center; width: 42px; height: 42px; border-radius: 50%;
  background: #fff8e4; color: #082b55; font: 800 15px/1 Nunito, Arial, sans-serif;
  border: 1px solid #fdc800;
}
.elementor-877 .e-na-service-grid > .e-con { height: auto; }
.elementor-877 .e-na-clinical-list .elementor-icon-list-items {
  display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin: 0;
}
.elementor-877 .e-na-clinical-list .elementor-icon-list-item {
  align-items: flex-start; margin: 0; padding: 18px 20px; border: 1px solid rgba(255,255,255,.18);
  border-radius: 12px; background: rgba(255,255,255,.065);
}
.elementor-877 .e-na-clinical-list .elementor-icon-list-item:nth-child(2) { grid-column: 1 / -1; }
.elementor-877 .e-na-clinical-list .elementor-icon-list-icon { margin-top: 4px; }
@media (max-width: 767px) {
  .elementor-877 .e-na-requirements .elementor-widget-text-editor > ol > li { padding: 72px 22px 22px; }
  .elementor-877 .e-na-requirements .elementor-widget-text-editor > ol > li::before { left: 22px; top: 18px; }
  .elementor-877 .e-na-clinical-list .elementor-icon-list-items { grid-template-columns: 1fr; }
  .elementor-877 .e-na-clinical-list .elementor-icon-list-item:nth-child(2) { grid-column: auto; }
}
CSS;
$existing_css = $page_settings['custom_css'] ?? '';
$marker = '/* Nursing Admissions: SABER Design System 0.2 page polish. */';
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
$report = [
    'page_id' => $post_id, 'url' => get_permalink($post_id), 'backup' => $backup,
    'content_fields_preserved' => count($original_content),
    'protected_templates_unchanged' => $protected_unchanged,
    'design_assets' => $assets, 'hero_image' => 1317,
    'native_widget_types' => array_values(array_unique(array_column($widgets, 'widgetType'))),
    'elementor_widgets' => count($widgets),
];
file_put_contents('/tmp/nursing-admissions-redesign-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('Nursing Admissions redesigned. Original content and links are preserved.');
