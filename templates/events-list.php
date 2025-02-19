<?php
if (!defined('ABSPATH')) {
    exit;
}

use EventorIntegration\Utilities;

// Sort events by date and split into past and upcoming
$past_events = [];
$upcoming_events = [];
$today = new DateTime();

foreach ($events->Event as $event) {
    $event_date = new DateTime($event->StartDate->Date);
    if ($event_date < $today) {
        $past_events[] = $event;
    } else {
        $upcoming_events[] = $event;
    }
}

// Sort past events chronologically (oldest to most recent)
usort($past_events, function($a, $b) {
    return strtotime($a->StartDate->Date) - strtotime($b->StartDate->Date);
});

$past_events = array_reverse($past_events);

// Limit past events to 1   
$past_events = array_slice($past_events, 0, get_option('eventor_integration_past_events_count', 1));
$past_events = array_reverse($past_events);

// Sort upcoming events chronologically
usort($upcoming_events, function($a, $b) {
    return strtotime($a->StartDate->Date) - strtotime($b->StartDate->Date);
});
?>

<div class="eventor-timeline">
    <!-- Past Events Section -->
    <?php if (!empty($past_events)): ?>
        <?php foreach ($past_events as $event): ?>
            <?php Utilities::render_event($event, $api, 'past'); ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="eventor-event past no-events">
            <p><?php esc_html_e('Ingen tidligere arrangementer å vise.', 'eventor-integration'); ?></p>
        </div>
    <?php endif; ?>

    <!-- Next Upcoming Event (Featured) -->
    <?php if (!empty($upcoming_events)): 
        $next_event = array_shift($upcoming_events); // Remove and get the first upcoming event
        Utilities::render_event($next_event, $api, 'featured');
    else: ?>
        <div class="eventor-event no-events">
            <p><?php esc_html_e('Ingen kommende arrangementer å vise.', 'eventor-integration'); ?></p>
        </div>
    <?php endif; ?>

    <!-- Future Events Section -->
    <?php foreach ($upcoming_events as $event): ?>
        <?php Utilities::render_event($event, $api, 'future'); ?>
    <?php endforeach; ?>

</div>

<style>
.eventor-timeline {
    position: relative;
    max-width: 800px;
    margin: 0 auto;
    padding: 10px;
}
.event-heading{
    font-size: 1.2em;
    font-weight: bold;
}

.eventor-event {
    position: relative;
    margin: 12px 0;
    padding: 12px;
    border-radius: 6px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.eventor-event:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}

.eventor-event.featured {
    background: #f8f8f8;
    border-left: 4px solid #4CAF50;
    margin: 20px 0;
}

.eventor-event.past {
    opacity: 0.8;
}


.event-organiser small{
    font-size: 0.9em;
    color: #666;
    margin: 0 0 3px 0;
}

.event-organiser {
    color: #666;
    margin: 0 0 3px 0;
}

.event-map-link {
    display: inline-block;
    margin-left: 8px;
    color: #666;
    text-decoration: none;
    vertical-align: text-bottom;
}

.event-map-link:hover {
    color: #4CAF50;
}

.map-icon {
    vertical-align: middle;
    transition: transform 0.2s ease;
}

.event-map-link:hover .map-icon {
    transform: scale(1.1);
}

/* Responsive adjustments */
@media (max-width: 600px) {
    .eventor-event {
        margin-left: 0;
    }
}

.event-message {
    position: relative;
    font-size: 0.9em;
    color: #666;
    margin: 0 0 8px 24px;
    line-height: 1.4;
}

.message-arrow {
    position: absolute;
    left: -16px;
    top: -2px;
    transform: scale(0.8); /* Make the arrow slightly smaller */
}

.expand-message, .collapse-message {
    background: none;
    border: none;
    color: #4CAF50;
    padding: 0;
    margin-left: 5px;
    cursor: pointer;
    font-size: 0.9em;
    text-decoration: underline;
}

.expand-message:hover, .collapse-message:hover {
    color: #388E3C;
}

.message-full {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.event-deadline {
    font-size: 0.5em;
    color: #666;
    font-weight: normal;
    margin-left: 12px;
    padding: 2px 8px;
    border-radius: 4px;
    background: #f0f0f0;
    vertical-align: middle;
}

.eventor-event.no-events {
    text-align: center;
    padding: 0;
    color: #666;
    font-style: italic;
    background: #f8f8f8;
}

.eventor-event.no-events p {
    margin: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.event-message.expandable').forEach(function(container) {
        const expandBtn = container.querySelector('.expand-message');
        const collapseBtn = container.querySelector('.collapse-message');
        const shortMsg = container.querySelector('.message-short');
        const fullMsg = container.querySelector('.message-full');

        expandBtn?.addEventListener('click', function() {
            shortMsg.hidden = true;
            fullMsg.hidden = false;
            expandBtn.setAttribute('aria-expanded', 'true');
        });

        collapseBtn?.addEventListener('click', function() {
            shortMsg.hidden = false;
            fullMsg.hidden = true;
            expandBtn.setAttribute('aria-expanded', 'false');
        });
    });
});
</script> 