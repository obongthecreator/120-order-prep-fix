<?php
/**
 * Plugin Name: 120 Fruit Inventory Manager
 * Plugin URI: https://120fruit.com
 * Description: A sleek and functional inventory management system for tracking fruit products with real-time calculations.
 * Version: 1.0.0
 * Author: Okonudo EseAbasi
 * Text Domain: fruit-inventory-manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Fruit_Inventory_Manager {
    
    // Plugin version
    const VERSION = '1.0.0';
    
    // Plugin instance
    private static $instance = null;
    
    // Plugin initialization
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        // Define constants
        $this->define_constants();
        
        // Include required files
        $this->includes();
        
        // Initialize hooks
        $this->init_hooks();
        
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Define constants
     */
    private function define_constants() {
        define('FIM_PLUGIN_FILE', __FILE__);
        define('FIM_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('FIM_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('FIM_PLUGIN_VERSION', self::VERSION);
        define('FIM_MAIN_COLOR', '#FF0000');
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Core files
        require_once FIM_PLUGIN_DIR . 'includes/class-fim-db.php';
        require_once FIM_PLUGIN_DIR . 'includes/class-fim-products.php';
        require_once FIM_PLUGIN_DIR . 'includes/class-fim-frontend.php';
        require_once FIM_PLUGIN_DIR . 'includes/class-fim-ajax.php';
        
        // Admin
        if (is_admin()) {
            require_once FIM_PLUGIN_DIR . 'includes/admin/class-fim-admin.php';
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('fruit-inventory-manager', false, dirname(plugin_basename(FIM_PLUGIN_FILE)) . '/languages');
    }
    
    /**
 * Enqueue frontend assets
 */
public function enqueue_frontend_assets() {
    // Styles
    wp_enqueue_style('fim-frontend-css', FIM_PLUGIN_URL . 'assets/css/frontend.css', array(), FIM_PLUGIN_VERSION);
    wp_enqueue_style('fim-animations-css', FIM_PLUGIN_URL . 'assets/css/animations.css', array(), FIM_PLUGIN_VERSION);
    
    // Scripts
    wp_enqueue_script('fim-frontend-js', FIM_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), FIM_PLUGIN_VERSION, true);
    
    // Localize script
    $params = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('fim-nonce'),
        'main_color' => FIM_MAIN_COLOR,
        'current_user' => wp_get_current_user()->display_name,
        'current_date' => current_time('Y-m-d'),
        'current_time' => current_time('H:i:s'),
        'is_admin' => current_user_can('manage_options') ? '1' : '0',
    );
    
    // Allow filtering of localized script params
    $params = apply_filters('fim_frontend_localize_script', $params);
    
    wp_localize_script('fim-frontend-js', 'fim_params', $params);
}
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        $fim_pages = array(
            'toplevel_page_fruit-inventory-manager',
            'fruit-inventory_page_fim-products',
            'fruit-inventory_page_fim-inventory',
            'fruit-inventory_page_fim-logs',
            'fruit-inventory_page_fim-settings',
        );
        
        if (!in_array($hook, $fim_pages)) {
            return;
        }
        
        // Styles
        wp_enqueue_style('fim-admin-css', FIM_PLUGIN_URL . 'assets/css/admin.css', array(), FIM_PLUGIN_VERSION);
        wp_enqueue_style('fim-animations-css', FIM_PLUGIN_URL . 'assets/css/animations.css', array(), FIM_PLUGIN_VERSION);
        
        // Scripts
        wp_enqueue_script('fim-admin-js', FIM_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), FIM_PLUGIN_VERSION, true);
        
        // Localize script
        wp_localize_script('fim-admin-js', 'fim_admin_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fim-admin-nonce'),
            'main_color' => FIM_MAIN_COLOR,
        ));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $db = new FIM_DB();
        $db->create_tables();
        
        // Create default products from the provided screenshots
        $products = new FIM_Products();
        $products->create_default_products();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

/**
 * Returns the main instance of the plugin
 */
function Fruit_Inventory_Manager() {
    return Fruit_Inventory_Manager::instance();
}

// Initialize the plugin
Fruit_Inventory_Manager();