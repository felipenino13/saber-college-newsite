<?php
/**
 * Apply SABER Design System 0.2 to page 34, preserving all existing copy and links.
 * Copy this file and path-light.svg/path-warm.svg to /tmp before running with WP-CLI.
 * Backups are written outside the web root and must be copied to the host afterwards.
 */
if (!defined('ABSPATH')) { exit(1); }

$post_id = 34;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'student-services') {
    WP_CLI::error('Page 34 is not Student Services.');
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
$link_ids = ['354ffa1', 'd18a0df', '0e6146c', 'f82250f', '7cb5f35', '860c34d', 'cd5af0b', 'df129d8', '7c2a8dc', '4c3232f', '5050627'];
foreach (array_merge(['8953628', 'efc94cd'], $link_ids) as $id) {
    if (!isset($widgets[$id])) { WP_CLI::error("Original widget $id is missing. Nothing was changed."); }
}
$original_copy = [];
foreach ($link_ids as $id) { $original_copy[$id] = $widgets[$id]['settings']['editor']; }
$original_title = $widgets['efc94cd']['settings']['title'];
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
$backup = '/tmp/student-services-34-before-' . gmdate('Ymd-His') . '.json';
if (false === file_put_contents($backup, wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) {
    WP_CLI::error('Could not create backup. Nothing was changed.');
}
WP_CLI::line('BACKUP=' . $backup);

/* Register only these trusted local SVGs; no global change to permitted upload types. */
$assets = [];
foreach (['path-light', 'path-warm'] as $asset) {
    $existing = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_saber_design_asset', 'meta_value' => $asset . '-v02']);
    if ($existing) { $assets[$asset] = $existing[0]; continue; }
    $source = '/tmp/' . $asset . '.svg';
    if (!is_readable($source)) { WP_CLI::error("Missing controlled asset $source."); }
    $svg = file_get_contents($source);
    if (preg_match('/<script|<foreignObject|\bon\w+\s*=|(?:href|src)\s*=/i', $svg)) { WP_CLI::error('Unexpected SVG content.'); }
    $uploads = wp_upload_dir();
    $directory = $uploads['basedir'] . '/saber-design-system';
    wp_mkdir_p($directory);
    $filename = wp_unique_filename($directory, 'saber-' . $asset . '-v02.svg');
    $destination = $directory . '/' . $filename;
    if (false === file_put_contents($destination, $svg)) { WP_CLI::error('Could not store design background.'); }
    $attachment = wp_insert_attachment([
        'post_mime_type' => 'image/svg+xml', 'post_title' => 'SABER Design System - ' . $asset,
        'post_status' => 'inherit', 'post_content' => '',
    ], $destination, 0, true);
    if (is_wp_error($attachment)) { WP_CLI::error($attachment->get_error_message()); }
    wp_update_attachment_metadata($attachment, ['width' => 1600, 'height' => 900, 'file' => 'saber-design-system/' . $filename, 'sizes' => []]);
    update_post_meta($attachment, '_saber_design_asset', $asset . '-v02');
    $assets[$asset] = $attachment;
}

$typed = static fn($type, $value) => ['$$type' => $type, 'value' => $value];
$size = static fn($value, $unit = 'px') => ['$$type' => 'size', 'value' => ['size' => $value, 'unit' => $unit]];
$pad = static function ($top, $right, $bottom, $left) use ($typed, $size) {
    return $typed('dimensions', ['block-start' => $size($top), 'inline-end' => $size($right), 'block-end' => $size($bottom), 'inline-start' => $size($left)]);
};
$bg = static function ($color, $attachment = 0) use ($typed) {
    $value = ['color' => $typed('color', $color)];
    if ($attachment) {
        $value['background-overlay'] = $typed('background-overlay', [$typed('background-image-overlay', [
            'image' => $typed('image', [
                'src' => $typed('image-src', ['id' => $typed('image-attachment-id', $attachment), 'url' => null]),
                'size' => $typed('string', 'full'),
            ]),
            'repeat' => $typed('string', 'no-repeat'), 'size' => $typed('string', 'cover'), 'position' => $typed('string', 'center center'),
        ])]);
    }
    return $typed('background', $value);
};
$container = static function ($name, $children, $desktop = [], $tablet = [], $mobile = [], $hover = []) use ($typed, $size) {
    $id = substr(md5('saber-student-services-' . $name), 0, 7);
    $class = 'e-ss-' . $name;
    $desktop = array_merge(['display' => $typed('string', 'flex'), 'flex-direction' => $typed('string', 'column'), 'min-width' => $size(0)], $desktop);
    $variants = [['meta' => ['breakpoint' => 'desktop', 'state' => null], 'props' => $desktop, 'custom_css' => null]];
    foreach (['tablet' => $tablet, 'mobile' => $mobile] as $breakpoint => $props) {
        if ($props) { $variants[] = ['meta' => ['breakpoint' => $breakpoint, 'state' => null], 'props' => $props, 'custom_css' => null]; }
    }
    if ($hover) { $variants[] = ['meta' => ['breakpoint' => 'desktop', 'state' => 'hover'], 'props' => $hover, 'custom_css' => null]; }
    return [
        'id' => $id, 'elType' => 'e-flexbox',
        'settings' => ['classes' => $typed('classes', [$class])],
        'elements' => $children, 'isInner' => false,
        'styles' => [$class => ['id' => $class, 'label' => $class, 'type' => 'class', 'variants' => $variants]],
        'interactions' => [], 'editor_settings' => [], 'version' => '0.0',
    ];
};
$icon = static function ($name, $glyph, $color = '#25377b', $font_size = 28) {
    return ['id' => substr(md5('ss-icon-' . $name), 0, 7), 'elType' => 'widget', 'widgetType' => 'icon', 'elements' => [], 'settings' => [
        'selected_icon' => ['value' => 'fas fa-' . $glyph, 'library' => 'fa-solid'],
        'view' => 'default', 'primary_color' => $color, 'align' => 'left',
        'size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ]];
};
$link = static function ($id, $font_size = 22, $color = '#001a39') use (&$widgets) {
    $widget = $widgets[$id];
    unset($widget['settings']['__globals__']);
    $widget['settings'] = array_merge($widget['settings'], [
        'text_color' => $color, 'link_color' => $color, 'link_hover_color' => $color === '#ffffff' ? '#fdc800' : '#005fbd',
        'typography_typography' => 'custom', 'typography_font_family' => 'Nunito', 'typography_font_weight' => '800',
        'typography_font_size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
        'typography_font_size_mobile' => ['unit' => 'px', 'size' => min(22, $font_size), 'sizes' => []],
        'typography_line_height' => ['unit' => 'em', 'size' => 1.3, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ]);
    return $widget;
};
$inner = static function ($name, $children, $extra = [], $mobile = []) use ($container, $size, $typed, $pad) {
    return $container($name, $children, array_merge([
        'width' => $size(100, '%'), 'max-width' => $size(1200), 'padding' => $pad(64, 24, 64, 24), 'gap' => $size(24),
    ], $extra), [], array_merge(['padding' => $pad(36, 20, 40, 20)], $mobile));
};
$section = static function ($name, $children, $color, $attachment = 0) use ($container, $size, $typed, $bg) {
    return $container($name, $children, ['width' => $size(100, '%'), 'align-items' => $typed('string', 'center'), 'background' => $bg($color, $attachment)]);
};

$breadcrumbs = $section('breadcrumbs', [$inner('breadcrumbs-inner', [$widgets['8953628']], [
    'padding' => $pad(20, 24, 20, 24), 'align-items' => $typed('string', 'center'), 'text-align' => $typed('string', 'center'),
], ['padding' => $pad(68, 20, 18, 20)])], '#ffffff');

$title = $widgets['efc94cd'];
$title['settings'] = array_merge($title['settings'], [
    'title_color' => '#001a39', 'typography_font_size' => ['unit' => 'px', 'size' => 68, 'sizes' => []],
    'typography_font_size_tablet' => ['unit' => 'px', 'size' => 54, 'sizes' => []],
    'typography_font_size_mobile' => ['unit' => 'px', 'size' => 44, 'sizes' => []],
    'typography_line_height' => ['unit' => 'em', 'size' => 1.05, 'sizes' => []],
    'typography_letter_spacing' => ['unit' => 'px', 'size' => -1.7, 'sizes' => []],
]);
$rule = ['id' => 'ssrule1', 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [], 'settings' => [
    'color' => '#fdc800', 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []],
    'width' => ['unit' => 'px', 'size' => 72, 'sizes' => []], 'gap' => ['unit' => 'px', 'size' => 5, 'sizes' => []], 'align' => 'left',
]];
$hero_copy = $container('hero-copy', [$rule, $title], ['width' => $size(42, '%'), 'gap' => $size(16)], [], ['width' => $size(100, '%')]);
$hero_image = ['id' => 'ssphoto', 'elType' => 'widget', 'widgetType' => 'image', 'elements' => [], 'settings' => [
    'image' => ['id' => 1699, 'url' => wp_get_attachment_url(1699), 'alt' => 'SABER College campus in Miami', 'source' => 'library'],
    'image_size' => 'large', 'width' => ['unit' => '%', 'size' => 100, 'sizes' => []],
    'height' => ['unit' => 'px', 'size' => 280, 'sizes' => []], 'height_mobile' => ['unit' => 'px', 'size' => 200, 'sizes' => []],
    'object-fit' => 'cover', 'object-position' => 'center center',
    'image_border_radius' => ['unit' => 'px', 'top' => '70', 'right' => '14', 'bottom' => '70', 'left' => '14', 'isLinked' => false],
]];
$hero_visual = $container('hero-visual', [$hero_image], ['width' => $size(58, '%')], [], ['width' => $size(100, '%')]);
$hero = $section('hero', [$inner('hero-inner', [$hero_copy, $hero_visual], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'), 'gap' => $size(72), 'padding' => $pad(52, 24, 66, 24),
], ['flex-direction' => $typed('string', 'column'), 'align-items' => $typed('string', 'stretch'), 'gap' => $size(26), 'padding' => $pad(34, 20, 40, 20)])], '#f3f9ff', $assets['path-light']);

$primary = [];
$primary_specs = [['354ffa1', 'book-open'], ['d18a0df', 'passport'], ['0e6146c', 'briefcase']];
foreach ($primary_specs as $i => [$id, $glyph]) {
    $dark = $i === 2;
    $primary[] = $container('primary-' . ($i + 1), [
        $icon('primary-' . $i, $glyph, $dark ? '#fdc800' : '#25377b', 30),
        $container('primary-line-' . $i, [$link($id, 27, $dark ? '#ffffff' : '#001a39'), $icon('primary-arrow-' . $i, 'arrow-right', $dark ? '#fdc800' : '#005fbd', 18)], [
            'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'), 'justify-content' => $typed('string', 'space-between'), 'gap' => $size(18),
        ]),
    ], [
        'width' => $size(31.8, '%'), 'min-height' => $size(212), 'justify-content' => $typed('string', 'space-between'),
        'padding' => $size(30), 'gap' => $size(34), 'background' => $bg($dark ? '#082b55' : '#ffffff'),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', $dark ? '#082b55' : '#dce5ec'), 'border-width' => $size(1), 'border-radius' => $size(16),
    ], ['padding' => $size(22)], ['width' => $size(100, '%'), 'min-height' => $size(158), 'gap' => $size(24), 'padding' => $size(26)], [
        'border-color' => $typed('color', $dark ? '#25377b' : '#9eb9d2'), 'background' => $bg($dark ? '#173b64' : '#f9fcff'),
    ]);
}
$primary_grid = $container('primary-grid', $primary, ['width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'), 'gap' => $size(24), 'align-items' => $typed('string', 'stretch')], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(16)]);
$secondary = [];
foreach ([['f82250f', 'file-alt'], ['7cb5f35', 'comment-dots']] as $i => [$id, $glyph]) {
    $secondary[] = $container('secondary-' . ($i + 1), [$icon('secondary-' . $i, $glyph), $link($id, 23), $icon('secondary-arrow-' . $i, $i === 0 ? 'external-link-alt' : 'arrow-right', '#005fbd', 16)], [
        'width' => $size(48.9, '%'), 'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
        'gap' => $size(22), 'min-height' => $size(118), 'padding' => $size(26), 'background' => $bg('#f3f9ff'), 'border-radius' => $size(12),
    ], [], ['width' => $size(100, '%'), 'gap' => $size(16), 'padding' => $size(22)], ['background' => $bg('#eaf3fc')]);
}
$secondary_grid = $container('secondary-grid', $secondary, ['width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'), 'gap' => $size(24), 'align-items' => $typed('string', 'stretch')], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(16)]);
$services = $section('services', [$inner('services-inner', [$primary_grid, $secondary_grid], ['padding' => $pad(52, 24, 56, 24)])], '#ffffff');

