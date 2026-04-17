<?php
/**
 * AJAX Handler Class
 *
 * @package Fruit_Inventory_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class FIM_AJAX {
    
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
        
        // Frontend AJAX handlers
        add_action('wp_ajax_fim_submit_inventory', array($this, 'handle_submit_inventory'));
        add_action('wp_ajax_fim_get_opening_value', array($this, 'handle_get_opening_value'));
        add_action('wp_ajax_fim_load_records', array($this, 'handle_load_records'));
        add_action('wp_ajax_fim_get_inventory_record', array($this, 'handle_get_inventory_record')); // New handler
        
        // Admin AJAX handlers
        add_action('wp_ajax_fim_admin_get_product', array($this, 'handle_admin_get_product'));
        add_action('wp_ajax_fim_admin_save_product', array($this, 'handle_admin_save_product'));
        add_action('wp_ajax_fim_admin_delete_product', array($this, 'handle_admin_delete_product'));
        add_action('wp_ajax_fim_admin_get_inventory', array($this, 'handle_admin_get_inventory'));
        add_action('wp_ajax_fim_admin_get_inventory_list', array($this, 'handle_admin_get_inventory_list'));
        add_action('wp_ajax_fim_admin_save_inventory', array($this, 'handle_admin_save_inventory'));
        add_action('wp_ajax_fim_admin_delete_inventory', array($this, 'handle_admin_delete_inventory'));
        add_action('wp_ajax_fim_admin_clear_inventory', array($this, 'handle_admin_clear_inventory'));
        add_action('wp_ajax_fim_admin_get_logs', array($this, 'handle_admin_get_logs'));
    }
    
    /**
     * Handle inventory form submission
     */
    public function handle_submit_inventory() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fim-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'fruit-inventory-manager')));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to submit the form.', 'fruit-inventory-manager')));
        }
        
        // Get current user
        $current_user = wp_get_current_user();
        $is_admin = current_user_can('manage_options');
        
        // Get form data
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : current_time('Y-m-d');
        $remarks = isset($_POST['remarks']) ? sanitize_textarea_field($_POST['remarks']) : '';
        
        // Get products data
        $products_data = isset($_POST['products']) ? $_POST['products'] : array();
        
        if (empty($products_data)) {
            wp_send_json_error(array('message' => __('No product data submitted.', 'fruit-inventory-manager')));
        }
        
        // Process each product
        $success_count = 0;
        $errors = array();
        $updated_records = array(); // Track updated records to return to client
        
        foreach ($products_data as $product_id => $data) {
            // Sanitize data
            $product_id = absint($product_id);
            $opening = isset($data['opening']) ? floatval($data['opening']) : 0;
            $total_added = isset($data['total_added']) ? floatval($data['total_added']) : 0;
            $total_sold = isset($data['total_sold']) ? floatval($data['total_sold']) : 0;
            
            // For admin submissions, use the provided closing value directly if it exists
            if ($is_admin && isset($data['closing'])) {
                $closing = floatval($data['closing']);
            } else {
                // For staff or if admin didn't provide closing, calculate it
                $closing = $this->products->calculate_closing($opening, $total_added, $total_sold);
            }
            
            // Prepare inventory data
            $inventory_data = array(
                'product_id' => $product_id,
                'staff_id' => $current_user->ID,
                'staff_name' => $current_user->display_name,
                'date' => $date,
                'opening' => $opening,
                'total_added' => $total_added,
                'total_sold' => $total_sold,
                'closing' => $closing,
                'remarks' => $remarks,
            );
            
            // Check if entry already exists for this product and date
            $existing_entries = $this->db->get_inventory(array(
                'product_id' => $product_id,
                'date' => $date,
            ));
            
            if (!empty($existing_entries)) {
                // Update existing entry
                $entry_id = $existing_entries[0]['id'];
                $result = $this->db->update_inventory($entry_id, $inventory_data);
                
                if ($result === false) {
                    $errors[] = sprintf(__('Failed to update inventory for product ID %d.', 'fruit-inventory-manager'), $product_id);
                } else {
                    $success_count++;
                    
                    // Store updated record data
                    $updated_records[$product_id] = array(
                        'opening' => $opening,
                        'total_added' => $total_added,
                        'total_sold' => $total_sold,
                        'closing' => $closing
                    );
                    
                    // Log the update
                    $this->db->add_log(array(
                        'user_id' => $current_user->ID,
                        'action' => 'update',
                        'object_type' => 'inventory',
                        'object_id' => $entry_id,
                        'details' => json_encode($inventory_data),
                        'ip_address' => $this->get_client_ip(),
                    ));
                }
            } else {
                // Add new entry
                $entry_id = $this->db->add_inventory($inventory_data);
                
                if ($entry_id === false) {
                    $errors[] = sprintf(__('Failed to add inventory for product ID %d.', 'fruit-inventory-manager'), $product_id);
                } else {
                    $success_count++;
                    
                    // Store updated record data
                    $updated_records[$product_id] = array(
                        'opening' => $opening,
                        'total_added' => $total_added,
                        'total_sold' => $total_sold,
                        'closing' => $closing
                    );
                    
                    // Log the addition
                    $this->db->add_log(array(
                        'user_id' => $current_user->ID,
                        'action' => 'add',
                        'object_type' => 'inventory',
                        'object_id' => $entry_id,
                        'details' => json_encode($inventory_data),
                        'ip_address' => $this->get_client_ip(),
                    ));
                }
            }
            
            // IMPORTANT: Update tomorrow's opening values to be today's closing values
            $tomorrow = date('Y-m-d', strtotime($date . ' +1 day'));
            
            // Check if tomorrow's record already exists
            $tomorrow_entries = $this->db->get_inventory(array(
                'product_id' => $product_id,
                'date' => $tomorrow,
            ));
            
            if (!empty($tomorrow_entries)) {
                // Update tomorrow's opening value
                $tomorrow_id = $tomorrow_entries[0]['id'];
                $this->db->update_inventory($tomorrow_id, array(
                    'opening' => $closing,
                    'closing' => $closing + floatval($tomorrow_entries[0]['total_added']) - floatval($tomorrow_entries[0]['total_sold'])
                ));
            } else {
                // Create a new entry for tomorrow with opening value set to today's closing
                $this->db->add_inventory(array(
                    'product_id' => $product_id,
                    'staff_id' => $current_user->ID,
                    'staff_name' => $current_user->display_name,
                    'date' => $tomorrow,
                    'opening' => $closing,
                    'total_added' => 0,
                    'total_sold' => 0,
                    'closing' => $closing, // Initial closing is same as opening
                    'remarks' => __('Auto-generated from previous day closing', 'fruit-inventory-manager'),
                ));
            }
        }
        
        // Send response
        if ($success_count > 0) {
            $message = sprintf(
                __('%d product(s) successfully updated.', 'fruit-inventory-manager'),
                $success_count
            );
            
            if (!empty($errors)) {
                $message .= ' ' . implode(' ', $errors);
                wp_send_json_error(array('message' => $message));
            } else {
                // Include updated records in the response
                wp_send_json_success(array(
                    'message' => $message,
                    'updated_records' => $updated_records
                ));
            }
        } else {
            wp_send_json_error(array('message' => implode(' ', $errors)));
        }
    }
    
    /**
     * Handle getting opening value
     */
    public function handle_get_opening_value() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fim-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'fruit-inventory-manager')));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to access this data.', 'fruit-inventory-manager')));
        }
        
        // Get parameters
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : current_time('Y-m-d');
        
        if (empty($product_id)) {
            wp_send_json_error(array('message' => __('Invalid product ID.', 'fruit-inventory-manager')));
        }
        
        // Get opening value
        $opening_value = $this->products->get_opening_inventory($product_id, $date);
        
        // Send response
        wp_send_json_success(array('opening_value' => $opening_value));
    }

    /**
     * Handle getting inventory record for a product on a specific date
     */
    public function handle_get_inventory_record() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fim-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'fruit-inventory-manager')));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to access this data.', 'fruit-inventory-manager')));
        }
        
        // Get parameters
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : current_time('Y-m-d');
        
        if (empty($product_id)) {
            wp_send_json_error(array('message' => __('Invalid product ID.', 'fruit-inventory-manager')));
        }
        
        // Get inventory record for this product and date
        $record = $this->db->get_inventory(array(
            'product_id' => $product_id,
            'date' => $date,
            'limit' => 1
        ));
        
        if (!empty($record)) {
            // Return the record
            wp_send_json_success(array('record' => $record[0]));
        } else {
            // No record found for this date
            wp_send_json_success(array('record' => null));
        }
    }
    
    /**
     * Handle loading records
     */
    public function handle_load_records() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fim-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'fruit-inventory-manager')));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to access this data.', 'fruit-inventory-manager')));
        }
        
        // Get parameters
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : 20;
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
        
        // Prepare args
        $args = array(
            'limit' => $per_page,
            'offset' => ($page - 1) * $per_page,
        );
        
        if (!empty($product_id)) {
            $args['product_id'] = $product_id;
        }
        
        if (!empty($date_from) && !empty($date_to)) {
            $args['date_from'] = $date_from;
            $args['date_to'] = $date_to;
        }
        
        // Get records
        $records = $this->db->get_inventory($args);
        
        // Get total count for pagination
        $total_records = $this->db->count_inventory($args);
        $total_pages = ceil($total_records / $per_page);
        
        // Format data for display
        $formatted_records = array();
        
        foreach ($records as $record) {
            $formatted_records[] = array(
                'id' => $record['id'],
                'product_name' => $record['product_name'],
                'staff_name' => $record['staff_name'],
                'date' => date_i18n(get_option('date_format'), strtotime($record['date_of_preparation'])),
                'opening' => number_format($record['opening'], 2),
                'total_added' => number_format($record['total_added'], 2),
                'total_sold' => number_format($record['total_sold'], 2),
                'closing' => number_format($record['closing'], 2),
                'remarks' => $record['remarks'],
                'created_at' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($record['created_at'])),
            );
        }
        
        // Send response
        wp_send_json_success(array(
            'records' => $formatted_records,
            'total_records' => $total_records,
            'total_pages' => $total_pages,
            'current_page' => $page,
        ));
    }
    
    /**
     * Handle admin get product
     */
    public function handle_admin_get_product() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fim-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'fruit-inventory-manager')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'fruit-inventory-manager')));
        }
        
        // Get product ID
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        
        if (empty($product_id)) {
            wp_send_json_error(array('message' => __('Invalid product ID.', 'fruit-inventory-manager')));
        }
        
        // Get product
        $product = $this->products->get_product($product_id);
        
        if (!$product) {
            wp_send_json_error(array('message' => __('Product not found.', 'fruit-inventory-manager')));
        }
        
        // Send response
        wp_send_json_success(array('product' => $product));
    }
    
    /**
     * Handle admin save product
     */
    public function handle_admin_save_product() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fim-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'fruit-inventory-manager')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'fruit-inventory-manager')));
        }
        
        // Get form data
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $product_name = isset($_POST['product_name']) ? sanitize_text_field($_POST['product_name']) : '';
        $product_type = isset($_POST['product_type']) ? sanitize_text_field($_POST['product_type']) : '';
        $product_status = isset($_POST['product_status']) ? sanitize_text_field($_POST['product_status']) : 'active';
        
        if (empty($product_name) || empty($product_type)) {
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'fruit-inventory-manager')));
        }
        
        // Prepare product data
        $product_data = array(
            'name' => $product_name,
            'type' => $product_type,
            'status' => $product_status,
        );
        
        // Current user
        $current_user = wp_get_current_user();
        
        if ($product_id > 0) {
            // Update existing product
            $result = $this->products->update_product($product_id, $product_data);
            
            if ($result === false) {
                wp_send_json_error(array('message' => __('Failed to update product.', 'fruit-inventory-manager')));
            }
            
            // Log the update
            $this->db->add_log(array(
                'user_id' => $current_user->ID,
                'action' => 'update',
                'object_type' => 'product',
                'object_id' => $product_id,
                'details' => json_encode($product_data),
                'ip_address' => $this->get_client_ip(),
            ));
            
            wp_send_json_success(array(
                'message' => __('Product updated successfully.', 'fruit-inventory-manager'),
                'product_id' => $product_id,
            ));
        } else {
            // Add new product
            $product_id = $this->products->add_product($product_data);
            
            if ($product_id === false) {
                wp_send_json_error(array('message' => __('Failed to add product.', 'fruit-inventory-manager')));
            }
            
            // Log the addition
            $this->db->add_log(array(
                'user_id' => $current_user->ID,
                'action' => 'add',
                'object_type' => 'product',
                'object_id' => $product_id,
                'details' => json_encode($product_data),
                'ip_address' => $this->get_client_ip(),
            ));
            
            wp_send_json_success(array(
                'message' => __('Product added successfully.', 'fruit-inventory-manager'),
                'product_id' => $product_id,
            ));
        }
    }
    
    /**
     * Handle admin delete product
     */
    public function handle_admin_delete_product() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fim-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'fruit-inventory-manager')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'fruit-inventory-manager')));
        }
        
        // Get product ID
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        
        if (empty($product_id)) {
            wp_send_json_error(array('message' => __('Invalid product ID.', 'fruit-inventory-manager')));
        }
        
        // Delete product
        $result = $this->products->delete_product($product_id);
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to delete product.', 'fruit-inventory-manager')));
        }
        
        // Log the deletion
        $current_user = wp_get_current_user();
        $this->db->add_log(array(
            'user_id' => $current_user->ID,
            'action' => 'delete',
            'object_type' => 'product',
            'object_id' => $product_id,
            'details' => '',
            'ip_address' => $this->get_client_ip(),
        ));
        
        wp_send_json_success(array('message' => __('Product deleted successfully.', 'fruit-inventory-manager')));
    }
    
    /**
     * Handle admin get inventory
     */
    public function handle_admin_get_inventory() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fim-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'fruit-inventory-manager')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'fruit-inventory-manager')));
        }
        
        // Get inventory ID
        $inventory_id = isset($_POST['inventory_id']) ? absint($_POST['inventory_id']) : 0;
        
        if (empty($inventory_id)) {
            wp_send_json_error(array('message' => __('Invalid inventory ID.', 'fruit-inventory-manager')));
        }
        
        // Get inventory
        $inventory = $this->db->get_inventory_entry($inventory_id);
        
        if (!$inventory) {
            wp_send_json_error(array('message' => __('Inventory entry not found.', 'fruit-inventory-manager')));
        }
        
        // Send response
        wp_send_json_success(array('inventory' => $inventory));
    }
    
    /**
     * Handle admin get inventory list
     */
    public function handle_admin_get_inventory_list() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fim-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'fruit-inventory-manager')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'fruit-inventory-manager')));
        }
        
        // Get parameters
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : 20;
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
        
        // Prepare args
        $args = array(
            'limit' => $per_page,
            'offset' => ($page - 1) * $per_page,
        );
        
        if (!empty($product_id)) {
            $args['product_id'] = $product_id;
        }
        
        if (!empty($date_from) && !empty($date_to)) {
            $args['date_from'] = $date_from;
            $args['date_to'] = $date_to;
        }
        
        // Get records
        $records = $this->db->get_inventory($args);
        
        // Get total count for pagination
        $total_records = $this->db->count_inventory($args);
        $total_pages = ceil($total_records / $per_page);
        
        // Format data for display
        $formatted_records = array();
        
        foreach ($records as $record) {
            $formatted_records[] = array(
                'id' => $record['id'],
                'product_id' => $record['product_id'],
                'product_name' => $record['product_name'],
                'staff_id' => $record['staff_id'],
                'staff_name' => $record['staff_name'],
                'date' => date_i18n(get_option('date_format'), strtotime($record['date_of_preparation'])),
                'date_of_preparation' => $record['date_of_preparation'],
                'opening' => number_format($record['opening'], 2),
                'total_added' => number_format($record['total_added'], 2),
                'total_sold' => number_format($record['total_sold'], 2),
                'closing' => number_format($record['closing'], 2),
                'remarks' => $record['remarks'],
                'created_at' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($record['created_at'])),
            );
        }
        
        // Send response
        wp_send_json_success(array(
            'records' => $formatted_records,
            'total_records' => $total_records,
            'total_pages' => $total_pages,
            'current_page' => $page,
        ));
    }
    
    /**
     * Handle admin save inventory
     */
    public function handle_admin_save_inventory() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fim-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'fruit-inventory-manager')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'fruit-inventory-manager')));
        }
        
        // Get form data
        $inventory_id = isset($_POST['inventory_id']) ? absint($_POST['inventory_id']) : 0;
        $total_added = isset($_POST['total_added']) ? floatval($_POST['total_added']) : 0;
        $total_sold = isset($_POST['total_sold']) ? floatval($_POST['total_sold']) : 0;
        $remarks = isset($_POST['remarks']) ? sanitize_textarea_field($_POST['remarks']) : '';
        
        if (empty($inventory_id)) {
            wp_send_json_error(array('message' => __('Invalid inventory ID.', 'fruit-inventory-manager')));
        }
        
        // Get existing inventory entry
        $inventory = $this->db->get_inventory_entry($inventory_id);
        
        if (!$inventory) {
            wp_send_json_error(array('message' => __('Inventory entry not found.', 'fruit-inventory-manager')));
        }
        
        // Calculate closing value
        $closing = $this->products->calculate_closing($inventory['opening'], $total_added, $total_sold);
        
        // Prepare inventory data
        $inventory_data = array(
            'total_added' => $total_added,
            'total_sold' => $total_sold,
            'closing' => $closing,
            'remarks' => $remarks,
        );
        
        // Update inventory
        $result = $this->db->update_inventory($inventory_id, $inventory_data);
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to update inventory entry.', 'fruit-inventory-manager')));
        }
        
        // Log the update
        $current_user = wp_get_current_user();
        $this->db->add_log(array(
            'user_id' => $current_user->ID,
            'action' => 'update',
            'object_type' => 'inventory',
            'object_id' => $inventory_id,
            'details' => json_encode($inventory_data),
            'ip_address' => $this->get_client_ip(),
        ));
        
        wp_send_json_success(array('message' => __('Inventory entry updated successfully.', 'fruit-inventory-manager')));
    }
    
    /**
     * Handle admin delete inventory
     */
    public function handle_admin_delete_inventory() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fim-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'fruit-inventory-manager')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'fruit-inventory-manager')));
        }
        
        // Get inventory ID
        $inventory_id = isset($_POST['inventory_id']) ? absint($_POST['inventory_id']) : 0;
        
        if (empty($inventory_id)) {
            wp_send_json_error(array('message' => __('Invalid inventory ID.', 'fruit-inventory-manager')));
        }
        
        // Delete inventory
        $result = $this->db->delete_inventory($inventory_id);
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to delete inventory entry.', 'fruit-inventory-manager')));
        }
        
        // Log the deletion
        $current_user = wp_get_current_user();
        $this->db->add_log(array(
            'user_id' => $current_user->ID,
            'action' => 'delete',
            'object_type' => 'inventory',
            'object_id' => $inventory_id,
            'details' => '',
            'ip_address' => $this->get_client_ip(),
        ));
        
        wp_send_json_success(array('message' => __('Inventory entry deleted successfully.', 'fruit-inventory-manager')));
    }
    
    /**
     * Handle admin clear inventory
     */
    public function handle_admin_clear_inventory() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fim-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'fruit-inventory-manager')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'fruit-inventory-manager')));
        }
        
        // Clear inventory
        $result = $this->db->clear_inventory();
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to clear inventory.', 'fruit-inventory-manager')));
        }
        
        // Log the clearing
        $current_user = wp_get_current_user();
        $this->db->add_log(array(
            'user_id' => $current_user->ID,
            'action' => 'clear',
            'object_type' => 'inventory',
            'object_id' => 0,
            'details' => '',
            'ip_address' => $this->get_client_ip(),
        ));
        
        wp_send_json_success(array('message' => __('Inventory cleared successfully.', 'fruit-inventory-manager')));
    }
    
    /**
     * Handle admin get logs
     */
    public function handle_admin_get_logs() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fim-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'fruit-inventory-manager')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'fruit-inventory-manager')));
        }
        
        // Get parameters
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : 20;
        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
        $object_type = isset($_POST['object_type']) ? sanitize_text_field($_POST['object_type']) : '';
        
        // Prepare args
        $args = array(
            'limit' => $per_page,
            'offset' => ($page - 1) * $per_page,
        );
        
        if (!empty($user_id)) {
            $args['user_id'] = $user_id;
        }
        
        if (!empty($action)) {
            $args['action'] = $action;
        }
        
        if (!empty($object_type)) {
            $args['object_type'] = $object_type;
        }
        
        // Get logs
        $logs = $this->db->get_logs($args);
        
        // Format logs for display
        $formatted_logs = array();
        
        foreach ($logs as $log) {
            $user = get_user_by('id', $log['user_id']);
            $user_name = $user ? $user->display_name : __('Unknown', 'fruit-inventory-manager');
            
            $action_label = ucfirst($log['action']);
            $object_type_label = ucfirst($log['object_type']);
            
            $formatted_logs[] = array(
                'id' => $log['id'],
                'user_name' => $user_name,
                'action' => $action_label,
                'object_type' => $object_type_label,
                'object_id' => $log['object_id'],
                'details' => $log['details'],
                'ip_address' => $log['ip_address'],
                'created_at' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['created_at'])),
            );
        }
        
        // Send response
        wp_send_json_success(array('logs' => $formatted_logs));
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
new FIM_AJAX();