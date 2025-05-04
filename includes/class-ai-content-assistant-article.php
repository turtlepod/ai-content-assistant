<?php
if (!defined('ABSPATH')) {
    exit;
}

class AI_Content_Assistant_Article {
    private $options;

    public function __construct() {
        $this->options = get_option('ai_content_assistant_options');
    }

    public function init() {
        add_action('admin_post_ai_content_assistant_generate_article', array($this, 'handle_article_generation'));
    }

    public function render_article_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'ai-content-assistant'));
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Create New Article', 'ai-content-assistant'); ?></h1>
            
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="ai_content_assistant_generate_article">
                <?php wp_nonce_field('ai_content_assistant_generate_article', 'ai_content_assistant_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="article_source"><?php echo esc_html__('Article Source', 'ai-content-assistant'); ?></label>
                        </th>
                        <td>
                            <textarea name="article_source" id="article_source" rows="10" class="large-text" required></textarea>
                            <p class="description"><?php echo esc_html__('Enter the source article text or HTML content.', 'ai-content-assistant'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="prompt"><?php echo esc_html__('Prompt', 'ai-content-assistant'); ?></label>
                        </th>
                        <td>
                            <textarea name="prompt" id="prompt" rows="5" class="large-text" required></textarea>
                            <p class="description"><?php echo esc_html__('Enter the prompt to modify the article (e.g., change tone, add information).', 'ai-content-assistant'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="category"><?php echo esc_html__('Category', 'ai-content-assistant'); ?></label>
                        </th>
                        <td>
                            <?php
                            wp_dropdown_categories(array(
                                'name' => 'category',
                                'id' => 'category',
                                'show_option_none' => __('Select a category', 'ai-content-assistant'),
                                'hide_empty' => 0,
                                'hierarchical' => 1
                            ));
                            ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="tags"><?php echo esc_html__('Tags', 'ai-content-assistant'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="tags" id="tags" class="regular-text">
                            <p class="description"><?php echo esc_html__('Enter tags separated by commas.', 'ai-content-assistant'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Generate Article', 'ai-content-assistant')); ?>
            </form>
        </div>
        <?php
    }

    public function handle_article_generation() {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'ai-content-assistant'));
        }

        if (!isset($_POST['ai_content_assistant_nonce']) || !wp_verify_nonce($_POST['ai_content_assistant_nonce'], 'ai_content_assistant_generate_article')) {
            wp_die(__('Security check failed.', 'ai-content-assistant'));
        }

        $article_source = isset($_POST['article_source']) ? wp_kses_post($_POST['article_source']) : '';
        $prompt = isset($_POST['prompt']) ? sanitize_text_field($_POST['prompt']) : '';
        $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
        $tags = isset($_POST['tags']) ? sanitize_text_field($_POST['tags']) : '';

        if (empty($article_source) || empty($prompt)) {
            wp_die(__('Article source and prompt are required.', 'ai-content-assistant'));
        }

        // Get API settings
        $api_key = isset($this->options['api_key']) ? $this->options['api_key'] : '';
        $model = isset($this->options['model']) ? $this->options['model'] : 'gpt-3.5-turbo';

        if (empty($api_key)) {
            wp_die(__('OpenAI API key is not configured.', 'ai-content-assistant'));
        }

        // Prepare the prompt for OpenAI
        $system_prompt = "You are a content editor. Your task is to modify the given article according to the provided instructions. Maintain the original structure and formatting while implementing the requested changes.";
        $user_prompt = "Article to modify:\n\n" . $article_source . "\n\nInstructions: " . $prompt;

        // Call OpenAI API
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => $model,
                'messages' => array(
                    array('role' => 'system', 'content' => $system_prompt),
                    array('role' => 'user', 'content' => $user_prompt)
                ),
                'temperature' => 0.7,
            )),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            wp_die($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($body['choices'][0]['message']['content'])) {
            wp_die(__('Failed to generate content from OpenAI.', 'ai-content-assistant'));
        }

        $generated_content = $body['choices'][0]['message']['content'];

        // Create a new post
        $post_data = array(
            'post_title'    => wp_strip_all_tags($prompt),
            'post_content'  => $generated_content,
            'post_status'   => 'draft',
            'post_author'   => get_current_user_id(),
            'post_category' => array($category),
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            wp_die($post_id->get_error_message());
        }

        // Add tags
        if (!empty($tags)) {
            wp_set_post_tags($post_id, $tags);
        }

        // Add excerpt
        $excerpt = wp_trim_words($generated_content, 55);
        update_post_meta($post_id, '_ai_content_assistant_excerpt', $excerpt);

        // Redirect to the edit page of the new post
        wp_redirect(admin_url('post.php?post=' . $post_id . '&action=edit'));
        exit;
    }
} 