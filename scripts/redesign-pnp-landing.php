<?php
/**
 * Redesign /lp/pnp/ (page 1640) with native Elementor containers.
 *
 * The script reuses the page's existing copy, form, links, and media assets.
 * It normalizes invalid heading markup, converts the four inline CTA links to
 * native Elementor buttons, and moves the existing "Send My Request" label to
 * the real Elementor form submit button.
 *
 * Run with: wp eval-file /tmp/redesign-pnp-landing.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 1640;
$page = get_post($post_id);
if (!$page || $page->post_name !== 'pnp' || (int) $page->post_parent !== 1638) {
    WP_CLI::error('Page 1640 is not /lp/pnp/.');
}

$raw = get_post_meta($post_id, '_elementor_data', true);
$data = json_decode($raw, true);
if (!is_array($data)) {
    WP_CLI::error('The Elementor data for page 1640 is not valid JSON.');
}

$image_alts = [
    1652 => 'Nursing students practicing clinical skills',
    1659 => 'Nursing students learning together',
    1660 => 'SABER College nursing student',
    1661 => 'Nursing students at SABER College',
    1662 => 'SABER College',
    1651 => 'SABER College accreditation',
];
$current_page_settings = get_post_meta($post_id, '_elementor_page_settings', true);
$already_applied = is_array($current_page_settings)
    && str_contains((string) ($current_page_settings['custom_css'] ?? ''), '/* PNP landing: SABER Design System 0.2 */')
    && str_contains($raw, 'e-pnp-hero')
    && str_contains($raw, 'first_name')
    && str_contains($raw, 'last_name');
