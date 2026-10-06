<?php
/** Replace only the legacy feature image in the Visa I-20 Elementor page. */

if (!defined('WP_CLI') || !WP_CLI) {
    exit("Run this file with WP-CLI.\n");
}

$page_id = 898;
$old_image_marker = 'study-in-the-United-States';
$new_attachment_id = (int) getenv('SABER_NEW_ATTACHMENT_ID');
$raw = get_post_meta($page_id, '_elementor_data', true);
$data = json_decode($raw, true);

if (!is_array($data)) {
    WP_CLI::error('The Visa I-20 page has invalid Elementor data.');
}

$matches = [];

function saber_replace_visa_image(array &$node, string $path, string $marker, int $attachment_id, array &$matches): void
{
    $url = isset($node['url']) && is_string($node['url']) ? $node['url'] : '';

    if ($url !== '' && stripos($url, $marker) !== false) {
        $matches[] = [
            'path' => $path,
            'old_id' => $node['id'] ?? null,
            'old_url' => $url,
        ];

        if ($attachment_id > 0) {
            $new_url = wp_get_attachment_url($attachment_id);
            if (!$new_url) {
                WP_CLI::error('The new attachment URL could not be resolved.');
            }

            $node['id'] = $attachment_id;
            $node['url'] = $new_url;
        }
    }

    foreach ($node as $key => &$value) {
        if (is_array($value)) {
            saber_replace_visa_image($value, $path . '/' . (string) $key, $marker, $attachment_id, $matches);
        }
    }
    unset($value);
}

saber_replace_visa_image($data, 'root', $old_image_marker, $new_attachment_id, $matches);

if (count($matches) !== 1) {
    WP_CLI::error('Expected exactly one legacy Visa I-20 image reference; found ' . count($matches) . '.');
}

if ($new_attachment_id <= 0) {
    WP_CLI::line(wp_json_encode(['page_id' => $page_id, 'matches' => $matches], JSON_PRETTY_PRINT));
    exit(0);
}

if (get_post_type($new_attachment_id) !== 'attachment' || !wp_attachment_is_image($new_attachment_id)) {
    WP_CLI::error('SABER_NEW_ATTACHMENT_ID must point to an image attachment.');
}

$backup = '/tmp/visa-i20-elementor-before-image-' . gmdate('Ymd-His') . '.json';
if (file_put_contents($backup, $raw) === false) {
    WP_CLI::error('Could not create the Elementor JSON backup.');
}

$encoded = wp_json_encode($data);
if (!$encoded || update_post_meta($page_id, '_elementor_data', wp_slash($encoded)) === false) {
    WP_CLI::error('Could not update the Visa I-20 Elementor data.');
}

update_post_meta(
    $new_attachment_id,
    '_wp_attachment_image_alt',
    'International students walking together at SABER College in Miami'
);

if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

$saved = get_post_meta($page_id, '_elementor_data', true);
$new_url = wp_get_attachment_url($new_attachment_id);
$new_file = wp_basename((string) wp_parse_url($new_url, PHP_URL_PATH));

if (stripos($saved, $old_image_marker) !== false || stripos($saved, $new_file) === false) {
    WP_CLI::error('The saved Elementor image replacement could not be verified.');
}

WP_CLI::line(wp_json_encode([
    'page_id' => $page_id,
    'attachment_id' => $new_attachment_id,
    'new_url' => $new_url,
    'backup' => $backup,
    'replaced' => $matches,
], JSON_PRETTY_PRINT));
WP_CLI::success('Visa I-20 image replaced without changing page text.');
