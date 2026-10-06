<?php
/** Read-only regression checks against the original Tuition Payment Options backup. */
if (!defined('ABSPATH')) { exit(1); }
$before = json_decode(file_get_contents('/tmp/tuition-payment-885-before-latest.json'), true);
if (!$before) { WP_CLI::error('Original backup unavailable.'); }

$content_fields = [
    'fec4385' => ['shortcode'], '6f304e4' => ['title'], 'a9ccbe6' => ['editor'],
    '1ed8ea3' => ['editor'], '86cacf5' => ['editor'], 'bcbb8c3' => ['editor'],
    'aee7d09' => ['editor'], 'eef7e1a' => ['editor'], '9fe5110' => ['editor'],
    '4fd533f' => ['editor'], 'e097e65' => ['editor'], '4a11296' => ['editor'],
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
$current = $extract(json_decode(get_post_meta(885, '_elementor_data', true), true));
$checks = ['all_original_content_unchanged' => $original === $current];
$checks['rank_math_unchanged'] = true;
$rank_math_differences = [];
foreach ($before['meta'] as $key => $value) {
    if (str_starts_with($key, 'rank_math_') && $key !== 'rank_math_seo_score') {
        $current_rank_math = get_post_meta(885, $key);
        if ($value !== $current_rank_math) {
            $checks['rank_math_unchanged'] = false;
            $rank_math_differences[$key] = ['before' => $value, 'current' => $current_rank_math];
        }
    }
}
foreach ($before['protected_hashes'] as $id => $hash) {
    $checks['protected_' . $id . '_unchanged'] = hash_equals($hash, hash('sha256', get_post_meta($id, '_elementor_data', true)));
}
$checks['post_content_unchanged'] = $before['post']['post_content'] === get_post_field('post_content', 885);
$checks['all_12_fields_present'] = count($current) === 12;
$checks['document_link_preserved'] = str_contains($current['4a11296']['editor'] ?? '', '/wp-content/uploads/2022/12/Disclosure-for-Direct-Loans.pdf');
$current_data = json_decode(get_post_meta(885, '_elementor_data', true), true);
$invalid_style_labels = [];
$validate_styles = function ($nodes) use (&$validate_styles, &$invalid_style_labels) {
    foreach ($nodes as $node) {
        foreach (($node['styles'] ?? []) as $style_key => $style) {
            $label = (string) ($style['label'] ?? '');
            if ($label === '' || !preg_match('/^[A-Za-z_-][A-Za-z0-9_-]*$/', $label)) {
                $invalid_style_labels[] = ['element' => $node['id'] ?? '', 'style' => $style_key, 'label' => $label];
            }
        }
        $validate_styles($node['elements'] ?? []);
    }
};
$validate_styles($current_data);
$checks['style_labels_valid_for_elementor'] = $invalid_style_labels === [];
WP_CLI::line(wp_json_encode($checks, JSON_PRETTY_PRINT));
if ($rank_math_differences || $invalid_style_labels) {
    WP_CLI::line(wp_json_encode([
        'rank_math_differences' => $rank_math_differences,
        'invalid_style_labels' => $invalid_style_labels,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
if (in_array(false, $checks, true)) { WP_CLI::error('Regression check failed.'); }
WP_CLI::success('Original copy, document link, SEO and protected templates are preserved.');
