<?php
/** Read-only regression checks against the original Apply To SABER backup. */
if (!defined('ABSPATH')) { exit(1); }
$before = json_decode(file_get_contents('/tmp/apply-saber-883-before-latest.json'), true);
if (!$before) { WP_CLI::error('Original backup unavailable.'); }

$content_fields = [
    '71b8471' => ['shortcode'], '0d5b90d' => ['title'], '4a076a9' => ['editor'],
    'f4dd20f' => ['title'], '54993c7' => ['editor'], '5faa943' => ['title'],
    '21670a6' => ['editor'], '07062f8' => ['icon_list'], 'dbf6d69' => ['editor'],
    'ba40f70' => ['title'], '51ccffc' => ['editor'], '1530653' => ['editor'],
    '427b09c' => ['icon_list'], '9390b03' => ['title'], '43dcca2' => ['editor'],
    '3e2a333' => ['editor'], 'b8311e0' => ['icon_list'], '8aeac69' => ['editor'],
    '16ba6a7' => ['editor'], 'a3cc0a1' => ['icon_list'], '2deec22' => ['editor'],
    '612a4b5' => ['title'], '19b57a4' => ['editor'], '9c619c7' => ['editor'],
    'd5dbe5b' => ['editor'], '33ce315' => ['title'], '366a581' => ['editor'],
    '59bc269' => ['icon_list'], '6f67d05' => ['editor'], 'b66aaf4' => ['title'],
    '8b04218' => ['editor'], 'b108ce4' => ['editor'], '769aa6e' => ['title'],
    '1801b59' => ['editor'], 'ab134a4' => ['editor'],
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
$current = $extract(json_decode(get_post_meta(883, '_elementor_data', true), true));
$checks = ['all_original_content_unchanged' => $original === $current];
$checks['rank_math_unchanged'] = true;
foreach ($before['meta'] as $key => $value) {
    if (str_starts_with($key, 'rank_math_')) {
        $checks['rank_math_unchanged'] = $checks['rank_math_unchanged'] && $value === get_post_meta(883, $key);
    }
}
foreach ($before['protected_hashes'] as $id => $hash) {
    $checks['protected_' . $id . '_unchanged'] = hash_equals($hash, hash('sha256', get_post_meta($id, '_elementor_data', true)));
}
$checks['post_content_unchanged'] = $before['post']['post_content'] === get_post_field('post_content', 883);
$checks['all_35_fields_present'] = count($current) === 35;
WP_CLI::line(wp_json_encode($checks, JSON_PRETTY_PRINT));
if (in_array(false, $checks, true)) { WP_CLI::error('Regression check failed.'); }
WP_CLI::success('Original copy, lists, SEO and protected templates are preserved.');
