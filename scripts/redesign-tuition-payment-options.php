<?php
/**
 * Apply SABER Design System 0.2 to Tuition Payment Options (page 885).
 * Existing copy, document link, Rank Math data and protected templates are preserved.
 */
if (!defined('ABSPATH')) { exit(1); }

$post_id = 885;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'tuition-payment-options' || (int) $page->post_parent !== 36) {
    WP_CLI::error('Page 885 is not the General Tuition Payment Options page.');
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
    'fec4385' => ['shortcode'], '6f304e4' => ['title'], 'a9ccbe6' => ['editor'],
    '1ed8ea3' => ['editor'], '86cacf5' => ['editor'], 'bcbb8c3' => ['editor'],
    'aee7d09' => ['editor'], 'eef7e1a' => ['editor'], '9fe5110' => ['editor'],
    '4fd533f' => ['editor'], 'e097e65' => ['editor'], '4a11296' => ['editor'],
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
$backup = '/tmp/tuition-payment-885-before-' . $stamp . '.json';
$backup_json = wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (false === file_put_contents($backup, $backup_json)) {
    WP_CLI::error('Could not create backup. Nothing was changed.');
}
file_put_contents('/tmp/tuition-payment-885-before-latest.json', $backup_json);
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
if (!wp_get_attachment_url(1026)) { WP_CLI::error('Financial aid image 1026 is unavailable.'); }

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
    $id = substr(md5('saber-tuition-' . $name), 0, 7);
    $class = 'e-tuition-' . $name;
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
        'id' => substr(md5('tuition-icon-' . $name), 0, 7), 'elType' => 'widget',
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
$image = static function ($name, $attachment_id, $alt, $height) {
    return [
        'id' => substr(md5('tuition-image-' . $name), 0, 7), 'elType' => 'widget',
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

$breadcrumbs = $section('breadcrumbs', [$inner('breadcrumbs-inner', [$widgets['fec4385']], [
    'padding' => $pad(20, 24, 20, 24), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'),
], [], ['padding' => $pad(68, 20, 18, 20)])], '#ffffff');

$rule = [
    'id' => 'tuitrule', 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [],
    'settings' => [
        'color' => '#fdc800', 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []],
        'width' => ['unit' => 'px', 'size' => 72, 'sizes' => []],
        'gap' => ['unit' => 'px', 'size' => 5, 'sizes' => []], 'align' => 'left',
    ],
];
$hero_copy = $container('hero-copy', [
    $rule, $heading('6f304e4', 47, 36, '#001a39', 'h1'),
    $text_widget('a9ccbe6', '#25377b', 21, '800'),
    $text_widget('1ed8ea3', '#29435f', 18),
], [
    'width' => $size(56, '%'), 'gap' => $size(17), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$hero_visual = $container('hero-visual', [$image('financial-aid', 1026, 'Financial aid planning for college', 440)], [
    'width' => $size(44, '%'), 'min-height' => $size(440), 'overflow' => $typed('string', 'hidden'),
    'border-radius' => $size(28), 'background' => $bg('#fff8e4'),
], [], ['width' => $size(100, '%'), 'min-height' => $size(310)]);
$hero = $section('hero', [$inner('hero-inner', [$hero_copy, $hero_visual], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(48), 'padding' => $pad(46, 24, 66, 24),
], [], [
    'flex-direction' => $typed('string', 'column'), 'align-items' => $typed('string', 'stretch'),
    'gap' => $size(30), 'padding' => $pad(34, 20, 48, 20),
])], '#f3f9ff', $assets['path-light']);

$overview_cards = [
    $container('overview-card-one', [$icon('overview-one', 'compass', '#25377b', 30), $text_widget('86cacf5', '#29435f', 18)], [
        'width' => $size(49, '%'), 'padding' => $pad(30, 32, 30, 32), 'gap' => $size(17),
        'background' => $bg('#ffffff'), 'border-style' => $typed('string', 'solid'),
        'border-color' => $typed('color', '#dce5ec'), 'border-width' => $size(1), 'border-radius' => $size(18),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(24)]),
    $container('overview-card-two', [$icon('overview-two', 'graduation-cap', '#25377b', 30), $text_widget('bcbb8c3', '#29435f', 18)], [
        'width' => $size(49, '%'), 'padding' => $pad(30, 32, 30, 32), 'gap' => $size(17),
        'background' => $bg('#f3f9ff'), 'border-style' => $typed('string', 'solid'),
        'border-color' => $typed('color', '#dce5ec'), 'border-width' => $size(1), 'border-radius' => $size(18),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(24)]),
];
$overview = $section('overview', [$inner('overview-inner', $overview_cards, [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'stretch'),
    'gap' => $size(24), 'padding' => $pad(70, 24, 74, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)])], '#ffffff');

$options_intro = $container('options-intro', [
    $icon('options', 'wallet', '#25377b', 34),
], [
    'width' => $size(100, '%'), 'align-items' => $typed('string', 'center'), 'text-align' => $typed('string', 'center'),
]);
$options_list = $text_widget('aee7d09', '#29435f', 16);
$options = $section('options', [$inner('options-inner', [
    $options_intro,
    $container('options-list', [$options_list], [
        'width' => $size(100, '%'), 'max-width' => $size(1140), 'align-self' => $typed('string', 'center'),
    ]),
], ['gap' => $size(26), 'padding' => $pad(74, 24, 82, 24)])], '#fffcf4', $assets['path-warm']);

$guidance_specs = [
    ['eef7e1a', 'hands-helping', '#ffffff'],
    ['9fe5110', 'comments-dollar', 'rgba(255,255,255,.075)'],
    ['4fd533f', 'chart-line', 'rgba(255,255,255,.075)'],
];
$guidance_cards = [];
foreach ($guidance_specs as $index => [$copy_id, $glyph, $color]) {
    $guidance_cards[] = $container('guidance-card-' . ($index + 1), [
        $icon('guidance-' . $index, $glyph, '#fdc800', 29),
        $text_widget($copy_id, '#ffffff', 16),
    ], [
        'width' => $size($index === 0 ? 100 : 49, '%'), 'padding' => $pad(28, 30, 28, 30),
        'gap' => $size(18), 'background' => $bg($color),
        'border-style' => $typed('string', 'solid'),
        'border-color' => $typed('color', $index === 0 ? '#ffffff' : 'rgba(255,255,255,.18)'),
        'border-width' => $size(1), 'border-radius' => $size(18),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(24)]);
}
$guidance_lower = $container('guidance-lower', [$guidance_cards[1], $guidance_cards[2]], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'stretch'), 'gap' => $size(20),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)]);
$guidance = $section('guidance', [$inner('guidance-inner', [$guidance_cards[0], $guidance_lower], [
    'gap' => $size(20), 'padding' => $pad(74, 24, 80, 24),
])], '#082b55', $assets['path-navy']);

