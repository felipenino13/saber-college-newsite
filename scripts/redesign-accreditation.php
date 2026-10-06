<?php
/**
 * Apply SABER Design System 0.2 to Accreditation (page 887).
 * Existing copy, Rank Math data and protected templates are preserved.
 */
if (!defined('ABSPATH')) { exit(1); }

$post_id = 887;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'saber-college-accreditation' || (int) $page->post_parent !== 0) {
    WP_CLI::error('Page 887 is not the root SABER College Accreditation page.');
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
    'b14cb89' => ['shortcode'], '37f289a' => ['title'], '6521486' => ['editor'],
    '212ee7c' => ['editor'], '44bf79b' => ['editor'], 'ab1e840' => ['editor'],
    'ebdf51a' => ['editor'], '3c770bd' => ['editor'], 'ea82a31' => ['editor'],
    '1ed484a' => ['editor'], 'fb7874a' => ['editor'],
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
$backup = '/tmp/accreditation-887-before-' . $stamp . '.json';
$backup_json = wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (false === file_put_contents($backup, $backup_json)) {
    WP_CLI::error('Could not create backup. Nothing was changed.');
}
file_put_contents('/tmp/accreditation-887-before-latest.json', $backup_json);
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
$image_ids = [1699, 691, 458, 84, 1701];
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
    $id = substr(md5('saber-accred-' . $name), 0, 7);
    $class = 'e-accred-' . $name;
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
        'id' => substr(md5('accred-icon-' . $name), 0, 7), 'elType' => 'widget',
        'widgetType' => 'icon', 'elements' => [],
        'settings' => [
            'selected_icon' => ['value' => 'fas fa-' . $glyph, 'library' => 'fa-solid'],
            'view' => 'default', 'primary_color' => $color, 'align' => 'left',
            'size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
            '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
    ];
};
$image = static function ($name, $attachment_id, $alt, $width = 100, $height = 0) {
    $settings = [
        'image' => ['id' => $attachment_id, 'url' => wp_get_attachment_url($attachment_id), 'alt' => $alt, 'source' => 'library'],
        'image_size' => 'full', 'width' => ['unit' => '%', 'size' => $width, 'sizes' => []],
        '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
    ];
    if ($height) {
        $settings['height'] = ['unit' => 'px', 'size' => $height, 'sizes' => []];
        $settings['height_mobile'] = ['unit' => 'px', 'size' => 300, 'sizes' => []];
        $settings['object-fit'] = 'cover';
        $settings['object-position'] = 'center center';
    }
    return [
        'id' => substr(md5('accred-image-' . $name), 0, 7), 'elType' => 'widget',
        'widgetType' => 'image', 'elements' => [], 'settings' => $settings,
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

$breadcrumbs = $section('breadcrumbs', [$inner('breadcrumbs-inner', [$widgets['b14cb89']], [
    'padding' => $pad(20, 24, 20, 24), 'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'),
], [], ['padding' => $pad(68, 20, 18, 20)])], '#ffffff');

$rule = [
    'id' => 'accrule', 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [],
    'settings' => [
        'color' => '#fdc800', 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []],
        'width' => ['unit' => 'px', 'size' => 72, 'sizes' => []],
        'gap' => ['unit' => 'px', 'size' => 5, 'sizes' => []], 'align' => 'left',
    ],
];
$hero_copy = $container('hero-copy', [
    $rule, $heading('37f289a', 46, 34, '#001a39', 'h1'), $text('6521486', '#29435f', 18),
], [
    'width' => $size(54, '%'), 'gap' => $size(20), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$hero_visual = $container('hero-visual', [
    $image('campus', 1699, 'SABER College campus in Miami', 100, 430),
], [
    'width' => $size(46, '%'), 'min-height' => $size(430), 'overflow' => $typed('string', 'hidden'),
    'border-radius' => $size(28), 'background' => $bg('#dfeefa'),
], [], ['width' => $size(100, '%'), 'min-height' => $size(300)]);
$logo_strip = $container('logo-strip', [
    $image('cie', 691, 'Florida Commission for Independent Education', 100),
    $image('coe', 458, 'Council on Occupational Education', 100),
    $image('capte', 84, 'Commission on Accreditation in Physical Therapy Education', 100),
], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'center'), 'justify-content' => $typed('string', 'space-around'),
    'gap' => $size(34), 'padding' => $pad(24, 34, 24, 34),
    'background' => $bg('#ffffff'), 'border-radius' => $size(18),
    'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#dce5ec'),
    'border-width' => $size(1),
], [], ['gap' => $size(22), 'padding' => $pad(20, 18, 20, 18)]);
$hero = $section('hero', [$inner('hero-inner', [
    $container('hero-row', [$hero_copy, $hero_visual], [
        'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'), 'gap' => $size(48),
    ], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)]),
    $logo_strip,
], [
    'gap' => $size(34), 'padding' => $pad(46, 24, 64, 24),
])], '#f3f9ff', $assets['path-light']);

