<?php
/**
 * Messaging System Admin Interface
 *
 * Provides an admin interface for configuring the toast messaging system.
 *
 * @package Hello_Child_Theme
 * @subpackage Messaging/Admin
 */

namespace Lilac\Messaging\Admin;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class MessagingAdmin {
    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Option name for storing message settings
     */
    private $option_name = 'lilac_messaging_settings';

    /**
     * Get instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add settings link on plugin page
        add_filter('plugin_action_links_hello-theme-child-master/style.css', array($this, 'add_settings_link'));
    }

    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Messaging System', 'hello-child'),
            __('Messaging', 'hello-child'),
            'manage_options',
            'lilac-messaging',
            array($this, 'admin_page_display'),
            'dashicons-megaphone',
            30
        );
        
        // Add submenus
        add_submenu_page(
            'lilac-messaging',
            __('Welcome Messages', 'hello-child'),
            __('Welcome Messages', 'hello-child'),
            'manage_options',
            'lilac-messaging',
            array($this, 'admin_page_display')
        );
        
        add_submenu_page(
            'lilac-messaging',
            __('Contextual Messages', 'hello-child'),
            __('Contextual Messages', 'hello-child'),
            'manage_options',
            'lilac-messaging-contextual',
            array($this, 'contextual_page_display')
        );
        
        add_submenu_page(
            'lilac-messaging',
            __('Progress Notifications', 'hello-child'),
            __('Progress Notifications', 'hello-child'),
            'manage_options',
            'lilac-messaging-progress',
            array($this, 'progress_page_display')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('lilac_messaging_settings', $this->option_name);
        
        // Welcome Message Settings
        add_settings_section(
            'lilac_messaging_welcome_section',
            __('Welcome Message Settings', 'hello-child'),
            array($this, 'welcome_section_callback'),
            'lilac-messaging'
        );
        
        add_settings_field(
            'welcome_message_enabled',
            __('Enable Welcome Message', 'hello-child'),
            array($this, 'checkbox_field_callback'),
            'lilac-messaging',
            'lilac_messaging_welcome_section',
            array(
                'label_for' => 'welcome_message_enabled',
                'field_name' => $this->option_name . '[welcome_message_enabled]'
            )
        );
        
        add_settings_field(
            'welcome_message_title',
            __('Welcome Message Title', 'hello-child'),
            array($this, 'text_field_callback'),
            'lilac-messaging',
            'lilac_messaging_welcome_section',
            array(
                'label_for' => 'welcome_message_title',
                'field_name' => $this->option_name . '[welcome_message_title]'
            )
        );
        
        add_settings_field(
            'welcome_message_content',
            __('Welcome Message Content', 'hello-child'),
            array($this, 'textarea_field_callback'),
            'lilac-messaging',
            'lilac_messaging_welcome_section',
            array(
                'label_for' => 'welcome_message_content',
                'field_name' => $this->option_name . '[welcome_message_content]',
                'description' => __('Supports HTML. Use this to welcome users to your site.', 'hello-child')
            )
        );
        
        add_settings_field(
            'welcome_message_type',
            __('Message Type', 'hello-child'),
            array($this, 'select_field_callback'),
            'lilac-messaging',
            'lilac_messaging_welcome_section',
            array(
                'label_for' => 'welcome_message_type',
                'field_name' => $this->option_name . '[welcome_message_type]',
                'options' => array(
                    'info' => __('Info (Blue)', 'hello-child'),
                    'success' => __('Success (Green)', 'hello-child'),
                    'warning' => __('Warning (Yellow)', 'hello-child'),
                    'error' => __('Error (Red)', 'hello-child')
                )
            )
        );
        
        add_settings_field(
            'welcome_message_auto_close',
            __('Auto Close After (seconds)', 'hello-child'),
            array($this, 'number_field_callback'),
            'lilac-messaging',
            'lilac_messaging_welcome_section',
            array(
                'label_for' => 'welcome_message_auto_close',
                'field_name' => $this->option_name . '[welcome_message_auto_close]',
                'description' => __('Set to 0 to disable auto-close', 'hello-child'),
                'min' => 0,
                'max' => 60
            )
        );
        
        // Display Conditions
        add_settings_section(
            'lilac_messaging_display_section',
            __('Display Conditions', 'hello-child'),
            array($this, 'display_section_callback'),
            'lilac-messaging'
        );
        
        add_settings_field(
            'welcome_display_homepage',
            __('Show on Homepage', 'hello-child'),
            array($this, 'checkbox_field_callback'),
            'lilac-messaging',
            'lilac_messaging_display_section',
            array(
                'label_for' => 'welcome_display_homepage',
                'field_name' => $this->option_name . '[welcome_display_homepage]'
            )
        );
        
        add_settings_field(
            'welcome_display_once_per_session',
            __('Show Once Per Session', 'hello-child'),
            array($this, 'checkbox_field_callback'),
            'lilac-messaging',
            'lilac_messaging_display_section',
            array(
                'label_for' => 'welcome_display_once_per_session',
                'field_name' => $this->option_name . '[welcome_display_once_per_session]'
            )
        );
        
        add_settings_field(
            'welcome_specific_urls',
            __('Show on Specific URLs', 'hello-child'),
            array($this, 'textarea_field_callback'),
            'lilac-messaging',
            'lilac_messaging_display_section',
            array(
                'label_for' => 'welcome_specific_urls',
                'field_name' => $this->option_name . '[welcome_specific_urls]',
                'description' => __('Enter one URL per line. Leave empty to show on all pages.', 'hello-child')
            )
        );
    }

    /**
     * Section callbacks
     */
    public function welcome_section_callback() {
        echo '<p>' . __('Configure the welcome message that will be shown to users.', 'hello-child') . '</p>';
    }
    