$resource_rows = [];
$resource_glyphs = ['credit-card', 'hands-helping', 'file-signature', 'id-card', 'balance-scale', 'vote-yea'];
foreach (array_slice($link_ids, 5) as $i => $id) {
    $resource_rows[] = $container('resource-' . ($i + 1), [
        $icon('resource-' . $i, $resource_glyphs[$i], '#25377b', 23), $link($id, 19), $icon('resource-arrow-' . $i, 'external-link-alt', '#52677b', 14),
    ], [
        'width' => $size(48.9, '%'), 'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'), 'gap' => $size(20),
        'padding' => $pad(25, 26, 25, 26), 'min-height' => $size(110), 'background' => $bg('#ffffff'),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#e0e5e9'), 'border-width' => $size(1), 'border-radius' => $size(12),
    ], [], ['width' => $size(100, '%'), 'gap' => $size(16), 'padding' => $size(22)], ['border-color' => $typed('color', '#b4c6d6')]);
}
$resources = $section('resources', [$inner('resource-grid', $resource_rows, [
    'flex-direction' => $typed('string', 'row'), 'flex-wrap' => $typed('string', 'wrap'), 'align-items' => $typed('string', 'stretch'), 'gap' => $size(22), 'padding' => $pad(56, 24, 64, 24),
], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(14)])], '#fffcf4', $assets['path-warm']);

