var MainTH, selectedMain;
var suppliersData = [];

$(document).ready(function() {
    // Load suppliers data using the same pattern as supplier maintenance
    datatables.loadSupplierData();

    // Row click handler for supplier transactions
    $("#supplierCreditTable").on("click", "tbody tr", function() {
        $("#supplierCreditTable tbody").css('pointer-events', 'none');
        const selectedSupplierCode = $(this).attr('id');
        
        if (selectedSupplierCode) {
            showSupplierTransactions(selectedSupplierCode);
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
    const creditMemo = summary ? summary.credit_memo : 0;

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

    // Set credit memo display
    const creditMemoElement = $('#creditMemoBalance');
    creditMemoElement.val('₱' + creditMemo.toLocaleString('en-US', {minimumFractionDigits: 2}));
    if (creditMemo > 0) {
        creditMemoElement.addClass('text-info');
    } else {
        creditMemoElement.removeClass('text-info');
    }

    // Populate transactions table
    const tbody = $('#transactionsTableBody');
    tbody.empty();

    if (transactions && transactions.length > 0) {
        $('#noTransactionsMessage').hide();
        
        // Sort all transactions by date and time (oldest first)
        const sortedTransactions = transactions.sort((a, b) => {
            const dateA = new Date(a.sort_date || a.date);
            const dateB = new Date(b.sort_date || b.date);
            return dateA - dateB; // Ascending order (oldest first)
        });
        
        let grandTotalAmount = 0;
        let grandTotalPaid = 0;
        let grandTotalBalance = 0;
        
        sortedTransactions.forEach(function(transaction) {
            // Format the date with time if available
            let formattedDate = 'N/A';
            if (transaction.date) {
                const transactionDate = new Date(transaction.date);
                formattedDate = transactionDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });
                
                // Add time if available in sort_date
                if (transaction.sort_date && transaction.sort_date !== transaction.date) {
                    const sortDate = new Date(transaction.sort_date);
                    const timeString = sortDate.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                    formattedDate += ` ${timeString}`;
                }
            }
            
            // Check transaction type
            const isPayment = transaction.type === 'payment';
            const isCreditMemo = transaction.type === 'credit_memo';
            const rowClass = isPayment ? 'table-info' : (isCreditMemo ? 'table-warning' : '');
            
            let amountDisplay, paidDisplay, balanceDisplay, descriptionText, statusBadge;
            
            if (isPayment) {
                // For payment rows
                if (transaction.reference_number && transaction.reference_number.startsWith('AUTO-CM-')) {
                    descriptionText = 'Auto Credit Memo Application';
                    statusBadge = '<span class="badge bg-warning">Credit Applied</span>';
                } else {
                    descriptionText = `Payment - ${transaction.payment_type || 'Cash'}`;
                    statusBadge = transaction.running_balance <= 0 ? 
                        '<span class="badge bg-success">Fully Paid</span>' : 
                        '<span class="badge bg-info">Payment Made</span>';
                }
                amountDisplay = '-'; // No original amount for payments
                paidDisplay = `₱${parseFloat(transaction.payment_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                balanceDisplay = `₱${parseFloat(transaction.running_balance).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                    
                // Add to grand totals
                grandTotalPaid += parseFloat(transaction.payment_amount);
            } else if (isCreditMemo) {
                // For credit memo rows
                descriptionText = `Credit Memo - Overpayment`;
                amountDisplay = '-'; // No original amount for credit memos
                paidDisplay = '-'; // No payment amount for credit memos
                balanceDisplay = `-₱${parseFloat(transaction.credit_memo_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                statusBadge = '<span class="badge bg-info">Credit Available</span>';
                
                // Credit memos don't affect grand totals as they're already counted in payments
            } else {
                // For original transaction rows
                descriptionText = `Invoice - ${transaction.reference_number || 'N/A'}`;
                const originalAmount = parseFloat(transaction.transaction_amount);
                amountDisplay = `₱${originalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                paidDisplay = '-'; // Will show cumulative in payments
                balanceDisplay = `₱${parseFloat(transaction.running_balance).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                
                // Status badge for original transactions
                if (transaction.status === 'Paid' || parseFloat(transaction.running_balance) <= 0) {
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
            const currentBalance = parseFloat(transaction.running_balance);
            const balanceColor = currentBalance > 0 ? '#dc3545' : '#28a745';
            
            // Determine row styling
            let rowStyle = '';
            if (isPayment) {
                rowStyle = 'font-style: italic;';
            } else if (isCreditMemo) {
                rowStyle = 'font-style: italic; background-color: rgba(255, 193, 7, 0.1);';
            }

            const row = `
                <tr class="${rowClass}" style="${rowStyle}">
                    <td>${formattedDate}</td>
                    <td>${descriptionText}</td>
                    <td>${transaction.rr_number || 'N/A'}</td>
                    <td class="text-end">${amountDisplay}</td>
                    <td class="text-end" style="color: ${isCreditMemo ? '#0dcaf0' : '#28a745'};">${paidDisplay}</td>
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

// Add download button click handler
$(document).ready(function() {
    $('#csvDLBtn').on('click', function () {
        downloadToCSV(suppliersData);
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

// Note: Payment functionality is handled in the Accounts Payable page
// This page is for viewing transaction history only

// Export functions for external access if needed
window.SupplierCreditModule = {
    showSupplierTransactions: showSupplierTransactions,
    suppliersData: suppliersData
};