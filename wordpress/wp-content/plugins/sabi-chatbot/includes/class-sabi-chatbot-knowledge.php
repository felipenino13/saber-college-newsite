<?php

namespace SaberCollege\SabiChatbot;

if (!defined('ABSPATH')) {
    exit;
}

final class Knowledge
{
    private const INDEX_OPTION = 'sabi_chatbot_knowledge_index_hash';
    private const SOURCES_OPTION = 'sabi_chatbot_knowledge_sources_hash';
    private const INDEX_FILE = 'knowledge/index.json';

    public static function maybe_import(): void
    {
        $path = SABI_CHATBOT_PATH . self::INDEX_FILE;
        $sources_hash = self::definitions_hash();
        if (!is_file($path)) {
            if (get_option(self::SOURCES_OPTION) !== $sources_hash) {
                self::sync_sources();
                update_option(self::SOURCES_OPTION, $sources_hash, false);
            }
            return;
        }

        $hash = hash_file('sha256', $path);
        if (is_string($hash) && (get_option(self::INDEX_OPTION) !== $hash || get_option(self::SOURCES_OPTION) !== $sources_hash)) {
            self::rebuild();
        }
    }

    /**
     * @return array<string, int>|\WP_Error
     */
    public static function rebuild(): array|\WP_Error
    {
        global $wpdb;

        self::sync_sources();
        $path = SABI_CHATBOT_PATH . self::INDEX_FILE;
        if (!is_file($path)) {
            return new \WP_Error('sabi_knowledge_missing', __('The bundled knowledge index is missing.', 'sabi-chatbot'));
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded) || !isset($decoded['sources']) || !is_array($decoded['sources'])) {
            return new \WP_Error('sabi_knowledge_invalid', __('The bundled knowledge index is invalid.', 'sabi-chatbot'));
        }

