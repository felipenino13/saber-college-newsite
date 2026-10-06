<?php
/** Repair Elementor atomic style labels on Tuition Payment Options without changing content. */
if (!defined('ABSPATH')) { exit(1); }

$post_id = 885;
$raw = get_post_meta($post_id, '_elementor_data', true);
$data = json_decode($raw, true);
if (!is_array($data)) { WP_CLI::error('Invalid Elementor data.'); }

$backup = '/tmp/tuition-payment-885-before-style-label-fix-' . gmdate('Ymd-His') . '.json';
if (false === file_put_contents($backup, $raw)) {
    WP_CLI::error('Could not create the pre-repair backup. Nothing was changed.');
}

$changed = [];
$repair = function ($nodes) use (&$repair, &$changed) {
    foreach ($nodes as &$node) {
        if (isset($node['styles']) && is_array($node['styles'])) {
            foreach ($node['styles'] as $style_key => &$style) {
                $current = (string) ($style['label'] ?? '');
                $safe = (string) ($style['id'] ?? $style_key);
                if (!preg_match('/^[A-Za-z_-][A-Za-z0-9_-]*$/', $safe)) {
                    $safe = sanitize_html_class($safe, 'saber-style');
                }
                if ($current !== $safe) {
                    $changed[] = [
                        'element' => $node['id'] ?? '',
                        'style' => $style_key,
                        'before' => $current,
                        'after' => $safe,
                    ];
                    $style['label'] = $safe;
                }
            }
            unset($style);
        }
        $node['elements'] = $repair($node['elements'] ?? []);
    }
    unset($node);
    return $nodes;
};
$data = $repair($data);

if ($changed) {
    $updated = update_post_meta($post_id, '_elementor_data', wp_slash(wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    if ($updated === false) { WP_CLI::error('Elementor data could not be updated.'); }
    update_post_meta($post_id, '_elementor_edit_mode', 'builder');
    if (class_exists('Elementor\\Plugin')) {
        Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    clean_post_cache($post_id);
}

WP_CLI::line(wp_json_encode([
    'page_id' => $post_id,
    'backup' => $backup,
    'labels_repaired' => count($changed),
    'changes' => $changed,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success($changed ? 'Elementor style labels repaired.' : 'No invalid Elementor style labels found.');