if ($already_applied) {
    foreach ($image_alts as $attachment_id => $alt_text) {
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
    }
    WP_CLI::line(wp_json_encode([
        'page_id' => $post_id,
        'status' => 'already_applied',
        'idempotent' => true,
        'elementor_json_sha256' => hash('sha256', $raw),
        'image_alts_verified' => count($image_alts),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    return;
}

$widgets = [];
$collect = static function (array $nodes) use (&$collect, &$widgets): void {
    foreach ($nodes as $node) {
        if (($node['elType'] ?? '') === 'widget' && isset($node['id'])) {
            $widgets[$node['id']] = $node;
        }
        if (!empty($node['elements']) && is_array($node['elements'])) {
            $collect($node['elements']);
        }
    }
};
$collect($data);

$required = [
    '48a1cbd', '03806ea', '28a9a12', '2fc57b9',
    '9969a67', '52a4ab4', '8537981', '8ee6cf7', '7946335',
    '444007b', '476d26c', 'd58182d',
    '8b72009', '72af871', 'e5a03b9', '9cbf9e5', '858052f', '79822df', 'f3efa42',
    '8f8dcf0', '439daea', 'b7c630b', 'f3ef365', 'ff8245a', '12b4699',
    '0e78424', 'af9f3a0', '16f58fe', '10aeef6',
    '556267d', 'dd0a99f', 'f0f682a', '07ead1f', '7bf770b', 'c393672',
    'e4f204c', 'dfe5bad', '85b34c3', '8d6539a',
    'e5cf091', 'f5b9dbd', 'dd8f8c0', '7a84349', '597811a', '0455e19',
    '917f4cc', '278e2bb', 'c1f40e5', 'b66320f', 'a0ade2c', 'cd0b386',
    '997df28', '581c657', '1802a1c', '4bf2df7', 'a0523e2', '9aeb4fc',
    '809f889', '62145f2', 'dac6d50', '9912d24', '923df6d', '899a2cd',
    '964cf10', 'f0b0470', '86de24b', '5f14145', 'a3ff76f',
];
foreach ($required as $id) {
    if (!isset($widgets[$id])) {
        WP_CLI::error("Required Elementor widget {$id} was not found; no changes were made.");
    }
}

$visible_text = static function ($value): string {
    $value = html_entity_decode(wp_strip_all_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim((string) preg_replace('/\s+/u', ' ', $value));
};
$content_fields = ['title', 'editor', 'text', 'button_text'];
$original_texts = [];
foreach ($widgets as $id => $widget) {
    if ($id === '45fe7f9') {
        continue; // Existing editorial marker: "// Eliminar".
    }
    foreach ($content_fields as $field) {
        if (!empty($widget['settings'][$field])) {
            $text = $visible_text($widget['settings'][$field]);
            if ($text !== '') {
                $original_texts[] = $text;
            }
        }
    }
}

$snapshot = [
    'page_id' => $post_id,
    'created_utc' => gmdate('c'),
    'post' => (array) $page,
    'meta' => get_post_meta($post_id),
    'protected_hashes' => [],
];
foreach ([7, 16, 53] as $protected_id) {
    $snapshot['protected_hashes'][$protected_id] = hash('sha256', get_post_meta($protected_id, '_elementor_data', true));
}
$backup = '/tmp/pnp-landing-1640-before-' . gmdate('Ymd-His') . '.json';
if (false === file_put_contents($backup, wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) {
    WP_CLI::error('Could not create the in-container backup; no changes were made.');
}

$assets = [];
foreach (['path-light', 'path-warm'] as $asset) {
    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => '_saber_design_asset',
        'meta_value' => $asset . '-v02',
    ]);
    if (!$existing) {
        WP_CLI::error("Design asset {$asset}-v02 is unavailable.");
    }
    $assets[$asset] = (int) $existing[0];
}
foreach ([1652, 1659, 1660, 1661, 1662, 1651] as $attachment_id) {
    if (!wp_get_attachment_url($attachment_id)) {
        WP_CLI::error("Required media attachment {$attachment_id} is unavailable.");
    }
}
foreach ($image_alts as $attachment_id => $alt_text) {
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
}

$typed = static fn(string $type, $value): array => ['$$type' => $type, 'value' => $value];
$size = static fn($value, string $unit = 'px'): array => ['$$type' => 'size', 'value' => ['size' => $value, 'unit' => $unit]];
$pad = static function ($top, $right, $bottom, $left) use ($typed, $size): array {
    return $typed('dimensions', [
        'block-start' => $size($top),
        'inline-end' => $size($right),
        'block-end' => $size($bottom),
        'inline-start' => $size($left),
    ]);
};
$background = static function (string $color, int $attachment_id = 0) use ($typed): array {
    $value = ['color' => $typed('color', $color)];
    if ($attachment_id > 0) {
        $value['background-overlay'] = $typed('background-overlay', [$typed('background-image-overlay', [
            'image' => $typed('image', [
                'src' => $typed('image-src', [
                    'id' => $typed('image-attachment-id', $attachment_id),
                    'url' => null,
                ]),
                'size' => $typed('string', 'full'),
            ]),
            'repeat' => $typed('string', 'no-repeat'),
            'size' => $typed('string', 'cover'),
            'position' => $typed('string', 'center center'),
        ])]);
    }
    return $typed('background', $value);
};
$container = static function (
    string $name,
    array $children,
    array $desktop = [],
    array $tablet = [],
    array $mobile = [],
    array $hover = []
) use ($typed, $size): array {
    $id = substr(md5('saber-pnp-landing-' . $name), 0, 7);
    $class = 'e-pnp-' . $name;
    $desktop = array_merge([
        'display' => $typed('string', 'flex'),
        'flex-direction' => $typed('string', 'column'),
        'min-width' => $size(0),
    ], $desktop);
    $variants = [[
        'meta' => ['breakpoint' => 'desktop', 'state' => null],
        'props' => $desktop,
        'custom_css' => null,
    ]];
    foreach (['tablet' => $tablet, 'mobile' => $mobile] as $breakpoint => $props) {
        if ($props !== []) {
            $variants[] = [
                'meta' => ['breakpoint' => $breakpoint, 'state' => null],
                'props' => $props,
                'custom_css' => null,
            ];
        }
    }
    if ($hover !== []) {
        $variants[] = [
            'meta' => ['breakpoint' => 'desktop', 'state' => 'hover'],
            'props' => $hover,
            'custom_css' => null,
        ];
    }
    return [
        'id' => $id,
        'elType' => 'e-flexbox',
        'settings' => ['classes' => $typed('classes', [$class])],
        'elements' => $children,
        'isInner' => false,
        'styles' => [$class => [
            'id' => $class,
            'label' => $class,
            'type' => 'class',
            'variants' => $variants,
        ]],
        'interactions' => [],
        'editor_settings' => [],
        'version' => '0.0',
    ];
};
$section = static function (string $name, array $children, string $color, int $asset_id = 0) use ($container, $typed, $size, $background): array {
    return $container($name, $children, [
        'width' => $size(100, '%'),
        'align-items' => $typed('string', 'center'),
        'background' => $background($color, $asset_id),
    ]);
};
$inner = static function (string $name, array $children, array $extra = [], array $tablet = [], array $mobile = []) use ($container, $typed, $size, $pad): array {
    return $container($name, $children, array_merge([
        'width' => $size(100, '%'),
        'max-width' => $size(1200),
        'gap' => $size(28),
        'padding' => $pad(76, 24, 80, 24),
    ], $extra), $tablet, array_merge([
        'padding' => $pad(50, 18, 54, 18),
    ], $mobile));
};

$heading = static function (array $widget, int $desktop_size, int $mobile_size, string $color, string $tag) use ($visible_text): array {
    unset($widget['settings']['__globals__']);
    $widget['settings']['title'] = $visible_text($widget['settings']['title'] ?? '');
    $widget['settings']['header_size'] = $tag;
    $widget['settings']['title_color'] = $color;
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_weight'] = '800';
    $widget['settings']['typography_font_size'] = ['unit' => 'px', 'size' => $desktop_size, 'sizes' => []];
    $widget['settings']['typography_font_size_mobile'] = ['unit' => 'px', 'size' => $mobile_size, 'sizes' => []];
    $widget['settings']['typography_line_height'] = ['unit' => 'em', 'size' => 1.12, 'sizes' => []];
    $widget['settings']['typography_letter_spacing'] = ['unit' => 'px', 'size' => -0.4, 'sizes' => []];
    $widget['settings']['_margin'] = ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true];
    return $widget;
};
$text = static function (array $widget, string $color = '#29435f', int $font_size = 17, string $weight = '500'): array {
    unset($widget['settings']['__globals__']);
    $widget['settings']['text_color'] = $color;
    $widget['settings']['link_color'] = '#005fbd';
    $widget['settings']['link_hover_color'] = '#003f7d';
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_weight'] = $weight;
    $widget['settings']['typography_font_size'] = ['unit' => 'px', 'size' => $font_size, 'sizes' => []];
    $widget['settings']['typography_font_size_mobile'] = ['unit' => 'px', 'size' => min(17, max(15, $font_size - 1)), 'sizes' => []];
    $widget['settings']['typography_line_height'] = ['unit' => 'em', 'size' => 1.66, 'sizes' => []];
    $widget['settings']['_margin'] = ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true];
    return $widget;
};
$button_from_text = static function (array $widget, string $style = 'primary') use ($visible_text): array {
    $html = (string) ($widget['settings']['editor'] ?? '');
    preg_match('/href=["\']([^"\']+)["\']/i', $html, $url_match);
    $label = $visible_text($html);
    $url = $url_match[1] ?? '#form-field';
    $palette = [
        'primary' => ['#fdc800', '#001a39', '#fdc800'],
        'secondary' => ['#082b55', '#ffffff', '#082b55'],
        'light' => ['#ffffff', '#082b55', '#ffffff'],
    ];
    [$background_color, $text_color, $border_color] = $palette[$style] ?? $palette['primary'];
    return [
        'id' => $widget['id'],
        'elType' => 'widget',
        'widgetType' => 'button',
        'elements' => [],
        'settings' => [
            'text' => $label,
            'link' => ['url' => $url, 'is_external' => '', 'nofollow' => '', 'custom_attributes' => ''],
            'align' => 'left',
            'background_color' => $background_color,
            'button_text_color' => $text_color,
            'hover_color' => $text_color,
            'button_background_hover_color' => $background_color,
            'border_border' => 'solid',
            'border_width' => ['unit' => 'px', 'top' => '2', 'right' => '2', 'bottom' => '2', 'left' => '2', 'isLinked' => true],
            'border_color' => $border_color,
            'border_radius' => ['unit' => 'px', 'top' => '10', 'right' => '10', 'bottom' => '10', 'left' => '10', 'isLinked' => true],
            'text_padding' => ['unit' => 'px', 'top' => '14', 'right' => '22', 'bottom' => '14', 'left' => '22', 'isLinked' => false],
            'typography_typography' => 'custom',
            'typography_font_family' => 'Nunito',
            'typography_font_size' => ['unit' => 'px', 'size' => 16, 'sizes' => []],
            'typography_font_weight' => '800',
        ],
    ];
};
$image = static function (string $name, int $attachment_id, string $alt, int $height = 360, string $fit = 'cover'): array {
    return [
        'id' => substr(md5('saber-pnp-image-' . $name), 0, 7),
        'elType' => 'widget',
        'widgetType' => 'image',
        'elements' => [],
        'settings' => [
            'image' => [
                'id' => $attachment_id,
                'url' => wp_get_attachment_url($attachment_id),
                'alt' => $alt,
                'source' => 'library',
            ],
            'image_size' => 'full',
            'width' => ['unit' => '%', 'size' => 100, 'sizes' => []],
            'height' => ['unit' => 'px', 'size' => $height, 'sizes' => []],
            'height_mobile' => ['unit' => 'px', 'size' => min(320, $height), 'sizes' => []],
            'object-fit' => $fit,
            'object-position' => 'center center',
        ],
    ];
};

// Normalize semantic headings while retaining the exact visible wording.
$widgets['48a1cbd'] = $heading($widgets['48a1cbd'], 52, 38, '#001a39', 'h1');
foreach (['9969a67', '444007b', '8b72009', '8f8dcf0', '556267d', 'e5cf091', '917f4cc', '997df28'] as $id) {
    $widgets[$id] = $heading($widgets[$id], 40, 31, '#001a39', 'h2');
}
foreach (['b7c630b', 'ff8245a', '0e78424', '16f58fe', 'f0f682a', '7bf770b', 'e4f204c', '85b34c3', 'b66320f'] as $id) {
    $widgets[$id] = $heading($widgets[$id], 23, 21, '#082b55', 'h3');
}
foreach (['581c657', 'a0523e2', '62145f2', '923df6d'] as $id) {
    $widgets[$id] = $heading($widgets[$id], 21, 20, '#082b55', 'h3');
}

$lead_ids = ['03806ea', '52a4ab4', '439daea'];
foreach ($lead_ids as $id) {
    $widgets[$id] = $text($widgets[$id], '#29435f', 19, '600');
}
$body_ids = [
    '28a9a12', '8537981', '476d26c', '72af871', 'e5a03b9', '9cbf9e5', '858052f', '79822df', 'f3efa42',
    'f3ef365', '12b4699', 'af9f3a0', '10aeef6', 'dd0a99f', '07ead1f', 'c393672', 'dfe5bad', '8d6539a',
    'f5b9dbd', 'dd8f8c0', '7a84349', '597811a', '278e2bb', 'c1f40e5', 'a0ade2c',
    '1802a1c', '4bf2df7', '9aeb4fc', '809f889', 'dac6d50', '9912d24', '899a2cd',
    '964cf10', 'f0b0470', 'a3ff76f',
];
foreach ($body_ids as $id) {
    $widgets[$id] = $text($widgets[$id]);
}
$widgets['8537981'] = $text($widgets['8537981'], '#52677b', 13, '500');
$widgets['964cf10'] = $text($widgets['964cf10'], '#fdc800', 14, '800');
$widgets['f0b0470'] = $text($widgets['f0b0470'], '#ffffff', 14, '500');
$widgets['a3ff76f'] = $text($widgets['a3ff76f'], '#ffffff', 14, '500');
$widgets['dd0a99f'] = $text($widgets['dd0a99f'], '#fdc800', 20, '800');
$widgets['dd8f8c0'] = $text($widgets['dd8f8c0'], '#082b55', 18, '800');
$widgets['c1f40e5'] = $text($widgets['c1f40e5'], '#082b55', 17, '700');

$cta_hero = $button_from_text($widgets['2fc57b9'], 'primary');
$cta_overview = $button_from_text($widgets['d58182d'], 'secondary');
$cta_career = $button_from_text($widgets['0455e19'], 'primary');
$cta_admissions = $button_from_text($widgets['cd0b386'], 'light');
$brand_cta = $cta_hero;
$brand_cta['id'] = 'pnpbrcta';
$brand_cta['settings']['text'] = $visible_text($widgets['7946335']['settings']['button_text'] ?? '');
$brand_cta['settings']['align'] = 'right';

// Use the existing standalone submit label on the actual Elementor form.
$form = $widgets['7946335'];
unset($form['settings']['__globals__']);
$form['settings']['button_text'] = $visible_text($widgets['8ee6cf7']['settings']['editor'] ?? 'Send My Request');
$form['settings']['button_width'] = '100';
$form['settings']['input_size'] = 'md';
$form['settings']['button_size'] = 'md';
$form['settings']['label_position'] = 'above';
$form['settings']['label_color'] = '#001a39';
$form['settings']['field_text_color'] = '#001a39';
$form['settings']['field_background_color'] = '#ffffff';
$form['settings']['field_border_color'] = '#c8d6e2';
$form['settings']['field_border_width'] = ['unit' => 'px', 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => true];
$form['settings']['field_border_radius'] = ['unit' => 'px', 'top' => '10', 'right' => '10', 'bottom' => '10', 'left' => '10', 'isLinked' => true];
$form['settings']['field_padding'] = ['unit' => 'px', 'top' => '13', 'right' => '14', 'bottom' => '13', 'left' => '14', 'isLinked' => true];
$form['settings']['button_background_color'] = '#fdc800';
$form['settings']['button_text_color'] = '#001a39';
$form['settings']['button_hover_color'] = '#001a39';
$form['settings']['button_background_hover_color'] = '#f0bd00';
$form['settings']['button_border_radius'] = ['unit' => 'px', 'top' => '10', 'right' => '10', 'bottom' => '10', 'left' => '10', 'isLinked' => true];
$form['settings']['button_typography_typography'] = 'custom';
$form['settings']['button_typography_font_family'] = 'Nunito';
$form['settings']['button_typography_font_weight'] = '800';
$form['settings']['button_typography_font_size'] = ['unit' => 'px', 'size' => 16, 'sizes' => []];
if (isset($form['settings']['form_fields'][0])) {
    $form['settings']['form_fields'][0]['custom_id'] = 'first_name';
}
if (isset($form['settings']['form_fields'][1])) {
    $form['settings']['form_fields'][1]['custom_id'] = 'last_name';
}

$form_anchor = [
    'id' => 'pnpform',
    'elType' => 'widget',
    'widgetType' => 'menu-anchor',
    'elements' => [],
    'settings' => ['anchor' => 'form-field'],
];

$logo = $widgets['86de24b'];
$logo['settings']['width'] = ['unit' => 'px', 'size' => 92, 'sizes' => []];
$logo['settings']['align'] = 'left';
$logo['settings']['image']['alt'] = 'SABER College';

$brand_inner = $container('brand-inner', [$logo, $brand_cta], [
    'width' => $size(100, '%'),
    'max-width' => $size(1200),
    'flex-direction' => $typed('string', 'row'),
    'justify-content' => $typed('string', 'space-between'),
    'align-items' => $typed('string', 'center'),
    'padding' => $pad(18, 24, 18, 24),
], [], ['padding' => $pad(14, 18, 14, 18)]);
$brand = $section('brand', [$brand_inner], '#082b55');

$hero_photo = $image('hero-training', 1652, 'Nursing students practicing clinical skills', 286, 'cover');
$hero_copy = $container('hero-copy', [
    $widgets['48a1cbd'], $widgets['03806ea'], $widgets['28a9a12'], $cta_hero, $hero_photo,
], [
    'width' => $size(56, '%'),
    'gap' => $size(20),
], ['width' => $size(100, '%')], ['width' => $size(100, '%'), 'gap' => $size(17)]);
$form_card = $container('form-card', [
    $form_anchor,
    $widgets['9969a67'],
    $widgets['52a4ab4'],
    $form,
    $widgets['8537981'],
], [
    'width' => $size(44, '%'),
    'gap' => $size(15),
    'padding' => $size(34),
    'background' => $background('#ffffff'),
    'border-radius' => $size(24),
    'align-self' => $typed('string', 'flex-start'),
], ['width' => $size(100, '%'), 'padding' => $size(30)], ['width' => $size(100, '%'), 'padding' => $size(24)]);
$hero_inner = $inner('hero-inner', [$hero_copy, $form_card], [
    'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'flex-start'),
    'gap' => $size(42),
    'padding' => $pad(62, 24, 72, 24),
], [
    'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'stretch'),
    'gap' => $size(34),
], [
    'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'stretch'),
    'gap' => $size(28),
    'padding' => $pad(42, 18, 50, 18),
]);
$hero = $section('hero', [$hero_inner], '#f3f9ff', $assets['path-light']);

$overview_copy = $container('overview-copy', [$widgets['444007b'], $widgets['476d26c'], $cta_overview], [
    'width' => $size(56, '%'),
    'gap' => $size(18),
    'justify-content' => $typed('string', 'center'),
], [], ['width' => $size(100, '%')]);
$overview_visual = $container('overview-visual', [$image('overview-students', 1659, 'Nursing students learning together', 430, 'cover')], [
    'width' => $size(44, '%'),
    'overflow' => $typed('string', 'hidden'),
    'border-radius' => $size(24),
], [], ['width' => $size(100, '%')]);
$overview = $section('overview', [$inner('overview-inner', [$overview_copy, $overview_visual], [
    'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'center'),
    'gap' => $size(54),
], [
    'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'stretch'),
], [
    'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'stretch'),
    'gap' => $size(28),
])], '#ffffff');

$benefit_ids = ['72af871', 'e5a03b9', '9cbf9e5', '858052f', '79822df', 'f3efa42'];
$benefit_cards = [];
foreach ($benefit_ids as $index => $id) {
    $benefit_cards[] = $container('benefit-' . ($index + 1), [$widgets[$id]], [
        'width' => $size(31.9, '%'),
        'padding' => $size(26),
        'background' => $background($index === 0 ? '#082b55' : '#ffffff'),
        'border-radius' => $size(18),
        'border-style' => $typed('string', 'solid'),
        'border-color' => $typed('color', $index === 0 ? '#082b55' : '#dce5ec'),
        'border-width' => $size(1),
    ], ['width' => $size(48, '%')], ['width' => $size(100, '%'), 'padding' => $size(22)]);
    if ($index === 0) {
        $benefit_cards[$index]['elements'][0]['settings']['text_color'] = '#ffffff';
    }
}
$benefit_grid = $container('benefits-grid', $benefit_cards, [
    'width' => $size(100, '%'),
    'flex-direction' => $typed('string', 'row'),
    'flex-wrap' => $typed('string', 'wrap'),
    'align-items' => $typed('string', 'stretch'),
    'gap' => $size(22),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(16)]);
$benefits_intro = $container('benefits-intro', [$widgets['8b72009']], [
    'width' => $size(100, '%'),
    'max-width' => $size(820),
    'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'),
]);
$benefits = $section('benefits', [$inner('benefits-inner', [$benefits_intro, $benefit_grid], [
    'align-items' => $typed('string', 'center'),
    'gap' => $size(36),
])], '#fff8e4', $assets['path-warm']);

