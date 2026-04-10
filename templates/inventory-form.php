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

<div class="fim-container tw-max-w-5xl tw-mx-auto tw-p-5 tw-font-sans">
    <div class="tw-mb-8 tw-text-center">
        <h2 class="tw-text-2xl tw-font-semibold tw-text-gray-800 tw-mb-2"><?php _e('Fruit Inventory Management', 'fruit-inventory-manager'); ?></h2>
        <p class="tw-text-base tw-text-gray-500"><?php _e('Track your fruit products inventory with ease', 'fruit-inventory-manager'); ?></p>
    </div>
    
    <form id="fim-inventory-form" class="fim-form">
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-mb-8 tw-overflow-hidden">
            <div class="tw-bg-red-600 tw-text-white tw-px-5 tw-py-4 tw-flex tw-justify-between tw-items-center">
                <h3 class="tw-m-0 tw-text-lg tw-font-medium"><?php _e('Preparation Details', 'fruit-inventory-manager'); ?></h3>
            </div>
            <div class="tw-p-5">
                <div class="tw-flex tw-flex-wrap tw--mx-2.5">
                    <div class="tw-flex-1 tw-px-2.5 tw-mb-5 tw-min-w-[200px]">
                        <label for="fim-staff-id" class="tw-block tw-mb-2 tw-font-medium tw-text-gray-800"><?php _e('Staff ID', 'fruit-inventory-manager'); ?></label>
                        <input type="text" id="fim-staff-id" class="fim-form-control tw-w-full tw-py-2.5 tw-px-4 tw-text-base tw-border tw-border-gray-300 tw-rounded tw-bg-gray-50 tw-opacity-70" value="<?php echo esc_attr($current_user->user_login); ?>" readonly>
                    </div>
                    
                    <div class="tw-flex-1 tw-px-2.5 tw-mb-5 tw-min-w-[200px]">
                        <label for="fim-staff-name" class="tw-block tw-mb-2 tw-font-medium tw-text-gray-800"><?php _e('Staff Name', 'fruit-inventory-manager'); ?></label>
                        <input type="text" id="fim-staff-name" class="fim-form-control tw-w-full tw-py-2.5 tw-px-4 tw-text-base tw-border tw-border-gray-300 tw-rounded tw-bg-gray-50 tw-opacity-70" value="<?php echo esc_attr($current_user->display_name); ?>" readonly>
                    </div>
                    
                    <div class="tw-flex-1 tw-px-2.5 tw-mb-5 tw-min-w-[200px]">
                        <label for="fim-date" class="tw-block tw-mb-2 tw-font-medium tw-text-gray-800"><?php _e('Date of Preparation', 'fruit-inventory-manager'); ?></label>
                        <input type="date" id="fim-date" name="date" class="fim-form-control tw-w-full tw-py-2.5 tw-px-4 tw-text-base tw-border tw-border-gray-300 tw-rounded tw-bg-white focus:tw-border-red-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-200" value="<?php echo esc_attr($current_date); ?>" max="<?php echo esc_attr(date('Y-m-d')); ?>">
                    </div>
                </div>
                
                <div class="tw-mb-5">
                    <label for="fim-remarks" class="tw-block tw-mb-2 tw-font-medium tw-text-gray-800"><?php _e('Remarks', 'fruit-inventory-manager'); ?></label>
                    <textarea id="fim-remarks" name="remarks" class="fim-form-control fim-textarea tw-w-full tw-py-2.5 tw-px-4 tw-text-base tw-border tw-border-gray-300 tw-rounded tw-bg-white tw-min-h-[100px] tw-resize-y focus:tw-border-red-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-200" placeholder="<?php esc_attr_e('Leave your remarks here if any...', 'fruit-inventory-manager'); ?>"></textarea>
                </div>
            </div>
        </div>
        
        <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-mb-8 tw-overflow-hidden">
            <div class="tw-bg-red-600 tw-text-white tw-px-5 tw-py-4 tw-flex tw-justify-between tw-items-center">
                <h3 class="tw-m-0 tw-text-lg tw-font-medium"><?php _e('Products Inventory', 'fruit-inventory-manager'); ?></h3>
                <?php if ($is_admin): ?>
                <small class="tw-text-red-100 tw-font-medium tw-ml-4 tw-italic"><?php _e('Admin mode: You can edit opening and closing values', 'fruit-inventory-manager'); ?></small>
                <?php endif; ?>
            </div>
            <div class="tw-p-5">
                <div class="tw-overflow-x-auto">
                    <table id="fim-products-table" class="fim-table tw-w-full tw-border-collapse">
                        <thead>
                            <tr>
                                <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('Fruit', 'fruit-inventory-manager'); ?></th>
                                <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('Opening (Cup/Bottle)', 'fruit-inventory-manager'); ?></th>
                                <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('Total Added', 'fruit-inventory-manager'); ?></th>
                                <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('Total Sold', 'fruit-inventory-manager'); ?></th>
                                <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('Closing', 'fruit-inventory-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_products as $product) : ?>
                                <tr data-product-id="<?php echo esc_attr($product['id']); ?>" class="hover:tw-bg-red-50 tw-border-b tw-border-gray-200 last:tw-border-b-0">
                                    <td class="tw-py-3 tw-px-4"><?php echo esc_html($product['product_name']); ?></td>
                                    <td class="tw-py-3 tw-px-4">
                                        <input type="number" step="0.01" min="0" class="fim-opening tw-w-full tw-py-2 tw-px-3 tw-border tw-border-gray-300 tw-rounded tw-bg-gray-50 tw-font-semibold tw-text-gray-800 focus:tw-border-red-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-200" value="0" <?php echo $is_admin ? '' : 'readonly'; ?>>
                                    </td>
                                    <td class="tw-py-3 tw-px-4">
                                        <input type="number" step="0.01" min="0" class="fim-total-added tw-w-full tw-py-2 tw-px-3 tw-border tw-border-gray-300 tw-rounded tw-bg-white focus:tw-border-red-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-200" value="0">
                                    </td>
                                    <td class="tw-py-3 tw-px-4">
                                        <input type="number" step="0.01" min="0" class="fim-total-sold tw-w-full tw-py-2 tw-px-3 tw-border tw-border-gray-300 tw-rounded tw-bg-white focus:tw-border-red-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-200" value="0">
                                    </td>
                                    <td class="tw-py-3 tw-px-4">
                                        <input type="number" step="0.01" min="0" class="fim-closing tw-w-full tw-py-2 tw-px-3 tw-border tw-border-gray-200 tw-rounded tw-bg-gray-50 tw-font-semibold tw-text-red-600 focus:tw-border-red-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-200" value="0" <?php echo $is_admin ? '' : 'readonly'; ?>>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="tw-text-center tw-mt-5">
            <button type="submit" id="fim-submit-button" class="fim-btn fim-btn-primary tw-inline-block tw-font-medium tw-text-center tw-text-white tw-bg-red-600 tw-border tw-border-red-600 tw-py-3 tw-px-6 tw-text-lg tw-rounded tw-cursor-pointer hover:tw-bg-red-700 hover:tw-border-red-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-200">
                <?php _e('Submit Inventory', 'fruit-inventory-manager'); ?>
            </button>
        </div>
    </form>
</div>

<!-- Toast Container -->
<div id="fim-toast-container"></div>
