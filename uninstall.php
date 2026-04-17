<?php
/**
 * Uninstall File
 *
 * @package Fruit_Inventory_Manager
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Access the database via SQL
global $wpdb;

// Drop tables
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fim_products");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fim_inventory");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fim_logs");

// Delete options
delete_option('fim_shortcode_page_id');
delete_option('fim_records_page_id');
delete_option('fim_per_page');
