let paymentHistoryData = [];
let filteredData = [];
let paymentHistoryTable;
let supplierFilterVS;
let paymentStatusFilterVS;
let filteredStartDate;
let filteredEndDate;
let suppliersData = [];

// Initialize page
$(document).ready(function() {
    initializeFilters();
    initDateRangePicker();
    loadSuppliersData();
    loadPaymentHistoryData();
});

// Load suppliers data for dropdown
function loadSuppliersData() {
    $.ajax({
        url: '/api/payment-history/suppliers',
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
                populateSupplierDropdowns();
            }
        },
        error: function(xhr) {
            console.error('Failed to load suppliers data:', xhr);
            suppliersData = [];
            populateSupplierDropdowns();
            
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
    if (typeof VirtualSelect === 'undefined') {
        console.error('VirtualSelect library is not loaded');
        return;
    }

    let supplierOptions = [{ label: 'All Suppliers', value: '' }];
    suppliersData.forEach(supplier => {
        supplierOptions.push({
            label: `${supplier.SupplierCode} - ${supplier.SupplierName}`,
            value: supplier.SupplierCode
        });
    });

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
}

// Initialize Virtual Select filters
function initializeFilters() {
    // Initialize Payment Status filter (Full/Partial)
    paymentStatusFilterVS = VirtualSelect.init({
        ele: '#paymentStatusFilter_VS',
        options: [
            { label: 'All Status', value: '' },
            { label: 'Full Payment', value: 'full' },
            { label: 'Partial Payment', value: 'partial' }
        ],
        placeholder: 'Select Payment Status',
        search: false,
        multiple: false
    });

    // Set up event listeners for filters
    document.querySelector('#paymentStatusFilter_VS').addEventListener('change', function() {
        applyFilters();
    });
}

// Load payment history data
function loadPaymentHistoryData() {
    $.ajax({
        url: '/api/payment-history',
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                paymentHistoryData = response.data;
                filteredData = [...paymentHistoryData];
                initPaymentHistoryDataTable();
                loadStatistics();
            }
        },
        error: function(xhr) {
            console.error('Failed to load payment history data:', xhr);
            Swal.fire('Error!', 'Failed to load payment history data.', 'error');
        }
    });
}

