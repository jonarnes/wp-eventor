<?php
namespace EventorIntegration\API;

class EventorAPI {
    private $api_url = 'https://eventor.orientering.no/api/';
    private $api_key;

    public function __construct() {
        $this->api_key = get_option('eventor_integration_api_key');
    }

    public function get_events($params = []) {
        $endpoint = 'events';
        
        // Get defaults from options
        $default_days_back = get_option('eventor_integration_days_back', 30);
        $default_days_forward = get_option('eventor_integration_days_forward', 90);
        $default_org_ids = get_option('eventor_integration_organisation_ids');
        
        // Use provided values or fall back to defaults
        $days_back = $params['days_back'] ?? $default_days_back;
        $days_forward = $params['days_forward'] ?? $default_days_forward;
        $org_ids = $params['organisation_ids'] ?? $default_org_ids;
        
        $query_params = [
            'fromDate' => date('Y-m-d', strtotime("-{$days_back} days")),
            'toDate' => date('Y-m-d', strtotime("+{$days_forward} days")),
            'organisationIds' => $org_ids,
        ];

        // Debug the API request parameters
        error_log('Eventor API request params: ' . print_r($query_params, true));

        return $this->make_request($endpoint, $query_params);
    }

    /**
     * Get organization details with caching
     * 
     * @param int $org_id Organization ID
     * @return object Organization data
     */
    public function get_organisation($org_id) {
        // Generate a unique transient key for this organization
        $cache_key = 'eventor_org_' . $org_id;
        
        // Try to get cached data
        $org_name = get_transient($cache_key) ;
        
        // If no cached data, fetch from API and cache it
        if ($org_name === false) {
            $endpoint = 'organisation/' . $org_id;
            $org_data = $this->make_request($endpoint);
            if ($org_data && !empty($org_data->Name)) {
                $org_name = (string)$org_data->Name;
                // Cache for 7 days (organization data rarely changes)
                set_transient($cache_key, $org_name, 7 * DAY_IN_SECONDS);
            }
        }
        
        return (object)['Name' => $org_name];
    }

    /**
     * Clear organization cache
     * 
     * @param int|null $org_id Optional specific organization ID to clear
     */
    public function clear_organisation_cache($org_id = null) {
        if ($org_id) {
            delete_transient('eventor_org_' . $org_id);
        } else {
            global $wpdb;
            $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_eventor_org_%'");
            $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_eventor_org_%'");
        }
    }

    private function make_request($endpoint, $params = []) {
        if (empty($this->api_key)) {
            throw new \Exception(__('API key is not configured', 'eventor-integration'));
        }

        $url = add_query_arg($params, $this->api_url . $endpoint);

        $response = wp_remote_get($url, [
            'headers' => [
                'ApiKey' => $this->api_key,
            ],
        ]);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = simplexml_load_string($body);

        if (!$data) {
            throw new \Exception(__('Invalid response from API', 'eventor-integration'));
        }

        return $data;
    }
}