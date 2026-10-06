<?php
/**
 * Apply SABER Design System 0.2 to SABER Survey (page 904).
 * Existing copy, form configuration, Rank Math data and protected templates are preserved.
 */
if (!defined('ABSPATH')) { exit(1); }

$post_id = 904;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'saber-survey' || (int) $page->post_parent !== 34) {
    WP_CLI::error('Page 904 is not the SABER Survey child of Student Services.');
}
$data = json_decode(get_post_meta($post_id, '_elementor_data', true), true);
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
    '7085814' => ['shortcode'], '99f1689' => ['title'],
    'f8c422e' => ['editor'], 'd134e1f' => ['editor'],
];
$original_content = [];
foreach ($content_fields as $id => $fields) {
    if (!isset($widgets[$id])) { WP_CLI::error("Original widget $id is missing. Nothing was changed."); }
    foreach ($fields as $field) { $original_content[$id][$field] = $widgets[$id]['settings'][$field] ?? null; }
}
if (!isset($widgets['4df5919'])) { WP_CLI::error('Original survey form is missing. Nothing was changed.'); }
$original_form_settings = $widgets['4df5919']['settings'];

$snapshot = [
    'page_id' => $post_id, 'created_utc' => gmdate('c'), 'post' => (array) $page,
    'meta' => get_post_meta($post_id), 'protected_hashes' => [],
];
foreach ([7, 16, 53] as $protected) {
    $snapshot['protected_hashes'][$protected] = hash('sha256', get_post_meta($protected, '_elementor_data', true));
}
$stamp = gmdate('Ymd-His');
$backup = '/tmp/saber-survey-904-before-' . $stamp . '.json';
$backup_json = wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (false === file_put_contents($backup, $backup_json)) {
    WP_CLI::error('Could not create backup. Nothing was changed.');
}
file_put_contents('/tmp/saber-survey-904-before-latest.json', $backup_json);
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
$image_ids = [572];
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
    $id = substr(md5('saber-survey-' . $name), 0, 7);
    $class = 'e-survey-' . $name;
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
        'id' => substr(md5('survey-icon-' . $name), 0, 7), 'elType' => 'widget',
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
        'id' => substr(md5('survey-image-' . $name), 0, 7), 'elType' => 'widget',
        'widgetType' => 'image', 'elements' => [],
        'settings' => [
            'image' => ['id' => $attachment_id, 'url' => wp_get_attachment_url($attachment_id), 'alt' => $alt, 'source' => 'library'],
            'image_size' => 'full', 'width' => ['unit' => '%', 'size' => 100, 'sizes' => []],
            'height' => ['unit' => 'px', 'size' => $height, 'sizes' => []],
            'height_mobile' => ['unit' => 'px', 'size' => 260, 'sizes' => []],
            'object-fit' => 'contain', 'object-position' => 'center center',
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

$breadcrumbs = $section('breadcrumbs', [$inner('breadcrumbs-inner', [$widgets['7085814']], [
    'padding' => $pad(20, 24, 20, 24), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'),
], [], ['padding' => $pad(68, 20, 18, 20)])], '#ffffff');

$rule = [
    'id' => 'survrule', 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [],
    'settings' => [
        'color' => '#fdc800', 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []],
        'width' => ['unit' => 'px', 'size' => 72, 'sizes' => []],
        'gap' => ['unit' => 'px', 'size' => 5, 'sizes' => []], 'align' => 'left',
    ],
];
$hero_copy = $container('hero-copy', [
    $rule, $heading('99f1689', 52, 38, '#001a39', 'h1'), $text('f8c422e', '#29435f', 18),
], [
    'width' => $size(62, '%'), 'gap' => $size(20), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$hero_visual = $container('hero-visual', [
    $image('sabi', 572, 'Sabi, SABER College mascot', 330),
    $container('hero-icons', [
        $icon('community', 'users', '#25377b', 24),
        $icon('survey', 'poll', '#25377b', 24),
        $icon('care', 'heart', '#25377b', 24),
    ], [
        'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
        'justify-content' => $typed('string', 'center'), 'gap' => $size(24),
    ]),
], [
    'width' => $size(38, '%'), 'min-height' => $size(420), 'align-items' => $typed('string', 'center'),
    'justify-content' => $typed('string', 'center'), 'padding' => $pad(28, 32, 28, 32),
    'gap' => $size(12), 'background' => $bg('#fdc800'), 'border-radius' => $size(28),
], [], ['width' => $size(100, '%'), 'min-height' => $size(340), 'padding' => $size(22)]);
$hero = $section('hero', [$inner('hero-inner', [$hero_copy, $hero_visual], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(50), 'padding' => $pad(48, 24, 68, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#f3f9ff', $assets['path-light']);

$purpose = $section('purpose', [$inner('purpose-inner', [
    $container('purpose-card', [
        $icon('regulation', 'balance-scale', '#25377b', 36), $text('d134e1f', '#001a39', 20, '800'),
    ], [
        'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'), 'justify-content' => $typed('string', 'center'),
        'text-align' => $typed('string', 'center'), 'gap' => $size(24),
        'padding' => $pad(28, 36, 30, 36), 'background' => $bg('#fdc800'), 'border-radius' => $size(20),
    ], [], ['flex-direction' => $typed('string', 'column'), 'padding' => $size(24)]),
], ['padding' => $pad(52, 24, 56, 24)])], '#ffffff');

$form_widget = $widgets['4df5919'];
$form_card = $container('form-card', [
    $container('form-mark', [$icon('form', 'clipboard-check', '#25377b', 40)], [
        'width' => $size(100, '%'), 'align-items' => $typed('string', 'center'),
    ]),
    $form_widget,
], [
    'width' => $size(100, '%'), 'max-width' => $size(980), 'padding' => $pad(42, 46, 46, 46),
    'gap' => $size(28), 'background' => $bg('#ffffff'), 'border-radius' => $size(24),
    'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#e4dbc4'),
    'border-width' => $size(1),
], [], ['padding' => $size(24)]);
$form_section = $section('form-section', [$inner('form-inner', [$form_card], [
    'align-items' => $typed('string', 'center'), 'padding' => $pad(72, 24, 82, 24),
])], '#fff8e4', $assets['path-warm']);

$new_data = [$breadcrumbs, $hero, $purpose, $form_section];
$widgets = [];
$collect($new_data);
foreach ($original_content as $id => $fields) {
    foreach ($fields as $field => $value) {
        if (($widgets[$id]['settings'][$field] ?? null) !== $value) {
            WP_CLI::error("Content preservation failed for $id:$field.");
        }
    }
}
if (($widgets['4df5919']['settings'] ?? null) !== $original_form_settings) {
    WP_CLI::error('Survey form configuration preservation failed.');
}

$page_settings = get_post_meta($post_id, '_elementor_page_settings', true) ?: [];
$page_css = <<<'CSS'
/* SABER Survey: Design System 0.2 page polish. */
.elementor-904 .rank-math-breadcrumb p { margin: 0; }
.elementor-904 .elementor-widget-text-editor p { margin-top: 0; }
.elementor-904 .elementor-widget-text-editor p:last-child { margin-bottom: 0; }
.elementor-904 a:focus-visible { outline: 3px solid #005fbd; outline-offset: 5px; box-shadow: 0 0 0 7px #fff; border-radius: 3px; }
.elementor-904 .e-survey-hero-visual img { display: block; mix-blend-mode: multiply; }
.elementor-904 .e-survey-purpose-card p { margin: 0; }
.elementor-904 .e-survey-form-card .elementor-form-fields-wrapper {
  display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px;
}
.elementor-904 .e-survey-form-card .elementor-field-group {
  width: auto !important; margin: 0 !important; padding: 0 !important;
}
.elementor-904 .e-survey-form-card .elementor-field-type-submit { grid-column: 1 / -1; margin-top: 8px !important; }
.elementor-904 .e-survey-form-card .elementor-field-label {
  margin-bottom: 8px; color: #001a39; font: 800 15px/1.35 Nunito, Arial, sans-serif;
}
.elementor-904 .e-survey-form-card input.elementor-field,
.elementor-904 .e-survey-form-card select.elementor-field {
  min-height: 52px; padding: 12px 15px; color: #183653; background: #f7fbff;
  border: 1px solid #cbd9e5; border-radius: 10px; font: 500 16px/1.4 Nunito, Arial, sans-serif;
}
.elementor-904 .e-survey-form-card input.elementor-field:focus,
.elementor-904 .e-survey-form-card select.elementor-field:focus {
  border-color: #005fbd; outline: 3px solid rgba(0,95,189,.16); box-shadow: none;
}
.elementor-904 .e-survey-form-card .elementor-button {
  min-height: 52px; padding: 14px 30px; color: #001a39; background: #fdc800;
  border: 2px solid #fdc800; border-radius: 10px; font: 800 16px/1.2 Nunito, Arial, sans-serif;
}
.elementor-904 .e-survey-form-card .elementor-button:hover,
.elementor-904 .e-survey-form-card .elementor-button:focus-visible {
  color: #ffffff; background: #082b55; border-color: #082b55;
}
@media (max-width: 767px) {
  .elementor-904 .e-survey-form-card .elementor-form-fields-wrapper { grid-template-columns: 1fr; }
  .elementor-904 .e-survey-form-card .elementor-field-type-submit { grid-column: auto; }
}
CSS;
$existing_css = $page_settings['custom_css'] ?? '';
$marker = '/* SABER Survey: Design System 0.2 page polish. */';
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
    'content_fields_preserved' => count($original_content) + 1,
    'form_fields_preserved' => count($original_form_settings['form_fields'] ?? []),
    'form_configuration_preserved' => true,
    'protected_templates_unchanged' => $protected_unchanged,
    'design_assets' => $assets, 'images' => $image_ids,
    'native_widget_types' => array_values(array_unique($widget_types)),
    'elementor_widgets' => count($widgets),
];
file_put_contents('/tmp/saber-survey-redesign-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('SABER Survey redesigned. Original copy, form configuration and SEO fields are preserved.');
