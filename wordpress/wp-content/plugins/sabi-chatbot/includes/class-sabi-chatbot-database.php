<?php

namespace SaberCollege\SabiChatbot;

if (!defined('ABSPATH')) {
    exit;
}

final class Database
{
    private const SCHEMA_VERSION = '3.0.0';
    private const SCHEMA_OPTION = 'sabi_chatbot_schema_version';

    public static function table_name(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'sabi_chatbot_leads';
    }

    public static function sources_table(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'sabi_chatbot_sources';
    }

    public static function chunks_table(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'sabi_chatbot_chunks';
    }

    public static function usage_table(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'sabi_chatbot_usage';
    }

    public static function maybe_install(): void
    {
        if (get_option(self::SCHEMA_OPTION) !== self::SCHEMA_VERSION) {
            self::install();
        }
    }

    public static function install(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table = self::table_name();
        $sources_table = self::sources_table();
        $chunks_table = self::chunks_table();
        $usage_table = self::usage_table();
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            created_at datetime NOT NULL,
            name varchar(120) NOT NULL,
            program varchar(64) NOT NULL,
            email varchar(191) NOT NULL,
            phone varchar(40) NOT NULL,
            consent tinyint(1) unsigned NOT NULL DEFAULT 1,
            language varchar(5) NOT NULL DEFAULT 'en',
            source_url varchar(500) NOT NULL DEFAULT '',
            status varchar(20) NOT NULL DEFAULT 'new',
            PRIMARY KEY  (id),
            KEY created_at (created_at),
            KEY email (email),
            KEY program (program)
        ) {$charset};";

