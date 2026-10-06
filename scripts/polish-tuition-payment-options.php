<?php
/** Preserve the CSS escape used by the nested financial-aid checkmarks. */
if (!defined('ABSPATH')) { exit(1); }
$post_id = 885;
$settings = get_post_meta($post_id, '_elementor_page_settings', true) ?: [];
$css = $settings['custom_css'] ?? '';
$css = str_replace("content: '2713';", "content: '\\\\2713';", $css);
$settings['custom_css'] = $css;
update_post_meta($post_id, '_elementor_page_settings', $settings);
delete_post_meta($post_id, '_elementor_element_cache');
do_action('elementor/atomic-widgets/styles/clear', ['local', $post_id]);
Elementor\Core\Files\CSS\Post::create($post_id)->update();
clean_post_cache($post_id);
WP_CLI::success('Tuition Payment Options checkmark CSS updated.');
