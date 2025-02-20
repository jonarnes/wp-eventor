<?php
namespace EventorIntegration;

class Plugin {
    private $api;
    private $admin;

    public function init() {
        // Initialize API handler
        $this->api = new API\EventorAPI();
        
        // Initialize admin pages
        if (is_admin()) {
            $this->admin = new Admin\AdminPage();
            $this->admin->init();
        }

        // Register shortcodes
        $this->register_shortcodes();
    }

    private function register_shortcodes() {
        add_shortcode('eventor_events', [$this, 'render_events_shortcode']);
    }

    public function render_events_shortcode($atts) {
        // Debug the incoming attributes
        error_log('Eventor shortcode attributes: ' . print_r($atts, true));

        $attributes = shortcode_atts([
            'organisation_ids' => get_option('eventor_integration_organisation_ids'),
            'days_back' => get_option('eventor_integration_days_back', 30),
            'days_forward' => get_option('eventor_integration_days_forward', 90),
            'past_events_count' => get_option('eventor_integration_past_events_count', 1),
            'layout' => get_option('eventor_integration_default_layout', 'rich'),
        ], $atts);

        try {
            // Only override defaults if values are actually provided (not 0 or empty)
            $params = $attributes;
            
            if (!empty($attributes['organisation_ids'])) {
                $params['organisation_ids'] = $attributes['organisation_ids'];
            }
            
            if (isset($attributes['days_back']) && $attributes['days_back'] > 0) {
                $params['days_back'] = $attributes['days_back'];
            }
            
            if (isset($attributes['days_forward']) && $attributes['days_forward'] > 0) {
                $params['days_forward'] = $attributes['days_forward'];
            }
            
            if (isset($attributes['past_events_count']) && $attributes['past_events_count'] > 0) {
                $params['past_events_count'] = $attributes['past_events_count'];
            }

            // Debug the final parameters
            error_log('Eventor API parameters: ' . print_r($params, true));

            $events = $this->api->get_events($params);
            $api = $this->api;
            
            // Make past_events_count available to template
            $past_events_count = $params['past_events_count'] ?? get_option('eventor_integration_past_events_count', 1);
            
            ob_start();
            include EVENTOR_INTEGRATION_PLUGIN_DIR . 'templates/events-list.php';
            return ob_get_clean();
        } catch (\Exception $e) {
            return '<p class="error">' . esc_html__('Error fetching events:', 'eventor-integration') . ' ' . esc_html($e->getMessage()) . '</p>';
        }
    }
} 