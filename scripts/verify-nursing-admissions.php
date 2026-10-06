<?php
/** Read-only regression checks against the original Nursing Admissions backup. */
if (!defined('ABSPATH')) { exit(1); }
$before = json_decode(file_get_contents('/tmp/nursing-admissions-877-before-20261002-115129.json'), true);
if (!$before) { WP_CLI::error('Original backup unavailable.'); }

$content_fields = [
    'd7f3000' => ['shortcode'], 'd0eeed3' => ['title'], '5e4d671' => ['title'],
    '06ff86b' => ['editor'], '2681e7b' => ['editor'], '8c93c3d' => ['title'],
    '85abbc6' => ['editor'], '2999fe5' => ['title'], 'caa7f81' => ['editor'],
    '8bad809' => ['title'], 'cb237b4' => ['editor'], '89b8e2f' => ['title'],
    '31e6daf' => ['icon_list'],
];
$extract = function ($nodes) use (&$extract, $content_fields) {
    $content = [];
    foreach ($nodes as $node) {
        if (isset($content_fields[$node['id']])) {
            foreach ($content_fields[$node['id']] as $field) {
                $content[$node['id']][$field] = $node['settings'][$field] ?? null;
            }
        }
        $content += $extract($node['elements'] ?? []);
    }
    return $content;
};
$original = $extract(json_decode($before['meta']['_elementor_data'][0], true));
$current = $extract(json_decode(get_post_meta(877, '_elementor_data', true), true));
$checks = ['all_original_content_unchanged' => $original === $current];
$checks['rank_math_unchanged'] = true;
foreach ($before['meta'] as $key => $value) {
    if (str_starts_with($key, 'rank_math_')) {
        $checks['rank_math_unchanged'] = $checks['rank_math_unchanged'] && $value === get_post_meta(877, $key);
    }
}
foreach ($before['protected_hashes'] as $id => $hash) {
    $checks['protected_' . $id . '_unchanged'] = hash_equals($hash, hash('sha256', get_post_meta($id, '_elementor_data', true)));
}
$checks['post_content_unchanged'] = $before['post']['post_content'] === get_post_field('post_content', 877);
WP_CLI::line(wp_json_encode($checks, JSON_PRETTY_PRINT));
if (in_array(false, $checks, true)) { WP_CLI::error('Regression check failed.'); }
WP_CLI::success('Original copy, links, SEO and protected templates are preserved.');
