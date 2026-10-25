<?php
/**
 * Read-only export of Rank Math data for all published WordPress pages.
 * Run with: wp eval-file /tmp/export-rank-math-inventory.php --allow-root
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$pages = get_posts([
    'post_type' => 'page',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
]);

$result = [
    'generated_at' => gmdate('c'),
    'site_url' => home_url('/'),
    'page_count' => count($pages),
    'pages' => [],
];

foreach ($pages as $page) {
    $all_meta = get_post_meta($page->ID);
    $rank_math_meta = [];
    foreach ($all_meta as $key => $values) {
        if (str_starts_with($key, 'rank_math')) {
            $rank_math_meta[$key] = count($values) === 1 ? maybe_unserialize($values[0]) : array_map('maybe_unserialize', $values);
        }
    }

    $robots = get_post_meta($page->ID, 'rank_math_robots', true);
    if (!is_array($robots)) {
        $robots = $robots ? [$robots] : [];
    }

    $result['pages'][] = [
        'id' => (int) $page->ID,
        'title' => html_entity_decode(get_the_title($page), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'slug' => $page->post_name,
        'parent_id' => (int) $page->post_parent,
        'url' => get_permalink($page),
        'modified_gmt' => get_post_modified_time('c', true, $page),
        'focus_keyword' => (string) get_post_meta($page->ID, 'rank_math_focus_keyword', true),
        'seo_score' => get_post_meta($page->ID, 'rank_math_seo_score', true),
        'seo_title' => (string) get_post_meta($page->ID, 'rank_math_title', true),
        'seo_description' => (string) get_post_meta($page->ID, 'rank_math_description', true),
        'robots' => $robots,
        'canonical' => (string) get_post_meta($page->ID, 'rank_math_canonical_url', true),
        'schema_type' => (string) get_post_meta($page->ID, 'rank_math_rich_snippet', true),
        'rank_math_meta' => $rank_math_meta,
    ];
}

WP_CLI::line(wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
