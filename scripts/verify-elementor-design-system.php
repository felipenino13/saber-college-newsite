<?php
/** Read-only checks for the SABER Elementor component library. */
if (!defined('ABSPATH')) { exit(1); }
$page = get_page_by_path('saber-design-system-elementor', OBJECT, 'page');
if (!$page) { WP_CLI::error('Design system page is missing.'); }
$data = json_decode(get_post_meta($page->ID, '_elementor_data', true), true);
if (!is_array($data)) { WP_CLI::error('Elementor data is invalid.'); }

$widgets = [];
$walk = function ($nodes) use (&$walk, &$widgets) {
    foreach ($nodes as $node) {
        if (($node['elType'] ?? '') === 'widget') { $widgets[] = $node; }
        $walk($node['elements'] ?? []);
    }
};
$walk($data);
$forms = array_values(array_filter($widgets, fn($widget) => ($widget['widgetType'] ?? '') === 'form'));
$anchors = [];
foreach ($widgets as $widget) {
    if (($widget['widgetType'] ?? '') === 'menu-anchor') { $anchors[] = $widget['settings']['anchor'] ?? ''; }
}
$h1 = array_values(array_filter($widgets, fn($widget) =>
    ($widget['widgetType'] ?? '') === 'heading' && ($widget['settings']['header_size'] ?? '') === 'h1'
));
$settings = get_post_meta($page->ID, '_elementor_page_settings', true);
$checks = [
    'published' => $page->post_status === 'publish',
    'title_correct' => $page->post_title === 'SABER Design System – Elementor',
    'noindex' => get_post_meta($page->ID, 'rank_math_robots', true) === ['noindex'],
    'hide_title' => ($settings['hide_title'] ?? '') === 'yes',
    'seven_sections' => count($data) === 7,
    'ninety_four_widgets' => count($widgets) === 94,
    'single_h1' => count($h1) === 1,
    'anchors_present' => $anchors === ['components', 'patterns'],
    'single_demo_form' => count($forms) === 1,
    'form_has_no_actions' => count($forms) === 1 && ($forms[0]['settings']['submit_actions'] ?? null) === [],
    'form_button_disabled_on_reference' => str_contains($settings['custom_css'] ?? '', 'pointer-events: none'),
];
WP_CLI::line(wp_json_encode($checks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
if (in_array(false, $checks, true)) { WP_CLI::error('Design system verification failed.'); }
WP_CLI::success('Elementor design system page passed all structural checks.');