$learn_pairs = [
    ['b7c630b', 'f3ef365'],
    ['ff8245a', '12b4699'],
    ['0e78424', 'af9f3a0'],
    ['16f58fe', '10aeef6'],
];
$learn_cards = [];
foreach ($learn_pairs as $index => [$title_id, $copy_id]) {
    $learn_cards[] = $container('learn-' . ($index + 1), [$widgets[$title_id], $widgets[$copy_id]], [
        'width' => $size(48.9, '%'),
        'gap' => $size(12),
        'padding' => $size(30),
        'background' => $background(in_array($index, [1, 2], true) ? '#f3f9ff' : '#ffffff'),
        'border-radius' => $size(18),
        'border-style' => $typed('string', 'solid'),
        'border-color' => $typed('color', '#dce5ec'),
        'border-width' => $size(1),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(23)]);
}
$learn_grid = $container('learn-grid', $learn_cards, [
    'width' => $size(100, '%'),
    'flex-direction' => $typed('string', 'row'),
    'flex-wrap' => $typed('string', 'wrap'),
    'align-items' => $typed('string', 'stretch'),
    'gap' => $size(24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(16)]);
$learn_intro = $container('learn-intro', [$widgets['8f8dcf0'], $widgets['439daea']], [
    'width' => $size(100, '%'),
    'max-width' => $size(820),
    'gap' => $size(12),
    'align-items' => $typed('string', 'center'),
    'text-align' => $typed('string', 'center'),
]);
$learn = $section('learn', [$inner('learn-inner', [$learn_intro, $learn_grid], [
    'align-items' => $typed('string', 'center'),
    'gap' => $size(38),
])], '#ffffff');

$widgets['556267d']['settings']['title_color'] = '#ffffff';
$curriculum_left = $container('curriculum-left', [
    $widgets['556267d'], $widgets['dd0a99f'], $image('curriculum-student', 1660, 'SABER College nursing student', 410, 'cover'),
], [
    'width' => $size(37, '%'),
    'gap' => $size(18),
], ['width' => $size(100, '%')], ['width' => $size(100, '%')]);
$curriculum_pairs = [
    ['f0f682a', '07ead1f'],
    ['7bf770b', 'c393672'],
    ['e4f204c', 'dfe5bad'],
    ['85b34c3', '8d6539a'],
];
$curriculum_cards = [];
foreach ($curriculum_pairs as $index => [$title_id, $copy_id]) {
    $curriculum_cards[] = $container('curriculum-' . ($index + 1), [$widgets[$title_id], $widgets[$copy_id]], [
        'width' => $size(100, '%'),
        'gap' => $size(8),
        'padding' => $size(24),
        'background' => $background('#ffffff'),
        'border-radius' => $size(16),
    ], [], ['padding' => $size(21)]);
}
$curriculum_right = $container('curriculum-right', $curriculum_cards, [
    'width' => $size(63, '%'),
    'gap' => $size(16),
], ['width' => $size(100, '%')], ['width' => $size(100, '%')]);
$curriculum = $section('curriculum', [$inner('curriculum-inner', [$curriculum_left, $curriculum_right], [
    'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'flex-start'),
    'gap' => $size(44),
], [
    'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'stretch'),
], [
    'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'stretch'),
    'gap' => $size(26),
])], '#082b55');

$career_copy = $container('career-copy', [
    $widgets['e5cf091'], $widgets['f5b9dbd'], $widgets['dd8f8c0'], $widgets['7a84349'], $widgets['597811a'], $cta_career,
], [
    'width' => $size(58, '%'),
    'gap' => $size(14),
], [], ['width' => $size(100, '%')]);
$career_visual = $container('career-visual', [$image('career-nurses', 1661, 'Nursing students at SABER College', 470, 'contain')], [
    'width' => $size(42, '%'),
    'justify-content' => $typed('string', 'center'),
    'align-items' => $typed('string', 'center'),
    'background' => $background('#fff8e4'),
    'border-radius' => $size(24),
    'overflow' => $typed('string', 'hidden'),
], [], ['width' => $size(100, '%')]);
$career = $section('career', [$inner('career-inner', [$career_copy, $career_visual], [
    'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'center'),
    'gap' => $size(48),
], [
    'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'stretch'),
], [
    'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'stretch'),
    'gap' => $size(28),
])], '#f3f7fa');

$admissions_main = $container('admissions-main', [
    $widgets['917f4cc'], $widgets['278e2bb'], $widgets['c1f40e5'],
], [
    'width' => $size(66, '%'),
    'gap' => $size(16),
    'padding' => $size(38),
    'background' => $background('#ffffff'),
    'border-radius' => $size(22),
], [], ['width' => $size(100, '%'), 'padding' => $size(25)]);
$admissions_cta = $container('admissions-cta', [
    $widgets['b66320f'], $widgets['a0ade2c'], $cta_admissions,
], [
    'width' => $size(34, '%'),
    'gap' => $size(16),
    'padding' => $size(34),
    'justify-content' => $typed('string', 'center'),
    'background' => $background('#082b55'),
    'border-radius' => $size(22),
], [], ['width' => $size(100, '%'), 'padding' => $size(25)]);
$admissions_cta['elements'][0]['settings']['title_color'] = '#ffffff';
$admissions_cta['elements'][1]['settings']['text_color'] = '#ffffff';
$admissions = $section('admissions', [$inner('admissions-inner', [$admissions_main, $admissions_cta], [
    'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'stretch'),
    'gap' => $size(24),
], [], [
    'flex-direction' => $typed('string', 'column'),
    'gap' => $size(20),
])], '#fff8e4', $assets['path-warm']);

$faq_pairs = [
    ['581c657', ['1802a1c', '4bf2df7']],
    ['a0523e2', ['9aeb4fc', '809f889']],
    ['62145f2', ['dac6d50', '9912d24']],
    ['923df6d', ['899a2cd']],
];
$faq_cards = [];
foreach ($faq_pairs as $index => [$question_id, $answer_ids]) {
    $children = [$widgets[$question_id]];
    foreach ($answer_ids as $answer_id) {
        $children[] = $widgets[$answer_id];
    }
    $faq_cards[] = $container('faq-' . ($index + 1), $children, [
        'width' => $size(48.9, '%'),
        'gap' => $size(9),
        'padding' => $size(27),
        'background' => $background($index === 0 ? '#f3f9ff' : '#ffffff'),
        'border-style' => $typed('string', 'solid'),
        'border-color' => $typed('color', '#dce5ec'),
        'border-width' => $size(1),
        'border-radius' => $size(16),
    ], [], ['width' => $size(100, '%'), 'padding' => $size(22)]);
}
$faq_grid = $container('faq-grid', $faq_cards, [
    'width' => $size(100, '%'),
    'flex-direction' => $typed('string', 'row'),
    'flex-wrap' => $typed('string', 'wrap'),
    'align-items' => $typed('string', 'stretch'),
    'gap' => $size(24),
], [], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(16)]);
$faq = $section('faq', [$inner('faq-inner', [$widgets['997df28'], $faq_grid], [
    'gap' => $size(34),
])], '#ffffff');

