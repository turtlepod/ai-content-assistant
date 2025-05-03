<?php
/**
 * Plugin Name: AI Content Assistant
 * Plugin URI: https://yourwebsite.com/ai-content-assistant
 * Description: A WordPress plugin to integrate with ChatGPT for content generation and editing
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-content-assistant
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AI_CONTENT_ASSISTANT_VERSION', '1.0.0');
define('AI_CONTENT_ASSISTANT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AI_CONTENT_ASSISTANT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once AI_CONTENT_ASSISTANT_PLUGIN_DIR . 'includes/class-ai-content-assistant.php';
require_once AI_CONTENT_ASSISTANT_PLUGIN_DIR . 'includes/class-ai-content-assistant-settings.php';
require_once AI_CONTENT_ASSISTANT_PLUGIN_DIR . 'includes/class-ai-content-assistant-article.php';

// Initialize the plugin
function ai_content_assistant_init() {
    $plugin = new AI_Content_Assistant();
    $plugin->init();
}
add_action('plugins_loaded', 'ai_content_assistant_init');

// Activation hook
register_activation_hook(__FILE__, 'ai_content_assistant_activate');
function ai_content_assistant_activate() {
    // Add any activation tasks here
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'ai_content_assistant_deactivate');
function ai_content_assistant_deactivate() {
    // Add any deactivation tasks here
} 