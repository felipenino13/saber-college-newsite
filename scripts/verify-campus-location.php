<?php
/** Verify Campus Location against its pre-change snapshot. */
if (!defined('ABSPATH')) { exit(1); }
$post_id = 861;
$backup_file = '/tmp/campus-location-861-before-latest.json';
if (!is_file($backup_file)) { WP_CLI::error('Campus Location backup was not found.'); }
$snapshot = json_decode(file_get_contents($backup_file), true);
if (!is_array($snapshot)) { WP_CLI::error('Campus Location backup is invalid.'); }
$data = json_decode(get_post_meta($post_id, '_elementor_data', true), true);
if (!is_array($data)) { WP_CLI::error('Current Elementor data is invalid.'); }
$widgets = [];
$labels = [];
$collect = function ($nodes) use (&$collect, &$widgets, &$labels) {
    foreach ($nodes as $node) {
        if (($node['elType'] ?? '') === 'widget' && isset($node['id'])) { $widgets[$node['id']] = $node; }
        foreach (($node['styles'] ?? []) as $style) { if (isset($style['label'])) { $labels[] = $style['label']; } }
        $collect($node['elements'] ?? []);
    }
};
$collect($data);
$content_ok = true;
foreach ($snapshot['original_content'] as $id => $fields) {
    foreach ($fields as $field => $value) {
        $content_ok = $content_ok && isset($widgets[$id]) && (($widgets[$id]['settings'][$field] ?? null) === $value);
    }
}
$rank_math = [];
foreach (get_post_meta($post_id) as $key => $values) {
    if (str_starts_with($key, 'rank_math_')) { $rank_math[$key] = $values; }
}
$protected = [];
foreach ($snapshot['protected_hashes'] as $id => $hash) {
    $protected[$id] = hash_equals($hash, hash('sha256', get_post_meta($id, '_elementor_data', true)));
}
$labels_ok = true;
foreach ($labels as $label) { if (!preg_match('/^[a-z0-9_-]+$/', $label)) { $labels_ok = false; break; } }
$page = get_post($post_id);
$map = $widgets['e199f5a']['settings']['html'] ?? '';
$report = [
    'all_13_content_fields_unchanged' => $content_ok,
    'all_original_widgets_present' => count(array_intersect(array_keys($snapshot['original_content']), array_keys($widgets))) === 13,
    'rank_math_unchanged' => $rank_math === $snapshot['rank_math'],
    'protected_7_unchanged' => $protected[7] ?? false,
    'protected_16_unchanged' => $protected[16] ?? false,
    'protected_53_unchanged' => $protected[53] ?? false,
    'post_content_unchanged' => hash_equals($snapshot['post_content_hash'], hash('sha256', $page->post_content)),
    'page_identity_unchanged' => $page->post_name === 'campus-location' && (int) $page->post_parent === 0 && $page->post_status === 'publish',
    'one_original_h1_widget' => (($widgets['0bdab84']['settings']['header_size'] ?? '') === 'h1'),
    'main_contact_items_present' => count($widgets['753b6eb']['settings']['icon_list'] ?? []) === 3,
    'visit_items_present' => count($widgets['c937775']['settings']['icon_list'] ?? []) === 2,
    'admissions_contact_items_present' => count($widgets['4a46000']['settings']['icon_list'] ?? []) === 3 && count($widgets['a0f069c']['settings']['icon_list'] ?? []) === 3,
    'map_embed_present' => str_contains($map, 'google.com/maps/embed') && str_contains($map, 'SABER%20College'),
    'style_labels_valid_for_elementor' => $labels_ok,
    'elementor_widget_count' => count($widgets),
];
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
$checks = $report;
unset($checks['elementor_widget_count']);
if (in_array(false, $checks, true)) { WP_CLI::error('Campus Location verification failed.'); }
WP_CLI::success('Campus Location copy, contacts, map, SEO, protected templates and style labels are preserved.');