$badge = $widgets['5f14145'];
$badge['settings']['width'] = ['unit' => 'px', 'size' => 312, 'sizes' => []];
$badge['settings']['align'] = 'left';
$badge['settings']['image']['alt'] = 'SABER College accreditation';
$accreditation_brand = $container('accreditation-brand', [$widgets['964cf10'], $badge], [
    'width' => $size(34, '%'),
    'gap' => $size(12),
], [], ['width' => $size(100, '%')]);
$accreditation_copy = $container('accreditation-copy', [$widgets['f0b0470'], $widgets['a3ff76f']], [
    'width' => $size(66, '%'),
    'gap' => $size(12),
], [], ['width' => $size(100, '%')]);
$accreditation = $section('accreditation', [$inner('accreditation-inner', [$accreditation_brand, $accreditation_copy], [
    'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'center'),
    'gap' => $size(48),
    'padding' => $pad(54, 24, 58, 24),
], [], [
    'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'flex-start'),
    'gap' => $size(24),
    'padding' => $pad(42, 18, 46, 18),
])], '#001a39');

$new_data = [
    $brand,
    $hero,
    $overview,
    $benefits,
    $learn,
    $curriculum,
    $career,
    $admissions,
    $faq,
    $accreditation,
];

// Confirm that every original visible string remains present, except the explicit editorial marker.
$new_widgets = [];
$collect_new = static function (array $nodes) use (&$collect_new, &$new_widgets): void {
    foreach ($nodes as $node) {
        if (($node['elType'] ?? '') === 'widget') {
            $new_widgets[] = $node;
        }
        if (!empty($node['elements']) && is_array($node['elements'])) {
            $collect_new($node['elements']);
        }
    }
};
$collect_new($new_data);
$new_texts = [];
foreach ($new_widgets as $widget) {
    foreach (['title', 'editor', 'text', 'button_text'] as $field) {
        if (!empty($widget['settings'][$field])) {
            $text_value = $visible_text($widget['settings'][$field]);
            if ($text_value !== '') {
                $new_texts[] = $text_value;
            }
        }
    }
}
$missing_texts = [];
foreach (array_unique($original_texts) as $original_text) {
    if (!in_array($original_text, $new_texts, true)) {
        $missing_texts[] = $original_text;
    }
}
if ($missing_texts !== []) {
    WP_CLI::error('Content preservation failed: ' . implode(' | ', $missing_texts));
}

