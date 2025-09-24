var MainTH, selectedMain;
var suppliersData = [];

$(document).ready(function() {
    // Load suppliers data using the same pattern as supplier maintenance
    datatables.loadSupplierData();

    // Row click handler for supplier selection
    $("#supplierCreditTable").on("click", "tbody tr", function() {
        $("#supplierCreditTable tbody").css('pointer-events', 'none');
        const selectedSupplierCode = $(this).attr('id');
        
        if (selectedSupplierCode) {
            showPayableSelectionModal(selectedSupplierCode);
        }
        
        setTimeout(() => {
            $("#supplierCreditTable tbody").css('pointer-events', 'auto');
        }, 500);
    });
});

// AJAX function matching the supplier maintenance pattern
async function ajax(endpoint, method, data, successCallback = () => { }, errorCallback = () => { }) {
    return new Promise((resolve, reject) => {
        const ajaxConfig = {
            url: globalApi + endpoint,
            type: method,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                'Accept': 'application/json'
            },
            success: function (response) {
                successCallback(response);
                resolve(response);
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error Details:', {
                    url: globalApi + endpoint,
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    responseJSON: xhr.responseJSON
                });
                errorCallback(xhr, status, error);
                reject(xhr);
            }
        };

        // Handle data differently based on method
        if (method.toUpperCase() === 'POST' || method.toUpperCase() === 'PUT' || method.toUpperCase() === 'PATCH') {
            ajaxConfig.headers['Content-Type'] = 'application/json';
            ajaxConfig.data = data;
        } else {
            ajaxConfig.data = data;
        }

        $.ajax(ajaxConfig);
    });
}

// DataTables object matching the supplier maintenance pattern
const datatables = {
    loadSupplierData: async () => {
        const supplierData = await ajax('api/supplier-credit', 'GET', null, (response) => {
            suppliersData = response.data;
            datatables.initSupplierDatatable(response);
        }, (xhr, status, error) => {
            console.error('Error loading supplier credit data:', error);
            console.error('XHR Details:', xhr);
        });
    },
    
    initSupplierDatatable: (response) => {
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
                MainTH.order([1, 'asc']).draw(); // Order by Supplier Name column in ascending order
            } else {
                MainTH = $('#supplierCreditTable').DataTable({
                    data: response.data,
                    language: {
                        searchPlaceholder: "Search suppliers..."
                    },
                    order: [[1, 'asc']], // Order by Supplier Name column in ascending order
                    columns: [
                        {
                            data: null,
                            title: 'Supplier Code',
                            render: function(data, type, row){
                                if (!data) return '';
                                return `<strong>${row.SupplierCode}</strong>`;
                            }
                        },
                        {
                            data: null,
                            title: 'Supplier Name',
                            render: function(data, type, row){
                                if (!data) return '';
                                return `${row.SupplierName}`;
                            }
                        },
                        { 
                            data: null,
                            title: 'Total Credit',
                            render: function(data, type, row) {
                                const amount = parseFloat(row.total_credit) || 0;
                                return '₱' + amount.toLocaleString('en-US', {minimumFractionDigits: 2});
                            }
                        },
                        { 
                            data: null,
                            title: 'Paid',
                            render: function(data, type, row) {
                                const amount = parseFloat(row.total_paid) || 0;
                                return '₱' + amount.toLocaleString('en-US', {minimumFractionDigits: 2});
                            }
                        },
                        { 
                            data: null,
                            title: 'Balance',
                            render: function(data, type, row) {
                                const amount = parseFloat(row.balance) || 0;
                                const colorClass = amount > 0 ? 'text-danger' : 'text-success';
                                return `<span class="${colorClass}">₱${amount.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>`;
                            }
                        }
                    ],
                    columnDefs: [
                        { className: "text-start", targets: [ 0, 1 ] },
                        { className: "text-end", targets: [ 2, 3, 4 ] },
                        { className: "text-nowrap", targets: '_all' },
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    "createdRow": function (row, data) {
                        $(row).attr('id', data.SupplierCode);
                    },
                    "pageLength": 15,
                    "lengthChange": false,
                    drawCallback: function(settings) {
                        var api = this.api();
                        var rows = api.rows({ page: 'current' }).nodes();
                        
                        // Add IDs to rows for click handling
                        api.rows({ page: 'current' }).every(function() {
                            var data = this.data();
                            $(this.node()).attr('id', data.SupplierCode);
                        });
                    },
                    initComplete: function () {
                        $(this.api().table().container()).find('#dt-search-0').addClass('p-1 mx-0 dtsearchInput nofocus');
                        $(this.api().table().container()).find('.dt-search label').addClass('py-1 mx-0 dtsearchLabel').html('<span class="mdi mdi-magnify"></span>');
                        $(this.api().table().container()).find('.dt-layout-row').first().find('.dt-layout-cell').each(function() { this.style.setProperty('height', '38px', 'important'); });
                        $(this.api().table().container()).find('.dt-layout-table').removeClass('px-4');
                        $(this.api().table().container()).find('.dt-scroll-body').addClass('rmvBorder');
                        $(this.api().table().container()).find('.dt-layout-table').addClass('btmdtborder');

                        $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                        $('.loadingScreen').remove();
                        $('#dattableDiv').removeClass('opacity-0');

                        const tableDiv = $('.dt-layout-row').first();
                        tableDiv.after('<div style="background: linear-gradient(to right, var(--primary-color, #1b438f), var(--secondary-color, #33336F) ); color: var(--text-color-light, #FFF); margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px">Supplier Credit Information</p></div>');
                    }
                });
            }
        }
    }
};

