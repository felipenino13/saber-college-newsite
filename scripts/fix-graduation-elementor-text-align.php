<?php
/** Repair the invalid mobile text-align value that blocks Elementor saves. */

if (!defined('ABSPATH') || !class_exists('WP_CLI')) {
    exit("Run this script through WP-CLI.\n");
}

$post_id = 915;
$element_id = 'grcoreh';
$style_id = 'e-gr-core-heading';
$raw = get_post_meta($post_id, '_elementor_data', true);
$data = json_decode($raw, true);

if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
    WP_CLI::error('Invalid Elementor data.');
}

$backup = '/tmp/graduation-requirements-915-before-text-align-fix-' . gmdate('Ymd-His') . '.json';
if (file_put_contents($backup, $raw) === false) {
    WP_CLI::error('Could not create the pre-repair Elementor backup.');
}

function &saber_graduation_find_element(array &$elements, string $target_id)
{
    foreach ($elements as &$element) {
        if (($element['id'] ?? '') === $target_id) {
            return $element;
        }

        if (!empty($element['elements']) && is_array($element['elements'])) {
            $found = &saber_graduation_find_element($element['elements'], $target_id);
            if ($found !== null) {
                return $found;
            }
        }
    }

    static $not_found = null;
    return $not_found;
}

function saber_graduation_collect_content(array $elements, array &$content): void
{
    foreach ($elements as $element) {
        foreach (['title', 'editor', 'text', 'button_text', 'description', 'caption'] as $key) {
            if (array_key_exists($key, $element['settings'] ?? [])) {
                $content[] = [$element['id'] ?? '', $key, $element['settings'][$key]];
            }
        }

        if (!empty($element['elements']) && is_array($element['elements'])) {
            saber_graduation_collect_content($element['elements'], $content);
        }
    }
}

$content_before = [];
saber_graduation_collect_content($data, $content_before);
$content_hash_before = hash('sha256', wp_json_encode($content_before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

$element = &saber_graduation_find_element($data, $element_id);
if ($element === null || !isset($element['styles'][$style_id]['variants'])) {
    WP_CLI::error("Element {$element_id} or style {$style_id} was not found.");
}

$matches = 0;
$changed = 0;
foreach ($element['styles'][$style_id]['variants'] as &$variant) {
    $is_mobile = ($variant['meta']['breakpoint'] ?? '') === 'mobile';
    $is_default_state = ($variant['meta']['state'] ?? null) === null;
    if (!$is_mobile || !$is_default_state || !isset($variant['props']['text-align'])) {
        continue;
    }

    $matches++;
    $current = $variant['props']['text-align']['value'] ?? null;
    if (!in_array($current, ['left', 'start'], true)) {
        WP_CLI::error("Unexpected mobile text-align value: " . wp_json_encode($current));
    }

    if ($current !== 'start') {
        $variant['props']['text-align']['value'] = 'start';
        $changed++;
    }
}
unset($variant);

if ($matches !== 1) {
    WP_CLI::error("Expected one mobile text-align property; found {$matches}.");
}

$content_after = [];
saber_graduation_collect_content($data, $content_after);
$content_hash_after = hash('sha256', wp_json_encode($content_after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
if (!hash_equals($content_hash_before, $content_hash_after)) {
    WP_CLI::error('Page content changed unexpectedly.');
}

if ($changed) {
    $encoded = wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!$encoded || update_post_meta($post_id, '_elementor_data', wp_slash($encoded)) === false) {
        WP_CLI::error('Elementor data could not be updated.');
    }

    update_post_meta($post_id, '_elementor_edit_mode', 'builder');
    if (class_exists('Elementor\\Plugin')) {
        Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    clean_post_cache($post_id);
}

$saved = json_decode(get_post_meta($post_id, '_elementor_data', true), true);
$saved_element = &saber_graduation_find_element($saved, $element_id);
$saved_value = $saved_element['styles'][$style_id]['variants'][1]['props']['text-align']['value'] ?? null;
if ($saved_value !== 'start') {
    WP_CLI::error('The repaired value was not persisted.');
}

WP_CLI::log(wp_json_encode([
    'page_id' => $post_id,
    'element_id' => $element_id,
    'style_id' => $style_id,
    'before' => $changed ? 'left' : 'start',
    'after' => $saved_value,
    'backup' => $backup,
    'content_hash' => $content_hash_after,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success($changed ? 'Invalid Elementor text-align repaired.' : 'Elementor text-align was already valid.');