$closing_copy = $container('closing-copy', [
    $icon('closing', 'lightbulb', '#25377b', 34),
    $text_widget('e097e65', '#001a39', 22, '800'),
], [
    'width' => $size(66, '%'), 'gap' => $size(18),
], [], ['width' => $size(100, '%')]);
$resource = $container('resource-card', [
    $icon('resource', 'file-pdf', '#25377b', 34),
    $text_widget('4a11296', '#001a39', 18, '800'),
], [
    'width' => $size(34, '%'), 'padding' => $pad(28, 30, 28, 30), 'gap' => $size(16),
    'background' => $bg('#ffffff'), 'border-radius' => $size(18),
], [], ['width' => $size(100, '%'), 'padding' => $size(24)]);
$closing = $section('closing', [$inner('closing-inner', [$closing_copy, $resource], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(44), 'padding' => $pad(62, 24, 66, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(24)])], '#fdc800');

$new_data = [$breadcrumbs, $hero, $overview, $options, $guidance, $closing];
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
/* Tuition Payment Options: Design System 0.2 page polish. */
.elementor-885 .rank-math-breadcrumb p { margin: 0; }
.elementor-885 .elementor-widget-text-editor p { margin-top: 0; }
.elementor-885 .elementor-widget-text-editor p:last-child { margin-bottom: 0; }
.elementor-885 a:focus-visible { outline: 3px solid #005fbd; outline-offset: 5px; box-shadow: 0 0 0 7px #fff; border-radius: 3px; }
.elementor-885 .e-tuition-hero-visual img { display: block; }
.elementor-885 .e-tuition-options-list .elementor-widget-text-editor > ol {
  list-style: none; counter-reset: tuition-option; display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; margin: 0; padding: 0;
}
.elementor-885 .e-tuition-options-list .elementor-widget-text-editor > ol > li {
  counter-increment: tuition-option; position: relative; margin: 0; padding: 30px 30px 30px 88px;
  background: #fff; border: 1px solid #dce5ec; border-radius: 18px;
  box-shadow: 0 12px 34px rgba(8,43,85,.045);
}
.elementor-885 .e-tuition-options-list .elementor-widget-text-editor > ol > li::before {
  content: counter(tuition-option, decimal-leading-zero); position: absolute; left: 24px; top: 27px;
  display: grid; place-items: center; width: 44px; height: 44px; border-radius: 50%;
  background: #fff8e4; color: #082b55; font: 800 15px/1 Nunito, Arial, sans-serif;
  border: 1px solid #fdc800;
}
.elementor-885 .e-tuition-options-list .elementor-widget-text-editor > ol > li > ul {
  list-style: none; display: grid; gap: 12px; margin: 20px 0 0; padding: 0;
}
.elementor-885 .e-tuition-options-list .elementor-widget-text-editor > ol > li > ul > li {
  position: relative; margin: 0; padding: 16px 18px 16px 44px; background: #f3f9ff;
  border: 1px solid #dce5ec; border-radius: 12px;
}
.elementor-885 .e-tuition-options-list .elementor-widget-text-editor > ol > li > ul > li::before {
  content: '\\2713'; position: absolute; left: 17px; top: 15px; color: #25377b; font-weight: 800;
}
.elementor-885 .e-tuition-guidance-inner .elementor-widget-icon { flex: 0 0 34px; width: 34px; }
.elementor-885 .e-tuition-resource-card a { color: #001a39; text-decoration: underline; text-underline-offset: 4px; }
@media (max-width: 767px) {
  .elementor-885 .e-tuition-options-list .elementor-widget-text-editor > ol { grid-template-columns: 1fr; }
  .elementor-885 .e-tuition-options-list .elementor-widget-text-editor > ol > li { padding: 76px 22px 24px; }
  .elementor-885 .e-tuition-options-list .elementor-widget-text-editor > ol > li::before { left: 22px; top: 20px; }
}
CSS;
$existing_css = $page_settings['custom_css'] ?? '';
$marker = '/* Tuition Payment Options: Design System 0.2 page polish. */';
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
    'design_assets' => $assets, 'images' => [1026],
    'native_widget_types' => array_values(array_unique($widget_types)),
    'elementor_widgets' => count($widgets),
];
file_put_contents('/tmp/tuition-payment-redesign-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('Tuition Payment Options redesigned. Original content and document link are preserved.');
