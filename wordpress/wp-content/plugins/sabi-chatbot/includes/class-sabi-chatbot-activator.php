<?php

namespace SaberCollege\SabiChatbot;

if (!defined('ABSPATH')) {
    exit;
}

final class Activator
{
    public static function activate(): void
    {
        if (get_option(Settings::OPTION_NAME, null) === null) {
            add_option(Settings::OPTION_NAME, Settings::defaults(), '', false);
        }

        update_option('sabi_chatbot_version', SABI_CHATBOT_VERSION, false);
        Database::install();

        if (get_option('sabi_chatbot_installed_at', null) === null) {
            add_option('sabi_chatbot_installed_at', gmdate('c'), '', false);
        }

        $administrator = get_role('administrator');
        if ($administrator && !$administrator->has_cap(Admin::CAPABILITY)) {
            $administrator->add_cap(Admin::CAPABILITY);
        }
    }

    public static function deactivate(): void
    {
        // Settings and capabilities are intentionally preserved for safe rollback.
    }
}

