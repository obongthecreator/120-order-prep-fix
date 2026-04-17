<?php
/**
 * Admin Settings Template
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
        <h1><?php _e('Settings', 'fruit-inventory-manager'); ?></h1>
    </div>
    
    <?php settings_errors('fim_settings'); ?>
    
    <div class="fim-admin-card">
        <div class="fim-admin-card-header">
            <h2><?php _e('General Settings', 'fruit-inventory-manager'); ?></h2>
        </div>
        <div class="fim-admin-card-body">
            <div class="fim-admin-settings">
                <form method="post" action="" class="fim-admin-form">
                    <?php wp_nonce_field('fim_settings_nonce'); ?>
                    <input type="hidden" name="fim_save_settings" value="1">
                    
                    <div class="fim-admin-form-group">
                        <label for="fim_shortcode_page_id"><?php _e('Frontend Form Page', 'fruit-inventory-manager'); ?></label>
                        <select id="fim_shortcode_page_id" name="fim_shortcode_page_id" class="fim-admin-form-control">
                            <option value="0"><?php _e('-- Select Page --', 'fruit-inventory-manager'); ?></option>
                            <?php
                            $pages = get_pages();
                            foreach ($pages as $page) :
                            ?>
                                <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($shortcode_page_id, $page->ID); ?>>
                                    <?php echo esc_html($page->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php _e('Select the page where you have placed the [fruit_inventory_form] shortcode.', 'fruit-inventory-manager'); ?>
                        </p>
                    </div>
                    
                    <div class="fim-admin-form-group">
                        <label for="fim_records_page_id"><?php _e('Frontend Records Page', 'fruit-inventory-manager'); ?></label>
                        <select id="fim_records_page_id" name="fim_records_page_id" class="fim-admin-form-control">
                            <option value="0"><?php _e('-- Select Page --', 'fruit-inventory-manager'); ?></option>
                            <?php
                            foreach ($pages as $page) :
                            ?>
                                <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($records_page_id, $page->ID); ?>>
                                    <?php echo esc_html($page->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php _e('Select the page where you have placed the [fruit_inventory_records] shortcode.', 'fruit-inventory-manager'); ?>
                        </p>
                    </div>
                    
                    <div class="fim-admin-form-group">
                        <label for="fim_per_page"><?php _e('Records Per Page', 'fruit-inventory-manager'); ?></label>
                        <input type="number" id="fim_per_page" name="fim_per_page" class="fim-admin-form-control" value="<?php echo esc_attr($per_page); ?>" min="1" max="100">
                        <p class="description">
                            <?php _e('Number of records to display per page in the frontend and admin.', 'fruit-inventory-manager'); ?>
                        </p>
                    </div>
                    
                    <div class="fim-admin-form-actions">
                        <button type="submit" class="fim-admin-btn fim-admin-btn-primary">
                            <?php _e('Save Settings', 'fruit-inventory-manager'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="fim-admin-card">
        <div class="fim-admin-card-header">
            <h2><?php _e('Shortcodes', 'fruit-inventory-manager'); ?></h2>
        </div>
        <div class="fim-admin-card-body">
            <div class="fim-admin-shortcodes">
                <div class="fim-admin-shortcode-item">
                    <h3><?php _e('Frontend Form', 'fruit-inventory-manager'); ?></h3>
                    <div class="fim-admin-shortcode-code">
                        <code>[fruit_inventory_form]</code>
                        <button type="button" class="fim-admin-btn fim-admin-btn-sm fim-admin-btn-secondary fim-copy-shortcode" data-shortcode="[fruit_inventory_form]">
                            <?php _e('Copy', 'fruit-inventory-manager'); ?>
                        </button>
                    </div>
                    <p class="description">
                        <?php _e('Place this shortcode on a page to display the inventory form for staff.', 'fruit-inventory-manager'); ?>
                    </p>
                </div>
                
                <div class="fim-admin-shortcode-item">
                    <h3><?php _e('Frontend Records', 'fruit-inventory-manager'); ?></h3>
                    <div class="fim-admin-shortcode-code">
                        <code>[fruit_inventory_records]</code>
                        <button type="button" class="fim-admin-btn fim-admin-btn-sm fim-admin-btn-secondary fim-copy-shortcode" data-shortcode="[fruit_inventory_records]">
                            <?php _e('Copy', 'fruit-inventory-manager'); ?>
                        </button>
                    </div>
                    <p class="description">
                        <?php _e('Place this shortcode on a page to display the inventory records for staff.', 'fruit-inventory-manager'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="fim-admin-card">
        <div class="fim-admin-card-header">
            <h2><?php _e('About', 'fruit-inventory-manager'); ?></h2>
        </div>
        <div class="fim-admin-card-body">
            <div class="fim-admin-about">
                <h3><?php _e('Fruit Inventory Manager', 'fruit-inventory-manager'); ?></h3>
                <p>
                    <?php _e('Version', 'fruit-inventory-manager'); ?>: <strong><?php echo FIM_PLUGIN_VERSION; ?></strong>
                </p>
                <p>
                    <?php _e('A sleek and functional inventory management system for tracking fruit products with real-time calculations.', 'fruit-inventory-manager'); ?>
                </p>
                <p>
                    <?php _e('This plugin provides a comprehensive solution for managing fruit inventory with features like real-time calculations, staff tracking, and detailed reporting.', 'fruit-inventory-manager'); ?>
                </p>
                <h4><?php _e('Features', 'fruit-inventory-manager'); ?>:</h4>
                <ul>
                    <li><?php _e('Frontend form for staffs to input inventory data', 'fruit-inventory-manager'); ?></li>
                    <li><?php _e('Real-time calculation of closing values', 'fruit-inventory-manager'); ?></li>
                    <li><?php _e('Previous day\'s closing becomes next day\'s opening', 'fruit-inventory-manager'); ?></li>
                    <li><?php _e('Frontend records page with filters and pagination', 'fruit-inventory-manager'); ?></li>
                    <li><?php _e('Admin dashboard with comprehensive management options', 'fruit-inventory-manager'); ?></li>
                    <li><?php _e('Detailed activity logging for security and tracking', 'fruit-inventory-manager'); ?></li>
                </ul>
                
                <p>
                    <?php _e('Designed and developed for 120Fruit.', 'fruit-inventory-manager'); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    // Copy shortcode functionality
    jQuery(document).ready(function($) {
        $('.fim-copy-shortcode').on('click', function() {
            var shortcode = $(this).data('shortcode');
            var tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(shortcode).select();
            document.execCommand('copy');
            tempInput.remove();
            
            // Change button text temporarily
            var $button = $(this);
            var originalText = $button.text();
            $button.text('<?php _e('Copied!', 'fruit-inventory-manager'); ?>');
            
            setTimeout(function() {
                $button.text(originalText);
            }, 2000);
        });
    });
</script>
