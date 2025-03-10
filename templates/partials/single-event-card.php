<?php
if (!defined('ABSPATH')) {
    exit;
}

$org_logo_url = !empty($event->Organiser->Organisation->OrganisationId) ? 
    'https://eventor.orientering.no/Organisation/Logotype/' . 
    esc_attr($event->Organiser->Organisation->OrganisationId) . 
    '?type=LargeIcon' : '';

$is_future_event = strtotime($event->StartDate->Date) > time();

// Check if we have a valid organizer object with a name
$org_name = is_object($organizer) ? $organizer->Name : (is_string($organizer) ? $organizer : '');
$org_url = !empty($event->WebURL) ? $event->WebURL : 
          (is_object($organizer) && !empty($organizer->WebURL) ? $organizer->WebURL : '');
?>

<div class="eventor-single-event">
    <div class="eventor-event-card">
        <div class="eventor-card-header">
            <?php if (!empty($event->Name)): ?>
                <h2 class="eventor-event-title">
                    <?php if (!empty($event->EventId)): ?>
                        <a href="https://eventor.orientering.no/Events/Show/<?php echo esc_attr($event->EventId); ?>" 
                           target="_blank">
                            <?php echo esc_html($event->Name); ?>
                        </a>
                    <?php else: ?>
                        <?php echo esc_html($event->Name); ?>
                    <?php endif; ?>
                </h2>
            <?php endif; ?>

            <div class="eventor-event-datetime">
                <?php if (!empty($event->Arena->Name) || !empty($event->Arena->Address->City)): ?>
                    <span class="dashicons dashicons-location"></span>
                    <?php if (!empty($event->Arena->Name)): ?>
                        <span class="arena-name"><?php echo esc_html($event->Arena->Name); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($event->Arena->Address->City)): ?>
                        <span class="arena-city"><?php echo esc_html($event->Arena->Address->City); ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="eventor-card-logo">
            <?php if (isset($org_logo_url)): ?>
                <?php if (!empty($org_url)): ?>
                    <a href="<?php echo esc_url($org_url); ?>" target="_blank" class="logo-link">
                <?php endif; ?>
                <img src="<?php echo esc_url($org_logo_url); ?>" 
                     alt="<?php echo esc_attr($org_name); ?>" 
                     class="eventor-organizer-logo"
                     onerror="this.style.display='none'">
                <?php if (!empty($org_url)): ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($eventor_message)): ?>
            <div class="eventor-event-message">
                <div class="message-content">
                    <?php echo nl2br(esc_html(trim($eventor_message))); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="eventor-card-content">
            <?php if (!empty($org_name)): ?>
                <div class="eventor-event-organizer">
                    <?php if (!empty($org_url)): ?>
                        <a href="<?php echo esc_url($org_url); ?>" 
                           target="_blank"
                           class="organizer-link">
                            <?php echo esc_html($org_name); ?>
                        </a>
                    <?php else: ?>
                        <span><?php echo esc_html($org_name); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="eventor-action-buttons">
                <?php if (!empty($event->EventId)): ?>
                    <a href="https://eventor.orientering.no/Events/Show/<?php echo esc_attr($event->EventId); ?>" 
                       class="meta-action-button primary" 
                       target="_blank">
                        <img src="https://eventor.orientering.no/Organisation/Logotype/2?type=smallIcon" 
                             alt="" 
                             class="eventor-icon"
                             width="16" 
                             height="16">
                        <?php esc_html_e('Eventor', 'eventor-integration'); ?>
                    </a>
                <?php endif; ?>

                <?php if (!empty($maps_url)): ?>
                    <a href="<?php echo esc_url($maps_url); ?>" 
                       class="meta-action-button secondary" 
                       target="_blank">
                        <svg class="map-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" 
                                  fill="currentColor"/>
                        </svg>
                        <?php esc_html_e('Veibeskrivelse', 'eventor-integration'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="eventor-two-columns">
                <div class="eventor-column event-details">
                    <ul class="event-details-list">
                        <?php if (!empty($event->EventStatusId) && (int)$event->EventStatusId === 7): ?>
                            <li>
                                <strong><?php esc_html_e('Status:', 'eventor-integration'); ?></strong>
                                <?php echo esc_html__('Avlyst', 'eventor-integration'); ?>
                            </li>
                        <?php endif; ?>                        
                        <li>
                            <span class="event-date-info">
                                <?php echo esc_html__('Tidspunkt: ', 'eventor-integration'); echo esc_html($event_date); ?>
                            </span>
                        </li>
                        <?php if (!empty($event->EventClassificationId)): ?>
                            <li>
                                <?php 
                                $classification = match((int)$event->EventClassificationId) {
                                    1 => __('Mesterskap', 'eventor-integration'),
                                    2 => __('Nasjonalt arrangement', 'eventor-integration'),
                                    3 => __('Kretsløp', 'eventor-integration'),
                                    4 => __('Nærløp', 'eventor-integration'),
                                    5 => __('Klubbløp', 'eventor-integration'),
                                    6 => __('Kvalifiseringsløp', 'eventor-integration'),
                                    default => $event->EventClassification ?? __('', 'eventor-integration')
                                };
                                echo esc_html($classification); 
                                ?>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($event->EventRace->Distance)): ?>
                            <li>
                                <?php echo esc_html__('Distanse: ', 'eventor-integration'); echo esc_html((string)$event->EventRace->Distance); ?>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($event->EventRace->LightCondition)): ?>
                            <li>
                                    <?php 
                                    $light_condition = ((string)$event->EventRace->LightCondition === 'Day') 
                                        ? __('Dag', 'eventor-integration') 
                                        : __('Natt', 'eventor-integration');
                                        echo esc_html__('Lysforhold: ', 'eventor-integration');
                                        echo esc_html($light_condition); 
                                    ?>
                            </li>
                        <?php endif; ?>
                        <?php if (!empty($event->EventRace->FirstStart)): ?>
                            <li>
                                <?php echo esc_html__('Første start: ', 'eventor-integration'); ?>
                                    <?php echo esc_html(date_i18n('H:i', strtotime($event->EventRace->FirstStart->Clock))); ?>
                            </li>
                        <?php endif; ?>

                        <?php 
                        $position = $event->EventRace->EventCenterPosition;
                        if (!empty($position)) {
                            $attrs = $position->attributes();
                            if (!empty($attrs->x) && !empty($attrs->y)) {
                                $coordinates = (string)$attrs->y . ',' . (string)$attrs->x;
                        ?>
                            <li>
                                <strong><?php esc_html_e('Værvarsel:', 'eventor-integration'); ?></strong>
                                <a href="https://www.yr.no/nb/værvarsel/daglig-tabell/<?php echo esc_attr($coordinates); ?>" 
                                   target="_blank" 
                                   class="weather-link">
                                    <span class="dashicons dashicons-cloud"></span>
                                    <?php esc_html_e('Værvarsel', 'eventor-integration'); ?>
                                </a>
                            </li>
                        <?php 
                            }
                        }
                        ?>

                    </ul>
                </div>

                <div class="eventor-column event-documents">
                    <?php if (!empty($documents)): ?>
                        <ul class="documents-list">
                            <?php foreach ($documents as $doc): ?>
                                <li>
                                    <a href="<?php echo esc_url($doc['url']); ?>" 
                                       target="_blank"
                                       title="<?php echo esc_attr(sprintf(
                                           __('Last modified: %s', 'eventor-integration'),
                                           $doc['modifyDate']
                                       )); ?>">
                                        <?php echo esc_html($doc['name']); ?>
                                        <small class="document-type">(<?php echo esc_html($doc['type']); ?>)</small>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($event->EventForm)): ?>
                <div class="eventor-event-form">
                    <span class="dashicons dashicons-info"></span>
                    <?php echo esc_html($event->EventForm); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 