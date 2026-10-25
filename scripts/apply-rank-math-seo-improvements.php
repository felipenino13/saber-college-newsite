<?php
/**
 * Apply the approved local Rank Math metadata and media-alt improvements.
 * Visible page copy and Elementor structures are protected by hash checks.
 *
 * Run with:
 * wp eval-file /tmp/apply-rank-math-seo-improvements.php --allow-root
 */

if (!defined('ABSPATH')) {
    exit(1);
}

$data_file = '/tmp/rank-math-indexable-optimization-20261018.json';
if (!is_readable($data_file)) {
    WP_CLI::error("Missing optimization data: {$data_file}");
}

$targets = json_decode((string) file_get_contents($data_file), true);
if (!is_array($targets) || count($targets) !== 36) {
    WP_CLI::error('Expected exactly 36 indexable page definitions.');
}

$noindex_ids = [1328, 1621, 1638, 1639, 1640, 1641, 2066];
$attachment_alts = [
    414  => 'Sabi, the SABER College mascot, dressed as a nurse',
    466  => 'Sabi, the SABER College mascot, promoting student support',
    1308 => 'SABER College graduates at a commencement ceremony',
    1699 => 'SABER College campus in Miami, Florida',
    458  => 'Council on Occupational Education logo',
    691  => 'Florida Commission for Independent Education logo',
    1678 => 'Students arriving at SABER College',
    632  => 'Professional Nursing student at SABER College',
    612  => 'Nursing student checking a patient’s blood pressure',
    613  => 'Nursing student providing patient care',
    609  => 'Professional Nursing student at SABER College',
    441  => 'Healthcare students assisting a patient during clinical training',
    1317 => 'Professional Nursing student at SABER College',
    608  => 'Physical Therapist Assistant student helping a patient exercise',
    606  => 'SABER College healthcare graduate outside the Miami campus',
    1700 => 'Nursing student holding a tablet with a SABER College information form',
];

$snapshot_rank_math = static function (int $post_id): array {
    $result = [];
    foreach (get_post_meta($post_id) as $key => $values) {
        if (!str_starts_with($key, 'rank_math')) {
            continue;
        }
        $result[$key] = array_map('maybe_unserialize', $values);
    }
    ksort($result);
    return $result;
};

$content_hashes = [];
$rank_math_before = [];
foreach (array_map('intval', array_keys($targets)) as $post_id) {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'page' || $post->post_status !== 'publish') {
        WP_CLI::error("Target {$post_id} is not a published page.");
    }
    $content_hashes[$post_id] = [
        'post_content' => hash('sha256', (string) $post->post_content),
        'elementor_data' => hash('sha256', (string) get_post_meta($post_id, '_elementor_data', true)),
    ];
    $rank_math_before[$post_id] = $snapshot_rank_math($post_id);
}

$noindex_before = [];
foreach ($noindex_ids as $post_id) {
    $robots = get_post_meta($post_id, 'rank_math_robots', true);
    if (!is_array($robots) || !in_array('noindex', $robots, true)) {
        WP_CLI::error("Protected page {$post_id} is expected to remain noindex.");
    }
    $noindex_before[$post_id] = $snapshot_rank_math($post_id);
}

$alt_before = [];
foreach ($attachment_alts as $attachment_id => $alt) {
    if (get_post_type($attachment_id) !== 'attachment') {
        WP_CLI::error("Media attachment {$attachment_id} does not exist.");
    }
    $alt_before[$attachment_id] = (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
}

$backup = '/tmp/rank-math-seo-before-' . gmdate('Ymd-His') . '.json';
$backup_payload = [
    'created_utc' => gmdate('c'),
    'scope' => 'local-only',
    'targets' => $rank_math_before,
    'protected_noindex' => $noindex_before,
    'attachment_alt' => $alt_before,
    'content_hashes' => $content_hashes,
];
if (false === file_put_contents($backup, wp_json_encode($backup_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) {
    WP_CLI::error('Could not create the pre-change backup.');
}

$changed_posts = 0;
foreach ($targets as $post_id_raw => $seo) {
    $post_id = (int) $post_id_raw;
    foreach (['keyword', 'title', 'description'] as $field) {
        if (!isset($seo[$field]) || !is_string($seo[$field]) || trim($seo[$field]) === '') {
            WP_CLI::error("Target {$post_id} has an invalid {$field} value.");
        }
    }

    $title_length = mb_strlen($seo['title']);
    $description_length = mb_strlen($seo['description']);
    if ($title_length < 30 || $title_length > 60 || $description_length < 120 || $description_length > 160) {
        WP_CLI::error("Target {$post_id} falls outside the approved title or description length.");
    }
    $primary_keyword = trim(explode(',', $seo['keyword'])[0]);
    if (stripos($seo['title'], $primary_keyword) === false || stripos($seo['description'], $primary_keyword) === false) {
        WP_CLI::error("Target {$post_id} does not include its focus keyword in both title and description.");
    }

    $desired = [
        'rank_math_focus_keyword' => $seo['keyword'],
        'rank_math_title' => $seo['title'],
        'rank_math_description' => $seo['description'],
        'rank_math_facebook_title' => $seo['title'],
        'rank_math_facebook_description' => $seo['description'],
        'rank_math_twitter_title' => $seo['title'],
        'rank_math_twitter_description' => $seo['description'],
    ];
    $post_changed = false;
    foreach ($desired as $meta_key => $value) {
        if ((string) get_post_meta($post_id, $meta_key, true) === $value) {
            continue;
        }
        update_post_meta($post_id, $meta_key, $value);
        $post_changed = true;
    }
    if ($post_changed) {
        $changed_posts++;
        clean_post_cache($post_id);
    }
}

$changed_attachments = 0;
foreach ($attachment_alts as $attachment_id => $alt) {
    if ((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true) === $alt) {
        continue;
    }
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
    clean_post_cache($attachment_id);
    $changed_attachments++;
}

foreach ($content_hashes as $post_id => $hashes) {
    $post = get_post($post_id);
    $current = [
        'post_content' => hash('sha256', (string) $post->post_content),
        'elementor_data' => hash('sha256', (string) get_post_meta($post_id, '_elementor_data', true)),
    ];
    if ($current !== $hashes) {
        WP_CLI::error("Visible content or Elementor data changed unexpectedly on page {$post_id}. Restore from {$backup}.");
    }
}

foreach ($noindex_before as $post_id => $snapshot) {
    if ($snapshot_rank_math($post_id) !== $snapshot) {
        WP_CLI::error("Protected noindex metadata changed unexpectedly on page {$post_id}. Restore from {$backup}.");
    }
}

$report = [
    'status' => $changed_posts || $changed_attachments ? 'applied' : 'already_applied',
    'backup' => $backup,
    'target_pages' => count($targets),
    'changed_pages' => $changed_posts,
    'changed_attachments' => $changed_attachments,
    'visible_content_unchanged' => true,
    'elementor_data_unchanged' => true,
    'protected_noindex_unchanged' => true,
    'schema_policy' => 'Keep Rank Math base WebPage/Organization/Breadcrumb schema; do not add unsupported specialized schema.',
];
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
