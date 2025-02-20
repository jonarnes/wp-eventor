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

$layout = !empty($attributes['layout']) ? $attributes['layout'] : get_option('eventor_integration_default_layout', 'rich');
// Make layout available globally for Utilities class
$GLOBALS['eventor_layout'] = $layout;
?>

<div class="eventor-timeline layout-<?php echo esc_attr($layout); ?>">
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