$page_settings = get_post_meta($post_id, '_elementor_page_settings', true);
if (!is_array($page_settings)) {
    $page_settings = [];
}
$marker = '/* PNP landing: SABER Design System 0.2 */';
$existing_css = (string) ($page_settings['custom_css'] ?? '');
if (str_contains($existing_css, $marker)) {
    $existing_css = strstr($existing_css, $marker, true);
}
$page_css = <<<'CSS'
/* PNP landing: SABER Design System 0.2 */
.elementor-1640,
.elementor-1640 * { box-sizing: border-box; }
.elementor-1640 { overflow: clip; color: #29435f; }
.elementor-1640 .e-con { min-width: 0; }
.elementor-1640 h1,
.elementor-1640 h2,
.elementor-1640 h3,
.elementor-1640 p { margin-top: 0; }
.elementor-1640 .elementor-widget-text-editor p:last-child,
.elementor-1640 .elementor-widget-text-editor ul:last-child { margin-bottom: 0; }
.elementor-1640 .elementor-widget-text-editor ul { padding-left: 1.25rem; }
.elementor-1640 .elementor-widget-text-editor li { margin-bottom: .5rem; }
.elementor-1640 .elementor-widget-text-editor li:last-child { margin-bottom: 0; }
.elementor-1640 strong { color: inherit; font-weight: 800; }
.elementor-1640 a:focus-visible,
.elementor-1640 button:focus-visible,
.elementor-1640 input:focus-visible,
.elementor-1640 select:focus-visible,
.elementor-1640 textarea:focus-visible {
  outline: 3px solid #005fbd;
  outline-offset: 3px;
  box-shadow: 0 0 0 6px #fff;
}
.elementor-1640 .elementor-button { transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease; }
.elementor-1640 .elementor-button:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,26,57,.14); }
.elementor-1640 .e-pnp-form-card { box-shadow: 0 20px 56px rgba(0,26,57,.13); }
.elementor-1640 .e-pnp-form-card .elementor-widget-form,
.elementor-1640 .e-pnp-form-card .elementor-form { display: block !important; visibility: visible !important; opacity: 1 !important; height: auto !important; }
.elementor-1640 .e-pnp-form-card .elementor-form-fields-wrapper { display: flex; flex-wrap: wrap; gap: 14px 0; }
.elementor-1640 .e-pnp-form-card .elementor-field-group { margin-bottom: 0; }
.elementor-1640 .e-pnp-form-card .elementor-field-label { margin-bottom: 6px; color: #001a39; font: 700 15px/1.35 Nunito, Arial, sans-serif; }
.elementor-1640 .e-pnp-form-card .elementor-field-textual { min-height: 50px; border: 1px solid #c8d6e2; border-radius: 10px; background: #fff; color: #001a39; }
.elementor-1640 .e-pnp-form-card .elementor-field-textual::placeholder { color: #52677b; opacity: 1; }
.elementor-1640 .e-pnp-form-card .e-form__buttons { margin-top: 4px; }
.elementor-1640 .e-pnp-form-card .elementor-button[type="submit"] { min-height: 50px; border: 2px solid #fdc800; border-radius: 10px; background: #fdc800; color: #001a39; font: 800 16px/1.2 Nunito, Arial, sans-serif; }
.elementor-1640 .e-pnp-form-card .elementor-message { border-radius: 10px; padding: 12px 14px; }
.elementor-1640 .e-pnp-hero-copy > .elementor-widget-text-editor:nth-child(3) ul { display: grid; gap: 10px; margin: 0; padding: 0; list-style: none; }
.elementor-1640 .e-pnp-hero-copy > .elementor-widget-text-editor:nth-child(3) li { position: relative; margin: 0; padding-left: 32px; }
.elementor-1640 .e-pnp-hero-copy > .elementor-widget-text-editor:nth-child(3) li > span:first-child { position: absolute; left: 0; top: 1px; display: grid; place-items: center; width: 23px; height: 23px; border-radius: 50%; background: #fdc800; color: #001a39; font-weight: 900; }
.elementor-1640 .e-pnp-hero-photo img,
.elementor-1640 .e-pnp-overview-visual img { display: block; width: 100%; border-radius: 18px; object-fit: cover; }
.elementor-1640 .e-pnp-hero-photo img { box-shadow: 0 16px 38px rgba(8,43,85,.12); }
.elementor-1640 .e-pnp-overview-visual img { min-height: 430px; }
.elementor-1640 .e-pnp-benefits-grid > .e-con { height: auto; box-shadow: 0 10px 28px rgba(8,43,85,.055); }
.elementor-1640 .e-pnp-benefits-grid > .e-con .elementor-widget-text-editor { position: relative; padding-top: 45px; }
.elementor-1640 .e-pnp-benefits-grid > .e-con .elementor-widget-text-editor::before {
  content: "✓"; position: absolute; top: 0; left: 0; display: grid; place-items: center;
  width: 34px; height: 34px; border-radius: 50%; background: #fdc800; color: #001a39;
  font: 900 18px/1 Nunito, Arial, sans-serif;
}
.elementor-1640 .e-pnp-benefits-grid > .e-con .elementor-widget-text-editor p { font-weight: 700; }
.elementor-1640 .e-pnp-learn-grid > .e-con,
.elementor-1640 .e-pnp-faq-grid > .e-con { height: auto; }
.elementor-1640 .e-pnp-learn-grid .elementor-widget-text-editor ul,
.elementor-1640 .e-pnp-curriculum-right .elementor-widget-text-editor ul,
.elementor-1640 .e-pnp-career-copy .elementor-widget-text-editor ul,
.elementor-1640 .e-pnp-admissions-main .elementor-widget-text-editor ul { margin: 0; }
.elementor-1640 .e-pnp-curriculum-left img { display: block; width: 100%; border-radius: 20px; background: #fff8e4; object-fit: cover; }
.elementor-1640 .e-pnp-curriculum-right > .e-con { box-shadow: 0 12px 30px rgba(0,0,0,.08); }
.elementor-1640 .e-pnp-career-visual img { display: block; max-height: 470px; object-fit: contain; object-position: center bottom; }
.elementor-1640 .e-pnp-admissions-main .elementor-widget-text-editor ul { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 22px; padding: 0; list-style: none; }
.elementor-1640 .e-pnp-admissions-main .elementor-widget-text-editor li { position: relative; margin: 0; padding-left: 26px; }
.elementor-1640 .e-pnp-admissions-main .elementor-widget-text-editor li::before { content: "✓"; position: absolute; left: 0; top: 0; color: #005fbd; font-weight: 900; }
.elementor-1640 .e-pnp-faq-grid > .e-con { box-shadow: 0 10px 28px rgba(8,43,85,.045); }
.elementor-1640 .e-pnp-accreditation img { max-width: 100%; height: auto; }
@media (max-width: 1024px) {
  .elementor-1640 .e-pnp-benefits-grid > .e-con { width: calc(50% - 12px); }
  .elementor-1640 .e-pnp-curriculum-left img { max-height: 430px; object-fit: contain; }
}
@media (max-width: 767px) {
  .elementor-1640 { overflow-x: clip; }
  .elementor-1640 .elementor-element,
  .elementor-1640 .elementor-widget-container { max-width: 100%; }
  .elementor-1640 h1,
  .elementor-1640 h2,
  .elementor-1640 h3,
  .elementor-1640 p,
  .elementor-1640 li { overflow-wrap: anywhere; }
  .elementor-1640 .elementor-button { width: 100%; }
  .elementor-1640 .e-pnp-form-card .elementor-field-group { width: 100%; }
  .elementor-1640 .e-pnp-overview-visual img { min-height: 280px; }
  .elementor-1640 .e-pnp-benefits-grid > .e-con { width: 100%; }
  .elementor-1640 .e-pnp-admissions-main .elementor-widget-text-editor ul { grid-template-columns: 1fr; }
}
CSS;
$page_settings['custom_css'] = rtrim($existing_css) . "\n" . $page_css;
$page_settings['hide_title'] = 'yes';

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
if (!$protected_unchanged) {
    WP_CLI::error('A protected template changed unexpectedly. Restore from the backup.');
}

$report = [
    'page_id' => $post_id,
    'url' => get_permalink($post_id),
    'backup' => $backup,
    'top_level_sections' => count($new_data),
    'widgets' => count($new_widgets),
    'preserved_visible_strings' => count(array_unique($original_texts)),
    'removed_editorial_marker' => '// Eliminar',
    'converted_ctas' => 4,
    'form_submit_label' => $form['settings']['button_text'],
    'form_field_ids_unique' => true,
    'media_ids' => [1652, 1659, 1660, 1661, 1662, 1651],
    'design_assets' => $assets,
    'protected_templates_unchanged' => true,
    'elementor_json_sha256' => hash('sha256', get_post_meta($post_id, '_elementor_data', true)),
];
WP_CLI::line('BACKUP=' . $backup);
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
