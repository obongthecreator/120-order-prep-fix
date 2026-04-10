<?php
/**
 * Inventory Records Template
 *
 * @package Fruit_Inventory_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="fim-container fim-animate-fadeIn" id="fim-records-container">
    <div class="fim-header">
        <h2><?php _e('Inventory Records', 'fruit-inventory-manager'); ?></h2>
        <p><?php _e('View and filter your inventory records', 'fruit-inventory-manager'); ?></p>
    </div>
    
    <div class="fim-section fim-animate-slideUp">
        <div class="fim-section-header">
            <h3><?php _e('Filters', 'fruit-inventory-manager'); ?></h3>
        </div>
        <div class="fim-section-body">
            <form id="fim-filter-form" class="fim-form">
                <div class="fim-filters">
                    <div class="fim-filter-item">
                        <label for="fim-product-filter" class="fim-filter-label"><?php _e('Product', 'fruit-inventory-manager'); ?></label>
                        <select id="fim-product-filter" class="fim-filter-control">
                            <option value=""><?php _e('All Products', 'fruit-inventory-manager'); ?></option>
                            <?php foreach ($all_products as $product) : ?>
                                <option value="<?php echo esc_attr($product['id']); ?>"><?php echo esc_html($product['product_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="fim-filter-item">
                        <label for="fim-date-from-filter" class="fim-filter-label"><?php _e('Date From', 'fruit-inventory-manager'); ?></label>
                        <input type="date" id="fim-date-from-filter" class="fim-filter-control" max="<?php echo esc_attr(date('Y-m-d')); ?>">
                    </div>
                    
                    <div class="fim-filter-item">
                        <label for="fim-date-to-filter" class="fim-filter-label"><?php _e('Date To', 'fruit-inventory-manager'); ?></label>
                        <input type="date" id="fim-date-to-filter" class="fim-filter-control" max="<?php echo esc_attr(date('Y-m-d')); ?>">
                    </div>
                    
                    <div class="fim-filter-buttons">
                        <button type="submit" id="fim-filter-button" class="fim-btn fim-btn-primary"><?php _e('Apply Filters', 'fruit-inventory-manager'); ?></button>
                        <button type="button" id="fim-reset-filter-button" class="fim-btn fim-btn-secondary"><?php _e('Reset', 'fruit-inventory-manager'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="fim-section fim-animate-slideUp" style="animation-delay: 0.2s;">
        <div class="fim-section-header">
            <h3><?php _e('Records', 'fruit-inventory-manager'); ?></h3>
        </div>
        <div class="fim-section-body">
            <div class="fim-table-container">
                <table id="fim-records-table" class="fim-table">
                    <thead>
                        <tr>
                            <th><?php _e('ID', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Product', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Staff', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Date', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Opening', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Added', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Sold', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Closing', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Remarks', 'fruit-inventory-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="9" class="fim-loader-container">
                                <div class="fim-loader"></div>
                                <span class="fim-loader-text"><?php _e('Loading records...', 'fruit-inventory-manager'); ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <ul id="fim-pagination" class="fim-pagination"></ul>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="fim-toast-container"></div>