$credential_cards = [
    $container('cie-card', [
        $container('cie-mark', [$image('cie-card-logo', 691, 'Florida Commission for Independent Education', 100)], [
            'width' => $size(112), 'height' => $size(112), 'align-items' => $typed('string', 'center'),
            'justify-content' => $typed('string', 'center'), 'padding' => $size(18),
            'background' => $bg('#fff8e4'), 'border-radius' => $size(18),
        ]),
        $text('212ee7c', '#29435f', 17),
    ], [
        'width' => $size(49, '%'), 'padding' => $pad(30, 32, 32, 32), 'gap' => $size(22),
        'background' => $bg('#ffffff'), 'border-radius' => $size(20),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#e4dbc4'),
        'border-width' => $size(1),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(24)]),
    $container('coe-card', [
        $container('coe-mark', [$image('coe-card-logo', 458, 'Council on Occupational Education', 100)], [
            'width' => $size(112), 'height' => $size(112), 'align-items' => $typed('string', 'center'),
            'justify-content' => $typed('string', 'center'), 'padding' => $size(14),
            'background' => $bg('#f3f9ff'), 'border-radius' => $size(18),
        ]),
        $text('44bf79b', '#29435f', 17),
    ], [
        'width' => $size(49, '%'), 'padding' => $pad(30, 32, 32, 32), 'gap' => $size(22),
        'background' => $bg('#ffffff'), 'border-radius' => $size(20),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#e4dbc4'),
        'border-width' => $size(1),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(24)]),
];
$credentials = $section('credentials', [$inner('credentials-inner', $credential_cards, [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'stretch'),
    'gap' => $size(22), 'padding' => $pad(72, 24, 78, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)])], '#fff8e4', $assets['path-warm']);

