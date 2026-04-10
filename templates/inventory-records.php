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

<div class="fim-container tw-max-w-5xl tw-mx-auto tw-p-5 tw-font-sans" id="fim-records-container">
    <div class="tw-mb-8 tw-text-center">
        <h2 class="tw-text-2xl tw-font-semibold tw-text-gray-800 tw-mb-2"><?php _e('Inventory Records', 'fruit-inventory-manager'); ?></h2>
        <p class="tw-text-base tw-text-gray-500"><?php _e('View and filter your inventory records', 'fruit-inventory-manager'); ?></p>
    </div>
    
    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-mb-8 tw-overflow-hidden">
        <div class="tw-bg-red-600 tw-text-white tw-px-5 tw-py-4 tw-flex tw-justify-between tw-items-center">
            <h3 class="tw-m-0 tw-text-lg tw-font-medium"><?php _e('Filters', 'fruit-inventory-manager'); ?></h3>
        </div>
        <div class="tw-p-5">
            <form id="fim-filter-form" class="fim-form">
                <div class="tw-flex tw-flex-wrap tw-gap-4 tw-mb-5">
                    <div class="tw-flex-1 tw-min-w-[200px] tw-max-w-xs">
                        <label for="fim-product-filter" class="tw-block tw-mb-1 tw-font-medium tw-text-gray-800 tw-text-sm"><?php _e('Product', 'fruit-inventory-manager'); ?></label>
                        <select id="fim-product-filter" class="fim-filter-control tw-w-full tw-py-2 tw-px-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded focus:tw-border-red-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-200">
                            <option value=""><?php _e('All Products', 'fruit-inventory-manager'); ?></option>
                            <?php foreach ($all_products as $product) : ?>
                                <option value="<?php echo esc_attr($product['id']); ?>"><?php echo esc_html($product['product_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="tw-flex-1 tw-min-w-[200px] tw-max-w-xs">
                        <label for="fim-date-from-filter" class="tw-block tw-mb-1 tw-font-medium tw-text-gray-800 tw-text-sm"><?php _e('Date From', 'fruit-inventory-manager'); ?></label>
                        <input type="date" id="fim-date-from-filter" class="fim-filter-control tw-w-full tw-py-2 tw-px-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded focus:tw-border-red-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-200" max="<?php echo esc_attr(date('Y-m-d')); ?>">
                    </div>
                    
                    <div class="tw-flex-1 tw-min-w-[200px] tw-max-w-xs">
                        <label for="fim-date-to-filter" class="tw-block tw-mb-1 tw-font-medium tw-text-gray-800 tw-text-sm"><?php _e('Date To', 'fruit-inventory-manager'); ?></label>
                        <input type="date" id="fim-date-to-filter" class="fim-filter-control tw-w-full tw-py-2 tw-px-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded focus:tw-border-red-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-200" max="<?php echo esc_attr(date('Y-m-d')); ?>">
                    </div>
                    
                    <div class="tw-flex tw-gap-2.5 tw-mt-7">
                        <button type="submit" id="fim-filter-button" class="fim-btn fim-btn-primary tw-inline-block tw-font-medium tw-text-center tw-text-white tw-bg-red-600 tw-border tw-border-red-600 tw-py-2.5 tw-px-5 tw-text-base tw-rounded tw-cursor-pointer hover:tw-bg-red-700 hover:tw-border-red-700"><?php _e('Apply Filters', 'fruit-inventory-manager'); ?></button>
                        <button type="button" id="fim-reset-filter-button" class="fim-btn fim-btn-secondary tw-inline-block tw-font-medium tw-text-center tw-text-white tw-bg-gray-800 tw-border tw-border-gray-800 tw-py-2.5 tw-px-5 tw-text-base tw-rounded tw-cursor-pointer hover:tw-bg-gray-900 hover:tw-border-gray-900"><?php _e('Reset', 'fruit-inventory-manager'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-mb-8 tw-overflow-hidden">
        <div class="tw-bg-red-600 tw-text-white tw-px-5 tw-py-4 tw-flex tw-justify-between tw-items-center">
            <h3 class="tw-m-0 tw-text-lg tw-font-medium"><?php _e('Records', 'fruit-inventory-manager'); ?></h3>
        </div>
        <div class="tw-p-5">
            <div class="tw-overflow-x-auto">
                <table id="fim-records-table" class="fim-table tw-w-full tw-border-collapse">
                    <thead>
                        <tr>
                            <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('ID', 'fruit-inventory-manager'); ?></th>
                            <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('Product', 'fruit-inventory-manager'); ?></th>
                            <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('Staff', 'fruit-inventory-manager'); ?></th>
                            <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('Date', 'fruit-inventory-manager'); ?></th>
                            <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('Opening', 'fruit-inventory-manager'); ?></th>
                            <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('Added', 'fruit-inventory-manager'); ?></th>
                            <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('Sold', 'fruit-inventory-manager'); ?></th>
                            <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('Closing', 'fruit-inventory-manager'); ?></th>
                            <th class="tw-py-3 tw-px-4 tw-text-left tw-bg-gray-50 tw-font-semibold tw-text-gray-800 tw-whitespace-nowrap tw-border-b tw-border-gray-200"><?php _e('Remarks', 'fruit-inventory-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="9" class="tw-text-center tw-py-5">
                                <div class="fim-loader tw-inline-block tw-w-8 tw-h-8 tw-border-[3px] tw-border-red-200 tw-rounded-full tw-border-t-red-600 tw-mx-auto" style="animation: fim-spin 1s linear infinite;"></div>
                                <span class="tw-block tw-mt-2.5 tw-text-sm tw-text-gray-500"><?php _e('Loading records...', 'fruit-inventory-manager'); ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <ul id="fim-pagination" class="fim-pagination tw-flex tw-justify-center tw-list-none tw-p-0 tw-mt-5"></ul>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="fim-toast-container"></div>
