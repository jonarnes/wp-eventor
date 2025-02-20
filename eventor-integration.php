<?php
/**
 * Plugin Name: Eventor Integration
 * Plugin URI: https://github.com/jonarnes/eventor-integration
 * Description: Integration with Eventor API for displaying orienteering event data
 * Version: 1.0.0
 * Author: Jon Arne Stæterås
 * Author URI: https://ijas.no
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: eventor-integration
 * Domain Path: /languages
 *
 * @package EventorIntegration
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('EVENTOR_INTEGRATION_VERSION', '1.0.0');
define('EVENTOR_INTEGRATION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EVENTOR_INTEGRATION_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader for classes
spl_autoload_register(function ($class) {
    $prefix = 'EventorIntegration\\';
    $base_dir = EVENTOR_INTEGRATION_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
function eventor_integration_init() {
    // Initialize main plugin class
    $plugin = new EventorIntegration\Plugin();
    $plugin->init();

    // Register the block
    $block = new \EventorIntegration\Blocks\EventorEventsBlock($plugin);
    $block->init();
}
add_action('plugins_loaded', 'eventor_integration_init');

// Activation hook
function eventor_integration_activate() {
    // Add activation tasks here
    add_option('eventor_integration_api_key', '');
    add_option('eventor_integration_organisation_id', '');
}
register_activation_hook(__FILE__, 'eventor_integration_activate');

// Deactivation hook
function eventor_integration_deactivate() {
    // Add deactivation tasks here
}
register_deactivation_hook(__FILE__, 'eventor_integration_deactivate');

// Uninstall hook
function eventor_integration_uninstall() {
    // Clean up plugin options
    delete_option('eventor_integration_api_key');
    delete_option('eventor_integration_organisation_id');
}
register_uninstall_hook(__FILE__, 'eventor_integration_uninstall');

class EventorIntegration {
    public function __construct() {
        // Add this line to debug
        add_action('init', function() {
            error_log('Eventor Integration Plugin initialized');
        });
    }

    public function init() {
        // Load block editor assets
        add_action('init', [$this, 'register_block_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        
        // Initialize the block with plugin instance
        $block = new \EventorIntegration\Blocks\EventorEventsBlock($this);
        $block->init();
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'eventor-events-block',
            EVENTOR_INTEGRATION_PLUGIN_URL . 'assets/css/blocks/events-block.css',
            [],
            EVENTOR_INTEGRATION_VERSION
        );
    }

    public function register_block_assets() {
        // Register block editor style
        wp_register_style(
            'eventor-events-block-editor',
            EVENTOR_INTEGRATION_PLUGIN_URL . 'assets/css/blocks/events-block-editor.css',
            [],
            EVENTOR_INTEGRATION_VERSION
        );
    }
} 