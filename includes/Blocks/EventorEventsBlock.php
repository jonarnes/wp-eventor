<?php
namespace EventorIntegration\Blocks;

class EventorEventsBlock {
    private $plugin;

    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    public function init() {
        add_action('init', [$this, 'register_block']);
    }

    public function register_block() {
        if (!function_exists('register_block_type')) {
            return;
        }

        wp_register_script(
            'eventor-events-block-editor',
            EVENTOR_INTEGRATION_PLUGIN_URL . 'assets/js/blocks/build/events-block.js',
            [
                'wp-blocks',
                'wp-element',
                'wp-editor',
                'wp-components',
                'wp-i18n',
                'wp-block-editor'
            ],
            EVENTOR_INTEGRATION_VERSION
        );

        wp_register_style(
            'eventor-events-block',
            EVENTOR_INTEGRATION_PLUGIN_URL . 'assets/css/blocks/events-block.css',
            [],
            EVENTOR_INTEGRATION_VERSION
        );

        register_block_type('eventor-integration/events', [
            'editor_script' => 'eventor-events-block-editor',
            'editor_style'  => 'eventor-events-block-editor',
            'style'         => 'eventor-events-block',
            'attributes' => [
                'organisationIds' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'daysBack' => [
                    'type' => 'number',
                    'default' => 0
                ],
                'daysForward' => [
                    'type' => 'number',
                    'default' => 0
                ],
                'pastEventsCount' => [
                    'type' => 'number',
                    'default' => 0
                ],
                'layout' => [
                    'type' => 'string',
                    'default' => ''
                ]
            ],
            'render_callback' => function($attributes) {
                // Convert camelCase attributes to snake_case
                $converted_atts = [];
                if (isset($attributes['organisationIds'])) {
                    $converted_atts['organisation_ids'] = $attributes['organisationIds'];
                }
                if (isset($attributes['daysBack'])) {
                    $converted_atts['days_back'] = $attributes['daysBack'];
                }
                if (isset($attributes['daysForward'])) {
                    $converted_atts['days_forward'] = $attributes['daysForward'];
                }
                if (isset($attributes['pastEventsCount'])) {
                    $converted_atts['past_events_count'] = $attributes['pastEventsCount'];
                }
                if (isset($attributes['layout'])) {
                    $converted_atts['layout'] = $attributes['layout'];
                }
                
                return $this->plugin->render_events_shortcode($converted_atts);
            }
        ]);
    }
} 