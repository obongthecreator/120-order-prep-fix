/**
 * Frontend JavaScript for Fruit Inventory Manager
 */

(function($) {
    'use strict';

    // Main FIM object
    var FIM = {
        
        /**
         * Initialize the app
         */
        init: function() {
            this.cacheDom();
            this.bindEvents();
            this.initializeDatepicker();
            this.initializeForm();
            
            // If on records page, load the records
            if (this.$recordsContainer.length) {
                this.loadRecords(1);
            }
        },
        
        /**
         * Cache DOM elements
         */
        cacheDom: function() {
            this.$document = $(document);
            this.$window = $(window);
            this.$body = $('body');
            
            // Form elements
            this.$inventoryForm = $('#fim-inventory-form');
            this.$productsTable = $('#fim-products-table');
            this.$productRows = this.$productsTable.find('tbody tr');
            this.$dateField = $('#fim-date');
            this.$staffIdField = $('#fim-staff-id');
            this.$staffNameField = $('#fim-staff-name');
            this.$remarksField = $('#fim-remarks');
            this.$submitButton = $('#fim-submit-button');
            
            // Records elements
            this.$recordsContainer = $('#fim-records-container');
            this.$recordsTable = $('#fim-records-table');
            this.$recordsTableBody = this.$recordsTable.find('tbody');
            this.$pagination = $('#fim-pagination');
            this.$filterForm = $('#fim-filter-form');
            this.$productFilter = $('#fim-product-filter');
            this.$dateFromFilter = $('#fim-date-from-filter');
            this.$dateToFilter = $('#fim-date-to-filter');
            this.$filterButton = $('#fim-filter-button');
            this.$resetFilterButton = $('#fim-reset-filter-button');
            
            // Toast notification
            this.$toastContainer = $('#fim-toast-container');
            if (!this.$toastContainer.length) {
                this.$toastContainer = $('<div id="fim-toast-container"></div>').appendTo(this.$body);
            }
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            // Form submission
            this.$inventoryForm.on('submit', this.handleFormSubmit.bind(this));
            
            // Input calculation - THIS IS THE CRITICAL PART
            this.$productsTable.on('input', '.fim-total-added, .fim-total-sold', this.calculateClosing.bind(this));
            
            // For admin, also listen to changes on opening values
            if (fim_params.is_admin === '1') {
                this.$productsTable.on('input', '.fim-opening', this.calculateClosing.bind(this));
            }
            
            // Date change
            this.$dateField.on('change', this.handleDateChange.bind(this));
            
            // Filter form
            this.$filterForm.on('submit', this.handleFilterSubmit.bind(this));
            this.$resetFilterButton.on('click', this.resetFilters.bind(this));
            
            // Pagination
            this.$pagination.on('click', '.fim-page-link', this.handlePaginationClick.bind(this));
        },
        
        /**
         * Initialize datepicker
         */
        initializeDatepicker: function() {
            // If jQuery UI datepicker is available, use it
            if ($.fn.datepicker) {
                this.$dateField.datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    maxDate: '0'
                });
                
                this.$dateFromFilter.datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    maxDate: '0',
                    onSelect: function(selectedDate) {
                        FIM.$dateToFilter.datepicker('option', 'minDate', selectedDate);
                    }
                });
                
                this.$dateToFilter.datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    maxDate: '0',
                    onSelect: function(selectedDate) {
                        FIM.$dateFromFilter.datepicker('option', 'maxDate', selectedDate);
                    }
                });
            }
        },
        
        /**
         * Initialize form
         */
        initializeForm: function() {
            // Get opening values for each product
            if (this.$inventoryForm.length) {
                this.getOpeningValues();
            }
        },
        
        /**
         * Handle date change
         */
        handleDateChange: function() {
            // Get opening values when date changes
            this.getOpeningValues();
        },
        
        /**
         * Get opening values for products with improved persistence
         */
        getOpeningValues: function() {
            var date = this.$dateField.val();
            var isAdmin = (fim_params.is_admin === '1');
            
            // Show loading message
            this.showToast('info', 'Loading', 'Fetching values for ' + date);
            
            // Count how many products we need to process
            var totalProducts = this.$productRows.length;
            var processedProducts = 0;
            
            this.$productRows.each(function() {
                var $row = $(this);
                var productId = $row.data('product-id');
                var $openingField = $row.find('.fim-opening');
                var $closingField = $row.find('.fim-closing');
                
                // Display loading spinner
                $openingField.addClass('loading');
                
                // Get opening value from server
                $.ajax({
                    url: fim_params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'fim_get_opening_value',
                        nonce: fim_params.nonce,
                        product_id: productId,
                        date: date
                    },
                    success: function(response) {
                        if (response.success) {
                            // Set opening value from server response
                            $openingField.val(parseFloat(response.data.opening_value).toFixed(2));
                            
                            // After setting opening value, also check if we need to get the full inventory record
                            // to get the actual saved values for this date
                            $.ajax({
                                url: fim_params.ajax_url,
                                type: 'POST',
                                data: {
                                    action: 'fim_get_inventory_record',
                                    nonce: fim_params.nonce,
                                    product_id: productId,
                                    date: date
                                },
                                success: function(recordResponse) {
                                    if (recordResponse.success && recordResponse.data.record) {
                                        // We have a saved record for this date, use those values
                                        var record = recordResponse.data.record;
                                        
                                        // Update all fields with saved values
                                        $openingField.val(parseFloat(record.opening).toFixed(2));
                                        $row.find('.fim-total-added').val(parseFloat(record.total_added).toFixed(2));
                                        $row.find('.fim-total-sold').val(parseFloat(record.total_sold).toFixed(2));
                                        $closingField.val(parseFloat(record.closing).toFixed(2));
                                        
                                        // Values loaded for this row
                                    } else {
                                        // No saved record found, use opening value from previous day
                                        // and for admins, set closing to match opening initially
                                        if (isAdmin && ($closingField.val() == "0.00" || $closingField.val() == "0")) {
                                            $closingField.val($openingField.val());
                                        }
                                        
                                        // Calculate closing value
                                        FIM.calculateClosingForRow($row);
                                    }
                                },
                                error: function() {
                                    // If getting full record fails, still use the opening value
                                    if (isAdmin && ($closingField.val() == "0.00" || $closingField.val() == "0")) {
                                        $closingField.val($openingField.val());
                                    }
                                    FIM.calculateClosingForRow($row);
                                }
                            });
                        } else {
                            FIM.showToast('error', 'Error', response.data.message);
                        }
                        
                        // Remove loading spinner
                        $openingField.removeClass('loading');
                        
                        // Update progress
                        processedProducts++;
                        if (processedProducts === totalProducts) {
                            // All products are loaded
                            FIM.showToast('success', 'Ready', 'Values loaded successfully');
                        }
                    },
                    error: function() {
                        FIM.showToast('error', 'Error', 'Failed to get opening value for product ID ' + productId);
                        $openingField.removeClass('loading');
                        
                        // Update progress even on error
                        processedProducts++;
                    }
                });
            });
        },
        
        /**
         * Calculate closing value for a product row
         */
        calculateClosingForRow: function($row) {
            var opening = parseFloat($row.find('.fim-opening').val()) || 0;
            var totalAdded = parseFloat($row.find('.fim-total-added').val()) || 0;
            var totalSold = parseFloat($row.find('.fim-total-sold').val()) || 0;
            
            var closing = opening + totalAdded - totalSold;
            
            // Set the closing value
            $row.find('.fim-closing').val(closing.toFixed(2));
        },
        
        /**
         * Calculate closing for the changed row
         */
        calculateClosing: function(e) {
            var $input = $(e.target);
            var $row = $input.closest('tr');
            
            this.calculateClosingForRow($row);
        },
        
        /**
         * Handle form submission
         */
        handleFormSubmit: function(e) {
            e.preventDefault();
            
            // Validate form
            if (!this.validateForm()) {
                return;
            }
            
            // Check if current user is admin
            var isAdmin = (fim_params.is_admin === '1');
            
            // Collect data
            var formData = {
                date: this.$dateField.val(),
                remarks: this.$remarksField.val(),
                products: {}
            };
            
            // Track which products have data entered
            var productsWithData = [];
            
            // Collect products data
            this.$productRows.each(function() {
                var $row = $(this);
                var productId = $row.data('product-id');
                var productName = $row.find('td:first-child').text();
                
                var opening = parseFloat($row.find('.fim-opening').val()) || 0;
                var totalAdded = parseFloat($row.find('.fim-total-added').val()) || 0;
                var totalSold = parseFloat($row.find('.fim-total-sold').val()) || 0;
                var closing = parseFloat($row.find('.fim-closing').val()) || 0;
                
                // For admin, include products with any non-zero values
                if (isAdmin && (opening > 0 || totalAdded > 0 || totalSold > 0 || closing > 0)) {
                    formData.products[productId] = {
                        opening: opening,
                        total_added: totalAdded,
                        total_sold: totalSold,
                        closing: closing // Include closing value for admin
                    };
                    
                    productsWithData.push({
                        id: productId,
                        name: productName
                    });
                } 
                // For regular staff, only include products with added/sold data
                else if (!isAdmin && (totalAdded > 0 || totalSold > 0)) {
                    formData.products[productId] = {
                        opening: opening,
                        total_added: totalAdded,
                        total_sold: totalSold
                        // Closing will be calculated server-side
                    };
                    
                    productsWithData.push({
                        id: productId,
                        name: productName
                    });
                }
            });
            
            // Ensure at least one product has data
            if (Object.keys(formData.products).length === 0) {
                this.showToast('error', 'Error', 'Please enter data for at least one product');
                return;
            }
            
            // Show loading state
            this.$submitButton.addClass('fim-btn-loading').prop('disabled', true);
            
            // Submit form
            $.ajax({
                url: fim_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_submit_inventory',
                    nonce: fim_params.nonce,
                    date: formData.date,
                    remarks: formData.remarks,
                    products: formData.products
                },
                success: function(response) {
                    // Hide loading state
                    FIM.$submitButton.removeClass('fim-btn-loading').prop('disabled', false);
                    
                    if (response.success) {
                        // Process the server response to update the form
                        if (response.data && response.data.updated_records) {
                            // Update form with server-calculated values
                            FIM.processServerResponse(response);
                        } else {
                            // Fallback if server doesn't return calculated values
                            FIM.updateFormWithCalculatedValues();
                        }
                        
                        // Show success message
                        FIM.showToast('success', 'Success', response.data.message);
                        
                        // For admin, don't clear inputs since they might be setting baseline values
                        if (!isAdmin) {
                            // Clear only remarks and input fields for products that were submitted
                            FIM.$remarksField.val('');
                            
                            $.each(productsWithData, function(index, product) {
                                var $row = FIM.$productsTable.find('tr[data-product-id="' + product.id + '"]');
                                $row.find('.fim-total-added, .fim-total-sold').val('');
                            });
                        }
                        
                        // Check if we need to update the date
                        var today = new Date();
                        var formDate = new Date(FIM.$dateField.val());
                        
                        // If user is working on today's date, we don't need to remind them
                        if (formDate.toDateString() !== today.toDateString()) {
                            FIM.showToast('info', 'Reminder', 'Your data has been saved for ' + 
                                FIM.$dateField.val() + '. Switch to today\'s date to enter current inventory.');
                        }
                    } else {
                        // Show error message
                        FIM.showToast('error', 'Error', response.data.message);
                    }
                },
                error: function() {
                    // Hide loading state
                    FIM.$submitButton.removeClass('fim-btn-loading').prop('disabled', false);
                    
                    // Show error message
                    FIM.showToast('error', 'Error', 'Failed to submit form');
                }
            });
        },
        
        /**
         * Process server response with updated records
         * This function updates the form with values from the server response
         */
        processServerResponse: function(response) {
            if (!response || !response.data || !response.data.updated_records) {
                return;
            }
            
            var updatedRecords = response.data.updated_records;
            var updatedProducts = [];
            
            // Update each product row with the values from server
            this.$productRows.each(function() {
                var $row = $(this);
                var productId = $row.data('product-id');
                
                if (updatedRecords[productId]) {
                    var record = updatedRecords[productId];
                    
                    // Update the values
                    $row.find('.fim-opening').val(parseFloat(record.opening).toFixed(2));
                    $row.find('.fim-total-added').val(parseFloat(record.total_added).toFixed(2));
                    $row.find('.fim-total-sold').val(parseFloat(record.total_sold).toFixed(2));
                    $row.find('.fim-closing').val(parseFloat(record.closing).toFixed(2));
                    
                    // Add to the list of updated products
                    updatedProducts.push(productId);
                }
            });
            
            // Show a message about which products were updated
            if (updatedProducts.length > 0) {
                this.showToast('success', 'Success', 'Updated ' + updatedProducts.length + ' products with calculated values');
            }
        },
        
        /**
         * Update form with calculated values
         * This preserves the calculated values in the UI
         */
        updateFormWithCalculatedValues: function() {
            this.$productRows.each(function() {
                var $row = $(this);
                var opening = parseFloat($row.find('.fim-opening').val()) || 0;
                var totalAdded = parseFloat($row.find('.fim-total-added').val()) || 0;
                var totalSold = parseFloat($row.find('.fim-total-sold').val()) || 0;
                
                // Calculate closing value
                var closing = opening + totalAdded - totalSold;
                
                // Update the closing field
                $row.find('.fim-closing').val(closing.toFixed(2));
            });
        },
        
        /**
         * Validate form
         */
        validateForm: function() {
            var isValid = true;
            
            // Validate date
            if (!this.$dateField.val()) {
                this.showToast('error', 'Error', 'Please select a date');
                this.$dateField.focus();
                return false;
            }
            
            return isValid;
        },
        
        /**
         * Load records
         */
        loadRecords: function(page) {
            // Show loading
            this.$recordsTableBody.html('<tr><td colspan="9" class="fim-loader-container"><div class="fim-loader"></div><span class="fim-loader-text">Loading records...</span></td></tr>');
            
            // Get filter values
            var productId = this.$productFilter.val();
            var dateFrom = this.$dateFromFilter.val();
            var dateTo = this.$dateToFilter.val();
            
            // Get records from server
            $.ajax({
                url: fim_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_load_records',
                    nonce: fim_params.nonce,
                    page: page,
                    per_page: 20, // Adjust as needed
                    product_id: productId,
                    date_from: dateFrom,
                    date_to: dateTo
                },
                success: function(response) {
                    if (response.success) {
                        // Render records
                        FIM.renderRecords(response.data.records);
                        
                        // Render pagination
                        FIM.renderPagination(response.data);
                    } else {
                        // Show error message
                        FIM.showToast('error', 'Error', response.data.message);
                        
                        // Clear table
                        FIM.$recordsTableBody.html('<tr><td colspan="9" class="text-center">No records found</td></tr>');
                    }
                },
                error: function() {
                    // Show error message
                    FIM.showToast('error', 'Error', 'Failed to load records');
                    
                    // Clear table
                    FIM.$recordsTableBody.html('<tr><td colspan="9" class="text-center">Error loading records</td></tr>');
                }
            });
        },
        
        /**
         * Render records
         */
        renderRecords: function(records) {
            // Clear table
            this.$recordsTableBody.empty();
            
            if (records.length === 0) {
                this.$recordsTableBody.html('<tr><td colspan="9" class="text-center">No records found</td></tr>');
                return;
            }
            
            // Add records
            $.each(records, function(index, record) {
                var $row = $('<tr class="hover:tw-bg-red-50 tw-border-b tw-border-gray-200"></tr>');
                
                $row.append('<td class="tw-py-3 tw-px-4">' + record.id + '</td>');
                $row.append('<td class="tw-py-3 tw-px-4">' + record.product_name + '</td>');
                $row.append('<td class="tw-py-3 tw-px-4">' + record.staff_name + '</td>');
                $row.append('<td class="tw-py-3 tw-px-4">' + record.date + '</td>');
                $row.append('<td class="tw-py-3 tw-px-4">' + record.opening + '</td>');
                $row.append('<td class="tw-py-3 tw-px-4">' + record.total_added + '</td>');
                $row.append('<td class="tw-py-3 tw-px-4">' + record.total_sold + '</td>');
                $row.append('<td class="tw-py-3 tw-px-4">' + record.closing + '</td>');
                $row.append('<td class="tw-py-3 tw-px-4">' + (record.remarks || '-') + '</td>');
                
                FIM.$recordsTableBody.append($row);
            });
        },
        
        /**
         * Render pagination
         */
        renderPagination: function(data) {
            // Clear pagination
            this.$pagination.empty();
            
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
            this.$pagination.append($prevItem);
            
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
                this.$pagination.append($pageItem);
            }
            
            // Next button
            var $nextItem = $('<li class="fim-page-item"></li>');
            var $nextLink = $('<a href="#" class="fim-page-link" data-page="' + (data.current_page + 1) + '">Next</a>');
            
            if (data.current_page === data.total_pages) {
                $nextItem.addClass('disabled');
                $nextLink.attr('tabindex', '-1');
            }
            
            $nextItem.append($nextLink);
            this.$pagination.append($nextItem);
        },
        
        /**
         * Handle pagination click
         */
        handlePaginationClick: function(e) {
            e.preventDefault();
            
            var $link = $(e.currentTarget);
            var page = parseInt($link.data('page'), 10);
            
            if ($link.parent().hasClass('disabled')) {
                return;
            }
            
            this.loadRecords(page);
            
            // Scroll to top of records
            $('html, body').animate({
                scrollTop: this.$recordsContainer.offset().top - 50
            }, 500);
        },
        
        /**
         * Handle filter submit
         */
        handleFilterSubmit: function(e) {
            e.preventDefault();
            
            this.loadRecords(1);
        },
        
        /**
         * Reset filters
         */
        resetFilters: function(e) {
            e.preventDefault();
            
            // Reset filter values
            this.$productFilter.val('');
            this.$dateFromFilter.val('');
            this.$dateToFilter.val('');
            
            // Load records
            this.loadRecords(1);
        },
        
        /**
         * Show toast notification with faster timing
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
                FIM.hideToast(toastId);
            });
            $toast.append($close);
            
            // Add toast to container
            this.$toastContainer.append($toast);
            
            // Auto hide after 1 second (reduced from 5 seconds)
            setTimeout(function() {
                FIM.hideToast(toastId);
            }, 1000);
        },
        
        /**
         * Hide toast notification with faster timing
         */
        hideToast: function(toastId) {
            var $toast = $('#' + toastId);
            
            if ($toast.length) {
                $toast.addClass('fim-toast-out');
                
                // Reduced from 500ms to 100ms
                setTimeout(function() {
                    $toast.remove();
                }, 100);
            }
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        FIM.init();
    });
    
})(jQuery);