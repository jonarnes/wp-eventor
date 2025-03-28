<?php
if (!defined('ABSPATH')) {
    exit;
}

// Handle multiple organizers
$organizers = array();
if (!empty($event->Organiser)) {
    //error_log('Organiser data: ' . print_r($event->Organiser, true));
    //error_log('Organisation type: ' . gettype($event->Organiser->Organisation));
    
    // Check if Organisation is a SimpleXMLElement
    if ($event->Organiser->Organisation instanceof \SimpleXMLElement) {
        // Count the number of Organisation elements
        $org_count = count($event->Organiser->Organisation);
        //error_log('Number of organizations: ' . $org_count);
        
        if ($org_count > 1) {
            // Multiple organizations
            foreach ($event->Organiser->Organisation as $org) {
                $organizers[] = array(
                    'id' => (string)$org->OrganisationId,
                    'name' => (string)$org->Name,
                    'logo_url' => 'https://eventor.orientering.no/Organisation/Logotype/' . (string)$org->OrganisationId . '?type=LargeIcon',
                );
            }
        } else {
            // Single organization
            $org = $event->Organiser->Organisation;
            $organizers[] = array(
                'id' => (string)$org->OrganisationId,
                'name' => (string)$org->Name,
                'logo_url' => 'https://eventor.orientering.no/Organisation/Logotype/' . (string)$org->OrganisationId . '?type=LargeIcon',
            );
        }
    }
}

$is_future_event = strtotime($event->StartDate->Date) > time();

// Check if we have a valid organizer object with a name
$org_name = is_object($organizer) ? $organizer->Name : (is_string($organizer) ? $organizer : '');
$org_url = !empty($event->WebURL) ? $event->WebURL : 
          (is_object($organizer) && !empty($organizer->WebURL) ? $organizer->WebURL : '');
?>

