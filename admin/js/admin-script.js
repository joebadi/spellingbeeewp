/**
 * Spelling Bee Pro - Admin JavaScript
 */

(function($) {
    'use strict';

    // Wait for document ready
    $(document).ready(function() {
        // Initialize admin functionality
        OSB_Admin.init();
    });

    var OSB_Admin = {

        /**
         * Initialize admin functionality
         */
        init: function() {
            this.bindEvents();
            this.initModals();
            this.initFilters();
            this.initTables();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            var self = this;

            // Form validation
            $('form[id*="osb-"]').on('submit', function(e) {
                if (!self.validateForm($(this))) {
                    e.preventDefault();
                }
            });

        // Confirm delete actions
        $('.delete-item, .button-link-delete').on('click', function(e) {
            if (!confirm(osb_admin.strings.confirm_delete)) {
                e.preventDefault();
            }
        });

        // Select all checkboxes
        $('[id*="select-all"]').on('change', function() {
            var target = $(this).data('target') || '.item-checkbox';
            $(target).prop('checked', this.checked);
        });

        // Individual checkbox change
        $('.item-checkbox').on('change', function() {
            var selectAllId = $(this).data('select-all') || '#select-all';
            if (!this.checked) {
                $(selectAllId).prop('checked', false);
            } else if ($('.item-checkbox:checked').length === $('.item-checkbox').length) {
                $(selectAllId).prop('checked', true);
            }
        });
    },

    /**
     * Initialize modal functionality
     */
    initModals: function() {
        // Modal close handlers
        $(document).on('click', '.osb-modal-close, .osb-modal', function(e) {
            if (e.target === this) {
                $(this).closest('.osb-modal').hide();
            }
        });

        // Escape key to close modals
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC key
                $('.osb-modal:visible').hide();
            }
        });
    },

    /**
     * Initialize filter functionality
     */
    initFilters: function() {
        // Auto-filter on change
        $('[id*="filter"]').on('change keyup', function() {
            var filterType = $(this).attr('id').replace('-filter', '');
            OSB_Admin.applyFilters();
        });
    },

    /**
     * Initialize table functionality
     */
    initTables: function() {
        // Sortable table headers
        $('.sortable').on('click', function() {
            var $this = $(this);
            var $table = $this.closest('table');
            var column = $this.data('sort');
            var currentOrder = $this.data('order') || 'asc';
            var newOrder = currentOrder === 'asc' ? 'desc' : 'asc';

            // Remove all sorting classes
            $table.find('.sortable').removeClass('sorted-asc sorted-desc');

            // Add sorting class to current column
            $this.addClass('sorted-' + newOrder).data('order', newOrder);

            // Sort table rows
            OSB_Admin.sortTable($table, column, newOrder);
        });
    },

    /**
     * Validate form before submission
     */
    validateForm: function($form) {
        var isValid = true;
        var $requiredFields = $form.find('[required]');

        $requiredFields.each(function() {
            var $field = $(this);
            var value = $field.val().trim();

            if (!value) {
                $field.addClass('error').css('border-color', '#dc3232');
                isValid = false;
            } else {
                $field.removeClass('error').css('border-color', '');
            }
        });

        if (!isValid) {
            this.showNotice('Please fill in all required fields.', 'error');
        }

        return isValid;
    },

    /**
     * Apply table filters
     */
    applyFilters: function() {
        var filters = {};

        // Collect all filter values
        $('[id*="filter"]').each(function() {
            var $this = $(this);
            var filterName = $this.attr('id').replace('-filter', '');
            var value = $this.val().toLowerCase();

            if (value) {
                filters[filterName] = value;
            }
        });

        // Apply filters to table rows
        $('.wp-list-table tbody tr').each(function() {
            var $row = $(this);
            var show = true;

            for (var filterName in filters) {
                var filterValue = filters[filterName];
                var rowData = '';

                // Get row data based on filter type
                if (filterName === 'search') {
                    rowData = $row.text().toLowerCase();
                } else {
                    // Look for data attribute or specific class
                    if ($row.data(filterName)) {
                        rowData = $row.data(filterName).toString().toLowerCase();
                    } else if ($row.hasClass(filterName + '-' + filterValue)) {
                        rowData = filterValue;
                    }
                }

                if (filterValue && !rowData.includes(filterValue)) {
                    show = false;
                    break;
                }
            }

            $row.toggle(show);
        });
    },

    /**
     * Sort table by column
     */
    sortTable: function($table, column, order) {
        var $tbody = $table.find('tbody');
        var $rows = $tbody.find('tr').get();

        $rows.sort(function(a, b) {
            var aValue = $(a).find('[data-sort="' + column + '"]').text();
            var bValue = $(b).find('[data-sort="' + column + '"]').text();

            // Try to parse as numbers
            var aNum = parseFloat(aValue);
            var bNum = parseFloat(bValue);

            if (!isNaN(aNum) && !isNaN(bNum)) {
                return order === 'asc' ? aNum - bNum : bNum - aNum;
            }

            // Sort as strings
            aValue = aValue.toLowerCase();
            bValue = bValue.toLowerCase();

            if (order === 'asc') {
                return aValue < bValue ? -1 : aValue > bValue ? 1 : 0;
            } else {
                return aValue > bValue ? -1 : aValue < bValue ? 1 : 0;
            }
        });

        $.each($rows, function(index, row) {
            $tbody.append(row);
        });
    },

    /**
     * Show admin notice
     */
    showNotice: function(message, type) {
        type = type || 'info';

        var $notice = $('<div class="notice notice-' + type + ' is-dismissible osb-notice">')
            .append('<p>' + message + '</p>')
            .append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');

        $('.wrap h1').after($notice);

        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut();
        }, 5000);

        // Manual dismiss
        $notice.find('.notice-dismiss').on('click', function() {
            $notice.fadeOut();
        });
    },

    /**
     * Show loading state
     */
    showLoading: function($element) {
        $element = $element || $('body');
        $element.addClass('osb-loading');
    },

    /**
     * Hide loading state
     */
    hideLoading: function($element) {
        $element = $element || $('body');
        $element.removeClass('osb-loading');
    },

    /**
     * Make AJAX request
     */
    ajaxRequest: function(action, data, callback) {
        var self = this;

        data = data || {};
        data.action = 'osb_admin_action';
        data.sub_action = action;
        data.nonce = osb_admin.nonce;

        $.ajax({
            url: osb_admin.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function() {
                self.showLoading();
            },
            success: function(response) {
                self.hideLoading();

                if (response.success) {
                    if (callback) {
                        callback(response.data);
                    }
                    self.showNotice(response.data.message || osb_admin.strings.success_message, 'success');
                } else {
                    self.showNotice(response.data.message || osb_admin.strings.error_message, 'error');
                }
            },
            error: function() {
                self.hideLoading();
                self.showNotice(osb_admin.strings.error_message, 'error');
            }
        });
    },

    /**
     * Clear all filters
     */
    clearFilters: function() {
        $('[id*="filter"]').val('');
        this.applyFilters();
    },

    /**
     * Export data
     */
    exportData: function(type, eventId) {
        var url = osb_admin.ajax_url +
                  '?action=osb_export_data' +
                  '&type=' + type +
                  '&event_id=' + (eventId || '') +
                  '&_wpnonce=' + osb_admin.nonce;

        window.open(url, '_blank');
    },

    /**
     * Bulk action handler
     */
    bulkAction: function(action, items, callback) {
        if (!items || items.length === 0) {
            this.showNotice('Please select items to perform bulk action.', 'warning');
            return;
        }

        if (!confirm('Are you sure you want to perform this action on ' + items.length + ' item(s)?')) {
            return;
        }

        this.ajaxRequest('bulk_' + action, {
            items: items
        }, callback);
    }
    };

    // Make OSB_Admin global for template access
    window.OSB_Admin = OSB_Admin;

    // Global functions for template use
    window.filterByEvent = function(eventId) {
        var url = new URL(window.location);
        if (eventId) {
            url.searchParams.set('event_id', eventId);
        } else {
            url.searchParams.delete('event_id');
        }
        window.location.href = url.toString();
    };

    window.clearFilters = function() {
        OSB_Admin.clearFilters();
    };

    // Add loading CSS
    $(function() {
        $('<style>')
            .prop('type', 'text/css')
            .html(
                '.osb-loading {' +
                    'position: relative;' +
                    'pointer-events: none;' +
                    'opacity: 0.6;' +
                '}' +
                '.osb-loading::after {' +
                    'content: "";' +
                    'position: absolute;' +
                    'top: 50%;' +
                    'left: 50%;' +
                    'width: 20px;' +
                    'height: 20px;' +
                    'margin: -10px 0 0 -10px;' +
                    'border: 2px solid #ccc;' +
                    'border-top-color: #0073aa;' +
                    'border-radius: 50%;' +
                    'animation: spin 1s linear infinite;' +
                    'z-index: 1000;' +
                '}' +
                '@keyframes spin {' +
                    'to { transform: rotate(360deg); }' +
                '}' +
                '.error {' +
                    'border-color: #dc3232 !important;' +
                    'box-shadow: 0 0 2px rgba(220, 50, 50, 0.8);' +
                '}'
            )
            .appendTo('head');
    });

})(jQuery);