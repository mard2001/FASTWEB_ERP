let MainTH = null;
let jsonArr = [];
let isloading = false;
let currentEditId = null;

// Global variables
let accountsPayableData = [];
let filteredData = [];
let accountsPayableTable;
let statusFilterVS;
let supplierFilterVS;
let filteredStartDate;
let filteredEndDate;
let suppliersData = [];
let banksData = [];
let gcashData = [];

// Initialize page
$(document).ready(function() {
    initializeFilters();
    initDateRangePicker();
    loadSuppliersData();
    loadAccountsPayableData();
    loadGcashData(); // Load GCash data for payment dropdown
    
    // Initialize number formatting for amount fields
    initializeAmountFormatting();
});

// Load suppliers data for dropdown
function loadSuppliersData() {
    $.ajax({
        url: '/api/accounts-payable/suppliers',
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success && response.data) {
                suppliersData = response.data;
                populateSupplierDropdowns();
            } else {
                console.warn('No supplier data received or invalid response format');
                // Still initialize dropdowns with empty data
                populateSupplierDropdowns();
            }
        },
        error: function(xhr) {
            console.error('Failed to load suppliers data:', xhr);
            // Still initialize dropdowns with empty data to prevent further errors
            suppliersData = [];
            populateSupplierDropdowns();
            
            // Show user-friendly message only if it's a critical error
            if (xhr.status === 401) {
                console.warn('Authentication required for suppliers data');
            } else if (xhr.status >= 500) {
                console.error('Server error loading suppliers data');
            }
        }
    });
}

// Load banks data for payment dropdown
function loadBanksData() {
    $.ajax({
        url: '/api/accounts-payable/banks',
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success && response.data) {
                banksData = response.data;
                populateBankDropdown();
            } else {
                console.warn('No bank data received or invalid response format');
                banksData = [];
            }
        },
        error: function(xhr) {
            console.error('Failed to load banks data:', xhr);
            banksData = [];
            
            if (xhr.status === 401) {
                console.warn('Authentication required for banks data');
            } else if (xhr.status >= 500) {
                console.error('Server error loading banks data');
            }
        }
    });
}

// Load banks data specifically for modal (with better error handling)
function loadBanksDataForModal() {
    $.ajax({
        url: '/api/accounts-payable/banks',
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success && response.data && Array.isArray(response.data)) {
                banksData = response.data;
                populateBankDropdown();
            } else {
                console.warn('No bank data received or invalid response format:', response);
                banksData = [];
                populateBankDropdown();
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to load banks data:', xhr.status, xhr.statusText);
            console.error('Response:', xhr.responseText);
            banksData = [];
            populateBankDropdown(); // Still call to show empty dropdown
            
            // Show user-friendly error for critical failures
            if (xhr.status === 401) {
                console.warn('Authentication required for banks data');
            } else if (xhr.status === 404) {
                console.warn('Banks API endpoint not found');
            } else if (xhr.status >= 500) {
                console.error('Server error loading banks data');
            }
        }
    });
}

// Load GCash data for payment dropdown
function loadGcashData() {
    $.ajax({
        url: '/api/gcash',
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success && response.data) {
                gcashData = response.data;
                populateGcashDropdown();
            } else {
                console.warn('No GCash data received or invalid response format');
                gcashData = [];
            }
        },
        error: function(xhr) {
            console.error('Failed to load GCash data:', xhr);
            gcashData = [];
            
            if (xhr.status === 401) {
                console.warn('Authentication required for GCash data');
            } else if (xhr.status >= 500) {
                console.error('Server error loading GCash data');
            }
        }
    });
}

// Populate supplier dropdowns
function populateSupplierDropdowns() {
    // Check if VirtualSelect is available
    if (typeof VirtualSelect === 'undefined') {
        console.error('VirtualSelect library is not loaded');
        return;
    }

    // For filter dropdown
    let supplierOptions = [{ label: 'All Suppliers', value: '' }];
    suppliersData.forEach(supplier => {
        supplierOptions.push({
            label: `${supplier.SupplierCode} - ${supplier.SupplierName}`,
            value: supplier.SupplierCode
        });
    });

    // Initialize supplier filter if element exists and not already initialized
    const supplierFilterElement = document.querySelector('#supplierFilter_VS');
    if (supplierFilterElement && !supplierFilterVS) {
        try {
            supplierFilterVS = VirtualSelect.init({
                ele: '#supplierFilter_VS',
                options: supplierOptions,
                placeholder: 'Select Supplier',
                search: true,
                multiple: false
            });

            supplierFilterElement.addEventListener('change', function() {
                applyFilters();
            });
        } catch (error) {
            console.error('Error initializing supplier filter:', error);
        }
    }

    // For form dropdown - only initialize if element exists
    const supplierFormElement = document.querySelector('#supplier_code_VS');
    if (supplierFormElement) {
        try {
            let formSupplierOptions = [];
            suppliersData.forEach(supplier => {
                formSupplierOptions.push({
                    label: `${supplier.SupplierCode} - ${supplier.SupplierName}`,
                    value: supplier.SupplierCode,
                    customData: supplier.SupplierName
                });
            });

            VirtualSelect.init({
                ele: '#supplier_code_VS',
                options: formSupplierOptions,
                placeholder: 'Select Supplier',
                search: true,
                multiple: false
            });

            // Add event listener for supplier selection in form
            supplierFormElement.addEventListener('change', function() {
                const selectedValue = this.value;
                const selectedSupplier = suppliersData.find(s => s.SupplierCode === selectedValue);
                if (selectedSupplier) {
                    $('#supplier_name').val(selectedSupplier.SupplierName);
                }
            });
        } catch (error) {
            console.error('Error initializing supplier form dropdown:', error);
        }
    }
}

// Populate bank dropdown for payment modal
function populateBankDropdown() {
    const bankSelect = $('#bank_selection');
    bankSelect.empty().append('<option value="">Choose Bank...</option>');
    
    if (banksData && banksData.length > 0) {
        banksData.forEach(bank => {
            const option = `<option value="${bank.BankID}" data-bank='${JSON.stringify(bank)}'>${bank.BankName}</option>`;
            bankSelect.append(option);
        });
    }
}

// Populate GCash dropdown for payment modal
function populateGcashDropdown() {
    const gcashSelect = $('#gcash_selection');
    gcashSelect.empty().append('<option value="">Choose GCash Account...</option>');
    
    if (gcashData && gcashData.length > 0) {
        gcashData.forEach(gcash => {
            const option = `<option value="${gcash.GcashID}" data-gcash='${JSON.stringify(gcash)}'>${gcash.AccountName}</option>`;
            gcashSelect.append(option);
        });
    }
}