// Initialize DataTable
function initPaymentHistoryDataTable() {
    if (paymentHistoryTable) {
        paymentHistoryTable.clear();
        paymentHistoryTable.rows.add(filteredData);
        paymentHistoryTable.draw();
        return;
    }

    paymentHistoryTable = $('#PaymentHistoryTable').DataTable({
        data: filteredData,
        language: {
            searchPlaceholder: "Search here..."
        },
        columns: [
            { 
                data: 'payment_date', 
                title: 'Payment Date',
                render: function(data, type, row) {
                    if (type === 'sort') {
                        // For sorting, use the sort_timestamp for precise ordering
                        return row.sort_timestamp || 0;
                    }
                    return data ? moment(data).format('MMM DD, YYYY') : 'N/A';
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
                data: 'payment_type', 
                title: 'Payment Type',
                render: function(data, type, row) {
                    return data ? data.charAt(0).toUpperCase() + data.slice(1) : 'Cash';
                }
            },
            { 
                data: 'bank_name', 
                title: 'Bank Name',
                render: function(data, type, row) {
                    if (row.payment_type && row.payment_type.toLowerCase() === 'bank') {
                        return data || 'N/A';
                    } else {
                        return '<span class="text-muted">-</span>';
                    }
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
                data: 'invoice_amount', 
                title: 'Invoice Amount',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            },
            { 
                data: 'payment_amount', 
                title: 'Payment Amount',
                render: function(data, type, row) {
                    return `<span class="amount-positive">${formatCurrency(data)}</span>`;
                }
            },
            { 
                data: 'payment_status', 
                title: 'Payment Status',
                render: function(data, type, row) {
                    return getPaymentStatusBadge(data);
                }
            },
            { 
                data: 'process_by', 
                title: 'Process By',
                render: function(data, type, row) {
                    return data || 'System';
                }
            }
        ],
        order: [[0, 'desc']], // Order by payment date descending
        pageLength: 25,
        responsive: true,
        createdRow: function(row, data, dataIndex) {
            // Add classes based on payment type
            if (data.payment_type === 'full') {
                $(row).addClass('table-success');
            } else if (data.payment_type === 'partial') {
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
    tableDiv.after('<div style="background: linear-gradient(to right, var(--primary-color, #1b438f), var(--secondary-color, #33336F) ); color: var(--text-color-light, #FFF); margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px; color: var(--text-color-light, #FFF);">Payment History Management</p></div>');

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
        opens: 'left',
        drops: 'down',
        parentEl: '.filteringOptionDiv',
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
    let supplierFilter = supplierFilterVS ? supplierFilterVS.getSelectedOptions()[0]?.value || '' : '';
    let paymentStatusFilter = paymentStatusFilterVS ? paymentStatusFilterVS.getSelectedOptions()[0]?.value || '' : '';

    filteredData = paymentHistoryData.filter(function(item) {
        let matchesDate = true;
        let matchesSupplier = true;
        let matchesPaymentStatus = true;

        // Date filter
        if (filteredStartDate && filteredEndDate) {
            let itemDate = moment(item.payment_date);
            if (itemDate.isBefore(moment(filteredStartDate)) || itemDate.isAfter(moment(filteredEndDate))) {
                matchesDate = false;
            }
        }

        // Supplier filter
        if (supplierFilter && item.supplier_code !== supplierFilter) {
            matchesSupplier = false;
        }

        // Payment status filter
        if (paymentStatusFilter && item.payment_status !== paymentStatusFilter) {
            matchesPaymentStatus = false;
        }

        return matchesDate && matchesSupplier && matchesPaymentStatus;
    });

    // Update DataTable
    if (paymentHistoryTable) {
        paymentHistoryTable.clear();
        paymentHistoryTable.rows.add(filteredData);
        paymentHistoryTable.draw();
    }

    // Update statistics
    loadStatistics();
}

// Load statistics
function loadStatistics() {
    let totalToday = 0;
    let totalMonth = 0;
    let totalAmount = 0;
    let totalRecords = filteredData.length;

    let today = moment();
    let startOfMonth = moment().startOf('month');

    filteredData.forEach(function(item) {
        let paymentAmount = parseFloat(item.payment_amount || 0);
        totalAmount += paymentAmount;

        let paymentDate = moment(item.payment_date);
        
        // Today's payments
        if (paymentDate.isSame(today, 'day')) {
            totalToday += paymentAmount;
        }

        // This month's payments
        if (paymentDate.isSameOrAfter(startOfMonth)) {
            totalMonth += paymentAmount;
        }
    });

    $('#total-today').text(formatCurrency(totalToday));
    $('#total-month').text(formatCurrency(totalMonth));
    $('#total-amount').text(formatCurrency(totalAmount));
    $('#total-records').text(totalRecords + ' Records');
}

// Show payment history modal
function showPaymentHistoryModal(rowData) {
    // Fill modal with data
    fillPaymentHistoryModal(rowData);
    
    // Show the modal
    $('#paymentHistoryModal').modal('show');
}

// Fill modal with data
function fillPaymentHistoryModal(data) {
    $('#view_payment_date').val(data.payment_date || '');
    $('#view_supplier_code').val(data.supplier_code || 'N/A');
    $('#view_supplier_name').val(data.supplier_name || 'N/A');
    $('#view_rr_number').val(data.rr_number || 'N/A');
    $('#view_reference_number').val(data.reference_number || 'N/A');
    $('#view_invoice_date').val(data.invoice_date || '');
    $('#view_invoice_amount').val(formatCurrency(data.invoice_amount || 0));
    $('#view_payment_amount').val(formatCurrency(data.payment_amount || 0));
    $('#view_payment_status').val(data.payment_status ? (data.payment_status.charAt(0).toUpperCase() + data.payment_status.slice(1) + ' Payment') : 'Full Payment');
    $('#view_payment_type').val(data.payment_type ? (data.payment_type.charAt(0).toUpperCase() + data.payment_type.slice(1)) : 'Cash');
    
    // Show bank name only for bank payments
    if (data.payment_type && data.payment_type.toLowerCase() === 'bank') {
        $('#view_bank_name').val(data.bank_name || 'N/A');
    } else {
        $('#view_bank_name').val('-');
    }
    
    $('#view_terms').val(data.terms || 'N/A');
    $('#view_process_by').val(data.process_by || 'System');
    $('#view_remarks').val(data.remarks || '');
}

// Setup event handlers
function setupEventHandlers() {
    // Row click handler for viewing records
    $(document).on('click', '.clickable-row', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let rowData = paymentHistoryData.find(item => item.id == id);
        
        if (rowData) {
            showPaymentHistoryModal(rowData);
        }
    });

    // Print all table data functionality
    $('#phPrintAllBtn').on('click', function() {
        // Store the current filtered data for printing
        sessionStorage.setItem('printingPHData', JSON.stringify(filteredData));
        
        // For now, just show alert - implement printing later
        Swal.fire({
            title: 'Print Report',
            text: 'Print functionality will be implemented soon.',
            icon: 'info'
        });
    });

    // Download functionality
    $(document).on('click', '.downloadBtn', function() {
        exportToCSV();
    });

    // Add CSS for hover effect if not already added
    if (!$('#hover-style-ph').length) {
        $('<style id="hover-style-ph">')
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

function getPaymentStatusBadge(status) {
    if (status === 'full') {
        return `<span class="statusBadge1">Full Payment</span>`;
    } else if (status === 'partial') {
        return `<span class="statusBadge3">Partial Payment</span>`;
    } else {
        return `<span class="statusBadge1">Full Payment</span>`;
    }
}

function getTransactionTypeBadge(type) {
    if (type === 'Payable') {
        return `<span class="statusBadge4">Payable</span>`;
    } else if (type === 'Receivable') {
        return `<span class="statusBadge1">Receivable</span>`;
    } else {
        return `<span class="statusBadge4">Payable</span>`;
    }
}

function exportToCSV() {
    let csvContent = "data:text/csv;charset=utf-8,";
    
    // Add headers
    csvContent += "Payment Date,Supplier Code,Supplier Name,RR Number,Payment Type,Bank Name,Reference Number,Invoice Date,Invoice Amount,Payment Amount,Payment Status,Terms,Process By,Remarks\n";
    
    // Add data rows
    filteredData.forEach(function(row) {
        let csvRow = [
            row.payment_date || '',
            row.supplier_code || '',
            `"${row.supplier_name || ''}"`,
            row.rr_number || '',
            row.payment_type || 'cash',
            `"${(row.payment_type && row.payment_type.toLowerCase() === 'bank') ? (row.bank_name || 'N/A') : '-'}"`,
            row.reference_number || '',
            row.invoice_date || '',
            row.invoice_amount || '0',
            row.payment_amount || '0',
            row.payment_status || 'full',
            `"${row.terms || ''}"`,
            `"${row.process_by || 'System'}"`,
            `"${row.remarks || ''}"`
        ].join(",");
        csvContent += csvRow + "\n";
    });
    
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "PaymentHistory_" + moment().format('YYYY-MM-DD') + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
