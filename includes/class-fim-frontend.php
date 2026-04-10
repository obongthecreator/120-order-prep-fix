<?php
/**
 * Frontend Class
 *
 * @package Fruit_Inventory_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class FIM_Frontend {
    
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
        
        // Register shortcodes
        add_shortcode('fruit_inventory_form', array($this, 'inventory_form_shortcode'));
        add_shortcode('fruit_inventory_records', array($this, 'inventory_records_shortcode'));
        
        // Modify script localization to include admin status
        add_filter('fim_frontend_localize_script', array($this, 'add_admin_status_to_script'));
    }
    
    /**
     * Add admin status to localized script
     */
    public function add_admin_status_to_script($params) {
        $params['is_admin'] = current_user_can('manage_options') ? '1' : '0';
        return $params;
    }
    
    /**
     * Inventory form shortcode
     */
    public function inventory_form_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<div class="fim-alert fim-alert-warning">' . __('You must be logged in to access this form.', 'fruit-inventory-manager') . '</div>';
        }
        
        // Get current user
        $current_user = wp_get_current_user();
        
        // Get all products
        $all_products = $this->products->get_products();
        
        // Get current date
        $current_date = current_time('Y-m-d');
        
        // Buffer output
        ob_start();
        
        // Include form template
        include_once FIM_PLUGIN_DIR . 'templates/inventory-form.php';
        
        return ob_get_clean();
    }
    
    /**
     * Inventory records shortcode
     */
    public function inventory_records_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<div class="fim-alert fim-alert-warning">' . __('You must be logged in to access this page.', 'fruit-inventory-manager') . '</div>';
        }
        
        // Parse attributes
        $atts = shortcode_atts(array(
            'per_page' => 20,
        ), $atts);
        
        // Get all products for filter
        $all_products = $this->products->get_products();
        
        // Buffer output
        ob_start();
        
        // Include records template
        include_once FIM_PLUGIN_DIR . 'templates/inventory-records.php';
        
        return ob_get_clean();
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        // Check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        
        // Check for IPs passing through proxies
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Can include multiple IPs, first is the real client IP
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ip_list[0]);
        }
        
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Initialize the class
new FIM_Frontend();