// This function is now handled by datatables.loadSupplierData() above

async function showSupplierTransactions(supplierCode) {
    // Show loading state
    $('#supplierTransactionsModal').modal('show');
    $('#transactionsTableBody').html('<tr><td colspan="8" class="text-center">Loading transactions...</td></tr>');
    
    try {
        const response = await ajax(`api/supplier-credit/${supplierCode}/transactions`, 'GET', null);
        
        if (response.success) {
            populateModal(response.data);
        } else {
            showNotification('error', 'Failed to load transactions: ' + response.message);
            $('#supplierTransactionsModal').modal('hide');
        }
    } catch (xhr) {
        console.error('Error loading transactions:', xhr);
        let errorMessage = 'Error loading transaction data';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        }
        showNotification('error', errorMessage);
        $('#supplierTransactionsModal').modal('hide');
    }
}

async function showFilteredTransactions(supplierCode, selectedIds, startDate, endDate, selectedStatuses) {
    // Show loading state
    $('#supplierTransactionsModal').modal('show');
    $('#transactionsTableBody').html('<tr><td colspan="8" class="text-center">Loading filtered transactions...</td></tr>');
    
    try {
        const response = await ajax(`api/supplier-credit/${supplierCode}/transactions`, 'GET', null);
        
        if (response.success) {
            // Apply filters to the data
            const filteredData = applyFiltersToTransactionData(response.data, selectedIds, startDate, endDate, selectedStatuses);
            populateModal(filteredData);
        } else {
            showNotification('error', 'Failed to load transactions: ' + response.message);
            $('#supplierTransactionsModal').modal('hide');
        }
    } catch (xhr) {
        console.error('Error loading transactions:', xhr);
        let errorMessage = 'Error loading transaction data';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        }
        showNotification('error', errorMessage);
        $('#supplierTransactionsModal').modal('hide');
    }
}

function applyFiltersToTransactionData(data, selectedIds, startDate, endDate, selectedStatuses) {
    const { supplier, transactions, summary } = data;
    
    // Filter transactions based on selected payable IDs
    let filteredTransactions = transactions.filter(transaction => {
        // Include transaction if its parent transaction ID is in selected IDs
        return selectedIds.includes(transaction.parent_transaction_id?.toString());
    });
    
    // Apply date filter if provided
    if (startDate && endDate) {
        filteredTransactions = filteredTransactions.filter(transaction => {
            const transactionDate = new Date(transaction.date);
            const filterStartDate = new Date(startDate);
            const filterEndDate = new Date(endDate);
            return transactionDate >= filterStartDate && transactionDate <= filterEndDate;
        });
    }
    
    // Apply status filter if provided
    if (selectedStatuses && selectedStatuses.length > 0) {
        filteredTransactions = filteredTransactions.filter(transaction => {
            let status = 'pending';
            
            if (transaction.type === 'payment') {
                status = transaction.running_balance <= 0 ? 'paid' : 'partial';
            } else {
                // For original transactions, check the final status
                if (transaction.status === 'Paid' || transaction.running_balance <= 0) {
                    status = 'paid';
                } else if (transaction.running_balance < parseFloat(transaction.transaction_amount || 0)) {
                    status = 'partial';
                } else {
                    status = 'pending';
                }
            }
            
            return selectedStatuses.includes(status);
        });
    }
    
    // Recalculate summary for filtered data
    const filteredSummary = calculateSummaryFromTransactions(filteredTransactions);
    
    return {
        supplier,
        transactions: filteredTransactions,
        summary: filteredSummary
    };
}

