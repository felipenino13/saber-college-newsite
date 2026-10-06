<?php
/**
 * Redesign /admissions-and-tuition/ with native Elementor containers.
 * Existing copy, button labels, links, and content order are preserved.
 *
 * Run with: wp eval-file /tmp/redesign-admissions-tuition.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 1225;
$data = json_decode(get_post_meta($post_id, '_elementor_data', true), true);
if (!is_array($data)) {
    WP_CLI::error('The Elementor data for page 1225 is not valid JSON.');
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
    '2a2188a', '14848f2', '6247bbb', '83aeafb', '045ff1c', '11c5d50', 'fd6d8ed',
    'cc34ebd', '1a3cd74', '259d71d', 'd6857e7', 'f162be1', '165c81b', 'f86e4de',
    '1e05879', 'a8c594d', 'bc6e52f', 'bfb8746', '56b4755',
    '575202f', 'aae96b1', 'ca6c4c6', 'a3e97c2', '9550f88', 'd087e0e',
    '211dfd0', '463f8b8', '92c78d7',
    '7219bd9', 'ed9505c', '8e6879e', '0599f1d', 'bf9ebd7',
    'd485959', 'f124f72', '51e7171', '964fd8a', 'e84d16a',
    'ff3cbfc', '8346bdb', 'a6093de', '1a70ff1', '69f20b3',
    '2cff04e', 'f3da86e', '352a995', 'b1a655b', '29a7814', '218b9a7',
];
foreach ($required as $id) {
    if (!isset($widgets[$id])) {
        WP_CLI::error("Required Elementor widget {$id} was not found; no changes were made.");
    }
}

$typed = static fn(string $type, $value): array => ['$$type' => $type, 'value' => $value];
$size = static fn($value, string $unit = 'px'): array => ['$$type' => 'size', 'value' => ['size' => $value, 'unit' => $unit]];
$dimensions = static function (array $values) use ($typed, $size): array {
    $sides = [];
    foreach ($values as $side => $value) {
        $sides[$side] = $size($value);
    }
    return $typed('dimensions', $sides);
};
$background = static fn(string $color): array => [
    '$$type' => 'background',
    'value' => ['color' => ['$$type' => 'color', 'value' => $color]],
];
$style = static function (string $class, array $desktop, array $mobile = []): array {
    $variants = [['meta' => ['breakpoint' => 'desktop', 'state' => null], 'props' => $desktop, 'custom_css' => null]];
    if ($mobile !== []) {
        $variants[] = ['meta' => ['breakpoint' => 'mobile', 'state' => null], 'props' => $mobile, 'custom_css' => null];
    }
    return [$class => ['id' => $class, 'label' => 'local', 'type' => 'class', 'variants' => $variants]];
};
$container = static function (string $id, string $class, array $children, array $desktop, array $mobile = []) use ($style): array {
    return [
        'id' => $id,
        'elType' => 'e-flexbox',
        'settings' => ['classes' => ['$$type' => 'classes', 'value' => [$class]]],
        'elements' => $children,
        'isInner' => false,
        'styles' => $style($class, $desktop, $mobile),
        'interactions' => [],
        'editor_settings' => [],
        'version' => '0.0',
    ];
};

$heading = static function (array $widget, int $size, string $color, string $tag): array {
    $widget['settings']['header_size'] = $tag;
    $widget['settings']['title_color'] = $color;
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_weight'] = '800';
    $widget['settings']['typography_font_size'] = ['unit' => 'px', 'size' => $size, 'sizes' => []];
    $widget['settings']['typography_font_size_mobile'] = ['unit' => 'px', 'size' => $size >= 50 ? 42 : ($size >= 38 ? 32 : 23), 'sizes' => []];
    $widget['settings']['typography_line_height'] = ['unit' => 'em', 'size' => 1.14, 'sizes' => []];
    return $widget;
};
$text = static function (array $widget, string $color, int $font_size = 17, string $weight = '500'): array {
    unset($widget['settings']['__globals__']);
    $widget['settings']['text_color'] = $color;
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_size'] = ['unit' => 'px', 'size' => $font_size, 'sizes' => []];
    $widget['settings']['typography_font_size_mobile'] = ['unit' => 'px', 'size' => max(16, $font_size - 1), 'sizes' => []];
    $widget['settings']['typography_font_weight'] = $weight;
    $widget['settings']['typography_line_height'] = ['unit' => 'em', 'size' => 1.65, 'sizes' => []];
    return $widget;
};
$button = static function (array $widget, string $background_color, string $text_color, string $border_color = ''): array {
    unset($widget['settings']['__globals__']);
    $widget['settings']['background_color'] = $background_color;
    $widget['settings']['button_text_color'] = $text_color;
    $widget['settings']['hover_color'] = $text_color;
    $widget['settings']['button_background_hover_color'] = $background_color;
    $widget['settings']['border_border'] = 'solid';
    $widget['settings']['border_width'] = ['unit' => 'px', 'top' => '2', 'right' => '2', 'bottom' => '2', 'left' => '2', 'isLinked' => true];
    $widget['settings']['border_color'] = $border_color !== '' ? $border_color : $background_color;
    $widget['settings']['border_radius'] = ['unit' => 'px', 'top' => '10', 'right' => '10', 'bottom' => '10', 'left' => '10', 'isLinked' => true];
    $widget['settings']['text_padding'] = ['unit' => 'px', 'top' => '14', 'right' => '22', 'bottom' => '14', 'left' => '22', 'isLinked' => false];
    $widget['settings']['typography_typography'] = 'custom';
    $widget['settings']['typography_font_family'] = 'Nunito';
    $widget['settings']['typography_font_size'] = ['unit' => 'px', 'size' => 16, 'sizes' => []];
    $widget['settings']['typography_font_weight'] = '800';
    return $widget;
};

$widgets['14848f2'] = $heading($widgets['14848f2'], 58, '#ffffff', 'h1');
foreach (['cc34ebd', '575202f', '7219bd9', 'd485959', 'ff3cbfc', '2cff04e'] as $id) {
    $widgets[$id] = $heading($widgets[$id], 42, '#082b55', 'h2');
}
foreach (['d6857e7', '165c81b', '1e05879', 'bc6e52f', 'a3e97c2', '211dfd0'] as $id) {
    $widgets[$id] = $heading($widgets[$id], 25, '#082b55', 'h3');
}

$eyebrows = ['6247bbb', '1a3cd74', 'aae96b1', 'ed9505c', 'f124f72', '8346bdb', 'f3da86e'];
foreach ($eyebrows as $id) {
    $widgets[$id] = $text($widgets[$id], '#0b4f91', 19, '800');
}
$body_ids = ['83aeafb', '045ff1c', '259d71d', 'f162be1', 'f86e4de', 'a8c594d', 'bfb8746', 'ca6c4c6', '9550f88', '463f8b8', '8e6879e', '0599f1d', '51e7171', '964fd8a', 'a6093de', '1a70ff1', '352a995', 'b1a655b'];
foreach ($body_ids as $id) {
    $widgets[$id] = $text($widgets[$id], '#29435f');
}
foreach (['11c5d50', '56b4755', 'd087e0e', 'bf9ebd7', 'e84d16a'] as $id) {
    $widgets[$id] = $button($widgets[$id], '#f7c900', '#082b55');
}
foreach (['fd6d8ed', '92c78d7', '69f20b3', '218b9a7'] as $id) {
    $widgets[$id] = $button($widgets[$id], '#082b55', '#ffffff');
}
$widgets['29a7814'] = $button($widgets['29a7814'], '#ffffff', '#082b55', '#ffffff');
$widgets['6247bbb']['settings']['text_color'] = '#f7c900';
$widgets['83aeafb']['settings']['text_color'] = '#ffffff';
$widgets['045ff1c']['settings']['text_color'] = '#ffffff';
$widgets['575202f']['settings']['title_color'] = '#ffffff';
$widgets['aae96b1']['settings']['text_color'] = '#f7c900';
$widgets['ca6c4c6']['settings']['text_color'] = '#ffffff';

$breadcrumbs_inner = $container('atbrdin', 'e-at-breadcrumbs-inner', [$widgets['2a2188a']], [
    'width' => $size(100, '%'), 'max-width' => $size(1200), 'justify-content' => $typed('string', 'center'),
    'align-items' => $typed('string', 'center'),
    'padding' => $dimensions(['block-start' => 18, 'inline-end' => 24, 'block-end' => 18, 'inline-start' => 24]),
], ['padding' => $dimensions(['block-start' => 72, 'inline-end' => 16, 'block-end' => 18, 'inline-start' => 16])]);
$breadcrumbs = $container('atbread', 'e-at-breadcrumbs', [$breadcrumbs_inner], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'center'), 'background' => $background('#ffffff'),
]);

$hero_copy = $container('athcopy', 'e-at-hero-copy', [$widgets['14848f2'], $widgets['6247bbb'], $widgets['83aeafb'], $widgets['045ff1c']], [
    'width' => $size(62, '%'), 'flex-direction' => $typed('string', 'column'), 'gap' => $size(14),
], ['width' => $size(100, '%')]);
$hero_actions = $container('athacts', 'e-at-hero-actions', [$widgets['11c5d50'], $widgets['fd6d8ed']], [
    'width' => $size(38, '%'), 'flex-direction' => $typed('string', 'column'), 'gap' => $size(16),
    'padding' => $size(34), 'background' => $background('#ffffff'), 'border-radius' => $size(22),
], ['width' => $size(100, '%'), 'padding' => $size(24)]);
$hero_inner = $container('atherin', 'e-at-hero-inner', [$hero_copy, $hero_actions], [
    'width' => $size(100, '%'), 'max-width' => $size(1200), 'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'center'), 'gap' => $size(58),
    'padding' => $dimensions(['block-start' => 78, 'inline-end' => 24, 'block-end' => 82, 'inline-start' => 24]),
], ['flex-direction' => $typed('string', 'column'), 'align-items' => $typed('string', 'flex-start'), 'gap' => $size(30),
    'padding' => $dimensions(['block-start' => 48, 'inline-end' => 20, 'block-end' => 54, 'inline-start' => 20])]);
$hero = $container('athero1', 'e-at-hero', [$hero_inner], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'center'), 'background' => $background('#082b55'),
]);

$step_pairs = [
    ['d6857e7', 'f162be1'], ['165c81b', 'f86e4de'],
    ['1e05879', 'a8c594d'], ['bc6e52f', 'bfb8746'],
];
$step_cards = [];
foreach ($step_pairs as $index => [$title_id, $text_id]) {
    $step_cards[] = $container('atstep' . ($index + 1), 'e-at-step-' . ($index + 1), [$widgets[$title_id], $widgets[$text_id]], [
        'width' => $size(48.8, '%'), 'flex-direction' => $typed('string', 'column'), 'gap' => $size(10),
        'padding' => $size(30), 'background' => $background(in_array($index, [1, 2], true) ? '#fff8d8' : '#ffffff'),
        'border-radius' => $size(18),
    ], ['width' => $size(100, '%'), 'padding' => $size(24)]);
}
$steps_grid = $container('atstpgr', 'e-at-steps-grid', $step_cards, [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'), 'flex-wrap' => $typed('string', 'wrap'),
    'align-items' => $typed('string', 'stretch'), 'gap' => $size(22),
], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(18)]);
$path_intro = $container('atpthin', 'e-at-path-intro', [$widgets['cc34ebd'], $widgets['1a3cd74'], $widgets['259d71d']], [
    'width' => $size(100, '%'), 'max-width' => $size(800), 'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'center'), 'text-align' => $typed('string', 'center'), 'gap' => $size(10),
]);
$path_inner = $container('atpathi', 'e-at-path-inner', [$path_intro, $steps_grid, $widgets['56b4755']], [
    'width' => $size(100, '%'), 'max-width' => $size(1200), 'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'center'), 'gap' => $size(34),
    'padding' => $dimensions(['block-start' => 74, 'inline-end' => 24, 'block-end' => 78, 'inline-start' => 24]),
], ['padding' => $dimensions(['block-start' => 50, 'inline-end' => 18, 'block-end' => 54, 'inline-start' => 18])]);
$path = $container('atpath1', 'e-at-path', [$path_inner], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'column'), 'align-items' => $typed('string', 'center'),
    'background' => $background('#f3f7fa'),
]);

$program_nursing = $container('atnursg', 'e-at-program-nursing', [$widgets['a3e97c2'], $widgets['9550f88'], $widgets['d087e0e']], [
    'width' => $size(50, '%'), 'flex-direction' => $typed('string', 'column'), 'gap' => $size(14),
    'padding' => $size(36), 'background' => $background('#ffffff'), 'border-radius' => $size(20),
], ['width' => $size(100, '%'), 'padding' => $size(25)]);
$program_pta = $container('atptapr', 'e-at-program-pta', [$widgets['211dfd0'], $widgets['463f8b8'], $widgets['92c78d7']], [
    'width' => $size(50, '%'), 'flex-direction' => $typed('string', 'column'), 'gap' => $size(14),
    'padding' => $size(36), 'background' => $background('#f7c900'), 'border-radius' => $size(20),
], ['width' => $size(100, '%'), 'padding' => $size(25)]);
$program_grid = $container('atprggr', 'e-at-program-grid', [$program_nursing, $program_pta], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'), 'align-items' => $typed('string', 'stretch'), 'gap' => $size(24),
], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(20)]);
$requirements_intro = $container('atrqint', 'e-at-requirements-intro', [$widgets['575202f'], $widgets['aae96b1'], $widgets['ca6c4c6']], [
    'width' => $size(100, '%'), 'max-width' => $size(860), 'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'center'), 'text-align' => $typed('string', 'center'), 'gap' => $size(10),
]);
$requirements_inner = $container('atrequi', 'e-at-requirements-inner', [$requirements_intro, $program_grid], [
    'width' => $size(100, '%'), 'max-width' => $size(1200), 'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'center'), 'gap' => $size(36),
    'padding' => $dimensions(['block-start' => 74, 'inline-end' => 24, 'block-end' => 80, 'inline-start' => 24]),
], ['padding' => $dimensions(['block-start' => 50, 'inline-end' => 18, 'block-end' => 54, 'inline-start' => 18])]);
$requirements = $container('atrequs', 'e-at-requirements', [$requirements_inner], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'column'), 'align-items' => $typed('string', 'center'),
    'background' => $background('#082b55'),
]);

$finance_specs = [
    ['attuitn', 'e-at-tuition-card', '#ffffff', ['7219bd9', 'ed9505c', '8e6879e', '0599f1d', 'bf9ebd7']],
    ['atfinaid', 'e-at-aid-card', '#fff8d8', ['d485959', 'f124f72', '51e7171', '964fd8a', 'e84d16a']],
];
$finance_cards = [];
foreach ($finance_specs as [$id, $class, $color, $ids]) {
    $children = array_map(static fn(string $widget_id): array => $widgets[$widget_id], $ids);
    $finance_cards[] = $container($id, $class, $children, [
        'width' => $size(50, '%'), 'flex-direction' => $typed('string', 'column'), 'gap' => $size(12),
        'padding' => $size(40), 'background' => $background($color), 'border-radius' => $size(22),
    ], ['width' => $size(100, '%'), 'padding' => $size(26)]);
}
$finance_inner = $container('atfinin', 'e-at-finance-inner', $finance_cards, [
    'width' => $size(100, '%'), 'max-width' => $size(1200), 'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'stretch'), 'gap' => $size(24),
    'padding' => $dimensions(['block-start' => 76, 'inline-end' => 24, 'block-end' => 80, 'inline-start' => 24]),
], ['flex-direction' => $typed('string', 'column'), 'gap' => $size(20),
    'padding' => $dimensions(['block-start' => 50, 'inline-end' => 18, 'block-end' => 54, 'inline-start' => 18])]);
$finance = $container('atfinan', 'e-at-finance', [$finance_inner], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'column'), 'align-items' => $typed('string', 'center'),
    'background' => $background('#f3f7fa'),
]);

$accreditation_title = $container('ataccti', 'e-at-accreditation-title', [$widgets['ff3cbfc'], $widgets['8346bdb']], [
    'width' => $size(42, '%'), 'flex-direction' => $typed('string', 'column'), 'gap' => $size(12),
], ['width' => $size(100, '%')]);
$accreditation_copy = $container('ataccco', 'e-at-accreditation-copy', [$widgets['a6093de'], $widgets['1a70ff1'], $widgets['69f20b3']], [
    'width' => $size(58, '%'), 'flex-direction' => $typed('string', 'column'), 'gap' => $size(12),
    'padding' => $size(38), 'background' => $background('#ffffff'), 'border-radius' => $size(22),
], ['width' => $size(100, '%'), 'padding' => $size(25)]);
$widgets['ff3cbfc']['settings']['title_color'] = '#ffffff';
$widgets['8346bdb']['settings']['text_color'] = '#f7c900';
$accreditation_title['elements'] = [$widgets['ff3cbfc'], $widgets['8346bdb']];
$accreditation_inner = $container('ataccin', 'e-at-accreditation-inner', [$accreditation_title, $accreditation_copy], [
    'width' => $size(100, '%'), 'max-width' => $size(1200), 'flex-direction' => $typed('string', 'row'),
    'align-items' => $typed('string', 'center'), 'gap' => $size(48),
    'padding' => $dimensions(['block-start' => 76, 'inline-end' => 24, 'block-end' => 80, 'inline-start' => 24]),
], ['flex-direction' => $typed('string', 'column'), 'align-items' => $typed('string', 'flex-start'), 'gap' => $size(28),
    'padding' => $dimensions(['block-start' => 50, 'inline-end' => 18, 'block-end' => 54, 'inline-start' => 18])]);
$accreditation = $container('ataccre', 'e-at-accreditation', [$accreditation_inner], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'column'), 'align-items' => $typed('string', 'center'),
    'background' => $background('#082b55'),
]);

$cta_buttons = $container('atctabt', 'e-at-cta-buttons', [$widgets['29a7814'], $widgets['218b9a7']], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'row'), 'justify-content' => $typed('string', 'center'), 'gap' => $size(16),
], ['flex-direction' => $typed('string', 'column')]);
$cta_card = $container('atctacd', 'e-at-cta-card', [$widgets['2cff04e'], $widgets['f3da86e'], $widgets['352a995'], $widgets['b1a655b'], $cta_buttons], [
    'width' => $size(100, '%'), 'max-width' => $size(950), 'flex-direction' => $typed('string', 'column'),
    'align-items' => $typed('string', 'center'), 'text-align' => $typed('string', 'center'), 'gap' => $size(12),
    'padding' => $size(52), 'background' => $background('#f7c900'), 'border-radius' => $size(24),
], ['align-items' => $typed('string', 'stretch'), 'text-align' => $typed('string', 'start'), 'padding' => $size(28)]);
$cta_inner = $container('atctain', 'e-at-cta-inner', [$cta_card], [
    'width' => $size(100, '%'), 'max-width' => $size(1200), 'justify-content' => $typed('string', 'center'),
    'padding' => $dimensions(['block-start' => 72, 'inline-end' => 24, 'block-end' => 76, 'inline-start' => 24]),
], ['padding' => $dimensions(['block-start' => 48, 'inline-end' => 18, 'block-end' => 52, 'inline-start' => 18])]);
$cta = $container('atcta01', 'e-at-cta', [$cta_inner], [
    'width' => $size(100, '%'), 'flex-direction' => $typed('string', 'column'), 'align-items' => $typed('string', 'center'),
    'background' => $background('#ffffff'),
]);

$new_data = [$breadcrumbs, $hero, $path, $requirements, $finance, $accreditation, $cta];
$encoded = wp_slash(wp_json_encode($new_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
update_post_meta($post_id, '_elementor_data', $encoded);
update_post_meta($post_id, '_elementor_edit_mode', 'builder');
update_post_meta($post_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '4.2.2');
clean_post_cache($post_id);
if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}
WP_CLI::success('The Admissions and Tuition page was redesigned with native Elementor containers.');
