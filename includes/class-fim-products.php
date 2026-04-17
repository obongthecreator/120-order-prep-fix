<?php
/**
 * Products Class
 *
 * @package Fruit_Inventory_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class FIM_Products {
    
    // Database instance
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new FIM_DB();
    }
    
    /**
     * Create default products from the provided screenshots
     */
    public function create_default_products() {
        $products = array(
            // Fruit Salads
            array('name' => 'Throne Feast Fruit Salad', 'type' => 'fruit_salad'),
            array('name' => 'Premium Fruit Salad', 'type' => 'fruit_salad'),
            array('name' => 'Mini Premium Fruit Salad', 'type' => 'fruit_salad'),
            array('name' => 'Standard Fruit Salad', 'type' => 'fruit_salad'),
            array('name' => 'Mini Standard Fruit Salad', 'type' => 'fruit_salad'),
            array('name' => 'Watermelon Salad', 'type' => 'fruit_salad'),
            array('name' => 'Weight Gain Fruit Salad', 'type' => 'fruit_salad'),
            array('name' => 'Weight Loss Fruit Salad', 'type' => 'fruit_salad'),
            
            // Parfaits
            array('name' => 'Premium Parfait', 'type' => 'parfait'),
            array('name' => 'Standard Parfait', 'type' => 'parfait'),
            array('name' => 'Mini Standard Parfait', 'type' => 'parfait'),
            array('name' => 'Nut Parfait', 'type' => 'parfait'),
            array('name' => 'Cake Parfait', 'type' => 'parfait'),
            array('name' => 'Fruit Parfait', 'type' => 'parfait'),
            
            // Milk Drinks
            array('name' => 'Tiger Nut Milk', 'type' => 'milk'),
            array('name' => 'Standard Milk Shake', 'type' => 'milk_shake'),
            array('name' => 'Mini Standard Milk Shake', 'type' => 'milk_shake'),
            array('name' => 'Chocolate Milk Shake', 'type' => 'milk_shake'),
            array('name' => 'Strawberry Milk Shake', 'type' => 'milk_shake'),
            
            // Juices
            array('name' => 'Watermelon Fruit Juice', 'type' => 'juice'),
            array('name' => 'Tropical Blend Fruit Juice', 'type' => 'juice'),
            array('name' => 'Pine-Ginger Blast Fruit Juice', 'type' => 'juice'),
            
            // Slushies
            array('name' => 'Slushie Watermelon', 'type' => 'slushie'),
            array('name' => 'Slushie Pineapple', 'type' => 'slushie'),
            
            // Smoothies
            array('name' => 'Premium Smoothie', 'type' => 'smoothie'),
            array('name' => 'Standard Smoothie', 'type' => 'smoothie'),
            array('name' => 'Weight Gain Smoothie(₦ 8,500)', 'type' => 'smoothie'),
            array('name' => 'Weight Loss Smoothie', 'type' => 'smoothie'),
            array('name' => 'Weight Gain Smoothie(₦ 5,500)', 'type' => 'smoothie'),
            array('name' => 'Libido Smoothie', 'type' => 'smoothie'),
            array('name' => 'Golden Cream Blend', 'type' => 'smoothie'),
            
            // Others
            array('name' => 'Bottle Water', 'type' => 'water'),
            array('name' => 'Mocktail', 'type' => 'mocktail'),
            array('name' => 'Fruit Cake', 'type' => 'cake'),
            array('name' => 'Frappuccino', 'type' => 'frappuccino'),
            array('name' => 'Chocolate Frappuccino', 'type' => 'frappuccino')
        );
        
        foreach ($products as $product) {
            // Check if product already exists by name before adding
            $existing_product = $this->get_product_by_name($product['name']);
            if (!$existing_product) {
                $this->db->add_product($product);
            }
        }
    }
    
    /**
     * Get product by name
     */
    public function get_product_by_name($name) {
        global $wpdb;
        
        $products_table = $wpdb->prefix . 'fim_products';
        
        $query = $wpdb->prepare(
            "SELECT * FROM $products_table WHERE product_name = %s LIMIT 1",
            $name
        );
        
        return $wpdb->get_row($query, ARRAY_A);
    }
    
    /**
     * Get all products
     * Modified to return only unique products by name, sorted alphabetically
     */
    public function get_products($args = array()) {
        $products = $this->db->get_products($args);
        
        // Filter out duplicates by product name
        $unique_products = array();
        $product_names = array();
        
        foreach ($products as $product) {
            if (!in_array($product['product_name'], $product_names)) {
                $product_names[] = $product['product_name'];
                $unique_products[] = $product;
            }
        }
        
        // Sort alphabetically by product name
        usort($unique_products, function($a, $b) {
            return strcasecmp($a['product_name'], $b['product_name']);
        });
        
        return $unique_products;
    }
    
    /**
     * Get product by ID
     */
    public function get_product($id) {
        return $this->db->get_product($id);
    }
    
    /**
     * Add product
     */
    public function add_product($data) {
        // Validate data
        if (empty($data['name']) || empty($data['type'])) {
            return false;
        }
        
        return $this->db->add_product($data);
    }
    
    /**
     * Update product
     */
    public function update_product($id, $data) {
        // Validate data
        if (empty($data['name']) || empty($data['type'])) {
            return false;
        }
        
        return $this->db->update_product($id, $data);
    }
    
    /**
     * Delete product
     */
    public function delete_product($id) {
        return $this->db->delete_product($id);
    }
    
    /**
     * Get product categories
     */
    public function get_product_categories() {
        return array(
            'fruit_salad' => __('Fruit Salad', 'fruit-inventory-manager'),
            'parfait' => __('Parfait', 'fruit-inventory-manager'),
            'milk' => __('Milk', 'fruit-inventory-manager'),
            'milk_shake' => __('Milk Shake', 'fruit-inventory-manager'),
            'juice' => __('Juice', 'fruit-inventory-manager'),
            'slushie' => __('Slushie', 'fruit-inventory-manager'),
            'smoothie' => __('Smoothie', 'fruit-inventory-manager'),
            'water' => __('Water', 'fruit-inventory-manager'),
            'mocktail' => __('Mocktail', 'fruit-inventory-manager'),
            'cake' => __('Cake', 'fruit-inventory-manager'),
            'frappuccino' => __('Frappuccino', 'fruit-inventory-manager'),
        );
    }
    
    /**
     * Get opening inventory for a product
     * 
     * Gets the closing value from the previous day to use as opening value
     */
    public function get_opening_inventory($product_id, $date) {
        // Get the latest inventory entry for this product before the given date
        global $wpdb;
        
        $inventory_table = $wpdb->prefix . 'fim_inventory';
        
        $query = $wpdb->prepare(
            "SELECT closing 
             FROM $inventory_table
             WHERE product_id = %d 
             AND date_of_preparation < %s
             ORDER BY date_of_preparation DESC, id DESC
             LIMIT 1",
            $product_id,
            $date
        );
        
        $result = $wpdb->get_var($query);
        
        // If no previous entry, return 0
        if ($result === null) {
            return 0;
        }
        
        return floatval($result);
    }
    
    /**
     * Calculate closing inventory
     */
    public function calculate_closing($opening, $total_added, $total_sold) {
        return floatval($opening) + floatval($total_added) - floatval($total_sold);
    }
}