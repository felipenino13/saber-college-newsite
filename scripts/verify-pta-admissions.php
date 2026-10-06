<?php
/** Read-only regression checks against the original PTA Admissions backup. */
if (!defined('ABSPATH')) { exit(1); }
$before = json_decode(file_get_contents('/tmp/pta-admissions-881-before-latest.json'), true);
if (!$before) { WP_CLI::error('Original backup unavailable.'); }

$content_fields = [
    'c813558' => ['shortcode'], '21857c1' => ['title'], 'c2df82c' => ['title'],
    '94db9a3' => ['editor'], '6e51e9d' => ['editor'], 'a2f4879' => ['editor'],
    'c0e49d6' => ['title'], '8374ce8' => ['title'], '3c2b237' => ['editor'],
    '3d48339' => ['editor'], '7a7a590' => ['editor'], '5b30056' => ['editor'],
    '385aff4' => ['editor'],
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
$current = $extract(json_decode(get_post_meta(881, '_elementor_data', true), true));
$checks = ['all_original_content_unchanged' => $original === $current];
$checks['rank_math_unchanged'] = true;
foreach ($before['meta'] as $key => $value) {
    if (str_starts_with($key, 'rank_math_')) {
        $checks['rank_math_unchanged'] = $checks['rank_math_unchanged'] && $value === get_post_meta(881, $key);
    }
}
foreach ($before['protected_hashes'] as $id => $hash) {
    $checks['protected_' . $id . '_unchanged'] = hash_equals($hash, hash('sha256', get_post_meta($id, '_elementor_data', true)));
}
$checks['post_content_unchanged'] = $before['post']['post_content'] === get_post_field('post_content', 881);
$checks['all_13_fields_present'] = count($current) === 13;
WP_CLI::line(wp_json_encode($checks, JSON_PRETTY_PRINT));
if (in_array(false, $checks, true)) { WP_CLI::error('Regression check failed.'); }
WP_CLI::success('Original copy, links, SEO and protected templates are preserved.');
