<?php
/**
 * Apply SABER Design System 0.2 to PTA Admissions (page 881).
 * Existing copy, links, Rank Math data and protected templates are preserved.
 */
if (!defined('ABSPATH')) { exit(1); }

$post_id = 881;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'admissions-pta') {
    WP_CLI::error('Page 881 is not PTA Admissions.');
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
    'c813558' => ['shortcode'],
    '21857c1' => ['title'],
    'c2df82c' => ['title'],
    '94db9a3' => ['editor'],
    '6e51e9d' => ['editor'],
    'a2f4879' => ['editor'],
    'c0e49d6' => ['title'],
    '8374ce8' => ['title'],
    '3c2b237' => ['editor'],
    '3d48339' => ['editor'],
    '7a7a590' => ['editor'],
    '5b30056' => ['editor'],
    '385aff4' => ['editor'],
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
$stamp = gmdate('Ymd-His');
$backup = '/tmp/pta-admissions-881-before-' . $stamp . '.json';
$backup_json = wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (false === file_put_contents($backup, $backup_json)) {
    WP_CLI::error('Could not create backup. Nothing was changed.');
}
file_put_contents('/tmp/pta-admissions-881-before-latest.json', $backup_json);
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
foreach ([1663, 1665] as $image_id) {
    if (!wp_get_attachment_url($image_id)) { WP_CLI::error("PTA image $image_id is unavailable."); }
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
$container = static function ($name, $children, $desktop = [], $tablet = [], $mobile = [], $hover = []) use ($typed, $size) {
    $id = substr(md5('saber-pta-admissions-' . $name), 0, 7);
    $class = 'e-pta-' . $name;
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
        'id' => substr(md5('pta-icon-' . $name), 0, 7), 'elType' => 'widget',
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
$image = static function ($name, $attachment_id, $alt, $height, $fit = 'contain') {
    return [
        'id' => substr(md5('pta-image-' . $name), 0, 7), 'elType' => 'widget',
        'widgetType' => 'image', 'elements' => [],
        'settings' => [
            'image' => ['id' => $attachment_id, 'url' => wp_get_attachment_url($attachment_id), 'alt' => $alt, 'source' => 'library'],
            'image_size' => 'large', 'width' => ['unit' => '%', 'size' => 100, 'sizes' => []],
            'height' => ['unit' => 'px', 'size' => $height, 'sizes' => []],
            'height_mobile' => ['unit' => 'px', 'size' => min($height, 340), 'sizes' => []],
            'object-fit' => $fit, 'object-position' => 'center center',
            '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
    ];
};

$breadcrumbs = $section('breadcrumbs', [$inner('breadcrumbs-inner', [$widgets['c813558']], [
    'padding' => $pad(20, 24, 20, 24), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'),
], [], ['padding' => $pad(68, 20, 18, 20)])], '#ffffff');

$rule = [
    'id' => 'ptarule1', 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [],
    'settings' => [
        'color' => '#fdc800', 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []],
        'width' => ['unit' => 'px', 'size' => 72, 'sizes' => []],
        'gap' => ['unit' => 'px', 'size' => 5, 'sizes' => []], 'align' => 'left',
    ],
];
$hero_copy = $container('hero-copy', [$rule, $heading('21857c1', 44, 35)], [
    'width' => $size(58, '%'), 'gap' => $size(18), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$hero_visual = $container('hero-visual', [$image('hero', 1663, 'Physical therapist assistant working with a patient', 430)], [
    'width' => $size(42, '%'), 'min-height' => $size(440),
    'justify-content' => $typed('string', 'center'), 'align-items' => $typed('string', 'center'),
    'padding' => $pad(18, 18, 8, 18), 'background' => $bg('#fff8e4'),
    'border-radius' => $size(32), 'overflow' => $typed('string', 'hidden'),
], [], ['width' => $size(100, '%'), 'min-height' => $size(320), 'padding' => $size(8)]);
$hero = $section('hero', [$inner('hero-inner', [$hero_copy, $hero_visual], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(44), 'padding' => $pad(42, 24, 64, 24),
], [], [
    'flex-direction' => $typed('string', 'column'), 'align-items' => $typed('string', 'stretch'),
    'gap' => $size(26), 'padding' => $pad(32, 20, 44, 20),
])], '#f3f9ff', $assets['path-light']);

$intro_lead = $container('intro-lead', [
    $heading('c2df82c', 35, 29),
    $text_widget('94db9a3', '#29435f', 18),
], [
    'width' => $size(100, '%'), 'max-width' => $size(880), 'align-self' => $typed('string', 'center'),
    'align-items' => $typed('string', 'center'), 'text-align' => $typed('string', 'center'), 'gap' => $size(16),
]);
$prep_specs = [
    ['6e51e9d', 'graduation-cap', '#ffffff'],
    ['a2f4879', 'clinic-medical', '#f3f9ff'],
];
$prep_cards = [];
foreach ($prep_specs as $index => [$copy_id, $glyph, $color]) {
    $prep_cards[] = $container('prep-' . ($index + 1), [
        $icon('prep-' . $index, $glyph, '#25377b', 30),
        $text_widget($copy_id, '#29435f', 17),
    ], [
        'width' => $size(49, '%'), 'padding' => $pad(30, 32, 30, 32), 'gap' => $size(18),
        'background' => $bg($color), 'border-style' => $typed('string', 'solid'),
        'border-color' => $typed('color', '#dce5ec'), 'border-width' => $size(1),
        'border-radius' => $size(18),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(24)], [
        'border-color' => $typed('color', '#9eb9d2'),
    ]);
}
$intro = $section('intro', [$inner('intro-inner', [
    $intro_lead,
    $container('prep-grid', $prep_cards, [
        'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'), 'gap' => $size(24),
    ], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)]),
], ['gap' => $size(34), 'padding' => $pad(72, 24, 78, 24)])], '#ffffff');

$criteria_header = $container('criteria-header', [
    $heading('c0e49d6', 38, 31),
    $heading('8374ce8', 22, 20, '#25377b'),
    $text_widget('3c2b237', '#29435f', 18),
], [
    'width' => $size(100, '%'), 'max-width' => $size(860), 'align-self' => $typed('string', 'center'),
    'align-items' => $typed('string', 'center'), 'text-align' => $typed('string', 'center'), 'gap' => $size(13),
]);
$criteria_list = $text_widget('3d48339', '#29435f', 16);
$criteria = $section('criteria', [$inner('criteria-inner', [
    $criteria_header,
    $container('criteria-list', [$criteria_list], [
        'width' => $size(100, '%'), 'max-width' => $size(1140), 'align-self' => $typed('string', 'center'),
    ]),
], ['gap' => $size(38), 'padding' => $pad(76, 24, 82, 24)])], '#fffcf4', $assets['path-warm']);

$closing_image = $container('closing-visual', [
    $image('closing', 1665, 'Physical therapist assistant supporting a patient during rehabilitation', 460),
], [
    'width' => $size(38, '%'), 'align-items' => $typed('string', 'center'),
    'justify-content' => $typed('string', 'center'), 'padding' => $size(12),
    'background' => $bg('#ffffff'), 'border-radius' => $size(26), 'overflow' => $typed('string', 'hidden'),
], [], ['width' => $size(100, '%'), 'padding' => $size(8)]);
$closing_cards = [];
$closing_specs = [
    ['7a7a590', 'user-check'],
    ['5b30056', 'hands-helping'],
    ['385aff4', 'shield-alt'],
];
foreach ($closing_specs as $index => [$copy_id, $glyph]) {
    $closing_cards[] = $container('closing-card-' . ($index + 1), [
        $icon('closing-' . $index, $glyph, '#fdc800', 25),
        $text_widget($copy_id, '#ffffff', 16),
    ], [
        'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'flex-start'), 'gap' => $size(18),
        'padding' => $pad(23, 24, 23, 24), 'background' => $bg('rgba(255,255,255,.07)'),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', 'rgba(255,255,255,.17)'),
        'border-width' => $size(1), 'border-radius' => $size(16),
    ], [], ['padding' => $size(21), 'gap' => $size(15)]);
}
$closing_copy = $container('closing-copy', $closing_cards, [
    'width' => $size(62, '%'), 'gap' => $size(16),
], [], ['width' => $size(100, '%')]);
$closing = $section('closing', [$inner('closing-inner', [$closing_image, $closing_copy], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(48), 'padding' => $pad(76, 24, 82, 24),
], [], [
    'flex-direction' => $typed('string', 'column'), 'gap' => $size(28),
])], '#082b55', $assets['path-navy']);

