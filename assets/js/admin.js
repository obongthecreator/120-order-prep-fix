/**
 * Admin JavaScript for Fruit Inventory Manager
 */

(function($) {
    'use strict';

    // Main FIM Admin object
    var FIMAdmin = {
        
        /**
         * Initialize the app
         */
        init: function() {
            this.cacheDom();
            this.bindEvents();
            this.initializeTabs();
            this.initializeDatepicker();
            
            // Initialize based on the current page
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
        
        /**
         * Cache DOM elements
         */
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
        
        /**
         * Bind events
         */
        bindEvents: function() {
            // Tabs
            this.$tabLinks.on('click', this.handleTabClick.bind(this));
            
            // Products
            this.$addProductButton.on('click', this.openAddProductModal.bind(this));
            this.$productsTable.on('click', '.fim-edit-product-button', this.openEditProductModal.bind(this));
            this.$productsTable.on('click', '.fim-delete-product-button', this.confirmDeleteProduct.bind(this));
            this.$productForm.on('submit', this.handleProductFormSubmit.bind(this));
            
            // Inventory
            this.$inventoryFilterForm.on('submit', this.handleInventoryFilterSubmit.bind(this));
            this.$inventoryResetFilterButton.on('click', this.resetInventoryFilters.bind(this));
            this.$inventoryPagination.on('click', '.fim-page-link', this.handleInventoryPaginationClick.bind(this));
            this.$inventoryTable.on('click', '.fim-edit-inventory-button', this.openEditInventoryModal.bind(this));
            this.$inventoryTable.on('click', '.fim-delete-inventory-button', this.confirmDeleteInventory.bind(this));
            this.$clearInventoryButton.on('click', this.confirmClearInventory.bind(this));
            this.$inventoryForm.on('submit', this.handleInventoryFormSubmit.bind(this));
            
            // Input calculation for inventory form
            this.$inventoryForm.on('input', '.fim-total-added, .fim-total-sold', this.calculateInventoryClosing.bind(this));
            
            // Logs
            this.$logsFilterForm.on('submit', this.handleLogsFilterSubmit.bind(this));
            this.$logsPagination.on('click', '.fim-page-link', this.handleLogsPaginationClick.bind(this));
            
            // Modal
            this.$modalCloseButtons.on('click', this.closeModal.bind(this));
            this.$modalBackdrop.on('click', function(e) {
                if ($(e.target).hasClass('fim-modal-backdrop')) {
                    FIMAdmin.closeModal();
                }
            });
        },
        
        /**
         * Initialize tabs
         */
        initializeTabs: function() {
            // Show first tab by default
            if (this.$tabs.length && this.$tabLinks.length) {
                this.$tabLinks.first().addClass('active');
                var firstTabId = this.$tabLinks.first().attr('href');
                $(firstTabId).addClass('active');
            }
        },
        
        /**
         * Initialize datepicker
         */
        initializeDatepicker: function() {
            // If jQuery UI datepicker is available, use it
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
        
        /**
         * Handle tab click
         */
        handleTabClick: function(e) {
            e.preventDefault();
            
            var $link = $(e.currentTarget);
            var tabId = $link.attr('href');
            
            // Remove active class from all tabs and contents
            this.$tabLinks.removeClass('active');
            this.$tabContents.removeClass('active');
            
            // Add active class to clicked tab and its content
            $link.addClass('active');
            $(tabId).addClass('active');
        },
        
        /**
         * Initialize products
         */
        initializeProducts: function() {
            // Add sortable functionality if available
            if ($.fn.sortable) {
                this.$productsTable.find('tbody').sortable({
                    handle: '.fim-sort-handle',
                    axis: 'y',
                    update: function(event, ui) {
                        // Update product order
                        var order = [];
                        $(this).find('tr').each(function(index) {
                            order.push({
                                id: $(this).data('product-id'),
                                position: index
                            });
                        });
                        
                        // Save order via AJAX
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
        
        /**
         * Open add product modal
         */
        openAddProductModal: function() {
            // Reset form
            this.$productForm[0].reset();
            this.$productForm.find('input[name="product_id"]').val('');
            this.$productModal.find('.fim-modal-title').text('Add Product');
            
            // Show modal
            this.openModal(this.$productModal);
        },
        
        /**
         * Open edit product modal
         */
        openEditProductModal: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var productId = $button.data('product-id');
            
            // Show loading
            this.$productModal.find('.fim-modal-body').html('<div class="fim-loader-container"><div class="fim-loader"></div><span class="fim-loader-text">Loading product data...</span></div>');
            
            // Show modal
            this.openModal(this.$productModal);
            
            // Get product data
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
                        // Reset form
                        FIMAdmin.$productForm[0].reset();
                        
                        // Set form values
                        FIMAdmin.$productForm.find('input[name="product_id"]').val(response.data.product.id);
                        FIMAdmin.$productNameField.val(response.data.product.product_name);
                        FIMAdmin.$productTypeField.val(response.data.product.product_type);
                        FIMAdmin.$productStatusField.val(response.data.product.status);
                        
                        // Set modal title
                        FIMAdmin.$productModal.find('.fim-modal-title').text('Edit Product');
                        
                        // Show form
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
        
        /**
         * Handle product form submit
         */
        handleProductFormSubmit: function(e) {
            e.preventDefault();
            
            // Validate form
            if (!this.validateProductForm()) {
                return;
            }
            
            // Get form data
            var productId = this.$productForm.find('input[name="product_id"]').val();
            var productName = this.$productNameField.val();
            var productType = this.$productTypeField.val();
            var productStatus = this.$productStatusField.val();
            
            // Show loading
            this.$saveProductButton.addClass('fim-btn-loading').prop('disabled', true);
            
            // Save product
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
                    // Hide loading
                    FIMAdmin.$saveProductButton.removeClass('fim-btn-loading').prop('disabled', false);
                    
                    if (response.success) {
                        // Show success message
                        FIMAdmin.showToast('success', 'Success', response.data.message);
                        
                        // Close modal
                        FIMAdmin.closeModal();
                        
                        // Reload page to show updated products
                        location.reload();
                    } else {
                        // Show error message
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                    }
                },
                error: function() {
                    // Hide loading
                    FIMAdmin.$saveProductButton.removeClass('fim-btn-loading').prop('disabled', false);
                    
                    // Show error message
                    FIMAdmin.showToast('error', 'Error', 'Failed to save product');
                }
            });
        },
        
        /**
         * Validate product form
         */
        validateProductForm: function() {
            var isValid = true;
            
            // Validate product name
            if (!this.$productNameField.val()) {
                this.showToast('error', 'Error', 'Please enter product name');
                this.$productNameField.focus();
                return false;
            }
            
            // Validate product type
            if (!this.$productTypeField.val()) {
                this.showToast('error', 'Error', 'Please select product type');
                this.$productTypeField.focus();
                return false;
            }
            
            return isValid;
        },
        
        /**
         * Confirm delete product
         */
        confirmDeleteProduct: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var productId = $button.data('product-id');
            var productName = $button.data('product-name');
            
            if (confirm('Are you sure you want to delete the product "' + productName + '"? This action cannot be undone.')) {
                this.deleteProduct(productId);
            }
        },
        
        /**
         * Delete product
         */
        deleteProduct: function(productId) {
            // Show loading
            var $row = this.$productsTable.find('tr[data-product-id="' + productId + '"]');
            $row.addClass('fim-deleting');
            
            // Delete product
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
                        // Show success message
                        FIMAdmin.showToast('success', 'Success', response.data.message);
                        
                        // Remove row with animation
                        $row.fadeOut(400, function() {
                            $(this).remove();
                        });
                    } else {
                        // Show error message
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                        
                        // Remove loading class
                        $row.removeClass('fim-deleting');
                    }
                },
                error: function() {
                    // Show error message
                    FIMAdmin.showToast('error', 'Error', 'Failed to delete product');
                    
                    // Remove loading class
                    $row.removeClass('fim-deleting');
                }
            });
        },
        
        /**
         * Initialize inventory
         */
        initializeInventory: function() {
            // Load inventory records
            this.loadInventoryRecords(1);
        },
        
        /**
         * Load inventory records
         */
        loadInventoryRecords: function(page) {
            // Show loading
            this.$inventoryTableBody.html('<tr><td colspan="10" class="fim-loader-container"><div class="fim-loader"></div><span class="fim-loader-text">Loading inventory...</span></td></tr>');
            
            // Get filter values
            var productId = this.$inventoryProductFilter.val();
            var dateFrom = this.$inventoryDateFromFilter.val();
            var dateTo = this.$inventoryDateToFilter.val();
            
            // Get inventory records
            $.ajax({
                url: fim_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_admin_get_inventory_list',
                    nonce: fim_admin_params.nonce,
                    page: page,
                    per_page: 20, // Adjust as needed
                    product_id: productId,
                    date_from: dateFrom,
                    date_to: dateTo
                },
                success: function(response) {
                    if (response.success) {
                        // Render inventory records
                        FIMAdmin.renderInventoryRecords(response.data.records);
                        
                        // Render pagination
                        FIMAdmin.renderInventoryPagination(response.data);
                    } else {
                        // Show error message
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                        
                        // Clear table
                        FIMAdmin.$inventoryTableBody.html('<tr><td colspan="10" class="text-center">No records found</td></tr>');
                    }
                },
                error: function() {
                    // Show error message
                    FIMAdmin.showToast('error', 'Error', 'Failed to load inventory records');
                    
                    // Clear table
                    FIMAdmin.$inventoryTableBody.html('<tr><td colspan="10" class="text-center">Error loading records</td></tr>');
                }
            });
        },
        
        /**
         * Render inventory records
         */
        renderInventoryRecords: function(records) {
            // Clear table
            this.$inventoryTableBody.empty();
            
            if (records.length === 0) {
                this.$inventoryTableBody.html('<tr><td colspan="10" class="text-center">No records found</td></tr>');
                return;
            }
            
            // Add records
            $.each(records, function(index, record) {
                var $row = $('<tr ></tr>');
                
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
                
                // Actions
                var actions = '<td class="fim-actions">';
                actions += '<button type="button" class="fim-admin-btn fim-admin-btn-primary fim-admin-btn-sm fim-edit-inventory-button" data-inventory-id="' + record.id + '">Edit</button> ';
                actions += '<button type="button" class="fim-admin-btn fim-admin-btn-danger fim-admin-btn-sm fim-delete-inventory-button" data-inventory-id="' + record.id + '">Delete</button>';
                actions += '</td>';
                
                $row.append(actions);
                
                FIMAdmin.$inventoryTableBody.append($row);
                
                // Add staggered animation delay
                $row.css('animation-delay', (index * 0.05) + 's');
            });
        },
        
        /**
         * Render inventory pagination
         */
        renderInventoryPagination: function(data) {
            // Clear pagination
            this.$inventoryPagination.empty();
            
            if (data.total_pages <= 1) {
                return;
            }
            
            // Previous button
            var $prevItem = $('<li class="fim-page-item"></li>');
            var $prevLink = $('<a href="#" class="fim-page-link" data-page="' + (data.current_page - 1) + '">Previous</a>');
            
            if (data.current_page === 1) {
                $prevItem.addClass('disabled');
                $prevLink.attr('tabindex', '-1');
            }
            
            $prevItem.append($prevLink);
            this.$inventoryPagination.append($prevItem);
            
            // Page numbers
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
            
            // Next button
            var $nextItem = $('<li class="fim-page-item"></li>');
            var $nextLink = $('<a href="#" class="fim-page-link" data-page="' + (data.current_page + 1) + '">Next</a>');
            
            if (data.current_page === data.total_pages) {
                $nextItem.addClass('disabled');
                $nextLink.attr('tabindex', '-1');
            }
            
            $nextItem.append($nextLink);
            this.$inventoryPagination.append($nextItem);
        },
        
        /**
         * Handle inventory pagination click
         */
        handleInventoryPaginationClick: function(e) {
            e.preventDefault();
            
            var $link = $(e.currentTarget);
            var page = parseInt($link.data('page'), 10);
            
            if ($link.parent().hasClass('disabled')) {
                return;
            }
            
            this.loadInventoryRecords(page);
            
            // Scroll to top of inventory
            $('html, body').animate({
                scrollTop: this.$inventoryTable.offset().top - 50
            }, 500);
        },
        
        /**
         * Handle inventory filter submit
         */
        handleInventoryFilterSubmit: function(e) {
            e.preventDefault();
            
            this.loadInventoryRecords(1);
        },
        
        /**
         * Reset inventory filters
         */
        resetInventoryFilters: function(e) {
            e.preventDefault();
            
            // Reset filter values
            this.$inventoryProductFilter.val('');
            this.$inventoryDateFromFilter.val('');
            this.$inventoryDateToFilter.val('');
            
            // Load records
            this.loadInventoryRecords(1);
        },
        
        /**
         * Open edit inventory modal
         */
        openEditInventoryModal: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var inventoryId = $button.data('inventory-id');
            
            // Show loading
            this.$inventoryModal.find('.fim-modal-body').html('<div class="fim-loader-container"><div class="fim-loader"></div><span class="fim-loader-text">Loading inventory data...</span></div>');
            
            // Show modal
            this.openModal(this.$inventoryModal);
            
            // Get inventory data
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
                        // Reset form
                        FIMAdmin.$inventoryForm[0].reset();
                        
                        // Set form values
                        FIMAdmin.$inventoryForm.find('input[name="inventory_id"]').val(response.data.inventory.id);
                        FIMAdmin.$inventoryForm.find('.fim-product-name').text(response.data.inventory.product_name);
                        FIMAdmin.$inventoryForm.find('.fim-staff-name').text(response.data.inventory.staff_name);
                        FIMAdmin.$inventoryForm.find('.fim-date').text(response.data.inventory.date_of_preparation);
                        FIMAdmin.$inventoryForm.find('.fim-opening').val(response.data.inventory.opening);
                        FIMAdmin.$inventoryForm.find('.fim-total-added').val(response.data.inventory.total_added);
                        FIMAdmin.$inventoryForm.find('.fim-total-sold').val(response.data.inventory.total_sold);
                        FIMAdmin.$inventoryForm.find('.fim-closing').val(response.data.inventory.closing);
                        FIMAdmin.$inventoryForm.find('.fim-remarks').val(response.data.inventory.remarks);
                        
                        // Set modal title
                        FIMAdmin.$inventoryModal.find('.fim-modal-title').text('Edit Inventory Entry');
                        
                        // Show form
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
        
        /**
         * Calculate inventory closing
         */
        calculateInventoryClosing: function() {
            var opening = parseFloat(this.$inventoryForm.find('.fim-opening').val()) || 0;
            var totalAdded = parseFloat(this.$inventoryForm.find('.fim-total-added').val()) || 0;
            var totalSold = parseFloat(this.$inventoryForm.find('.fim-total-sold').val()) || 0;
            
            var closing = opening + totalAdded - totalSold;
            
            // Set the closing value
            this.$inventoryForm.find('.fim-closing').val(closing.toFixed(2));
        },
        
        /**
         * Handle inventory form submit
         */
        handleInventoryFormSubmit: function(e) {
            e.preventDefault();
            
            // Get form data
            var inventoryId = this.$inventoryForm.find('input[name="inventory_id"]').val();
            var totalAdded = this.$inventoryForm.find('.fim-total-added').val();
            var totalSold = this.$inventoryForm.find('.fim-total-sold').val();
            var remarks = this.$inventoryForm.find('.fim-remarks').val();
            
            // Show loading
            this.$inventoryForm.find('.fim-save-button').addClass('fim-btn-loading').prop('disabled', true);
            
            // Save inventory
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
                    // Hide loading
                    FIMAdmin.$inventoryForm.find('.fim-save-button').removeClass('fim-btn-loading').prop('disabled', false);
                    
                    if (response.success) {
                        // Show success message
                        FIMAdmin.showToast('success', 'Success', response.data.message);
                        
                        // Close modal
                        FIMAdmin.closeModal();
                        
                        // Reload inventory records
                        FIMAdmin.loadInventoryRecords(1);
                    } else {
                        // Show error message
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                    }
                },
                error: function() {
                    // Hide loading
                    FIMAdmin.$inventoryForm.find('.fim-save-button').removeClass('fim-btn-loading').prop('disabled', false);
                    
                    // Show error message
                    FIMAdmin.showToast('error', 'Error', 'Failed to save inventory');
                }
            });
        },
        
        /**
         * Confirm delete inventory
         */
        confirmDeleteInventory: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var inventoryId = $button.data('inventory-id');
            
            if (confirm('Are you sure you want to delete this inventory entry? This action cannot be undone.')) {
                this.deleteInventory(inventoryId);
            }
        },
        
        /**
         * Delete inventory
         */
        deleteInventory: function(inventoryId) {
            // Show loading
            var $row = this.$inventoryTable.find('tr[data-inventory-id="' + inventoryId + '"]');
            $row.addClass('fim-deleting');
            
            // Delete inventory
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
                        // Show success message
                        FIMAdmin.showToast('success', 'Success', response.data.message);
                        
                        // Remove row with animation
                        $row.fadeOut(400, function() {
                            $(this).remove();
                        });
                    } else {
                        // Show error message
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                        
                        // Remove loading class
                        $row.removeClass('fim-deleting');
                    }
                },
                error: function() {
                    // Show error message
                    FIMAdmin.showToast('error', 'Error', 'Failed to delete inventory');
                    
                    // Remove loading class
                    $row.removeClass('fim-deleting');
                }
            });
        },
        
        /**
         * Confirm clear inventory
         */
        confirmClearInventory: function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to clear all inventory data? This action cannot be undone.')) {
                this.clearInventory();
            }
        },
        
        /**
         * Clear inventory
         */
        clearInventory: function() {
            // Show loading
            this.$clearInventoryButton.addClass('fim-btn-loading').prop('disabled', true);
            
            // Clear inventory
            $.ajax({
                url: fim_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_admin_clear_inventory',
                    nonce: fim_admin_params.nonce
                },
                success: function(response) {
                    // Hide loading
                    FIMAdmin.$clearInventoryButton.removeClass('fim-btn-loading').prop('disabled', false);
                    
                    if (response.success) {
                        // Show success message
                        FIMAdmin.showToast('success', 'Success', response.data.message);
                        
                        // Reload inventory records
                        FIMAdmin.loadInventoryRecords(1);
                    } else {
                        // Show error message
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                    }
                },
                error: function() {
                    // Hide loading
                    FIMAdmin.$clearInventoryButton.removeClass('fim-btn-loading').prop('disabled', false);
                    
                    // Show error message
                    FIMAdmin.showToast('error', 'Error', 'Failed to clear inventory');
                }
            });
        },
        
        /**
         * Initialize logs
         */
        initializeLogs: function() {
            // Load logs
            this.loadLogs(1);
        },
        
        /**
         * Load logs
         */
        loadLogs: function(page) {
            // Show loading
            this.$logsTableBody.html('<tr><td colspan="7" class="fim-loader-container"><div class="fim-loader"></div><span class="fim-loader-text">Loading logs...</span></td></tr>');
            
            // Get filter values
            var userId = this.$logsFilterForm.find('.fim-user-filter').val();
            var actionType = this.$logsFilterForm.find('.fim-action-filter').val();
            var objectType = this.$logsFilterForm.find('.fim-object-type-filter').val();
            
            // Get logs
            $.ajax({
                url: fim_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_admin_get_logs',
                    nonce: fim_admin_params.nonce,
                    page: page,
                    per_page: 20, // Adjust as needed
                    user_id: userId,
                    action_type: actionType,
                    object_type: objectType
                },
                success: function(response) {
                    if (response.success) {
                        // Render logs
                        FIMAdmin.renderLogs(response.data.logs);
                        
                        // Render pagination if included in response
                        if (response.data.total_pages) {
                            FIMAdmin.renderLogsPagination(response.data);
                        }
                    } else {
                        // Show error message
                        FIMAdmin.showToast('error', 'Error', response.data.message);
                        
                        // Clear table
                        FIMAdmin.$logsTableBody.html('<tr><td colspan="7" class="text-center">No logs found</td></tr>');
                    }
                },
                error: function() {
                    // Show error message
                    FIMAdmin.showToast('error', 'Error', 'Failed to load logs');
                    
                    // Clear table
                    FIMAdmin.$logsTableBody.html('<tr><td colspan="7" class="text-center">Error loading logs</td></tr>');
                }
            });
        },
        
        /**
         * Render logs
         */
        renderLogs: function(logs) {
            // Clear table
            this.$logsTableBody.empty();
            
            if (logs.length === 0) {
                this.$logsTableBody.html('<tr><td colspan="7" class="text-center">No logs found</td></tr>');
                return;
            }
            
            // Add logs
            $.each(logs, function(index, log) {
                var $row = $('<tr ></tr>');
                
                $row.append('<td>' + log.id + '</td>');
                $row.append('<td>' + log.user_name + '</td>');
                $row.append('<td>' + log.action + '</td>');
                $row.append('<td>' + log.object_type + '</td>');
                $row.append('<td>' + log.object_id + '</td>');
                $row.append('<td>' + log.ip_address + '</td>');
                $row.append('<td>' + log.created_at + '</td>');
                
                FIMAdmin.$logsTableBody.append($row);
                
                // Add staggered animation delay
                $row.css('animation-delay', (index * 0.05) + 's');
            });
        },
        
        /**
         * Render logs pagination
         */
        renderLogsPagination: function(data) {
            // Clear pagination
            this.$logsPagination.empty();
            
            if (data.total_pages <= 1) {
                return;
            }
            
            // Previous button
            var $prevItem = $('<li class="fim-page-item"></li>');
            var $prevLink = $('<a href="#" class="fim-page-link" data-page="' + (data.current_page - 1) + '">Previous</a>');
            
            if (data.current_page === 1) {
                $prevItem.addClass('disabled');
                $prevLink.attr('tabindex', '-1');
            }
            
            $prevItem.append($prevLink);
            this.$logsPagination.append($prevItem);
            
            // Page numbers
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
            
            // Next button
            var $nextItem = $('<li class="fim-page-item"></li>');
            var $nextLink = $('<a href="#" class="fim-page-link" data-page="' + (data.current_page + 1) + '">Next</a>');
            
            if (data.current_page === data.total_pages) {
                $nextItem.addClass('disabled');
                $nextLink.attr('tabindex', '-1');
            }
            
            $nextItem.append($nextLink);
            this.$logsPagination.append($nextItem);
        },
        
        /**
         * Handle logs pagination click
         */
        handleLogsPaginationClick: function(e) {
            e.preventDefault();
            
            var $link = $(e.currentTarget);
            var page = parseInt($link.data('page'), 10);
            
            if ($link.parent().hasClass('disabled')) {
                return;
            }
            
            this.loadLogs(page);
            
            // Scroll to top of logs
            $('html, body').animate({
                scrollTop: this.$logsTable.offset().top - 50
            }, 500);
        },
        
        /**
         * Handle logs filter submit
         */
        handleLogsFilterSubmit: function(e) {
            e.preventDefault();
            
            this.loadLogs(1);
        },
        
        /**
         * Open modal
         */
        openModal: function($modal) {
            this.$modalBackdrop.addClass('show');
            $modal.addClass('show');
        },
        
        /**
         * Close modal
         */
        closeModal: function() {
            this.$modalBackdrop.removeClass('show');
            this.$modals.removeClass('show');
        },
        
        /**
         * Show toast notification
         */
        showToast: function(type, title, message) {
            // Generate random ID
            var toastId = 'fim-toast-' + Math.floor(Math.random() * 10000);
            
            // Create toast HTML
            var $toast = $('<div id="' + toastId + '" class="fim-toast fim-toast-' + type + '"></div>');
            
            // Toast icon
            var iconClass = 'dashicons ';
            switch (type) {
                case 'success':
                    iconClass += 'dashicons-yes-alt';
                    break;
                case 'error':
                    iconClass += 'dashicons-dismiss';
                    break;
                case 'warning':
                    iconClass += 'dashicons-warning';
                    break;
                case 'info':
                    iconClass += 'dashicons-info';
                    break;
            }
            
            var $icon = $('<div class="fim-toast-icon"><span class="' + iconClass + '"></span></div>');
            $toast.append($icon);
            
            // Toast content
            var $content = $('<div class="fim-toast-content"></div>');
            $content.append('<div class="fim-toast-title">' + title + '</div>');
            $content.append('<div class="fim-toast-message">' + message + '</div>');
            $toast.append($content);
            
            // Close button
            var $close = $('<button type="button" class="fim-toast-close">&times;</button>');
            $close.on('click', function() {
                FIMAdmin.hideToast(toastId);
            });
            $toast.append($close);
            
            // Add toast to container
            this.$toastContainer.append($toast);
            
            // Auto hide after 5 seconds
            setTimeout(function() {
                FIMAdmin.hideToast(toastId);
            }, 5000);
        },
        
        /**
         * Hide toast notification
         */
        hideToast: function(toastId) {
            var $toast = $('#' + toastId);
            
            if ($toast.length) {
                $toast.addClass('fim-toast-out');
                
                setTimeout(function() {
                    $toast.remove();
                }, 500);
            }
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        FIMAdmin.init();
    });
    
})(jQuery);