// Initialize Virtual Select filters
function initializeFilters() {
    // Initialize Status filter
    VirtualSelect.init({
        ele: '#statusFilter_VS',
        options: [
            { label: 'All Status', value: '' },
            { label: 'Pending', value: 'Pending' },
            { label: 'Partial', value: 'Partial' },
            { label: 'Paid', value: 'Paid' },
            { label: 'Overdue', value: 'overdue' }
        ],
        placeholder: 'Select Status',
        search: false,
        multiple: false
    });

    // Store the element reference (VirtualSelect attaches methods to the DOM node)
    statusFilterVS = document.querySelector('#statusFilter_VS');

    // Set up event listeners for filters
    $('#rrNumberFilter').on('change keyup', function() {
        applyFilters();
    });

    document.querySelector('#statusFilter_VS').addEventListener('change', function() {
        applyFilters();
    });
}

// Load accounts Payable data
function loadAccountsPayableData(page = 1) {
    $.ajax({
        url: '/api/accounts-payable',
        type: 'GET',
        data: {
            page: page,
            per_page: 25 // Reduced for faster loading
        },
        success: function(response) {
            if (response.success) {
                accountsPayableData = response.data;
                filteredData = [...accountsPayableData];
                initAccountsPayableDataTable();
                loadStatistics();
                // Re-apply current filters after reload so user selections persist
                applyFilters();
                
                // Log pagination info for debugging
                if (response.pagination) {
                    console.log(`Loaded page ${response.pagination.current_page} of ${response.pagination.total_pages} (${response.pagination.total_records} total records)`);
                }
            }
        },
        error: function(xhr) {
            console.error('Failed to load accounts payable data:', xhr);
            Swal.fire('Error!', 'Failed to load accounts payable data.', 'error');
        }
    });
}

// AJAX helper function
function ajax(url, method, data, successCallback, errorCallback) {
    $.ajax({
        url: url,
        method: method,
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (successCallback) successCallback(response);
        },
        error: function(xhr, status, error) {
            if (errorCallback) {
                errorCallback(xhr, status, error);
            } else {
                console.error('AJAX Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing your request.'
                });
            }
        }
    });
}

// Initialize DataTable
function initAccountsPayableDataTable() {
    if (accountsPayableTable) {
        // Fast refresh: just update data without recreating table
        accountsPayableTable.clear().rows.add(filteredData).draw(false);
        return;
    }

    accountsPayableTable = $('#AccountsPayableTable').DataTable({
        data: filteredData,
        language: {
            searchPlaceholder: "Search here..."
        },
        deferRender: true, // Only render visible rows for better performance
        processing: false, // Disable processing indicator for faster feel
        columns: [
            { 
                data: 'date', 
                title: 'Date',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return moment(data).format('MMM DD, YYYY');
                    }
                    return data; // Return raw data for sorting/filtering
                }
            },
            { 
                data: 'supplier_name', 
                title: 'Supplier',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return `<div class="supplier-info"><div class="fw-bold">${data}</div><small class="text-muted">${row.supplier_code}</small></div>`;
                    }
                    return data;
                }
            },
            { 
                data: 'rr_number', 
                title: 'RR #',
                defaultContent: 'N/A'
            },
            { 
                data: 'reference_number', 
                title: 'Reference #',
                defaultContent: 'N/A'
            },
            { 
                data: 'total_amount', 
                title: 'Total Amount',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return formatCurrency(data);
                    }
                    return data;
                }
            },
            { 
                data: 'terms', 
                title: 'Terms',
                defaultContent: 'N/A'
            },
            { 
                data: 'status', 
                title: 'Status',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return getStatusBadge(data, row.is_overdue);
                    }
                    return data;
                }
            },
            { 
                data: 'balance_amount', 
                title: 'Balance',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return formatCurrency(data);
                    }
                    return data;
                }
            },
            { 
                data: 'CreditMemo', 
                title: 'Credit Memo',
                render: function(data, type, row) {
                    if (type === 'display') {
                        if (data && data > 0) {
                            return formatCurrency(data);
                        }
                        return '<span class="text-muted">-</span>';
                    }
                    return data || 0;
                }
            },
            { 
                data: 'process_by', 
                title: 'Process By',
                defaultContent: 'N/A'
            }
        ],
        order: [[0, 'desc']], // Order by date descending
        pageLength: 25,
        responsive: true,
        rowCallback: function(row, data, index) {
            // Lightweight row styling - only essential classes
            const $row = $(row);
            $row.addClass('clickable-row').attr('data-id', data.id);
            
            // Add status-based classes efficiently
            if (data.status === 'Pending' && data.is_overdue) {
                $row.addClass('table-danger');
            } else if (data.status === 'Paid') {
                $row.addClass('table-success');
            } else if (data.status === 'Partial') {
                $row.addClass('table-warning');
            }
        },
        initComplete: function () {
            const container = $(this.api().table().container());
            container.find('#dt-search-0').addClass('p-1 mx-0 dtsearchInput nofocus');
            container.find('.dt-search label').addClass('py-1 px-3 mx-0 dtsearchLabel').html('<span class="mdi mdi-magnify"></span>');
            container.find('.dt-layout-row').first().find('.dt-layout-cell').each(function() { 
                this.style.setProperty('height', '38px', 'important'); 
            });
            container.find('.dt-layout-table').removeClass('px-4').addClass('btmdtborder');
            container.find('.dt-scroll-body').addClass('rmvBorder');
            container.find('.dt-search').addClass('d-flex justify-content-end');
        }
    });

    // Hide loading screen and show table
    $('.loadingScreen').remove();
    $('#dattableDiv').removeClass('opacity-0');

    // Add blue header to the table
    const tableDiv = $('.dt-layout-row').first();
    tableDiv.after('<div style="background: linear-gradient(to right, var(--primary-color, #1b438f), var(--secondary-color, #33336F) ); color: var(--text-color-light, #FFF); margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px; color: var(--text-color-light, #FFF);">Accounts Payable Management</p></div>');

    // Set up event handlers
    setupEventHandlers();
    
    // Add CSS for hover effects (more efficient than jQuery hover)
    if (!$('#ap-hover-styles').length) {
        $('<style id="ap-hover-styles">')
            .text(`
                .clickable-row { cursor: pointer; transition: background-color 0.2s ease; }
                .clickable-row:hover { background-color: #f8f9fa !important; }
                .table-hover-effect { background-color: #e9ecef !important; }
            `)
            .appendTo('head');
    }
}

