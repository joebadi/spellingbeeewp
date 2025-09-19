<?php
/**
 * Plugin Name: Spelling Bee Pro
 * Plugin URI: https://omafurufoundation.org
 * Description: Complete competition management system for spelling bee competitions with registration, video management, and user integration.
 * Version: 1.0.0
 * Author: E-Clicks Solutions
 * Author URI: https://www.e-clicks.net
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: spelling-bee-pro
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Prevent duplicate plugin loading - check for any OSB constants or functions
if (defined('OSB_PLUGIN_VERSION') ||
    defined('OSB_TABLE_PREFIX') ||
    function_exists('osb_init_plugin') ||
    class_exists('OmafuruSpellingBee')) {

    // Add admin notice about duplicate installation
    add_action('admin_notices', function() {
        $current_plugin = plugin_basename(__FILE__);
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p><strong>Spelling Bee Pro - Duplicate Installation Detected:</strong><br>';
        echo 'Plugin "' . esc_html($current_plugin) . '" cannot load because another instance is already active.<br>';
        echo 'Please deactivate and remove duplicate plugin folders: <code>/spellingbee/</code> and <code>/spellingbee-1/</code><br>';
        echo 'Keep only ONE installation to avoid conflicts.</p>';
        echo '</div>';
    });

    // Log the duplicate detection
    if (function_exists('error_log')) {
        error_log('[Spelling Bee Pro] Duplicate installation detected - Plugin ' . plugin_basename(__FILE__) . ' blocked from loading');
    }

    return; // Plugin already loaded - exit immediately
}

// Enable error logging for debugging
if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', true);
}

// Function to log errors safely
if (!function_exists('osb_log_error')) {
    function osb_log_error($message, $context = []) {
        if (function_exists('error_log')) {
            $log_message = '[Spelling Bee Pro] ' . $message;
            if (!empty($context)) {
                $log_message .= ' | Context: ' . print_r($context, true);
            }
            error_log($log_message);
        }
    }
}

// Function to check plugin directory structure
if (!function_exists('osb_check_plugin_structure')) {
    function osb_check_plugin_structure() {
    $required_dirs = ['includes', 'admin', 'public', 'templates'];
    $required_files = [
        'includes/class-activator.php',
        'includes/class-deactivator.php',
        'includes/class-database.php',
        'includes/class-user-manager.php',
        'includes/class-email-handler.php',
        'includes/class-file-handler.php',
        'includes/class-donation-calculator.php',
        'includes/class-shortcodes.php',
        'includes/class-database-migration.php',
        'includes/class-school-classifier.php',
        'includes/class-workflow-automation.php',
        'admin/class-admin-menu.php',
        'admin/class-admin-ajax.php',
        'public/class-frontend.php'
    ];

    osb_log_error("Checking plugin structure");

    foreach ($required_dirs as $dir) {
        $dir_path = OSB_PLUGIN_PATH . $dir;
        if (!is_dir($dir_path)) {
            osb_log_error("Missing directory: {$dir}");
            return false;
        }
    }

    foreach ($required_files as $file) {
        $file_path = OSB_PLUGIN_PATH . $file;
        if (!file_exists($file_path)) {
            osb_log_error("Missing required file: {$file}");
            return false;
        }
    }

    osb_log_error("Plugin structure check passed");
    return true;
    }
}

// Set up error handler for plugin activation
if (!function_exists('osb_error_handler')) {
    function osb_error_handler($errno, $errstr, $errfile, $errline) {
    $error_msg = "Fatal Error: {$errstr} in {$errfile} on line {$errline}";
    osb_log_error($error_msg);

    // If it's a fatal error during activation, provide helpful information
    if (strpos($errfile, 'spellingbee') !== false) {
        osb_log_error("Plugin activation error detected in Spelling Bee Pro plugin", [
            'file' => $errfile,
            'line' => $errline,
            'error' => $errstr
        ]);
    }

    return false; // Let WordPress handle the error normally
    }
}

// Set custom error handler
set_error_handler('osb_error_handler');

// Log plugin loading start
osb_log_error("Plugin loading started");

// Log environment info
osb_log_error("Environment info", [
    'PHP_VERSION' => PHP_VERSION,
    'WP_VERSION' => defined('ABSPATH') ? get_bloginfo('version') : 'Unknown',
    'PLUGIN_PATH' => __FILE__,
    'WP_DEBUG' => defined('WP_DEBUG') ? WP_DEBUG : false,
    'WP_DEBUG_LOG' => defined('WP_DEBUG_LOG') ? WP_DEBUG_LOG : false
]);

try {
    // Define plugin constants
    define('OSB_PLUGIN_URL', plugin_dir_url(__FILE__));
    define('OSB_PLUGIN_PATH', plugin_dir_path(__FILE__));
    define('OSB_PLUGIN_VERSION', '1.0.0');
    define('OSB_PLUGIN_BASENAME', plugin_basename(__FILE__));
    define('OSB_TABLE_PREFIX', 'osb_');

    osb_log_error("Plugin constants defined successfully");

    // Check plugin structure before proceeding
    if (!osb_check_plugin_structure()) {
        throw new Exception("Plugin structure validation failed - missing required files or directories");
    }

} catch (Exception $e) {
    osb_log_error("Error defining plugin constants: " . $e->getMessage());
    return;
} catch (Error $e) {
    osb_log_error("Fatal error defining plugin constants: " . $e->getMessage());
    return;
}

/**
 * Main Plugin Class
 */
