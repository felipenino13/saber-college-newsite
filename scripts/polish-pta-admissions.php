<?php
/** Apply the post-review icon spacing adjustment to PTA Admissions. */
if (!defined('ABSPATH')) { exit(1); }
$post_id = 881;
$settings = get_post_meta($post_id, '_elementor_page_settings', true) ?: [];
$css = $settings['custom_css'] ?? '';
$marker = '/* PTA closing icon spacing. */';
if (str_contains($css, $marker)) { $css = strstr($css, $marker, true); }
$settings['custom_css'] = rtrim($css) . <<<'CSS'

/* PTA closing icon spacing. */
.elementor-881 .e-pta-closing-copy .elementor-widget-icon { flex: 0 0 30px; width: 30px; }
.elementor-881 .e-pta-closing-copy .elementor-widget-text-editor { flex: 1 1 auto; min-width: 0; }
CSS;
update_post_meta($post_id, '_elementor_page_settings', $settings);
delete_post_meta($post_id, '_elementor_element_cache');
do_action('elementor/atomic-widgets/styles/clear', ['local', $post_id]);
Elementor\Core\Files\CSS\Post::create($post_id)->update();
clean_post_cache($post_id);
WP_CLI::success('PTA Admissions closing icon spacing updated.');
