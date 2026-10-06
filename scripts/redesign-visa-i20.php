<?php
/**
 * Apply SABER Design System 0.2 to VISA I-20 Assistance (page 898).
 * Existing copy, links, Rank Math data and protected templates are preserved.
 */
if (!defined('ABSPATH')) { exit(1); }

$post_id = 898;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'visa-i-20-assistance' || (int) $page->post_parent !== 36) {
    WP_CLI::error('Page 898 is not the VISA I-20 Assistance child of General.');
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
    '06fc8b6' => ['shortcode'], '0afdcc5' => ['title'], '39864a0' => ['editor'],
    '97fd8f9' => ['editor'], '11c7185' => ['editor'], '9766170' => ['editor'],
    '6f524af' => ['title'], '9c0ea31' => ['editor'], 'be0c041' => ['editor'],
    '2428553' => ['title'], '12ef8a1' => ['icon_list'], '2bd928a' => ['title'],
    '96e8c90' => ['editor'], 'c15c1f5' => ['editor'], 'bd01d63' => ['title'],
    'adec432' => ['editor'], '560bef8' => ['editor'], 'fdd7f3c' => ['editor'],
    '3efbc28' => ['editor'], '20f3b50' => ['title'], 'a09b3a9' => ['editor'],
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
$backup = '/tmp/visa-i20-898-before-' . $stamp . '.json';
$backup_json = wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (false === file_put_contents($backup, $backup_json)) {
    WP_CLI::error('Could not create backup. Nothing was changed.');
}
file_put_contents('/tmp/visa-i20-898-before-latest.json', $backup_json);
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
$image_ids = [987, 1699];
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
    $id = substr(md5('saber-visa-' . $name), 0, 7);
    $class = 'e-visa-' . $name;
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
        'id' => substr(md5('visa-icon-' . $name), 0, 7), 'elType' => 'widget',
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
        'id' => substr(md5('visa-image-' . $name), 0, 7), 'elType' => 'widget',
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
        'typography_font_weight' => '500',
        'typography_font_size' => ['unit' => 'px', 'size' => 16, 'sizes' => []],
        'typography_font_size_mobile' => ['unit' => 'px', 'size' => 15, 'sizes' => []],
        'typography_line_height' => ['unit' => 'em', 'size' => 1.55, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ]);
    return $widget;
};

$breadcrumbs = $section('breadcrumbs', [$inner('breadcrumbs-inner', [$widgets['06fc8b6']], [
    'padding' => $pad(20, 24, 20, 24), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'),
], [], ['padding' => $pad(68, 20, 18, 20)])], '#ffffff');

