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
                    <?php echo esc_html($event_date); ?>
                </time>
                <?php if (!empty($event->EventRace->FirstStart)): ?>
                    <div class="event-time">
                        <span class="dashicons dashicons-clock"></span>
                        <?php echo esc_html(date_i18n('H:i', strtotime($event->EventRace->FirstStart->Clock))); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="event-header-content">
                <!-- Organizer Section -->
                <div class="event-organizer-section">
                    <?php if (isset($org_logo_url)): ?>
                        <div class="organizer-logo-wrapper">
                            <?php if (!empty($org_url)): ?>
                                <a href="<?php echo esc_url($org_url); ?>" target="_blank" class="logo-link">
                            <?php endif; ?>
                            <img src="<?php echo esc_url($org_logo_url); ?>" 
                                 alt="<?php echo esc_attr($org_name); ?>" 
                                 class="organizer-logo"
                                 onerror="this.style.display='none'">
                            <?php if (!empty($org_url)): ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($event->Name)): ?>
                    <div class="event-title-section">
                    <?php if (!empty($org_name)): ?>
                            <div class="organizer-info">
                                <?php if (!empty($org_url)): ?>
                                    <a href="<?php echo esc_url($org_url); ?>" target="_blank" class="organizer-name">
                                        <?php echo esc_html($org_name); ?>:
                                    </a>
                                <?php else: ?>
                                    <span class="organizer-name"><?php echo esc_html($org_name); ?>:</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="title-row">
                            <h2 class="event-title">
                                <?php if (!empty($event->EventId)): ?>
                                    <a href="https://eventor.orientering.no/Events/Show/<?php echo esc_attr($event->EventId); ?>" 
                                       target="_blank">
                                        <?php echo esc_html($event->Name); ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo esc_html($event->Name); ?>
                                <?php endif; ?>
                            </h2>
                            <?php if (!empty($event->EventClassificationId)): 
                                $classification = match((int)$event->EventClassificationId) {
                                    1 => __('Mesterskap', 'eventor-integration'),
                                    2 => __('Nasjonalt arrangement', 'eventor-integration'),
                                    3 => __('Kretsløp', 'eventor-integration'),
                                    4 => __('Nærløp', 'eventor-integration'),
                                    5 => __('Klubbløp', 'eventor-integration'),
                                    6 => __('Kvalifiseringsløp', 'eventor-integration'),
                                    default => $event->EventClassification ?? __('', 'eventor-integration')
                                };
                            ?>
                                <span class="event-classification">
                                    <span class="dashicons dashicons-tag"></span>
                                    <?php echo esc_html($classification); ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($event->EventRace->attributes()->raceDistance)): ?>
                                <span class="event-classification">
                                    <span class="dashicons dashicons-info-outline"></span>
                                    <?php echo esc_html((string)$event->EventRace->attributes()->raceDistance); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Event Main Content -->
        <div class="eventor-card-body">
            <?php if (!empty($eventor_message)): ?>
                <div class="event-message">
                    <span class="dashicons dashicons-megaphone" style="color: coral; font-size: xx-large;"></span>
                    <p><?php echo nl2br(esc_html(trim($eventor_message))); ?></p>
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
                                <span class="document-type"><?php echo esc_html($doc['type']); ?></span>
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