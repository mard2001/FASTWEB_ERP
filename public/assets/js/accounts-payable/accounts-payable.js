let MainTH = null;
let jsonArr = [];
let isloading = false;
let currentEditId = null;

// Global variables
let accountsPayableData = [];
let filteredData = [];
let accountsPayableTable;
let statusFilterVS;

// Initialize page
$(document).ready(function() {
    initializeFilters();
    loadAccountsPayableData();
});

// Initialize Virtual Select filters
function initializeFilters() {
    // Initialize Status filter
    statusFilterVS = VirtualSelect.init({
        ele: '#statusFilter_VS',
        options: [
            { label: 'All Status', value: '' },
            { label: 'Outstanding', value: 'Outstanding' },
            { label: 'Settled', value: 'Settled' },
            { label: 'Credit Balance', value: 'Credit Balance' }
        ],
        placeholder: 'Select Status',
        search: false,
        multiple: false
    });

    // Set up event listeners for filters
    $('#dateFrom, #dateTo, #branchFilter').on('change keyup', function() {
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
                data: 'branch', 
                title: 'Branch',
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            { 
                data: 'opening_balance', 
                title: 'Opening Balance',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            },
            { 
                data: 'invoices', 
                title: 'Invoices',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            },
            { 
                data: 'debit_notes', 
                title: 'Debit Notes',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            },
            { 
                data: 'credit_notes', 
                title: 'Credit Notes',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            },
            { 
                data: 'adjustments', 
                title: 'Adjustments',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            },
            { 
                data: 'disbursements', 
                title: 'Disbursements',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            },
            { 
                data: 'closing_balance', 
                title: 'Closing Balance',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            },
            { 
                data: 'status', 
                title: 'Status',
                render: function(data, type, row) {
                    return getStatusBadge(data);
                }
            },
            { 
                data: 'report_date', 
                title: 'Report Date',
                render: function(data, type, row) {
                    return moment(data).format('MMM DD, YYYY');
                }
            }
        ],
        order: [[9, 'desc']], // Order by report_date descending
        pageLength: 25,
        responsive: true,
        createdRow: function(row, data, dataIndex) {
            $(row).addClass('clickable-row');
            $(row).attr('data-id', data.id);
            $(row).css('cursor', 'pointer');
            $(row).attr('title', 'Click to view/edit details');
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
    tableDiv.after('<div style="background: linear-gradient(to right, #1b438f, #33336F ); color: #FFF; margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px">Accounts Payable Report</p></div>');

    // Set up event handlers
    setupEventHandlers();
}

// Apply filters
function applyFilters() {
    let dateFrom = $('#dateFrom').val();
    let dateTo = $('#dateTo').val();
    let branchFilter = $('#branchFilter').val().toLowerCase();
    let statusFilter = statusFilterVS ? statusFilterVS.getSelectedOptions()[0]?.value || '' : '';

    filteredData = accountsPayableData.filter(function(item) {
        let matchesDate = true;
        let matchesBranch = true;
        let matchesStatus = true;

        // Date filter
        if (dateFrom || dateTo) {
            let itemDate = moment(item.report_date);
            if (dateFrom && itemDate.isBefore(moment(dateFrom))) {
                matchesDate = false;
            }
            if (dateTo && itemDate.isAfter(moment(dateTo))) {
                matchesDate = false;
            }
        }

        // Branch filter
        if (branchFilter && !item.branch.toLowerCase().includes(branchFilter)) {
            matchesBranch = false;
        }

        // Status filter
        if (statusFilter && item.status !== statusFilter) {
            matchesStatus = false;
        }

        return matchesDate && matchesBranch && matchesStatus;
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
    let totalOutstanding = 0;
    let totalInvoices = 0;
    let totalCredit = 0;
    let totalRecords = filteredData.length;

    filteredData.forEach(function(item) {
        if (item.status === 'Outstanding') {
            totalOutstanding += parseFloat(item.closing_balance || 0);
        }
        totalInvoices += parseFloat(item.invoices || 0);
        if (item.status === 'Credit Balance') {
            totalCredit += Math.abs(parseFloat(item.closing_balance || 0));
        }
    });

    $('#total-outstanding').text(formatCurrency(totalOutstanding));
    $('#total-invoices').text(formatCurrency(totalInvoices));
    $('#total-credit').text(formatCurrency(totalCredit));
    $('#total-records').text(totalRecords + ' Records');
}

// Setup event handlers
function setupEventHandlers() {
    // Add new record
    $(document).on('click', '.addNewBtn', function() {
        $('#accountsPayableForm')[0].reset();
        $('#accountsPayableForm').removeData('id');
        $('#accountsPayableModal').modal('show');
    });

    // Row click to edit record
    $(document).on('click', '.clickable-row', function() {
        let id = $(this).data('id');
        
        $.ajax({
            url: `/api/accounts-payable/${id}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let data = response.data;
                    $('#branch').val(data.branch);
                    $('#report_date').val(data.report_date);
                    $('#opening_balance').val(data.opening_balance);
                    $('#invoices').val(data.invoices);
                    $('#debit_notes').val(data.debit_notes);
                    $('#credit_notes').val(data.credit_notes);
                    $('#adjustments').val(data.adjustments);
                    $('#disbursements').val(data.disbursements);
                    $('#closing_balance').val(data.closing_balance);
                    
                    $('#accountsPayableForm').data('id', id);
                    $('#accountsPayableModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Failed to load record data.', 'error');
            }
        });
    });

    // Form submission
    $('#accountsPayableForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();
        let id = $(this).data('id');
        let url = id ? `/api/accounts-payable/${id}` : '/api/accounts-payable';
        let method = id ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#accountsPayableModal').modal('hide');
                    loadAccountsPayableData(); // Reload data
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = 'Please check your input.';
                
                if (errors) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                Swal.fire('Error!', errorMessage, 'error');
            }
        });
    });

    // Download functionality
    $(document).on('click', '.downloadBtn', function() {
        exportToCSV();
    });
}

// Helper functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount || 0);
}

function getStatusBadge(status) {
    let badgeClass = '';
    let displayText = status;
    
    switch(status) {
        case 'Outstanding':
            badgeClass = 'statusBadge2';  // Red - Outstanding
            break;
        case 'Settled':
            badgeClass = 'statusBadge1';  // Green - Settled
            break;
        case 'Credit Balance':
            badgeClass = 'statusBadge4';  // Blue - Credit Balance
            break;
        default:
            badgeClass = 'statusBadge3';  // Orange - Default
    }
    
    return `<span class="${badgeClass}">${displayText}</span>`;
}

function exportToCSV() {
    let csvContent = "data:text/csv;charset=utf-8,";
    
    // Add headers
    csvContent += "Branch,Opening Balance,Invoices,Debit Notes,Credit Notes,Adjustments,Disbursements,Closing Balance,Status,Report Date\n";
    
    // Add data rows
    filteredData.forEach(function(row) {
        let csvRow = [
            row.branch,
            row.opening_balance,
            row.invoices,
            row.debit_notes,
            row.credit_notes,
            row.adjustments,
            row.disbursements,
            row.closing_balance,
            row.status,
            moment(row.report_date).format('YYYY-MM-DD')
        ].join(",");
        csvContent += csvRow + "\n";
    });
    
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "tblAccountsPayable_" + moment().format('YYYY-MM-DD') + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}