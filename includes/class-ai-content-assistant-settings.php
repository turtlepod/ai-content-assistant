<?php
if (!defined('ABSPATH')) {
    exit;
}

class AI_Content_Assistant_Settings {
    private $options;

    public function __construct() {
        $this->options = get_option('ai_content_assistant_options');
    }

    public function init() {
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting(
            'ai_content_assistant_options',
            'ai_content_assistant_options',
            array($this, 'sanitize_options')
        );

        add_settings_section(
            'ai_content_assistant_api_section',
            __('API Configuration', 'ai-content-assistant'),
            array($this, 'render_api_section'),
            'ai-content-assistant'
        );

        add_settings_field(
            'api_key',
            __('OpenAI API Key', 'ai-content-assistant'),
            array($this, 'render_api_key_field'),
            'ai-content-assistant',
            'ai_content_assistant_api_section'
        );

        add_settings_field(
            'model',
            __('Model', 'ai-content-assistant'),
            array($this, 'render_model_field'),
            'ai-content-assistant',
            'ai_content_assistant_api_section'
        );
    }

    public function sanitize_options($input) {
        $sanitized = array();
        
        if (isset($input['api_key'])) {
            $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        }
        
        if (isset($input['model'])) {
            $sanitized['model'] = sanitize_text_field($input['model']);
        }
        
        return $sanitized;
    }

    public function render_api_section() {
        echo '<p>' . __('Configure your OpenAI API settings below.', 'ai-content-assistant') . '</p>';
    }

    public function render_api_key_field() {
        $api_key = isset($this->options['api_key']) ? $this->options['api_key'] : '';
        echo '<input type="password" id="api_key" name="ai_content_assistant_options[api_key]" value="' . esc_attr($api_key) . '" class="regular-text">';
        echo '<p class="description">' . __('Enter your OpenAI API key. This will be used to authenticate with the OpenAI API.', 'ai-content-assistant') . '</p>';
    }

    public function render_model_field() {
        $model = isset($this->options['model']) ? $this->options['model'] : 'gpt-3.5-turbo';
        echo '<select id="model" name="ai_content_assistant_options[model]">';
        echo '<option value="gpt-3.5-turbo" ' . selected($model, 'gpt-3.5-turbo', false) . '>GPT-3.5 Turbo</option>';
        echo '<option value="gpt-4" ' . selected($model, 'gpt-4', false) . '>GPT-4</option>';
        echo '</select>';
        echo '<p class="description">' . __('Select the OpenAI model to use for content generation.', 'ai-content-assistant') . '</p>';
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('ai_content_assistant_options');
                do_settings_sections('ai-content-assistant');
                submit_button(__('Save Settings', 'ai-content-assistant'));
                ?>
            </form>
        </div>
        <?php
    }
} 