$rule = [
    'id' => 'visrule', 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [],
    'settings' => [
        'color' => '#fdc800', 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []],
        'width' => ['unit' => 'px', 'size' => 72, 'sizes' => []],
        'gap' => ['unit' => 'px', 'size' => 5, 'sizes' => []], 'align' => 'left',
    ],
];
$hero_copy = $container('hero-copy', [
    $rule, $heading('0afdcc5', 49, 36, '#001a39', 'h1'), $text('39864a0', '#29435f', 18),
    $container('document-link', [$icon('document', 'file-pdf', '#25377b', 24), $text('97fd8f9', '#001a39', 16, '800')], [
        'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
        'gap' => $size(14), 'padding' => $pad(16, 20, 16, 20), 'background' => $bg('#ffffff'),
        'border-radius' => $size(14), 'max-width' => $size(420),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#d8e3ec'),
        'border-width' => $size(1),
    ]),
], [
    'width' => $size(55, '%'), 'gap' => $size(20), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$hero_visual = $container('hero-visual', [
    $image('international-students', 987, 'International students studying in the United States', 500),
], [
    'width' => $size(45, '%'), 'min-height' => $size(500), 'overflow' => $typed('string', 'hidden'),
    'border-radius' => $size(28), 'background' => $bg('#ffffff'),
], [], ['width' => $size(100, '%'), 'min-height' => $size(310)]);
$hero = $section('hero', [$inner('hero-inner', [$hero_copy, $hero_visual], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(48), 'padding' => $pad(46, 24, 68, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#f3f9ff', $assets['path-light']);

$welcome_cards = [
    $container('welcome-one', [$icon('community', 'globe-americas', '#25377b', 34), $text('11c7185')], [
        'width' => $size(49, '%'), 'padding' => $pad(30, 32, 32, 32), 'gap' => $size(18),
        'background' => $bg('#ffffff'), 'border-radius' => $size(20),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#dce5ec'), 'border-width' => $size(1),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(24)]),
    $container('welcome-two', [$icon('welcome', 'users', '#25377b', 34), $text('9766170')], [
        'width' => $size(49, '%'), 'padding' => $pad(30, 32, 32, 32), 'gap' => $size(18),
        'background' => $bg('#fff8e4'), 'border-radius' => $size(20),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#eadcae'), 'border-width' => $size(1),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(24)]),
];
$welcome = $section('welcome', [$inner('welcome-inner', $welcome_cards, [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'stretch'), 'gap' => $size(22),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)])], '#ffffff');

$help_cards = $container('help-cards', [
    $container('help-details', [$icon('guidance', 'compass', '#25377b', 32), $text('9c0ea31')], [
        'width' => $size(49, '%'), 'padding' => $pad(28, 30, 30, 30), 'gap' => $size(16),
        'background' => $bg('#ffffff'), 'border-radius' => $size(18),
    ], [], ['width' => $size(100, '%')]),
    $container('help-services', [$icon('paperwork', 'passport', '#25377b', 32), $text('be0c041')], [
        'width' => $size(49, '%'), 'padding' => $pad(28, 30, 30, 30), 'gap' => $size(16),
        'background' => $bg('#ffffff'), 'border-radius' => $size(18),
    ], [], ['width' => $size(100, '%')]),
], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'), 'gap' => $size(22),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)]);
$help = $section('help', [$inner('help-inner', [
    $heading('6f524af', 38, 30), $help_cards,
], ['gap' => $size(30)])], '#fff8e4', $assets['path-warm']);

$why = $section('why', [$inner('why-inner', [
    $container('why-heading', [$icon('why', 'plane-departure', '#25377b', 38), $heading('2428553', 38, 30)], [
        'width' => $size(100, '%'), 'align-items' => $typed('string', 'center'), 'gap' => $size(14),
    ]),
    $container('why-list', [$list('12ef8a1')], ['width' => $size(100, '%')]),
], ['gap' => $size(32)])], '#ffffff');

$cert_copy = $container('cert-copy', [
    $icon('certified', 'shield-alt', '#fdc800', 42), $heading('2bd928a', 38, 30, '#ffffff'),
    $text('96e8c90', '#e8f2fb', 17), $text('c15c1f5', '#e8f2fb', 17),
], [
    'width' => $size(72, '%'), 'gap' => $size(19),
], [], ['width' => $size(100, '%')]);
$cert_mark = $container('cert-mark', [
    $icon('federal', 'landmark', '#25377b', 56),
], [
    'width' => $size(28, '%'), 'min-height' => $size(250), 'align-items' => $typed('string', 'center'),
    'justify-content' => $typed('string', 'center'), 'background' => $bg('#fdc800'), 'border-radius' => $size(24),
], [], ['width' => $size(100, '%'), 'min-height' => $size(150)]);
$cert = $section('certification', [$inner('cert-inner', [$cert_copy, $cert_mark], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(48), 'padding' => $pad(76, 24, 82, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#082b55', $assets['path-navy']);

$process_intro = $container('process-heading', [
    $icon('process', 'clipboard-list', '#25377b', 38), $heading('bd01d63', 40, 30), $text('adec432', '#29435f', 18),
], ['width' => $size(100, '%'), 'gap' => $size(15)]);
$process_steps = $container('process-steps', [$text('560bef8', '#29435f', 16)], ['width' => $size(100, '%')]);
$process_notes = $container('process-notes', [
    $container('confirmation-note', [$icon('confirmation', 'check-circle', '#25377b', 30), $text('fdd7f3c')], [
        'width' => $size(66, '%'), 'padding' => $pad(26, 28, 28, 28), 'gap' => $size(15),
        'background' => $bg('#ffffff'), 'border-radius' => $size(18),
    ], [], ['width' => $size(100, '%')]),
    $container('shipping-note', [$icon('shipping', 'shipping-fast', '#25377b', 30), $text('3efbc28', '#001a39', 17, '800')], [
        'width' => $size(34, '%'), 'padding' => $pad(26, 28, 28, 28), 'gap' => $size(15),
        'background' => $bg('#fdc800'), 'border-radius' => $size(18),
    ], [], ['width' => $size(100, '%')]),
], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'stretch'), 'gap' => $size(20),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)]);
$process = $section('process', [$inner('process-inner', [$process_intro, $process_steps, $process_notes], [
    'gap' => $size(34), 'padding' => $pad(74, 24, 82, 24),
])], '#fff8e4', $assets['path-warm']);

$closing_copy = $container('closing-copy', [
    $icon('dream', 'graduation-cap', '#fdc800', 40), $heading('20f3b50', 40, 31, '#ffffff'),
    $text('a09b3a9', '#e8f2fb', 18),
], [
    'width' => $size(54, '%'), 'gap' => $size(18), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$closing_visual = $container('closing-visual', [
    $image('campus', 1699, 'SABER College campus in Miami', 360),
], [
    'width' => $size(46, '%'), 'overflow' => $typed('string', 'hidden'), 'border-radius' => $size(24),
], [], ['width' => $size(100, '%')]);
$closing = $section('closing', [$inner('closing-inner', [$closing_copy, $closing_visual], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(46), 'padding' => $pad(72, 24, 78, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(28)])], '#082b55', $assets['path-navy']);

$new_data = [$breadcrumbs, $hero, $welcome, $help, $why, $cert, $process, $closing];
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
/* VISA I-20 Assistance: Design System 0.2 page polish. */
.elementor-898 .rank-math-breadcrumb p { margin: 0; }
.elementor-898 .elementor-widget-text-editor p { margin-top: 0; }
.elementor-898 .elementor-widget-text-editor p:last-child { margin-bottom: 0; }
.elementor-898 a:focus-visible { outline: 3px solid #005fbd; outline-offset: 5px; box-shadow: 0 0 0 7px #fff; border-radius: 3px; }
.elementor-898 .e-visa-hero-visual img,
.elementor-898 .e-visa-closing-visual img { display: block; }
.elementor-898 .e-visa-document-link p { margin: 0; }
.elementor-898 .e-visa-document-link a { color: #001a39; text-decoration: none; }
.elementor-898 .e-visa-document-link:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(8,43,85,.10); }
.elementor-898 .e-visa-document-link { transition: transform .2s ease, box-shadow .2s ease; }
.elementor-898 .e-visa-cert-copy a { color: #fdc800; font-weight: 800; }
.elementor-898 .e-visa-why-list .elementor-icon-list-items {
  display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; margin: 0; padding: 0;
}
.elementor-898 .e-visa-why-list .elementor-icon-list-item {
  position: relative; display: flex; align-items: flex-start; min-height: 230px; margin: 0;
  padding: 76px 26px 28px; background: #f3f9ff; border: 1px solid #dce5ec;
  border-radius: 18px; box-shadow: 0 12px 32px rgba(8,43,85,.045);
}
.elementor-898 .e-visa-why-list .elementor-icon-list-item::before {
  content: ''; position: absolute; left: 26px; top: 24px; width: 42px; height: 42px;
  border-radius: 50%; background: #fdc800 url('/wp-content/uploads/2026/09/icon-world.png') center/23px no-repeat;
}
.elementor-898 .e-visa-why-list .elementor-icon-list-icon { margin-top: 4px; }
.elementor-898 .e-visa-why-list .elementor-icon-list-text strong { color: #001a39; }
.elementor-898 .e-visa-process-steps ol {
  display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px;
  margin: 0; padding: 0; list-style: none; counter-reset: i20-step;
}
.elementor-898 .e-visa-process-steps li {
  counter-increment: i20-step; position: relative; min-height: 210px; margin: 0;
  padding: 76px 28px 28px; background: #fff; border: 1px solid #e4dbc4;
  border-radius: 18px; box-shadow: 0 12px 32px rgba(8,43,85,.045);
}
.elementor-898 .e-visa-process-steps li::before {
  content: counter(i20-step, decimal-leading-zero); position: absolute; left: 28px; top: 24px;
  display: grid; place-items: center; width: 42px; height: 42px; border-radius: 50%;
  background: #082b55; color: #fff; font: 800 14px/1 Nunito, Arial, sans-serif;
}
@media (max-width: 767px) {
  .elementor-898 .e-visa-why-list .elementor-icon-list-items,
  .elementor-898 .e-visa-process-steps ol { grid-template-columns: 1fr; }
  .elementor-898 .e-visa-why-list .elementor-icon-list-item,
  .elementor-898 .e-visa-process-steps li { min-height: 0; }
}
@media (prefers-reduced-motion: reduce) {
  .elementor-898 .e-visa-document-link { transition: none; }
}
CSS;
$existing_css = $page_settings['custom_css'] ?? '';
$marker = '/* VISA I-20 Assistance: Design System 0.2 page polish. */';
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
file_put_contents('/tmp/visa-i20-redesign-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('VISA I-20 Assistance redesigned. Original content, links and SEO fields are preserved.');
