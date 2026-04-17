<?php
/**
 * Admin Logs Template
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
        <h1><?php _e('Activity Logs', 'fruit-inventory-manager'); ?></h1>
    </div>
    
    <div class="fim-admin-card">
        <div class="fim-admin-card-header">
            <h2><?php _e('Filters', 'fruit-inventory-manager'); ?></h2>
        </div>
        <div class="fim-admin-card-body">
            <form id="fim-admin-logs-filter-form" class="fim-admin-form">
                <div class="fim-admin-filters">
                    <div class="fim-admin-filter-item">
                        <label for="fim-admin-logs-user-filter" class="fim-admin-filter-label"><?php _e('User', 'fruit-inventory-manager'); ?></label>
                        <select id="fim-admin-logs-user-filter" class="fim-admin-filter-control fim-user-filter">
                            <option value=""><?php _e('All Users', 'fruit-inventory-manager'); ?></option>
                            <?php
                            $users = get_users();
                            foreach ($users as $user) :
                            ?>
                                <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="fim-admin-filter-item">
                        <label for="fim-admin-logs-action-filter" class="fim-admin-filter-label"><?php _e('Action', 'fruit-inventory-manager'); ?></label>
                        <select id="fim-admin-logs-action-filter" class="fim-admin-filter-control fim-action-filter">
                            <option value=""><?php _e('All Actions', 'fruit-inventory-manager'); ?></option>
                            <option value="add"><?php _e('Add', 'fruit-inventory-manager'); ?></option>
                            <option value="update"><?php _e('Update', 'fruit-inventory-manager'); ?></option>
                            <option value="delete"><?php _e('Delete', 'fruit-inventory-manager'); ?></option>
                            <option value="clear"><?php _e('Clear', 'fruit-inventory-manager'); ?></option>
                        </select>
                    </div>
                    
                    <div class="fim-admin-filter-item">
                        <label for="fim-admin-logs-object-type-filter" class="fim-admin-filter-label"><?php _e('Object Type', 'fruit-inventory-manager'); ?></label>
                        <select id="fim-admin-logs-object-type-filter" class="fim-admin-filter-control fim-object-type-filter">
                            <option value=""><?php _e('All Types', 'fruit-inventory-manager'); ?></option>
                            <option value="product"><?php _e('Product', 'fruit-inventory-manager'); ?></option>
                            <option value="inventory"><?php _e('Inventory', 'fruit-inventory-manager'); ?></option>
                        </select>
                    </div>
                    
                    <div class="fim-admin-filter-buttons">
                        <button type="submit" id="fim-admin-logs-filter-button" class="fim-admin-btn fim-admin-btn-primary">
                            <?php _e('Apply Filters', 'fruit-inventory-manager'); ?>
                        </button>
                        <button type="button" id="fim-admin-logs-reset-filter-button" class="fim-admin-btn fim-admin-btn-secondary">
                            <?php _e('Reset', 'fruit-inventory-manager'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="fim-admin-card">
        <div class="fim-admin-card-header">
            <h2><?php _e('Logs', 'fruit-inventory-manager'); ?></h2>
        </div>
        <div class="fim-admin-card-body">
            <div class="fim-admin-table-container">
                <table id="fim-admin-logs-table" class="fim-admin-table">
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
                    <tbody id="fim-admin-logs-table-body">
                        <tr>
                            <td colspan="7" class="fim-admin-loader-container">
                                <div class="fim-admin-loader"></div>
                                <span class="fim-admin-loader-text"><?php _e('Loading logs...', 'fruit-inventory-manager'); ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <ul id="fim-admin-logs-pagination" class="fim-admin-pagination"></ul>
        </div>
    </div>
</div>
