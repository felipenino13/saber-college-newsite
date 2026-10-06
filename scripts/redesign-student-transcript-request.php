<?php
/**
 * Apply SABER Design System 0.2 to Student Transcript Request (page 902).
 * Existing copy, links, Rank Math data and protected templates are preserved.
 */
if (!defined('ABSPATH')) { exit(1); }

$post_id = 902;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'student-transcript-request' || (int) $page->post_parent !== 34) {
    WP_CLI::error('Page 902 is not the Student Transcript Request child of Student Services.');
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
    '0ef606e' => ['shortcode'], 'c78e913' => ['title'], '25b14b5' => ['editor'],
    '2b9865a' => ['editor'], 'db01cd8' => ['editor'], 'abaad2b' => ['editor'],
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
$backup = '/tmp/student-transcript-902-before-' . $stamp . '.json';
$backup_json = wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (false === file_put_contents($backup, $backup_json)) {
    WP_CLI::error('Could not create backup. Nothing was changed.');
}
file_put_contents('/tmp/student-transcript-902-before-latest.json', $backup_json);
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
$image_ids = [1699];
foreach ($image_ids as $image_id) {
    if (!wp_get_attachment_url($image_id)) { WP_CLI::error("Image $image_id is unavailable."); }
}
$document_id = 1804;
$document_url = wp_get_attachment_url($document_id);
if (!$document_url) { WP_CLI::error('Transcript request document is unavailable.'); }

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
    $id = substr(md5('saber-transcript-' . $name), 0, 7);
    $class = 'e-transcript-' . $name;
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
$icon = static function ($name, $glyph, $color = '#25377b', $font_size = 30, $link = '') {
    $settings = [
        'selected_icon' => ['value' => 'fas fa-' . $glyph, 'library' => 'fa-solid'],
        'view' => 'default', 'primary_color' => $color, 'align' => 'left',
        'size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ];
    if ($link !== '') {
        $settings['link'] = ['url' => $link, 'is_external' => true, 'nofollow' => ''];
    }
    return [
        'id' => substr(md5('transcript-icon-' . $name), 0, 7), 'elType' => 'widget',
        'widgetType' => 'icon', 'elements' => [], 'settings' => $settings,
    ];
};
$image = static function ($name, $attachment_id, $alt, $height) {
    return [
        'id' => substr(md5('transcript-image-' . $name), 0, 7), 'elType' => 'widget',
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

$breadcrumbs = $section('breadcrumbs', [$inner('breadcrumbs-inner', [$widgets['0ef606e']], [
    'padding' => $pad(20, 24, 20, 24), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'),
], [], ['padding' => $pad(68, 20, 18, 20)])], '#ffffff');

$rule = [
    'id' => 'trnrule', 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [],
    'settings' => [
        'color' => '#fdc800', 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []],
        'width' => ['unit' => 'px', 'size' => 72, 'sizes' => []],
        'gap' => ['unit' => 'px', 'size' => 5, 'sizes' => []], 'align' => 'left',
    ],
];
$hero_copy = $container('hero-copy', [
    $rule, $heading('c78e913', 47, 34, '#001a39', 'h1'),
], [
    'width' => $size(62, '%'), 'gap' => $size(20), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$download_card = $container('download-card', [
    $icon('download', 'file-pdf', '#25377b', 58, $document_url),
    $text('25b14b5', '#001a39', 17, '800'),
], [
    'width' => $size(38, '%'), 'min-height' => $size(260), 'align-items' => $typed('string', 'center'),
    'justify-content' => $typed('string', 'center'), 'text-align' => $typed('string', 'center'),
    'padding' => $pad(34, 34, 34, 34), 'gap' => $size(24), 'background' => $bg('#ffffff'),
    'border-radius' => $size(24), 'border-style' => $typed('string', 'solid'),
    'border-color' => $typed('color', '#d8e3ec'), 'border-width' => $size(1),
], [], ['width' => $size(100, '%'), 'min-height' => $size(220)]);
$hero = $section('hero', [$inner('hero-inner', [$hero_copy, $download_card], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(50), 'padding' => $pad(52, 24, 72, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#f3f9ff', $assets['path-light']);

$process = $section('process', [$inner('process-inner', [
    $container('process-mark', [$icon('steps', 'clipboard-list', '#25377b', 40)], [
        'width' => $size(100, '%'), 'align-items' => $typed('string', 'center'),
    ]),
    $container('process-steps', [$text('2b9865a', '#29435f', 16)], ['width' => $size(100, '%')]),
], ['gap' => $size(30), 'padding' => $pad(72, 24, 80, 24)])], '#fff8e4', $assets['path-warm']);

$schedule = $section('schedule', [$inner('schedule-inner', [
    $container('schedule-card', [
        $icon('schedule', 'clock', '#fdc800', 40), $text('db01cd8', '#e8f2fb', 19, '700'),
    ], [
        'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'), 'gap' => $size(28),
        'padding' => $pad(34, 40, 36, 40), 'background' => $bg('#123b70'),
        'border-radius' => $size(22),
    ], [], ['flex-direction' => $typed('string', 'column'), 'align-items' => $typed('string', 'flex-start'), 'padding' => $size(26)]),
], ['padding' => $pad(62, 24, 66, 24)])], '#082b55', $assets['path-navy']);

$campus_visual = $container('campus-visual', [
    $image('campus', 1699, 'SABER College campus in Miami', 360),
], [
    'width' => $size(56, '%'), 'overflow' => $typed('string', 'hidden'), 'border-radius' => $size(24),
], [], ['width' => $size(100, '%')]);
$invitation = $container('invitation', [
    $icon('visit', 'map-marker-alt', '#25377b', 38), $text('abaad2b', '#001a39', 20, '800'),
], [
    'width' => $size(44, '%'), 'padding' => $pad(36, 38, 38, 38), 'gap' => $size(20),
    'justify-content' => $typed('string', 'center'), 'background' => $bg('#fdc800'),
    'border-radius' => $size(22),
], [], ['width' => $size(100, '%'), 'padding' => $size(26)]);
$closing = $section('closing', [$inner('closing-inner', [$campus_visual, $invitation], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(42), 'padding' => $pad(72, 24, 78, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(24)])], '#ffffff');

$new_data = [$breadcrumbs, $hero, $process, $schedule, $closing];
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
/* Student Transcript Request: Design System 0.2 page polish. */
.elementor-902 .rank-math-breadcrumb p { margin: 0; }
.elementor-902 .elementor-widget-text-editor p { margin-top: 0; }
.elementor-902 .elementor-widget-text-editor p:last-child { margin-bottom: 0; }
.elementor-902 a:focus-visible { outline: 3px solid #005fbd; outline-offset: 5px; box-shadow: 0 0 0 7px #fff; border-radius: 3px; }
.elementor-902 .e-transcript-campus-visual img { display: block; }
.elementor-902 .e-transcript-download-card { transition: transform .2s ease, box-shadow .2s ease; }
.elementor-902 .e-transcript-download-card:hover { transform: translateY(-3px); box-shadow: 0 16px 38px rgba(8,43,85,.10); }
.elementor-902 .e-transcript-download-card p { margin: 0; }
.elementor-902 .e-transcript-download-card a { color: #001a39; text-decoration: none; }
.elementor-902 .e-transcript-process-steps ol {
  display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px;
  margin: 0; padding: 0; list-style: none; counter-reset: transcript-step;
}
.elementor-902 .e-transcript-process-steps ol > li {
  counter-increment: transcript-step; position: relative; min-height: 330px; margin: 0;
  padding: 78px 28px 30px; background: #fff; border: 1px solid #e4dbc4;
  border-radius: 18px; box-shadow: 0 12px 32px rgba(8,43,85,.045);
}
.elementor-902 .e-transcript-process-steps ol > li::before {
  content: counter(transcript-step, decimal-leading-zero); position: absolute; left: 28px; top: 24px;
  display: grid; place-items: center; width: 42px; height: 42px; border-radius: 50%;
  background: #082b55; color: #fff; font: 800 14px/1 Nunito, Arial, sans-serif;
}
.elementor-902 .e-transcript-process-steps ul {
  margin: 18px 0 0; padding: 0; list-style: none;
}
.elementor-902 .e-transcript-process-steps ul li {
  position: relative; margin: 0 0 11px; padding: 10px 12px 10px 34px;
  overflow-wrap: anywhere; background: #f3f9ff; border-radius: 10px; color: #082b55;
}
.elementor-902 .e-transcript-process-steps ul li::before {
  content: '@'; position: absolute; left: 12px; top: 9px;
  font: 800 16px/1.4 Nunito, Arial, sans-serif; color: #25377b;
}
.elementor-902 .e-transcript-schedule-card em { font-style: normal; }
@media (max-width: 900px) {
  .elementor-902 .e-transcript-process-steps ol { grid-template-columns: 1fr; }
  .elementor-902 .e-transcript-process-steps ol > li { min-height: 0; }
}
@media (prefers-reduced-motion: reduce) {
  .elementor-902 .e-transcript-download-card { transition: none; }
}
CSS;
$existing_css = $page_settings['custom_css'] ?? '';
$marker = '/* Student Transcript Request: Design System 0.2 page polish. */';
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
    'design_assets' => $assets, 'images' => $image_ids, 'document' => $document_id,
    'native_widget_types' => array_values(array_unique($widget_types)),
    'elementor_widgets' => count($widgets),
];
file_put_contents('/tmp/student-transcript-redesign-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('Student Transcript Request redesigned. Original content, document link and SEO fields are preserved.');