$capte_visual = $container('capte-visual', [
    $image('pta-care', 1701, 'Physical Therapist Assistant supporting a patient', 100, 500),
    $container('capte-badge', [$image('capte-badge-logo', 84, 'CAPTE accreditation', 100)], [
        'width' => $size(154), 'padding' => $size(14), 'background' => $bg('#ffffff'),
        'border-radius' => $size(18),
    ]),
], [
    'width' => $size(40, '%'), 'gap' => $size(18), 'padding' => $size(18),
    'background' => $bg('#ffffff'), 'border-radius' => $size(24),
], [], ['width' => $size(100, '%'), 'padding' => $size(14)]);
$capte_copy = $container('capte-copy', [
    $icon('capte-shield', 'award', '#fdc800', 38),
    $text('ab1e840', '#ffffff', 17),
    $container('capte-contact', [$text('ebdf51a', '#dcecff', 16)], [
        'width' => $size(100, '%'), 'padding' => $pad(24, 26, 24, 26),
        'background' => $bg('rgba(255,255,255,.075)'), 'border-radius' => $size(16),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', 'rgba(255,255,255,.18)'),
        'border-width' => $size(1),
    ], [], ['padding' => $size(20)]),
], [
    'width' => $size(60, '%'), 'gap' => $size(22), 'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$capte = $section('capte', [$inner('capte-inner', [$capte_visual, $capte_copy], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(48), 'padding' => $pad(76, 24, 82, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#082b55', $assets['path-navy']);

$access_cards = [
    $container('title-four-card', [
        $icon('title-four', 'university', '#25377b', 34), $text('3c770bd', '#29435f', 17),
    ], [
        'width' => $size(49, '%'), 'padding' => $pad(30, 32, 32, 32), 'gap' => $size(20),
        'background' => $bg('#ffffff'), 'border-radius' => $size(20),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#dce5ec'),
        'border-width' => $size(1),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(24)]),
    $container('rehab-card', [
        $icon('rehab', 'hands-helping', '#25377b', 34), $text('ea82a31', '#29435f', 17),
    ], [
        'width' => $size(49, '%'), 'padding' => $pad(30, 32, 32, 32), 'gap' => $size(20),
        'background' => $bg('#f3f9ff'), 'border-radius' => $size(20),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#dce5ec'),
        'border-width' => $size(1),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(24)]),
];
$access = $section('access', [$inner('access-inner', $access_cards, [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'stretch'),
    'gap' => $size(22), 'padding' => $pad(72, 24, 78, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)])], '#ffffff');

$commitment_copy = $container('commitment-copy', [
    $icon('commitment', 'shield-alt', '#25377b', 38), $text('1ed484a', '#29435f', 18),
], [
    'width' => $size(68, '%'), 'gap' => $size(20),
], [], ['width' => $size(100, '%')]);
$commitment_cta = $container('commitment-cta', [
    $icon('contact', 'comments', '#25377b', 34), $text('fb7874a', '#001a39', 18, '800'),
], [
    'width' => $size(32, '%'), 'padding' => $pad(28, 30, 28, 30), 'gap' => $size(16),
    'background' => $bg('#fdc800'), 'border-radius' => $size(18),
], [], ['width' => $size(100, '%'), 'padding' => $size(24)]);
$commitment = $section('commitment', [$inner('commitment-inner', [$commitment_copy, $commitment_cta], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(42), 'padding' => $pad(68, 24, 72, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(24)])], '#f3f9ff', $assets['path-light']);

$new_data = [$breadcrumbs, $hero, $credentials, $capte, $access, $commitment];
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
/* Accreditation: Design System 0.2 page polish. */
.elementor-887 .rank-math-breadcrumb p { margin: 0; }
.elementor-887 .elementor-widget-text-editor p { margin-top: 0; }
.elementor-887 .elementor-widget-text-editor p:last-child { margin-bottom: 0; }
.elementor-887 a:focus-visible { outline: 3px solid #005fbd; outline-offset: 5px; box-shadow: 0 0 0 7px #fff; border-radius: 3px; }
.elementor-887 .e-accred-hero-visual img,
.elementor-887 .e-accred-capte-visual > .elementor-element:first-child img { display: block; }
.elementor-887 .e-accred-logo-strip .elementor-widget-image { max-width: 116px; }
.elementor-887 .e-accred-logo-strip .elementor-widget-image:last-child { max-width: 136px; }
.elementor-887 .e-accred-cie-mark .elementor-widget-image,
.elementor-887 .e-accred-coe-mark .elementor-widget-image { width: 76px; }
.elementor-887 .e-accred-capte-badge .elementor-widget-image { width: 124px; }
@media (max-width: 767px) {
  .elementor-887 .e-accred-logo-strip .elementor-widget-image { max-width: 78px; }
  .elementor-887 .e-accred-logo-strip .elementor-widget-image:last-child { max-width: 94px; }
}
CSS;
$existing_css = $page_settings['custom_css'] ?? '';
$marker = '/* Accreditation: Design System 0.2 page polish. */';
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
file_put_contents('/tmp/accreditation-redesign-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('Accreditation redesigned. Original content and SEO fields are preserved.');
