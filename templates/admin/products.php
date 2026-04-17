<?php
/**
 * Admin Products Template
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
        <h1><?php _e('Manage Products', 'fruit-inventory-manager'); ?></h1>
        <div class="fim-admin-header-actions">
            <button type="button" id="fim-add-product-button" class="fim-admin-btn fim-admin-btn-primary">
                <span class="dashicons dashicons-plus-alt"></span> <?php _e('Add New Product', 'fruit-inventory-manager'); ?>
            </button>
        </div>
    </div>
    
    <div class="fim-admin-card">
        <div class="fim-admin-card-header">
            <h2><?php _e('Products List', 'fruit-inventory-manager'); ?></h2>
        </div>
        <div class="fim-admin-card-body">
            <div class="fim-admin-table-container">
                <table id="fim-admin-products-table" class="fim-admin-table">
                    <thead>
                        <tr>
                            <th width="40"><?php _e('ID', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Product Name', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Category', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Status', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Date Created', 'fruit-inventory-manager'); ?></th>
                            <th width="150"><?php _e('Actions', 'fruit-inventory-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)) : ?>
                            <?php foreach ($products as $index => $product) : ?>
                                <tr data-product-id="<?php echo esc_attr($product['id']); ?>">
                                    <td><?php echo esc_html($product['id']); ?></td>
                                    <td><?php echo esc_html($product['product_name']); ?></td>
                                    <td>
                                        <?php 
                                        $category_key = $product['product_type'];
                                        echo isset($product_categories[$category_key]) ? esc_html($product_categories[$category_key]) : esc_html($category_key);
                                        ?>
                                    </td>
                                    <td>
                                        <span class="fim-status fim-status-<?php echo esc_attr($product['status'] === 'active' ? 'success' : 'warning'); ?>">
                                            <?php echo esc_html(ucfirst($product['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($product['created_at']))); ?></td>
                                    <td class="fim-actions">
                                        <button type="button" class="fim-admin-btn fim-admin-btn-primary fim-admin-btn-sm fim-edit-product-button" data-product-id="<?php echo esc_attr($product['id']); ?>">
                                            <?php _e('Edit', 'fruit-inventory-manager'); ?>
                                        </button>
                                        <button type="button" class="fim-admin-btn fim-admin-btn-danger fim-admin-btn-sm fim-delete-product-button" data-product-id="<?php echo esc_attr($product['id']); ?>" data-product-name="<?php echo esc_attr($product['product_name']); ?>">
                                            <?php _e('Delete', 'fruit-inventory-manager'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center"><?php _e('No products found', 'fruit-inventory-manager'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="fim-modal-backdrop">
    <div id="fim-product-modal" class="fim-modal">
        <div class="fim-modal-header">
            <h3 class="fim-modal-title"><?php _e('Add Product', 'fruit-inventory-manager'); ?></h3>
            <button type="button" class="fim-modal-close">&times;</button>
        </div>
        <div class="fim-modal-body">
            <form id="fim-product-form" class="fim-form">
                <input type="hidden" name="product_id" value="">
                
                <div class="fim-form-group">
                    <label for="fim-product-name"><?php _e('Product Name', 'fruit-inventory-manager'); ?> <span class="required">*</span></label>
                    <input type="text" id="fim-product-name" name="product_name" class="fim-form-control" required>
                </div>
                
                <div class="fim-form-group">
                    <label for="fim-product-type"><?php _e('Category', 'fruit-inventory-manager'); ?> <span class="required">*</span></label>
                    <select id="fim-product-type" name="product_type" class="fim-form-control" required>
                        <option value=""><?php _e('Select Category', 'fruit-inventory-manager'); ?></option>
                        <?php foreach ($product_categories as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="fim-form-group">
                    <label for="fim-product-status"><?php _e('Status', 'fruit-inventory-manager'); ?></label>
                    <select id="fim-product-status" name="product_status" class="fim-form-control">
                        <option value="active"><?php _e('Active', 'fruit-inventory-manager'); ?></option>
                        <option value="inactive"><?php _e('Inactive', 'fruit-inventory-manager'); ?></option>
                    </select>
                </div>
                
                <div class="fim-form-actions">
                    <button type="submit" id="fim-save-product-button" class="fim-btn fim-btn-primary">
                        <?php _e('Save Product', 'fruit-inventory-manager'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
