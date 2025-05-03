<?php
if (!defined('ABSPATH')) {
    exit;
}

class AI_Content_Assistant {
    private $settings;
    private $article;

    public function __construct() {
        $this->settings = new AI_Content_Assistant_Settings();
        $this->article = new AI_Content_Assistant_Article();
    }

    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Initialize settings
        $this->settings->init();
        
        // Initialize article handling
        $this->article->init();
    }

    public function add_admin_menu() {
        add_menu_page(
            __('AI Content Assistant', 'ai-content-assistant'),
            __('AI Content', 'ai-content-assistant'),
            'manage_options',
            'ai-content-assistant',
            array($this->settings, 'render_settings_page'),
            'dashicons-format-aside'
        );

        add_submenu_page(
            'ai-content-assistant',
            __('Settings', 'ai-content-assistant'),
            __('Settings', 'ai-content-assistant'),
            'manage_options',
            'ai-content-assistant',
            array($this->settings, 'render_settings_page')
        );

        add_submenu_page(
            'ai-content-assistant',
            __('Create Article', 'ai-content-assistant'),
            __('Create Article', 'ai-content-assistant'),
            'edit_posts',
            'ai-content-assistant-article',
            array($this->article, 'render_article_page')
        );
    }
} 