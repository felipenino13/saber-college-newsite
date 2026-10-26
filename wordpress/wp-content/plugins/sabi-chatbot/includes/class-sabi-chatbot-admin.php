<?php

namespace SaberCollege\SabiChatbot;

if (!defined('ABSPATH')) {
    exit;
}

final class Admin
{
    public const CAPABILITY = 'manage_sabi_chatbot';

    /** @var string[] */
    private array $tabs = [
        'general',
        'content',
        'sources',
        'leads',
        'appearance',
        'api',
        'security',
        'tests',
    ];

    public function register(): void
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_post_sabi_chatbot_save_settings', [$this, 'save_settings']);
        add_action('admin_post_sabi_chatbot_delete_lead', [$this, 'delete_lead']);
        add_action('admin_post_sabi_chatbot_export_leads', [$this, 'export_leads']);
        add_action('admin_post_sabi_chatbot_rebuild_knowledge', [$this, 'rebuild_knowledge']);
    }

    public function register_menu(): void
    {
        add_menu_page(
            __('SABI Chatbot', 'sabi-chatbot'),
            __('SABI Chatbot', 'sabi-chatbot'),
            self::CAPABILITY,
            'sabi-chatbot',
            [$this, 'render_page'],
            SABI_CHATBOT_URL . 'assets/images/sabi-chat-plugin.svg',
            59
        );
    }

    public function enqueue_assets(string $hook): void
    {
        if ($hook !== 'toplevel_page_sabi-chatbot') {
            return;
        }

        wp_enqueue_style('sabi-chatbot-admin', SABI_CHATBOT_URL . 'assets/css/admin.css', [], SABI_CHATBOT_VERSION);
        wp_enqueue_style('sabi-chatbot-public', SABI_CHATBOT_URL . 'assets/css/sabi-chatbot.css', [], SABI_CHATBOT_VERSION);
    }

    public function save_settings(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('You are not allowed to manage SABI Chatbot.', 'sabi-chatbot'), '', ['response' => 403]);
        }

        check_admin_referer('sabi_chatbot_save_settings');

        $section = sanitize_key(wp_unslash($_POST['section'] ?? ''));
        if (!in_array($section, ['general', 'content', 'leads', 'appearance', 'api', 'security'], true)) {
            wp_die(esc_html__('Invalid settings section.', 'sabi-chatbot'), '', ['response' => 400]);
        }

        $input = isset($_POST['settings']) && is_array($_POST['settings'])
            ? wp_unslash($_POST['settings'])
            : [];

        Settings::save_section($section, $input);

        $redirect = add_query_arg(
            [
                'page' => 'sabi-chatbot',
                'tab' => $section,
                'updated' => '1',
            ],
            admin_url('admin.php')
        );
        wp_safe_redirect($redirect);
        exit;
    }

    public function delete_lead(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('You are not allowed to manage SABI leads.', 'sabi-chatbot'), '', ['response' => 403]);
        }

        $lead_id = absint($_POST['lead_id'] ?? 0);
        check_admin_referer('sabi_chatbot_delete_lead_' . $lead_id);
        $deleted = $lead_id > 0 && Database::delete($lead_id);

        wp_safe_redirect(add_query_arg([
            'page' => 'sabi-chatbot',
            'tab' => 'leads',
            'lead_deleted' => $deleted ? '1' : '0',
        ], admin_url('admin.php')));
        exit;
    }

    public function export_leads(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('You are not allowed to export SABI leads.', 'sabi-chatbot'), '', ['response' => 403]);
        }

        check_admin_referer('sabi_chatbot_export_leads');
        $rows = Database::all();

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="sabi-leads-' . gmdate('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'wb');
        if ($output === false) {
            wp_die(esc_html__('The CSV file could not be created.', 'sabi-chatbot'));
        }

        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['ID', 'Date (UTC)', 'Name', 'Program', 'Email', 'Phone', 'Consent', 'Language', 'Source', 'Status']);
        foreach ($rows as $row) {
            fputcsv($output, [
                (int) $row['id'],
                self::csv_cell($row['created_at']),
                self::csv_cell($row['name']),
                self::csv_cell($this->program_label($row['program'])),
                self::csv_cell($row['email']),
                self::csv_cell($row['phone']),
                !empty($row['consent']) ? 'Yes' : 'No',
                self::csv_cell($row['language']),
                self::csv_cell($row['source_url']),
                self::csv_cell($row['status']),
            ]);
        }
        fclose($output);
        exit;
    }

    public function rebuild_knowledge(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('You are not allowed to rebuild SABI knowledge.', 'sabi-chatbot'), '', ['response' => 403]);
        }

        check_admin_referer('sabi_chatbot_rebuild_knowledge');
        $result = Knowledge::rebuild();
        $arguments = [
            'page' => 'sabi-chatbot',
            'tab' => 'sources',
        ];
        if (is_wp_error($result)) {
            $arguments['knowledge_error'] = '1';
        } else {
            $arguments['knowledge_updated'] = '1';
            $arguments['knowledge_chunks'] = (string) $result['chunks'];
        }

        wp_safe_redirect(add_query_arg($arguments, admin_url('admin.php')));
        exit;
    }

    public function render_page(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            return;
        }

        $tab = sanitize_key(wp_unslash($_GET['tab'] ?? 'general'));
        if (!in_array($tab, $this->tabs, true)) {
            $tab = 'general';
        }

        $settings = Settings::get();
        ?>
        <div class="wrap sabi-admin">
            <header class="sabi-admin__hero">
                <div>
                    <span class="sabi-admin__eyebrow"><?php esc_html_e('SABER College', 'sabi-chatbot'); ?></span>
                    <h1><?php esc_html_e('SABI Chatbot', 'sabi-chatbot'); ?></h1>
                    <p><?php esc_html_e('Configure the virtual guide without exposing it to visitors while development is in progress.', 'sabi-chatbot'); ?></p>
                </div>
                <div class="sabi-admin__status">
                    <span class="sabi-status-dot sabi-status-dot--warning"></span>
                    <strong><?php esc_html_e('Development mode', 'sabi-chatbot'); ?></strong>
                    <small><?php echo Api::is_ready() ? esc_html__('API configured; public widget is not active', 'sabi-chatbot') : esc_html__('API and public widget are not active', 'sabi-chatbot'); ?></small>
                </div>
            </header>

            <?php if (isset($_GET['updated'])) : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('SABI settings saved.', 'sabi-chatbot'); ?></p></div>
            <?php endif; ?>
            <?php if (isset($_GET['lead_deleted'])) : ?>
                <div class="notice <?php echo $_GET['lead_deleted'] === '1' ? 'notice-success' : 'notice-error'; ?> is-dismissible"><p><?php echo $_GET['lead_deleted'] === '1' ? esc_html__('Lead deleted.', 'sabi-chatbot') : esc_html__('The lead could not be deleted.', 'sabi-chatbot'); ?></p></div>
            <?php endif; ?>
            <?php if (isset($_GET['knowledge_updated'])) : ?>
                <div class="notice notice-success is-dismissible"><p><?php printf(esc_html__('Knowledge rebuilt successfully: %d searchable fragments.', 'sabi-chatbot'), absint($_GET['knowledge_chunks'] ?? 0)); ?></p></div>
            <?php endif; ?>
            <?php if (isset($_GET['knowledge_error'])) : ?>
                <div class="notice notice-error is-dismissible"><p><?php esc_html_e('The knowledge library could not be rebuilt. Review the plugin tests and server log.', 'sabi-chatbot'); ?></p></div>
            <?php endif; ?>

            <nav class="nav-tab-wrapper sabi-admin__tabs" aria-label="<?php esc_attr_e('SABI settings', 'sabi-chatbot'); ?>">
                <?php foreach ($this->tabs as $tab_key) : ?>
                    <?php
                    $label = $this->tab_label($tab_key);
                    $url = add_query_arg(['page' => 'sabi-chatbot', 'tab' => $tab_key], admin_url('admin.php'));
                    ?>
                    <a class="nav-tab <?php echo $tab === $tab_key ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url($url); ?>">
                        <?php echo esc_html($label); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <main class="sabi-admin__panel">
                <?php $this->render_tab($tab, $settings); ?>
            </main>
        </div>
        <?php
    }

    /**
     * @param array<string, mixed> $settings Complete plugin settings.
     */
    private function render_tab(string $tab, array $settings): void
    {
        if ($tab === 'sources') {
            $this->render_sources($settings['sources']);
            return;
        }

        if ($tab === 'leads') {
            $this->render_leads($settings['leads']);
            return;
        }

        if ($tab === 'tests') {
            $this->render_tests($settings);
            return;
        }

        $this->render_settings_form($tab, $settings[$tab]);
    }

    /**
     * @param array<string, mixed> $values Section values.
     */
    private function render_settings_form(string $section, array $values): void
    {
        ?>
        <form class="sabi-settings-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="sabi_chatbot_save_settings">
            <input type="hidden" name="section" value="<?php echo esc_attr($section); ?>">
            <?php wp_nonce_field('sabi_chatbot_save_settings'); ?>

            <?php if ($section === 'general') : ?>
                <h2><?php esc_html_e('General', 'sabi-chatbot'); ?></h2>
                <p class="description"><?php esc_html_e('The public chatbot remains disabled by default. Preview mode only grants access to administrators.', 'sabi-chatbot'); ?></p>
                <?php $this->checkbox('enabled', __('Allow shortcode output for visitors', 'sabi-chatbot'), $values['enabled'], __('Keep this disabled until the conversational phases are complete.', 'sabi-chatbot')); ?>
                <?php $this->checkbox('preview_mode', __('Administrator preview mode', 'sabi-chatbot'), $values['preview_mode'], __('Allows administrators to review the simulated conversation while public output is disabled.', 'sabi-chatbot')); ?>
                <?php $this->text_field('assistant_name', __('Assistant name', 'sabi-chatbot'), $values['assistant_name']); ?>
                <div class="sabi-readonly"><strong><?php esc_html_e('Shortcode', 'sabi-chatbot'); ?></strong><code>[sabi_chatbot]</code></div>
            <?php elseif ($section === 'content') : ?>
                <h2><?php esc_html_e('Content and personality', 'sabi-chatbot'); ?></h2>
                <?php $this->textarea('welcome_message', __('Welcome message', 'sabi-chatbot'), $values['welcome_message'], 4); ?>
                <?php $this->text_field('input_placeholder', __('Input placeholder', 'sabi-chatbot'), $values['input_placeholder']); ?>
                <?php $this->textarea('compliance_text', __('Visitor notice', 'sabi-chatbot'), $values['compliance_text'], 4); ?>
                <?php $this->textarea('instructions', __('SABI instructions', 'sabi-chatbot'), $values['instructions'], 18); ?>
            <?php elseif ($section === 'appearance') : ?>
                <h2><?php esc_html_e('Appearance', 'sabi-chatbot'); ?></h2>
                <?php $this->url_field('avatar_url', __('Avatar URL', 'sabi-chatbot'), $values['avatar_url']); ?>
                <div class="sabi-color-grid">
                    <?php $this->color_field('primary_color', __('Primary color', 'sabi-chatbot'), $values['primary_color']); ?>
                    <?php $this->color_field('accent_color', __('Accent color', 'sabi-chatbot'), $values['accent_color']); ?>
                    <?php $this->color_field('surface_color', __('Surface color', 'sabi-chatbot'), $values['surface_color']); ?>
                </div>
                <label class="sabi-field"><span><?php esc_html_e('Widget position', 'sabi-chatbot'); ?></span><select name="settings[position]"><option value="bottom-right" <?php selected($values['position'], 'bottom-right'); ?>><?php esc_html_e('Bottom right', 'sabi-chatbot'); ?></option><option value="bottom-left" <?php selected($values['position'], 'bottom-left'); ?>><?php esc_html_e('Bottom left', 'sabi-chatbot'); ?></option></select></label>
            <?php elseif ($section === 'api') : ?>
                <?php
                $api_ready = Api::is_ready();
                $usage = Database::usage_stats();
                $credential_source = Settings::credential_source();
                $credential_labels = [
                    'wordpress_encrypted' => __('Encrypted WordPress storage', 'sabi-chatbot'),
                    'constant' => __('wp-config.php constant', 'sabi-chatbot'),
                    'environment' => __('Server environment', 'sabi-chatbot'),
                    'not_configured' => __('Not configured', 'sabi-chatbot'),
                ];
                ?>
                <h2><?php esc_html_e('API', 'sabi-chatbot'); ?></h2>
                <div class="sabi-callout <?php echo $api_ready ? 'sabi-callout--success' : 'sabi-callout--warning'; ?>"><strong><?php echo $api_ready ? esc_html__('OpenAI configuration ready', 'sabi-chatbot') : esc_html__('OpenAI key is not available to WordPress', 'sabi-chatbot'); ?></strong><p><?php esc_html_e('A credential entered here is encrypted before it is stored and is never returned to the browser. Server environment configuration remains available as a fallback.', 'sabi-chatbot'); ?></p></div>
                <?php $this->checkbox('enabled', __('Enable AI responses in the chatbot', 'sabi-chatbot'), (bool) $values['enabled'], __('This controls API responses only. Public visibility remains controlled separately under General.', 'sabi-chatbot')); ?>
                <div class="sabi-readonly"><strong><?php esc_html_e('Provider', 'sabi-chatbot'); ?></strong><span>OpenAI</span></div>
                <?php $this->text_field('model', __('Preferred model', 'sabi-chatbot'), $values['model']); ?>
                <?php $this->number_field('max_output_tokens', __('Maximum output tokens', 'sabi-chatbot'), (int) $values['max_output_tokens'], 100, 2000); ?>
                <?php $this->number_field('timeout_seconds', __('Request timeout in seconds', 'sabi-chatbot'), (int) $values['timeout_seconds'], 10, 90); ?>
                <?php $this->password_field('api_key', __('OpenAI API credential', 'sabi-chatbot'), Settings::has_stored_api_key() ? __('A credential is already stored. Leave this blank to keep it or enter a new value to replace it.', 'sabi-chatbot') : __('Leave this blank to continue using the server environment credential.', 'sabi-chatbot')); ?>
                <?php if (Settings::has_stored_api_key()) : ?>
                    <?php $this->checkbox('clear_api_key', __('Remove the stored credential', 'sabi-chatbot'), false, __('After saving, SABI will fall back to the wp-config.php constant or server environment credential.', 'sabi-chatbot')); ?>
                <?php endif; ?>
                <div class="sabi-readonly"><strong><?php esc_html_e('Endpoint', 'sabi-chatbot'); ?></strong><code>https://api.openai.com/v1/responses</code></div>
                <div class="sabi-readonly"><strong><?php esc_html_e('Credential source', 'sabi-chatbot'); ?></strong><span><?php echo esc_html($credential_labels[$credential_source] ?? $credential_labels['not_configured']); ?></span></div>
                <h3><?php esc_html_e('Today’s technical usage', 'sabi-chatbot'); ?></h3>
                <div class="sabi-knowledge-stats"><div><strong><?php echo esc_html(number_format_i18n($usage['requests'])); ?></strong><span><?php esc_html_e('requests', 'sabi-chatbot'); ?></span></div><div><strong><?php echo esc_html(number_format_i18n($usage['successful'])); ?></strong><span><?php esc_html_e('successful', 'sabi-chatbot'); ?></span></div><div><strong><?php echo esc_html(number_format_i18n($usage['total_tokens'])); ?></strong><span><?php esc_html_e('total tokens', 'sabi-chatbot'); ?></span></div><div><strong><?php echo esc_html(number_format_i18n($usage['average_latency_ms'])); ?></strong><span><?php esc_html_e('average latency (ms)', 'sabi-chatbot'); ?></span></div></div>
                <p class="description"><?php esc_html_e('Only technical metrics are stored. Visitor questions, responses, and retrieved fragments are not saved in this table.', 'sabi-chatbot'); ?></p>
            <?php elseif ($section === 'security') : ?>
                <h2><?php esc_html_e('Security and privacy defaults', 'sabi-chatbot'); ?></h2>
                <?php $this->number_field('max_input_length', __('Maximum input length', 'sabi-chatbot'), $values['max_input_length'], 100, 4000); ?>
                <?php $this->number_field('requests_per_minute', __('Requests per minute and visitor', 'sabi-chatbot'), $values['requests_per_minute'], 1, 60); ?>
                <?php $this->number_field('requests_per_session', __('Requests per conversation session', 'sabi-chatbot'), $values['requests_per_session'], 1, 500); ?>
                <?php $this->number_field('requests_per_day', __('Total API requests per day', 'sabi-chatbot'), $values['requests_per_day'], 10, 10000); ?>
                <?php $this->number_field('lead_retention_days', __('Lead retention in days', 'sabi-chatbot'), $values['lead_retention_days'], 1, 730); ?>
                <?php $this->checkbox('store_conversations', __('Store conversation transcripts', 'sabi-chatbot'), $values['store_conversations'], __('Disabled by default to minimize personal data retention.', 'sabi-chatbot')); ?>
                <?php $this->url_field('privacy_url', __('Privacy policy URL', 'sabi-chatbot'), $values['privacy_url']); ?>
            <?php endif; ?>

            <?php submit_button(__('Save settings', 'sabi-chatbot')); ?>
        </form>
        <?php
    }

    /**
     * @param array<int, array<string, mixed>> $sources Sources list.
     */
    private function render_sources(array $sources): void
    {
        $stats = Knowledge::stats();
        $registered_sources = Knowledge::sources();
        $bundle = Knowledge::bundle_info();
        $search = sanitize_text_field(wp_unslash($_GET['knowledge_q'] ?? ''));
        $results = $search !== '' ? Knowledge::search($search) : [];
        ?>
        <div class="sabi-section-heading">
            <div>
                <h2><?php esc_html_e('Institutional knowledge', 'sabi-chatbot'); ?></h2>
                <p><?php esc_html_e('Searchable, traceable sources prepared locally before an AI provider is connected.', 'sabi-chatbot'); ?></p>
            </div>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="sabi_chatbot_rebuild_knowledge">
                <?php wp_nonce_field('sabi_chatbot_rebuild_knowledge'); ?>
                <button class="button button-primary" type="submit"><?php esc_html_e('Rebuild knowledge', 'sabi-chatbot'); ?></button>
            </form>
        </div>

        <div class="sabi-callout"><strong><?php esc_html_e('Local and controlled', 'sabi-chatbot'); ?></strong><p><?php esc_html_e('No content is sent to an AI provider. Only current approved sources are indexed; historical files remain outside the active library.', 'sabi-chatbot'); ?></p></div>

        <div class="sabi-knowledge-stats">
            <div><strong><?php echo esc_html(number_format_i18n($stats['current_sources'])); ?></strong><span><?php esc_html_e('current sources', 'sabi-chatbot'); ?></span></div>
            <div><strong><?php echo esc_html(number_format_i18n($stats['indexed_sources'])); ?></strong><span><?php esc_html_e('indexed sources', 'sabi-chatbot'); ?></span></div>
            <div><strong><?php echo esc_html(number_format_i18n($stats['pages'])); ?></strong><span><?php esc_html_e('source pages', 'sabi-chatbot'); ?></span></div>
            <div><strong><?php echo esc_html(number_format_i18n($stats['chunks'])); ?></strong><span><?php esc_html_e('searchable fragments', 'sabi-chatbot'); ?></span></div>
        </div>

        <form class="sabi-knowledge-search" action="<?php echo esc_url(admin_url('admin.php')); ?>" method="get">
            <input type="hidden" name="page" value="sabi-chatbot">
            <input type="hidden" name="tab" value="sources">
            <label for="sabi-knowledge-search"><?php esc_html_e('Test the local knowledge search', 'sabi-chatbot'); ?></label>
            <div><input id="sabi-knowledge-search" type="search" name="knowledge_q" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Example: graduation requirements', 'sabi-chatbot'); ?>"><button class="button" type="submit"><?php esc_html_e('Search', 'sabi-chatbot'); ?></button></div>
        </form>

        <?php if ($search !== '') : ?>
            <section class="sabi-knowledge-results" aria-labelledby="sabi-search-results-title">
                <h3 id="sabi-search-results-title"><?php printf(esc_html__('Results for “%s”', 'sabi-chatbot'), esc_html($search)); ?></h3>
                <?php if (!$results) : ?><p><?php esc_html_e('No fragment matched this search.', 'sabi-chatbot'); ?></p><?php endif; ?>
                <?php foreach ($results as $result) : ?>
                    <article class="sabi-knowledge-result">
                        <div><strong><?php echo esc_html($result['source_label']); ?></strong><span><?php echo esc_html($result['type']); ?><?php echo (int) $result['page_number'] > 0 ? ' · ' . esc_html(sprintf(__('Page %d', 'sabi-chatbot'), (int) $result['page_number'])) : ''; ?></span></div>
                        <?php if (!empty($result['heading'])) : ?><h4><?php echo esc_html($result['heading']); ?></h4><?php endif; ?>
                        <p><?php echo esc_html($result['snippet']); ?></p>
                        <a href="<?php echo esc_url(home_url($result['url'])); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open source', 'sabi-chatbot'); ?><span class="screen-reader-text">: <?php echo esc_html($result['source_label']); ?></span></a>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <h3><?php esc_html_e('Registered sources', 'sabi-chatbot'); ?></h3>
        <div class="sabi-sources-wrap"><table class="widefat striped sabi-sources"><thead><tr><th><?php esc_html_e('Priority', 'sabi-chatbot'); ?></th><th><?php esc_html_e('Source', 'sabi-chatbot'); ?></th><th><?php esc_html_e('Scope', 'sabi-chatbot'); ?></th><th><?php esc_html_e('Reviewed', 'sabi-chatbot'); ?></th><th><?php esc_html_e('Coverage', 'sabi-chatbot'); ?></th><th><?php esc_html_e('Status', 'sabi-chatbot'); ?></th></tr></thead><tbody>
        <?php foreach ($registered_sources as $source) : ?>
            <?php $status = sanitize_html_class((string) $source['status']); ?>
            <tr>
                <td><?php echo esc_html((string) $source['priority']); ?></td>
                <td><strong><?php echo esc_html($source['label']); ?></strong><span><?php echo esc_html($source['type']); ?></span><code><?php echo esc_html($source['url']); ?></code></td>
                <td><?php echo esc_html($source['scope']); ?></td>
                <td><?php echo esc_html($source['reviewed_at'] ?: '—'); ?></td>
                <td><?php printf(esc_html__('%1$d pages · %2$d fragments', 'sabi-chatbot'), (int) $source['page_count'], (int) $source['chunk_count']); ?></td>
                <td><span class="sabi-pill sabi-pill--<?php echo esc_attr($status); ?>"><?php echo esc_html($source['status']); ?></span><?php if (!empty($source['notes'])) : ?><small><?php echo esc_html($source['notes']); ?></small><?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php if (!empty($bundle['generated_at'])) : ?><p class="description sabi-index-meta"><?php printf(esc_html__('Bundled PDF index generated: %s UTC.', 'sabi-chatbot'), esc_html((string) $bundle['generated_at'])); ?></p><?php endif; ?>
        <?php
    }

    /**
     * @param array<string, mixed> $leads Lead settings.
     */
    private function render_leads(array $leads): void
    {
        $search = sanitize_text_field(wp_unslash($_GET['s'] ?? ''));
        $page = max(1, absint($_GET['paged'] ?? 1));
        $results = Database::query($search, $page, 20);
        $all_count = Database::count();
        ?>
        <h2><?php esc_html_e('Lead capture', 'sabi-chatbot'); ?></h2>
        <div class="sabi-callout"><strong><?php esc_html_e('Structured and independent from the AI', 'sabi-chatbot'); ?></strong><p><?php esc_html_e('Only the submitted contact fields are stored. Conversation messages are not saved. The public chatbot remains controlled separately under General.', 'sabi-chatbot'); ?></p></div>

        <form class="sabi-settings-form sabi-lead-settings" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="sabi_chatbot_save_settings">
            <input type="hidden" name="section" value="leads">
            <?php wp_nonce_field('sabi_chatbot_save_settings'); ?>
            <?php $this->checkbox('enabled', __('Enable conversational lead capture inside SABI', 'sabi-chatbot'), (bool) $leads['enabled'], __('This does not enable the public chatbot; it only enables the guided contact flow wherever SABI is already visible.', 'sabi-chatbot')); ?>
            <?php $this->checkbox('notify_by_email', __('Send an email notification for new leads', 'sabi-chatbot'), (bool) $leads['notify_by_email'], __('Optional. Keep disabled while testing to avoid accidental messages.', 'sabi-chatbot')); ?>
            <?php $this->text_field('notification_email', __('Notification email', 'sabi-chatbot'), (string) $leads['notification_email'], 'email'); ?>
            <?php $this->textarea('consent_text_en', __('Consent text in English', 'sabi-chatbot'), (string) $leads['consent_text_en'], 3); ?>
            <?php $this->textarea('consent_text_es', __('Consent text in Spanish', 'sabi-chatbot'), (string) $leads['consent_text_es'], 3); ?>
            <?php submit_button(__('Save lead settings', 'sabi-chatbot')); ?>
        </form>

        <div class="sabi-leads-heading">
            <div><h2><?php esc_html_e('Saved leads', 'sabi-chatbot'); ?></h2><p><?php printf(esc_html__('%d records', 'sabi-chatbot'), (int) $results['total']); ?></p></div>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="sabi_chatbot_export_leads">
                <?php wp_nonce_field('sabi_chatbot_export_leads'); ?>
                <button class="button button-secondary" type="submit" <?php disabled($all_count === 0); ?>><?php esc_html_e('Export CSV', 'sabi-chatbot'); ?></button>
            </form>
        </div>
        <form class="sabi-lead-search" action="<?php echo esc_url(admin_url('admin.php')); ?>" method="get">
            <input type="hidden" name="page" value="sabi-chatbot"><input type="hidden" name="tab" value="leads">
            <label class="screen-reader-text" for="sabi-lead-search"><?php esc_html_e('Search leads', 'sabi-chatbot'); ?></label>
            <input id="sabi-lead-search" type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Name, email, phone or program', 'sabi-chatbot'); ?>">
            <button class="button" type="submit"><?php esc_html_e('Search', 'sabi-chatbot'); ?></button>
            <?php if ($search !== '') : ?><a class="button-link" href="<?php echo esc_url(add_query_arg(['page' => 'sabi-chatbot', 'tab' => 'leads'], admin_url('admin.php'))); ?>"><?php esc_html_e('Clear', 'sabi-chatbot'); ?></a><?php endif; ?>
        </form>

        <div class="sabi-leads-table-wrap"><table class="widefat striped sabi-leads-table"><thead><tr><th><?php esc_html_e('Date', 'sabi-chatbot'); ?></th><th><?php esc_html_e('Contact', 'sabi-chatbot'); ?></th><th><?php esc_html_e('Program', 'sabi-chatbot'); ?></th><th><?php esc_html_e('Phone', 'sabi-chatbot'); ?></th><th><?php esc_html_e('Source', 'sabi-chatbot'); ?></th><th><?php esc_html_e('Actions', 'sabi-chatbot'); ?></th></tr></thead><tbody>
        <?php if (!$results['items']) : ?><tr><td colspan="6"><?php esc_html_e('No leads found.', 'sabi-chatbot'); ?></td></tr><?php endif; ?>
        <?php foreach ($results['items'] as $lead) : ?>
            <tr>
                <td><time datetime="<?php echo esc_attr($lead['created_at']); ?>"><?php echo esc_html(get_date_from_gmt($lead['created_at'], 'M j, Y g:i a')); ?></time><small><?php echo esc_html(strtoupper($lead['language'])); ?></small></td>
                <td><strong><?php echo esc_html($lead['name']); ?></strong><a href="mailto:<?php echo esc_attr($lead['email']); ?>"><?php echo esc_html($lead['email']); ?></a></td>
                <td><?php echo esc_html($this->program_label($lead['program'])); ?></td>
                <td><?php echo esc_html($lead['phone']); ?></td>
                <td><code><?php echo esc_html($lead['source_url'] ?: '/'); ?></code></td>
                <td><form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" onsubmit="return confirm('<?php echo esc_js(__('Delete this lead permanently?', 'sabi-chatbot')); ?>');"><input type="hidden" name="action" value="sabi_chatbot_delete_lead"><input type="hidden" name="lead_id" value="<?php echo esc_attr((string) $lead['id']); ?>"><?php wp_nonce_field('sabi_chatbot_delete_lead_' . (int) $lead['id']); ?><button class="button-link-delete" type="submit"><?php esc_html_e('Delete', 'sabi-chatbot'); ?></button></form></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php if ((int) $results['pages'] > 1) : ?><div class="tablenav"><div class="tablenav-pages"><?php echo wp_kses_post(paginate_links(['base' => add_query_arg(['page' => 'sabi-chatbot', 'tab' => 'leads', 's' => $search, 'paged' => '%#%'], admin_url('admin.php')), 'current' => $page, 'total' => (int) $results['pages']])); ?></div></div><?php endif; ?>
        <?php
    }

    /**
     * @param array<string, mixed> $settings Complete plugin settings.
     */
    private function render_tests(array $settings): void
    {
        $ai_engine_active = is_plugin_active('ai-engine/ai-engine.php');
        $knowledge_stats = Knowledge::stats();
        $mapped_topics = count(array_filter(Conversation::topics(), static fn(array $topic): bool => !empty($topic['sources'])));
        ?>
        <h2><?php esc_html_e('Configuration tests', 'sabi-chatbot'); ?></h2>
        <div class="sabi-checklist">
            <?php $this->test_row(__('Plugin settings', 'sabi-chatbot'), get_option(Settings::OPTION_NAME, null) !== null, __('Stored under sabi_chatbot_settings', 'sabi-chatbot')); ?>
            <?php $this->test_row(__('AI Engine coexistence', 'sabi-chatbot'), $ai_engine_active, $ai_engine_active ? __('Active and unchanged', 'sabi-chatbot') : __('Not active', 'sabi-chatbot')); ?>
            <?php $this->test_row(__('Public chatbot', 'sabi-chatbot'), empty($settings['general']['enabled']), empty($settings['general']['enabled']) ? __('Safely disabled', 'sabi-chatbot') : __('Enabled for shortcode visitors', 'sabi-chatbot')); ?>
            <?php $this->test_row(__('API credential', 'sabi-chatbot'), Api::is_ready(), Api::is_ready() ? __('Available from the server environment; not stored in WordPress', 'sabi-chatbot') : __('Not available to WordPress', 'sabi-chatbot')); ?>
            <?php $this->test_row(__('API usage table', 'sabi-chatbot'), Database::usage_table_exists(), __('Technical metrics only; no message content', 'sabi-chatbot')); ?>
            <?php $this->test_row(__('Knowledge tables', 'sabi-chatbot'), Database::knowledge_tables_exist(), __('Local sources and fragments tables available', 'sabi-chatbot')); ?>
            <?php $this->test_row(__('Knowledge sources', 'sabi-chatbot'), $knowledge_stats['indexed_sources'] >= 3, sprintf(__('%1$d current sources and %2$d searchable fragments', 'sabi-chatbot'), $knowledge_stats['current_sources'], $knowledge_stats['chunks'])); ?>
            <?php $this->test_row(__('Traceable answers', 'sabi-chatbot'), $mapped_topics >= 12, sprintf(__('%d controlled topics mapped to approved sources', 'sabi-chatbot'), $mapped_topics)); ?>
            <?php $this->test_row(__('Controlled conversation', 'sabi-chatbot'), count(Conversation::topics()) >= 10, sprintf(__('%d controlled topics remain available in English and Spanish', 'sabi-chatbot'), count(Conversation::topics()))); ?>
            <?php $this->test_row(__('Lead database', 'sabi-chatbot'), Database::table_exists(), sprintf(__('%d leads stored', 'sabi-chatbot'), Database::count())); ?>
        </div>
        <h2><?php esc_html_e('Shortcode preview', 'sabi-chatbot'); ?></h2>
        <p><?php esc_html_e('Suggested questions use controlled local responses. Free-form questions use the configured API with locally retrieved sources. The conversation remains in browser memory and is not stored.', 'sabi-chatbot'); ?></p>
        <div class="sabi-admin__preview"><?php echo do_shortcode('[sabi_chatbot preview="1"]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
        <?php
    }

    private function tab_label(string $tab): string
    {
        $labels = [
            'general' => __('General', 'sabi-chatbot'),
            'content' => __('Content', 'sabi-chatbot'),
            'sources' => __('Sources', 'sabi-chatbot'),
            'leads' => __('Leads', 'sabi-chatbot'),
            'appearance' => __('Appearance', 'sabi-chatbot'),
            'api' => __('API', 'sabi-chatbot'),
            'security' => __('Security', 'sabi-chatbot'),
            'tests' => __('Tests', 'sabi-chatbot'),
        ];

        return $labels[$tab] ?? ucfirst($tab);
    }

    private function text_field(string $name, string $label, string $value, string $type = 'text'): void
    {
        printf('<label class="sabi-field"><span>%1$s</span><input class="regular-text" type="%4$s" name="settings[%2$s]" value="%3$s"></label>', esc_html($label), esc_attr($name), esc_attr($value), esc_attr($type));
    }

    private function password_field(string $name, string $label, string $description): void
    {
        printf('<label class="sabi-field"><span>%1$s</span><input class="regular-text" type="password" name="settings[%2$s]" value="" autocomplete="new-password" spellcheck="false" placeholder="%3$s"><small>%4$s</small></label>', esc_html($label), esc_attr($name), esc_attr__('Enter a new credential', 'sabi-chatbot'), esc_html($description));
    }

    private function url_field(string $name, string $label, string $value): void
    {
        printf('<label class="sabi-field"><span>%1$s</span><input class="large-text" type="url" name="settings[%2$s]" value="%3$s"></label>', esc_html($label), esc_attr($name), esc_attr($value));
    }

    private function textarea(string $name, string $label, string $value, int $rows): void
    {
        printf('<label class="sabi-field"><span>%1$s</span><textarea class="large-text code" rows="%4$d" name="settings[%2$s]">%3$s</textarea></label>', esc_html($label), esc_attr($name), esc_textarea($value), $rows);
    }

    private function checkbox(string $name, string $label, bool $checked, string $description): void
    {
        printf('<label class="sabi-check"><input type="checkbox" name="settings[%1$s]" value="1" %2$s><span><strong>%3$s</strong><small>%4$s</small></span></label>', esc_attr($name), checked($checked, true, false), esc_html($label), esc_html($description));
    }

    private function color_field(string $name, string $label, string $value): void
    {
        printf('<label class="sabi-field sabi-color-field"><span>%1$s</span><span><input type="color" name="settings[%2$s]" value="%3$s"><code>%3$s</code></span></label>', esc_html($label), esc_attr($name), esc_attr($value));
    }

    private function number_field(string $name, string $label, int $value, int $min, int $max): void
    {
        printf('<label class="sabi-field"><span>%1$s</span><input class="small-text" type="number" name="settings[%2$s]" value="%3$d" min="%4$d" max="%5$d"></label>', esc_html($label), esc_attr($name), $value, $min, $max);
    }

    private function test_row(string $label, bool $success, string $detail): void
    {
        printf('<div class="sabi-test-row"><span class="dashicons %1$s" aria-hidden="true"></span><strong>%2$s</strong><span>%3$s</span></div>', esc_attr($success ? 'dashicons-yes-alt' : 'dashicons-warning'), esc_html($label), esc_html($detail));
    }

    private function program_label(string $program): string
    {
        return [
            'professional-nursing' => __('Professional Nursing', 'sabi-chatbot'),
            'physical-therapist-assistant' => __('Physical Therapist Assistant', 'sabi-chatbot'),
            'undecided' => __('Undecided', 'sabi-chatbot'),
        ][$program] ?? $program;
    }

    private static function csv_cell(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }
}

