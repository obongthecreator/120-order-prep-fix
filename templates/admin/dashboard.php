<?php
/**
 * Admin Dashboard Template
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
        <h1><?php _e('Fruit Inventory Manager', 'fruit-inventory-manager'); ?></h1>
        <div class="fim-admin-header-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=fim-products')); ?>" class="fim-admin-btn fim-admin-btn-primary">
                <?php _e('Manage Products', 'fruit-inventory-manager'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=fim-inventory')); ?>" class="fim-admin-btn fim-admin-btn-secondary">
                <?php _e('View Inventory', 'fruit-inventory-manager'); ?>
            </a>
        </div>
    </div>
    
    <div class="fim-admin-widgets">
        <div class="fim-admin-widget">
            <div class="fim-admin-widget-header">
                <h3 class="fim-admin-widget-title"><?php _e('Total Products', 'fruit-inventory-manager'); ?></h3>
            </div>
            <div class="fim-admin-widget-body">
                <div class="fim-admin-widget-value"><?php echo esc_html($total_products); ?></div>
                <div class="fim-admin-widget-label"><?php _e('Products', 'fruit-inventory-manager'); ?></div>
            </div>
        </div>
        
        <div class="fim-admin-widget">
            <div class="fim-admin-widget-header">
                <h3 class="fim-admin-widget-title"><?php _e('Today\'s Entries', 'fruit-inventory-manager'); ?></h3>
            </div>
            <div class="fim-admin-widget-body">
                <div class="fim-admin-widget-value"><?php echo esc_html($today_entries); ?></div>
                <div class="fim-admin-widget-label"><?php _e('Entries Today', 'fruit-inventory-manager'); ?></div>
            </div>
        </div>
        
        <div class="fim-admin-widget">
            <div class="fim-admin-widget-header">
                <h3 class="fim-admin-widget-title"><?php _e('Yesterday\'s Entries', 'fruit-inventory-manager'); ?></h3>
            </div>
            <div class="fim-admin-widget-body">
                <div class="fim-admin-widget-value"><?php echo esc_html($yesterday_entries); ?></div>
                <div class="fim-admin-widget-label"><?php _e('Entries Yesterday', 'fruit-inventory-manager'); ?></div>
            </div>
        </div>
        
        <div class="fim-admin-widget">
            <div class="fim-admin-widget-header">
                <h3 class="fim-admin-widget-title"><?php _e('Monthly Entries', 'fruit-inventory-manager'); ?></h3>
            </div>
            <div class="fim-admin-widget-body">
                <div class="fim-admin-widget-value"><?php echo esc_html($this_month_entries); ?></div>
                <div class="fim-admin-widget-label"><?php _e('Entries This Month', 'fruit-inventory-manager'); ?></div>
            </div>
        </div>
    </div>
    
    <div class="fim-admin-card">
        <div class="fim-admin-card-header">
            <h2><?php _e('Recent Inventory Entries', 'fruit-inventory-manager'); ?></h2>
            <a href="<?php echo esc_url(admin_url('admin.php?page=fim-inventory')); ?>" class="fim-admin-btn fim-admin-btn-sm fim-admin-btn-secondary">
                <?php _e('View All', 'fruit-inventory-manager'); ?>
            </a>
        </div>
        <div class="fim-admin-card-body">
            <div class="fim-admin-table-container">
                <table class="fim-admin-table">
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
                            <th><?php _e('Actions', 'fruit-inventory-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_entries)) : ?>
                            <?php foreach ($recent_entries as $entry) : ?>
                                <tr data-inventory-id="<?php echo esc_attr($entry['id']); ?>">
                                    <td><?php echo esc_html($entry['id']); ?></td>
                                    <td><?php echo esc_html($entry['product_name']); ?></td>
                                    <td><?php echo esc_html($entry['staff_name']); ?></td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($entry['date_of_preparation']))); ?></td>
                                    <td><?php echo esc_html(number_format($entry['opening'], 2)); ?></td>
                                    <td><?php echo esc_html(number_format($entry['total_added'], 2)); ?></td>
                                    <td><?php echo esc_html(number_format($entry['total_sold'], 2)); ?></td>
                                    <td><?php echo esc_html(number_format($entry['closing'], 2)); ?></td>
                                    <td class="fim-actions">
                                        <button type="button" class="fim-admin-btn fim-admin-btn-primary fim-admin-btn-sm fim-edit-inventory-button" data-inventory-id="<?php echo esc_attr($entry['id']); ?>">
                                            <?php _e('Edit', 'fruit-inventory-manager'); ?>
                                        </button>
                                        <button type="button" class="fim-admin-btn fim-admin-btn-danger fim-admin-btn-sm fim-delete-inventory-button" data-inventory-id="<?php echo esc_attr($entry['id']); ?>">
                                            <?php _e('Delete', 'fruit-inventory-manager'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="text-center"><?php _e('No recent entries found', 'fruit-inventory-manager'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="fim-admin-card">
        <div class="fim-admin-card-header">
            <h2><?php _e('Recent Activity Logs', 'fruit-inventory-manager'); ?></h2>
            <a href="<?php echo esc_url(admin_url('admin.php?page=fim-logs')); ?>" class="fim-admin-btn fim-admin-btn-sm fim-admin-btn-secondary">
                <?php _e('View All', 'fruit-inventory-manager'); ?>
            </a>
        </div>
        <div class="fim-admin-card-body">
            <div class="fim-admin-table-container">
                <table class="fim-admin-table">
                    <thead>
                        <tr>
                            <th><?php _e('ID', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('User', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Action', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Object Type', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Object ID', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('IP Address', 'fruit-inventory-manager'); ?></th>
                            <th><?php _e('Date & Time', 'fruit-inventory-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_logs)) : ?>
                            <?php foreach ($recent_logs as $log) : ?>
                                <?php
                                $user = get_user_by('id', $log['user_id']);
                                $user_name = $user ? $user->display_name : __('Unknown', 'fruit-inventory-manager');
                                
                                $action_label = ucfirst($log['action']);
                                $object_type_label = ucfirst($log['object_type']);
                                ?>
                                <tr>
                                    <td><?php echo esc_html($log['id']); ?></td>
                                    <td><?php echo esc_html($user_name); ?></td>
                                    <td><?php echo esc_html($action_label); ?></td>
                                    <td><?php echo esc_html($object_type_label); ?></td>
                                    <td><?php echo esc_html($log['object_id']); ?></td>
                                    <td><?php echo esc_html($log['ip_address']); ?></td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['created_at']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center"><?php _e('No recent logs found', 'fruit-inventory-manager'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="fim-admin-card">
        <div class="fim-admin-card-header">
            <h2><?php _e('Quick Links', 'fruit-inventory-manager'); ?></h2>
        </div>
        <div class="fim-admin-card-body">
            <div class="fim-admin-quick-links">
                <a href="<?php echo esc_url(admin_url('admin.php?page=fim-products')); ?>" class="fim-admin-btn fim-admin-btn-primary">
                    <?php _e('Manage Products', 'fruit-inventory-manager'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fim-inventory')); ?>" class="fim-admin-btn fim-admin-btn-secondary">
                    <?php _e('View Inventory', 'fruit-inventory-manager'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fim-logs')); ?>" class="fim-admin-btn fim-admin-btn-secondary">
                    <?php _e('Activity Logs', 'fruit-inventory-manager'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fim-settings')); ?>" class="fim-admin-btn fim-admin-btn-secondary">
                    <?php _e('Settings', 'fruit-inventory-manager'); ?>
                </a>
                <?php
                $shortcode_page_id = get_option('fim_shortcode_page_id', 0);
                if ($shortcode_page_id) :
                    $shortcode_page_url = get_permalink($shortcode_page_id);
                ?>
                <a href="<?php echo esc_url($shortcode_page_url); ?>" class="fim-admin-btn fim-admin-btn-primary" target="_blank">
                    <?php _e('View Frontend Form', 'fruit-inventory-manager'); ?>
                </a>
                <?php endif; ?>
                
                <?php
                $records_page_id = get_option('fim_records_page_id', 0);
                if ($records_page_id) :
                    $records_page_url = get_permalink($records_page_id);
                ?>
                <a href="<?php echo esc_url($records_page_url); ?>" class="fim-admin-btn fim-admin-btn-primary" target="_blank">
                    <?php _e('View Frontend Records', 'fruit-inventory-manager'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
