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

// Initialize page
$(document).ready(function() {
    initializeFilters();
    initDateRangePicker();
    loadSuppliersData();
    loadAccountsPayableData();
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

// Initialize Virtual Select filters
function initializeFilters() {
    // Initialize Status filter
    statusFilterVS = VirtualSelect.init({
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

    // Set up event listeners for filters
    $('#rrNumberFilter').on('change keyup', function() {
        applyFilters();
    });

    document.querySelector('#statusFilter_VS').addEventListener('change', function() {
        applyFilters();
    });
}

// Load accounts Payable data
function loadAccountsPayableData() {
    $.ajax({
        url: '/api/accounts-payable',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                accountsPayableData = response.data;
                filteredData = [...accountsPayableData];
                initAccountsPayableDataTable();
                loadStatistics();
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
        accountsPayableTable.clear();
        accountsPayableTable.rows.add(filteredData);
        accountsPayableTable.draw();
        return;
    }

    accountsPayableTable = $('#AccountsPayableTable').DataTable({
        data: filteredData,
        language: {
            searchPlaceholder: "Search here..."
        },
        columns: [
            { 
                data: 'date', 
                title: 'Date',
                render: function(data, type, row) {
                    if (type === 'sort') {
                        // For sorting, use the sort_timestamp for precise ordering
                        return row.sort_timestamp || 0;
                    }
                    return moment(data).format('MMM DD, YYYY');
                }
            },
            { 
                data: 'supplier_name', 
                title: 'Supplier',
                render: function(data, type, row) {
                    return `<div class="supplier-info">
                                <div class="fw-bold">${data}</div>
                                <small class="text-muted">${row.supplier_code}</small>
                            </div>`;
                }
            },
            { 
                data: 'rr_number', 
                title: 'RR #',
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            { 
                data: 'reference_number', 
                title: 'Reference #',
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            { 
                data: 'total_amount', 
                title: 'Total Amount',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            },
            { 
                data: 'terms', 
                title: 'Terms',
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            { 
                data: 'status', 
                title: 'Status',
                render: function(data, type, row) {
                    return getStatusBadge(data, row.is_overdue);
                }
            },
            { 
                data: 'balance_amount', 
                title: 'Balance',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            },
            { 
                data: 'process_by', 
                title: 'Process By',
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            }
        ],
        order: [[0, 'desc']], // Order by date descending
        pageLength: 25,
        responsive: true,
        createdRow: function(row, data, dataIndex) {
            // Add classes based on status
            if (data.status === 'Pending' && data.is_overdue) {
                $(row).addClass('table-danger');
            } else if (data.status === 'Paid') {
                $(row).addClass('table-success');
            } else if (data.status === 'Partial') {
                $(row).addClass('table-warning');
            }
            
            // Make row clickable and add hover effect
            $(row).addClass('clickable-row');
            $(row).attr('data-id', data.id);
            $(row).css({
                'cursor': 'pointer',
                'transition': 'background-color 0.2s ease'
            });
            
            // Add hover effect
            $(row).hover(
                function() { $(this).addClass('table-hover-effect'); },
                function() { $(this).removeClass('table-hover-effect'); }
            );
        },
        initComplete: function () {
            $(this.api().table().container()).find('#dt-search-0').addClass('p-1 mx-0 dtsearchInput nofocus');
            $(this.api().table().container()).find('.dt-search label').addClass('py-1 px-3 mx-0 dtsearchLabel').html('<span class="mdi mdi-magnify"></span>');
            $(this.api().table().container()).find('.dt-layout-row').first().find('.dt-layout-cell').each(function() { this.style.setProperty('height', '38px', 'important'); });
            $(this.api().table().container()).find('.dt-layout-table').removeClass('px-4');
            $(this.api().table().container()).find('.dt-scroll-body').addClass('rmvBorder');
            $(this.api().table().container()).find('.dt-layout-table').addClass('btmdtborder');
            $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
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
    let rrNumberFilter = $('#rrNumberFilter').val().toLowerCase();
    let statusFilter = statusFilterVS ? statusFilterVS.getSelectedOptions()[0]?.value || '' : '';
    let supplierFilter = supplierFilterVS ? supplierFilterVS.getSelectedOptions()[0]?.value || '' : '';

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

        // RR Number filter
        if (rrNumberFilter && !item.rr_number.toLowerCase().includes(rrNumberFilter)) {
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
    // First, hide all buttons to prevent the glitch
    $('#processPaymentBtn, #deleteAPBtn, #saveAPBtn, #editAPBtn, #cancelEditAPBtn').hide();
    
    // Fill modal with data first
    fillAccountsPayableModal(rowData);
    
    // Set modal mode (this will show/hide the correct buttons)
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
    $('#total_amount').val(data.total_amount || '');
    $('#terms').val(data.terms || '');
    $('#status').val(data.status || '');
    $('#balance_amount').val(data.balance_amount || '');
    $('#remarks').val(data.remarks || '');
    
    // Set supplier in Virtual Select
    const supplierVS = document.querySelector('#supplier_code_VS');
    if (supplierVS && supplierVS.setValue && data.supplier_code) {
        supplierVS.setValue(data.supplier_code);
    }
}

// Set modal mode (view/edit)
function setModalMode(mode, status) {
    const isPending = status === 'Pending' || status === 'Partial';
    const isEdit = mode === 'edit';
    
    // Enable/disable form fields based on mode
    $('#accountsPayableForm input, #accountsPayableForm textarea').prop('disabled', !isEdit);
    $('#status, #balance_amount').prop('disabled', true); // Always disabled
    
    // Show/hide buttons based on mode and status
    $('#processPaymentBtn').toggle(isPending && !isEdit);
    $('#deleteAPBtn').toggle(!isEdit);
    $('#saveAPBtn').toggle(isEdit);
    $('#editAPBtn').toggle(!isEdit);
    $('#cancelEditAPBtn').toggle(isEdit);
    
    // Update modal title
    const titleElement = $('#accountsPayableModal .modalHeaderTitle');
    if (isEdit) {
        titleElement.text('EDIT ACCOUNTS PAYABLE RECORD');
    } else {
        titleElement.text('ACCOUNTS PAYABLE RECORD');
    }
}

// Show payment modal
function showPaymentModal(rowData) {
    $('#payment_ap_id').val(rowData.id);
    $('#payment_total_amount').text(formatCurrency(rowData.total_amount));
    $('#payment_balance_amount').text(formatCurrency(rowData.balance_amount));
    $('#payment_amount').attr('max', rowData.balance_amount);
    $('#payment_amount').val('');
    $('#payment_type').val('full');
    $('#payment_type_indicator').removeClass('bg-warning bg-success').addClass('bg-success').text('Full Payment');
    $('#payment_remarks').val('');
    
    // Store balance amount for validation
    $('#payment_amount').data('balance-amount', rowData.balance_amount);
    
    $('#paymentModal').modal('show');
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
                $('#total_amount').val(data.total_amount);
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
    $(document).on('click', '#editAPBtn', function() {
        setModalMode('edit', $('#status').val());
    });

    $(document).on('click', '#cancelEditAPBtn', function() {
        // Try to get record data from form first, then modal
        let recordData = $('#accountsPayableForm').data('record-data') || $('#accountsPayableModal').data('record-data');
        
        if (recordData) {
            fillAccountsPayableModal(recordData);
            setModalMode('view', recordData.status);
        }
    });

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

    $(document).on('click', '#deleteAPBtn', function() {
        const id = $('#accountsPayableForm').data('id');
        if (id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/api/accounts-payable/${id}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success');
                                $('#accountsPayableModal').modal('hide');
                                loadAccountsPayableData();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'Failed to delete record.', 'error');
                        }
                    });
                }
            });
        }
    });

    $(document).on('click', '#saveAPBtn', function() {
        $('#accountsPayableForm').submit();
    });

    // Add new record
    $(document).on('click', '.addNewBtn', function() {
        // Hide all buttons first to prevent glitch
        $('#processPaymentBtn, #deleteAPBtn, #saveAPBtn, #editAPBtn, #cancelEditAPBtn').hide();
        
        // Clear form
        $('#accountsPayableForm')[0].reset();
        $('#accountsPayableForm').removeData('id');
        $('#accountsPayableForm').removeData('record-data');
        
        // Reset Virtual Select
        const supplierVS = document.querySelector('#supplier_code_VS');
        if (supplierVS && supplierVS.reset) {
            supplierVS.reset();
        }
        
        // Set to edit mode for new record (this will show correct buttons)
        setModalMode('edit', 'Pending');
        $('#accountsPayableModal .modalHeaderTitle').text('ADD NEW ACCOUNTS PAYABLE RECORD');
        
        // Set default date to today
        $('#date').val(moment().format('YYYY-MM-DD'));
        
        $('#accountsPayableModal').modal('show');
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

    // Form submission
    $('#accountsPayableForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get supplier code from Virtual Select
        const supplierVS = document.querySelector('#supplier_code_VS');
        let supplierCode = '';
        
        if (supplierVS && supplierVS.value !== undefined) {
            supplierCode = supplierVS.value;
        }
        
        if (!supplierCode) {
            Swal.fire('Error!', 'Please select a supplier.', 'error');
            return;
        }
        
        let formData = new FormData(this);
        formData.append('supplier_code', supplierCode);
        
        let id = $(this).data('id');
        let url = id ? `/api/accounts-payable/${id}` : '/api/accounts-payable';
        let method = id ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#accountsPayableModal').modal('hide');
                    loadAccountsPayableData(); // Reload data
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                let errorMessage = 'Please check your input.';
                
                if (errors) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                Swal.fire('Error!', errorMessage, 'error');
            }
        });
    });

    // Payment form submission
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        
        let id = $('#payment_ap_id').val();
        let rawAmount = $('#payment_amount').val().replace(/,/g, ''); // Remove commas for calculation
        let enteredAmount = parseFloat(rawAmount) || 0;
        let balanceAmount = parseFloat($('#payment_amount').data('balance-amount')) || 0;
        let paymentType = $('#payment_type').val();
        
        // Final validation before submission
        if (enteredAmount <= 0) {
            Swal.fire('Error!', 'Please enter a valid payment amount.', 'error');
            return;
        }
        
        if (enteredAmount > balanceAmount) {
            Swal.fire('Error!', 'Payment amount cannot exceed the current balance of ' + formatCurrency(balanceAmount) + '.', 'error');
            return;
        }
        
        if (!paymentType) {
            Swal.fire('Error!', 'Invalid payment amount detected.', 'error');
            return;
        }
        
        // Create form data and set the raw amount (without commas)
        let formData = new FormData(this);
        formData.set('payment_amount', rawAmount); // Send raw number without commas
        
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

    // Payment amount change handler - automated payment type detection
    $('#payment_amount').on('input keyup change', function() {
        let rawValue = $(this).val().replace(/,/g, ''); // Remove existing commas
        let enteredAmount = parseFloat(rawValue) || 0;
        let balanceAmount = parseFloat($(this).data('balance-amount')) || 0;
        let paymentTypeField = $('#payment_type');
        let indicator = $('#payment_type_indicator');
        let amountField = $(this);
        
        // Format the input value with commas
        if (rawValue && !isNaN(rawValue)) {
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
        
        // Remove any existing validation classes
        amountField.removeClass('is-invalid is-valid');
        
        if (enteredAmount === 0) {
            // No amount entered yet
            paymentTypeField.val('full');
            indicator.removeClass('bg-warning bg-success bg-danger').addClass('bg-success').text('Full Payment');
            return;
        }
        
        if (enteredAmount > balanceAmount) {
            // Amount exceeds balance - not allowed
            paymentTypeField.val('');
            indicator.removeClass('bg-warning bg-success').addClass('bg-danger').text('Invalid Amount');
            amountField.addClass('is-invalid');
            
            // Show tooltip or error message
            if (!amountField.next('.invalid-feedback').length) {
                amountField.after('<div class="invalid-feedback">Amount cannot exceed current balance of ' + formatCurrency(balanceAmount) + '</div>');
            }
        } else if (enteredAmount === balanceAmount) {
            // Exact amount - full payment
            paymentTypeField.val('full');
            indicator.removeClass('bg-warning bg-danger').addClass('bg-success').text('Full Payment');
            amountField.addClass('is-valid').next('.invalid-feedback').remove();
        } else if (enteredAmount < balanceAmount && enteredAmount > 0) {
            // Less than balance - partial payment
            paymentTypeField.val('partial');
            indicator.removeClass('bg-success bg-danger').addClass('bg-warning').text('Partial Payment');
            amountField.addClass('is-valid').next('.invalid-feedback').remove();
        }
    });

    // Reset payment modal when closed
    $('#paymentModal').on('hidden.bs.modal', function() {
        $('#payment_amount').removeClass('is-invalid is-valid').next('.invalid-feedback').remove();
        $('#payment_type_indicator').removeClass('bg-warning bg-danger').addClass('bg-success').text('Full Payment');
        $(this).find('form')[0].reset();
    });

    // Add CSS for hover effect if not already added
    if (!$('#hover-style').length) {
        $('<style id="hover-style">')
            .prop('type', 'text/css')
            .html(`
                .table-hover-effect {
                    background-color: #f8f9fa !important;
                    transition: background-color 0.2s ease;
                }
                .clickable-row:hover {
                    background-color: #e9ecef !important;
                }
            `)
            .appendTo('head');
    }
}

// Helper functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount || 0);
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