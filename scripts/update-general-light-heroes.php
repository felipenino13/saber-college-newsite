<?php
/**
 * Align selected institutional page heroes with the SABER light design-system background.
 *
 * Run with:
 *   wp eval-file /tmp/update-general-light-heroes.php --allow-root
 */

if (!defined('ABSPATH') || !class_exists('WP_CLI')) {
    exit("Run this script through WP-CLI.\n");
}

$background_attachment_id = 2064;
$targets = [
    913  => ['path' => '/general/manuals/', 'hero_id' => 'mnlhero'],
    915  => ['path' => '/general/graduation-requirements/', 'hero_id' => 'grhero1'],
    917  => ['path' => '/general/gainful-employment-programs/', 'hero_id' => 'gehero1'],
    919  => ['path' => '/general/saber-college-faculty/', 'hero_id' => 'fachero'],
    921  => ['path' => '/general/saber-college-distance-education/', 'hero_id' => 'dehero1'],
    923  => ['path' => '/general/heerf-quarterly-reports/', 'hero_id' => 'hrhero1'],
    925  => ['path' => '/general/institutional-plans/', 'hero_id' => 'iphero1'],
    1633 => ['path' => '/general/safety-plan/', 'hero_id' => 'sphero1'],
    1628 => ['path' => '/45-day-report-on-ed-grants/', 'hero_id' => 'cahero1'],
];

if (get_post_type($background_attachment_id) !== 'attachment') {
    WP_CLI::error("Background attachment {$background_attachment_id} does not exist.");
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

function &saber_find_element_by_id(array &$elements, string $element_id)
{
    foreach ($elements as &$element) {
        if (($element['id'] ?? '') === $element_id) {
            return $element;
        }

        if (!empty($element['elements']) && is_array($element['elements'])) {
            $found = &saber_find_element_by_id($element['elements'], $element_id);
            if ($found !== null) {
                return $found;
            }
        }
    }

    static $not_found = null;
    return $not_found;
}

function saber_collect_content_values(array $elements, array &$content): void
{
    $content_keys = ['title', 'editor', 'text', 'button_text', 'description', 'caption'];

    foreach ($elements as $element) {
        foreach ($content_keys as $key) {
            if (array_key_exists($key, $element['settings'] ?? [])) {
                $content[] = [$element['id'] ?? '', $key, $element['settings'][$key]];
            }
        }

        if (!empty($element['elements']) && is_array($element['elements'])) {
            saber_collect_content_values($element['elements'], $content);
        }
    }
}

function saber_update_white_hero_headings(array &$elements, int &$compatible_headings): int
{
    $updated = 0;

    foreach ($elements as &$element) {
        if (($element['widgetType'] ?? '') === 'heading') {
            $current_color = strtolower((string) ($element['settings']['title_color'] ?? ''));
            if (in_array($current_color, ['#fff', '#ffffff'], true)) {
                $element['settings']['title_color'] = '#001a39';
                $updated++;
                $current_color = '#001a39';
            }

            if ($current_color === '#001a39') {
                $compatible_headings++;
            }
        }

        if (!empty($element['elements']) && is_array($element['elements'])) {
            $updated += saber_update_white_hero_headings($element['elements'], $compatible_headings);
        }
    }

    return $updated;
}

$results = [];

foreach ($targets as $post_id => $target) {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'page') {
        WP_CLI::error("Expected page {$post_id} was not found.");
    }

    $raw_data = get_post_meta($post_id, '_elementor_data', true);
    $elements = json_decode($raw_data, true);
    if (!is_array($elements) || json_last_error() !== JSON_ERROR_NONE) {
        WP_CLI::error("Page {$post_id} has invalid Elementor data.");
    }

    $content_before = [];
    saber_collect_content_values($elements, $content_before);
    $content_hash_before = hash('sha256', wp_json_encode($content_before, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

    $hero = &saber_find_element_by_id($elements, $target['hero_id']);
    if ($hero === null) {
        WP_CLI::error("Hero {$target['hero_id']} was not found on {$target['path']}.");
    }

    $background_variants_updated = 0;
    if (empty($hero['styles']) || !is_array($hero['styles'])) {
        WP_CLI::error("Hero styles were not found on {$target['path']}.");
    }

    foreach ($hero['styles'] as &$style) {
        if (empty($style['variants']) || !is_array($style['variants'])) {
            continue;
        }

        foreach ($style['variants'] as &$variant) {
            $is_desktop = ($variant['meta']['breakpoint'] ?? '') === 'desktop';
            $is_default_state = ($variant['meta']['state'] ?? null) === null;
            if ($is_desktop && $is_default_state && array_key_exists('background', $variant['props'] ?? [])) {
                $variant['props']['background'] = $light_background($background_attachment_id);
                $background_variants_updated++;
            }
        }
    }

    if ($background_variants_updated !== 1) {
        WP_CLI::error(
            "Expected one desktop background on {$target['path']}; found {$background_variants_updated}."
        );
    }

    $compatible_headings = 0;
    $headings_updated = saber_update_white_hero_headings($hero['elements'], $compatible_headings);
    if ($compatible_headings < 1) {
        WP_CLI::error("No compatible hero heading was found on {$target['path']}.");
    }

    $content_after = [];
    saber_collect_content_values($elements, $content_after);
    $content_hash_after = hash('sha256', wp_json_encode($content_after, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    if (!hash_equals($content_hash_before, $content_hash_after)) {
        WP_CLI::error("Content changed unexpectedly on {$target['path']}.");
    }

    $encoded = wp_json_encode($elements, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (!$encoded) {
        WP_CLI::error("Could not encode Elementor data for {$target['path']}.");
    }

    update_post_meta($post_id, '_elementor_data', wp_slash($encoded));

    $saved = json_decode(get_post_meta($post_id, '_elementor_data', true), true);
    $saved_hero = &saber_find_element_by_id($saved, $target['hero_id']);
    $saved_json = wp_json_encode($saved_hero, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (strpos($saved_json, '"value":2064') === false || strpos($saved_json, '"value":"#f3f9ff"') === false) {
        WP_CLI::error("Background verification failed on {$target['path']}.");
    }

    $results[] = [
        'post_id' => $post_id,
        'path' => $target['path'],
        'hero_id' => $target['hero_id'],
        'headings_updated' => $headings_updated,
        'compatible_headings' => $compatible_headings,
        'content_hash' => $content_hash_after,
    ];
}

if (class_exists('\\Elementor\\Plugin')) {
    \Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success('Updated nine light hero backgrounds without changing page content.');
WP_CLI::log(wp_json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
