<?php
/** Read-only regression checks against the pre-redesign backup. */
if (!defined('ABSPATH')) { exit(1); }
$before = json_decode(file_get_contents('/tmp/student-services-34-before-20261002-113559.json'), true);
if (!$before) { WP_CLI::error('Original backup unavailable.'); }
$collect = function ($nodes) use (&$collect) {
    $copy = [];
    foreach ($nodes as $node) {
        foreach (['title', 'editor', 'shortcode'] as $key) {
            if (isset($node['settings'][$key])) { $copy[$node['id'] . ':' . $key] = $node['settings'][$key]; }
        }
        $copy += $collect($node['elements'] ?? []);
    }
    return $copy;
};
$original = $collect(json_decode($before['meta']['_elementor_data'][0], true));
$current = $collect(json_decode(get_post_meta(34, '_elementor_data', true), true));
$checks = ['all_original_copy_unchanged' => $original === $current];
$checks['rank_math_unchanged'] = true;
foreach ($before['meta'] as $key => $value) {
    if (str_starts_with($key, 'rank_math_')) {
        $checks['rank_math_unchanged'] = $checks['rank_math_unchanged'] && $value === get_post_meta(34, $key);
    }
}
foreach ($before['protected_hashes'] as $id => $hash) {
    $checks['protected_' . $id . '_unchanged'] = hash_equals($hash, hash('sha256', get_post_meta($id, '_elementor_data', true)));
}
$checks['post_content_unchanged'] = $before['post']['post_content'] === get_post_field('post_content', 34);
WP_CLI::line(wp_json_encode($checks, JSON_PRETTY_PRINT));
if (in_array(false, $checks, true)) { WP_CLI::error('Regression check failed.'); }
WP_CLI::success('All original copy, links, SEO and protected templates preserved.');
