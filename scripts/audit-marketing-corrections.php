<?php
/**
 * Read-only inventory for the July 2026 marketing corrections.
 *
 * Run with:
 *   wp eval-file scripts/audit-marketing-corrections.php --allow-root
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$needles = [
    'vesl' => ['VESL'],
    'esol' => ['ESOL', 'English for Speakers of Other Languages'],
    'medical_assisting' => ['Medical Assisting', 'Medical Assistant'],
    'josefina_habif' => ['Josefina Habif'],
    'kirssys_fabre' => ['Kirssys Fabre', 'kfabre@sabercollege.edu'],
    'lucy_alvarez' => ['Lucy Alvarez', 'lalvarez@sabercollege.edu'],
    'dulce_estevez' => ['Dulce Estevez'],
    'marivi_rodriguez' => ['Marivi Rodriguez'],
    'claudio_bettinelli' => ['Claudio Bettinelli'],
    'placement_coordinator' => ['Placement Coordinator'],
    'karen_arocha' => ['Karen Arocha'],
    'karen_flynn' => ['Karen Flynn'],
    'gaspara_bard' => ['Gaspara Barditch', 'Gaspara Bardritch'],
    'year_2026_2027' => ['2026 - 2027', '2026-2027'],
    'designed_access_impact' => ['Designed for Access and Impact'],
];

function saber_find_matches($value, $needles) {
    if (!is_string($value) || $value === '') {
        return [];
    }

    $matches = [];
    foreach ($needles as $group => $variants) {
        foreach ($variants as $variant) {
            if (stripos($value, $variant) !== false) {
                $matches[$group][] = $variant;
            }
        }
    }

    return $matches;
}

function saber_collect_elementor_matches($elements, $needles, &$matches, $path = []) {
    foreach ((array) $elements as $index => $element) {
        if (!is_array($element)) {
            continue;
        }

        $element_path = array_merge($path, [$index]);
        $settings = isset($element['settings']) && is_array($element['settings']) ? $element['settings'] : [];

        foreach ($settings as $key => $value) {
            $encoded = is_scalar($value) ? (string) $value : wp_json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $found = saber_find_matches($encoded, $needles);
            if (!$found) {
                continue;
            }

            $matches[] = [
                'id' => $element['id'] ?? '',
                'type' => $element['widgetType'] ?? ($element['elType'] ?? ''),
                'path' => implode('.', $element_path),
                'setting' => $key,
                'matches' => $found,
                'value' => mb_substr(wp_strip_all_tags($encoded), 0, 700),
            ];
        }

        if (!empty($element['elements'])) {
            saber_collect_elementor_matches($element['elements'], $needles, $matches, $element_path);
        }
    }
}

$report = [
    'generated_at' => current_time('mysql'),
    'site_url' => home_url('/'),
    'posts' => [],
    'terms' => [],
    'menus' => [],
];

$posts = get_posts([
    'post_type' => get_post_types(['public' => true]),
    'post_status' => ['publish', 'draft', 'pending', 'private', 'trash'],
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
]);

$library = get_posts([
    'post_type' => 'elementor_library',
    'post_status' => ['publish', 'draft', 'pending', 'private', 'trash'],
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
]);

foreach (array_merge($posts, $library) as $post) {
    $base = implode("\n", [$post->post_title, $post->post_excerpt, $post->post_content]);
    $base_matches = saber_find_matches($base, $needles);
    $elementor_matches = [];
    $data = get_post_meta($post->ID, '_elementor_data', true);

    if (is_string($data) && $data !== '') {
        $decoded = json_decode($data, true);
        if (is_array($decoded)) {
            saber_collect_elementor_matches($decoded, $needles, $elementor_matches);
        }
    }

    if (!$base_matches && !$elementor_matches) {
        continue;
    }

    $report['posts'][] = [
        'id' => (int) $post->ID,
        'type' => $post->post_type,
        'status' => $post->post_status,
        'title' => html_entity_decode(get_the_title($post), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'path' => wp_make_link_relative(get_permalink($post)),
        'base_matches' => $base_matches,
        'elementor_matches' => $elementor_matches,
    ];
}

$terms = get_terms([
    'taxonomy' => ['category', 'post_tag'],
    'hide_empty' => false,
]);

if (!is_wp_error($terms)) {
    foreach ($terms as $term) {
        $found = saber_find_matches($term->name . "\n" . $term->slug . "\n" . $term->description, $needles);
        if ($found) {
            $report['terms'][] = [
                'id' => (int) $term->term_id,
                'taxonomy' => $term->taxonomy,
                'name' => $term->name,
                'slug' => $term->slug,
                'count' => (int) $term->count,
                'matches' => $found,
            ];
        }
    }
}

foreach (wp_get_nav_menus() as $menu) {
    $items = [];
    foreach ((array) wp_get_nav_menu_items($menu->term_id, ['post_status' => 'any']) as $item) {
        $value = implode("\n", [$item->title, $item->url, $item->description, $item->attr_title]);
        $found = saber_find_matches($value, $needles);
        if ($found) {
            $items[] = [
                'id' => (int) $item->ID,
                'title' => $item->title,
                'url' => $item->url,
                'status' => $item->post_status,
                'matches' => $found,
            ];
        }
    }

    if ($items) {
        $report['menus'][] = [
            'id' => (int) $menu->term_id,
            'name' => $menu->name,
            'items' => $items,
        ];
    }
}

echo wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
