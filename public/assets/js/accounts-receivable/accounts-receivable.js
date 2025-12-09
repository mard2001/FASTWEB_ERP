let MainTH = null;
let jsonArr = [];
let isloading = false;
let currentEditId = null;

// Global variables
let accountsReceivableData = [];
let filteredData = [];
let accountsReceivableTable;
let statusFilterVS;
let customerFilterVS;
let filteredStartDate;
let filteredEndDate;
let customersData = [];
let banksData = [];
let gcashData = [];

// Initialize page
$(document).ready(function() {
    initializeFilters();
    initDateRangePicker();
    loadCustomersData();
    loadAccountsReceivableData();
    loadGcashData(); // Load GCash data for payment dropdown
    
    // Initialize number formatting for amount fields
    initializeAmountFormatting();
});

// Load customers data for dropdown
function loadCustomersData() {
    $.ajax({
        url: '/api/accounts-receivable/customers',
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success && response.data) {
                customersData = response.data;
                populateCustomerDropdowns();
            } else {
                console.warn('No customer data received or invalid response format');
                // Still initialize dropdowns with empty data
                populateCustomerDropdowns();
            }
        },
        error: function(xhr) {
            console.error('Failed to load customers data:', xhr);
            // Still initialize dropdowns with empty data to prevent further errors
            customersData = [];
            populateCustomerDropdowns();
            
            // Show user-friendly message only if it's a critical error
            if (xhr.status === 401) {
                console.warn('Authentication required for customers data');
            } else if (xhr.status >= 500) {
                console.error('Server error loading customers data');
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

// Populate customer dropdowns
function populateCustomerDropdowns() {
    // Check if VirtualSelect is available
    if (typeof VirtualSelect === 'undefined') {
        console.error('VirtualSelect library is not loaded');
        return;
    }

    // For filter dropdown
    let customerOptions = [{ label: 'All Customers', value: '' }];
    customersData.forEach(customer => {
        customerOptions.push({
            label: `${customer.custCode} - ${customer.custName}`,
            value: customer.custCode
        });
    });

    // Initialize customer filter if element exists and not already initialized
    const customerFilterElement = document.querySelector('#customerFilter_VS');
    if (customerFilterElement && !customerFilterVS) {
        try {
            customerFilterVS = VirtualSelect.init({
                ele: '#customerFilter_VS',
                options: customerOptions,
                placeholder: 'Select Customer',
                search: true,
                multiple: false
            });

            customerFilterElement.addEventListener('change', function() {
                applyFilters();
            });
        } catch (error) {
            console.error('Error initializing customer filter:', error);
        }
    }

    // For form dropdown - only initialize if element exists
    const customerFormElement = document.querySelector('#customer_code_VS');
    if (customerFormElement) {
        try {
            let formCustomerOptions = [];
            customersData.forEach(customer => {
                formCustomerOptions.push({
                    label: `${customer.custCode} - ${customer.custName}`,
                    value: customer.custCode,
                    customData: customer.custName
                });
            });

            VirtualSelect.init({
                ele: '#customer_code_VS',
                options: formCustomerOptions,
                placeholder: 'Select Customer',
                search: true,
                multiple: false
            });

            // Add event listener for customer selection in form
            customerFormElement.addEventListener('change', function() {
                const selectedValue = this.value;
                const selectedCustomer = customersData.find(c => c.custCode === selectedValue);
                if (selectedCustomer) {
                    $('#customer_name').val(selectedCustomer.custName);
                }
            });
        } catch (error) {
            console.error('Error initializing customer form dropdown:', error);
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
    const statusFilterElement = document.querySelector('#statusFilter_VS');

    if (statusFilterElement) {
        try {
            VirtualSelect.init({
                ele: '#statusFilter_VS',
                options: [
                    { label: 'All Status', value: '' },
                    { label: 'Outstanding', value: 'Outstanding' },
                    { label: 'Partial', value: 'Partial' },
                    { label: 'Fully Paid', value: 'Settled' },
                    { label: 'Overdue', value: 'overdue' }
                ],
                placeholder: 'Select Status',
                search: false,
                multiple: false
            });

            statusFilterVS = statusFilterElement;

            statusFilterElement.addEventListener('change', function() {
                applyFilters();
            });
        } catch (error) {
            console.error('Error initializing status filter:', error);
        }
    } else {
        statusFilterVS = null;
    }

    const soNumberInput = $('#soNumberFilter');
    if (soNumberInput.length) {
        soNumberInput.on('change keyup', function() {
            applyFilters();
        });
    }
}

// Load accounts receivable data
function loadAccountsReceivableData(page = 1) {
    $.ajax({
        url: '/api/accounts-receivable',
        type: 'GET',
        data: {
            page: page,
            per_page: 25 // Reduced for faster loading
        },
        success: function(response) {
            if (response.success) {
                accountsReceivableData = response.data;
                filteredData = [...accountsReceivableData];
                initAccountsReceivableDataTable();
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
            console.error('Failed to load accounts receivable data:', xhr);
            Swal.fire('Error!', 'Failed to load accounts receivable data.', 'error');
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
function initAccountsReceivableDataTable() {
    if (accountsReceivableTable) {
        // Fast refresh: just update data without recreating table
        accountsReceivableTable.clear().rows.add(filteredData).draw(false);
        return;
    }

    accountsReceivableTable = $('#AccountsReceivableTable').DataTable({
        data: filteredData,
        language: {
            searchPlaceholder: "Search here..."
        },
        deferRender: true, // Only render visible rows for better performance
        processing: false, // Disable processing indicator for faster feel
        columnDefs: [
            {
                targets: 8, // Credit Memo column index
                visible: false // Hide the column
            }
        ],
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
                data: 'customer_name', 
                title: 'Customer',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return `<div class="customer-info"><div class="fw-bold">${data}</div><small class="text-muted">${row.customer_code}</small></div>`;
                    }
                    return data;
                }
            },
            { 
                data: 'so_number', 
                title: 'SO #',
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
                        return getStatusBadge(data, row.is_overdue, row.total_amount, row.balance_amount);
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
            },
            {
                data: 'created_at',
                title: 'Created At',
                visible: false,
                searchable: false
            }
        ],
        // Order primarily by creation time (latest first), then by date
        order: [[10, 'desc'], [0, 'desc']],
        pageLength: 25,
        responsive: true,
        rowCallback: function(row, data, index) {
            // Lightweight row styling - only essential classes
            const $row = $(row);
            $row.addClass('clickable-row').attr('data-id', data.id);
            
            // Add status-based classes efficiently
            const isPartial = (parseFloat(data.balance_amount) > 0) && (parseFloat(data.balance_amount) < parseFloat(data.total_amount));
            if (data.status === 'Outstanding' && data.is_overdue && !isPartial) {
                $row.addClass('table-danger');
            } else if (data.status === 'Settled' || parseFloat(data.balance_amount) <= 0) {
                $row.addClass('table-success');
            } else if (isPartial) {
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
        tableDiv.after('<div style="background: linear-gradient(to right, var(--primary-color, #1b438f), var(--secondary-color, #33336F) ); color: var(--text-color-light, #FFF); margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px; color: var(--text-color-light, #FFF);">Accounts Receivable Management</p></div>');    // Set up event handlers
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
    const soNumberFilterRaw = $('#soNumberFilter').val();
    let soNumberFilter = (soNumberFilterRaw || '').toLowerCase();
    let statusFilter = '';
    if (statusFilterVS && typeof statusFilterVS.getSelectedOptions === 'function') {
        const statusOpts = statusFilterVS.getSelectedOptions();
        statusFilter = (Array.isArray(statusOpts) && statusOpts[0] && statusOpts[0].value) ? statusOpts[0].value : '';
    }
    let customerFilter = '';
    if (customerFilterVS && typeof customerFilterVS.getSelectedOptions === 'function') {
        const customerOpts = customerFilterVS.getSelectedOptions();
        customerFilter = (Array.isArray(customerOpts) && customerOpts[0] && customerOpts[0].value) ? customerOpts[0].value : '';
    }

    filteredData = accountsReceivableData.filter(function(item) {
        let matchesDate = true;
        let matchesSONumber = true;
        let matchesStatus = true;
        let matchesCustomer = true;

        // Date filter
        if (filteredStartDate && filteredEndDate) {
            let itemDate = moment(item.date);
            if (itemDate.isBefore(moment(filteredStartDate)) || itemDate.isAfter(moment(filteredEndDate))) {
                matchesDate = false;
            }
        }

        // SO Number filter (guard against undefined so_number)
        const soText = (item.so_number || '').toString().toLowerCase();
        if (soNumberFilter && !soText.includes(soNumberFilter)) {
            matchesSONumber = false;
        }

        // Status filter
        if (statusFilter) {
            if (statusFilter === 'overdue') {
                matchesStatus = item.status === 'Outstanding' && item.is_overdue;
            } else {
                matchesStatus = item.status === statusFilter;
            }
        }

        // Customer filter
        if (customerFilter && item.customer_code !== customerFilter) {
            matchesCustomer = false;
        }

        return matchesDate && matchesSONumber && matchesStatus && matchesCustomer;
    });

    // Update DataTable
    if (accountsReceivableTable) {
        accountsReceivableTable.clear();
        accountsReceivableTable.rows.add(filteredData);
        accountsReceivableTable.draw();
    }

    // Update statistics
    loadStatistics();
}

// Load statistics
function loadStatistics() {
    let totalOutstanding = 0;
    let totalPartial = 0;
    let totalSettled = 0;
    let totalOverdue = 0;
    let totalBalance = 0;
    let totalRecords = filteredData.length;

    filteredData.forEach(function(item) {
        if (item.status === 'Outstanding') {
            totalOutstanding += parseFloat(item.total_amount || 0);
            totalBalance += parseFloat(item.balance_amount || 0);
            
            if (item.is_overdue) {
                totalOverdue += parseFloat(item.total_amount || 0);
            }
        } else if (item.status === 'Partial') {
            totalPartial += parseFloat(item.total_amount || 0);
            totalBalance += parseFloat(item.balance_amount || 0);
        } else if (item.status === 'Settled') {
            totalSettled += parseFloat(item.total_amount || 0);
        }
    });

    $('#total-outstanding').text(formatCurrency(totalOutstanding));
    $('#total-invoices').text(formatCurrency(totalSettled));
    $('#total-credit').text(formatCurrency(totalOverdue));
    $('#total-records').text(totalRecords + ' Records');
}

// Show main modal for record interaction
function showAccountsReceivableModal(rowData, mode = 'view') {
    // Fill modal with data first
    fillAccountsReceivableModal(rowData);
    
    // Set modal mode (view only)
    setModalMode(mode, rowData.status);
    
    // Store data in modal for later use
    const formElement = $('#accountsReceivableForm');
    if (formElement.length > 0) {
        formElement.data('id', rowData.id);
        formElement.data('record-data', rowData);
    } else {
        // If form still not found, store data on modal itself
        $('#accountsReceivableModal').data('id', rowData.id);
        $('#accountsReceivableModal').data('record-data', rowData);
    }
    
    // Now show the modal after everything is configured
    $('#accountsReceivableModal').modal('show');
}

// Fill modal with data
function fillAccountsReceivableModal(data) {
    // Format date for HTML date input (yyyy-MM-dd)
    let formattedDate = data.date ? moment(data.date).format('YYYY-MM-DD') : '';
    $('#date').val(formattedDate);
    
    $('#so_number').val(data.so_number || '');
    $('#reference_number').val(data.reference_number || '');
    $('#total_amount').val(formatNumberWithCommas(data.total_amount || ''));
    $('#terms').val(data.terms || '');
    $('#status').val(data.status || '');
    
    // Set Balance Amount - if status is "Settled", balance should be 0
    let balanceAmount = data.balance_amount || '';
    if (data.status && data.status.toLowerCase() === 'settled') {
        balanceAmount = '0.00';
    }
    $('#balance_amount').val(formatNumberWithCommas(balanceAmount));
    
    $('#remarks').val(data.remarks || '');

    // Calculate and set Total Paid (Total Amount - Balance Amount)
    const totalAmount = parseFloat(data.total_amount || 0);
    const balanceAmountForCalculation = parseFloat(balanceAmount || 0);
    const totalPaid = totalAmount - balanceAmountForCalculation;
    $('#total_paid').val(formatNumberWithCommas(totalPaid.toFixed(2)));

    // Set Credit Memo amount; default to 0.00 if none
    const creditMemoRaw = (data.CreditMemo !== undefined ? data.CreditMemo : data.credit_memo);
    const creditMemoAmount = parseFloat(creditMemoRaw || 0);
    $('#credit_memo').val(formatNumberWithCommas(creditMemoAmount.toFixed(2)));
    
    // Control balance amount visibility based on status
    const status = data.status || '';
    if (status.toLowerCase() === 'outstanding') {
        $('#balance_amount_container').hide();
    } else {
        $('#balance_amount_container').show();
    }
    
    // Set customer in Virtual Select and display field
    const customerVS = document.querySelector('#customer_code_VS');
    if (customerVS && customerVS.setValue && data.customer_code) {
        customerVS.setValue(data.customer_code);
    }
    
    // Set customer display field (readonly)
    const customerDisplay = document.getElementById('customer_display');
    if (customerDisplay && data.customer_name) {
        customerDisplay.value = `${data.customer_name} (${data.customer_code})`;
    } else if (customerDisplay && data.customer_code) {
        // Fallback: find customer name from customersData
        const customer = customersData.find(c => c.custCode === data.customer_code);
        if (customer) {
            customerDisplay.value = `${customer.custName} (${customer.custCode})`;
        } else {
            customerDisplay.value = data.customer_code;
        }
    }
}

// Set modal mode (view only - no edit functionality)
function setModalMode(mode, status) {
    const isOutstanding = status === 'Outstanding' || status === 'Partial';
    
    // All form fields are always disabled (view-only)
    $('#accountsReceivableForm input, #accountsReceivableForm textarea').prop('disabled', true);
    
    // Always show readonly field, hide dropdown (view-only mode)
    const customerDisplay = document.getElementById('customer_display');
    const customerVS = document.getElementById('customer_code_VS');
    
    if (customerDisplay) customerDisplay.style.display = 'block';
    if (customerVS) customerVS.style.display = 'none';
    
    // Show/hide buttons - only Process Payment for outstanding/partial records
    $('#processPaymentBtn').toggle(isOutstanding);
    
    // Control balance amount visibility based on status
    if (status && status.toLowerCase() === 'outstanding') {
        $('#balance_amount_container').hide();
    } else {
        $('#balance_amount_container').show();
    }
    
    // Update modal title (always view mode)
    const titleElement = $('#accountsReceivableModal .modalHeaderTitle');
    titleElement.text('ACCOUNTS RECEIVABLE RECORD');
}

// Show payment modal
function showPaymentModal(rowData) {
    // Removed FIFO pre-check: allow paying any invoice for the customer
    $('#payment_ap_id').val(rowData.id);
    $('#payment_total_amount').text(formatCurrency(rowData.balance_amount));
    $('#payment_original_amount').text(formatCurrency(rowData.total_amount));
    $('#payment_balance_amount').text(formatCurrency(rowData.balance_amount));
    
    // Store customer code for payee auto-population
    $('#paymentModal').data('customer-code', rowData.customer_code);
    
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
    $('#pay_by_check').prop('checked', false).prop('disabled', true);
    $('label[for="pay_by_check"]').html('Pay by Check <small class="text-muted">(Select a bank first)</small>');
    
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
    $('#checkDetailsSection').hide();
}

// Auto-populate payee field with customer contact person
function autoPopulatePayeeField() {
    const customerCode = $('#paymentModal').data('customer-code');
    
    if (!customerCode || !customersData || customersData.length === 0) {
        console.warn('No customer code found or customers data not loaded');
        return;
    }
    
    // Find the customer in the customersData array
    const customer = customersData.find(c => c.custCode === customerCode);
    
    if (customer && customer.contactPerson) {
        $('#check_payee').val(customer.contactPerson);
        console.log(`Auto-populated payee field with: ${customer.contactPerson} for customer: ${customer.custName}`);
    } else if (customer) {
        // Don't populate if no contact person found
        console.warn(`No contact person found for customer ${customer.custName}, payee field left empty`);
        $('#check_payee').val('');
    } else {
        console.warn(`Customer with code ${customerCode} not found in customers data`);
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
        let rowData = accountsReceivableData.find(item => item.id == id);
        
        if (rowData) {
            // Show the main modal in view mode
            showAccountsReceivableModal(rowData, 'view');
        }
    });
    
    // Modal button handlers
    $(document).on('click', '#processPaymentBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        let recordData = null;
        let recordId = null;
        
        // Try to get data from form first
        const formElement = $('#accountsReceivableForm');
        
        if (formElement.length > 0) {
            recordData = formElement.data('record-data');
            recordId = formElement.data('id');
        }
        
        // If no data from form, try to get from modal
        if (!recordData) {
            recordData = $('#accountsReceivableModal').data('record-data');
            recordId = $('#accountsReceivableModal').data('id');
        }
        
        if (recordData && recordData.id) {
            // Hide the main modal first, then show payment modal after a short delay
            $('#accountsReceivableModal').modal('hide');
            
            // Wait for modal to hide completely before showing payment modal
            setTimeout(function() {
                showPaymentModal(recordData);
            }, 300);
        } else {
            Swal.fire('Error!', 'Unable to process payment. Please try again.', 'error');
        }
    });

    // Alternative direct binding for the button (backup approach)
    $('#accountsReceivableModal').on('shown.bs.modal', function() {
        $('#processPaymentBtn').off('click.payment').on('click.payment', function(e) {
            e.preventDefault();
            
            // Try to get data from form first, then modal
            let recordData = $('#accountsReceivableForm').data('record-data') || $('#accountsReceivableModal').data('record-data');
            
            if (recordData) {
                $('#accountsReceivableModal').modal('hide');
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
        const $btn = $(this);
        const $icon = $btn.find('.mdi');
        // Target only the direct label span, not the icon span inside .btnImg
        const $label = $btn.children('span');
        
        // Disable button and show loading state
        $btn.prop('disabled', true);
        $icon.removeClass('mdi-refresh').addClass('mdi-loading mdi-spin');
        $label.text('Refreshing...');
        
        // Show loading animation in table
        if (accountsReceivableTable) {
            accountsReceivableTable.clear().draw();
            const loadingRow = `<tr>
                <td colspan="11" class="text-center p-4">
                    <div class="d-flex justify-content-center align-items-center">
                        <i class="mdi mdi-loading mdi-spin me-2" style="font-size: 1.5rem;"></i>
                        <span>Refreshing accounts receivable data...</span>
                    </div>
                </td>
            </tr>`;
            $('#AccountsReceivableTable tbody').html(loadingRow);
        }
        
        // Make API call to refresh data
        $.ajax({
            url: '/api/accounts-receivable',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    accountsReceivableData = response.data;
                    filteredData = [...accountsReceivableData];
                    
                    // Re-apply current filters
                    applyFilters();
                    
                    // Update statistics
                    loadStatistics();
                    
                    // Show success message briefly
                    $label.text('Refreshed!');
                    setTimeout(() => {
                        $label.text('Refresh Data');
                    }, 1000);
                }
            },
            error: function(xhr) {
                console.error('Failed to refresh accounts receivable data:', xhr);
                
                // Restore original data on error
                if (accountsReceivableTable && filteredData.length > 0) {
                    accountsReceivableTable.clear();
                    accountsReceivableTable.rows.add(filteredData);
                    accountsReceivableTable.draw();
                }
                
                Swal.fire('Error!', 'Failed to refresh accounts receivable data.', 'error');
                $label.text('Refresh Data');
            },
            complete: function() {
                // Restore button state
                $btn.prop('disabled', false);
                $icon.removeClass('mdi-loading mdi-spin').addClass('mdi-refresh');
            }
        });
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
        
        // Helper function to clean UTF-8 text
        function cleanText(text) {
            if (!text) return '';
            // Clean problematic characters while preserving valid UTF-8
            return text.toString()
                .replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '') // Remove control characters but keep valid Unicode
                .replace(/\r\n|\r|\n/g, ' ') // Replace line breaks with spaces
                .trim();
        }
        
        // Create clean form data
        let formData = new FormData();
        
        // Add basic payment data with UTF-8 cleaning
        formData.set('payment_amount', rawAmount.toString()); // Ensure it's a string
        
        // Map frontend payment method to backend expected values
        let backendPaymentMethod;
        if (paymentMethod === 'bank' && $('#pay_by_check').is(':checked')) {
            backendPaymentMethod = 'check';
        } else if (paymentMethod === 'bank') {
            backendPaymentMethod = 'bank_transfer';
        } else if (paymentMethod === 'gcash') {
            backendPaymentMethod = 'gcash';
        } else {
            backendPaymentMethod = 'cash'; // Default to cash
        }
        
        formData.set('payment_method', backendPaymentMethod); // Backend expects 'payment_method'
        
        // Add current date as payment date (with fallback if moment.js not available)
        let currentDate;
        if (typeof moment !== 'undefined') {
            currentDate = moment().format('YYYY-MM-DD');
        } else {
            currentDate = new Date().toISOString().split('T')[0];
        }
        formData.set('payment_date', currentDate);
        
        // Add cleaned remarks if provided
        let remarksValue = $('#payment_remarks').val();
        if (remarksValue) {
            formData.set('remarks', cleanText(remarksValue));
        }
        
        // Add reference number if provided
        let referenceValue = $('#payment_reference_number').val();
        if (referenceValue) {
            formData.set('reference_number', cleanText(referenceValue));
        }
        
        // Add bank details if bank payment method is selected
        if (paymentMethod === 'bank') {
            const selectedBankId = $('#bank_selection').val();
            const bankData = $('#bank_selection').find('option:selected').data('bank');
            
            if (bankData) {
                formData.append('bank_id', selectedBankId.toString());
                formData.append('bank_name', cleanText(bankData.BankName || ''));
                formData.append('account_name', cleanText(bankData.AccountName || ''));
                formData.append('account_number', cleanText(bankData.AccountNumber || ''));
                formData.append('card_number', cleanText(bankData.CardNumber || ''));
                formData.append('expiration_date', cleanText(bankData.ExpirationDate || ''));
                
                // Add check details if check payment is selected
                if ($('#pay_by_check').is(':checked')) {
                    formData.append('pay_by_check', '1');
                    formData.append('check_payee', cleanText($('#check_payee').val() || ''));
                    formData.set('check_date', $('#check_date').val() || ''); // Use set instead of append for required field
                    formData.set('check_number', cleanText($('#check_number').val() || '')); // Use set instead of append for required field
                    formData.append('check_amount', rawAmount.toString());
                    formData.append('check_amount_in_words', cleanText($('#check_amount_in_words').val() || ''));
                }
            }
        }

        // Add GCash details if GCash payment method is selected
        if (paymentMethod === 'gcash') {
            const selectedGcashId = $('#gcash_selection').val();
            const gcashData = $('#gcash_selection').find('option:selected').data('gcash');
            
            if (gcashData) {
                formData.append('gcash_id', selectedGcashId.toString());
                formData.append('gcash_account_name', cleanText(gcashData.AccountName || ''));
                formData.append('gcash_account_number', cleanText(gcashData.AccountNumber || ''));
            }
        }
        
        // Confirm before submitting payment
        const formEl = $(this);
        let submitBtn = formEl.find('button[type="submit"]');
        let originalText = submitBtn.text();

        Swal.fire({
            icon: 'question',
            title: 'Confirm Payment',
            text: 'Are you sure you want to confirm this payment?',
            showCancelButton: true,
            confirmButtonText: 'Yes, confirm',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state and proceed with submission
                submitBtn.prop('disabled', true).text('Processing...');
                
                // Show loading banner (copied from Accounts Payable implementation)
                showPaymentLoadingBanner();
                // Disable submit button inside modal to prevent double submission
                $('#paymentModal button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: `/api/accounts-receivable/${id}/payment`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    cache: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json; charset=utf-8'
                    },
                    beforeSend: function(xhr) {
                        // Ensure UTF-8 encoding
                        xhr.overrideMimeType('application/json; charset=utf-8');
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success');
                            $('#paymentModal').modal('hide');
                            loadAccountsReceivableData(); // Reload data
                        }
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON;
                        let errors = response?.errors;
                        let errorMessage = response?.message || 'Please check your input.';

                        // Removed FIFO validation handling: backend no longer enforces payment order

                        if (errors) {
                            errorMessage = Object.values(errors).flat().join('\n');
                        }

                        Swal.fire('Error!', errorMessage, 'error');
                    },
                    complete: function() {
                        // Reset button state
                        submitBtn.prop('disabled', false).text(originalText);
                        $('#paymentModal button[type="submit"]').prop('disabled', false);
                        // Hide loading banner
                        hidePaymentLoadingBanner();
                    }
                });
            }
            // If cancelled, do nothing and keep the form untouched
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
        let enteredCents = Math.round(enteredAmount * 100);
        let balanceCents = Math.round(balanceAmount * 100);
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
        
        if (enteredCents === 0) {
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
        
        if (enteredCents >= balanceCents) {
            // Amount equals or exceeds balance - full payment (overpayment allowed)
            paymentTypeField.val('full');
            
            // Show overpayment info if amount exceeds balance
            if (enteredCents > balanceCents) {
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
                    amountField.parent().append('<span class="payment-status" style="position: absolute; top: -20px; right: 0; color: #28a745; font-size: 7px; font-weight: 700; text-transform: uppercase;">Full Payment</span>');
                } else {
                    var prevLabel = amountField.prev('label');
                    if (prevLabel.length) {
                        prevLabel.after('<span class="payment-status" style="padding-left: 90px; color: #28a745; font-size: 7px; font-weight: 700; text-transform: uppercase;">Full Payment</span>');
                    } else {
                        amountField.after('<span class="payment-status" style="margin-left: 8px; color: #28a745; font-size: 7px; font-weight: 700; text-transform: uppercase;">Full Payment</span>');
                    }
                }
            }
        } else if (enteredCents < balanceCents && enteredCents > 0) {
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

// Loading banner functions for payment submission (copied from Accounts Payable)
function showPaymentLoadingBanner() {
    // Remove any existing loading banner
    hidePaymentLoadingBanner();
    
    // Create loading banner HTML
    const loadingHTML = `
        <div id="paymentLoadingBanner" class="payment-loading-overlay">
            <div class="payment-loading-content">
                <div class="payment-loading-spinner"></div>
                <div class="payment-loading-text">
                    <h4>Processing Payment...</h4>
                    <p>Please wait while we process your payment request.</p>
                    <div class="payment-loading-dots">
                        <span>.</span><span>.</span><span>.</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add to body
    $('body').append(loadingHTML);
    
    // Add loading styles if not already present
    if (!$('#payment-loading-styles').length) {
        const loadingStyles = `
            <style id="payment-loading-styles">
                .payment-loading-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.7);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                    backdrop-filter: blur(3px);
                }
                
                .payment-loading-content {
                    background: white;
                    padding: 40px;
                    border-radius: 15px;
                    text-align: center;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    max-width: 400px;
                    width: 90%;
                }
                
                .payment-loading-spinner {
                    width: 60px;
                    height: 60px;
                    border: 4px solid #f3f3f3;
                    border-top: 4px solid #007bff;
                    border-radius: 50%;
                    animation: payment-spin 1s linear infinite;
                    margin: 0 auto 20px auto;
                }
                
                @keyframes payment-spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                .payment-loading-text h4 {
                    color: #333;
                    margin: 0 0 10px 0;
                    font-size: 24px;
                    font-weight: 600;
                }
                
                .payment-loading-text p {
                    color: #666;
                    margin: 0 0 15px 0;
                    font-size: 16px;
                }
                
                .payment-loading-dots {
                    font-size: 20px;
                    color: #007bff;
                }
                
                .payment-loading-dots span {
                    animation: payment-blink 1.4s infinite both;
                }
                
                .payment-loading-dots span:nth-child(2) {
                    animation-delay: 0.2s;
                }
                
                .payment-loading-dots span:nth-child(3) {
                    animation-delay: 0.4s;
                }
                
                @keyframes payment-blink {
                    0%, 80%, 100% { opacity: 0; }
                    40% { opacity: 1; }
                }
            </style>
        `;
        $('head').append(loadingStyles);
    }
}

function hidePaymentLoadingBanner() {
    $('#paymentLoadingBanner').fadeOut(300, function() {
        $(this).remove();
    });
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

function getStatusBadge(status, isOverdue = false, totalAmount = 0, balanceAmount = 0) {
    let badgeClass = '';
    let displayText = status;
    const total = parseFloat(totalAmount) || 0;
    const balance = parseFloat(balanceAmount) || 0;
    const isPartial = balance > 0 && balance < total;

    if (status === 'Settled' || balance <= 0) displayText = 'Fully Paid';
    else if (isPartial) displayText = 'Partial';
    else if (status === 'Outstanding') displayText = 'Pending';
    
    if ((status === 'Pending' || status === 'Outstanding') && isOverdue && !isPartial) {
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
            case 'Outstanding':
                badgeClass = isPartial ? 'statusBadge3' : 'statusBadge4';
                break;
            case 'Settled':
                badgeClass = 'statusBadge1';  // Green - Fully Paid
                break;
            case 'Credit Generated':
                badgeClass = 'statusBadge5';  // Purple - Credit Generated
                displayText = 'Credit Generated';
                break;
            case 'Credit Applied - Paid':
                badgeClass = 'statusBadge1';  // Green - Credit Applied and Paid
                displayText = 'Credit Applied - Paid';
                break;
            case 'Credit Applied - Partial':
                badgeClass = 'statusBadge6';  // Teal - Credit Applied but Partial
                displayText = 'Credit Applied - Partial';
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
