<?php
/**
 * Database Handler Class
 *
 * @package Fruit_Inventory_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class FIM_DB {
    
    // Tables
    private $products_table;
    private $inventory_table;
    private $logs_table;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        
        $this->products_table = $wpdb->prefix . 'fim_products';
        $this->inventory_table = $wpdb->prefix . 'fim_inventory';
        $this->logs_table = $wpdb->prefix . 'fim_logs';
    }
    
    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Products table
        $products_table = "CREATE TABLE {$this->products_table} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            product_name varchar(255) NOT NULL,
            product_type varchar(50) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // Inventory table
        $inventory_table = "CREATE TABLE {$this->inventory_table} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            product_id mediumint(9) NOT NULL,
            staff_id bigint(20) NOT NULL,
            staff_name varchar(100) NOT NULL,
            date_of_preparation date NOT NULL,
            opening float NOT NULL DEFAULT 0,
            total_added float NOT NULL DEFAULT 0,
            total_sold float NOT NULL DEFAULT 0,
            closing float NOT NULL DEFAULT 0,
            remarks text NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY product_id (product_id),
            KEY staff_id (staff_id),
            KEY date_of_preparation (date_of_preparation)
        ) $charset_collate;";
        
        // Logs table
        $logs_table = "CREATE TABLE {$this->logs_table} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            action varchar(50) NOT NULL,
            object_type varchar(50) NOT NULL,
            object_id mediumint(9) NOT NULL,
            details text NULL,
            ip_address varchar(100) NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY object_id (object_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($products_table);
        dbDelta($inventory_table);
        dbDelta($logs_table);
    }
    
    /**
     * Get all products
     */
    public function get_products($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => 'active',
            'orderby' => 'id',
            'order' => 'ASC',
            'limit' => -1,
            'offset' => 0,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = '';
        if (!empty($args['status'])) {
            $where = $wpdb->prepare("WHERE status = %s", $args['status']);
        }
        
        $limit = '';
        if ($args['limit'] > 0) {
            $limit = $wpdb->prepare("LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }
        
        $orderby = sanitize_sql_orderby("{$args['orderby']} {$args['order']}");
        if (!$orderby) {
            $orderby = "id ASC";
        }
        
        $query = "SELECT * FROM {$this->products_table} $where ORDER BY $orderby $limit";
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return $results;
    }
    
    /**
     * Get product by ID
     */
    public function get_product($id) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$this->products_table} WHERE id = %d",
            $id
        );
        
        $result = $wpdb->get_row($query, ARRAY_A);
        
        return $result;
    }
    
    /**
     * Add product
     */
    public function add_product($data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->products_table,
            array(
                'product_name' => $data['name'],
                'product_type' => $data['type'],
                'status' => isset($data['status']) ? $data['status'] : 'active',
            ),
            array('%s', '%s', '%s')
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update product
     */
    public function update_product($id, $data) {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->products_table,
            array(
                'product_name' => $data['name'],
                'product_type' => $data['type'],
                'status' => isset($data['status']) ? $data['status'] : 'active',
            ),
            array('id' => $id),
            array('%s', '%s', '%s'),
            array('%d')
        );
        
        return $result;
    }
    
    /**
     * Delete product
     */
    public function delete_product($id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->products_table,
            array('id' => $id),
            array('%d')
        );
        
        return $result;
    }
    
    /**
     * Get inventory entries
     */
    public function get_inventory($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'product_id' => 0,
            'staff_id' => 0,
            'date' => '',
            'date_from' => '',
            'date_to' => '',
            'orderby' => 'i.id',
            'order' => 'DESC',
            'limit' => -1,
            'offset' => 0,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array();
        
        if (!empty($args['product_id'])) {
            $where[] = $wpdb->prepare("i.product_id = %d", $args['product_id']);
        }
        
        if (!empty($args['staff_id'])) {
            $where[] = $wpdb->prepare("i.staff_id = %d", $args['staff_id']);
        }
        
        if (!empty($args['date'])) {
            $where[] = $wpdb->prepare("i.date_of_preparation = %s", $args['date']);
        }
        
        if (!empty($args['date_from']) && !empty($args['date_to'])) {
            $where[] = $wpdb->prepare(
                "i.date_of_preparation BETWEEN %s AND %s",
                $args['date_from'],
                $args['date_to']
            );
        }
        
        $where_clause = '';
        if (!empty($where)) {
            $where_clause = "WHERE " . implode(" AND ", $where);
        }
        
        $limit = '';
        if ($args['limit'] > 0) {
            $limit = $wpdb->prepare("LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }
        
        $orderby = sanitize_sql_orderby("{$args['orderby']} {$args['order']}");
        if (!$orderby) {
            $orderby = "i.id DESC";
        }
        
        $query = "SELECT i.*, p.product_name 
                  FROM {$this->inventory_table} i
                  LEFT JOIN {$this->products_table} p ON i.product_id = p.id
                  $where_clause
                  ORDER BY $orderby
                  $limit";
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return $results;
    }
    
    /**
     * Count inventory entries
     */
    public function count_inventory($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'product_id' => 0,
            'staff_id' => 0,
            'date' => '',
            'date_from' => '',
            'date_to' => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array();
        
        if (!empty($args['product_id'])) {
            $where[] = $wpdb->prepare("product_id = %d", $args['product_id']);
        }
        
        if (!empty($args['staff_id'])) {
            $where[] = $wpdb->prepare("staff_id = %d", $args['staff_id']);
        }
        
        if (!empty($args['date'])) {
            $where[] = $wpdb->prepare("date_of_preparation = %s", $args['date']);
        }
        
        if (!empty($args['date_from']) && !empty($args['date_to'])) {
            $where[] = $wpdb->prepare(
                "date_of_preparation BETWEEN %s AND %s",
                $args['date_from'],
                $args['date_to']
            );
        }
        
        $where_clause = '';
        if (!empty($where)) {
            $where_clause = "WHERE " . implode(" AND ", $where);
        }
        
        $query = "SELECT COUNT(*) FROM {$this->inventory_table} $where_clause";
        
        return $wpdb->get_var($query);
    }
    
    /**
     * Get inventory entry
     */
    public function get_inventory_entry($id) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT i.*, p.product_name 
             FROM {$this->inventory_table} i
             LEFT JOIN {$this->products_table} p ON i.product_id = p.id
             WHERE i.id = %d",
            $id
        );
        
        $result = $wpdb->get_row($query, ARRAY_A);
        
        return $result;
    }
    
    /**
     * Get latest inventory entry for a product
     */
    public function get_latest_inventory($product_id) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$this->inventory_table}
             WHERE product_id = %d
             ORDER BY date_of_preparation DESC, id DESC
             LIMIT 1",
            $product_id
        );
        
        $result = $wpdb->get_row($query, ARRAY_A);
        
        return $result;
    }
    
    /**
     * Get inventory entries for a specific date
     */
    public function get_inventory_by_date($date) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT i.*, p.product_name 
             FROM {$this->inventory_table} i
             LEFT JOIN {$this->products_table} p ON i.product_id = p.id
             WHERE i.date_of_preparation = %s",
            $date
        );
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return $results;
    }
    
    /**
     * Add inventory entry
     */
    public function add_inventory($data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->inventory_table,
            array(
                'product_id' => $data['product_id'],
                'staff_id' => $data['staff_id'],
                'staff_name' => $data['staff_name'],
                'date_of_preparation' => $data['date'],
                'opening' => $data['opening'],
                'total_added' => $data['total_added'],
                'total_sold' => $data['total_sold'],
                'closing' => $data['closing'],
                'remarks' => $data['remarks'],
            ),
            array('%d', '%d', '%s', '%s', '%f', '%f', '%f', '%f', '%s')
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update inventory entry
     */
    public function update_inventory($id, $data) {
        global $wpdb;
        
        $update_data = array();
        $update_format = array();
        
        // Check which fields are provided and add them to the update data
        if (isset($data['opening'])) {
            $update_data['opening'] = $data['opening'];
            $update_format[] = '%f';
        }
        
        if (isset($data['total_added'])) {
            $update_data['total_added'] = $data['total_added'];
            $update_format[] = '%f';
        }
        
        if (isset($data['total_sold'])) {
            $update_data['total_sold'] = $data['total_sold'];
            $update_format[] = '%f';
        }
        
        if (isset($data['closing'])) {
            $update_data['closing'] = $data['closing'];
            $update_format[] = '%f';
        }
        
        if (isset($data['remarks'])) {
            $update_data['remarks'] = $data['remarks'];
            $update_format[] = '%s';
        }
        
        // Only proceed if there's data to update
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update(
            $this->inventory_table,
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );
        
        return $result;
    }
    
    /**
     * Delete inventory entry
     */
    public function delete_inventory($id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->inventory_table,
            array('id' => $id),
            array('%d')
        );
        
        return $result;
    }
    
    /**
     * Clear all inventory entries
     */
    public function clear_inventory() {
        global $wpdb;
        
        $result = $wpdb->query("TRUNCATE TABLE {$this->inventory_table}");
        
        return $result !== false;
    }
    
    /**
     * Add log entry
     */
    public function add_log($data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->logs_table,
            array(
                'user_id' => $data['user_id'],
                'action' => $data['action'],
                'object_type' => $data['object_type'],
                'object_id' => $data['object_id'],
                'details' => isset($data['details']) ? $data['details'] : '',
                'ip_address' => isset($data['ip_address']) ? $data['ip_address'] : '',
            ),
            array('%d', '%s', '%s', '%d', '%s', '%s')
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Get logs
     */
    public function get_logs($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'user_id' => 0,
            'action' => '',
            'object_type' => '',
            'object_id' => 0,
            'orderby' => 'id',
            'order' => 'DESC',
            'limit' => -1,
            'offset' => 0,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array();
        
        if (!empty($args['user_id'])) {
            $where[] = $wpdb->prepare("user_id = %d", $args['user_id']);
        }
        
        if (!empty($args['action'])) {
            $where[] = $wpdb->prepare("action = %s", $args['action']);
        }
        
        if (!empty($args['object_type'])) {
            $where[] = $wpdb->prepare("object_type = %s", $args['object_type']);
        }
        
        if (!empty($args['object_id'])) {
            $where[] = $wpdb->prepare("object_id = %d", $args['object_id']);
        }
        
        $where_clause = '';
        if (!empty($where)) {
            $where_clause = "WHERE " . implode(" AND ", $where);
        }
        
        $limit = '';
        if ($args['limit'] > 0) {
            $limit = $wpdb->prepare("LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }
        
        $orderby = sanitize_sql_orderby("{$args['orderby']} {$args['order']}");
        if (!$orderby) {
            $orderby = "id DESC";
        }
        
        $query = "SELECT * FROM {$this->logs_table} $where_clause ORDER BY $orderby $limit";
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return $results;
    }
}