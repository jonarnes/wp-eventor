<?php
namespace EventorIntegration\Admin;

class AdminPage {
    public function init() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_eventor_clear_cache', [$this, 'handle_clear_cache']);
    }

    public function add_admin_menu() {
        add_options_page(
            __('Eventor Integration Settings', 'eventor-integration'),
            __('Eventor Integration', 'eventor-integration'),
            'manage_options',
            'eventor-integration',
            [$this, 'render_admin_page']
        );
    }

    public function register_settings() {
        // Add a callback when settings are updated
        foreach (['api_key', 'organisation_ids', 'days_back', 'days_forward', 'past_events_count'] as $setting) {
            register_setting(
                'eventor_integration_options', 
                'eventor_integration_' . $setting,
                [
                    'sanitize_callback' => function($value) {
                        // Remove the default "Settings saved." message
                        remove_action('admin_notices', 'settings_errors');
                        remove_action('all_admin_notices', 'settings_errors');
                        return $value;
                    }
                ]
            );
        }
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        include EVENTOR_INTEGRATION_PLUGIN_DIR . 'templates/admin-page.php';
    }

    public function handle_clear_cache() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        check_admin_referer('eventor_clear_cache');
        
        // Clear the cache
        $api = new \EventorIntegration\API\EventorAPI();
        $api->clear_organisation_cache();
        
        // Redirect back with success message
        add_settings_error(
            'eventor_messages',
            'eventor_cache_cleared',
            __('Organization cache has been cleared.', 'eventor-integration'),
            'success'
        );
        
        set_transient('settings_errors', get_settings_errors(), 30);
        
        wp_redirect(add_query_arg([
            'page' => 'eventor-integration',
            'settings-updated' => 1
        ], admin_url('options-general.php')));
        exit;
    }
} 