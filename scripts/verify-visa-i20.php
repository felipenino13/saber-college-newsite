<?php
/** Read-only regression checks for the VISA I-20 Assistance redesign. */
if (!defined('ABSPATH')) { exit(1); }

$before = json_decode(file_get_contents('/tmp/visa-i20-898-before-latest.json'), true);
if (!$before) { WP_CLI::error('Original backup unavailable.'); }

$content_fields = [
    '06fc8b6' => ['shortcode'], '0afdcc5' => ['title'], '39864a0' => ['editor'],
    '97fd8f9' => ['editor'], '11c7185' => ['editor'], '9766170' => ['editor'],
    '6f524af' => ['title'], '9c0ea31' => ['editor'], 'be0c041' => ['editor'],
    '2428553' => ['title'], '12ef8a1' => ['icon_list'], '2bd928a' => ['title'],
    '96e8c90' => ['editor'], 'c15c1f5' => ['editor'], 'bd01d63' => ['title'],
    'adec432' => ['editor'], '560bef8' => ['editor'], 'fdd7f3c' => ['editor'],
    '3efbc28' => ['editor'], '20f3b50' => ['title'], 'a09b3a9' => ['editor'],
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
$current_data = json_decode(get_post_meta(898, '_elementor_data', true), true);
$current = $extract($current_data);

$checks = ['all_original_content_unchanged' => $original === $current];
$checks['rank_math_unchanged'] = true;
foreach ($before['meta'] as $key => $value) {
    if (str_starts_with($key, 'rank_math_') && $key !== 'rank_math_seo_score') {
        $checks['rank_math_unchanged'] = $checks['rank_math_unchanged'] && $value === get_post_meta(898, $key);
    }
}
foreach ($before['protected_hashes'] as $id => $hash) {
    $checks['protected_' . $id . '_unchanged'] = hash_equals($hash, hash('sha256', get_post_meta($id, '_elementor_data', true)));
}
$checks['post_content_unchanged'] = $before['post']['post_content'] === get_post_field('post_content', 898);
$checks['all_21_fields_present'] = count($current) === 21;
$checks['all_3_why_items_present'] = count($current['12ef8a1']['icon_list'] ?? []) === 3;
$checks['admission_pdf_link_present'] = str_contains($current['97fd8f9']['editor'] ?? '', 'ADMISSION-OF-INTERNATIONAL-STUDENTS.pdf');
$checks['sevp_link_present'] = str_contains($current['96e8c90']['editor'] ?? '', 'studyinthestates.dhs.gov/certified-school/9690');
$checks['all_6_process_steps_present'] = substr_count($current['560bef8']['editor'] ?? '', '<li>') === 6;

$invalid_style_labels = [];
$validate_styles = function ($nodes) use (&$validate_styles, &$invalid_style_labels) {
    foreach ($nodes as $node) {
        foreach (($node['styles'] ?? []) as $style_key => $style) {
            $label = (string) ($style['label'] ?? '');
            if ($label === '' || strlen($label) > 50 || !preg_match('/^[A-Za-z_-][A-Za-z0-9_-]*$/', $label)) {
                $invalid_style_labels[] = ['element' => $node['id'] ?? '', 'style' => $style_key, 'label' => $label];
            }
        }
        $validate_styles($node['elements'] ?? []);
    }
};
$validate_styles($current_data);
$checks['style_labels_valid_for_elementor'] = $invalid_style_labels === [];

WP_CLI::line(wp_json_encode($checks, JSON_PRETTY_PRINT));
if ($invalid_style_labels) {
    WP_CLI::line(wp_json_encode(['invalid_style_labels' => $invalid_style_labels], JSON_PRETTY_PRINT));
}
if (in_array(false, $checks, true)) { WP_CLI::error('Regression check failed.'); }
WP_CLI::success('Original copy, links, lists, SEO, protected templates and Elementor style labels are preserved.');
