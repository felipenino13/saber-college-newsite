<?php
/**
 * Fix the copied form styles on the local home page.
 *
 * The copied CSS still targeted Elementor template 2015 and form f698ff6.
 * This script scopes the same rules to home page 7 and form b7159a9 while
 * preserving the form fields, actions, scripts, visible copy, and structure.
 *
 * Run with: wp eval-file /tmp/fix-home-form-style-scope.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$post_id = 7;
$style_widget_id = '8d28c13';
$marker = '/* Home form: selector scope fixed 2026-10-16 */';
$page = get_post($post_id);

if (!$page || $page->post_name !== 'home') {
    WP_CLI::error('Page 7 is not the expected home page.');
}

$raw = get_post_meta($post_id, '_elementor_data', true);
$data = json_decode($raw, true);
if (!is_array($data)) {
    WP_CLI::error('Home Elementor data is not valid JSON.');
}

$protected = [];
foreach ([872, 2015] as $protected_id) {
    $protected[$protected_id] = hash('sha256', (string) get_post_meta($protected_id, '_elementor_data', true));
}

$count_nodes = static function (array $nodes) use (&$count_nodes): array {
    $counts = ['nodes' => 0, 'widgets' => 0, 'forms' => 0, 'html' => 0];
    foreach ($nodes as $node) {
        if (!is_array($node)) {
            continue;
        }
        $counts['nodes']++;
        if (($node['elType'] ?? '') === 'widget') {
            $counts['widgets']++;
            if (($node['widgetType'] ?? '') === 'form') {
                $counts['forms']++;
            }
            if (($node['widgetType'] ?? '') === 'html') {
                $counts['html']++;
            }
        }
        if (!empty($node['elements']) && is_array($node['elements'])) {
            $child = $count_nodes($node['elements']);
            foreach ($counts as $key => $value) {
                $counts[$key] += $child[$key];
            }
        }
    }
    return $counts;
};

$before_counts = $count_nodes($data);
$found = false;
$before_css_hash = '';
$after_css_hash = '';
$old_scope_count = 0;
$old_form_count = 0;

$update = static function (array &$nodes) use (
    &$update,
    &$found,
    &$before_css_hash,
    &$after_css_hash,
    &$old_scope_count,
    &$old_form_count,
    $style_widget_id,
    $marker
): void {
    foreach ($nodes as &$node) {
        if (!is_array($node)) {
            continue;
        }

        if (($node['id'] ?? '') === $style_widget_id) {
            if (($node['widgetType'] ?? '') !== 'html') {
                WP_CLI::error('The expected style widget is no longer an HTML widget.');
            }

            $css = (string) ($node['settings']['html'] ?? '');
            $before_css_hash = hash('sha256', $css);
            $found = true;

            if (str_contains($css, $marker)) {
                $after_css_hash = $before_css_hash;
                continue;
            }

            $old_scope_count = substr_count($css, '.elementor-2015');
            $old_form_count = substr_count($css, '.elementor-element-f698ff6');
            if ($old_scope_count < 1 || $old_form_count < 1) {
                WP_CLI::error('The copied template selectors were not found; no changes were made.');
            }

            $css = str_replace('.elementor-2015', '.elementor-7', $css);
            $css = str_replace('.elementor-element-f698ff6', '.elementor-element-b7159a9', $css);

            // The reference template has a legal-copy widget that was not copied
            // to the home. Remove only its unused rule instead of targeting an
            // unrelated home widget.
            $css = (string) preg_replace(
                '/\n\.elementor-7 \.elementor-element\.elementor-element-365f20a p \{.*?\n\}\n/s',
                "\n",
                $css,
                1
            );
            $css = preg_replace('/<style>\s*/', "<style>\n{$marker}\n", $css, 1);
            $node['settings']['html'] = $css;
            $after_css_hash = hash('sha256', $css);
        }

        if (!empty($node['elements']) && is_array($node['elements'])) {
            $update($node['elements']);
        }
    }
    unset($node);
};

$update($data);
if (!$found) {
    WP_CLI::error('Style widget 8d28c13 was not found; no changes were made.');
}

if ($before_css_hash === $after_css_hash && str_contains($raw, $marker)) {
    WP_CLI::line(wp_json_encode([
        'page_id' => $post_id,
        'status' => 'already_applied',
        'idempotent' => true,
        'elementor_json_sha256' => hash('sha256', $raw),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    return;
}

$after_counts = $count_nodes($data);
if ($before_counts !== $after_counts) {
    WP_CLI::error('Elementor node counts changed unexpectedly; no changes were made.');
}

$snapshot = [
    'created_utc' => gmdate('c'),
    'page_id' => $post_id,
    'post' => (array) $page,
    'meta' => get_post_meta($post_id),
    'protected_hashes' => $protected,
];
$backup = '/tmp/home-form-style-before-' . gmdate('Ymd-His') . '.json';
if (false === file_put_contents($backup, wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) {
    WP_CLI::error('Could not create the in-container backup; no changes were made.');
}

wp_save_post_revision($post_id);
update_post_meta($post_id, '_elementor_data', wp_slash(wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
delete_post_meta($post_id, '_elementor_element_cache');
do_action('elementor/atomic-widgets/styles/clear', ['local', $post_id]);
Elementor\Plugin::$instance->documents->get($post_id, false);
Elementor\Core\Files\CSS\Post::create($post_id)->update();
clean_post_cache($post_id);

foreach ($protected as $protected_id => $hash) {
    $current = hash('sha256', (string) get_post_meta($protected_id, '_elementor_data', true));
    if (!hash_equals($hash, $current)) {
        WP_CLI::error("Protected page/template {$protected_id} changed unexpectedly. Restore from {$backup}.");
    }
}

$saved = (string) get_post_meta($post_id, '_elementor_data', true);
WP_CLI::line('BACKUP=' . $backup);
WP_CLI::line(wp_json_encode([
    'page_id' => $post_id,
    'url' => get_permalink($post_id),
    'style_widget_id' => $style_widget_id,
    'form_widget_id' => 'b7159a9',
    'reference_template_id' => 2015,
    'replaced_page_scope_selectors' => $old_scope_count,
    'replaced_form_selectors' => $old_form_count,
    'node_counts_unchanged' => true,
    'form_settings_unchanged' => true,
    'protected_reference_unchanged' => true,
    'elementor_json_sha256' => hash('sha256', $saved),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