function calculateSummaryFromTransactions(transactions) {
    let totalDebt = 0;
    let totalPaid = 0;
    
    transactions.forEach(transaction => {
        if (transaction.type === 'transaction') {
            totalDebt += parseFloat(transaction.transaction_amount || 0);
        } else if (transaction.type === 'payment') {
            totalPaid += parseFloat(transaction.payment_amount || 0);
        }
    });
    
    return {
        total_debt: totalDebt,
        total_paid: totalPaid,
        balance: totalDebt - totalPaid
    };
}

function populateModal(data) {
    const { supplier, transactions, summary } = data;

    // Populate supplier info
    $('#modalSupplierCode').val(supplier.code || '-');
    $('#modalSupplierName').val(supplier.name || '-');
    $('#modalContactPerson').val(supplier.contact_person || '-');
    $('#modalContactNo').val(supplier.contact_number || '-');

    // Use summary data from backend
    const totalCredit = summary ? summary.total_debt : 0;
    const totalPaid = summary ? summary.total_paid : 0;
    const balance = summary ? summary.balance : 0;

    $('#totalDebt').val('₱' + totalCredit.toLocaleString('en-US', {minimumFractionDigits: 2}));
    $('#totalPaid').val('₱' + totalPaid.toLocaleString('en-US', {minimumFractionDigits: 2}));
    
    // Set balance color and text
    const balanceElement = $('#balanceOwed');
    balanceElement.val('₱' + Math.abs(balance).toLocaleString('en-US', {minimumFractionDigits: 2}));
    balanceElement.removeClass('positive negative warning');
    if (balance > 0) {
        balanceElement.addClass('negative'); 
    } else if (balance < 0) {
        balanceElement.addClass('positive'); 
    }

    // Populate transactions table
    const tbody = $('#transactionsTableBody');
    tbody.empty();

    if (transactions && transactions.length > 0) {
        $('#noTransactionsMessage').hide();
        
        // Sort transactions by date and time in descending order (latest first)
        const sortedTransactions = transactions.sort((a, b) => {
            // First, try to sort by any available timestamp fields
            const timestampA = a.created_at || a.updated_at || a.payment_date || a.transaction_date || a.date;
            const timestampB = b.created_at || b.updated_at || b.payment_date || b.transaction_date || b.date;
            
            if (timestampA && timestampB) {
                const dateA = new Date(timestampA);
                const dateB = new Date(timestampB);
                
                // If timestamps are different, sort by them
                if (dateA.getTime() !== dateB.getTime()) {
                    return dateB.getTime() - dateA.getTime(); // Latest first
                }
            }
            
            // Parse main dates properly
            const dateA = new Date(a.date || '1900-01-01');
            const dateB = new Date(b.date || '1900-01-01');
            
            // If dates are the same, use multiple fallback criteria
            if (dateA.getTime() === dateB.getTime()) {
                // Priority: Payments should come before invoices on the same date (more recent activity)
                if (a.type !== b.type) {
                    if (a.type === 'payment' && b.type === 'transaction') return -1;
                    if (a.type === 'transaction' && b.type === 'payment') return 1;
                }
                
                // Then sort by any available ID in descending order (higher ID = more recent)
                const idA = parseInt(a.id || a.payment_id || a.transaction_id || a.parent_transaction_id || 0);
                const idB = parseInt(b.id || b.payment_id || b.transaction_id || b.parent_transaction_id || 0);
                return idB - idA;
            }
            
            return dateB.getTime() - dateA.getTime(); // Descending order by date
        });
        
        let grandTotalAmount = 0;
        let grandTotalPaid = 0;
        let grandTotalBalance = 0;
        
        // Calculate correct running balance for display order (newest first)
        // First, find the original invoice amount to start with
        const originalTransaction = sortedTransactions.find(t => t.type === 'transaction');
        let runningBalance = 0;
        if (originalTransaction) {
            runningBalance = parseFloat(originalTransaction.transaction_amount || 0);
        }
        
        // Calculate running balance by processing transactions in chronological order
        const chronologicalTransactions = [...sortedTransactions].reverse(); // Oldest first for calculation
        const balanceAtEachStep = {};
        let currentBalance = runningBalance;
        
        // Debug: Log the sorted transactions to see the order
        console.log('Original transactions:', transactions.length);
        console.log('Balance Calculation Debug:', {
            originalAmount: originalTransaction ? originalTransaction.transaction_amount : 'Not found',
            chronologicalOrder: chronologicalTransactions.map(t => ({
                date: t.date,
                type: t.type,
                amount: t.payment_amount || t.transaction_amount,
                original_balance: t.running_balance
            })),
            balanceAtEachStep: balanceAtEachStep
        });
        
        chronologicalTransactions.forEach((transaction, index) => {
            if (transaction.type === 'transaction') {
                // Original invoice
                currentBalance = parseFloat(transaction.transaction_amount || 0);
            } else if (transaction.type === 'payment') {
                // Payment - subtract from balance
                currentBalance -= parseFloat(transaction.payment_amount || 0);
            }
            
            // Store the balance after this transaction
            const transactionKey = `${transaction.date}_${transaction.type}_${transaction.payment_amount || transaction.transaction_amount}_${index}`;
            balanceAtEachStep[transactionKey] = currentBalance;
        });
        
        sortedTransactions.forEach(function(transaction, displayIndex) {
            // Format the date
            const formattedDate = transaction.date ? 
                new Date(transaction.date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                }) : 'N/A';
            
            // Check if this is a payment or original transaction
            const isPayment = transaction.type === 'payment';
            const rowClass = isPayment ? 'table-info' : '';
            
            // Calculate correct balance for this transaction
            const chronologicalIndex = chronologicalTransactions.findIndex(t => 
                t.date === transaction.date && 
                t.type === transaction.type && 
                (t.payment_amount || t.transaction_amount) === (transaction.payment_amount || transaction.transaction_amount)
            );
            const transactionKey = `${transaction.date}_${transaction.type}_${transaction.payment_amount || transaction.transaction_amount}_${chronologicalIndex}`;
            const correctBalance = balanceAtEachStep[transactionKey] || parseFloat(transaction.running_balance);
            
            let amountDisplay, paidDisplay, balanceDisplay, descriptionText, statusBadge;
            
            if (isPayment) {
                // For payment rows
                descriptionText = `Payment - ${transaction.payment_type || 'Cash'}`;
                amountDisplay = '-'; // No original amount for payments
                paidDisplay = `₱${parseFloat(transaction.payment_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                balanceDisplay = `₱${correctBalance.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                statusBadge = correctBalance <= 0 ? 
                    '<span class="badge bg-success">Fully Paid</span>' : 
                    '<span class="badge bg-info">Payment Made</span>';
                    
                // Add to grand totals
                grandTotalPaid += parseFloat(transaction.payment_amount);
            } else {
                // For original transaction rows
                descriptionText = `Invoice - ${transaction.reference_number || 'N/A'}`;
                const originalAmount = parseFloat(transaction.transaction_amount);
                amountDisplay = `₱${originalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                paidDisplay = '-'; // Will show cumulative in payments
                balanceDisplay = `₱${correctBalance.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                
                // Status badge for original transactions
                if (transaction.status === 'Paid' || correctBalance <= 0) {
                    statusBadge = '<span class="badge bg-success">Paid</span>';
                } else if (transaction.is_overdue) {
                    statusBadge = '<span class="badge bg-danger">Overdue</span>';
                } else {
                    statusBadge = '<span class="badge bg-warning">Pending</span>';
                }
                
                // Add to grand totals
                grandTotalAmount += originalAmount;
            }
            
            // Balance color
            const balanceColor = correctBalance > 0 ? '#dc3545' : '#28a745';
            
            const row = `
                <tr class="${rowClass}" style="${isPayment ? 'font-style: italic;' : ''}">
                    <td>${formattedDate}</td>
                    <td>${descriptionText}</td>
                    <td>${transaction.rr_number || 'N/A'}</td>
                    <td class="text-end">${amountDisplay}</td>
                    <td class="text-end" style="color: #28a745;">${paidDisplay}</td>
                    <td class="text-end" style="color: ${balanceColor};">${balanceDisplay}</td>
                    <td>${statusBadge}</td>
                    <td>${transaction.terms || 'N/A'}</td>
                </tr>
            `;
            tbody.append(row);
        });
        
        // Calculate final balance
        grandTotalBalance = grandTotalAmount - grandTotalPaid;
        
        // Add summary row
        const summaryRow = `
            <tr style="background-color: #f8f9fa; border-top: 2px solid #dee2e6; font-weight: bold;">
                <td colspan="3" class="text-end">TOTALS:</td>
                <td class="text-end" style="color: #dc3545;">₱${grandTotalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td class="text-end" style="color: #28a745;">₱${grandTotalPaid.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td class="text-end" style="color: ${grandTotalBalance >= 0 ? '#dc3545' : '#28a745'};">₱${grandTotalBalance.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td colspan="2"></td>
            </tr>
        `;
        tbody.append(summaryRow);
        
    } else {
        $('#noTransactionsMessage').show();
        tbody.html('<tr><td colspan="8" class="text-center text-muted">No transactions found for this supplier.</td></tr>');
    }
}

function showNotification(type, message) {
    // Create a simple notification system
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const notification = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    $('body').append(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}

// Download CSV functionality matching supplier maintenance pattern
function downloadToCSV(jsonArr){
    const csvData = Papa.unparse(jsonArr);
    var today = new Date().toISOString().split('T')[0];
    
    const blob = new Blob([csvData], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `SupplierCreditReport_${today}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Add download button click handler and selection modal handlers
$(document).ready(function() {
    $('#csvDLBtn').on('click', function () {
        downloadToCSV(suppliersData);
    });
    
    // Selection modal handlers - Radio button selection only
    
    // View selected transactions button
    $('#viewSelectedTransactions').on('click', function() {
        const selectedRadio = $('.payable-record-radio:checked');
        
        if (selectedRadio.length === 0) {
            showNotification('warning', 'Please select a payable record');
            return;
        }
        
        const selectedId = selectedRadio.val();
        
        // Get filter values
        const startDate = $('#filterStartDate').val();
        const endDate = $('#filterEndDate').val();
        
        // Close selection modal and show transactions modal with filtered data for single transaction
        $('#payableSelectionModal').modal('hide');
        showFilteredTransactions(selectedSupplierData.SupplierCode, [selectedId], startDate, endDate, null);
    });
    
    // Date filter change handlers
    $('#filterStartDate, #filterEndDate').on('change', function() {
        // Optionally re-filter the payable records table based on dates
        // For now, we'll just apply date filtering when showing transactions
    });
    
    // Print Statement of Account (all transactions)
    $('#printStatementBtn').on('click', function () {
        const supplierCode = $('#modalSupplierCode').val();
        if (supplierCode && supplierCode !== '-') {
            const printUrl = `${globalApi}api/supplier-credit/${supplierCode}/print-statement`;
            window.open(printUrl, '_blank');
        }
    });
    
    // Print Counter Receipt (pending transactions only)
    $('#printCounterReceiptBtn').on('click', function () {
        const supplierCode = $('#modalSupplierCode').val();
        if (supplierCode && supplierCode !== '-') {
            const printUrl = `${globalApi}api/supplier-credit/${supplierCode}/print-counter-receipt`;
            window.open(printUrl, '_blank');
        }
    });
});

// Payable Selection Modal Functions
let selectedSupplierData = null;
let allPayableRecords = [];

async function showPayableSelectionModal(supplierCode) {
    // Find supplier data
    selectedSupplierData = suppliersData.find(s => s.SupplierCode === supplierCode);
    
    if (!selectedSupplierData) {
        showNotification('error', 'Supplier not found');
        return;
    }
    
    // Populate supplier info
    $('#selectionSupplierName').val(selectedSupplierData.SupplierName);
    $('#selectionSupplierCode').val(selectedSupplierData.SupplierCode);
    
    // Set default date range (last 6 months)
    const endDate = new Date();
    const startDate = new Date();
    startDate.setMonth(startDate.getMonth() - 6);
    
    $('#filterStartDate').val(startDate.toISOString().split('T')[0]);
    $('#filterEndDate').val(endDate.toISOString().split('T')[0]);
    
    // Clear selection summary
    $('#selectedRecordInfo').val('No record selected');
    $('#selectedAmount').val('₱0.00');
    $('#selectedBalance').val('₱0.00');
    $('#viewSelectedTransactions').text('View Transaction History').prop('disabled', true);
    
    // Load payable records
    await loadPayableRecords(supplierCode);
    
    // Show the selection modal
    $('#payableSelectionModal').modal('show');
}

async function loadPayableRecords(supplierCode) {
    try {
        const response = await ajax(`api/supplier-credit/${supplierCode}/transactions`, 'GET', null);
        
        if (response.success) {
            allPayableRecords = response.data.transactions || [];
            populatePayableRecordsTable();
        } else {
            showNotification('error', 'Failed to load payable records: ' + response.message);
        }
    } catch (error) {
        console.error('Error loading payable records:', error);
        showNotification('error', 'Error loading payable records');
    }
}

function populatePayableRecordsTable() {
    const tbody = $('#payableRecordsTableBody');
    tbody.empty();
    
    // Filter to show only original transactions (not individual payments)
    const originalTransactions = allPayableRecords.filter(record => record.type === 'transaction');
    
    if (originalTransactions.length === 0) {
        tbody.append('<tr><td colspan="7" class="text-center text-muted">No payable records found</td></tr>');
        updateSelectionSummary();
        return;
    }
    
    // Sort transactions by date in descending order (latest first)
    const sortedOriginalTransactions = originalTransactions.sort((a, b) => {
        const dateA = new Date(a.date || '1900-01-01');
        const dateB = new Date(b.date || '1900-01-01');
        return dateB - dateA; // Descending order
    });
    
    sortedOriginalTransactions.forEach(function(record) {
        // Calculate total paid for this transaction
        const paymentsForTransaction = allPayableRecords.filter(t => 
            t.type === 'payment' && t.parent_transaction_id === record.parent_transaction_id
        );
        
        const totalPaid = paymentsForTransaction.reduce((sum, payment) => 
            sum + parseFloat(payment.payment_amount || 0), 0
        );
        
        const amount = parseFloat(record.transaction_amount || 0);
        const balance = amount - totalPaid;
        
        // Determine status
        let status = 'Pending';
        let statusClass = 'warning';
        if (balance <= 0) {
            status = 'Paid';
            statusClass = 'success';
        } else if (totalPaid > 0) {
            status = 'Partial';
            statusClass = 'info';
        } else if (record.is_overdue) {
            status = 'Overdue';
            statusClass = 'danger';
        }
        
        const formattedDate = record.date ? 
            new Date(record.date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            }) : 'N/A';
        
        const isFirst = tbody.children().length === 0; // Select first record by default
        
        const row = `
            <tr class="payable-record-row" style="cursor: pointer;">
                <td>
                    <input type="radio" class="form-check-input payable-record-radio" 
                           name="selectedPayableRecord"
                           value="${record.parent_transaction_id}" 
                           data-amount="${amount}"
                           data-balance="${balance}"
                           data-reference="${record.reference_number || 'N/A'}"
                           data-rr="${record.rr_number || 'N/A'}"
                           ${isFirst ? 'checked' : ''}>
                </td>
                <td>${formattedDate}</td>
                <td>${record.reference_number || 'N/A'}</td>
                <td>${record.rr_number || 'N/A'}</td>
                <td class="text-end">₱${amount.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td class="text-end">₱${balance.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td><span class="badge bg-${statusClass}">${status}</span></td>
            </tr>
        `;
        tbody.append(row);
    });
    
    // Update summary
    updateSelectionSummary();
    
    // Bind radio button change events
    $('.payable-record-radio').on('change', updateSelectionSummary);
    
    // Allow clicking entire row to select
    $('.payable-record-row').on('click', function(e) {
        if (e.target.type !== 'radio') {
            $(this).find('.payable-record-radio').prop('checked', true).trigger('change');
        }
    });
}

function updateSelectionSummary() {
    const selectedRadio = $('.payable-record-radio:checked');
    
    if (selectedRadio.length > 0) {
        const amount = parseFloat(selectedRadio.data('amount') || 0);
        const balance = parseFloat(selectedRadio.data('balance') || 0);
        const reference = selectedRadio.data('reference') || 'N/A';
        const rr = selectedRadio.data('rr') || 'N/A';
        
        $('#selectedRecordInfo').val(`${reference} (RR: ${rr})`);
        $('#selectedAmount').val('₱' + amount.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#selectedBalance').val('₱' + balance.toLocaleString('en-US', {minimumFractionDigits: 2}));
        
        // Update button text to show which record is selected
        $('#viewSelectedTransactions').text(`View History: ${reference}`);
        $('#viewSelectedTransactions').prop('disabled', false);
    } else {
        $('#selectedRecordInfo').val('No record selected');
        $('#selectedAmount').val('₱0.00');
        $('#selectedBalance').val('₱0.00');
        $('#viewSelectedTransactions').text('View Transaction History');
        $('#viewSelectedTransactions').prop('disabled', true);
    }
}

// Note: Payment functionality is handled in the Accounts Payable page
// This page is for viewing transaction history only

// Export functions for external access if needed
window.SupplierCreditModule = {
    showSupplierTransactions: showSupplierTransactions,
    suppliersData: suppliersData
};