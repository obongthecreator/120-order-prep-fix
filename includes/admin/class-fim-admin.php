<?php
/**
 * Admin Class
 *
 * @package Fruit_Inventory_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class FIM_Admin {
    
    // Database instance
    private $db;
    
    // Products instance
    private $products;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new FIM_DB();
        $this->products = new FIM_Products();
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Fruit Inventory', 'fruit-inventory-manager'),
            __('Fruit Inventory', 'fruit-inventory-manager'),
            'manage_options',
            'fruit-inventory-manager',
            array($this, 'render_dashboard_page'),
            'dashicons-cart',
            25
        );
        
        // Dashboard submenu
        add_submenu_page(
            'fruit-inventory-manager',
            __('Dashboard', 'fruit-inventory-manager'),
            __('Dashboard', 'fruit-inventory-manager'),
            'manage_options',
            'fruit-inventory-manager',
            array($this, 'render_dashboard_page')
        );
        
        // Products submenu
        add_submenu_page(
            'fruit-inventory-manager',
            __('Products', 'fruit-inventory-manager'),
            __('Products', 'fruit-inventory-manager'),
            'manage_options',
            'fim-products',
            array($this, 'render_products_page')
        );
        
        // Inventory submenu
        add_submenu_page(
            'fruit-inventory-manager',
            __('Inventory', 'fruit-inventory-manager'),
            __('Inventory', 'fruit-inventory-manager'),
            'manage_options',
            'fim-inventory',
            array($this, 'render_inventory_page')
        );
        
        // Logs submenu
        add_submenu_page(
            'fruit-inventory-manager',
            __('Logs', 'fruit-inventory-manager'),
            __('Logs', 'fruit-inventory-manager'),
            'manage_options',
            'fim-logs',
            array($this, 'render_logs_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'fruit-inventory-manager',
            __('Settings', 'fruit-inventory-manager'),
            __('Settings', 'fruit-inventory-manager'),
            'manage_options',
            'fim-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        // Get stats
        $total_products = count($this->products->get_products());
        
        $today = current_time('Y-m-d');
        $today_entries = $this->db->count_inventory(array('date' => $today));
        
        $yesterday = date('Y-m-d', strtotime('yesterday', current_time('timestamp')));
        $yesterday_entries = $this->db->count_inventory(array('date' => $yesterday));
        
        $this_month_start = date('Y-m-01', current_time('timestamp'));
        $this_month_end = date('Y-m-t', current_time('timestamp'));
        $this_month_entries = $this->db->count_inventory(array(
            'date_from' => $this_month_start,
            'date_to' => $this_month_end,
        ));
        
        // Recent entries
        $recent_entries = $this->db->get_inventory(array(
            'limit' => 10,
            'orderby' => 'i.created_at',
            'order' => 'DESC',
        ));
        
        // Recent logs
        $recent_logs = $this->db->get_logs(array(
            'limit' => 10,
            'orderby' => 'created_at',
            'order' => 'DESC',
        ));
        
        // Include dashboard template
        include_once FIM_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }
    
    /**
     * Render products page
     */
    public function render_products_page() {
        // Get all products
        $products = $this->products->get_products();
        
        // Get product categories
        $product_categories = $this->products->get_product_categories();
        
        // Include products template
        include_once FIM_PLUGIN_DIR . 'templates/admin/products.php';
    }
    
    /**
     * Render inventory page
     */
    public function render_inventory_page() {
        // Get all products for filter
        $products = $this->products->get_products();
        
        // Include inventory template
        include_once FIM_PLUGIN_DIR . 'templates/admin/inventory.php';
    }
    
    /**
     * Render logs page
     */
    public function render_logs_page() {
        // Include logs template
        include_once FIM_PLUGIN_DIR . 'templates/admin/logs.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Save settings
        if (isset($_POST['fim_save_settings']) && check_admin_referer('fim_settings_nonce')) {
            // Process settings
            $shortcode_page_id = isset($_POST['fim_shortcode_page_id']) ? absint($_POST['fim_shortcode_page_id']) : 0;
            update_option('fim_shortcode_page_id', $shortcode_page_id);
            
            $records_page_id = isset($_POST['fim_records_page_id']) ? absint($_POST['fim_records_page_id']) : 0;
            update_option('fim_records_page_id', $records_page_id);
            
            $per_page = isset($_POST['fim_per_page']) ? absint($_POST['fim_per_page']) : 20;
            update_option('fim_per_page', $per_page);
            
            // Show success message
            add_settings_error(
                'fim_settings',
                'fim_settings_saved',
                __('Settings saved successfully.', 'fruit-inventory-manager'),
                'updated'
            );
        }
        
        // Get settings
        $shortcode_page_id = get_option('fim_shortcode_page_id', 0);
        $records_page_id = get_option('fim_records_page_id', 0);
        $per_page = get_option('fim_per_page', 20);
        
        // Include settings template
        include_once FIM_PLUGIN_DIR . 'templates/admin/settings.php';
    }
}

// Initialize the class
new FIM_Admin();