        $wpdb->query('START TRANSACTION');
        try {
            $pdf_sources = 0;
            $web_sources = 0;
            $chunks = 0;
            foreach ($decoded['sources'] as $source) {
                if (!is_array($source) || empty($source['source_key']) || !isset($source['chunks']) || !is_array($source['chunks'])) {
                    continue;
                }
                $source_id = self::source_id(sanitize_key($source['source_key']));
                if ($source_id === 0) {
                    continue;
                }
                $inserted = self::replace_chunks($source_id, $source['chunks']);
                $wpdb->update(
                    Database::sources_table(),
                    [
                        'status' => 'indexed',
                        'checksum' => sanitize_text_field($source['checksum'] ?? ''),
                        'page_count' => absint($source['page_count'] ?? 0),
                        'chunk_count' => $inserted,
                        'indexed_at' => current_time('mysql', true),
                        'notes' => '',
                    ],
                    ['id' => $source_id],
                    ['%s', '%s', '%d', '%d', '%s', '%s'],
                    ['%d']
                );
                $pdf_sources++;
                $chunks += $inserted;
            }

            foreach (self::definitions() as $definition) {
                if (($definition['type'] ?? '') !== 'webpage') {
                    continue;
                }
                $result = self::index_webpage($definition);
                if ($result > 0) {
                    $web_sources++;
                    $chunks += $result;
                }
            }

            $wpdb->query('COMMIT');
            $hash = hash_file('sha256', $path);
            if (is_string($hash)) {
                update_option(self::INDEX_OPTION, $hash, false);
            }
            update_option(self::SOURCES_OPTION, self::definitions_hash(), false);

            return [
                'pdf_sources' => $pdf_sources,
                'web_sources' => $web_sources,
                'chunks' => $chunks,
            ];
        } catch (\Throwable $error) {
            $wpdb->query('ROLLBACK');

            return new \WP_Error('sabi_knowledge_import_failed', $error->getMessage());
        }
    }

    public static function sync_sources(): void
    {
        global $wpdb;

        foreach (self::definitions() as $source) {
            $source_key = sanitize_key($source['source_key'] ?? '');
            if ($source_key === '') {
                continue;
            }

            $data = [
                'source_key' => $source_key,
                'label' => sanitize_text_field($source['label'] ?? ''),
                'type' => sanitize_key($source['type'] ?? 'webpage'),
                'url' => esc_url_raw($source['url'] ?? ''),
                'priority' => max(1, absint($source['priority'] ?? 10)),
                'scope' => sanitize_key($source['scope'] ?? 'institution'),
                'is_current' => !empty($source['is_current']) ? 1 : 0,
                'reviewed_at' => self::date_value($source['reviewed_at'] ?? ''),
            ];
            $source_id = self::source_id($source_key);
            if ($source_id > 0) {
                $wpdb->update(
                    Database::sources_table(),
                    $data,
                    ['id' => $source_id],
                    ['%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s'],
                    ['%d']
                );
            } else {
                $data['status'] = 'registered';
                $wpdb->insert(
                    Database::sources_table(),
                    $data,
                    ['%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s']
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function sources(): array
    {
        global $wpdb;

        $table = Database::sources_table();
        $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY priority ASC, label ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array{sources: int, indexed_sources: int, current_sources: int, chunks: int, pages: int}
     */
    public static function stats(): array
    {
        global $wpdb;

        $sources = Database::sources_table();
        $chunks = Database::chunks_table();

        return [
            'sources' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$sources}"), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            'indexed_sources' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$sources} WHERE status = 'indexed'"), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            'current_sources' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$sources} WHERE is_current = 1"), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            'chunks' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$chunks}"), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            'pages' => (int) $wpdb->get_var("SELECT SUM(page_count) FROM {$sources}"), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function search(string $query, int $limit = 8, bool $include_content = false): array
    {
        global $wpdb;

        $query = sanitize_text_field($query);
        $tokens = self::search_tokens($query);
        if (!$tokens) {
            return [];
        }

        $conditions = [];
        $arguments = [];
        foreach ($tokens as $token) {
            $like = '%' . $wpdb->esc_like($token) . '%';
            $conditions[] = '(c.heading LIKE %s OR c.content LIKE %s)';
            $arguments[] = $like;
            $arguments[] = $like;
        }
        $arguments[] = 80;
        $sources = Database::sources_table();
        $chunks = Database::chunks_table();
        $sql = "SELECT c.id, c.page_number, c.heading, c.content, s.source_key, s.label AS source_label, s.url, s.type, s.scope, s.priority
            FROM {$chunks} c
            INNER JOIN {$sources} s ON s.id = c.source_id
            WHERE s.is_current = 1 AND s.status = 'indexed' AND (" . implode(' OR ', $conditions) . ')
            ORDER BY s.priority ASC, c.id ASC LIMIT %d';
        $rows = $wpdb->get_results($wpdb->prepare($sql, ...$arguments), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if (!is_array($rows)) {
            return [];
        }

        $normalized_query = self::normalize($query);
        foreach ($rows as &$row) {
            $heading = self::normalize($row['heading']);
            $content = self::normalize($row['content']);
            $score = str_contains($content, $normalized_query) ? 20 : 0;
            foreach ($tokens as $token) {
                $score += substr_count($heading, $token) * 5;
                $score += min(8, substr_count($content, $token));
            }
            $score += max(0, 6 - (int) $row['priority']) / 10;
            $row['score'] = $score;
            $row['snippet'] = self::snippet($row['content'], $tokens);
            if (!$include_content) {
                unset($row['content']);
            }
        }
        unset($row);

        usort($rows, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_slice($rows, 0, max(1, min(20, $limit)));
    }

    /**
     * @return array<string, mixed>
     */
    public static function bundle_info(): array
    {
        $path = SABI_CHATBOT_PATH . self::INDEX_FILE;
        if (!is_file($path)) {
            return [];
        }
        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function definitions(): array
    {
        $settings = Settings::get();

        return isset($settings['sources']) && is_array($settings['sources']) ? $settings['sources'] : [];
    }

    private static function definitions_hash(): string
    {
        return hash('sha256', (string) wp_json_encode(self::definitions()));
    }

    private static function source_id(string $source_key): int
    {
        global $wpdb;

        return (int) $wpdb->get_var($wpdb->prepare(
            'SELECT id FROM ' . Database::sources_table() . ' WHERE source_key = %s',
            $source_key
        ));
    }

    /**
     * @param array<int, array<string, mixed>> $chunks
     */
    private static function replace_chunks(int $source_id, array $chunks): int
    {
        global $wpdb;

        $wpdb->delete(Database::chunks_table(), ['source_id' => $source_id], ['%d']);
        $inserted = 0;
        foreach ($chunks as $chunk) {
            if (!is_array($chunk) || empty($chunk['content'])) {
                continue;
            }
            $result = $wpdb->insert(
                Database::chunks_table(),
                [
                    'source_id' => $source_id,
                    'chunk_index' => absint($chunk['index'] ?? $inserted),
                    'page_number' => absint($chunk['page'] ?? 0),
                    'heading' => sanitize_text_field($chunk['heading'] ?? ''),
                    'content' => wp_strip_all_tags((string) $chunk['content']),
                    'content_hash' => sanitize_text_field($chunk['hash'] ?? hash('sha256', (string) $chunk['content'])),
                ],
                ['%d', '%d', '%d', '%s', '%s', '%s']
            );
            if ($result === false) {
                throw new \RuntimeException(__('A knowledge chunk could not be stored.', 'sabi-chatbot'));
            }
            $inserted++;
        }

        return $inserted;
    }

    /**
     * @param array<string, mixed> $definition
     */
    private static function index_webpage(array $definition): int
    {
        global $wpdb;

        $source_key = sanitize_key($definition['source_key'] ?? '');
        $source_id = self::source_id($source_key);
        if ($source_id === 0) {
            return 0;
        }
        $page = get_page_by_path((string) ($definition['post_path'] ?? ''), OBJECT, 'page');
        if (!$page instanceof \WP_Post) {
            $wpdb->update(
                Database::sources_table(),
                ['status' => 'missing', 'notes' => __('The configured WordPress page was not found.', 'sabi-chatbot'), 'chunk_count' => 0],
                ['id' => $source_id],
                ['%s', '%s', '%d'],
                ['%d']
            );

            return 0;
        }

        $text = self::page_text($page);
        $chunks = [];
        foreach (self::chunk_text($text) as $index => $content) {
            $chunks[] = [
                'index' => $index,
                'page' => 0,
                'heading' => $page->post_title,
                'content' => $content,
                'hash' => hash('sha256', $content),
            ];
        }
        $inserted = self::replace_chunks($source_id, $chunks);
        $wpdb->update(
            Database::sources_table(),
            [
                'status' => $inserted > 0 ? 'indexed' : 'empty',
                'checksum' => hash('sha256', $text),
                'page_count' => 1,
                'chunk_count' => $inserted,
                'indexed_at' => current_time('mysql', true),
                'notes' => $inserted > 0 ? '' : __('No indexable text was found on the page.', 'sabi-chatbot'),
            ],
            ['id' => $source_id],
            ['%s', '%s', '%d', '%d', '%s', '%s'],
            ['%d']
        );

        return $inserted;
    }

    private static function page_text(\WP_Post $page): string
    {
        $parts = [$page->post_title, wp_strip_all_tags(strip_shortcodes($page->post_content))];
        $elementor = json_decode((string) get_post_meta($page->ID, '_elementor_data', true), true);
        if (is_array($elementor)) {
            self::collect_elementor_text($elementor, $parts);
        }
        $parts = array_values(array_unique(array_filter(array_map(static function (string $part): string {
            $part = html_entity_decode(wp_strip_all_tags($part), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            return trim((string) preg_replace('/\s+/u', ' ', $part));
        }, $parts), static fn(string $part): bool => mb_strlen($part) >= 3)));

        return implode("\n\n", $parts);
    }

    /**
     * @param array<mixed> $node
     * @param string[]     $parts
     */
    private static function collect_elementor_text(array $node, array &$parts): void
    {
        foreach ($node as $key => $value) {
            if (is_array($value)) {
                self::collect_elementor_text($value, $parts);
                continue;
            }
            if (!is_string($value) || !preg_match('/(title|text|description|editor|content|caption|label|question|answer)/i', (string) $key)) {
                continue;
            }
            if (filter_var($value, FILTER_VALIDATE_URL) || str_starts_with(trim($value), '{')) {
                continue;
            }
            $parts[] = strip_shortcodes($value);
        }
    }

    /**
     * @return string[]
     */
    private static function chunk_text(string $text, int $size = 1100, int $overlap = 160): array
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        if ($text === '') {
            return [];
        }
        $chunks = [];
        $offset = 0;
        $length = mb_strlen($text);
        while ($offset < $length) {
            $chunk = mb_substr($text, $offset, $size);
            if ($offset + $size < $length) {
                $last_space = mb_strrpos($chunk, ' ');
                if ($last_space !== false && $last_space > (int) ($size * 0.6)) {
                    $chunk = mb_substr($chunk, 0, $last_space);
                }
            }
            $chunks[] = trim($chunk);
            if ($offset + mb_strlen($chunk) >= $length) {
                break;
            }
            $advance = max(1, mb_strlen($chunk) - $overlap);
            $offset += $advance;
        }

        return array_values(array_filter($chunks));
    }

    /**
     * @return string[]
     */
    private static function search_tokens(string $query): array
    {
        $stopwords = ['the', 'and', 'for', 'with', 'from', 'that', 'this', 'what', 'how', 'are', 'una', 'para', 'con', 'los', 'las', 'que', 'como', 'del'];
        $parts = preg_split('/[^\p{L}\p{N}-]+/u', self::normalize($query)) ?: [];
        $parts = array_filter($parts, static fn(string $part): bool => mb_strlen($part) >= 3 && !in_array($part, $stopwords, true));

        return array_slice(array_values(array_unique($parts)), 0, 6);
    }

    /**
     * @param string[] $tokens
     */
    private static function snippet(string $content, array $tokens): string
    {
        $position = false;
        foreach ($tokens as $token) {
            $candidate = mb_stripos($content, $token);
            if ($candidate !== false && ($position === false || $candidate < $position)) {
                $position = $candidate;
            }
        }
        $start = $position === false ? 0 : max(0, $position - 140);
        $snippet = mb_substr($content, $start, 430);

        return ($start > 0 ? '...' : '') . trim($snippet) . (mb_strlen($content) > $start + 430 ? '...' : '');
    }

    private static function normalize(string $value): string
    {
        $value = remove_accents(mb_strtolower($value));

        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }

    private static function date_value(mixed $value): ?string
    {
        $value = sanitize_text_field((string) $value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }
}