if (!class_exists('OmafuruSpellingBee')) {
class OmafuruSpellingBee {

    /**
     * Plugin instance
     */
    private static $instance = null;

    /**
     * Get plugin instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        try {
            osb_log_error("Starting plugin constructor");

            $this->loadDependencies();
            osb_log_error("Dependencies loaded, defining hooks");

            $this->defineHooks();
            osb_log_error("Hooks defined, initializing plugin");

            $this->initializePlugin();

            // Initialize workflow automation
            OSB_Workflow_Automation::getInstance();
            osb_log_error("Plugin constructor completed successfully");

        } catch (Exception $e) {
            osb_log_error("Exception in plugin constructor: " . $e->getMessage());
            throw $e;
        } catch (Error $e) {
            osb_log_error("Fatal error in plugin constructor: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Load required dependencies
     */
    private function loadDependencies() {
        try {
            osb_log_error("Starting to load plugin dependencies");

            // Core classes with individual error checking
            $core_files = [
                'includes/class-activator.php',
                'includes/class-deactivator.php',
                'includes/class-database.php',
                'includes/class-user-manager.php',
                'includes/class-email-handler.php',
                'includes/class-file-handler.php',
                'includes/class-donation-calculator.php',
                'includes/class-shortcodes.php',
                'includes/class-database-migration.php',
                'includes/class-school-classifier.php',
                'includes/class-workflow-automation.php'
            ];

            foreach ($core_files as $file) {
                $file_path = OSB_PLUGIN_PATH . $file;
                if (!file_exists($file_path)) {
                    osb_log_error("Missing core file: {$file}");
                    throw new Exception("Missing required file: {$file}");
                }

                osb_log_error("Loading core file: {$file}");
                require_once $file_path;
                osb_log_error("Successfully loaded: {$file}");
            }

            // Admin classes
            if (is_admin()) {
                $admin_files = [
                    'admin/class-admin-menu.php',
                    'admin/class-admin-ajax.php'
                ];

                foreach ($admin_files as $file) {
                    $file_path = OSB_PLUGIN_PATH . $file;
                    if (!file_exists($file_path)) {
                        osb_log_error("Missing admin file: {$file}");
                        throw new Exception("Missing required admin file: {$file}");
                    }

                    osb_log_error("Loading admin file: {$file}");
                    require_once $file_path;
                    osb_log_error("Successfully loaded: {$file}");
                }
            }

            // Public classes
            $public_files = ['public/class-frontend.php'];
            foreach ($public_files as $file) {
                $file_path = OSB_PLUGIN_PATH . $file;
                if (!file_exists($file_path)) {
                    osb_log_error("Missing public file: {$file}");
                    throw new Exception("Missing required public file: {$file}");
                }

                osb_log_error("Loading public file: {$file}");
                require_once $file_path;
                osb_log_error("Successfully loaded: {$file}");
            }

            osb_log_error("All dependencies loaded successfully");

        } catch (Exception $e) {
            osb_log_error("Exception while loading dependencies: " . $e->getMessage());
            throw $e;
        } catch (Error $e) {
            osb_log_error("Fatal error while loading dependencies: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Define WordPress hooks
     */
    private function defineHooks() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array('OSB_Activator', 'activate'));
        register_deactivation_hook(__FILE__, array('OSB_Deactivator', 'deactivate'));

        // Initialize components
        add_action('plugins_loaded', array($this, 'initializeComponents'));
        add_action('init', array($this, 'loadTextDomain'));
        add_action('admin_init', array($this, 'checkDatabaseVersion'));

        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
        add_action('wp_enqueue_scripts', array($this, 'publicEnqueueScripts'));

        // AJAX hooks
        add_action('wp_ajax_osb_admin_action', array('OSB_Admin_Ajax', 'handleAjaxRequest'));
        add_action('wp_ajax_nopriv_osb_admin_action', array('OSB_Admin_Ajax', 'handleAjaxRequest')); // Allow frontend access
        add_action('wp_ajax_nopriv_osb_public_action', array('OSB_Frontend', 'handlePublicAjax'));

        // Admin form handlers
        add_action('admin_post_osb_save_event', array($this, 'handleSaveEvent'));
        add_action('admin_post_osb_save_school', array($this, 'handleSaveSchool'));
        add_action('admin_post_osb_save_student', array($this, 'handleSaveStudent'));
    }

    /**
     * Initialize plugin components
     */
    public function initializeComponents() {
        // Initialize core components
        OSB_Database::getInstance();
        OSB_User_Manager::getInstance();
        OSB_Email_Handler::getInstance();
        OSB_Shortcodes::getInstance();

        // Initialize admin components
        if (is_admin()) {
            OSB_Admin_Menu::getInstance();
        }

        // Initialize frontend components
        OSB_Frontend::getInstance();
    }

    /**
     * Initialize the plugin
     */
    private function initializePlugin() {
        // Run any initialization code here
        do_action('osb_plugin_loaded', $this);
    }

    /**
     * Load plugin text domain for translations
     */
    public function loadTextDomain() {
        load_plugin_textdomain(
            'omafuru-spelling-bee',
            false,
            dirname(OSB_PLUGIN_BASENAME) . '/languages/'
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function adminEnqueueScripts($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'spelling-bee') === false) {
            return;
        }

        // Enqueue WordPress media uploader
        wp_enqueue_media();

        wp_enqueue_style(
            'osb-admin-style',
            OSB_PLUGIN_URL . 'admin/css/admin-style.css',
            array(),
            OSB_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'osb-admin-script',
            OSB_PLUGIN_URL . 'admin/js/admin-script.js',
            array('jquery', 'wp-util', 'media-upload', 'media-views'),
            OSB_PLUGIN_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script('osb-admin-script', 'osb_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('osb_admin_nonce'),
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this item?', 'spelling-bee-pro'),
                'error_message' => __('An error occurred. Please try again.', 'spelling-bee-pro'),
                'success_message' => __('Operation completed successfully.', 'spelling-bee-pro')
            )
        ));
    }

    /**
     * Enqueue public scripts and styles
     */
    public function publicEnqueueScripts() {
        wp_enqueue_style(
            'osb-public-style',
            OSB_PLUGIN_URL . 'public/css/public-style.css',
            array(),
            OSB_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'osb-public-script',
            OSB_PLUGIN_URL . 'public/js/public-script.js',
            array('jquery'),
            OSB_PLUGIN_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script('osb-public-script', 'osb_public_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('osb_public_nonce'),
            'strings' => array(
                'loading' => __('Loading...', 'omafuru-spelling-bee'),
                'error' => __('Error occurred. Please try again.', 'omafuru-spelling-bee'),
                'success' => __('Success!', 'omafuru-spelling-bee')
            )
        ));
    }

    /**
     * Get plugin version
     */
    public function getVersion() {
        return OSB_PLUGIN_VERSION;
    }

    /**
     * Get plugin path
     */
    public function getPluginPath() {
        return OSB_PLUGIN_PATH;
    }

    /**
     * Get plugin URL
     */
    public function getPluginUrl() {
        return OSB_PLUGIN_URL;
    }

    /**
     * Check if database needs upgrading
     */
    public function checkDatabaseVersion() {
        $current_version = get_option('osb_db_version', '1.0.0');
        if (version_compare($current_version, '1.1.0', '<')) {
            OSB_Activator::upgradeDatabaseSchema();
            update_option('osb_db_version', '1.1.0');
        }
    }

    /**
     * Handle save event form submission
     */
    public function handleSaveEvent() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['osb_event_nonce'], 'osb_save_event')) {
            wp_die(__('Security check failed.', 'spelling-bee-pro'));
        }

        // Check permissions
        if (!current_user_can('osb_manage_events')) {
            wp_die(__('You do not have permission to manage events.', 'spelling-bee-pro'));
        }

        $event_id = intval($_POST['event_id'] ?? 0);
        $event_data = array(
            'name' => sanitize_text_field($_POST['title']),
            'title' => sanitize_text_field($_POST['title']),
            'description' => sanitize_textarea_field($_POST['description']),
            'year' => intval($_POST['year']),
            'start_date' => sanitize_text_field($_POST['event_date']),
            'start_time' => sanitize_text_field($_POST['event_time']),
            'event_date' => sanitize_text_field($_POST['event_date']),
            'event_time' => sanitize_text_field($_POST['event_time']),
            'venue_name' => sanitize_text_field($_POST['venue_name']),
            'venue_address' => sanitize_textarea_field($_POST['venue_address']),
            'status' => sanitize_text_field($_POST['status']),
            'max_students_per_school' => intval($_POST['max_students_per_school']),
            'registration_deadline' => sanitize_text_field($_POST['registration_deadline']),
            'prize_fund_goal' => floatval($_POST['prize_fund_goal']),
            'flyer_url' => esc_url_raw($_POST['flyer_url']),
            'created_by' => get_current_user_id()
        );

        $db = OSB_Database::getInstance();

        if ($event_id) {
            // Update existing event
            global $wpdb;
            $result = $wpdb->update(
                $wpdb->prefix . 'osb_events',
                $event_data,
                array('id' => $event_id),
                array('%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%f', '%s', '%d'),
                array('%d')
            );
            $message = __('Event updated successfully.', 'spelling-bee-pro');
        } else {
            // Create new event
            $event_data['created_at'] = current_time('mysql');
            global $wpdb;
            $result = $wpdb->insert(
                $wpdb->prefix . 'osb_events',
                $event_data,
                array('%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%f', '%s', '%d', '%s')
            );
            $event_id = $wpdb->insert_id;
            $message = __('Event created successfully.', 'spelling-bee-pro');
        }

        if ($result !== false) {
            $redirect_url = admin_url('admin.php?page=spelling-bee-events&action=edit&event_id=' . $event_id . '&saved=1');
        } else {
            $redirect_url = admin_url('admin.php?page=spelling-bee-events&action=' . ($event_id ? 'edit' : 'new') . '&event_id=' . $event_id . '&error=1');
        }

        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Handle save school form submission
     */
    public function handleSaveSchool() {
        // Similar implementation for schools
        wp_die(__('School save handler not yet implemented.', 'spelling-bee-pro'));
    }

    /**
     * Handle save student form submission
     */
    public function handleSaveStudent() {
        // Similar implementation for students
        wp_die(__('Student save handler not yet implemented.', 'spelling-bee-pro'));
    }
}
} // End class exists check

