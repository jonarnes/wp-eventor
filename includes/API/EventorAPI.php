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
    private $cache_ttl;

    public function __construct() {
        $this->api_key = get_option('eventor_integration_api_key');
        $this->cache_ttl = get_option('eventor_integration_cache_ttl', 24) * HOUR_IN_SECONDS;
    }

    /**
     * Get cached data or fetch from API
     * 
     * @param string $cache_key Cache key
     * @param callable $fetch_callback Callback to fetch data if not cached
     * @param int|null $ttl Time to live in seconds (null for default)
     * @return \SimpleXMLElement|false
     */
    private function get_cached_data($cache_key, $fetch_callback, $ttl = null) {
        $cache_key = 'eventor_' . $cache_key;
        $cached_data = get_transient($cache_key);
        
        if ($cached_data !== false) {
            // Convert cached string back to SimpleXMLElement
            return simplexml_load_string($cached_data);
        }
        
        $data = $fetch_callback();
        
        if ($data !== false) {
            // Use custom TTL if provided, otherwise use default
            $cache_duration = $ttl !== null ? $ttl : $this->cache_ttl;
            // Convert SimpleXMLElement to string before caching
            set_transient($cache_key, $data->asXML(), $cache_duration);
        }
        
        return $data;
    }

    /**
     * Get events from Eventor API
     * 
     * @param int $days_back Days back to fetch events
     * @param int $days_forward Days forward to fetch events
     * @param string|array $organisation_ids Optional organisation IDs to filter by
     * @return \SimpleXMLElement|false
     */
    public function get_events($days_back = null, $days_forward = null, $organisation_ids = null) {
        // Get default values from admin settings
        $default_days_back = get_option('eventor_integration_days_back', 30);
        $default_days_forward = get_option('eventor_integration_days_forward', 30);
        $default_org_ids = get_option('eventor_integration_organisation_ids', '');

        // Handle days_back and days_forward
        $days_back_num = $days_back === null ? $default_days_back : intval($days_back);
        $days_forward_num = $days_forward === null ? $default_days_forward : intval($days_forward);

        // If values are 0, use defaults
        if ($days_back_num === 0) {
            $days_back_num = $default_days_back;
        }
        if ($days_forward_num === 0) {
            $days_forward_num = $default_days_forward;
        }

        // Handle organisation IDs - prioritize provided IDs over defaults
        $org_ids = $organisation_ids;
        if ($org_ids === null || $org_ids === '') {
            $org_ids = $default_org_ids;
        }

        // Convert to string for cache key
        $org_ids_str = is_array($org_ids) ? implode(',', $org_ids) : (string)$org_ids;

        return $this->get_cached_data(
            'events_' . $days_back_num . '_' . $days_forward_num . '_' . $org_ids_str,
            function() use ($days_back_num, $days_forward_num, $org_ids) {
                // Calculate dates based on current date
                $from_date = date('Y-m-d', strtotime("-{$days_back_num} days"));
                $to_date = date('Y-m-d', strtotime("+{$days_forward_num} days"));

                $params = [
                    'fromDate' => $from_date,
                    'toDate' => $to_date
                ];

                // Add organisation IDs if provided
                if (!empty($org_ids)) {
                    $params['organisationIds'] = is_array($org_ids) ? implode(',', $org_ids) : $org_ids;
                }

                return $this->make_request('events', $params);
            }
        );
    }

    /**
     * Get a single event by ID
     * 
     * @param int $event_id The event ID
     * @return \SimpleXMLElement|false
     */
    public function get_single_event($event_id) {
        // Convert potential array value to string
        $event_id_str = is_array($event_id) ? implode(',', $event_id) : (string)$event_id;

        return $this->get_cached_data(
            'event_' . $event_id_str,
            function() use ($event_id) {
                return $this->make_request('event/' . $event_id);
            },
            10 // Cache for 10 seconds only
        );
    }

    /**
     * Get organisation data from Eventor API
     * 
     * @param int $organisation_id Organisation ID
     * @return \SimpleXMLElement|false
     */
    public function get_organisation($organisation_id) {
        return $this->get_cached_data(
            'organisation_' . (string)$organisation_id,
            function() use ($organisation_id) {
                return $this->make_request('organisation/' . $organisation_id);
            }
        );
    }

    /**
     * Get event documents from Eventor API
     * 
     * @param int $event_id Event ID
     * @return \SimpleXMLElement|false
     */
    public function get_event_documents($event_id) {
        // Convert potential array value to string
        $event_id_str = is_array($event_id) ? implode(',', $event_id) : (string)$event_id;

        return $this->get_cached_data(
            'event_documents_' . $event_id_str,
            function() use ($event_id_str) {
                return $this->make_request('events/documents', ['eventIds' => $event_id_str]);
            },
            10 // Cache for 10 seconds only
        );
    }

    /**
     * Get competitor count for an event
     * 
     * @param int $event_id Event ID
     * @return \SimpleXMLElement|false
     */
    public function get_competitor_count($event_id) {
        // Convert potential array value to string
        $event_id_str = is_array($event_id) ? implode(',', $event_id) : (string)$event_id;

        return $this->get_cached_data(
            'competitor_count_' . $event_id_str,
            function() use ($event_id_str) {
                return $this->make_request('competitorcount', ['eventids' => $event_id_str]);
            },
            3600 // Cache for 1 hour
        );
    }

    private function make_request($endpoint, $params = []) {
        if (empty($this->api_key)) {
            throw new \Exception(__('API key is not configured', 'eventor-integration'));
        }

        // Remove any leading slash from endpoint as it's already in the base URL
        $endpoint = ltrim($endpoint, '/');
        $url = $this->api_url . $endpoint;
        
        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }
        //error_log($url);
        $response = wp_remote_get($url, [
            'headers' => [
                'ApiKey' => $this->api_key,
            ],
        ]);
        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        //error_log(print_r($body, true));

        // Check if the response is an error message
        if (strpos($body, 'Bad Request') !== false || strpos($body, 'parser error') !== false) {
            throw new \Exception(__('Invalid API request: ', 'eventor-integration') . $body);
        }

        $data = simplexml_load_string($body);

        if (!$data) {
            throw new \Exception(__('Invalid response from API: ', 'eventor-integration') . $body);
        }

        return $data;
    }

    /**
     * Clear organisation cache
     * 
     * @param int|null $org_id Optional specific organization ID to clear
     */
    public function clear_organisation_cache($org_id = null) {
        if ($org_id) {
            delete_transient('eventor_organisation_' . $org_id);
        } else {
            global $wpdb;
            $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_eventor_organisation_%'");
            $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_eventor_organisation_%'");
        }
    }

    /**
     * Clear all Eventor caches
     */
    public function clear_all_caches() {
        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_eventor_%'");
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_eventor_%'");
    }
}