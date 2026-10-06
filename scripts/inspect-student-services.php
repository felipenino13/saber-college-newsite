<?php
/** Read-only inspection for the Student Services redesign. */
if (!defined('ABSPATH')) { exit(1); }
$page = get_post(34);
$source = json_decode(get_post_meta(34, '_elementor_data', true), true);
$widgets = [];
$walk = function ($nodes) use (&$walk, &$widgets) {
    foreach ($nodes as $node) {
        if (($node['elType'] ?? '') === 'widget') {
            $widgets[] = ['id' => $node['id'], 'type' => $node['widgetType'], 'settings' => $node['settings']];
        }
        $walk($node['elements'] ?? []);
    }
};
$walk($source);
WP_CLI::line(wp_json_encode(['page' => ['id' => 34, 'slug' => $page->post_name, 'modified' => $page->post_modified], 'widgets' => $widgets], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
$examples = [];
$find = function ($nodes) use (&$find, &$examples) {
    foreach ($nodes as $node) {
        foreach (($node['styles'] ?? []) as $style) {
            foreach (($style['variants'] ?? []) as $variant) {
                $props = $variant['props'] ?? [];
                if (isset($props['background']['value']['background-overlay']) || (isset($props['background']) && str_contains(wp_json_encode($props['background']), 'image'))) {
                    if (count($examples) < 4) { $examples[] = ['id' => $node['id'], 'props' => $props]; }
                }
            }
        }
        $find($node['elements'] ?? []);
    }
};
$find(json_decode(get_post_meta(7, '_elementor_data', true), true));
WP_CLI::line(wp_json_encode(['background_examples' => $examples, 'image1699' => wp_get_attachment_url(1699), 'elementor' => ELEMENTOR_VERSION], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
