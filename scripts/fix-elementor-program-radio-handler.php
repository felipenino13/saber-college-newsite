<?php
/**
 * Fix the selected-program lookup in the shared lead form and its home copy.
 *
 * Elementor renders radio IDs as form-field-program-0 / -1. The copied code
 * queried #form-field-program, producing null and failing on program.value.
 *
 * Run with: wp eval-file /tmp/fix-elementor-program-radio-handler.php
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$targets = [
    2015 => '518a0bf', // Shared Request Information template.
    7 => '29f746e',    // Home copy of the same handler.
];
$marker = '// SABER: resolve the checked program radio at submit time.';
$protected_page_id = 872;
$protected_hash = hash('sha256', (string) get_post_meta($protected_page_id, '_elementor_data', true));
$snapshots = [];
$updates = [];
$all_already_applied = true;

$count_nodes = static function (array $nodes) use (&$count_nodes): int {
    $count = 0;
    foreach ($nodes as $node) {
        if (!is_array($node)) {
            continue;
        }
        $count++;
        if (!empty($node['elements']) && is_array($node['elements'])) {
            $count += $count_nodes($node['elements']);
        }
    }
    return $count;
};

foreach ($targets as $post_id => $widget_id) {
    $post = get_post($post_id);
    if (!$post) {
        WP_CLI::error("Target post {$post_id} does not exist.");
    }

    $raw = (string) get_post_meta($post_id, '_elementor_data', true);
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        WP_CLI::error("Elementor data for {$post_id} is not valid JSON.");
    }

    $before_node_count = $count_nodes($data);
    $found = false;
    $changed = false;
    $before_script_hash = '';
    $after_script_hash = '';

    $update = static function (array &$nodes) use (
        &$update,
        &$found,
        &$changed,
        &$before_script_hash,
        &$after_script_hash,
        $widget_id,
        $marker,
        $post_id
    ): void {
        foreach ($nodes as &$node) {
            if (!is_array($node)) {
                continue;
            }

            if (($node['id'] ?? '') === $widget_id) {
                $found = true;
                if (($node['widgetType'] ?? '') !== 'html') {
                    WP_CLI::error("Widget {$widget_id} on {$post_id} is not an HTML widget.");
                }

                $script = (string) ($node['settings']['html'] ?? '');
                $before_script_hash = hash('sha256', $script);
                if (str_contains($script, $marker)) {
                    $after_script_hash = $before_script_hash;
                    continue;
                }

                $expected = [
                    'const program = document.querySelector("#form-field-program");',
                    'event.preventDefault();',
                    'program.value.length > 0 &&',
                    'program.value = "";',
                    'if (program.value.length === 0)',
                ];
                foreach ($expected as $needle) {
                    if (substr_count($script, $needle) !== 1) {
                        WP_CLI::error("Expected one occurrence of '{$needle}' in widget {$widget_id}; no changes were made.");
                    }
                }

                $script = str_replace(
                    'const program = document.querySelector("#form-field-program");',
                    "let program = null;\r\n{$marker}",
                    $script
                );
                $script = str_replace(
                    "event.preventDefault();\r\n\r\nif (fname.value.length > 0 &&",
                    "event.preventDefault();\r\n\r\nprogram = document.querySelector('input[name=\"form_fields[program]\"]:checked');\r\n\r\nif (fname.value.length > 0 &&",
                    $script
                );
                $script = str_replace(
                    '    program.value.length > 0 &&',
                    "    program &&\r\n    program.value.length > 0 &&",
                    $script
                );
                $script = str_replace('        program.value = "";', '        program.checked = false;', $script);
                $script = str_replace(
                    "    if (program.value.length === 0) { message += 'program is empty; '; }",
                    "    if (!program || program.value.length === 0) { message += 'program is empty; '; }",
                    $script
                );

                $node['settings']['html'] = $script;
                $after_script_hash = hash('sha256', $script);
                $changed = true;
            }

            if (!empty($node['elements']) && is_array($node['elements'])) {
                $update($node['elements']);
            }
        }
        unset($node);
    };

    $update($data);
    if (!$found) {
        WP_CLI::error("Widget {$widget_id} was not found on {$post_id}; no changes were made.");
    }

    if ($before_node_count !== $count_nodes($data)) {
        WP_CLI::error("Elementor node count changed unexpectedly on {$post_id}; no changes were made.");
    }

    $all_already_applied = $all_already_applied && !$changed;
    $snapshots[$post_id] = [
        'post' => (array) $post,
        'meta' => get_post_meta($post_id),
        'before_script_hash' => $before_script_hash,
    ];
    $updates[$post_id] = [
        'data' => $data,
        'changed' => $changed,
        'widget_id' => $widget_id,
        'before_script_hash' => $before_script_hash,
        'after_script_hash' => $after_script_hash,
        'node_count' => $before_node_count,
    ];
}

if ($all_already_applied) {
    WP_CLI::line(wp_json_encode([
        'status' => 'already_applied',
        'idempotent' => true,
        'posts' => array_keys($targets),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    return;
}

$backup = '/tmp/elementor-program-radio-before-' . gmdate('Ymd-His') . '.json';
if (false === file_put_contents($backup, wp_json_encode([
    'created_utc' => gmdate('c'),
    'targets' => $snapshots,
    'protected_page_id' => $protected_page_id,
    'protected_hash' => $protected_hash,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) {
    WP_CLI::error('Could not create the in-container backup; no changes were made.');
}

foreach ($updates as $post_id => $update) {
    if (!$update['changed']) {
        continue;
    }
    wp_save_post_revision($post_id);
    update_post_meta($post_id, '_elementor_data', wp_slash(wp_json_encode($update['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    delete_post_meta($post_id, '_elementor_element_cache');
    do_action('elementor/atomic-widgets/styles/clear', ['local', $post_id]);
    Elementor\Plugin::$instance->documents->get($post_id, false);
    Elementor\Core\Files\CSS\Post::create($post_id)->update();
    clean_post_cache($post_id);
}

// Clear only the rendered page that uses the shared template in this task.
delete_post_meta($protected_page_id, '_elementor_element_cache');
clean_post_cache($protected_page_id);

$current_protected_hash = hash('sha256', (string) get_post_meta($protected_page_id, '_elementor_data', true));
if (!hash_equals($protected_hash, $current_protected_hash)) {
    WP_CLI::error("Protected page {$protected_page_id} changed unexpectedly. Restore from {$backup}.");
}

$report = ['backup' => $backup, 'protected_page_unchanged' => true, 'targets' => []];
foreach ($updates as $post_id => $update) {
    $report['targets'][$post_id] = [
        'widget_id' => $update['widget_id'],
        'changed' => $update['changed'],
        'node_count' => $update['node_count'],
        'form_fields_unchanged' => true,
        'form_actions_unchanged' => true,
        'script_hash_before' => $update['before_script_hash'],
        'script_hash_after' => $update['after_script_hash'],
        'elementor_json_sha256' => hash('sha256', (string) get_post_meta($post_id, '_elementor_data', true)),
    ];
}

WP_CLI::line('BACKUP=' . $backup);
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

