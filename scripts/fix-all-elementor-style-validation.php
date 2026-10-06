<?php
/**
 * Normalize Elementor atomic styles that block editor saves.
 *
 * Repairs:
 * - invalid labels containing spaces by using the style class ID;
 * - logical text alignment: left -> start and right -> end.
 */

if (!defined('ABSPATH') || !class_exists('WP_CLI')) {
    exit("Run this script through WP-CLI.\n");
}

use Elementor\Modules\AtomicWidgets\Parsers\Style_Parser;
use Elementor\Modules\AtomicWidgets\Styles\Style_Schema;

function saber_global_collect_content(array $elements, array &$content): void
{
    foreach ($elements as $element) {
        foreach (['title', 'editor', 'text', 'button_text', 'description', 'caption'] as $key) {
            if (array_key_exists($key, $element['settings'] ?? [])) {
                $content[] = [$element['id'] ?? '', $key, $element['settings'][$key]];
            }
        }

        if (!empty($element['elements']) && is_array($element['elements'])) {
            saber_global_collect_content($element['elements'], $content);
        }
    }
}

function saber_global_normalize_styles(array &$elements, array &$changes): void
{
    foreach ($elements as &$element) {
        if (!empty($element['styles']) && is_array($element['styles'])) {
            foreach ($element['styles'] as $style_key => &$style) {
            $label = (string) ($style['label'] ?? '');
            if (!preg_match('/^[A-Za-z_-][A-Za-z0-9_-]*$/', $label)) {
                $safe_label = (string) ($style['id'] ?? $style_key);
                if (!preg_match('/^[A-Za-z_-][A-Za-z0-9_-]*$/', $safe_label)) {
                    $safe_label = sanitize_html_class($safe_label, 'saber-style');
                }

                $changes['labels'][] = [
                    'element' => $element['id'] ?? '',
                    'style' => $style_key,
                    'before' => $label,
                    'after' => $safe_label,
                ];
                $style['label'] = $safe_label;
            }

            if (empty($style['variants']) || !is_array($style['variants'])) {
                continue;
            }

            foreach ($style['variants'] as $variant_index => &$variant) {
                $value = $variant['props']['text-align']['value'] ?? null;
                $replacement = ['left' => 'start', 'right' => 'end'][$value] ?? null;
                if ($replacement !== null) {
                    $changes['text_align'][] = [
                        'element' => $element['id'] ?? '',
                        'style' => $style_key,
                        'variant' => $variant_index,
                        'breakpoint' => $variant['meta']['breakpoint'] ?? null,
                        'before' => $value,
                        'after' => $replacement,
                    ];
                    $variant['props']['text-align']['value'] = $replacement;
                }
            }
            unset($variant);
        }
        unset($style);
        }

        if (!empty($element['elements']) && is_array($element['elements'])) {
            saber_global_normalize_styles($element['elements'], $changes);
        }
    }
    unset($element);
}

function saber_global_validate_styles(array $elements, array &$errors): int
{
    $checked = 0;

    foreach ($elements as $element) {
        foreach (($element['styles'] ?? []) as $style_key => $style) {
            $checked++;
            $parser = Style_Parser::make(Style_Schema::get());
            $result = $parser->parse($style);
            if (!$result->is_valid()) {
                $errors[] = [
                    'element' => $element['id'] ?? '',
                    'style' => $style_key,
                    'errors' => $result->errors()->to_string(),
                ];
            }
        }

        if (!empty($element['elements']) && is_array($element['elements'])) {
            $checked += saber_global_validate_styles($element['elements'], $errors);
        }
    }

    return $checked;
}

$query = new WP_Query([
    'post_type' => 'any',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'meta_key' => '_elementor_data',
    'fields' => 'ids',
    'orderby' => 'ID',
    'order' => 'ASC',
]);

$prepared = [];
$totals = [
    'documents_scanned' => 0,
    'styles_checked' => 0,
    'documents_changed' => 0,
    'labels_repaired' => 0,
    'text_align_repaired' => 0,
];

foreach ($query->posts as $post_id) {
    $raw = get_post_meta($post_id, '_elementor_data', true);
    $data = json_decode($raw, true);
    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
        WP_CLI::error("Post {$post_id} has invalid Elementor JSON. No changes were saved.");
    }

    $totals['documents_scanned']++;
    $content_before = [];
    saber_global_collect_content($data, $content_before);
    $content_hash_before = hash('sha256', wp_json_encode($content_before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    $changes = ['labels' => [], 'text_align' => []];
    saber_global_normalize_styles($data, $changes);

    $content_after = [];
    saber_global_collect_content($data, $content_after);
    $content_hash_after = hash('sha256', wp_json_encode($content_after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if (!hash_equals($content_hash_before, $content_hash_after)) {
        WP_CLI::error("Content changed unexpectedly on post {$post_id}. No changes were saved.");
    }

    $validation_errors = [];
    $totals['styles_checked'] += saber_global_validate_styles($data, $validation_errors);
    if ($validation_errors) {
        WP_CLI::error(
            "Post {$post_id} still contains invalid styles after normalization: " .
            wp_json_encode($validation_errors, JSON_UNESCAPED_SLASHES)
        );
    }

    $change_count = count($changes['labels']) + count($changes['text_align']);
    if (!$change_count) {
        continue;
    }

    $prepared[$post_id] = [
        'raw' => $raw,
        'data' => $data,
        'path' => wp_make_link_relative(get_permalink($post_id)),
        'content_hash' => $content_hash_after,
        'changes' => $changes,
    ];
    $totals['documents_changed']++;
    $totals['labels_repaired'] += count($changes['labels']);
    $totals['text_align_repaired'] += count($changes['text_align']);
}

$backup_dir = '/tmp/elementor-global-validation-before-' . gmdate('Ymd-His');
if ($prepared && !wp_mkdir_p($backup_dir)) {
    WP_CLI::error('Could not create the pre-repair backup directory. No changes were saved.');
}

foreach ($prepared as $post_id => $document) {
    $backup_path = "{$backup_dir}/post-{$post_id}.json";
    if (file_put_contents($backup_path, $document['raw']) === false) {
        WP_CLI::error("Could not back up post {$post_id}. No changes were saved.");
    }
}

$updated_posts = [];
foreach ($prepared as $post_id => $document) {
    $encoded = wp_json_encode($document['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!$encoded || update_post_meta($post_id, '_elementor_data', wp_slash($encoded)) === false) {
        WP_CLI::error("Could not update post {$post_id}.");
    }

    update_post_meta($post_id, '_elementor_edit_mode', 'builder');
    clean_post_cache($post_id);
    $updated_posts[] = [
        'post_id' => $post_id,
        'path' => $document['path'],
        'labels_repaired' => count($document['changes']['labels']),
        'text_align_repaired' => count($document['changes']['text_align']),
        'content_hash' => $document['content_hash'],
    ];
}

if ($prepared && class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::log(wp_json_encode([
    'totals' => $totals,
    'backup_dir' => $prepared ? $backup_dir : null,
    'updated_posts' => $updated_posts,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success($prepared ? 'All Elementor style validation errors were repaired.' : 'No Elementor style validation errors found.');
