<?php
namespace EventorIntegration;

class Utilities {
    public static function get_google_maps_link($position) {
        if (empty($position) || empty($position['x']) || empty($position['y'])) {
            return false;
        }
        
        $longitude = $position['x'];
        $latitude = $position['y'];
        return "https://www.google.com/maps?q={$latitude},{$longitude}";
    }

    /**
     * Render a single event in detail view
     * 
     * @param object $event Event data
     * @param object $api API instance
     * @return void
     */
    public static function render_single_event($event, $api) {
        // Get organization data if available
        $organizer = null;
        
        if (!empty($event->Organiser->Organisation->OrganisationId)) {
            $organizer = $api->get_organisation((int)$event->Organiser->Organisation->OrganisationId);
        }

        // Format the event date
        $event_date = date_i18n(
            'j. M Y - H:i', 
            strtotime($event->StartDate->Date . ' ' . $event->StartDate->Clock)
        );

        // Get coordinates-based maps URL if available
        $maps_url = '';
        if (!empty($event->EventRace)) {
            $attrs = $event->EventRace->EventCenterPosition->attributes();
            if (!empty($attrs)) {
                $position = [
                    'x' => (string)$attrs->x,
                    'y' => (string)$attrs->y
                ];
                $maps_url = self::get_google_maps_link($position);
            }
        }

        // Get event documents
        $documents = [];
        try {
            $xml = $api->get_event_documents($event->EventId);
            
            if (isset($xml->Document) && count($xml->Document) > 0) {
                foreach ($xml->Document as $doc) {
                    $attrs = $doc->attributes();
                    $documents[] = [
                        'id' => (string)$attrs->id,
                        'name' => (string)$attrs->name,
                        'url' => (string)$attrs->url,
                        'type' => (string)$attrs->type,
                        'modifyDate' => date_i18n(
                            get_option('date_format'), 
                            strtotime((string)$attrs->modifyDate)
                        )
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silently handle the error - documents will remain empty
        }

        // Get Eventor message if available
        $eventor_message = '';
        if (!empty($event->HashTableEntry)) {
            foreach ($event->HashTableEntry as $entry) {
                if ((string)$entry->Key === 'Eventor_Message') {
                    $eventor_message = (string)$entry->Value;
                    break;
                }
            }
        }

        // Include the template
        include EVENTOR_INTEGRATION_PLUGIN_DIR . 'templates/partials/single-event-card.php';
    }

    public static function render_event($event, $api, $event_type = '') {
        // Get the current layout from the template
        global $eventor_layout;
        $message_length = $eventor_layout === 'dense' ? 50 : 120;
        ?>
        <div class="eventor-event <?php echo esc_attr($event_type); ?>">
            <div class="event-organiser">
                <?php echo esc_html(date('j. M Y', strtotime($event->StartDate->Date))); ?>: 
                <?php 
                if (!empty($event->Organiser->OrganisationId)):
                    try {
                        $org_data = $api->get_organisation($event->Organiser->OrganisationId);
                        if ($org_data && !empty($org_data->Name)):
                            global $eventor_layout;
                            $image_url = 'https://eventor.orientering.no/Organisation/Logotype/' . esc_attr($event->Organiser->OrganisationId);
                ?>
                    <?php if ($eventor_layout === 'dense'): ?>
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_html($org_data->Name); ?>"> 
                    <?php endif; ?>
                    <?php echo esc_html($org_data->Name); ?> 
                    <?php echo $event_type === 'past' ? esc_html__('arrangerte', 'eventor-integration') : esc_html__('inviterer til', 'eventor-integration'); ?>
                <?php 
                        endif;
                    } catch (\Exception $e) {}
                endif; 
                ?>
            </div>
            <div class="event-content">
                <?php 
                if (!empty($event->Organiser->OrganisationId) && $eventor_layout === 'rich'):
                    try {
                        $org_data = $api->get_organisation($event->Organiser->OrganisationId);
                        if ($org_data && !empty($org_data->Name)):
                            $image_url = 'https://eventor.orientering.no/Organisation/Logotype/' . esc_attr($event->Organiser->OrganisationId);
                            if ($eventor_layout === 'rich') {
                                $image_url .= '?type=LargeIcon';
                            }
                ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_html($org_data->Name); ?>">
                <?php 
                        endif;
                    } catch (\Exception $e) {}
                endif; 
                ?>
                <div class="event-content-text">
                    <span class="event-heading">
                        <?php if (!empty($event->EventId)): ?>
                            <a href="https://eventor.orientering.no/Events/Show/<?php echo esc_attr($event->EventId); ?>" 
                               target="_blank">                                
                        <?php endif; ?>
                        <?php echo esc_html($event->Name); ?>
                        <?php if (!empty($event->EventId)): ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($event->EventRace->EventCenterPosition['x']) && !empty($event->EventRace->EventCenterPosition['y'])): ?>
                            <a href="<?php echo esc_url(self::get_google_maps_link(['x' => (string)$event->EventRace->EventCenterPosition['x'], 'y' => (string)$event->EventRace->EventCenterPosition['y']])); ?>" 
                               class="event-map-link" 
                               target="_blank" 
                               title="<?php esc_attr_e('View location on map', 'eventor-integration'); ?>">
                                <svg class="map-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" 
                                          fill="currentColor"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </span>
                    <?php 
                    if (!empty($event->HashTableEntry)): 
                        foreach ($event->HashTableEntry as $entry):
                            if ((string)$entry->Key === 'Eventor_Message'):
                                $message = (string)$entry->Value;
                                $is_long = strlen($message) > $message_length;
                                $short_message = $is_long ? substr($message, 0, $message_length) . '...' : $message;
                    ?>
                            <div class="event-message <?php echo $is_long ? 'expandable' : ''; ?>">
                            <svg width="16" height="16" class="message-arrow" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
 <path d="M4 4V5.4C4 8.76031 4 10.4405 4.65396 11.7239C5.2292 12.8529 6.14708 13.7708 7.27606 14.346C8.55953 15 10.2397 15 13.6 15H20M20 15L15 10M20 15L15 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
 </svg>           
                                <div class="message-short">
                                    <?php echo trim(esc_html($short_message), ' \n\r\t\v\x00'); ?>
                                    <?php if ($is_long): ?>
                                        <button class="expand-message" aria-expanded="false">
                                            <?php esc_html_e('mer...', 'eventor-integration'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <?php if ($is_long): ?>
                                    <div class="message-full" hidden>
                                        <?php echo nl2br(esc_html($message)); ?>
                                        <button class="collapse-message">
                                            <?php esc_html_e('minimér', 'eventor-integration'); ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                    <?php 
                            endif;
                        endforeach;
                    endif; 
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
} 