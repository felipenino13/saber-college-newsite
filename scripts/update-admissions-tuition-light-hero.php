<?php
/**
 * Apply the SABER Path Light background to the Admissions and Tuition hero.
 *
 * Run with:
 *   wp eval-file /tmp/update-admissions-tuition-light-hero.php --allow-root
 */

if (!defined('ABSPATH') || !class_exists('WP_CLI')) {
    exit("Run this script through WP-CLI.\n");
}

$post_id = 1225;
$hero_id = 'athero1';
$background_attachment_id = 2064;
$expected_path = '/admissions-and-tuition/';

if (wp_make_link_relative(get_permalink($post_id)) !== $expected_path) {
    WP_CLI::error("Page {$post_id} is not {$expected_path}.");
}

$background_url = wp_get_attachment_url($background_attachment_id);
if (!$background_url || strpos($background_url, 'saber-path-light-v02.svg') === false) {
    WP_CLI::error("Attachment {$background_attachment_id} is not the expected Path Light asset.");
}

$typed = static fn(string $type, $value): array => ['$$type' => $type, 'value' => $value];
$light_background = static function (int $attachment_id) use ($typed): array {
    return $typed('background', [
        'color' => $typed('color', '#f3f9ff'),
        'background-overlay' => $typed('background-overlay', [
            $typed('background-image-overlay', [
                'image' => $typed('image', [
                    'src' => $typed('image-src', [
                        'id' => $typed('image-attachment-id', $attachment_id),
                        'url' => null,
                    ]),
                    'size' => $typed('string', 'full'),
                ]),
                'repeat' => $typed('string', 'no-repeat'),
                'size' => $typed('string', 'cover'),
                'position' => $typed('string', 'center center'),
            ]),
        ]),
    ]);
};

function &saber_admissions_find_element(array &$elements, string $element_id)
{
    foreach ($elements as &$element) {
        if (($element['id'] ?? '') === $element_id) {
            return $element;
        }

        if (!empty($element['elements']) && is_array($element['elements'])) {
            $found = &saber_admissions_find_element($element['elements'], $element_id);
            if ($found !== null) {
                return $found;
            }
        }
    }

    static $not_found = null;
    return $not_found;
}

function saber_admissions_collect_content(array $elements, array &$content): void
{
    foreach ($elements as $element) {
        foreach (['title', 'editor', 'text', 'button_text', 'description', 'caption'] as $key) {
            if (array_key_exists($key, $element['settings'] ?? [])) {
                $content[] = [$element['id'] ?? '', $key, $element['settings'][$key]];
            }
        }

        if (!empty($element['elements']) && is_array($element['elements'])) {
            saber_admissions_collect_content($element['elements'], $content);
        }
    }
}

$raw_data = get_post_meta($post_id, '_elementor_data', true);
$elements = json_decode($raw_data, true);
if (!is_array($elements) || json_last_error() !== JSON_ERROR_NONE) {
    WP_CLI::error('The page has invalid Elementor data.');
}

$content_before = [];
saber_admissions_collect_content($elements, $content_before);
$content_hash_before = hash('sha256', wp_json_encode($content_before, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

$hero = &saber_admissions_find_element($elements, $hero_id);
if ($hero === null || empty($hero['styles']) || !is_array($hero['styles'])) {
    WP_CLI::error("Hero {$hero_id} or its styles were not found.");
}

$backgrounds_updated = 0;
foreach ($hero['styles'] as &$style) {
    if (empty($style['variants']) || !is_array($style['variants'])) {
        continue;
    }

    foreach ($style['variants'] as &$variant) {
        $is_desktop = ($variant['meta']['breakpoint'] ?? '') === 'desktop';
        $is_default_state = ($variant['meta']['state'] ?? null) === null;
        if ($is_desktop && $is_default_state && array_key_exists('background', $variant['props'] ?? [])) {
            $variant['props']['background'] = $light_background($background_attachment_id);
            $backgrounds_updated++;
        }
    }
}

if ($backgrounds_updated !== 1) {
    WP_CLI::error("Expected one desktop hero background; found {$backgrounds_updated}.");
}

$color_updates = [
    '14848f2' => ['title_color', '#001a39'],
    '6247bbb' => ['text_color', '#25377b'],
    '83aeafb' => ['text_color', '#29435f'],
    '045ff1c' => ['text_color', '#29435f'],
];

foreach ($color_updates as $element_id => [$setting, $color]) {
    $widget = &saber_admissions_find_element($hero['elements'], $element_id);
    if ($widget === null) {
        WP_CLI::error("Expected hero widget {$element_id} was not found.");
    }
    $widget['settings'][$setting] = $color;
}

$content_after = [];
saber_admissions_collect_content($elements, $content_after);
$content_hash_after = hash('sha256', wp_json_encode($content_after, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
if (!hash_equals($content_hash_before, $content_hash_after)) {
    WP_CLI::error('Page content changed unexpectedly.');
}

$encoded = wp_json_encode($elements, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if (!$encoded) {
    WP_CLI::error('Could not encode the updated Elementor data.');
}

update_post_meta($post_id, '_elementor_data', wp_slash($encoded));

$saved = json_decode(get_post_meta($post_id, '_elementor_data', true), true);
$saved_hero = &saber_admissions_find_element($saved, $hero_id);
$saved_json = wp_json_encode($saved_hero, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if (substr_count($saved_json, '"value":2064') !== 1 || strpos($saved_json, '"value":"#f3f9ff"') === false) {
    WP_CLI::error('The saved Path Light background could not be verified.');
}

foreach ($color_updates as $element_id => [$setting, $color]) {
    $saved_widget = &saber_admissions_find_element($saved_hero['elements'], $element_id);
    if (($saved_widget['settings'][$setting] ?? '') !== $color) {
        WP_CLI::error("Color verification failed for widget {$element_id}.");
    }
}

if (class_exists('\\Elementor\\Plugin')) {
    \Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success('Admissions and Tuition now uses the light design-system hero background.');
WP_CLI::log(wp_json_encode([
    'post_id' => $post_id,
    'path' => $expected_path,
    'hero_id' => $hero_id,
    'background_attachment_id' => $background_attachment_id,
    'content_hash' => $content_hash_after,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
