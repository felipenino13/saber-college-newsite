<?php
/**
 * Import public SABER College post tags into the local WordPress database.
 *
 * Run through WP-CLI so WordPress creates terms and relationships using its
 * normal taxonomy APIs:
 *   wp eval-file /tmp/import-public-tags.php --allow-root
 */

if (!defined('ABSPATH')) {
    fwrite(STDERR, "This script must run inside WordPress.\n");
    exit(1);
}

$public_base = 'https://sabercollege.edu/wp-json/wp/v2';

/**
 * Fetch every page from a public WordPress REST collection.
 */
function saber_fetch_collection(string $url, array $query): array
{
    $items = [];
    $page = 1;

    do {
        $query['page'] = $page;
        $request_url = add_query_arg($query, $url);
        $response = wp_remote_get($request_url, [
            'timeout' => 30,
            'user-agent' => 'SABER College local migration audit',
        ]);

        if (is_wp_error($response)) {
            throw new RuntimeException($response->get_error_message());
        }

        $status = wp_remote_retrieve_response_code($response);
        if ($status !== 200) {
            throw new RuntimeException("Public REST request returned HTTP {$status}: {$request_url}");
        }

        $batch = json_decode(wp_remote_retrieve_body($response), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($batch)) {
            throw new RuntimeException("Unexpected REST response: {$request_url}");
        }

        $items = array_merge($items, $batch);
        $total_pages = max(1, (int) wp_remote_retrieve_header($response, 'x-wp-totalpages'));
        $page++;
    } while ($page <= $total_pages);

    return $items;
}

/**
 * Find a local post without losing legacy capitalization or known redirected slugs.
 */
function saber_find_local_post(string $public_slug): ?WP_Post
{
    global $wpdb;

    $redirected_slugs = [
        'registered-nurse-associate-in-applied-science' =>
            'registered-nurse-rn-associate-in-applied-science',
    ];
    $decoded_slug = rawurldecode($public_slug);
    $candidates = array_values(array_unique(array_filter([
        $public_slug,
        $decoded_slug,
        sanitize_title($decoded_slug),
        $redirected_slugs[strtolower($decoded_slug)] ?? null,
    ])));

    foreach ($candidates as $candidate) {
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE LOWER(post_name) = LOWER(%s) AND post_type = 'post'
             ORDER BY CASE post_status WHEN 'publish' THEN 0 ELSE 1 END, ID DESC
             LIMIT 1",
            $candidate
        ));

        if ($post_id) {
            $post = get_post((int) $post_id);
            if ($post instanceof WP_Post) {
                return $post;
            }
        }
    }

    return null;
}

$report = [
    'source' => 'https://sabercollege.edu/',
    'run_at' => gmdate('c'),
    'public_tags' => 0,
    'public_posts' => 0,
    'terms_created' => 0,
    'terms_existing' => 0,
    'terms_failed' => [],
    'posts_matched' => 0,
    'posts_unmatched' => [],
    'posts_tagged' => 0,
    'relationships_assigned' => 0,
    'assignment_errors' => [],
];

try {
    $public_tags = saber_fetch_collection("{$public_base}/tags", [
        'per_page' => 100,
        'hide_empty' => 'false',
        '_fields' => 'id,name,slug,count',
    ]);
    $public_posts = saber_fetch_collection("{$public_base}/posts", [
        'per_page' => 100,
        'status' => 'publish',
        '_fields' => 'id,slug,title,tags',
    ]);

    $report['public_tags'] = count($public_tags);
    $report['public_posts'] = count($public_posts);
    $term_map = [];

    foreach ($public_tags as $public_tag) {
        $slug = sanitize_title((string) $public_tag['slug']);
        $name = html_entity_decode((string) $public_tag['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $existing = get_term_by('slug', $slug, 'post_tag');

        if ($existing instanceof WP_Term) {
            $term_map[(int) $public_tag['id']] = (int) $existing->term_id;
            $report['terms_existing']++;
            continue;
        }

        $created = wp_insert_term($name, 'post_tag', ['slug' => $slug]);
        if (is_wp_error($created)) {
            $report['terms_failed'][] = [
                'public_id' => (int) $public_tag['id'],
                'slug' => $slug,
                'error' => $created->get_error_message(),
            ];
            continue;
        }

        $term_map[(int) $public_tag['id']] = (int) $created['term_id'];
        $report['terms_created']++;
    }

    foreach ($public_posts as $public_post) {
        $slug = (string) $public_post['slug'];
        $local_post = saber_find_local_post($slug);

        if (!$local_post instanceof WP_Post) {
            $report['posts_unmatched'][] = [
                'public_id' => (int) $public_post['id'],
                'slug' => $slug,
                'title' => html_entity_decode(
                    (string) ($public_post['title']['rendered'] ?? ''),
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                ),
            ];
            continue;
        }

        $report['posts_matched']++;
        $public_tag_ids = array_map('intval', (array) ($public_post['tags'] ?? []));
        $local_term_ids = [];

        foreach ($public_tag_ids as $public_tag_id) {
            if (isset($term_map[$public_tag_id])) {
                $local_term_ids[] = $term_map[$public_tag_id];
            }
        }

        $assigned = wp_set_post_terms((int) $local_post->ID, $local_term_ids, 'post_tag', false);
        if (is_wp_error($assigned)) {
            $report['assignment_errors'][] = [
                'local_post_id' => (int) $local_post->ID,
                'slug' => $slug,
                'error' => $assigned->get_error_message(),
            ];
            continue;
        }

        if ($local_term_ids !== []) {
            $report['posts_tagged']++;
            $report['relationships_assigned'] += count($local_term_ids);
        }
    }

    echo wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
} catch (Throwable $error) {
    $report['fatal_error'] = $error->getMessage();
    echo wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    exit(1);
}
