<?php

namespace SaberCollege\SabiChatbot;

if (!defined('ABSPATH')) {
    exit;
}

final class Shortcode
{
    public function register(): void
    {
        add_shortcode('sabi_chatbot', [$this, 'render']);
    }

    /**
     * Renders the conversation interface and its server-side API configuration.
     *
     * @param array<string, mixed>|string $attributes Shortcode attributes.
     */
    public function render(array|string $attributes = []): string
    {
        $attributes = shortcode_atts(
            ['preview' => '0'],
            is_array($attributes) ? $attributes : [],
            'sabi_chatbot'
        );
        $settings = Settings::get();
        $is_admin_preview = current_user_can(Admin::CAPABILITY)
            && ('1' === $attributes['preview'] || !empty($settings['general']['preview_mode']));

        if (empty($settings['general']['enabled']) && !$is_admin_preview) {
            return '';
        }

        wp_enqueue_style(
            'sabi-chatbot-public',
            SABI_CHATBOT_URL . 'assets/css/sabi-chatbot.css',
            [],
            SABI_CHATBOT_VERSION
        );
        wp_enqueue_script(
            'sabi-chatbot-public',
            SABI_CHATBOT_URL . 'assets/js/sabi-chatbot.js',
            [],
            SABI_CHATBOT_VERSION,
            true
        );

        $appearance = $settings['appearance'];
        $content = $settings['content'];
        $general = $settings['general'];
        $is_embedded_preview = '1' === $attributes['preview'];
        $instance_id = wp_unique_id('sabi-chatbot-');
        $panel_id = $instance_id . '-panel';
        $input_id = $instance_id . '-input';
        $config_id = $instance_id . '-config';
        $conversation = Conversation::config(
            $content['welcome_message'],
            $content['input_placeholder'],
            $content['compliance_text'],
            (int) $settings['security']['max_input_length']
        );
        $conversation['leadCapture'] = [
            'enabled' => !empty($settings['leads']['enabled']),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sabi_chatbot_submit_lead'),
            'consent' => [
                'en' => (string) $settings['leads']['consent_text_en'],
                'es' => (string) $settings['leads']['consent_text_es'],
            ],
        ];
        $conversation['ai'] = [
            'enabled' => Api::is_ready(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sabi_chatbot_message'),
        ];
        $position_class = 'bottom-left' === $appearance['position']
            ? 'sabi-chatbot--left'
            : 'sabi-chatbot--right';
        $root_classes = [
            'sabi-chatbot',
            $position_class,
        ];
        if ($is_embedded_preview) {
            $root_classes[] = 'sabi-chatbot--embedded';
        }
        $style = sprintf(
            '--sabi-primary:%1$s;--sabi-accent:%2$s;--sabi-surface:%3$s;',
            esc_attr($appearance['primary_color']),
            esc_attr($appearance['accent_color']),
            esc_attr($appearance['surface_color'])
        );

        ob_start();
        ?>
        <div
            id="<?php echo esc_attr($instance_id); ?>"
            class="<?php echo esc_attr(implode(' ', $root_classes)); ?>"
            style="<?php echo esc_attr($style); ?>"
            data-sabi-chatbot
            data-sabi-embedded="<?php echo $is_embedded_preview ? 'true' : 'false'; ?>"
            data-sabi-config-id="<?php echo esc_attr($config_id); ?>"
            data-chat-state="ready"
        >
            <?php if ($is_embedded_preview) : ?>
                <div class="sabi-chatbot__demo-controls" aria-label="<?php esc_attr_e('Preview states', 'sabi-chatbot'); ?>">
                    <span><?php esc_html_e('Preview state', 'sabi-chatbot'); ?></span>
                    <button type="button" class="is-active" data-sabi-demo-state="ready"><?php esc_html_e('Ready', 'sabi-chatbot'); ?></button>
                    <button type="button" data-sabi-demo-state="typing"><?php esc_html_e('Typing', 'sabi-chatbot'); ?></button>
                    <button type="button" data-sabi-demo-state="loading"><?php esc_html_e('Loading', 'sabi-chatbot'); ?></button>
                    <button type="button" data-sabi-demo-state="error"><?php esc_html_e('Error', 'sabi-chatbot'); ?></button>
                </div>
            <?php endif; ?>

            <button
                type="button"
                class="sabi-chatbot__launcher"
                aria-controls="<?php echo esc_attr($panel_id); ?>"
                aria-expanded="<?php echo $is_embedded_preview ? 'true' : 'false'; ?>"
                aria-label="<?php esc_attr_e('Open SABI virtual guide', 'sabi-chatbot'); ?>"
                data-sabi-toggle
            >
                <span class="sabi-chatbot__launcher-label"><?php esc_html_e('Ask SABI', 'sabi-chatbot'); ?></span>
                <span class="sabi-chatbot__launcher-avatar">
                    <img src="<?php echo esc_url($appearance['avatar_url']); ?>" alt="" width="62" height="62">
                    <span class="sabi-chatbot__online-dot" aria-hidden="true"></span>
                </span>
            </button>

            <section
                id="<?php echo esc_attr($panel_id); ?>"
                class="sabi-chatbot__panel"
                role="dialog"
                aria-label="<?php echo esc_attr($general['assistant_name'] . ' - SABER College virtual guide'); ?>"
                data-sabi-panel
                <?php echo $is_embedded_preview ? '' : 'hidden'; ?>
            >
                <header class="sabi-chatbot__header">
                    <span class="sabi-chatbot__brand-mark" aria-hidden="true"></span>
                    <img class="sabi-chatbot__avatar" src="<?php echo esc_url($appearance['avatar_url']); ?>" alt="" width="54" height="54">
                    <div class="sabi-chatbot__identity">
                        <strong><?php echo esc_html($general['assistant_name']); ?></strong>
                        <span><i aria-hidden="true"></i><?php esc_html_e('SABER College virtual guide', 'sabi-chatbot'); ?></span>
                    </div>
                    <div class="sabi-chatbot__language" role="group" aria-label="<?php esc_attr_e('Conversation language', 'sabi-chatbot'); ?>">
                        <button type="button" class="is-active" data-sabi-language="en" aria-pressed="true">EN</button>
                        <button type="button" data-sabi-language="es" aria-pressed="false">ES</button>
                    </div>
                    <button type="button" class="sabi-chatbot__close" aria-label="<?php esc_attr_e('Close SABI virtual guide', 'sabi-chatbot'); ?>" data-sabi-close>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.4 5 12 10.6 17.6 5 19 6.4 13.4 12 19 17.6 17.6 19 12 13.4 6.4 19 5 17.6 10.6 12 5 6.4Z"/></svg>
                    </button>
                </header>

                <?php if (empty($general['enabled'])) : ?>
                    <div class="sabi-chatbot__preview-banner"><?php esc_html_e('Administrator preview - not visible to visitors', 'sabi-chatbot'); ?></div>
                <?php endif; ?>

                <div class="sabi-chatbot__messages" data-sabi-messages>
                    <div class="sabi-chatbot__welcome sabi-chatbot__message sabi-chatbot__message--assistant" data-sabi-welcome>
                        <span class="sabi-chatbot__message-avatar" aria-hidden="true">
                            <img src="<?php echo esc_url($appearance['avatar_url']); ?>" alt="" width="32" height="32">
                        </span>
                        <div class="sabi-chatbot__bubble">
                            <p><?php echo esc_html($content['welcome_message']); ?></p>
                            <time><?php esc_html_e('Now', 'sabi-chatbot'); ?></time>
                        </div>
                    </div>

                    <div class="sabi-chatbot__quick-replies" data-sabi-quick-replies></div>

                    <div class="sabi-chatbot__state sabi-chatbot__state--typing" data-sabi-state="typing" aria-label="<?php esc_attr_e('SABI is typing', 'sabi-chatbot'); ?>">
                        <span></span><span></span><span></span>
                    </div>
                    <div class="sabi-chatbot__state sabi-chatbot__state--loading" data-sabi-state="loading">
                        <span class="sabi-chatbot__spinner" aria-hidden="true"></span>
                        <span><?php esc_html_e('Preparing a response...', 'sabi-chatbot'); ?></span>
                    </div>
                    <div class="sabi-chatbot__state sabi-chatbot__state--error" data-sabi-state="error" role="status">
                        <strong><?php esc_html_e('SABI could not connect.', 'sabi-chatbot'); ?></strong>
                        <span><?php esc_html_e('Please try again later or contact SABER College.', 'sabi-chatbot'); ?></span>
                    </div>
                </div>

                <form class="sabi-chatbot__composer" data-sabi-form>
                    <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>"><?php esc_html_e('Message for SABI', 'sabi-chatbot'); ?></label>
                    <input
                        id="<?php echo esc_attr($input_id); ?>"
                        type="text"
                        placeholder="<?php echo esc_attr($content['input_placeholder']); ?>"
                        maxlength="<?php echo esc_attr((string) $settings['security']['max_input_length']); ?>"
                        autocomplete="off"
                        aria-describedby="<?php echo esc_attr($input_id . '-notice'); ?>"
                        data-sabi-input
                    >
                    <button type="submit" aria-label="<?php esc_attr_e('Send message', 'sabi-chatbot'); ?>" data-sabi-send>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3.4 3.1 21 11.2a.9.9 0 0 1 0 1.6L3.4 20.9a.9.9 0 0 1-1.2-1.1l2.1-6.4 8.4-1.4-8.4-1.4-2.1-6.4a.9.9 0 0 1 1.2-1.1Z"/></svg>
                    </button>
                </form>
                <p id="<?php echo esc_attr($input_id . '-notice'); ?>" class="sabi-chatbot__notice" data-sabi-notice><?php echo esc_html($content['compliance_text']); ?></p>
                <footer class="sabi-chatbot__footer">
                    <span><?php esc_html_e('Powered by SABER College', 'sabi-chatbot'); ?></span>
                    <button type="button" data-sabi-reset><?php esc_html_e('Start over', 'sabi-chatbot'); ?></button>
                    <a href="<?php echo esc_url($settings['security']['privacy_url']); ?>" data-sabi-privacy><?php esc_html_e('Privacy', 'sabi-chatbot'); ?></a>
                </footer>
            </section>
            <span class="screen-reader-text" aria-live="polite" data-sabi-announcer></span>
            <script type="application/json" id="<?php echo esc_attr($config_id); ?>" data-sabi-config><?php echo wp_json_encode($conversation, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}

