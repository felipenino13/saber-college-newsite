<?php
/** Create/update the public, noindex Elementor component library for SABER College. */
if (!defined('ABSPATH')) { exit(1); }

$slug = 'saber-design-system-elementor';
$page = get_page_by_path($slug, OBJECT, 'page');
$created = false;
$protected_before = [];
foreach ([7, 16, 53] as $protected) {
    $protected_before[$protected] = hash('sha256', get_post_meta($protected, '_elementor_data', true));
}
if (!$page) {
    $post_id = wp_insert_post([
        'post_type' => 'page', 'post_status' => 'publish',
        'post_title' => 'SABER Design System – Elementor', 'post_name' => $slug,
        'post_content' => '', 'post_excerpt' => '', 'post_parent' => 0,
    ], true);
    if (is_wp_error($post_id)) { WP_CLI::error($post_id->get_error_message()); }
    $created = true;
} else {
    $post_id = $page->ID;
    $backup = '/tmp/saber-design-system-elementor-' . $post_id . '-before-' . gmdate('Ymd-His') . '.json';
    file_put_contents($backup, wp_json_encode([
        'post' => (array) $page, 'meta' => get_post_meta($post_id), 'created_utc' => gmdate('c'),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    WP_CLI::line('BACKUP=' . $backup);
}

/* Register the controlled dark pattern if it is not already available. */
$assets = [];
foreach (['path-light', 'path-warm', 'path-navy'] as $asset) {
    $existing = get_posts([
        'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1,
        'fields' => 'ids', 'meta_key' => '_saber_design_asset', 'meta_value' => $asset . '-v02',
    ]);
    if ($existing) { $assets[$asset] = $existing[0]; continue; }
    $source = '/tmp/' . $asset . '.svg';
    if (!is_readable($source)) { WP_CLI::error("Missing controlled asset $source."); }
    $svg = file_get_contents($source);
    if (preg_match('/<script|<foreignObject|\bon\w+\s*=|(?:href|src)\s*=/i', $svg)) {
        WP_CLI::error("Unexpected SVG content in $asset.");
    }
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
    wp_update_attachment_metadata($attachment, [
        'width' => 1600, 'height' => 900, 'file' => 'saber-design-system/' . $filename, 'sizes' => [],
    ]);
    update_post_meta($attachment, '_saber_design_asset', $asset . '-v02');
    $assets[$asset] = $attachment;
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
    $id = substr(md5('saber-elementor-ds-' . $name), 0, 7);
    $class = 'e-ds-' . $name;
    $desktop = array_merge([
        'display' => $typed('string', 'flex'), 'flex-direction' => $typed('string', 'column'),
        'min-width' => $size(0),
    ], $desktop);
    $variants = [['meta' => ['breakpoint' => 'desktop', 'state' => null], 'props' => $desktop, 'custom_css' => null]];
    foreach (['tablet' => $tablet, 'mobile' => $mobile] as $breakpoint => $props) {
        if ($props) { $variants[] = ['meta' => ['breakpoint' => $breakpoint, 'state' => null], 'props' => $props, 'custom_css' => null]; }
    }
    if ($hover) { $variants[] = ['meta' => ['breakpoint' => 'desktop', 'state' => 'hover'], 'props' => $hover, 'custom_css' => null]; }
    return [
        'id' => $id, 'elType' => 'e-flexbox', 'settings' => ['classes' => $typed('classes', [$class])],
        'elements' => $children, 'isInner' => false,
        'styles' => [$class => [
            'id' => $class, 'label' => $class, 'type' => 'class', 'variants' => $variants,
        ]],
        'interactions' => [], 'editor_settings' => [], 'version' => '0.0',
    ];
};
$inner = static function ($name, $children, $extra = [], $tablet = [], $mobile = []) use ($container, $size, $pad) {
    return $container($name, $children, array_merge([
        'width' => $size(100, '%'), 'max-width' => $size(1200),
        'padding' => $pad(76, 24, 82, 24), 'gap' => $size(36),
    ], $extra), $tablet, array_merge(['padding' => $pad(52, 20, 58, 20), 'gap' => $size(28)], $mobile));
};
$section = static function ($name, $children, $color, $attachment = 0) use ($container, $size, $typed, $bg) {
    return $container($name, $children, [
        'width' => $size(100, '%'), 'align-items' => $typed('string', 'center'),
        'background' => $bg($color, $attachment),
    ]);
};
$heading = static function ($name, $text, $tag = 'h2', $desktop = 42, $mobile = 32, $color = '#001a39', $weight = '800') {
    return [
        'id' => substr(md5('ds-heading-' . $name), 0, 7), 'elType' => 'widget', 'widgetType' => 'heading', 'elements' => [],
        'settings' => [
            'title' => $text, 'header_size' => $tag, 'title_color' => $color,
            'typography_typography' => 'custom', 'typography_font_family' => 'Nunito',
            'typography_font_weight' => $weight,
            'typography_font_size' => ['unit' => 'px', 'size' => $desktop, 'sizes' => []],
            'typography_font_size_mobile' => ['unit' => 'px', 'size' => $mobile, 'sizes' => []],
            'typography_line_height' => ['unit' => 'em', 'size' => 1.12, 'sizes' => []],
            'typography_letter_spacing' => ['unit' => 'px', 'size' => $desktop >= 40 ? -0.8 : 0, 'sizes' => []],
            '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
    ];
};
$text_widget = static function ($name, $html, $font_size = 17, $color = '#29435f', $weight = '500') {
    return [
        'id' => substr(md5('ds-text-' . $name), 0, 7), 'elType' => 'widget', 'widgetType' => 'text-editor', 'elements' => [],
        'settings' => [
            'editor' => $html, 'text_color' => $color, 'link_color' => '#005fbd', 'link_hover_color' => '#003f7d',
            'typography_typography' => 'custom', 'typography_font_family' => 'Nunito',
            'typography_font_weight' => $weight,
            'typography_font_size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
            'typography_font_size_mobile' => ['unit' => 'px', 'size' => min(16, $font_size), 'sizes' => []],
            'typography_line_height' => ['unit' => 'em', 'size' => 1.62, 'sizes' => []],
            '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
    ];
};
$icon = static function ($name, $glyph, $color = '#25377b', $font_size = 28) {
    return [
        'id' => substr(md5('ds-icon-' . $name), 0, 7), 'elType' => 'widget', 'widgetType' => 'icon', 'elements' => [],
        'settings' => [
            'selected_icon' => ['value' => 'fas fa-' . $glyph, 'library' => 'fa-solid'],
            'view' => 'default', 'primary_color' => $color, 'align' => 'left',
            'size' => ['unit' => 'px', 'size' => $font_size, 'sizes' => []],
            '_margin' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
    ];
};
$divider = static function ($name, $color = '#fdc800', $width = 72) {
    return [
        'id' => substr(md5('ds-divider-' . $name), 0, 7), 'elType' => 'widget', 'widgetType' => 'divider', 'elements' => [],
        'settings' => [
            'color' => $color, 'weight' => ['unit' => 'px', 'size' => 4, 'sizes' => []],
            'width' => ['unit' => 'px', 'size' => $width, 'sizes' => []],
            'gap' => ['unit' => 'px', 'size' => 4, 'sizes' => []], 'align' => 'left',
        ],
    ];
};
$button = static function ($name, $label, $url, $variant = 'primary') {
    $palette = [
        'primary' => ['#001a39', '#fdc800', '#082b55', '#ffd633'],
        'secondary' => ['#ffffff', '#082b55', '#ffffff', '#25377b'],
        'outline' => ['#082b55', '#ffffff', '#ffffff', '#f3f9ff'],
    ][$variant];
    return [
        'id' => substr(md5('ds-button-' . $name), 0, 7), 'elType' => 'widget', 'widgetType' => 'button', 'elements' => [],
        'settings' => [
            'text' => $label, 'link' => ['url' => $url], 'size' => 'md', 'align' => 'left',
            'selected_icon' => ['value' => 'fas fa-arrow-right', 'library' => 'fa-solid'], 'icon_align' => 'right',
            'button_text_color' => $palette[0], 'background_color' => $palette[1],
            'button_hover_color' => $palette[2], 'button_background_hover_color' => $palette[3],
            'border_border' => 'solid', 'border_width' => ['unit' => 'px', 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => true],
            'border_color' => $variant === 'outline' ? '#082b55' : $palette[1],
            'border_radius' => ['unit' => 'px', 'top' => '10', 'right' => '10', 'bottom' => '10', 'left' => '10', 'isLinked' => true],
            'typography_typography' => 'custom', 'typography_font_family' => 'Nunito', 'typography_font_weight' => '800',
            'typography_font_size' => ['unit' => 'px', 'size' => 15, 'sizes' => []],
        ],
    ];
};
$image_widget = static function ($name, $attachment, $height = 360) {
    return [
        'id' => substr(md5('ds-image-' . $name), 0, 7), 'elType' => 'widget', 'widgetType' => 'image', 'elements' => [],
        'settings' => [
            'image' => ['id' => $attachment, 'url' => wp_get_attachment_url($attachment), 'source' => 'library'],
            'image_size' => 'large', 'width' => ['unit' => '%', 'size' => 100, 'sizes' => []],
            'height' => ['unit' => 'px', 'size' => $height, 'sizes' => []],
            'height_mobile' => ['unit' => 'px', 'size' => 240, 'sizes' => []],
            'object-fit' => 'cover', 'object-position' => 'center center',
            'image_border_radius' => ['unit' => 'px', 'top' => '64', 'right' => '16', 'bottom' => '64', 'left' => '16', 'isLinked' => false],
        ],
    ];
};
$section_header = static function ($name, $eyebrow, $title, $description, $dark = false) use ($container, $heading, $text_widget, $size, $typed) {
    return $container('header-' . $name, [
        $heading($name . '-eyebrow', $eyebrow, 'p', 14, 14, $dark ? '#fdc800' : '#25377b', '800'),
        $heading($name . '-title', $title, 'h2', 42, 32, $dark ? '#ffffff' : '#001a39'),
        $text_widget($name . '-desc', '<p>' . $description . '</p>', 17, $dark ? '#ffffff' : '#52677b'),
    ], ['max-width' => $size(760), 'gap' => $size(10), 'align-self' => $typed('string', 'flex-start')]);
};

/* Hero */
$hero = $section('hero', [$inner('hero-inner', [
    $container('hero-copy', [
        $divider('hero'),
        $heading('hero-title', 'SABER Design System – Elementor', 'h1', 64, 42),
        $text_widget('hero-lead', '<p>A reusable component library for clear, human and professional SABER College pages.</p>', 20, '#29435f', '600'),
        $container('hero-actions', [
            $button('hero-primary', 'Primary action', '#components', 'primary'),
            $button('hero-secondary', 'Explore patterns', '#patterns', 'outline'),
        ], ['flex-direction' => $typed('string', 'row'), 'gap' => $size(14), 'flex-wrap' => $typed('string', 'wrap')]),
    ], ['width' => $size(54, '%'), 'gap' => $size(20), 'justify-content' => $typed('string', 'center')], [], ['width' => $size(100, '%')]),
    $container('hero-visual', [$image_widget('hero', 1699, 380)], ['width' => $size(46, '%')], [], ['width' => $size(100, '%')]),
], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'center'),
    'gap' => $size(54), 'padding' => $pad(58, 24, 68, 24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#f3f9ff', $assets['path-light']);

/* Foundations */
$swatches = [];
$colors = [
    ['ink', 'Ink', '#001A39', '#ffffff'], ['navy', 'Navy', '#082B55', '#ffffff'],
    ['blue', 'Blue', '#25377B', '#ffffff'], ['link', 'Link', '#005FBD', '#ffffff'],
    ['yellow', 'Yellow', '#FDC800', '#001a39'], ['sky', 'Sky', '#F3F9FF', '#001a39'],
    ['warm', 'Warm', '#FFF8E4', '#001a39'], ['paper', 'Paper', '#FFFFFF', '#001a39'],
];
foreach ($colors as [$key, $label, $hex, $text_color]) {
    $swatches[] = $container('swatch-' . $key, [
        $heading('swatch-' . $key, $label, 'h3', 20, 18, $text_color),
        $text_widget('swatch-' . $key, '<p>' . $hex . '</p>', 14, $text_color, '700'),
    ], [
        'width' => $size(23.3, '%'), 'min-height' => $size(132), 'padding' => $size(22),
        'justify-content' => $typed('string', 'space-between'), 'background' => $bg($hex),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', $hex === '#FFFFFF' ? '#dce5ec' : $hex),
        'border-width' => $size(1), 'border-radius' => $size(14),
    ], ['width' => $size(48, '%')], ['width' => $size(100, '%')]);
}
$foundations = $section('foundations', [$inner('foundations-inner', [
    $section_header('foundations', '01 / FOUNDATIONS', 'Colors, type and spacing', 'Copy these tokens and preserve the visual rhythm across every page.'),
    $container('swatch-grid', $swatches, [
        'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
        'flex-wrap' => $typed('string', 'wrap'), 'gap' => $size(22),
    ], ['gap' => $size(18)], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(14)]),
    $container('type-panel', [
        $heading('type-display', 'Display / Care meets confidence', 'h2', 58, 38),
        $heading('type-h2', 'Section heading / A clear way forward', 'h3', 38, 30),
        $heading('type-h3', 'Card heading / Student support', 'h4', 25, 22),
        $text_widget('type-body', '<p>Body / Nunito keeps long-form information approachable, with generous line height and a practical reading width.</p>', 17),
        $text_widget('type-small', '<p>SMALL / LABELS, HELP TEXT AND METADATA</p>', 13, '#52677b', '800'),
    ], [
        'width' => $size(100, '%'), 'padding' => $pad(38, 40, 38, 40), 'gap' => $size(18),
        'background' => $bg('#ffffff'), 'border-style' => $typed('string', 'solid'),
        'border-color' => $typed('color', '#dce5ec'), 'border-width' => $size(1), 'border-radius' => $size(18),
    ], [], ['padding' => $size(24)]),
])], '#ffffff');

/* Surfaces */
$surface_specs = [
    ['light', 'Open path', 'Use for interior heroes and focused introductions.', '#f3f9ff', $assets['path-light'], false],
    ['warm', 'Warm welcome', 'Use for requirements, benefits and reading groups.', '#fff8e4', $assets['path-warm'], false],
    ['trust', 'Trust moment', 'Use once per journey for confidence and institutional proof.', '#082b55', $assets['path-navy'], true],
];
$surface_cards = [];
foreach ($surface_specs as [$key, $title_text, $copy, $color, $asset, $dark]) {
    $surface_cards[] = $container('surface-' . $key, [
        $divider('surface-' . $key, '#fdc800', 52),
        $heading('surface-' . $key, $title_text, 'h3', 28, 24, $dark ? '#ffffff' : '#001a39'),
        $text_widget('surface-' . $key, '<p>' . $copy . '</p>', 16, $dark ? '#ffffff' : '#52677b'),
    ], [
        'width' => $size(31.8, '%'), 'min-height' => $size(270), 'padding' => $size(30),
        'gap' => $size(16), 'justify-content' => $typed('string', 'flex-end'),
        'background' => $bg($color, $asset), 'border-radius' => $size(18),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', $dark ? '#25377b' : '#dce5ec'),
        'border-width' => $size(1),
    ], [], ['width' => $size(100, '%')]);
}
$surfaces = $section('surfaces', [$inner('surfaces-inner', [
    $section_header('surfaces', '02 / SURFACES', 'Backgrounds with identity', 'Use light geometry to add depth while keeping white space dominant.'),
    $container('surface-grid', $surface_cards, [
        'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'), 'gap' => $size(24),
    ], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(16)]),
])], '#f3f7fa');

/* Components */
$service_card = $container('component-service', [
    $icon('component-service', 'user-graduate', '#fdc800', 32),
    $heading('component-service', 'Student services', 'h3', 27, 23, '#ffffff'),
    $text_widget('component-service', '<p>High-emphasis card for an important destination or next step.</p>', 16, '#ffffff'),
    $button('component-service', 'Explore service', '#', 'primary'),
], [
    'width' => $size(31.8, '%'), 'padding' => $size(30), 'gap' => $size(20),
    'background' => $bg('#082b55'), 'border-radius' => $size(18),
], [], ['width' => $size(100, '%')]);
$info_card = $container('component-info', [
    $icon('component-info', 'hands-helping', '#25377b', 30),
    $heading('component-info', 'Personal guidance', 'h3', 27, 23),
    $text_widget('component-info', '<p>Editorial card for support, benefits or a concise explanation.</p>', 16),
    $button('component-info', 'Learn more', '#', 'outline'),
], [
    'width' => $size(31.8, '%'), 'padding' => $size(30), 'gap' => $size(20),
    'background' => $bg('#ffffff'), 'border-style' => $typed('string', 'solid'),
    'border-color' => $typed('color', '#dce5ec'), 'border-width' => $size(1), 'border-radius' => $size(18),
], [], ['width' => $size(100, '%')]);
$stat_card = $container('component-stat', [
    $text_widget('component-stat-label', '<p>OUTCOME</p>', 13, '#25377b', '800'),
    $heading('component-stat-value', '01', 'p', 58, 46, '#001a39'),
    $text_widget('component-stat', '<p>Use one clear fact with a short, verifiable explanation.</p>', 16),
], [
    'width' => $size(31.8, '%'), 'padding' => $size(30), 'gap' => $size(14),
    'background' => $bg('#fff8e4'), 'border-radius' => $size(18),
], [], ['width' => $size(100, '%')]);
$resource_row = $container('component-resource', [
    $icon('component-resource', 'file-alt', '#25377b', 24),
    $container('component-resource-copy', [
        $heading('component-resource', 'Resource or document title', 'h3', 20, 19),
        $text_widget('component-resource', '<p>PDF · Updated 2026</p>', 13, '#52677b', '700'),
    ], ['flex-grow' => $typed('number', 1), 'gap' => $size(2)]),
    $icon('component-resource-arrow', 'arrow-right', '#005fbd', 17),
], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'center'), 'gap' => $size(20),
    'padding' => $pad(22, 26, 22, 26), 'background' => $bg('#ffffff'),
    'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#dce5ec'),
    'border-width' => $size(1), 'border-radius' => $size(12),
]);
$components = $section('components', [$inner('components-inner', [
    $section_header('components', '03 / COMPONENTS', 'Cards, actions and resources', 'Duplicate these named containers, then replace copy, links and icons.', false),
    $container('component-grid', [$service_card, $info_card, $stat_card], [
        'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'), 'gap' => $size(24),
    ], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(16)]),
    $resource_row,
    $container('button-row', [
        $button('primary', 'Primary action', '#', 'primary'),
        $button('secondary', 'Secondary action', '#', 'secondary'),
        $button('outline', 'Outline action', '#', 'outline'),
    ], [
        'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'center'), 'gap' => $size(16), 'flex-wrap' => $typed('string', 'wrap'),
    ]),
], [], [], ['css-id' => $typed('string', 'components')])], '#ffffff');
array_unshift($components['elements'], [
    'id' => substr(md5('ds-anchor-components'), 0, 7), 'elType' => 'widget',
    'widgetType' => 'menu-anchor', 'elements' => [], 'settings' => ['anchor' => 'components'],
]);

/* Form pattern. It intentionally has no actions and is visually disabled on this reference page. */
$form = [
    'id' => substr(md5('ds-form-example'), 0, 7), 'elType' => 'widget', 'widgetType' => 'form', 'elements' => [],
    'settings' => [
        'form_name' => 'Design System Example - No Actions',
        'form_fields' => [
            ['custom_id' => 'first_name', 'field_label' => 'First Name', 'placeholder' => 'First Name', 'width' => '50', 'required' => 'true', '_id' => 'dsfname'],
            ['custom_id' => 'last_name', 'field_label' => 'Last Name', 'placeholder' => 'Last Name', 'width' => '50', 'required' => 'true', '_id' => 'dslname'],
            ['custom_id' => 'email', 'field_type' => 'email', 'field_label' => 'Email', 'placeholder' => 'name@example.com', 'required' => 'true', '_id' => 'dsemail'],
            ['custom_id' => 'program', 'field_type' => 'select', 'field_label' => 'Program of Interest', 'field_options' => "Professional Nursing Program A.S.\nPhysical Therapist Assistant Program A.S.", 'required' => 'true', '_id' => 'dsprogram'],
            ['custom_id' => 'message', 'field_type' => 'textarea', 'field_label' => 'How can we help?', 'placeholder' => 'Tell us what you need', 'rows' => 4, '_id' => 'dsmessage'],
        ],
        'button_text' => 'Send My Request', 'submit_actions' => [],
        'selected_button_icon' => ['value' => 'fas fa-arrow-right', 'library' => 'fa-solid'],
        'button_icon_align' => 'row-reverse',
        'label_typography_typography' => 'custom', 'label_typography_font_family' => 'Nunito', 'label_typography_font_weight' => '800',
        'label_typography_font_size' => ['unit' => 'px', 'size' => 14, 'sizes' => []],
        'field_typography_typography' => 'custom', 'field_typography_font_family' => 'Nunito', 'field_typography_font_size' => ['unit' => 'px', 'size' => 16, 'sizes' => []],
        'button_typography_typography' => 'custom', 'button_typography_font_family' => 'Nunito', 'button_typography_font_weight' => '800',
        'button_text_color' => '#001a39', 'button_background_color' => '#fdc800',
        'button_hover_color' => '#082b55', 'button_background_hover_color' => '#ffd633',
        'field_text_color' => '#001a39', 'field_background_color' => '#ffffff', 'field_border_color' => '#b4c6d6',
        'field_border_width' => ['unit' => 'px', 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => true],
        'field_border_radius' => ['unit' => 'px', 'top' => '10', 'right' => '10', 'bottom' => '10', 'left' => '10', 'isLinked' => true],
        'button_border_radius' => ['unit' => 'px', 'top' => '10', 'right' => '10', 'bottom' => '10', 'left' => '10', 'isLinked' => true],
    ],
];
$form_section = $section('forms', [$inner('forms-inner', [
    $container('form-copy', [
        $section_header('forms', '04 / FORMS', 'A calm conversion pattern', 'Use one clear objective, visible labels, useful help and an explicit success state.'),
        $container('form-notes', [
            $icon('form-notes', 'shield-alt', '#25377b', 28),
            $text_widget('form-notes', '<p><strong>Reference only.</strong> This example has no submission actions. Configure destination, consent and validation after duplicating it.</p>', 15),
        ], [
            'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'flex-start'),
            'gap' => $size(16), 'padding' => $size(20), 'background' => $bg('#f3f9ff'), 'border-radius' => $size(12),
        ]),
    ], ['width' => $size(43, '%'), 'gap' => $size(24)], [], ['width' => $size(100, '%')]),
    $container('form-example', [$form], [
        'width' => $size(57, '%'), 'padding' => $size(32), 'background' => $bg('#ffffff'),
        'border-style' => $typed('string', 'solid'), 'border-color' => $typed('color', '#dce5ec'),
        'border-width' => $size(1), 'border-radius' => $size(18),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(22)]),
], [
    'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'flex-start'), 'gap' => $size(56),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(30)])], '#fff8e4', $assets['path-warm']);

/* Reusable page patterns */
$split_pattern = $container('pattern-split', [
    $container('pattern-split-copy', [
        $divider('pattern-split'),
        $heading('pattern-split', 'Split content pattern', 'h3', 36, 29),
        $text_widget('pattern-split', '<p>Use for program introductions, admissions guidance and service explanations.</p>', 17),
        $button('pattern-split', 'Next step', '#', 'primary'),
    ], ['width' => $size(48, '%'), 'gap' => $size(18), 'justify-content' => $typed('string', 'center')], [], ['width' => $size(100, '%')]),
    $container('pattern-split-image', [$image_widget('pattern-split', 1700, 310)], ['width' => $size(52, '%')], [], ['width' => $size(100, '%')]),
], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'center'), 'gap' => $size(42),
    'padding' => $size(34), 'background' => $bg('#f3f9ff', $assets['path-light']), 'border-radius' => $size(24),
], [], ['flex-direction' => $typed('string', 'column'), 'padding' => $size(22), 'gap' => $size(28)]);
$cta_pattern = $container('pattern-cta', [
    $container('pattern-cta-copy', [
        $heading('pattern-cta', 'Ready for the next step?', 'h3', 38, 30, '#ffffff'),
        $text_widget('pattern-cta', '<p>Use a concise closing statement and one primary decision.</p>', 17, '#ffffff'),
    ], ['width' => $size(70, '%'), 'gap' => $size(8)], [], ['width' => $size(100, '%')]),
    $container('pattern-cta-action', [$button('pattern-cta', 'Request Information', '#', 'primary')], [
        'width' => $size(30, '%'), 'align-items' => $typed('string', 'flex-end'), 'justify-content' => $typed('string', 'center'),
    ], [], ['width' => $size(100, '%'), 'align-items' => $typed('string', 'flex-start')]),
], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'center'), 'gap' => $size(30), 'padding' => $pad(42, 44, 42, 44),
    'background' => $bg('#082b55', $assets['path-navy']), 'border-radius' => $size(22),
], [], ['flex-direction' => $typed('string', 'column'), 'padding' => $size(26)]);
$patterns = $section('patterns', [$inner('patterns-inner', [
    $section_header('patterns', '05 / PAGE PATTERNS', 'Ready-to-copy sections', 'Start a new page with these responsive compositions, then adapt only what the content requires.'),
    $split_pattern, $cta_pattern,
], [], [], ['css-id' => $typed('string', 'patterns')])], '#ffffff');
array_unshift($patterns['elements'], [
    'id' => substr(md5('ds-anchor-patterns'), 0, 7), 'elType' => 'widget',
    'widgetType' => 'menu-anchor', 'elements' => [], 'settings' => ['anchor' => 'patterns'],
]);

/* Usage notes */
$usage_cards = [];
$usage_specs = [
    ['copy', 'Copy a whole container', 'Duplicate named sections instead of rebuilding their spacing and responsive rules.', 'copy'],
    ['edit', 'Replace content in place', 'Keep the component structure and edit headings, text, links, icons and images.', 'edit'],
    ['check', 'Verify the page', 'Review desktop and mobile, then confirm text, links, SEO and protected templates.', 'check-circle'],
];
foreach ($usage_specs as [$key, $title_text, $copy, $glyph]) {
    $usage_cards[] = $container('usage-' . $key, [
        $icon('usage-' . $key, $glyph, '#fdc800', 28),
        $heading('usage-' . $key, $title_text, 'h3', 23, 21, '#ffffff'),
        $text_widget('usage-' . $key, '<p>' . $copy . '</p>', 15, '#ffffff'),
    ], [
        'width' => $size(31.8, '%'), 'padding' => $size(26), 'gap' => $size(16),
        'background' => $bg('#173b64'), 'border-style' => $typed('string', 'solid'),
        'border-color' => $typed('color', '#365576'), 'border-width' => $size(1), 'border-radius' => $size(14),
    ], [], ['width' => $size(100, '%')]);
}
$usage = $section('usage', [$inner('usage-inner', [
    $section_header('usage', '06 / WORKFLOW', 'How to use this library', 'This page is a source for future Elementor work, not a production content page.', true),
    $container('usage-grid', $usage_cards, [
        'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'),
        'align-items' => $typed('string', 'stretch'), 'gap' => $size(24),
    ], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(16)]),
])], '#082b55', $assets['path-navy']);

