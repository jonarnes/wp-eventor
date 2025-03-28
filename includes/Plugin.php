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

        // Add actions
        add_action('init', [$this, 'register_shortcodes']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    public function register_shortcodes() {
        add_shortcode('eventor_events', [$this, 'render_events_shortcode']);
        add_shortcode('eventor_event', [$this, 'render_single_event_shortcode']);
    }

    public function render_events_shortcode($atts) {
        // Convert shortcode attributes to match plugin settings
        $attributes = shortcode_atts([
            'organisation_ids' => get_option('eventor_integration_organisation_ids'),
            'days_back' => get_option('eventor_integration_days_back', 30),
            'days_forward' => get_option('eventor_integration_days_forward', 90),
            'past_events_count' => get_option('eventor_integration_past_events_count', 1),
            'layout' => get_option('eventor_integration_default_layout', 'rich'),
            // Also support camelCase versions for consistency with block
            'organisationIds' => '',
            'daysBack' => '',
            'daysForward' => '',
            'pastEventsCount' => '',
        ], $atts);

        // Use camelCase versions if provided (override snake_case)
        if (!empty($attributes['organisationIds'])) {
            $attributes['organisation_ids'] = $attributes['organisationIds'];
        }
        if (!empty($attributes['daysBack'])) {
            $attributes['days_back'] = $attributes['daysBack'];
        }
        if (!empty($attributes['daysForward'])) {
            $attributes['days_forward'] = $attributes['daysForward'];
        }
        if (!empty($attributes['pastEventsCount'])) {
            $attributes['past_events_count'] = $attributes['pastEventsCount'];
        }

        try {
            $events = $this->api->get_events($attributes);
            $api = $this->api;
            
            // Make attributes available to template
            $GLOBALS['eventor_layout'] = $attributes['layout'];
            
            ob_start();
            include EVENTOR_INTEGRATION_PLUGIN_DIR . 'templates/events-list.php';
            return ob_get_clean();
        } catch (\Exception $e) {
            return '<p class="error">' . esc_html__('Error fetching events:', 'eventor-integration') . ' ' . esc_html($e->getMessage()) . '</p>';
        }
    }

    public function render_single_event_shortcode($atts) {
        $attributes = shortcode_atts([
            'event_id' => 0,
        ], $atts);

        if (empty($attributes['event_id'])) {
            return '<p class="error">' . esc_html__('Event ID is required', 'eventor-integration') . '</p>';
        }

        try {
            $event = $this->api->get_single_event($attributes['event_id']);
            if (!$event) {
                return '<p class="error">' . esc_html__('Event not found', 'eventor-integration') . '</p>';
            }

            ob_start();
            include EVENTOR_INTEGRATION_PLUGIN_DIR . 'templates/single-event.php';
            return ob_get_clean();
        } catch (\Exception $e) {
            return '<p class="error">' . esc_html__('Error fetching event:', 'eventor-integration') . ' ' . esc_html($e->getMessage()) . '</p>';
        }
    }

    public function enqueue_styles() {
        // Generate the CSS content
        ob_start();
        $css = ob_get_clean();

        // Add the CSS inline
        wp_register_style('eventor-integration-styles', false);
        wp_enqueue_style('eventor-integration-styles');
        wp_add_inline_style('eventor-integration-styles', $css);

        // Enqueue dashicons for the icons
        wp_enqueue_style('dashicons');
    }
} 