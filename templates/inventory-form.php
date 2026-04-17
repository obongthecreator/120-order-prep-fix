<?php
/**
 * Inventory Form Template
 *
 * @package Fruit_Inventory_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is admin
$is_admin = current_user_can('manage_options');
?>

<div class="fim-container">
    <div class="fim-header">
        <h2><?php _e('Fruit Inventory Management', 'fruit-inventory-manager'); ?></h2>
        <p><?php _e('Track your fruit products inventory with ease', 'fruit-inventory-manager'); ?></p>
    </div>
    
    <form id="fim-inventory-form" class="fim-form">
        <div class="fim-section">
            <div class="fim-section-header">
                <h3><?php _e('Preparation Details', 'fruit-inventory-manager'); ?></h3>
            </div>
            <div class="fim-section-body">
                <div class="fim-form-row">
                    <div class="fim-form-group">
                        <label for="fim-staff-id"><?php _e('Staff ID', 'fruit-inventory-manager'); ?></label>
                        <input type="text" id="fim-staff-id" class="fim-form-control readonly" value="<?php echo esc_attr($current_user->user_login); ?>" readonly>
                    </div>
                    
                    <div class="fim-form-group">
                        <label for="fim-staff-name"><?php _e('Staff Name', 'fruit-inventory-manager'); ?></label>
                        <input type="text" id="fim-staff-name" class="fim-form-control readonly" value="<?php echo esc_attr($current_user->display_name); ?>" readonly>
                    </div>
                    
                    <div class="fim-form-group">
                        <label for="fim-date"><?php _e('Date of Preparation', 'fruit-inventory-manager'); ?></label>
                        <input type="date" id="fim-date" name="date" class="fim-form-control" value="<?php echo esc_attr($current_date); ?>" max="<?php echo esc_attr(date('Y-m-d')); ?>">
                    </div>
                </div>
                
                <div class="fim-form-group">
                    <label for="fim-remarks"><?php _e('Remarks', 'fruit-inventory-manager'); ?></label>
                    <textarea id="fim-remarks" name="remarks" class="fim-form-control fim-textarea" placeholder="<?php esc_attr_e('Leave your remarks here if any...', 'fruit-inventory-manager'); ?>"></textarea>
                </div>
            </div>
        </div>
        
        <div class="fim-section">
            <div class="fim-section-header">
                <h3><?php _e('Products Inventory', 'fruit-inventory-manager'); ?></h3>
                <?php if ($is_admin): ?>
                <small class="fim-admin-notice"><?php _e('Admin mode: You can edit opening and closing values', 'fruit-inventory-manager'); ?></small>
                <?php endif; ?>
            </div>
            <div class="fim-section-body">
                <div class="fim-table-container">
                    <table id="fim-products-table" class="fim-table">
                        <thead>
                            <tr>
                                <th><?php _e('Fruit', 'fruit-inventory-manager'); ?></th>
                                <th><?php _e('Opening (Cup/Bottle)', 'fruit-inventory-manager'); ?></th>
                                <th><?php _e('Total Added', 'fruit-inventory-manager'); ?></th>
                                <th><?php _e('Total Sold', 'fruit-inventory-manager'); ?></th>
                                <th><?php _e('Closing', 'fruit-inventory-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_products as $product) : ?>
                                <tr data-product-id="<?php echo esc_attr($product['id']); ?>">
                                    <td><?php echo esc_html($product['product_name']); ?></td>
                                    <td>
                                        <input type="number" step="0.01" min="0" class="fim-opening" value="" placeholder="0" <?php echo $is_admin ? '' : 'readonly'; ?>>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" class="fim-total-added" value="" placeholder="0">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" class="fim-total-sold" value="" placeholder="0">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" class="fim-closing" value="" placeholder="0" <?php echo $is_admin ? '' : 'readonly'; ?>>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="fim-form-actions">
            <button type="submit" id="fim-submit-button" class="fim-btn fim-btn-primary fim-btn-lg">
                <?php _e('Submit Inventory', 'fruit-inventory-manager'); ?>
            </button>
        </div>
    </form>
</div>

<!-- Toast Container -->
<div id="fim-toast-container"></div>
