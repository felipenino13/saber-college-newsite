<?php

namespace SaberCollege\SabiChatbot;

if (!defined('ABSPATH')) {
    exit;
}

final class Plugin
{
    private static ?Plugin $instance = null;

    private bool $booted = false;

    public static function instance(): Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        if (get_option('sabi_chatbot_version') !== SABI_CHATBOT_VERSION) {
            update_option('sabi_chatbot_version', SABI_CHATBOT_VERSION, false);
        }

        Database::maybe_install();
        Knowledge::maybe_import();
        (new Api())->register();
        (new Shortcode())->register();
        (new Leads())->register();

        if (is_admin()) {
            (new Admin())->register();
        }

        add_filter('plugin_action_links_' . plugin_basename(SABI_CHATBOT_FILE), [$this, 'add_settings_link']);
    }

    /**
     * @param string[] $links Plugin action links.
     * @return string[]
     */
    public function add_settings_link(array $links): array
    {
        $url = admin_url('admin.php?page=sabi-chatbot');
        array_unshift($links, '<a href="' . esc_url($url) . '">' . esc_html__('Settings', 'sabi-chatbot') . '</a>');

        return $links;
    }
}

