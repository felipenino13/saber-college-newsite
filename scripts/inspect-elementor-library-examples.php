<?php
/** Read-only: find representative Elementor widget settings for the component library. */
if (!defined('ABSPATH')) { exit(1); }
$wanted = ['button', 'form', 'accordion', 'icon-box', 'image-box'];
$found = [];
$posts = get_posts([
    'post_type' => ['page', 'elementor_library'], 'post_status' => ['publish', 'draft', 'private'],
    'posts_per_page' => -1, 'fields' => 'ids',
]);
$walk = function ($nodes, $post_id) use (&$walk, &$found, $wanted) {
    foreach ($nodes as $node) {
        $type = $node['widgetType'] ?? '';
        if ($type && in_array($type, $wanted, true) && !isset($found[$type])) {
            $found[$type] = ['post_id' => $post_id, 'id' => $node['id'], 'settings' => $node['settings'] ?? []];
        }
        $walk($node['elements'] ?? [], $post_id);
    }
};
foreach ($posts as $post_id) {
    $data = json_decode(get_post_meta($post_id, '_elementor_data', true), true);
    if (is_array($data)) { $walk($data, $post_id); }
    if (count($found) === count($wanted)) { break; }
}
WP_CLI::line(wp_json_encode($found, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