/**
 * Initialize the plugin
 */
if (!function_exists('osb_init_plugin')) {
    function osb_init_plugin() {
    try {
        osb_log_error("Initializing main plugin instance");
        $instance = OmafuruSpellingBee::getInstance();
        osb_log_error("Plugin instance created successfully");
        return $instance;
    } catch (Exception $e) {
        osb_log_error("Exception during plugin initialization: " . $e->getMessage());

        // Add admin notice for activation errors
        add_action('admin_notices', function() use ($e) {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>Spelling Bee Pro activation failed:</strong> ' . esc_html($e->getMessage());
            echo '</p></div>';
        });

        return false;
    } catch (Error $e) {
        osb_log_error("Fatal error during plugin initialization: " . $e->getMessage());

        // Add admin notice for fatal errors
        add_action('admin_notices', function() use ($e) {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>Spelling Bee Pro fatal error:</strong> ' . esc_html($e->getMessage());
            echo '</p></div>';
        });

        return false;
    }
    }
}

// Start the plugin with error handling
if (!defined('OSB_PLUGIN_INITIALIZED')) {
    define('OSB_PLUGIN_INITIALIZED', true);

    try {
        osb_log_error("Starting plugin initialization");
        osb_init_plugin();
    } catch (Throwable $e) {
        osb_log_error("Critical error during plugin startup: " . $e->getMessage());

        // Deactivate plugin if critical error occurs
        if (function_exists('deactivate_plugins')) {
            deactivate_plugins(plugin_basename(__FILE__));
        }
    }
}

/**
 * Helper function to get plugin instance
 */
if (!function_exists('osb')) {
    function osb() {
        return OmafuruSpellingBee::getInstance();
    }
}