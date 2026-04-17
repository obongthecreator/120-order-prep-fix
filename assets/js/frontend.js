/**
 * Frontend JavaScript for Fruit Inventory Manager
 * Clean rebuild - no animations
 */

(function($) {
    'use strict';

    var FIM = {
        
        init: function() {
            this.cacheDom();
            this.bindEvents();
            this.initializeDatepicker();
            this.initializeForm();
            
            if (this.$recordsContainer.length) {
                this.loadRecords(1);
            }
        },
        
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
        
        bindEvents: function() {
            this.$inventoryForm.on('submit', this.handleFormSubmit.bind(this));
            this.$productsTable.on('input', '.fim-total-added, .fim-total-sold', this.calculateClosing.bind(this));
            
            if (fim_params.is_admin === '1') {
                this.$productsTable.on('input', '.fim-opening', this.calculateClosing.bind(this));
            }
            
            this.$dateField.on('change', this.handleDateChange.bind(this));
            this.$filterForm.on('submit', this.handleFilterSubmit.bind(this));
            this.$resetFilterButton.on('click', this.resetFilters.bind(this));
            this.$pagination.on('click', '.fim-page-link', this.handlePaginationClick.bind(this));
        },
        
        initializeDatepicker: function() {
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
        
        initializeForm: function() {
            if (this.$inventoryForm.length) {
                this.getOpeningValues();
            }
        },
        
        handleDateChange: function() {
            this.getOpeningValues();
        },
        
        getOpeningValues: function() {
            var date = this.$dateField.val();
            var isAdmin = (fim_params.is_admin === '1');
            
            this.showToast('info', 'Loading', 'Fetching values for ' + date);
            
            var totalProducts = this.$productRows.length;
            var processedProducts = 0;
            
            this.$productRows.each(function() {
                var $row = $(this);
                var productId = $row.data('product-id');
                var $openingField = $row.find('.fim-opening');
                var $closingField = $row.find('.fim-closing');
                
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
                            $openingField.val(parseFloat(response.data.opening_value).toFixed(2));
                            
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
                                        var record = recordResponse.data.record;
                                        $openingField.val(parseFloat(record.opening).toFixed(2));
                                        $row.find('.fim-total-added').val(parseFloat(record.total_added).toFixed(2));
                                        $row.find('.fim-total-sold').val(parseFloat(record.total_sold).toFixed(2));
                                        $closingField.val(parseFloat(record.closing).toFixed(2));
                                    } else {
                                        if (isAdmin && ($closingField.val() == "0.00" || $closingField.val() == "0")) {
                                            $closingField.val($openingField.val());
                                        }
                                        FIM.calculateClosingForRow($row);
                                    }
                                },
                                error: function() {
                                    if (isAdmin && ($closingField.val() == "0.00" || $closingField.val() == "0")) {
                                        $closingField.val($openingField.val());
                                    }
                                    FIM.calculateClosingForRow($row);
                                }
                            });
                        } else {
                            FIM.showToast('error', 'Error', response.data.message);
                        }
                        
                        processedProducts++;
                        if (processedProducts === totalProducts) {
                            FIM.showToast('success', 'Ready', 'Values loaded successfully');
                        }
                    },
                    error: function() {
                        FIM.showToast('error', 'Error', 'Failed to get opening value for product ID ' + productId);
                        processedProducts++;
                    }
                });
            });
        },
        
        calculateClosingForRow: function($row) {
            var opening = parseFloat($row.find('.fim-opening').val()) || 0;
            var totalAdded = parseFloat($row.find('.fim-total-added').val()) || 0;
            var totalSold = parseFloat($row.find('.fim-total-sold').val()) || 0;
            
            var closing = opening + totalAdded - totalSold;
            $row.find('.fim-closing').val(closing.toFixed(2));
        },
        
        calculateClosing: function(e) {
            var $input = $(e.target);
            var $row = $input.closest('tr');
            this.calculateClosingForRow($row);
        },
        
        handleFormSubmit: function(e) {
            e.preventDefault();
            
            if (!this.validateForm()) {
                return;
            }
            
            var isAdmin = (fim_params.is_admin === '1');
            
            var formData = {
                date: this.$dateField.val(),
                remarks: this.$remarksField.val(),
                products: {}
            };
            
            var productsWithData = [];
            
            this.$productRows.each(function() {
                var $row = $(this);
                var productId = $row.data('product-id');
                var productName = $row.find('td:first-child').text();
                
                var opening = parseFloat($row.find('.fim-opening').val()) || 0;
                var totalAdded = parseFloat($row.find('.fim-total-added').val()) || 0;
                var totalSold = parseFloat($row.find('.fim-total-sold').val()) || 0;
                var closing = parseFloat($row.find('.fim-closing').val()) || 0;
                
                if (isAdmin && (opening > 0 || totalAdded > 0 || totalSold > 0 || closing > 0)) {
                    formData.products[productId] = {
                        opening: opening,
                        total_added: totalAdded,
                        total_sold: totalSold,
                        closing: closing
                    };
                    productsWithData.push({ id: productId, name: productName });
                } else if (!isAdmin && (totalAdded > 0 || totalSold > 0)) {
                    formData.products[productId] = {
                        opening: opening,
                        total_added: totalAdded,
                        total_sold: totalSold
                    };
                    productsWithData.push({ id: productId, name: productName });
                }
            });
            
            if (Object.keys(formData.products).length === 0) {
                this.showToast('error', 'Error', 'Please enter data for at least one product');
                return;
            }
            
            this.$submitButton.addClass('fim-btn-loading').prop('disabled', true);
            
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
                    FIM.$submitButton.removeClass('fim-btn-loading').prop('disabled', false);
                    
                    if (response.success) {
                        if (response.data && response.data.updated_records) {
                            FIM.processServerResponse(response);
                        } else {
                            FIM.updateFormWithCalculatedValues();
                        }
                        
                        FIM.showToast('success', 'Success', response.data.message);
                        
                        if (!isAdmin) {
                            FIM.$remarksField.val('');
                            $.each(productsWithData, function(index, product) {
                                var $row = FIM.$productsTable.find('tr[data-product-id="' + product.id + '"]');
                                $row.find('.fim-total-added, .fim-total-sold').val('');
                            });
                        }
                        
                        var today = new Date();
                        var formDate = new Date(FIM.$dateField.val());
                        if (formDate.toDateString() !== today.toDateString()) {
                            FIM.showToast('info', 'Reminder', 'Your data has been saved for ' + 
                                FIM.$dateField.val() + '. Switch to today\'s date to enter current inventory.');
                        }
                    } else {
                        FIM.showToast('error', 'Error', response.data.message);
                    }
                },
                error: function() {
                    FIM.$submitButton.removeClass('fim-btn-loading').prop('disabled', false);
                    FIM.showToast('error', 'Error', 'Failed to submit form');
                }
            });
        },
        
        processServerResponse: function(response) {
            if (!response || !response.data || !response.data.updated_records) {
                return;
            }
            
            var updatedRecords = response.data.updated_records;
            var updatedProducts = [];
            
            this.$productRows.each(function() {
                var $row = $(this);
                var productId = $row.data('product-id');
                
                if (updatedRecords[productId]) {
                    var record = updatedRecords[productId];
                    $row.find('.fim-opening').val(parseFloat(record.opening).toFixed(2));
                    $row.find('.fim-total-added').val(parseFloat(record.total_added).toFixed(2));
                    $row.find('.fim-total-sold').val(parseFloat(record.total_sold).toFixed(2));
                    $row.find('.fim-closing').val(parseFloat(record.closing).toFixed(2));
                    updatedProducts.push(productId);
                }
            });
            
            if (updatedProducts.length > 0) {
                this.showToast('success', 'Success', 'Updated ' + updatedProducts.length + ' products with calculated values');
            }
        },
        
        updateFormWithCalculatedValues: function() {
            this.$productRows.each(function() {
                var $row = $(this);
                var opening = parseFloat($row.find('.fim-opening').val()) || 0;
                var totalAdded = parseFloat($row.find('.fim-total-added').val()) || 0;
                var totalSold = parseFloat($row.find('.fim-total-sold').val()) || 0;
                var closing = opening + totalAdded - totalSold;
                $row.find('.fim-closing').val(closing.toFixed(2));
            });
        },
        
        validateForm: function() {
            if (!this.$dateField.val()) {
                this.showToast('error', 'Error', 'Please select a date');
                this.$dateField.focus();
                return false;
            }
            return true;
        },
        
        loadRecords: function(page) {
            this.$recordsTableBody.html('<tr><td colspan="9" class="fim-loader-container"><div class="fim-loader"></div><span class="fim-loader-text">Loading records...</span></td></tr>');
            
            var productId = this.$productFilter.val();
            var dateFrom = this.$dateFromFilter.val();
            var dateTo = this.$dateToFilter.val();
            
            $.ajax({
                url: fim_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fim_load_records',
                    nonce: fim_params.nonce,
                    page: page,
                    per_page: 20,
                    product_id: productId,
                    date_from: dateFrom,
                    date_to: dateTo
                },
                success: function(response) {
                    if (response.success) {
                        FIM.renderRecords(response.data.records);
                        FIM.renderPagination(response.data);
                    } else {
                        FIM.showToast('error', 'Error', response.data.message);
                        FIM.$recordsTableBody.html('<tr><td colspan="9" class="text-center">No records found</td></tr>');
                    }
                },
                error: function() {
                    FIM.showToast('error', 'Error', 'Failed to load records');
                    FIM.$recordsTableBody.html('<tr><td colspan="9" class="text-center">Error loading records</td></tr>');
                }
            });
        },
        
        renderRecords: function(records) {
            this.$recordsTableBody.empty();
            
            if (records.length === 0) {
                this.$recordsTableBody.html('<tr><td colspan="9" class="text-center">No records found</td></tr>');
                return;
            }
            
            $.each(records, function(index, record) {
                var $row = $('<tr></tr>');
                
                $row.append('<td>' + record.id + '</td>');
                $row.append('<td>' + record.product_name + '</td>');
                $row.append('<td>' + record.staff_name + '</td>');
                $row.append('<td>' + record.date + '</td>');
                $row.append('<td>' + record.opening + '</td>');
                $row.append('<td>' + record.total_added + '</td>');
                $row.append('<td>' + record.total_sold + '</td>');
                $row.append('<td>' + record.closing + '</td>');
                $row.append('<td>' + (record.remarks || '-') + '</td>');
                
                FIM.$recordsTableBody.append($row);
            });
        },
        
        renderPagination: function(data) {
            this.$pagination.empty();
            
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
            this.$pagination.append($prevItem);
            
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
            
            var $nextItem = $('<li class="fim-page-item"></li>');
            var $nextLink = $('<a href="#" class="fim-page-link" data-page="' + (data.current_page + 1) + '">Next</a>');
            
            if (data.current_page === data.total_pages) {
                $nextItem.addClass('disabled');
                $nextLink.attr('tabindex', '-1');
            }
            
            $nextItem.append($nextLink);
            this.$pagination.append($nextItem);
        },
        
        handlePaginationClick: function(e) {
            e.preventDefault();
            
            var $link = $(e.currentTarget);
            var page = parseInt($link.data('page'), 10);
            
            if ($link.parent().hasClass('disabled')) {
                return;
            }
            
            this.loadRecords(page);
            
            $('html, body').scrollTop(this.$recordsContainer.offset().top - 50);
        },
        
        handleFilterSubmit: function(e) {
            e.preventDefault();
            this.loadRecords(1);
        },
        
        resetFilters: function(e) {
            e.preventDefault();
            this.$productFilter.val('');
            this.$dateFromFilter.val('');
            this.$dateToFilter.val('');
            this.loadRecords(1);
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
                FIM.hideToast(toastId);
            });
            $toast.append($close);
            
            this.$toastContainer.append($toast);
            
            setTimeout(function() {
                FIM.hideToast(toastId);
            }, 3000);
        },
        
        hideToast: function(toastId) {
            var $toast = $('#' + toastId);
            if ($toast.length) {
                $toast.remove();
            }
        }
    };
    
    $(document).ready(function() {
        FIM.init();
    });
    
})(jQuery);
