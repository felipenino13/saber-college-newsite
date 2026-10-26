<?php
/**
 * Plugin Name: SABI Chatbot
 * Plugin URI: https://sabercollege.edu/
 * Description: Provides the configurable SABER College SABI chatbot experience.
 * Version: 0.6.1
 * Author: SABER College
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Text Domain: sabi-chatbot
 */

namespace SaberCollege\SabiChatbot;

if (!defined('ABSPATH')) {
    exit;
}

define('SABI_CHATBOT_VERSION', '0.6.1');
define('SABI_CHATBOT_FILE', __FILE__);
define('SABI_CHATBOT_PATH', plugin_dir_path(__FILE__));
define('SABI_CHATBOT_URL', plugin_dir_url(__FILE__));

require_once SABI_CHATBOT_PATH . 'includes/class-sabi-chatbot-settings.php';
require_once SABI_CHATBOT_PATH . 'includes/class-sabi-chatbot-database.php';
require_once SABI_CHATBOT_PATH . 'includes/class-sabi-chatbot-knowledge.php';
require_once SABI_CHATBOT_PATH . 'includes/class-sabi-chatbot-api.php';
require_once SABI_CHATBOT_PATH . 'includes/class-sabi-chatbot-activator.php';
require_once SABI_CHATBOT_PATH . 'includes/class-sabi-chatbot-conversation.php';
require_once SABI_CHATBOT_PATH . 'includes/class-sabi-chatbot-leads.php';
require_once SABI_CHATBOT_PATH . 'includes/class-sabi-chatbot-shortcode.php';
require_once SABI_CHATBOT_PATH . 'includes/class-sabi-chatbot-admin.php';
require_once SABI_CHATBOT_PATH . 'includes/class-sabi-chatbot-plugin.php';

register_activation_hook(SABI_CHATBOT_FILE, [Activator::class, 'activate']);
register_deactivation_hook(SABI_CHATBOT_FILE, [Activator::class, 'deactivate']);

add_action('plugins_loaded', static function (): void {
    Plugin::instance()->boot();
});

