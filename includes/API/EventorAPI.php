<?php
namespace EventorIntegration\API;

/** 
 * https://eventor.orientering.no/api/competitorcount?organisationIds=303&eventids=20955
 *  - hente antall deltakere på en bestemt arrangement
 * 
 * 
*/


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
        // If days_back or days_forward is 0, use the default value
        $days_back = (!empty($params['days_back']) && $params['days_back'] > 0) ? $params['days_back'] : $default_days_back;
        $days_forward = (!empty($params['days_forward']) && $params['days_forward'] > 0) ? $params['days_forward'] : $default_days_forward;
        $org_ids = $params['organisation_ids'] ?? $default_org_ids;
        
        $query_params = [
            'fromDate' => date('Y-m-d', strtotime("-{$days_back} days")),
            'toDate' => date('Y-m-d', strtotime("+{$days_forward} days")),
            'organisationIds' => $org_ids,
        ];

        // Debug the API request parameters
        //error_log('Eventor API request params: ' . print_r($query_params, true));

        return $this->make_request($endpoint, $query_params);
    }

    /**
     * Get organization details with caching
     * 
     * @param int $org_id Organization ID
     * @return object Organization data
     */
    public function get_organisation($org_id) {
        //error_log('Getting organization data for ID: ' . $org_id);
        
        // Generate a unique transient key for this organization
        $cache_key = 'eventor_org_' . $org_id;
        
        // Try to get cached data
        $org_data = get_transient($cache_key);
        
        // Handle old cache format (string) or missing data
        if ($org_data === false || is_string($org_data)) {
            //error_log('No cached data found or old format, fetching from API');
            $endpoint = 'organisation/' . $org_id;
            try {
                $xml_data = $this->make_request($endpoint);
                //error_log('API response: ' . print_r($xml_data, true));
                if ($xml_data) {
                    $org_data = (object)[
                        'Name' => (string)$xml_data->Name,
                        'WebURL' => (string)$xml_data->WebURL
                    ];
                    //error_log('Organization data created: ' . print_r($org_data, true));
                    // Cache for 7 days (organization data rarely changes)
                    set_transient($cache_key, $org_data, 7 * DAY_IN_SECONDS);
                }
            } catch (\Exception $e) {
                error_log('Error fetching organization data: ' . $e->getMessage());
                return null;
            }
        } else if (is_string($org_data)) {
            // Convert old format to new format
            $org_data = (object)[
                'Name' => $org_data,
                'WebURL' => ''
            ];
        }
        
        //error_log('Returning organization data: ' . print_r($org_data, true));
        return $org_data;
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

    /**
     * Get a single event by ID
     * 
     * @param int $event_id The event ID
     * @return object|null Event data
     */
    public function get_single_event($event_id) {
        $endpoint = 'event/' . $event_id;
        
        try {
            $event = $this->make_request($endpoint);
            return $event;
        } catch (\Exception $e) {
            error_log('Eventor API error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get documents for an event
     *
     * @param int $eventId The event ID
     * @return SimpleXMLElement|false
     */
    public function get_event_documents($eventId) {
        return $this->make_request('events/documents', ['eventIds' => (string)$eventId]);
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