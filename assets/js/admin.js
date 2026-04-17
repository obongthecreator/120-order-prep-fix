/**
 * Admin JavaScript for Fruit Inventory Manager
 * Clean rebuild - no animations
 */

(function($) {
    'use strict';

    var FIMAdmin = {
        
        init: function() {
            this.cacheDom();
            this.bindEvents();
            this.initializeTabs();
            this.initializeDatepicker();
            
            if (this.$productsTable.length) {
                this.initializeProducts();
            }
            
            if (this.$inventoryTable.length) {
                this.initializeInventory();
            }
            
            if (this.$logsTable.length) {
                this.initializeLogs();
            }
        },
        
        cacheDom: function() {
            this.$document = $(document);
            this.$window = $(window);
            this.$body = $('body');
            
            // Tabs
            this.$tabs = $('.fim-admin-tabs');
            this.$tabLinks = $('.fim-admin-tab-link');
            this.$tabContents = $('.fim-admin-tab-content');
            
            // Products page
            this.$productsTable = $('#fim-admin-products-table');
            this.$addProductButton = $('#fim-add-product-button');
            this.$productModal = $('#fim-product-modal');
            this.$productForm = $('#fim-product-form');
            this.$productNameField = $('#fim-product-name');
            this.$productTypeField = $('#fim-product-type');
            this.$productStatusField = $('#fim-product-status');
            this.$saveProductButton = $('#fim-save-product-button');
            
            // Inventory page
            this.$inventoryTable = $('#fim-admin-inventory-table');
            this.$inventoryTableBody = this.$inventoryTable.find('tbody');
            this.$inventoryPagination = $('#fim-admin-inventory-pagination');
            this.$inventoryFilterForm = $('#fim-admin-inventory-filter-form');
            this.$inventoryProductFilter = $('#fim-admin-inventory-product-filter');
            this.$inventoryDateFromFilter = $('#fim-admin-inventory-date-from-filter');
            this.$inventoryDateToFilter = $('#fim-admin-inventory-date-to-filter');
            this.$inventoryFilterButton = $('#fim-admin-inventory-filter-button');
            this.$inventoryResetFilterButton = $('#fim-admin-inventory-reset-filter-button');
            this.$clearInventoryButton = $('#fim-clear-inventory-button');
            this.$inventoryModal = $('#fim-inventory-modal');
            this.$inventoryForm = $('#fim-inventory-form');
            
            // Logs page
            this.$logsTable = $('#fim-admin-logs-table');
            this.$logsTableBody = this.$logsTable.find('tbody');
            this.$logsPagination = $('#fim-admin-logs-pagination');
            this.$logsFilterForm = $('#fim-admin-logs-filter-form');
            
            // Modal
            this.$modalBackdrop = $('.fim-modal-backdrop');
            this.$modals = $('.fim-modal');
            this.$modalCloseButtons = $('.fim-modal-close');
            
            // Toast container
            this.$toastContainer = $('#fim-admin-toast-container');
            if (!this.$toastContainer.length) {
                this.$toastContainer = $('<div id="fim-admin-toast-container"></div>').appendTo(this.$body);
            }
        },
        
        bindEvents: function() {
            this.$tabLinks.on('click', this.handleTabClick.bind(this));
            
            this.$addProductButton.on('click', this.openAddProductModal.bind(this));
            this.$productsTable.on('click', '.fim-edit-product-button', this.openEditProductModal.bind(this));
            this.$productsTable.on('click', '.fim-delete-product-button', this.confirmDeleteProduct.bind(this));
            this.$productForm.on('submit', this.handleProductFormSubmit.bind(this));
            
            this.$inventoryFilterForm.on('submit', this.handleInventoryFilterSubmit.bind(this));
            this.$inventoryResetFilterButton.on('click', this.resetInventoryFilters.bind(this));
            this.$inventoryPagination.on('click', '.fim-page-link', this.handleInventoryPaginationClick.bind(this));
            this.$inventoryTable.on('click', '.fim-edit-inventory-button', this.openEditInventoryModal.bind(this));
            this.$inventoryTable.on('click', '.fim-delete-inventory-button', this.confirmDeleteInventory.bind(this));
            this.$clearInventoryButton.on('click', this.confirmClearInventory.bind(this));
            this.$inventoryForm.on('submit', this.handleInventoryFormSubmit.bind(this));
            this.$inventoryForm.on('input', '.fim-total-added, .fim-total-sold', this.calculateInventoryClosing.bind(this));
            
            this.$logsFilterForm.on('submit', this.handleLogsFilterSubmit.bind(this));
            this.$logsPagination.on('click', '.fim-page-link', this.handleLogsPaginationClick.bind(this));
            
            this.$modalCloseButtons.on('click', this.closeModal.bind(this));
            this.$modalBackdrop.on('click', function(e) {
                if ($(e.target).hasClass('fim-modal-backdrop')) {
                    FIMAdmin.closeModal();
                }
            });
        },
        
        initializeTabs: function() {
            if (this.$tabs.length && this.$tabLinks.length) {
                this.$tabLinks.first().addClass('active');
                var firstTabId = this.$tabLinks.first().attr('href');
                $(firstTabId).addClass('active');
            }
        },
        
        initializeDatepicker: function() {
            if ($.fn.datepicker) {
                this.$inventoryDateFromFilter.datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    maxDate: '0',
                    onSelect: function(selectedDate) {
                        FIMAdmin.$inventoryDateToFilter.datepicker('option', 'minDate', selectedDate);
                    }
                });
                
                this.$inventoryDateToFilter.datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    maxDate: '0',
                    onSelect: function(selectedDate) {
                        FIMAdmin.$inventoryDateFromFilter.datepicker('option', 'maxDate', selectedDate);
                    }
                });
            }
        },
        
        handleTabClick: function(e) {
            e.preventDefault();
            
            var $link = $(e.currentTarget);
            var tabId = $link.attr('href');
            
            this.$tabLinks.removeClass('active');
            this.$tabContents.removeClass('active');
            
            $link.addClass('active');
            $(tabId).addClass('active');
        },
        
        initializeProducts: function() {
            if ($.fn.sortable) {
                this.$productsTable.find('tbody').sortable({
                    handle: '.fim-sort-handle',
                    axis: 'y',
                    update: function() {
                        var order = [];
                        $(this).find('tr').each(function(index) {
                            order.push({
                                id: $(this).data('product-id'),
                                position: index
                            });
                        });
                        
                        $.ajax({
                            url: fim_admin_params.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'fim_admin_update_product_order',
                                nonce: fim_admin_params.nonce,
                                order: order
                            },
                            success: function(response) {
                                if (response.success) {
                                    FIMAdmin.showToast('success', 'Success', response.data.message);
                                } else {
                                    FIMAdmin.showToast('error', 'Error', response.data.message);
                                }
                            },
                            error: function() {
                                FIMAdmin.showToast('error', 'Error', 'Failed to update product order');
                            }
                        });
                    }
                });
            }
        },
        
        openAddProductModal: function() {
            this.$productForm[0].reset();
            this.$productForm.find('input[name="product_id"]').val('');
            this.$productModal.find('.fim-modal-title').text('Add Product');
            this.openModal(this.$productModal);
        },
        
        openEditProductModal: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var productId = $button.data('product-id');
            
            this.$productModal.find('.fim-modal-body').html('<div class="fim-loader-container"><div class="fim-loader"></div><span class="fim-loader-text">Loading product data...</span></div>');
            this.openModal(this.$productModal);
            
            $.ajax({
                url: fim_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_admin_get_product',
                    nonce: fim_admin_params.nonce,
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        FIMAdmin.$productForm[0].reset();
                        FIMAdmin.$productForm.find('input[name="product_id"]').val(response.data.product.id);
                        FIMAdmin.$productNameField.val(response.data.product.product_name);
                        FIMAdmin.$productTypeField.val(response.data.product.product_type);
                        FIMAdmin.$productStatusField.val(response.data.product.status);
                        FIMAdmin.$productModal.find('.fim-modal-title').text('Edit Product');
                        FIMAdmin.$productModal.find('.fim-modal-body').html(FIMAdmin.$productForm);
                    } else {
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                        FIMAdmin.closeModal();
                    }
                },
                error: function() {
                    FIMAdmin.showToast('error', 'Error', 'Failed to get product data');
                    FIMAdmin.closeModal();
                }
            });
        },
        
        handleProductFormSubmit: function(e) {
            e.preventDefault();
            
            if (!this.validateProductForm()) {
                return;
            }
            
            var productId = this.$productForm.find('input[name="product_id"]').val();
            var productName = this.$productNameField.val();
            var productType = this.$productTypeField.val();
            var productStatus = this.$productStatusField.val();
            
            this.$saveProductButton.addClass('fim-btn-loading').prop('disabled', true);
            
            $.ajax({
                url: fim_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_admin_save_product',
                    nonce: fim_admin_params.nonce,
                    product_id: productId,
                    product_name: productName,
                    product_type: productType,
                    product_status: productStatus
                },
                success: function(response) {
                    FIMAdmin.$saveProductButton.removeClass('fim-btn-loading').prop('disabled', false);
                    
                    if (response.success) {
                        FIMAdmin.showToast('success', 'Success', response.data.message);
                        FIMAdmin.closeModal();
                        location.reload();
                    } else {
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                    }
                },
                error: function() {
                    FIMAdmin.$saveProductButton.removeClass('fim-btn-loading').prop('disabled', false);
                    FIMAdmin.showToast('error', 'Error', 'Failed to save product');
                }
            });
        },
        
        validateProductForm: function() {
            if (!this.$productNameField.val()) {
                this.showToast('error', 'Error', 'Please enter product name');
                this.$productNameField.focus();
                return false;
            }
            
            if (!this.$productTypeField.val()) {
                this.showToast('error', 'Error', 'Please select product type');
                this.$productTypeField.focus();
                return false;
            }
            
            return true;
        },
        
        confirmDeleteProduct: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var productId = $button.data('product-id');
            var productName = $button.data('product-name');
            
            if (confirm('Are you sure you want to delete the product "' + productName + '"? This action cannot be undone.')) {
                this.deleteProduct(productId);
            }
        },
        
        deleteProduct: function(productId) {
            var $row = this.$productsTable.find('tr[data-product-id="' + productId + '"]');
            $row.addClass('fim-deleting');
            
            $.ajax({
                url: fim_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_admin_delete_product',
                    nonce: fim_admin_params.nonce,
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        FIMAdmin.showToast('success', 'Success', response.data.message);
                        $row.remove();
                    } else {
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                        $row.removeClass('fim-deleting');
                    }
                },
                error: function() {
                    FIMAdmin.showToast('error', 'Error', 'Failed to delete product');
                    $row.removeClass('fim-deleting');
                }
            });
        },
        
        initializeInventory: function() {
            this.loadInventoryRecords(1);
        },
        
        loadInventoryRecords: function(page) {
            this.$inventoryTableBody.html('<tr><td colspan="10" class="fim-loader-container"><div class="fim-loader"></div><span class="fim-loader-text">Loading inventory...</span></td></tr>');
            
            var productId = this.$inventoryProductFilter.val();
            var dateFrom = this.$inventoryDateFromFilter.val();
            var dateTo = this.$inventoryDateToFilter.val();
            
            $.ajax({
                url: fim_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_admin_get_inventory_list',
                    nonce: fim_admin_params.nonce,
                    page: page,
                    per_page: 20,
                    product_id: productId,
                    date_from: dateFrom,
                    date_to: dateTo
                },
                success: function(response) {
                    if (response.success) {
                        FIMAdmin.renderInventoryRecords(response.data.records);
                        FIMAdmin.renderInventoryPagination(response.data);
                    } else {
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                        FIMAdmin.$inventoryTableBody.html('<tr><td colspan="10" class="text-center">No records found</td></tr>');
                    }
                },
                error: function() {
                    FIMAdmin.showToast('error', 'Error', 'Failed to load inventory records');
                    FIMAdmin.$inventoryTableBody.html('<tr><td colspan="10" class="text-center">Error loading records</td></tr>');
                }
            });
        },
        
        renderInventoryRecords: function(records) {
            this.$inventoryTableBody.empty();
            
            if (records.length === 0) {
                this.$inventoryTableBody.html('<tr><td colspan="10" class="text-center">No records found</td></tr>');
                return;
            }
            
            $.each(records, function(index, record) {
                var $row = $('<tr></tr>');
                $row.attr('data-inventory-id', record.id);
                
                $row.append('<td>' + record.id + '</td>');
                $row.append('<td>' + record.product_name + '</td>');
                $row.append('<td>' + record.staff_name + '</td>');
                $row.append('<td>' + record.date + '</td>');
                $row.append('<td>' + record.opening + '</td>');
                $row.append('<td>' + record.total_added + '</td>');
                $row.append('<td>' + record.total_sold + '</td>');
                $row.append('<td>' + record.closing + '</td>');
                $row.append('<td>' + (record.remarks || '-') + '</td>');
                
                var actions = '<td class="fim-actions">';
                actions += '<button type="button" class="fim-admin-btn fim-admin-btn-primary fim-admin-btn-sm fim-edit-inventory-button" data-inventory-id="' + record.id + '">Edit</button> ';
                actions += '<button type="button" class="fim-admin-btn fim-admin-btn-danger fim-admin-btn-sm fim-delete-inventory-button" data-inventory-id="' + record.id + '">Delete</button>';
                actions += '</td>';
                
                $row.append(actions);
                FIMAdmin.$inventoryTableBody.append($row);
            });
        },
        
        renderInventoryPagination: function(data) {
            this.$inventoryPagination.empty();
            
            if (data.total_pages <= 1) {
                return;
            }
            
            var $prevItem = $('<li class="fim-page-item"></li>');
            var $prevLink = $('<a href="#" class="fim-page-link" data-page="' + (data.current_page - 1) + '">Previous</a>');
            
            if (data.current_page === 1) {
                $prevItem.addClass('disabled');
                $prevLink.attr('tabindex', '-1');
            }
            
            $prevItem.append($prevLink);
            this.$inventoryPagination.append($prevItem);
            
            var startPage = Math.max(1, data.current_page - 2);
            var endPage = Math.min(data.total_pages, startPage + 4);
            
            for (var i = startPage; i <= endPage; i++) {
                var $pageItem = $('<li class="fim-page-item"></li>');
                var $pageLink = $('<a href="#" class="fim-page-link" data-page="' + i + '">' + i + '</a>');
                
                if (i === data.current_page) {
                    $pageItem.addClass('active');
                }
                
                $pageItem.append($pageLink);
                this.$inventoryPagination.append($pageItem);
            }
            
            var $nextItem = $('<li class="fim-page-item"></li>');
            var $nextLink = $('<a href="#" class="fim-page-link" data-page="' + (data.current_page + 1) + '">Next</a>');
            
            if (data.current_page === data.total_pages) {
                $nextItem.addClass('disabled');
                $nextLink.attr('tabindex', '-1');
            }
            
            $nextItem.append($nextLink);
            this.$inventoryPagination.append($nextItem);
        },
        
        handleInventoryPaginationClick: function(e) {
            e.preventDefault();
            
            var $link = $(e.currentTarget);
            var page = parseInt($link.data('page'), 10);
            
            if ($link.parent().hasClass('disabled')) {
                return;
            }
            
            this.loadInventoryRecords(page);
            $('html, body').scrollTop(this.$inventoryTable.offset().top - 50);
        },
        
        handleInventoryFilterSubmit: function(e) {
            e.preventDefault();
            this.loadInventoryRecords(1);
        },
        
        resetInventoryFilters: function(e) {
            e.preventDefault();
            this.$inventoryProductFilter.val('');
            this.$inventoryDateFromFilter.val('');
            this.$inventoryDateToFilter.val('');
            this.loadInventoryRecords(1);
        },
        
        openEditInventoryModal: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var inventoryId = $button.data('inventory-id');
            
            this.$inventoryModal.find('.fim-modal-body').html('<div class="fim-loader-container"><div class="fim-loader"></div><span class="fim-loader-text">Loading inventory data...</span></div>');
            this.openModal(this.$inventoryModal);
            
            $.ajax({
                url: fim_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_admin_get_inventory',
                    nonce: fim_admin_params.nonce,
                    inventory_id: inventoryId
                },
                success: function(response) {
                    if (response.success) {
                        FIMAdmin.$inventoryForm[0].reset();
                        FIMAdmin.$inventoryForm.find('input[name="inventory_id"]').val(response.data.inventory.id);
                        FIMAdmin.$inventoryForm.find('.fim-product-name').text(response.data.inventory.product_name);
                        FIMAdmin.$inventoryForm.find('.fim-staff-name').text(response.data.inventory.staff_name);
                        FIMAdmin.$inventoryForm.find('.fim-date').text(response.data.inventory.date_of_preparation);
                        FIMAdmin.$inventoryForm.find('.fim-opening').val(response.data.inventory.opening);
                        FIMAdmin.$inventoryForm.find('.fim-total-added').val(response.data.inventory.total_added);
                        FIMAdmin.$inventoryForm.find('.fim-total-sold').val(response.data.inventory.total_sold);
                        FIMAdmin.$inventoryForm.find('.fim-closing').val(response.data.inventory.closing);
                        FIMAdmin.$inventoryForm.find('.fim-remarks').val(response.data.inventory.remarks);
                        FIMAdmin.$inventoryModal.find('.fim-modal-title').text('Edit Inventory Entry');
                        FIMAdmin.$inventoryModal.find('.fim-modal-body').html(FIMAdmin.$inventoryForm);
                    } else {
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                        FIMAdmin.closeModal();
                    }
                },
                error: function() {
                    FIMAdmin.showToast('error', 'Error', 'Failed to get inventory data');
                    FIMAdmin.closeModal();
                }
            });
        },
        
        calculateInventoryClosing: function() {
            var opening = parseFloat(this.$inventoryForm.find('.fim-opening').val()) || 0;
            var totalAdded = parseFloat(this.$inventoryForm.find('.fim-total-added').val()) || 0;
            var totalSold = parseFloat(this.$inventoryForm.find('.fim-total-sold').val()) || 0;
            var closing = opening + totalAdded - totalSold;
            this.$inventoryForm.find('.fim-closing').val(closing.toFixed(2));
        },
        
        handleInventoryFormSubmit: function(e) {
            e.preventDefault();
            
            var inventoryId = this.$inventoryForm.find('input[name="inventory_id"]').val();
            var totalAdded = this.$inventoryForm.find('.fim-total-added').val();
            var totalSold = this.$inventoryForm.find('.fim-total-sold').val();
            var remarks = this.$inventoryForm.find('.fim-remarks').val();
            
            this.$inventoryForm.find('.fim-save-button').addClass('fim-btn-loading').prop('disabled', true);
            
            $.ajax({
                url: fim_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_admin_save_inventory',
                    nonce: fim_admin_params.nonce,
                    inventory_id: inventoryId,
                    total_added: totalAdded,
                    total_sold: totalSold,
                    remarks: remarks
                },
                success: function(response) {
                    FIMAdmin.$inventoryForm.find('.fim-save-button').removeClass('fim-btn-loading').prop('disabled', false);
                    
                    if (response.success) {
                        FIMAdmin.showToast('success', 'Success', response.data.message);
                        FIMAdmin.closeModal();
                        FIMAdmin.loadInventoryRecords(1);
                    } else {
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                    }
                },
                error: function() {
                    FIMAdmin.$inventoryForm.find('.fim-save-button').removeClass('fim-btn-loading').prop('disabled', false);
                    FIMAdmin.showToast('error', 'Error', 'Failed to save inventory');
                }
            });
        },
        
        confirmDeleteInventory: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var inventoryId = $button.data('inventory-id');
            
            if (confirm('Are you sure you want to delete this inventory entry? This action cannot be undone.')) {
                this.deleteInventory(inventoryId);
            }
        },
        
        deleteInventory: function(inventoryId) {
            var $row = this.$inventoryTable.find('tr[data-inventory-id="' + inventoryId + '"]');
            $row.addClass('fim-deleting');
            
            $.ajax({
                url: fim_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_admin_delete_inventory',
                    nonce: fim_admin_params.nonce,
                    inventory_id: inventoryId
                },
                success: function(response) {
                    if (response.success) {
                        FIMAdmin.showToast('success', 'Success', response.data.message);
                        $row.remove();
                    } else {
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                        $row.removeClass('fim-deleting');
                    }
                },
                error: function() {
                    FIMAdmin.showToast('error', 'Error', 'Failed to delete inventory');
                    $row.removeClass('fim-deleting');
                }
            });
        },
        
        confirmClearInventory: function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to clear all inventory data? This action cannot be undone.')) {
                this.clearInventory();
            }
        },
        
        clearInventory: function() {
            this.$clearInventoryButton.addClass('fim-btn-loading').prop('disabled', true);
            
            $.ajax({
                url: fim_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_admin_clear_inventory',
                    nonce: fim_admin_params.nonce
                },
                success: function(response) {
                    FIMAdmin.$clearInventoryButton.removeClass('fim-btn-loading').prop('disabled', false);
                    
                    if (response.success) {
                        FIMAdmin.showToast('success', 'Success', response.data.message);
                        FIMAdmin.loadInventoryRecords(1);
                    } else {
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                    }
                },
                error: function() {
                    FIMAdmin.$clearInventoryButton.removeClass('fim-btn-loading').prop('disabled', false);
                    FIMAdmin.showToast('error', 'Error', 'Failed to clear inventory');
                }
            });
        },
        
        initializeLogs: function() {
            this.loadLogs(1);
        },
        
        loadLogs: function(page) {
            this.$logsTableBody.html('<tr><td colspan="7" class="fim-loader-container"><div class="fim-loader"></div><span class="fim-loader-text">Loading logs...</span></td></tr>');
            
            var userId = this.$logsFilterForm.find('.fim-user-filter').val();
            var actionType = this.$logsFilterForm.find('.fim-action-filter').val();
            var objectType = this.$logsFilterForm.find('.fim-object-type-filter').val();
            
            $.ajax({
                url: fim_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_admin_get_logs',
                    nonce: fim_admin_params.nonce,
                    page: page,
                    per_page: 20,
                    user_id: userId,
                    action_type: actionType,
                    object_type: objectType
                },
                success: function(response) {
                    if (response.success) {
                        FIMAdmin.renderLogs(response.data.logs);
                        if (response.data.total_pages) {
                            FIMAdmin.renderLogsPagination(response.data);
                        }
                    } else {
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                        FIMAdmin.$logsTableBody.html('<tr><td colspan="7" class="text-center">No logs found</td></tr>');
                    }
                },
                error: function() {
                    FIMAdmin.showToast('error', 'Error', 'Failed to load logs');
                    FIMAdmin.$logsTableBody.html('<tr><td colspan="7" class="text-center">Error loading logs</td></tr>');
                }
            });
        },
        
        renderLogs: function(logs) {
            this.$logsTableBody.empty();
            
            if (logs.length === 0) {
                this.$logsTableBody.html('<tr><td colspan="7" class="text-center">No logs found</td></tr>');
                return;
            }
            
            $.each(logs, function(index, log) {
                var $row = $('<tr></tr>');
                
                $row.append('<td>' + log.id + '</td>');
                $row.append('<td>' + log.user_name + '</td>');
                $row.append('<td>' + log.action + '</td>');
                $row.append('<td>' + log.object_type + '</td>');
                $row.append('<td>' + log.object_id + '</td>');
                $row.append('<td>' + log.ip_address + '</td>');
                $row.append('<td>' + log.created_at + '</td>');
                
                FIMAdmin.$logsTableBody.append($row);
            });
        },
        
        renderLogsPagination: function(data) {
            this.$logsPagination.empty();
            
            if (data.total_pages <= 1) {
                return;
            }
            
            var $prevItem = $('<li class="fim-page-item"></li>');
            var $prevLink = $('<a href="#" class="fim-page-link" data-page="' + (data.current_page - 1) + '">Previous</a>');
            
            if (data.current_page === 1) {
                $prevItem.addClass('disabled');
                $prevLink.attr('tabindex', '-1');
            }
            
            $prevItem.append($prevLink);
            this.$logsPagination.append($prevItem);
            
            var startPage = Math.max(1, data.current_page - 2);
            var endPage = Math.min(data.total_pages, startPage + 4);
            
            for (var i = startPage; i <= endPage; i++) {
                var $pageItem = $('<li class="fim-page-item"></li>');
                var $pageLink = $('<a href="#" class="fim-page-link" data-page="' + i + '">' + i + '</a>');
                
                if (i === data.current_page) {
                    $pageItem.addClass('active');
                }
                
                $pageItem.append($pageLink);
                this.$logsPagination.append($pageItem);
            }
            
            var $nextItem = $('<li class="fim-page-item"></li>');
            var $nextLink = $('<a href="#" class="fim-page-link" data-page="' + (data.current_page + 1) + '">Next</a>');
            
            if (data.current_page === data.total_pages) {
                $nextItem.addClass('disabled');
                $nextLink.attr('tabindex', '-1');
            }
            
            $nextItem.append($nextLink);
            this.$logsPagination.append($nextItem);
        },
        
        handleLogsPaginationClick: function(e) {
            e.preventDefault();
            
            var $link = $(e.currentTarget);
            var page = parseInt($link.data('page'), 10);
            
            if ($link.parent().hasClass('disabled')) {
                return;
            }
            
            this.loadLogs(page);
            $('html, body').scrollTop(this.$logsTable.offset().top - 50);
        },
        
        handleLogsFilterSubmit: function(e) {
            e.preventDefault();
            this.loadLogs(1);
        },
        
        openModal: function($modal) {
            this.$modalBackdrop.addClass('show');
            $modal.addClass('show');
        },
        
        closeModal: function() {
            this.$modalBackdrop.removeClass('show');
            this.$modals.removeClass('show');
        },
        
        showToast: function(type, title, message) {
            var toastId = 'fim-toast-' + Math.floor(Math.random() * 10000);
            var $toast = $('<div id="' + toastId + '" class="fim-toast fim-toast-' + type + '"></div>');
            
            var iconClass = 'dashicons ';
            switch (type) {
                case 'success': iconClass += 'dashicons-yes-alt'; break;
                case 'error': iconClass += 'dashicons-dismiss'; break;
                case 'warning': iconClass += 'dashicons-warning'; break;
                case 'info': iconClass += 'dashicons-info'; break;
            }
            
            $toast.append('<div class="fim-toast-icon"><span class="' + iconClass + '"></span></div>');
            
            var $content = $('<div class="fim-toast-content"></div>');
            $content.append('<div class="fim-toast-title">' + title + '</div>');
            $content.append('<div class="fim-toast-message">' + message + '</div>');
            $toast.append($content);
            
            var $close = $('<button type="button" class="fim-toast-close">&times;</button>');
            $close.on('click', function() {
                FIMAdmin.hideToast(toastId);
            });
            $toast.append($close);
            
            this.$toastContainer.append($toast);
            
            setTimeout(function() {
                FIMAdmin.hideToast(toastId);
            }, 5000);
        },
        
        hideToast: function(toastId) {
            var $toast = $('#' + toastId);
            if ($toast.length) {
                $toast.remove();
            }
        }
    };
    
    $(document).ready(function() {
        FIMAdmin.init();
    });
    
})(jQuery);
