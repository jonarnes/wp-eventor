<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php //settings_errors(); ?>

    <form action="options.php" method="post">
        <?php
        settings_fields('eventor_integration_options');
        do_settings_sections('eventor_integration_options');
        ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="eventor_integration_api_key"><?php esc_html_e('API Key', 'eventor-integration'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="eventor_integration_api_key" 
                           name="eventor_integration_api_key" 
                           value="<?php echo esc_attr(get_option('eventor_integration_api_key')); ?>" 
                           class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eventor_integration_organisation_ids"><?php esc_html_e('Organisation IDs', 'eventor-integration'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="eventor_integration_organisation_ids" 
                           name="eventor_integration_organisation_ids" 
                           value="<?php echo esc_attr(get_option('eventor_integration_organisation_ids')); ?>" 
                           class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Multiple IDs can be separated by commas (e.g., 123,456,789)', 'eventor-integration'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eventor_integration_days_back"><?php esc_html_e('Days to look back', 'eventor-integration'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           id="eventor_integration_days_back" 
                           name="eventor_integration_days_back" 
                           value="<?php echo esc_attr(get_option('eventor_integration_days_back', 30)); ?>" 
                           min="1" 
                           max="365" 
                           class="small-text">
                    <p class="description">
                        <?php esc_html_e('Number of days to look back from today (1-365)', 'eventor-integration'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eventor_integration_days_forward"><?php esc_html_e('Days to look forward', 'eventor-integration'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           id="eventor_integration_days_forward" 
                           name="eventor_integration_days_forward" 
                           value="<?php echo esc_attr(get_option('eventor_integration_days_forward', 90)); ?>" 
                           min="1" 
                           max="365" 
                           class="small-text">
                    <p class="description">
                        <?php esc_html_e('Number of days to look forward from today (1-365)', 'eventor-integration'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eventor_integration_past_events_count"><?php esc_html_e('Number of past events', 'eventor-integration'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           id="eventor_integration_past_events_count" 
                           name="eventor_integration_past_events_count" 
                           value="<?php echo esc_attr(get_option('eventor_integration_past_events_count', 1)); ?>" 
                           min="0" 
                           max="10" 
                           class="small-text">
                    <p class="description">
                        <?php esc_html_e('Number of past events to display (0-10)', 'eventor-integration'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>

    <hr>

    <h2><?php esc_html_e('Cache Management', 'eventor-integration'); ?></h2>
    <p><?php esc_html_e('Clear the cached organization data if you need to refresh the information.', 'eventor-integration'); ?></p>
    
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="eventor_clear_cache">
        <?php wp_nonce_field('eventor_clear_cache'); ?>
        <?php submit_button(__('Clear Organization Cache', 'eventor-integration'), 'secondary'); ?>
    </form>
</div> 