$new_data = [$breadcrumbs, $hero, $intro, $criteria, $closing];
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
/* PTA Admissions: SABER Design System 0.2 page polish. */
.elementor-881 .rank-math-breadcrumb p { margin: 0; }
.elementor-881 .elementor-widget-text-editor p { margin-top: 0; }
.elementor-881 .e-pta-intro-lead .elementor-widget-text-editor p:last-child,
.elementor-881 .e-pta-prep-grid .elementor-widget-text-editor p:last-child,
.elementor-881 .e-pta-closing-copy .elementor-widget-text-editor p:last-child { margin-bottom: 0; }
.elementor-881 a:focus-visible { outline: 3px solid #005fbd; outline-offset: 5px; box-shadow: 0 0 0 7px #fff; border-radius: 3px; }
.elementor-881 .e-pta-hero-visual img,
.elementor-881 .e-pta-closing-visual img { display: block; }
.elementor-881 .e-pta-criteria-list .elementor-widget-text-editor > ol {
  list-style: none; counter-reset: pta-admissions-step; display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin: 0; padding: 0;
}
.elementor-881 .e-pta-criteria-list .elementor-widget-text-editor > ol > li {
  counter-increment: pta-admissions-step; position: relative; margin: 0; padding: 26px 28px 26px 82px;
  background: #fff; border: 1px solid #dce5ec; border-radius: 16px;
  box-shadow: 0 12px 34px rgba(8,43,85,.045);
}
.elementor-881 .e-pta-criteria-list .elementor-widget-text-editor > ol > li::before {
  content: counter(pta-admissions-step, decimal-leading-zero); position: absolute; left: 22px; top: 23px;
  display: grid; place-items: center; width: 42px; height: 42px; border-radius: 50%;
  background: #fff8e4; color: #082b55; font: 800 15px/1 Nunito, Arial, sans-serif;
  border: 1px solid #fdc800;
}
.elementor-881 .e-pta-prep-grid > .e-con { height: auto; }
.elementor-881 .e-pta-closing-copy .elementor-icon { margin-top: 3px; }
.elementor-881 .e-pta-closing-copy .elementor-widget-icon { flex: 0 0 30px; width: 30px; }
.elementor-881 .e-pta-closing-copy .elementor-widget-text-editor { flex: 1 1 auto; min-width: 0; }
@media (max-width: 767px) {
  .elementor-881 .e-pta-criteria-list .elementor-widget-text-editor > ol { grid-template-columns: 1fr; }
  .elementor-881 .e-pta-criteria-list .elementor-widget-text-editor > ol > li { padding: 72px 22px 22px; }
  .elementor-881 .e-pta-criteria-list .elementor-widget-text-editor > ol > li::before { left: 22px; top: 18px; }
  .elementor-881 .e-pta-closing-card-1,
  .elementor-881 .e-pta-closing-card-2,
  .elementor-881 .e-pta-closing-card-3 { flex-direction: column; }
}
CSS;
$existing_css = $page_settings['custom_css'] ?? '';
$marker = '/* PTA Admissions: SABER Design System 0.2 page polish. */';
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
    'design_assets' => $assets, 'images' => [1663, 1665],
    'native_widget_types' => array_values(array_unique($widget_types)),
    'elementor_widgets' => count($widgets),
];
file_put_contents('/tmp/pta-admissions-redesign-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('PTA Admissions redesigned. Original content and links are preserved.');