<div class="eventor-single-event">
    <article class="eventor-event-card">
        <!-- Event Header -->
        <header class="eventor-card-header">
            <?php if (!empty($event->EventStatusId) && (int)$event->EventStatusId === 7): ?>
                <div class="event-status cancelled">
                    <?php esc_html_e('Avlyst', 'eventor-integration'); ?>
                </div>
            <?php endif; ?>

            <div class="event-meta">
                <time class="event-date">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <?php echo esc_html($event_date); 
                    if (!empty($event->FinishDate->Date) && (string)$event->FinishDate->Date !== (string)$event->StartDate->Date) {
                        echo ' - ' . esc_html(date('j. M Y', strtotime($event->FinishDate->Date)));
                    }
                    ?>
                </time>
                <?php if (!empty($event->EventRace->FirstStart)): ?>
                    <div class="event-time">
                        <span class="dashicons dashicons-clock"></span>
                        <?php echo esc_html(date_i18n('H:i', strtotime($event->EventRace->FirstStart->Clock))); 
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="event-header-content">
                <!-- Organizer Section -->
                <div class="event-organizer-section">
                    <?php if (!empty($organizers)): ?>
                        <div class="organizer-logos-stack">
                            <?php foreach ($organizers as $index => $organizer): ?>
                                <div class="organizer-logo-wrapper">
                                    <img 
                                        src="<?php echo esc_url($organizer['logo_url']); ?>" 
                                        alt="<?php echo esc_attr($organizer['name']); ?>"
                                        class="organizer-logo"
                                    >
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="event-title-section">
                    <div class="title-row">
                        <h2 class="event-title">
                            <?php if (!empty($event->EventId)): ?>
                                <a href="https://eventor.orientering.no/Events/Show/<?php echo esc_attr($event->EventId); ?>" 
                                   target="_blank">
                            <?php endif; ?>
                            <?php echo esc_html($event->Name); ?>
                            <?php if (!empty($event->EventId)): ?>
                                </a>
                            <?php endif; ?>
                        </h2>
                        <?php if (isset($event->EventClassificationId)): ?>
                            <?php
                            $classification = \EventorIntegration\Utilities::get_classification_translation($event->EventClassificationId);
                            ?>
                            <?php if (!empty($classification)): ?>
                                <?php if (!empty($event->WebURL)): ?>
                                    <a href="<?php echo esc_url($event->WebURL); ?>" target="_blank" class="event-classification">
                                <?php else: ?>
                                    <span class="event-classification">
                                <?php endif; ?>
                                    <span class="dashicons dashicons-tag"></span>
                                    <?php echo esc_html($classification); ?>
                                <?php if (!empty($event->WebURL)): ?>
                                    </a>
                                <?php else: ?>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php 
                        $races = $event->xpath('.//EventRace');
                        if (count($races) === 1): ?>
                            <span class="event-race-distance">
                                <span class="dashicons dashicons-info"></span>
                                <?php
                                echo esc_html(\EventorIntegration\Utilities::get_race_distance_translation($races[0]->attributes()->raceDistance));
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="organizer-info">
                        <?php 
                        if (!empty($organizers)) {
                            $organizer_links = array();
                            foreach ($organizers as $organizer) {
                                $organizer_links[] = esc_html($organizer['name']);    
                            }
                            echo implode(', ', $organizer_links);
                        }
                        ?>
                        <?php if (!empty($event->WebURL)): ?>
                            (<a href="<?php echo esc_url($event->WebURL); ?>" 
                               target="_blank" 
                               class="event-web-url">
                                <?php 
                                $parsedUrl = parse_url($event->WebURL);
                                $domain = $parsedUrl['host'];
                                esc_html_e($domain, 'eventor-integration'); ?>
                               </a>)
                        <?php endif; ?>

                    </div>
                    <?php 
                    // Get all EventRace elements from the root level
                    $races = $event->xpath('.//EventRace');
                    if (count($races) > 1): ?>
                        <div class="event-races">
                            <?php foreach ($races as $race): ?>
                                <div class="race-item">
                                    <?php if (!empty($race->Name)): ?>
                                        <span class="race-name"><?php echo esc_html($race->Name); ?>:</span>
                                    <?php endif; ?>
                                    <?php if (!empty($race->RaceDate)): ?>
                                        <span class="race-date">
                                            <?php echo esc_html(date_i18n('j. M', strtotime($race->RaceDate->Date))); ?> - <?php echo esc_html(date_i18n('H:i',strtotime($race->RaceDate->Date . ' ' . $race->RaceDate->Clock))); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($race->attributes()->raceDistance)): ?>
                                        <span class="race-distance">
                                            <?php echo esc_html(\EventorIntegration\Utilities::get_race_distance_translation($race->attributes()->raceDistance)); ?>
                                        </span>
                                    <?php else: ?>
                                        <?php echo esc_html(\EventorIntegration\Utilities::get_race_distance_translation('Middle')); ?>
                                    <?php endif; ?>
                                    <?php 
                                    $attrs = $race->EventCenterPosition->attributes();
                                    if (!empty($attrs->x) && !empty($attrs->y)) {
                                        $coordinates = (string)$attrs->y . ',' . (string)$attrs->x;
                                        $maps_url = "https://www.google.com/maps/search/?api=1&query={$coordinates}";
                                        ?>
                                        <a href="<?php echo esc_url($maps_url); ?>" 
                                           class="race-location" 
                                           target="_blank">
                                            <span class="dashicons dashicons-location"></span>
                                        </a>
                                        <?php
                                    }
                                    ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Event Main Content -->
        <div class="eventor-card-body">
            <?php if (!empty($eventor_message)): ?>
                <div class="event-message">
                    <span class="dashicons dashicons-megaphone" style="color: coral; font-size: xx-large;"></span>
                    <p><?php 
                    echo nl2br(\EventorIntegration\Utilities::convert_urls_to_links($eventor_message)); 
                    ?></p>
                </div>
            <?php endif; ?>

            <!-- Event Details Grid -->
            <div class="event-details-grid">
                <!-- Location Info -->
                <?php if (!empty($event->Arena->Name) || !empty($event->Arena->Address->City)): ?>
                    <div class="detail-item location-info">
                        <span class="dashicons dashicons-location"></span>
                        <div class="detail-content">
                            <?php if (!empty($event->Arena->Name)): ?>
                                <span class="arena-name"><?php echo esc_html($event->Arena->Name); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($event->Arena->Address->City)): ?>
                                <span class="arena-city"><?php echo esc_html($event->Arena->Address->City); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Race Details -->
                <?php if (!empty($event->EventRace->Distance) || !empty($event->EventRace->LightCondition)): ?>
                    <div class="detail-item race-info">
                        <span class="dashicons dashicons-performance"></span>
                        <div class="detail-content">
                            <?php if (!empty($event->EventRace->Distance)): ?>
                                <span class="race-distance"><?php echo esc_html((string)$event->EventRace->Distance); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($event->EventRace->LightCondition)): ?>
                                <span class="light-condition">
                                    <?php 
                                    echo ((string)$event->EventRace->LightCondition === 'Day') 
                                        ? esc_html__('Dagløp', 'eventor-integration')
                                        : esc_html__('Nattløp', 'eventor-integration');
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- Weather Forecast -->
                <?php 
                $position = $event->EventRace->EventCenterPosition;
                if (!empty($position)) {
                    $attrs = $position->attributes();
                    if (!empty($attrs->x) && !empty($attrs->y)) {
                        $coordinates = (string)$attrs->y . ',' . (string)$attrs->x;
                    }
                }
                ?>
            </div>

            <!-- Documents Section -->
            <?php if (!empty($documents)): ?>
                <div class="event-documents">
                    <h3><?php esc_html_e('Dokumenter', 'eventor-integration'); ?></h3>
                    <div class="documents-grid">
                        <?php foreach ($documents as $doc): ?>
                            <a href="<?php echo esc_url($doc['url']); ?>" 
                               target="_blank"
                               class="document-item"
                               title="<?php echo esc_attr(sprintf(
                                   __('Sist endret: %s', 'eventor-integration'),
                                   $doc['modifyDate']
                               )); ?>">
                                <span class="dashicons dashicons-media-document"></span>
                                <span class="document-name"><?php echo esc_html($doc['name']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="event-actions">
               <a href="https://eventor.orientering.no/Events/Show/<?php echo esc_attr($event->EventId); ?>" 
                class="action-button primary" 
                    target="_blank">
                    <img src="https://eventor.orientering.no/Organisation/Logotype/2?type=smallIcon" 
                                     alt="" 
                                     class="eventor-icon"
                                     width="16" 
                                     height="16">                    
                    <?php esc_html_e('Eventor', 'eventor-integration'); ?>
                </a>
                <?php if (!empty($coordinates)): ?>
                    <a href="https://www.yr.no/nb/værvarsel/daglig-tabell/<?php echo esc_attr($coordinates); ?>" 
                       class="action-button secondary" 
                       target="_blank">
                        <span class="dashicons dashicons-cloud"></span>
                        <?php esc_html_e('Værvarsel', 'eventor-integration'); ?>
                    </a>
                <?php endif; ?>

                <?php if (!empty($maps_url)): ?>
                    <a href="<?php echo esc_url($maps_url); ?>" 
                       class="action-button secondary" 
                       target="_blank">
                        <span class="dashicons dashicons-location"></span>
                        <?php esc_html_e('Veibeskrivelse', 'eventor-integration'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if (!empty($event->EventForm)): ?>
                <footer class="event-footer">
                    <div class="event-form-info">
                        <span class="dashicons dashicons-info"></span>
                        <?php echo esc_html($event->EventForm); ?>
                    </div>
                </footer>
            <?php endif; ?>
        </div>
    </article>
</div> 