        $sources_sql = "CREATE TABLE {$sources_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            source_key varchar(100) NOT NULL,
            label varchar(255) NOT NULL,
            type varchar(40) NOT NULL,
            url varchar(500) NOT NULL,
            priority smallint(5) unsigned NOT NULL DEFAULT 10,
            scope varchar(80) NOT NULL DEFAULT 'institution',
            is_current tinyint(1) unsigned NOT NULL DEFAULT 1,
            status varchar(30) NOT NULL DEFAULT 'registered',
            reviewed_at date DEFAULT NULL,
            checksum char(64) NOT NULL DEFAULT '',
            page_count int(10) unsigned NOT NULL DEFAULT 0,
            chunk_count int(10) unsigned NOT NULL DEFAULT 0,
            indexed_at datetime DEFAULT NULL,
            notes text NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY source_key (source_key),
            KEY source_status (is_current, status),
            KEY source_priority (priority)
        ) {$charset};";

        $chunks_sql = "CREATE TABLE {$chunks_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            source_id bigint(20) unsigned NOT NULL,
            chunk_index int(10) unsigned NOT NULL,
            page_number int(10) unsigned NOT NULL DEFAULT 0,
            heading varchar(255) NOT NULL DEFAULT '',
            content longtext NOT NULL,
            content_hash char(64) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY source_chunk (source_id, chunk_index),
            KEY source_id (source_id),
            KEY page_number (page_number),
            FULLTEXT KEY content_search (heading, content)
        ) {$charset};";

        $usage_sql = "CREATE TABLE {$usage_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            created_at datetime NOT NULL,
            provider varchar(40) NOT NULL,
            model varchar(100) NOT NULL,
            status varchar(30) NOT NULL,
            input_tokens int(10) unsigned NOT NULL DEFAULT 0,
            output_tokens int(10) unsigned NOT NULL DEFAULT 0,
            total_tokens int(10) unsigned NOT NULL DEFAULT 0,
            latency_ms int(10) unsigned NOT NULL DEFAULT 0,
            source_count smallint(5) unsigned NOT NULL DEFAULT 0,
            error_code varchar(80) NOT NULL DEFAULT '',
            session_hash char(64) NOT NULL DEFAULT '',
            response_id varchar(191) NOT NULL DEFAULT '',
            PRIMARY KEY  (id),
            KEY created_at (created_at),
            KEY request_status (status),
            KEY request_model (model)
        ) {$charset};";

        dbDelta($sql);
        dbDelta($sources_sql);
        dbDelta($chunks_sql);
        dbDelta($usage_sql);
        update_option(self::SCHEMA_OPTION, self::SCHEMA_VERSION, false);
    }

    /**
     * @param array<string, mixed> $lead Sanitized lead data.
     * @return int|\WP_Error
     */
    public static function insert(array $lead): int|\WP_Error
    {
        global $wpdb;

        $inserted = $wpdb->insert(
            self::table_name(),
            [
                'created_at' => current_time('mysql', true),
                'name' => $lead['name'],
                'program' => $lead['program'],
                'email' => $lead['email'],
                'phone' => $lead['phone'],
                'consent' => 1,
                'language' => $lead['language'],
                'source_url' => $lead['source_url'],
                'status' => 'new',
            ],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
        );

        if ($inserted === false) {
            return new \WP_Error('sabi_lead_insert_failed', __('The lead could not be saved.', 'sabi-chatbot'));
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int, pages: int}
     */
    public static function query(string $search = '', int $page = 1, int $per_page = 20): array
    {
        global $wpdb;

        $where = '1=1';
        $arguments = [];
        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where .= ' AND (name LIKE %s OR email LIKE %s OR phone LIKE %s OR program LIKE %s)';
            $arguments = [$like, $like, $like, $like];
        }

        $table = self::table_name();
        $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
        if ($arguments) {
            $count_sql = $wpdb->prepare($count_sql, ...$arguments); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }
        $total = (int) $wpdb->get_var($count_sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        $page = max(1, $page);
        $per_page = min(100, max(1, $per_page));
        $offset = ($page - 1) * $per_page;
        $query_sql = "SELECT * FROM {$table} WHERE {$where} ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d";
        $query_arguments = array_merge($arguments, [$per_page, $offset]);
        $query_sql = $wpdb->prepare($query_sql, ...$query_arguments); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $items = $wpdb->get_results($query_sql, ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        return [
            'items' => is_array($items) ? $items : [],
            'total' => $total,
            'pages' => max(1, (int) ceil($total / $per_page)),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        global $wpdb;

        $table = self::table_name();
        $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC, id DESC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        return is_array($rows) ? $rows : [];
    }

    public static function count(): int
    {
        global $wpdb;

        $table = self::table_name();

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    public static function table_exists(): bool
    {
        global $wpdb;

        $table = self::table_name();

        return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) === $table;
    }

    public static function knowledge_tables_exist(): bool
    {
        global $wpdb;

        $sources = self::sources_table();
        $chunks = self::chunks_table();

        return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($sources))) === $sources
            && $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($chunks))) === $chunks;
    }

    public static function usage_table_exists(): bool
    {
        global $wpdb;

        $table = self::usage_table();

        return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) === $table;
    }

    /**
     * Stores technical API metrics only. Messages and retrieved text are never persisted.
     *
     * @param array<string, mixed> $data Technical request metadata.
     */
    public static function log_usage(array $data): void
    {
        global $wpdb;

        $wpdb->insert(
            self::usage_table(),
            [
                'created_at' => current_time('mysql', true),
                'provider' => sanitize_key($data['provider'] ?? 'openai'),
                'model' => sanitize_text_field($data['model'] ?? ''),
                'status' => sanitize_key($data['status'] ?? 'error'),
                'input_tokens' => absint($data['input_tokens'] ?? 0),
                'output_tokens' => absint($data['output_tokens'] ?? 0),
                'total_tokens' => absint($data['total_tokens'] ?? 0),
                'latency_ms' => absint($data['latency_ms'] ?? 0),
                'source_count' => absint($data['source_count'] ?? 0),
                'error_code' => sanitize_key($data['error_code'] ?? ''),
                'session_hash' => sanitize_text_field($data['session_hash'] ?? ''),
                'response_id' => sanitize_text_field($data['response_id'] ?? ''),
            ],
            ['%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s']
        );
    }

    /**
     * @return array{requests: int, successful: int, input_tokens: int, output_tokens: int, total_tokens: int, average_latency_ms: int}
     */
    public static function usage_stats(): array
    {
        global $wpdb;

        $table = self::usage_table();
        $today = gmdate('Y-m-d 00:00:00');
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT COUNT(*) AS requests,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS successful,
                    COALESCE(SUM(input_tokens), 0) AS input_tokens,
                    COALESCE(SUM(output_tokens), 0) AS output_tokens,
                    COALESCE(SUM(total_tokens), 0) AS total_tokens,
                    COALESCE(AVG(latency_ms), 0) AS average_latency_ms
                FROM {$table} WHERE created_at >= %s",
                $today
            ),
            ARRAY_A
        );

        return [
            'requests' => (int) ($row['requests'] ?? 0),
            'successful' => (int) ($row['successful'] ?? 0),
            'input_tokens' => (int) ($row['input_tokens'] ?? 0),
            'output_tokens' => (int) ($row['output_tokens'] ?? 0),
            'total_tokens' => (int) ($row['total_tokens'] ?? 0),
            'average_latency_ms' => (int) round((float) ($row['average_latency_ms'] ?? 0)),
        ];
    }

    public static function delete(int $lead_id): bool
    {
        global $wpdb;

        return $wpdb->delete(self::table_name(), ['id' => $lead_id], ['%d']) === 1;
    }
}