    public function display_section_callback() {
        echo '<p>' . __('Control where and when messages are displayed.', 'hello-child') . '</p>';
    }

    /**
     * Field callbacks
     */
    public function text_field_callback($args) {
        $options = get_option($this->option_name);
        $field_name = $args['field_name'];
        $field_id = $args['label_for'];
        $value = isset($options[$field_id]) ? $options[$field_id] : '';
        
        echo '<input type="text" id="' . esc_attr($field_id) . '" name="' . esc_attr($field_name) . '" value="' . esc_attr($value) . '" class="regular-text">';
        
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    public function textarea_field_callback($args) {
        $options = get_option($this->option_name);
        $field_name = $args['field_name'];
        $field_id = $args['label_for'];
        $value = isset($options[$field_id]) ? $options[$field_id] : '';
        
        echo '<textarea id="' . esc_attr($field_id) . '" name="' . esc_attr($field_name) . '" rows="5" class="large-text">' . esc_textarea($value) . '</textarea>';
        
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    public function checkbox_field_callback($args) {
        $options = get_option($this->option_name);
        $field_name = $args['field_name'];
        $field_id = $args['label_for'];
        $checked = isset($options[$field_id]) && $options[$field_id] ? 'checked' : '';
        
        echo '<input type="checkbox" id="' . esc_attr($field_id) . '" name="' . esc_attr($field_name) . '" value="1" ' . $checked . '>';
        
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    public function select_field_callback($args) {
        $options = get_option($this->option_name);
        $field_name = $args['field_name'];
        $field_id = $args['label_for'];
        $value = isset($options[$field_id]) ? $options[$field_id] : '';
        
        echo '<select id="' . esc_attr($field_id) . '" name="' . esc_attr($field_name) . '">';
        
        foreach ($args['options'] as $option_value => $option_label) {
            $selected = ($value === $option_value) ? 'selected' : '';
            echo '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . esc_html($option_label) . '</option>';
        }
        
        echo '</select>';
        
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    public function number_field_callback($args) {
        $options = get_option($this->option_name);
        $field_name = $args['field_name'];
        $field_id = $args['label_for'];
        $value = isset($options[$field_id]) ? $options[$field_id] : 0;
        $min = isset($args['min']) ? 'min="' . intval($args['min']) . '"' : '';
        $max = isset($args['max']) ? 'max="' . intval($args['max']) . '"' : '';
        
        echo '<input type="number" id="' . esc_attr($field_id) . '" name="' . esc_attr($field_name) . '" value="' . esc_attr($value) . '" class="small-text" ' . $min . ' ' . $max . '>';
        
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Display the admin page
     */
    public function admin_page_display() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Show status message
        settings_errors('lilac_messaging_settings');
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_url(admin_url('admin.php?page=lilac-messaging')); ?>" class="nav-tab nav-tab-active"><?php _e('Welcome Message', 'hello-child'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=lilac-message-manager')); ?>" class="nav-tab"><?php _e('Message Management', 'hello-child'); ?></a>
            </h2>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('lilac_messaging_settings');
                do_settings_sections('lilac-messaging');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Display the contextual messages page
     */
    public function contextual_page_display() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p><?php _e('This page allows you to create context-specific messages based on user actions or page types.', 'hello-child'); ?></p>
            
            <div class="notice notice-info">
                <p><?php _e('Contextual messaging features are coming soon. This will allow you to create custom messages for specific post types, URLs, and user actions.', 'hello-child'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Display the progress notifications page
     */
    public function progress_page_display() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p><?php _e('Configure notifications for course progress and achievements.', 'hello-child'); ?></p>
            
            <div class="notice notice-info">
                <p><?php _e('Progress notification settings will be available soon. This will allow you to create custom messages for course completion, quiz results, and more.', 'hello-child'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Add settings link
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=lilac-messaging">' . __('Settings', 'hello-child') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

// Initialize the class
function lilac_messaging_admin() {
    return MessagingAdmin::get_instance();
}
lilac_messaging_admin();
