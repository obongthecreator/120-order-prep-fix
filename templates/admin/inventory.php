<?php
/**
 * Admin Inventory Template
 *
 * @package Fruit_Inventory_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap fim-admin-wrap">
    <div class="fim-admin-header">
        <h1><?php _e('Inventory Management', 'fruit-inventory-manager'); ?></h1>
        <div class="fim-admin-header-actions">
            <button type="button" id="fim-clear-inventory-button" class="fim-admin-btn fim-admin-btn-danger">
                <span class="dashicons dashicons-trash"></span> <?php _e('Clear All Inventory', 'fruit-inventory-manager'); ?>
            </button>
        </div>
    </div>
    
    <div class="fim-admin-card fim-animate-slideUp">
        <div class="fim-admin-card-header">
            <h2><?php _e('Filters', 'fruit-inventory-manager'); ?></h2>
        </div>
        <div class="fim-admin-card-body">
            <form id="fim-admin-inventory-filter-form" class="fim-admin-form">
                <div class="fim-admin-filters">
                    <div class="fim-admin-filter-item">
                        <label for="fim-admin-inventory-product-filter" class="fim-admin-filter-label"><?php _e('Product', 'fruit-inventory-manager'); ?></label>
                        <select id="fim-admin-inventory-product-filter" class="fim-admin-filter-control">
                            <option value=""><?php _e('All Products', 'fruit-inventory-manager'); ?></option>
                            <?php foreach ($products as $product) : ?>
                                <option value="<?php echo esc_attr($product['id']); ?>"><?php echo esc_html($product['product_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="fim-admin-filter-item">
                        <label for="fim-admin-inventory-date-from-filter" class="fim-admin-filter-label"><?php _e('Date From', 'fruit-inventory-manager'); ?></label>
                        <input type="date" id="fim-admin-inventory-date-from-filter" class="fim-admin-filter-control" max="<?php echo esc_attr(date('Y-m-d')); ?>">
                    </div>
                    
                    <div class="fim-admin-filter-item">
                        <label for="fim-admin-inventory-date-to-filter" class="fim-admin-filter-label"><?php _e('Date To', 'fruit-inventory-manager'); ?></label>
                        <input type="date" id="fim-admin-inventory-date-to-filter" class="fim-admin-filter-control" max="<?php echo esc_attr(date('Y-m-d')); ?>">
                    </div>
                    
                    <div class="fim-admin-filter-buttons">
                        <button type="submit" id="fim-admin-inventory-filter-button" class="fim-admin-btn fim-admin-btn-primary">
                            <?php _e('Apply Filters', 'fruit-inventory-manager'); ?>
                        </button>
                        <button type="button" id="fim-admin-inventory-reset-filter-button" class="fim-admin-btn fim-admin-btn-secondary">
                            <?php _e('Reset', 'fruit-inventory-manager'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="fim-admin-card fim-animate-slideUp" style="animation-delay: 0.2s;">
        <div class="fim-admin-card-header">
            <h2><?php _e('Inventory Records', 'fruit-inventory-manager'); ?></h2>
        </div>
        <div class="fim-admin-card-body">
            <div class="fim-admin-table-container">
                <table id="fim-admin-inventory-table" class="fim-admin-table">
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
                            <th><?php _e('Actions', 'fruit-inventory-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="fim-admin-inventory-table-body">
                        <tr>
                            <td colspan="10" class="fim-admin-loader-container">
                                <div class="fim-admin-loader"></div>
                                <span class="fim-admin-loader-text"><?php _e('Loading inventory...', 'fruit-inventory-manager'); ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <ul id="fim-admin-inventory-pagination" class="fim-admin-pagination"></ul>
        </div>
    </div>
</div>

<!-- Inventory Modal -->
<div class="fim-modal-backdrop">
    <div id="fim-inventory-modal" class="fim-modal">
        <div class="fim-modal-header">
            <h3 class="fim-modal-title"><?php _e('Edit Inventory Entry', 'fruit-inventory-manager'); ?></h3>
            <button type="button" class="fim-modal-close">&times;</button>
        </div>
        <div class="fim-modal-body">
            <form id="fim-inventory-form" class="fim-form">
                <input type="hidden" name="inventory_id" value="">
                
                <div class="fim-form-row">
                    <div class="fim-form-group">
                        <label><?php _e('Product', 'fruit-inventory-manager'); ?></label>
                        <div class="fim-product-name fim-static-value"></div>
                    </div>
                    
                    <div class="fim-form-group">
                        <label><?php _e('Staff', 'fruit-inventory-manager'); ?></label>
                        <div class="fim-staff-name fim-static-value"></div>
                    </div>
                    
                    <div class="fim-form-group">
                        <label><?php _e('Date', 'fruit-inventory-manager'); ?></label>
                        <div class="fim-date fim-static-value"></div>
                    </div>
                </div>
                
                <div class="fim-form-row">
                    <div class="fim-form-group">
                        <label for="fim-opening"><?php _e('Opening', 'fruit-inventory-manager'); ?></label>
                        <input type="number" step="0.01" min="0" class="fim-form-control fim-opening" readonly>
                    </div>
                    
                    <div class="fim-form-group">
                        <label for="fim-total-added"><?php _e('Total Added', 'fruit-inventory-manager'); ?></label>
                        <input type="number" step="0.01" min="0" class="fim-form-control fim-total-added">
                    </div>
                    
                    <div class="fim-form-group">
                        <label for="fim-total-sold"><?php _e('Total Sold', 'fruit-inventory-manager'); ?></label>
                        <input type="number" step="0.01" min="0" class="fim-form-control fim-total-sold">
                    </div>
                    
                    <div class="fim-form-group">
                        <label for="fim-closing"><?php _e('Closing', 'fruit-inventory-manager'); ?></label>
                        <input type="number" step="0.01" min="0" class="fim-form-control fim-closing" readonly>
                    </div>
                </div>
                
                <div class="fim-form-group">
                    <label for="fim-remarks"><?php _e('Remarks', 'fruit-inventory-manager'); ?></label>
                    <textarea class="fim-form-control fim-textarea fim-remarks"></textarea>
                </div>
                
                <div class="fim-form-actions">
                    <button type="submit" class="fim-btn fim-btn-primary fim-save-button">
                        <?php _e('Save Changes', 'fruit-inventory-manager'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>