// Initialize Date Range Picker
function initDateRangePicker() {
    var start = moment().subtract(29, 'days');
    filteredStartDate = start.format('YYYY-MM-DD');
    var end = moment();
    filteredEndDate = end.format('YYYY-MM-DD');

    function cb(start, end) {
        $('#dateRange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }

    $('#dateRange').daterangepicker({
        startDate: start,
        endDate: end,
        autoUpdateInput: false,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
        filteredStartDate = picker.startDate.format('YYYY-MM-DD');
        filteredEndDate = picker.endDate.format('YYYY-MM-DD');
        applyFilters();
    });

    $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    cb(start, end);
}

// Apply filters
function applyFilters() {
    const rrNumberFilterRaw = $('#rrNumberFilter').val();
    let rrNumberFilter = (rrNumberFilterRaw || '').toLowerCase();
    let statusFilter = '';
    if (statusFilterVS && typeof statusFilterVS.getSelectedOptions === 'function') {
        const statusOpts = statusFilterVS.getSelectedOptions();
        statusFilter = (Array.isArray(statusOpts) && statusOpts[0] && statusOpts[0].value) ? statusOpts[0].value : '';
    }
    let supplierFilter = '';
    if (supplierFilterVS && typeof supplierFilterVS.getSelectedOptions === 'function') {
        const supplierOpts = supplierFilterVS.getSelectedOptions();
        supplierFilter = (Array.isArray(supplierOpts) && supplierOpts[0] && supplierOpts[0].value) ? supplierOpts[0].value : '';
    }

    filteredData = accountsPayableData.filter(function(item) {
        let matchesDate = true;
        let matchesRRNumber = true;
        let matchesStatus = true;
        let matchesSupplier = true;

        // Date filter
        if (filteredStartDate && filteredEndDate) {
            let itemDate = moment(item.date);
            if (itemDate.isBefore(moment(filteredStartDate)) || itemDate.isAfter(moment(filteredEndDate))) {
                matchesDate = false;
            }
        }

        // RR Number filter (guard against undefined rr_number)
        const rrText = (item.rr_number || '').toString().toLowerCase();
        if (rrNumberFilter && !rrText.includes(rrNumberFilter)) {
            matchesRRNumber = false;
        }

        // Status filter
        if (statusFilter) {
            if (statusFilter === 'overdue') {
                matchesStatus = item.status === 'Pending' && item.is_overdue;
            } else {
                matchesStatus = item.status === statusFilter;
            }
        }

        // Supplier filter
        if (supplierFilter && item.supplier_code !== supplierFilter) {
            matchesSupplier = false;
        }

        return matchesDate && matchesRRNumber && matchesStatus && matchesSupplier;
    });

    // Update DataTable
    if (accountsPayableTable) {
        accountsPayableTable.clear();
        accountsPayableTable.rows.add(filteredData);
        accountsPayableTable.draw();
    }

    // Update statistics
    loadStatistics();
}

// Load statistics
function loadStatistics() {
    let totalPending = 0;
    let totalPartial = 0;
    let totalPaid = 0;
    let totalOverdue = 0;
    let totalBalance = 0;
    let totalRecords = filteredData.length;

    filteredData.forEach(function(item) {
        if (item.status === 'Pending') {
            totalPending += parseFloat(item.total_amount || 0);
            totalBalance += parseFloat(item.balance_amount || 0);
            
            if (item.is_overdue) {
                totalOverdue += parseFloat(item.total_amount || 0);
            }
        } else if (item.status === 'Partial') {
            totalPartial += parseFloat(item.total_amount || 0);
            totalBalance += parseFloat(item.balance_amount || 0);
        } else if (item.status === 'Paid') {
            totalPaid += parseFloat(item.total_amount || 0);
        }
    });

    $('#total-pending').text(formatCurrency(totalPending));
    $('#total-paid').text(formatCurrency(totalPaid));
    $('#total-overdue').text(formatCurrency(totalOverdue));
    $('#total-balance').text(formatCurrency(totalBalance));
    $('#total-records').text(totalRecords + ' Records');
}

// Show main modal for record interaction
function showAccountsPayableModal(rowData, mode = 'view') {
    // Fill modal with data first
    fillAccountsPayableModal(rowData);
    
    // Set modal mode (view only)
    setModalMode(mode, rowData.status);
    
    // Store data in modal for later use
    const formElement = $('#accountsPayableForm');
    if (formElement.length > 0) {
        formElement.data('id', rowData.id);
        formElement.data('record-data', rowData);
    } else {
        // If form still not found, store data on modal itself
        $('#accountsPayableModal').data('id', rowData.id);
        $('#accountsPayableModal').data('record-data', rowData);
    }
    
    // Now show the modal after everything is configured
    $('#accountsPayableModal').modal('show');
}

// Fill modal with data
function fillAccountsPayableModal(data) {
    // Format date for HTML date input (yyyy-MM-dd)
    let formattedDate = data.date ? moment(data.date).format('YYYY-MM-DD') : '';
    $('#date').val(formattedDate);
    
    $('#rr_number').val(data.rr_number || '');
    $('#reference_number').val(data.reference_number || '');
    $('#total_amount').val(formatNumberWithCommas(data.total_amount || ''));
    $('#terms').val(data.terms || '');
    $('#status').val(data.status || '');
    
    // Set Balance Amount - if status is "Paid", balance should be 0
    let balanceAmount = data.balance_amount || '';
    if (data.status && data.status.toLowerCase() === 'paid') {
        balanceAmount = '0.00';
    }
    $('#balance_amount').val(formatNumberWithCommas(balanceAmount));
    
    $('#remarks').val(data.remarks || '');
    
    // Calculate and set Total Paid (Total Amount - Balance Amount)
    const totalAmount = parseFloat(data.total_amount || 0);
    const balanceAmountForCalculation = parseFloat(balanceAmount || 0);
    const totalPaid = totalAmount - balanceAmountForCalculation;
    $('#total_paid').val(formatNumberWithCommas(totalPaid.toFixed(2)));
    
    // Handle Credit Memo display
    if (data.CreditMemo && data.CreditMemo > 0) {
        $('#credit_memo').val(formatCurrency(data.CreditMemo));
        $('#credit_memo_container').show();
    } else {
        $('#credit_memo').val(''); // Clear the field value
        $('#credit_memo_container').hide();
    }
    
    // Control balance amount visibility based on status
    const status = data.status || '';
    if (status.toLowerCase() === 'pending') {
        $('#balance_amount_container').hide();
    } else {
        $('#balance_amount_container').show();
    }
    
    // Set supplier in Virtual Select and display field
    const supplierVS = document.querySelector('#supplier_code_VS');
    if (supplierVS && supplierVS.setValue && data.supplier_code) {
        supplierVS.setValue(data.supplier_code);
    }
    
    // Set supplier display field (readonly)
    const supplierDisplay = document.getElementById('supplier_display');
    if (supplierDisplay && data.supplier_name) {
        supplierDisplay.value = `${data.supplier_name} (${data.supplier_code})`;
    } else if (supplierDisplay && data.supplier_code) {
        // Fallback: find supplier name from suppliersData
        const supplier = suppliersData.find(s => s.supplier_code === data.supplier_code);
        if (supplier) {
            supplierDisplay.value = `${supplier.supplier_name} (${supplier.supplier_code})`;
        } else {
            supplierDisplay.value = data.supplier_code;
        }
    }
}

// Set modal mode (view only - no edit functionality)
function setModalMode(mode, status) {
    const isPending = status === 'Pending' || status === 'Partial';
    
    // All form fields are always disabled (view-only)
    $('#accountsPayableForm input, #accountsPayableForm textarea').prop('disabled', true);
    
    // Always show readonly field, hide dropdown (view-only mode)
    const supplierDisplay = document.getElementById('supplier_display');
    const supplierVS = document.getElementById('supplier_code_VS');
    
    if (supplierDisplay) supplierDisplay.style.display = 'block';
    if (supplierVS) supplierVS.style.display = 'none';
    
    // Show/hide buttons - only Process Payment for pending/partial records
    $('#processPaymentBtn').toggle(isPending);
    
    // Control balance amount visibility based on status
    if (status && status.toLowerCase() === 'pending') {
        $('#balance_amount_container').hide();
    } else {
        $('#balance_amount_container').show();
    }
    
    // Note: Credit memo visibility is handled in fillAccountsPayableModal based on data
    
    // Update modal title (always view mode)
    const titleElement = $('#accountsPayableModal .modalHeaderTitle');
    titleElement.text('ACCOUNTS PAYABLE RECORD');
}

// Show payment modal
function showPaymentModal(rowData) {
    $('#payment_ap_id').val(rowData.id);
    $('#payment_total_amount').text(formatCurrency(rowData.balance_amount));
    $('#payment_original_amount').text(formatCurrency(rowData.total_amount));
    $('#payment_balance_amount').text(formatCurrency(rowData.balance_amount));
    
    // Store supplier code for payee auto-population
    $('#paymentModal').data('supplier-code', rowData.supplier_code);
    
    // Set max and data attributes for all payment amount fields
    const balanceAmount = rowData.balance_amount;
    $('#cash_amount_display, #bank_payment_amount, #gcash_amount_display, #check_amount_display').each(function() {
        $(this).attr('max', balanceAmount);
        $(this).data('balance-amount', balanceAmount);
        $(this).val('');
    });
    
    $('#payment_type').val('full');
    
    // Reset payment amount field styling
    $('#cash_amount_display, #bank_payment_amount, #gcash_amount_display, #check_amount_display').css({
        'border-color': '#dee2e6 !important',
        'background-color': '#fff !important'
    }).removeClass('is-invalid is-valid partial-payment');
    
    // Remove payment status indicators
    $('.payment-status').remove();
    
    $('#payment_remarks').val('');
    $('#payment_method').val(''); // No default payment method - user must select
    
    // Control current balance visibility based on status
    const status = rowData.status || '';
    if (status.toLowerCase() === 'pending') {
        $('#payment_balance_container').hide();
    } else {
        $('#payment_balance_container').show();
    }
    
    // Hide all sections first
    $('#bankDetailsSection').hide();
    $('#gcashDetailsSection').hide();
    $('#gcashPaymentSection').hide();
    $('#cashPaymentSection').hide();
    $('#checkDetailsSection').hide();
    
    // No sections shown by default - user must select payment method first
    
    // Reset all fields
    $('#bank_selection').val('');
    resetBankFields();
    $('#gcash_selection').val('');
    resetGcashFields();
    resetCashFields();
    resetCheckFields();
    
    // Load banks data and populate dropdown
    loadBanksDataForModal();
    
    $('#paymentModal').modal('show');
}

// Reset bank fields
function resetBankFields() {
    $('#bank_account_name').val('');
    $('#bank_account_number').val('');
    $('#bank_card_number').val('');
    $('#bank_expiration').val('');
}

// Reset check fields
function resetCheckFields() {
    $('#check_payee').val('');
    $('#check_date').val('');
    $('#check_number').val('');
    $('#check_amount_display').val('');
    $('#check_amount_in_words').val('');
    $('#pay_by_check').prop('checked', false).prop('disabled', true);
    $('label[for="pay_by_check"]').html('Pay by Check');
    $('#checkDetailsSection').hide();
}

// Auto-populate payee field with supplier contact person
function autoPopulatePayeeField() {
    const supplierCode = $('#paymentModal').data('supplier-code');
    
    if (!supplierCode || !suppliersData || suppliersData.length === 0) {
        console.warn('No supplier code found or suppliers data not loaded');
        return;
    }
    
    // Find the supplier in the suppliersData array
    const supplier = suppliersData.find(s => s.SupplierCode === supplierCode);
    
    if (supplier && supplier.ContactPerson) {
        $('#check_payee').val(supplier.ContactPerson);
        console.log(`Auto-populated payee field with: ${supplier.ContactPerson} for supplier: ${supplier.SupplierName}`);
    } else if (supplier) {
        // Don't populate if no contact person found
        console.warn(`No contact person found for supplier ${supplier.SupplierName}, payee field left empty`);
        $('#check_payee').val('');
    } else {
        console.warn(`Supplier with code ${supplierCode} not found in suppliers data`);
        $('#check_payee').val('');
    }
}

// Convert number to words (Philippine English format)
function numberToWords(amount) {
    if (!amount || isNaN(amount)) return '';
    
    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
    const teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    
    function convertThreeDigit(num) {
        let result = '';
        const hundreds = Math.floor(num / 100);
        const remainder = num % 100;
        
        if (hundreds > 0) {
            result += ones[hundreds] + ' Hundred ';
        }
        
        if (remainder >= 20) {
            result += tens[Math.floor(remainder / 10)];
            if (remainder % 10 > 0) {
                result += ' ' + ones[remainder % 10];
            }
        } else if (remainder >= 10) {
            result += teens[remainder - 10];
        } else if (remainder > 0) {
            result += ones[remainder];
        }
        
        return result.trim();
    }
    
    function convertNumber(num) {
        if (num === 0) return 'Zero';
        
        let resultParts = [];
        
        // Billions
        if (num >= 1000000000) {
            resultParts.push(convertThreeDigit(Math.floor(num / 1000000000)) + ' Billion');
            num %= 1000000000;
        }
        
        // Millions
        if (num >= 1000000) {
            resultParts.push(convertThreeDigit(Math.floor(num / 1000000)) + ' Million');
            num %= 1000000;
        }
        
        // Thousands
        if (num >= 1000) {
            resultParts.push(convertThreeDigit(Math.floor(num / 1000)) + ' Thousand');
            num %= 1000;
        }
        
        // Hundreds, tens, and ones
        if (num > 0) {
            resultParts.push(convertThreeDigit(num));
        }
        
        return resultParts.join(', ');
    }
    
    const pesos = Math.floor(amount);
    const centavos = Math.round((amount - pesos) * 100);
    
    let resultParts = [];
    
    if (pesos > 0) {
        resultParts.push(convertNumber(pesos) + ' Peso' + (pesos > 1 ? 's' : ''));
    }
    
    if (centavos > 0) {
        resultParts.push(convertNumber(centavos) + ' Centavo' + (centavos > 1 ? 's' : ''));
    }
    
    if (resultParts.length === 0) {
        resultParts.push('Zero Pesos');
    }
    
    let result = resultParts.join(' And ');
    
    // Add "Only" at the end
    return result + ' Only';
}

// Reset GCash fields
function resetGcashFields() {
    $('#gcash_account_name').val('');
    $('#gcash_account_number').val('');
}

// Reset Cash fields
function resetCashFields() {
    $('#cash_amount_display').val('').css({
        'border-color': '#dee2e6 !important',
        'background-color': '#fff !important'
    }).removeClass('is-invalid is-valid partial-payment');
    
    // Remove payment status indicator
    $('#cash_amount_display').parent().find('.payment-status').remove();
}

// Helper function to get the current payment amount field based on selected method
function getCurrentPaymentAmountField() {
    const paymentMethod = $('#payment_method').val();
    switch(paymentMethod) {
        case 'bank':
            // If pay with check is enabled, use check amount field
            if ($('#pay_by_check').is(':checked')) {
                return $('#check_amount_display');
            }
            return $('#bank_payment_amount');
        case 'gcash':
            return $('#gcash_amount_display');
        default: // cash
            return $('#cash_amount_display');
    }
}



// Edit record function
function editRecord(id) {
    $.ajax({
        url: `/api/accounts-payable/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let data = response.data;
                // Format date for HTML date input
                let formattedDate = data.date ? moment(data.date).format('YYYY-MM-DD') : '';
                $('#date').val(formattedDate);
                
                $('#rr_number').val(data.rr_number);
                $('#reference_number').val(data.reference_number);
                $('#total_amount').val(formatNumberWithCommas(data.total_amount));
                $('#terms').val(data.terms);
                $('#remarks').val(data.remarks);
                
                // Set supplier in Virtual Select
                const supplierVS = document.querySelector('#supplier_code_VS');
                if (supplierVS && supplierVS.setValue) {
                    supplierVS.setValue(data.supplier_code);
                }
                
                $('#accountsPayableForm').data('id', id);
                $('#accountsPayableModalLabel').text('Edit Accounts Payable');
                $('#accountsPayableModal').modal('show');
            }
        },
        error: function(xhr) {
            console.error('Failed to load record:', xhr);
            Swal.fire('Error!', 'Failed to load record details.', 'error');
        }
    });
}
 
// Setup event handlers
function setupEventHandlers() {
    // Row click handler for viewing/editing records
    $(document).on('click', '.clickable-row', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let rowData = accountsPayableData.find(item => item.id == id);
        
        if (rowData) {
            // Show the main modal in view mode
            showAccountsPayableModal(rowData, 'view');
        }
    });

    // Modal button handlers
    $(document).on('click', '#processPaymentBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        let recordData = null;
        let recordId = null;
        
        // Try to get data from form first
        const formElement = $('#accountsPayableForm');
        
        if (formElement.length > 0) {
            recordData = formElement.data('record-data');
            recordId = formElement.data('id');
        }
        
        // If no data from form, try to get from modal
        if (!recordData) {
            recordData = $('#accountsPayableModal').data('record-data');
            recordId = $('#accountsPayableModal').data('id');
        }
        
        if (recordData && recordData.id) {
            // Hide the main modal first, then show payment modal after a short delay
            $('#accountsPayableModal').modal('hide');
            
            // Wait for modal to hide completely before showing payment modal
            setTimeout(function() {
                showPaymentModal(recordData);
            }, 300);
        } else {
            Swal.fire('Error!', 'Unable to process payment. Please try again.', 'error');
        }
    });

    // Alternative direct binding for the button (backup approach)
    $('#accountsPayableModal').on('shown.bs.modal', function() {
        $('#processPaymentBtn').off('click.payment').on('click.payment', function(e) {
            e.preventDefault();
            
            // Try to get data from form first, then modal
            let recordData = $('#accountsPayableForm').data('record-data') || $('#accountsPayableModal').data('record-data');
            
            if (recordData) {
                $('#accountsPayableModal').modal('hide');
                setTimeout(() => showPaymentModal(recordData), 300);
            }
        });
    });

    // Print all table data functionality
    $('#apPrintAllBtn').on('click', function() {
        // Store the current filtered data for printing
        sessionStorage.setItem('printingAPData', JSON.stringify(filteredData));
        
        $.ajax({
            url: "/api/redirect-ap",
            type: "POST",
            data: { printAll: true },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Authorization': 'Bearer ' + localStorage.getItem('api_token')
            },
            success: function(response) {
                if (response.success) {
                    window.open('/print/ap', '_blank');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
                if (xhr.status === 401) {
                    window.location.href = "/login";
                } else {
                    Swal.fire('Error!', 'Failed to prepare print data.', 'error');
                }
            }
        });
    });

    // Refresh data tables without full page reload
    $('#apRefreshBtn').on('click', function() {
        loadAccountsPayableData();
    });

    // Payment form submission
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        
        let id = $('#payment_ap_id').val();
        let currentAmountField = getCurrentPaymentAmountField();
        let rawAmount = currentAmountField.val().replace(/,/g, ''); // Remove commas for calculation
        let enteredAmount = parseFloat(rawAmount) || 0;
        let balanceAmount = parseFloat(currentAmountField.data('balance-amount')) || 0;
        let paymentType = $('#payment_type').val();
        let paymentMethod = $('#payment_method').val();
        
        // Final validation before submission
        if (enteredAmount <= 0) {
            Swal.fire('Error!', 'Please enter a valid payment amount.', 'error');
            return;
        }
        
        // Overpayments are now allowed - removed balance validation
        
        if (!paymentType) {
            Swal.fire('Error!', 'Invalid payment amount detected.', 'error');
            return;
        }

        if (!paymentMethod) {
            Swal.fire('Error!', 'Please select a payment method.', 'error');
            return;
        }

        // Validate bank selection if payment method is bank
        if (paymentMethod === 'bank') {
            const selectedBank = $('#bank_selection').val();
            if (!selectedBank) {
                Swal.fire('Error!', 'Please select a bank when using bank payment method.', 'error');
                return;
            }
            
            // Additional validation for check payment
            if ($('#pay_by_check').is(':checked')) {
                const payee = $('#check_payee').val().trim();
                const checkDate = $('#check_date').val();
                
                if (!payee) {
                    Swal.fire('Error!', 'Please enter the payee name for the check.', 'error');
                    return;
                }
                
                if (!checkDate) {
                    Swal.fire('Error!', 'Please select the check date.', 'error');
                    return;
                }
            }
        }

        // Validate GCash selection if payment method is GCash
        if (paymentMethod === 'gcash') {
            const selectedGcash = $('#gcash_selection').val();
            if (!selectedGcash) {
                Swal.fire('Error!', 'Please select a GCash account when using GCash payment method.', 'error');
                return;
            }
        }
        
        // Create form data and set the raw amount (without commas)
        let formData = new FormData(this);
        formData.set('payment_amount', rawAmount); // Send raw number without commas
        formData.set('payment_type', paymentMethod); // Use the payment method (cash/bank/gcash)
        
        // Add bank details if bank payment method is selected
        if (paymentMethod === 'bank') {
            const selectedBankId = $('#bank_selection').val();
            const bankData = $('#bank_selection').find('option:selected').data('bank');
            
            if (bankData) {
                formData.append('bank_id', selectedBankId);
                formData.append('bank_name', bankData.BankName);
                formData.append('account_name', bankData.AccountName);
                formData.append('account_number', bankData.AccountNumber);
                formData.append('card_number', bankData.CardNumber);
                formData.append('expiration_date', bankData.ExpirationDate);
                
                // Add check details if check payment is selected
                if ($('#pay_by_check').is(':checked')) {
                    formData.append('pay_by_check', '1');
                    formData.append('check_payee', $('#check_payee').val().trim());
                    formData.append('check_date', $('#check_date').val());
                    formData.append('check_number', $('#check_number').val().trim());
                    formData.append('check_amount', rawAmount);
                    formData.append('check_amount_in_words', $('#check_amount_in_words').val());
                }
            }
        }

        // Add GCash details if GCash payment method is selected
        if (paymentMethod === 'gcash') {
            const selectedGcashId = $('#gcash_selection').val();
            const gcashData = $('#gcash_selection').find('option:selected').data('gcash');
            
            if (gcashData) {
                formData.append('gcash_id', selectedGcashId);
                formData.append('gcash_account_name', gcashData.AccountName);
                formData.append('gcash_account_number', gcashData.AccountNumber);
            }
        }
        
        // Show loading state
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Processing...');
        
        $.ajax({
            url: `/api/accounts-payable/${id}/payment`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#paymentModal').modal('hide');
                    loadAccountsPayableData(); // Reload data
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                let errorMessage = xhr.responseJSON?.message || 'Please check your input.';
                
                if (errors) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                Swal.fire('Error!', errorMessage, 'error');
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Download functionality
    $(document).on('click', '.downloadBtn', function() {
        exportToCSV();
    });

    // Create missing AP records from confirmed RRs
    $(document).on('click', '#createMissingAPBtn', function() {
        Swal.fire({
            title: 'Create Missing Accounts Payable?',
            text: 'This will create AP records for all confirmed RRs that don\'t have corresponding AP records yet.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, create them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/report/v2/create-missing-ap',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: `Created ${response.created_count} Accounts Payable records.`,
                                icon: 'success'
                            });
                            loadAccountsPayableData(); // Reload data
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = xhr.responseJSON?.message || 'Failed to create missing AP records.';
                        Swal.fire('Error!', errorMessage, 'error');
                    }
                });
            }
        });
    });

    // Check payment checkbox handler
    $(document).on('change', '#pay_by_check', function() {
        const selectedBank = $('#bank_selection').val();
        
        if ($(this).is(':checked')) {
            // Validate that a bank is selected first
            if (!selectedBank) {
                // Uncheck the checkbox
                $(this).prop('checked', false);
                
                // Show error message
                Swal.fire({
                    icon: 'warning',
                    title: 'Bank Selection Required',
                    text: 'Please select a bank first before enabling check payment.',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Hide the bank payment amount field since check amount will be used
            $('#bankPaymentAmountColumn').hide();
            
            $('#checkDetailsSection').slideDown();
            // Set default check date to today
            $('#check_date').val(moment().format('YYYY-MM-DD'));
            
            // Auto-populate payee field with supplier contact person
            autoPopulatePayeeField();
            
            // Update check amount and words when amount is already entered
            const currentAmount = $('#bank_payment_amount').val().replace(/,/g, '');
            if (currentAmount && !isNaN(currentAmount)) {
                updateCheckAmountFields(parseFloat(currentAmount));
            }
        } else {
            // Show the bank payment amount field when not using check
            $('#bankPaymentAmountColumn').show();
            
            $('#checkDetailsSection').slideUp();
            resetCheckFields();
        }
    });

    // Update check amount fields when bank payment amount changes
    $(document).on('input keyup change', '#bank_payment_amount', function() {
        const rawValue = $(this).val().replace(/,/g, '');
        const amount = parseFloat(rawValue) || 0;
        
        // If check payment is enabled, update check fields
        if ($('#pay_by_check').is(':checked') && amount > 0) {
            updateCheckAmountFields(amount);
        }
    });

    // Update check amount in words when check amount is typed directly
    $(document).on('input keyup change', '#check_amount_display', function() {
        const rawValue = $(this).val().replace(/,/g, '');
        const amount = parseFloat(rawValue) || 0;
        
        if (amount > 0) {
            // Update only the amount in words field
            const amountInWords = numberToWords(amount);
            $('#check_amount_in_words').val(amountInWords);
        } else {
            // Clear the amount in words field if no amount
            $('#check_amount_in_words').val('');
        }
    });

    // Payment amount change handler - automated payment type detection
    $(document).on('input keyup change', '#cash_amount_display, #bank_payment_amount, #gcash_amount_display, #check_amount_display', function() {
        let rawValue = $(this).val().replace(/,/g, ''); // Remove existing commas
        let enteredAmount = parseFloat(rawValue) || 0;
        let balanceAmount = parseFloat($(this).data('balance-amount')) || 0;
        let paymentTypeField = $('#payment_type');
        let amountField = $(this);
        
        console.log('Payment validation triggered - Amount:', enteredAmount, 'Balance:', balanceAmount);
        
        // Format the input value with commas, but preserve decimal point during typing
        if (rawValue && rawValue !== '' && !isNaN(parseFloat(rawValue))) {
            // Don't format if user is in the middle of typing a decimal (ends with . or has only one decimal point with no digits after)
            let isTypingDecimal = rawValue.endsWith('.') || /^\d+\.\d{0,2}$/.test(rawValue);
            
            if (!isTypingDecimal) {
                let formattedValue = parseFloat(rawValue).toLocaleString('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
                
                // Only update if the formatted value is different (to prevent cursor jumping)
                if (amountField.val() !== formattedValue) {
                    let cursorPos = amountField[0].selectionStart;
                    let oldLength = amountField.val().length;
                    amountField.val(formattedValue);
                    
                    // Adjust cursor position after formatting
                    let newLength = formattedValue.length;
                    let newPos = cursorPos + (newLength - oldLength);
                    amountField[0].setSelectionRange(newPos, newPos);
                }
            }
        }
        
        // Remove any existing validation classes
        amountField.removeClass('is-invalid is-valid');
        
        if (enteredAmount === 0) {
            // No amount entered yet - reset to default styling
            paymentTypeField.val('full');
            
            console.log('Resetting to default styling');
            amountField.attr('style', amountField.attr('style').replace(/border-color:[^;]*;?/g, '').replace(/background-color:[^;]*;?/g, '') + 
                'border-color: #dee2e6 !important; background-color: #fff !important;')
                .removeClass('is-invalid is-valid partial-payment');
            
            // Remove payment status and overpayment info
            amountField.next('.overpayment-info').remove();
            amountField.parent().find('.payment-status').remove();
            return;
        }
        
        if (enteredAmount >= balanceAmount) {
            // Amount equals or exceeds balance - full payment (overpayment allowed)
            paymentTypeField.val('full');
            
            // Show overpayment info if amount exceeds balance
            if (enteredAmount > balanceAmount) {
                // Over payment - green input
                console.log('Applying overpayment styling');
                amountField.attr('style', amountField.attr('style').replace(/border-color:[^;]*;?/g, '').replace(/background-color:[^;]*;?/g, '') + 
                    'border-color: #28a745 !important; background-color: #d4edda !important;')
                    .removeClass('is-invalid').addClass('is-valid');
                
                // Remove old indicators and add payment status text
                amountField.next('.overpayment-info').remove();
                amountField.parent().find('.payment-status').remove();
                
                // Handle different positioning for check amount field
                if (amountField.attr('id') === 'check_amount_display') {
                    // For check amount field, add status text to the relative container
                    amountField.parent().append('<span class="payment-status" style="position: absolute; top: -20px; right: 0; color: #28a745; font-size: 7px; font-weight: 700; text-transform: uppercase;">Over Payment</span>');
                } else {
                    // For other fields, use the original method
                    amountField.prev('label').after('<span class="payment-status" style="padding-left: 88px; color: #28a745; font-size: 7px; font-weight: 700; text-transform: uppercase;">Over Payment</span>');
                }
            } else {
                // Exact payment - green input
                console.log('Applying exact payment styling');
                amountField.attr('style', amountField.attr('style').replace(/border-color:[^;]*;?/g, '').replace(/background-color:[^;]*;?/g, '') + 
                    'border-color: #28a745 !important; background-color: #d4edda !important;')
                    .removeClass('is-invalid').addClass('is-valid');
                
                // Remove old indicators and add payment status text
                amountField.next('.overpayment-info').remove();
                amountField.parent().find('.payment-status').remove();
                
                // Handle different positioning for check amount field
                if (amountField.attr('id') === 'check_amount_display') {
                    // For check amount field, add status text to the relative container
                    amountField.parent().append('<span class="payment-status" style="position: absolute; top: -20px; right: 0; color: #28a745; font-size: 7px; font-weight: 700; text-transform: uppercase;">Full Payment</span>');
                } else {
                    // For other fields, use the original method
                    amountField.prev('label').after('<span class="payment-status" style="padding-left: 90px; color: #28a745; font-size: 7px; font-weight: 700; text-transform: uppercase;">Full Payment</span>');
                }
            }
        } else if (enteredAmount < balanceAmount && enteredAmount > 0) {
            // Less than balance - partial payment - yellow input
            paymentTypeField.val('partial');
            
            console.log('Applying partial payment styling');
            amountField.attr('style', amountField.attr('style').replace(/border-color:[^;]*;?/g, '').replace(/background-color:[^;]*;?/g, '') + 
                'border-color: #ffc107 !important; background-color: #fff3cd !important;')
                .removeClass('is-invalid is-valid').addClass('partial-payment');
            
            // Remove old indicators and add payment status text
            amountField.next('.overpayment-info').remove();
            amountField.parent().find('.payment-status').remove();
            
            // Handle different positioning for check amount field
            if (amountField.attr('id') === 'check_amount_display') {
                // For check amount field, add status text to the relative container
                amountField.parent().append('<span class="payment-status" style="position: absolute; top: -20px; right: 0; color: #ffc107; font-size: 7px; font-weight: 700; text-transform: uppercase;">Partial Payment</span>');
            } else {
                // For other fields, use the original method
                amountField.prev('label').after('<span class="payment-status" style="padding-left: 78px; color: #ffc107; font-size: 7px; font-weight: 700; text-transform: uppercase;">Partial Payment</span>');
            }
        }
    });

    // Payment method change handler
    $(document).on('change', '#payment_method', function() {
        const selectedMethod = $(this).val();
        
        // Hide all sections first
        $('#bankDetailsSection').hide();
        $('#gcashDetailsSection').hide();
        $('#gcashPaymentSection').hide();
        $('#cashPaymentSection').hide();
        $('#checkDetailsSection').hide();
        
        if (selectedMethod === 'bank') {
            $('#bankDetailsSection').show();
            // Check Details will only show if checkbox is checked
            if ($('#pay_by_check').is(':checked')) {
                $('#checkDetailsSection').show();
            }
            // Make bank selection required when bank is chosen
            $('#bank_selection').attr('required', true);
            $('#gcash_selection').removeAttr('required').val('');
            resetGcashFields();
            resetCashFields();
        } else if (selectedMethod === 'gcash') {
            $('#gcashDetailsSection').show();
            $('#gcashPaymentSection').show();
            // Make gcash selection required when gcash is chosen
            $('#gcash_selection').attr('required', true);
            $('#bank_selection').removeAttr('required').val('');
            resetBankFields();
            resetCashFields();
        } else if (selectedMethod === 'cash') {
            // Cash payment
            $('#cashPaymentSection').show();
            $('#bank_selection').removeAttr('required').val('');
            $('#gcash_selection').removeAttr('required').val('');
            resetBankFields();
            resetGcashFields();
        }
    });

    // Bank selection change handler
    $(document).on('change', '#bank_selection', function() {
        const selectedBankId = $(this).val();
        
        if (selectedBankId) {
            const selectedOption = $(this).find('option:selected');
            const bankData = selectedOption.data('bank');
            
            if (bankData) {
                // Populate bank fields with selected bank data
                $('#bank_account_name').val(bankData.AccountName || '');
                $('#bank_account_number').val(bankData.AccountNumber || '');
                $('#bank_card_number').val(bankData.CardNumber || '');
                
                // Format expiration date to MM/YY format
                let expirationDate = '';
                if (bankData.ExpirationDate) {
                    const date = new Date(bankData.ExpirationDate);
                    if (!isNaN(date.getTime())) {
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const year = String(date.getFullYear()).slice(-2);
                        expirationDate = `${month}/${year}`;
                    }
                }
                $('#bank_expiration').val(expirationDate);
                
                // Enable check payment option when bank is selected
                $('#pay_by_check').prop('disabled', false);
                $('label[for="pay_by_check"]').html('Pay by Check');
                
                // Auto-populate payee if check payment is already enabled
                if ($('#pay_by_check').is(':checked')) {
                    autoPopulatePayeeField();
                }
            } else {
                console.warn('No bank data found for selected option');
            }
        } else {
            resetBankFields();
            
            // If no bank is selected, disable check payment option
            $('#pay_by_check').prop('disabled', true).prop('checked', false);
            $('label[for="pay_by_check"]').html('Pay by Check <small class="text-muted">(Select a bank first)</small>');
            
            // Hide check details if currently shown
            if ($('#checkDetailsSection').is(':visible')) {
                $('#checkDetailsSection').slideUp();
                resetCheckFields();
            }
        }
    });

    // GCash selection change handler
    $(document).on('change', '#gcash_selection', function() {
        const selectedGcashId = $(this).val();
        
        if (selectedGcashId) {
            const selectedOption = $(this).find('option:selected');
            const gcashData = selectedOption.data('gcash');
            
            if (gcashData) {
                // Populate GCash fields with selected GCash data
                $('#gcash_account_name').val(gcashData.AccountName || '');
                $('#gcash_account_number').val(gcashData.AccountNumber || '');
            } else {
                console.warn('No GCash data found for selected option');
            }
        } else {
            resetGcashFields();
        }
    });

    // Function to update check amount fields
    function updateCheckAmountFields(amount) {
        const formattedAmount = formatNumberWithCommas(amount);
        const amountInWords = numberToWords(amount);
        
        $('#check_amount_display').val(formattedAmount);
        $('#check_amount_in_words').val(amountInWords);
    }

    // Reset payment modal when closed
    $('#paymentModal').on('hidden.bs.modal', function() {
        // Reset all payment amount fields and remove overpayment info
        $('#cash_amount_display, #bank_payment_amount, #gcash_amount_display, #check_amount_display').removeClass('is-invalid is-valid').next('.invalid-feedback, .overpayment-info').remove();
        
        // Reset all payment type indicators
        // Reset payment amount field styling
        $('#cash_amount_display, #bank_payment_amount, #gcash_amount_display').css({
            'border-color': '#dee2e6 !important',
            'background-color': '#fff !important'
        }).removeClass('is-invalid is-valid partial-payment');
        
        // Remove payment status indicators
        $('.payment-status').remove();
        
        $('#payment_method').val('');
        $('#bankDetailsSection').hide();
        $('#gcashDetailsSection').hide();
        $('#gcashPaymentSection').hide();
        $('#cashPaymentSection').hide();
        $('#checkDetailsSection').hide();
        $('#bank_selection').removeAttr('required').val('');
        $('#gcash_selection').removeAttr('required').val('');
        resetBankFields();
        resetGcashFields();
        resetCashFields();
        resetCheckFields();
        $(this).find('form')[0].reset();
    });

    // Hover styles are now handled in initAccountsPayableDataTable()
}

// Helper functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount || 0);
}

// Format number with commas (for input fields)
function formatNumberWithCommas(amount) {
    if (!amount || amount === '') return '';
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

// Remove commas from formatted number (for calculations)
function removeCommas(value) {
    if (!value) return 0;
    return parseFloat(value.toString().replace(/,/g, '')) || 0;
}

// Calculate Total Paid (Total Amount - Balance Amount)
function calculateTotalPaid() {
    const totalAmountValue = $('#total_amount').val();
    const balanceAmountValue = $('#balance_amount').val();
    
    const totalAmount = parseFloat(removeCommas(totalAmountValue)) || 0;
    const balanceAmount = parseFloat(removeCommas(balanceAmountValue)) || 0;
    const totalPaid = totalAmount - balanceAmount;
    
    $('#total_paid').val(formatNumberWithCommas(totalPaid.toFixed(2)));
}

// Initialize amount field formatting
function initializeAmountFormatting() {
    // Format total_amount field on blur (when user finishes typing)
    $('#total_amount').on('blur', function() {
        let value = $(this).val();
        if (value && !isNaN(removeCommas(value))) {
            $(this).val(formatNumberWithCommas(removeCommas(value)));
        }
        // Recalculate Total Paid when Total Amount changes
        calculateTotalPaid();
    });
    
    // Allow only numbers, decimal points, and commas during input
    $('#total_amount').on('input', function() {
        let value = $(this).val();
        // Remove any non-numeric characters except decimal point and comma
        value = value.replace(/[^0-9.,]/g, '');
        $(this).val(value);
    });
}

function getStatusBadge(status, isOverdue = false) {
    let badgeClass = '';
    let displayText = status;
    
    if (status === 'Pending' && isOverdue) {
        badgeClass = 'statusBadge2';  // Red - Overdue
        displayText = 'Overdue';
    } else {
        switch(status) {
            case 'Pending':
                badgeClass = 'statusBadge4';  // Blue - Pending
                break;
            case 'Paid':
                badgeClass = 'statusBadge1';  // Green - Paid
                break;
            case 'Partial':
                badgeClass = 'statusBadge3';  // Orange - Partial
                break;
            default:
                badgeClass = 'statusBadge3';  // Orange - Default
        }
    }
    
    return `<span class="${badgeClass}">${displayText}</span>`;
}

function exportToCSV() {
    let csvContent = "data:text/csv;charset=utf-8,";
    
    // Add headers
    csvContent += "Date,Supplier Code,Supplier Name,RR Number,Reference Number,Total Amount,Terms,Status,Balance Amount,Process By,Remarks\n";
    
    // Add data rows
    filteredData.forEach(function(row) {
        let csvRow = [
            moment(row.date).format('YYYY-MM-DD'),
            row.supplier_code,
            `"${row.supplier_name}"`,
            row.rr_number,
            row.reference_number,
            row.total_amount,
            `"${row.terms || ''}"`,
            row.status,
            row.balance_amount,
            `"${row.process_by || ''}"`,
            `"${row.remarks || ''}"`
        ].join(",");
        csvContent += csvRow + "\n";
    });
    
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "AccountsPayable_" + moment().format('YYYY-MM-DD') + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}