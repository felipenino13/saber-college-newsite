<?php
/** Read-only regression checks for the Student Transcript Request redesign. */
if (!defined('ABSPATH')) { exit(1); }

$before = json_decode(file_get_contents('/tmp/student-transcript-902-before-latest.json'), true);
if (!$before) { WP_CLI::error('Original backup unavailable.'); }

$content_fields = [
    '0ef606e' => ['shortcode'], 'c78e913' => ['title'], '25b14b5' => ['editor'],
    '2b9865a' => ['editor'], 'db01cd8' => ['editor'], 'abaad2b' => ['editor'],
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
$current_data = json_decode(get_post_meta(902, '_elementor_data', true), true);
$current = $extract($current_data);

$checks = ['all_original_content_unchanged' => $original === $current];
$checks['rank_math_unchanged'] = true;
foreach ($before['meta'] as $key => $value) {
    if (str_starts_with($key, 'rank_math_') && $key !== 'rank_math_seo_score') {
        $checks['rank_math_unchanged'] = $checks['rank_math_unchanged'] && $value === get_post_meta(902, $key);
    }
}
foreach ($before['protected_hashes'] as $id => $hash) {
    $checks['protected_' . $id . '_unchanged'] = hash_equals($hash, hash('sha256', get_post_meta($id, '_elementor_data', true)));
}
$checks['post_content_unchanged'] = $before['post']['post_content'] === get_post_field('post_content', 902);
$checks['all_6_fields_present'] = count($current) === 6;
$checks['document_link_present'] = str_contains($current['25b14b5']['editor'] ?? '', 'SABER-College-Transcript-Request-10-07-24-New-Logo.docx.pdf');
$checks['all_3_steps_present'] = substr_count($current['2b9865a']['editor'] ?? '', "\n<li>\n") >= 3;
$checks['all_5_email_destinations_present'] = substr_count($current['2b9865a']['editor'] ?? '', '@sabercollege.edu') === 5;

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
WP_CLI::success('Original copy, document link, email destinations, SEO, protected templates and style labels are preserved.');