$new_data = [$hero, $foundations, $surfaces, $components, $form_section, $patterns, $usage];

$page_settings = get_post_meta($post_id, '_elementor_page_settings', true) ?: [];
$page_settings['hide_title'] = 'yes';
$page_css = <<<'CSS'
/* SABER Elementor Design System reference page. */
.elementor-widget-text-editor p:last-child { margin-bottom: 0; }
.elementor-widget-button .elementor-button { min-height: 48px; display: inline-flex; align-items: center; justify-content: center; }
.elementor a:focus-visible { outline: 3px solid #005fbd; outline-offset: 5px; box-shadow: 0 0 0 7px #fff; border-radius: 3px; }
.e-ds-form-example .elementor-button { pointer-events: none; }
.e-ds-form-example input, .e-ds-form-example select, .e-ds-form-example textarea { min-height: 48px; }
.e-ds-form-example textarea { min-height: 120px; }
.e-ds-form-example .elementor-field-label { margin-bottom: 7px; }
.e-ds-component-resource .elementor-widget-icon { flex: 0 0 auto; width: auto; }
@media (max-width: 767px) {
  .e-ds-button-row .elementor-widget-button { width: 100%; }
  .e-ds-button-row .elementor-button { width: 100%; }
}
CSS;
$page_settings['custom_css'] = $page_css;

update_post_meta($post_id, '_elementor_data', wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
update_post_meta($post_id, '_elementor_page_settings', $page_settings);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_template_type', 'wp-page');
update_post_meta($post_id, '_elementor_version', ELEMENTOR_VERSION);
update_post_meta($post_id, '_wp_page_template', 'default');
update_post_meta($post_id, 'rank_math_robots', ['noindex']);
update_post_meta($post_id, 'rank_math_title', 'SABER Design System – Elementor');
update_post_meta($post_id, 'rank_math_description', 'Internal Elementor component library for SABER College.');
delete_post_meta($post_id, '_elementor_element_cache');
do_action('elementor/atomic-widgets/styles/clear', ['local', $post_id]);
Elementor\Plugin::$instance->documents->get($post_id, false);
Elementor\Core\Files\CSS\Post::create($post_id)->update();
clean_post_cache($post_id);

$protected_unchanged = true;
foreach ($protected_before as $id => $hash) {
    $protected_unchanged = $protected_unchanged && hash_equals($hash, hash('sha256', get_post_meta($id, '_elementor_data', true)));
}
$all_widgets = [];
$collect = function ($nodes) use (&$collect, &$all_widgets) {
    foreach ($nodes as $node) {
        if (($node['elType'] ?? '') === 'widget') { $all_widgets[] = $node['widgetType']; }
        $collect($node['elements'] ?? []);
    }
};
$collect($new_data);
$report = [
    'page_id' => $post_id, 'created' => $created, 'status' => get_post_status($post_id),
    'url' => get_permalink($post_id), 'noindex' => get_post_meta($post_id, 'rank_math_robots', true),
    'protected_templates_unchanged' => $protected_unchanged,
    'sections' => count($new_data), 'widgets' => count($all_widgets),
    'widget_types' => array_values(array_unique($all_widgets)), 'assets' => $assets,
];
file_put_contents('/tmp/elementor-design-system-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
WP_CLI::success('SABER Design System – Elementor is published and ready to reuse.');