$new_data = [$breadcrumbs, $hero, $services, $resources];
$before_widgets = $widgets;
$widgets = [];
$collect($new_data);
foreach ($original_copy as $id => $html) {
    if (($widgets[$id]['settings']['editor'] ?? null) !== $html) { WP_CLI::error("Copy preservation failed for $id."); }
}
if ($widgets['efc94cd']['settings']['title'] !== $original_title) { WP_CLI::error('Title changed unexpectedly.'); }

$page_settings = get_post_meta($post_id, '_elementor_page_settings', true) ?: [];
$page_css = <<<'CSS'
/* Student Services: polish around native, editable Elementor widgets. */
.elementor-34 .elementor-widget-text-editor { flex:1; min-width:0; }
.elementor-34 .elementor-widget-text-editor p { margin:0; }
.elementor-34 .elementor-widget-text-editor a { display:flex; align-items:center; min-height:48px; text-decoration:none; }
.elementor-34 .elementor-widget-text-editor a:hover { text-decoration:underline; text-underline-offset:5px; }
.elementor-34 a:focus-visible { outline:3px solid #005fbd; outline-offset:5px; box-shadow:0 0 0 7px #fff; border-radius:3px; }
.elementor-34 .elementor-widget-icon { flex-shrink:0; width:auto; }
.elementor-34 .elementor-icon-wrapper { line-height:1; }
.elementor-34 .e-ss-hero-visual img { display:block; }
.elementor-34 .rank-math-breadcrumb p { margin:0; }
CSS;
$existing_css = $page_settings['custom_css'] ?? '';
$marker = '/* Student Services: polish around native, editable Elementor widgets. */';
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
    'page_id' => 34, 'url' => get_permalink(34), 'backup' => $backup,
    'source_text_and_link_widgets_preserved' => count($original_copy), 'title_preserved' => true,
    'protected_templates_unchanged' => $protected_unchanged, 'design_assets' => $assets,
    'native_widget_types' => array_values(array_unique(array_column($widgets, 'widgetType'))),
    'elementor_widgets' => count($widgets),
];
file_put_contents('/tmp/student-services-redesign-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('Student Services redesigned. All eleven original text/link widgets are preserved.');
