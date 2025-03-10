<?php
if (!defined('ABSPATH')) {
    exit;
}

use EventorIntegration\Utilities;

Utilities::render_single_event($event, $this->api);
