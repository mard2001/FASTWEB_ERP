var MainTH, selectedMain;
var customersData = [];

$(document).ready(function() {
    // Ensure loading screen is visible initially
    $('.loadingScreen').show();
    $('#dattableDiv').addClass('opacity-0');
    
    // Small delay to ensure DOM is ready and loading screen shows
    setTimeout(() => {
        // Load customers data using the same pattern as supplier maintenance
        datatables.loadCustomerData();
    }, 100);

    // Row click handler for customer transactions
    $("#customerCreditTable").on("click", "tbody tr", function() {
        const $row = $(this);
        const selectedCustomerCode = $row.attr('id');
        
        if (selectedCustomerCode && !$row.hasClass('loading')) {
            // Add loading state to clicked row
            $row.addClass('loading').css({
                'background-color': 'rgba(13, 110, 253, 0.1)',
                'pointer-events': 'none'
            });
            
            // Add loading indicator to the row
            const originalContent = $row.html();
            const loadingHtml = `
                <td colspan="7" class="text-center py-2">
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="text-muted">Loading customer details...</span>
                    </div>
                </td>
            `;
            $row.html(loadingHtml);
            
            // Disable all row clicks temporarily
            $("#customerCreditTable tbody").css('pointer-events', 'none');
            
            showCustomerTransactions(selectedCustomerCode).finally(() => {
                // Restore row and enable clicks after loading
                setTimeout(() => {
                    $row.html(originalContent).removeClass('loading').css({
                        'background-color': '',
                        'pointer-events': ''
                    });
                    $("#customerCreditTable tbody").css('pointer-events', 'auto');
                }, 300);
            });
        }
    });

    // Refresh data handler
    $('#ccRefreshBtn').on('click', function() {
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        // Show loading state
        $btn.prop('disabled', true).html(`
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span>Refreshing...</span>
            </div>
        `);
        
        // Show loading animation inside the table
        if (MainTH) {
            // Clear current data and add a single loading row
            MainTH.clear().draw();
            
            // Insert loading HTML directly into the table body
            $('#customerCreditTable tbody').html(`
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="text-muted">Refreshing customer credit data...</div>
                            <small class="text-muted">Applying auto credit memos...</small>
                        </div>
                    </td>
                </tr>
            `);
        }
        
        // Clear current data 
        customersData = [];
        
        // First, trigger auto credit memo application to ensure data is synchronized
        ajax('api/accounts-receivable/apply-auto-credit-memos', 'POST', null,
            (autoCreditResponse) => {
                console.log('Auto credit memos applied:', autoCreditResponse);
                
                // Then refresh the customer credit data
                ajax('api/customer-credit', 'GET', null, 
                    (response) => {
                        // Success callback
                        customersData = response.data;
                        datatables.initCustomerDatatable(response);
                        
                        showNotification('success', 'Customer credit data refreshed and sync successfully');
                        
                        // Restore button after short delay
                        setTimeout(() => {
                            $btn.prop('disabled', false).html(originalHtml);
                        }, 500);
                    }, 
                    (xhr, status, error) => {
                        // Error callback for customer credit refresh
                        console.error('Error refreshing customer credit data:', error);
                        showNotification('error', 'Failed to refresh customer credit data');
                        
                        // Show error message in table
                        if (MainTH) {
                            $('#customerCreditTable tbody').html(`
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-danger">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="mdi mdi-alert-circle-outline mb-2" style="font-size: 2rem;"></i>
                                            <div>Failed to refresh customer credit data</div>
                                            <small class="text-muted mt-1">Please try again or contact support</small>
                                        </div>
                                    </td>
                                </tr>
                            `);
                        }
                        
                        // Restore button on error
                        setTimeout(() => {
                            $btn.prop('disabled', false).html(originalHtml);
                        }, 500);
                    }
                );
            },
            (xhr, status, error) => {
                // Error callback for auto credit memo application
                console.warn('Auto credit memo application failed, proceeding with refresh:', error);
                
                // Still try to refresh the data even if auto credit memo fails
                ajax('api/customer-credit', 'GET', null, 
                    (response) => {
                        customersData = response.data;
                        datatables.initCustomerDatatable(response);
                        
                        showNotification('warning', 'Customer credit data refreshed (auto credit memo sync may have failed)');
                        
                        setTimeout(() => {
                            $btn.prop('disabled', false).html(originalHtml);
                        }, 500);
                    }, 
                    (xhr, status, error) => {
                        console.error('Error refreshing customer credit data:', error);
                        showNotification('error', 'Failed to refresh customer credit data');
                        
                        if (MainTH) {
                            $('#customerCreditTable tbody').html(`
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-danger">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="mdi mdi-alert-circle-outline mb-2" style="font-size: 2rem;"></i>
                                            <div>Failed to refresh customer credit data</div>
                                            <small class="text-muted mt-1">Please try again or contact support</small>
                                        </div>
                                    </td>
                                </tr>
                            `);
                        }
                        
                        setTimeout(() => {
                            $btn.prop('disabled', false).html(originalHtml);
                        }, 500);
                    }
                );
            }
        );
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

// Currency formatting helpers to avoid displaying -0.00
function normalizeZero(n) {
    const num = parseFloat(n) || 0;
    const EPS = 1e-6; // clamp tiny values to 0
    return Math.abs(num) < EPS ? 0 : num;
}

function formatPeso(n) {
    const v = normalizeZero(n);
    return '₱' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatPesoAbs(n) {
    const v = normalizeZero(Math.abs(parseFloat(n) || 0));
    return '₱' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// DataTables object matching the supplier maintenance pattern
const datatables = {
    loadCustomerData: async () => {
        // Show enhanced loading message
        $('.loadingScreen .loader').after('<div class="mt-3 text-center"><small class="text-muted">Loading customer credit data...</small></div>');
        
        try {
            // First, ensure auto credit memos are applied for data synchronization
            try {
                await ajax('api/accounts-receivable/apply-auto-credit-memos', 'POST', null);
                console.log('Auto credit memos applied during initial load');
            } catch (autoCreditError) {
                console.warn('Auto credit memo application failed during initial load, proceeding with data load:', autoCreditError);
            }
            
            // Then load the customer credit data
            const customerData = await ajax('api/customer-credit', 'GET', null, (response) => {
                customersData = response.data;
                datatables.initCustomerDatatable(response);
            }, (xhr, status, error) => {
                console.error('Error loading customer credit data:', error);
                console.error('XHR Details:', xhr);
                // Hide loading screen on error
                $('.loadingScreen').remove();
                $('#dattableDiv').removeClass('opacity-0').html(
                    '<div class="alert alert-danger m-3"><i class="mdi mdi-alert-circle me-2"></i>Failed to load customer credit data. Please refresh the page.</div>'
                );
                showNotification('error', 'Failed to load customer credit data');
            });
        } catch (error) {
            console.error('Error in loadCustomerData:', error);
            $('.loadingScreen').remove();
            $('#dattableDiv').removeClass('opacity-0').html(
                '<div class="alert alert-danger m-3"><i class="mdi mdi-alert-circle me-2"></i>Failed to load customer credit data. Please refresh the page.</div>'
            );
            showNotification('error', 'Failed to load customer credit data');
        }
    },
    
    initCustomerDatatable: (response) => {
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
                MainTH.order([2, 'asc']).draw(); // Order by Total Credit column in ascending order (latest/largest at bottom)
            } else {
                MainTH = $('#customerCreditTable').DataTable({
                    data: response.data,
                    language: {
                        searchPlaceholder: "Search customers..."
                    },
                    order: [[2, 'asc']], // Order by Total Credit column in ascending order (latest/largest at bottom)
                    columns: [
                        {
                            data: null,
                            title: 'Customer Code',
                            render: function(data, type, row){
                                if (!data) return '';
                                return `<strong>${row.CustomerCode}</strong>`;
                            }
                        },
                        {
                            data: null,
                            title: 'Customer Name',
                            render: function(data, type, row){
                                if (!data) return '';
                                return `${row.CustomerName}`;
                            }
                        },
                        { 
                            data: null,
                            title: 'Total Credit',
                            render: function(data, type, row) {
                                const amount = parseFloat(row.total_credit) || 0;
                                return formatPeso(amount);
                            }
                        },
                        { 
                            data: null,
                            title: 'Paid',
                            render: function(data, type, row) {
                                const amount = parseFloat(row.total_paid) || 0;
                                return formatPeso(amount);
                            }
                        },
                        { 
                            data: null,
                            title: 'Balance',
                            render: function(data, type, row) {
                                const amount = parseFloat(row.balance) || 0;
                                const colorClass = amount > 0 ? 'text-danger' : 'text-success';
                                return `<span class="${colorClass}">${formatPeso(amount)}</span>`;
                            }
                        },
                        { 
                            data: null,
                            title: 'Credit Limit',
                            render: function(data, type, row) {
                                const amount = parseFloat(row.credit_limit) || 0;
                                if (amount === 0) {
                                    return '<span class="text-muted">No Limit</span>';
                                }
                                return formatPeso(amount);
                            }
                        },
                        { 
                            data: null,
                            title: 'Credit Balance',
                            render: function(data, type, row) {
                                const creditLimit = parseFloat(row.credit_limit) || 0;
                                const creditBalance = parseFloat(row.credit_balance) || 0;
                                
                                if (creditLimit === 0) {
                                    return '<span class="text-muted">No Limit</span>';
                                }
                                
                                const colorClass = creditBalance < 0 ? 'text-danger' : 'text-success';
                                return `<span class="${colorClass}">${formatPeso(creditBalance)}</span>`;
                            }
                        }
                    ],
                    columnDefs: [
                        { className: "text-start", targets: [ 0, 1 ] },
                        { className: "text-end", targets: [ 2, 3, 4, 5, 6 ] },
                        { className: "text-nowrap", targets: '_all' },
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    "createdRow": function (row, data) {
                        $(row).attr('id', data.CustomerCode);
                    },
                    "pageLength": 15,
                    "lengthChange": false,
                    drawCallback: function(settings) {
                        var api = this.api();
                        var rows = api.rows({ page: 'current' }).nodes();
                        
                        // Add IDs to rows for click handling
                        api.rows({ page: 'current' }).every(function() {
                            var data = this.data();
                            $(this.node()).attr('id', data.CustomerCode);
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
                    }
                });
                
                // Hide loading screen and show table (matching accounts payable pattern)
                $('.loadingScreen').remove();
                $('#dattableDiv').removeClass('opacity-0');

                const tableDiv = $('.dt-layout-row').first();
                tableDiv.after('<div style="background: linear-gradient(to right, var(--primary-color, #1b438f), var(--secondary-color, #33336F) ); color: var(--text-color-light, #FFF); margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px">Customer Credit Information</p></div>');
            }
        }
    }
};

// This function is now handled by datatables.loadCustomerData() above

async function showCustomerTransactions(customerCode) {
    // Show loading state
    $('#customerTransactionsModal').modal('show');
    
    // Enhanced loading animation for modal
    const loadingContent = `
        <tr>
            <td colspan="8" class="text-center py-4">
                <div class="d-flex flex-column align-items-center">
                    <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="text-muted">Applying auto credit memos and loading history...</div>
                    <small class="text-muted mt-1">Please wait while we sync and fetch data</small>
                </div>
            </td>
        </tr>
    `;
    
    $('#transactionsTableBody').html(loadingContent);
    
    // Clear previous data while loading
    $('#modalCustomerCode, #modalCustomerName, #modalContactPerson, #modalContactNo').val('Loading...');
    $('#totalDebt, #totalPaid, #balanceOwed, #creditMemoBalance').val('Loading...');
    
    try {
        // Ensure auto credit memos are applied for this customer before fetching transactions
        try {
            await ajax('api/accounts-receivable/apply-auto-credit-memos', 'POST', JSON.stringify({ customer_code: customerCode }));
            console.log('Auto credit memos applied for customer before loading transactions:', customerCode);
        } catch (autoCreditError) {
            console.warn('Auto credit memo application failed before loading transactions, proceeding with data load:', autoCreditError);
        }

        const response = await ajax(`api/customer-credit/${customerCode}/transactions`, 'GET', null);
        
        if (response.success) {
            populateModal(response.data);
        } else {
            showNotification('error', 'Failed to load transactions: ' + response.message);
            $('#transactionsTableBody').html(`
                <tr>
                    <td colspan="8" class="text-center text-danger py-4">
                        <i class="mdi mdi-alert-circle me-2"></i>
                        Failed to load transaction data: ${response.message}
                    </td>
                </tr>
            `);
        }
    } catch (xhr) {
        console.error('Error loading transactions:', xhr);
        let errorMessage = 'Error loading transaction data';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        }
        showNotification('error', errorMessage);
        $('#transactionsTableBody').html(`
            <tr>
                <td colspan="8" class="text-center text-danger py-4">
                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                    ${errorMessage}
                    <br><small class="text-muted mt-1">Please try again or contact support if the problem persists</small>
                </td>
            </tr>
        `);
    }
}

function populateModal(data) {
    const { customer, transactions, summary } = data;

    // Populate customer info
    $('#modalCustomerCode').val(customer.code || '-');
    $('#modalCustomerName').val(customer.name || '-');
    $('#modalContactPerson').val(customer.contact_person || '-');
    $('#modalContactNo').val(customer.contact_number || '-');

    // Use summary data from backend
    const totalCredit = summary ? summary.total_debt : 0;
    const totalPaid = summary ? summary.total_paid : 0;
    const balance = summary ? summary.balance : 0;
    const creditMemo = summary ? summary.credit_memo : 0;

    $('#totalDebt').val(formatPeso(totalCredit));
    $('#totalPaid').val(formatPeso(totalPaid));
    
    // Set balance color and text
    const balanceElement = $('#balanceOwed');
    balanceElement.val(formatPesoAbs(balance));
    balanceElement.removeClass('positive negative warning');
    if (balance > 0) {
        balanceElement.addClass('negative'); 
    } else if (balance < 0) {
        balanceElement.addClass('positive'); 
    }

    // Set credit memo display
    const creditMemoElement = $('#creditMemoBalance');
    creditMemoElement.val(formatPeso(creditMemo));
    if (creditMemo > 0) {
        creditMemoElement.addClass('text-info');
    } else {
        creditMemoElement.removeClass('text-info');
    }

    // Populate credit limit and credit balance
    const creditLimit = summary ? parseFloat(summary.credit_limit) || 0 : 0;
    const creditBalance = summary ? parseFloat(summary.credit_balance) || 0 : 0;
    
    $('#creditLimit').val(formatPeso(creditLimit));
    $('#creditBalance').val(formatPeso(creditBalance));

    // Populate transactions table
    const tbody = $('#transactionsTableBody');
    tbody.empty();

    if (transactions && transactions.length > 0) {
        $('#noTransactionsMessage').hide();
        
        // Sort all transactions by date and time (oldest first) to match backend logic
        const sortedTransactions = transactions.sort((a, b) => {
            const dateA = new Date(a.sort_date || a.date);
            const dateB = new Date(b.sort_date || b.date);
            return dateA - dateB; // Ascending order (oldest first)
        });

        // Separate invoices and payments for proper sequential processing
        const invoices = [];
        const payments = [];
        const autoCreditPayments = [];
        
        sortedTransactions.forEach((item) => {
            if (item.type === 'payment') {
                if (item.reference_number && item.reference_number.startsWith('AUTO-CM-')) {
                    autoCreditPayments.push(item);
                } else {
                    payments.push(item);
                }
            } else {
                invoices.push(item);
            }
        });

        // Note: Backend already includes credit memo applied on invoice rows.
        // To avoid double deduction, do not render separate auto-credit entries here.
        const autoCreditMap = new Map();

        // Process invoices and their related payments in chronological order
        const processedTransactions = [];
        const invoiceSettlementMap = new Map();
        
        invoices.forEach(invoice => {
            processedTransactions.push(invoice);

            // Do not push auto credit memo entries; invoice already shows CM in Paid and balance

            // Add regular payments for this invoice
            const relatedPayments = payments.filter(payment =>
                payment.parent_transaction_id === invoice.id ||
                payment.so_number === invoice.so_number
            );

            // Compute invoice settlement to drive status rendering
            const invoiceAmount = parseFloat(invoice.transaction_amount) || 0;
            const cmApplied = parseFloat(invoice.credit_memo_applied || 0);
            const paymentsSum = relatedPayments.reduce((sum, p) => sum + (parseFloat(p.payment_amount) || 0), 0);
            const remaining = invoiceAmount - cmApplied - paymentsSum;
            invoiceSettlementMap.set(invoice.id, { fullyPaid: normalizeZero(remaining) <= 0, remaining });

            relatedPayments.forEach(payment => {
                processedTransactions.push(payment);
            });
        });
        
        let grandTotalAmount = 0;
        let grandTotalPaid = 0;
        // Use a cumulative running balance across all rows to match supplier pattern
        let cumulativeBalance = 0;
        
        // Track unique transactions to avoid double counting
        let processedTransactionIds = new Set();
        
        processedTransactions.forEach(function(transaction, index) {
            // Format the date with time if available
            let formattedDate = 'N/A';
            if (transaction.date || transaction.payment_date) {
                const transactionDate = new Date(transaction.date || transaction.payment_date);
                formattedDate = transactionDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });
                
                // Add time if available
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
            
            // Determine transaction type and styling
            const isPayment = transaction.type === 'payment';
            const isAutoCredit = transaction.type === 'auto_credit';
            const hasCredit = isPayment && transaction.has_credit_memo;
            
            let rowClass = '';
            let rowStyle = '';
            
            if (isAutoCredit) {
                rowClass = 'table-success';
                rowStyle = 'font-style: italic; border-left: 4px solid #28a745;';
            } else if (isPayment) {
                // Default styling for payments; may be overridden inside payment branch
                rowClass = hasCredit ? 'table-warning' : 'table-info';
                rowStyle = hasCredit ? 
                    'font-style: italic; background-color: rgba(255, 193, 7, 0.2); border-left: 4px solid #ffc107;' : 
                    'font-style: italic;';
            }
            
            let amountDisplay, paidDisplay, balanceDisplay, descriptionText, statusBadge;
            let displayBalanceOverride = null; // For special display cases (e.g., overpayment rows)
            
            if (isAutoCredit) {
                // Auto credit memo application
                const creditAmount = parseFloat(transaction.payment_amount);
                runningBalance -= creditAmount;
                
                amountDisplay = '-';
                paidDisplay = `${formatPeso(creditAmount)} (CM)`;
                descriptionText = `Auto Credit from ${transaction.source_reference}`;
                statusBadge = '<span class="badge bg-success">Credit Applied</span>';
                // Note: Credit memo applications are not added to grandTotalPaid as they are not actual payments
                
            } else if (isPayment) {
                // Regular payment
                const paymentAmount = parseFloat(transaction.payment_amount);
                const EPS = 1e-6;
                const tempBalance = cumulativeBalance - paymentAmount;

                // Treat payments that push balance below zero as CM-involved, even if backend flag is missing
                const paidWithCM = hasCredit || (tempBalance < -EPS);

                if (paidWithCM) {
                    // Ensure row is visually highlighted as CM-related
                    rowClass = 'table-warning';
                    rowStyle = 'font-style: italic; background-color: rgba(255, 193, 7, 0.2); border-left: 4px solid #ffc107;';

                    // Show negative overage on this row and clamp cumulative to 0
                    if (tempBalance < 0) {
                        displayBalanceOverride = tempBalance;
                        cumulativeBalance = 0;
                    } else {
                        cumulativeBalance -= paymentAmount;
                    }

                    // Prefer backend description; otherwise append with CM
                    const backendDesc = transaction.description || '';
                    if (backendDesc && backendDesc.toLowerCase().includes('with cm')) {
                        descriptionText = backendDesc;
                    } else {
                        descriptionText = `Payment - ${transaction.payment_type || 'Cash'} with CM`;
                    }
                    statusBadge = '<span class="badge bg-warning">Paid with CM</span>';
                } else {
                    // Non-CM payment styling
                    rowClass = 'table-info';
                    rowStyle = 'font-style: italic;';

                    cumulativeBalance -= paymentAmount;
                    descriptionText = `Payment - ${transaction.payment_type || 'Cash'}`;
                    // Defer to backend status when available; fallback based on balance
                    const isFullyPaid = (transaction.status && transaction.status.toLowerCase().includes('fully')) || cumulativeBalance <= 0;
                    statusBadge = isFullyPaid ?
                        '<span class="badge bg-success">Fully Paid</span>' :
                        '<span class="badge bg-info">Payment Made</span>';
                }

                amountDisplay = '-';
                paidDisplay = `${formatPeso(paymentAmount)}`;
                grandTotalPaid += paymentAmount;
                
            } else {
                // Invoice/Transaction
                const originalAmount = parseFloat(transaction.transaction_amount);
                // Add invoice amount to cumulative balance
                cumulativeBalance += originalAmount;
                
                descriptionText = `Invoice - ${transaction.reference_number || 'N/A'}`;
                amountDisplay = `${formatPeso(originalAmount)}`;
                
                // Check if this invoice has credit memo applied to it
                const creditMemoApplied = parseFloat(transaction.credit_memo_applied || 0);
                if (creditMemoApplied > 0) {
                    paidDisplay = `${formatPeso(creditMemoApplied)} (CM)`;
                    // Note: Credit memo applications are not added to grandTotalPaid as they are not actual payments
                    // Subtract credit memo amount from the cumulative balance
                    cumulativeBalance -= creditMemoApplied;
                } else {
                    paidDisplay = '-';
                }
                
                // Status badge for invoices
                const settlement = invoiceSettlementMap.get(transaction.id);
                if (settlement && settlement.fullyPaid) {
                    statusBadge = '<span class="badge bg-success">Fully Paid</span>';
                } else if (transaction.status) {
                    const status = transaction.status.toLowerCase();
                    if (status.includes('fully')) {
                        statusBadge = '<span class="badge bg-success">Fully Paid</span>';
                    } else if (status.includes('paid')) {
                        statusBadge = '<span class="badge bg-success">Paid</span>';
                    } else if (status.includes('partial')) {
                        statusBadge = '<span class="badge bg-warning">Partial</span>';
                    } else if (transaction.is_overdue) {
                        statusBadge = '<span class="badge bg-danger">Overdue</span>';
                    } else {
                        statusBadge = '<span class="badge bg-secondary">Pending</span>';
                    }
                } else {
                    statusBadge = '<span class="badge bg-secondary">Pending</span>';
                }
                
                // Add to grand totals - only for invoices, and only once per unique transaction
                if (!processedTransactionIds.has(transaction.id)) {
                    grandTotalAmount += originalAmount;
                    processedTransactionIds.add(transaction.id);
                }
            }
            
            // Calculate balance display
            const displayBalance = displayBalanceOverride !== null ? displayBalanceOverride : cumulativeBalance;
            balanceDisplay = `${formatPeso(displayBalance)}`;
            
            // Balance color: red for positive (owed), green for zero/negative (credit/overpaid)
            const balanceColor = displayBalance > 0 ? '#dc3545' : '#28a745';

            // Determine paid column color
            let paidColor = '#28a745'; // Default green for payments
            if (isAutoCredit) {
                paidColor = '#28a745'; // Green for auto credit applications
            } else if (hasCredit) {
                paidColor = '#0dcaf0'; // Light blue for payments with credit memo
            } else if (!isPayment && parseFloat(transaction.credit_memo_applied || 0) > 0) {
                paidColor = '#17a2b8'; // Teal for invoices with credit memo applied
            }

            const row = `
                <tr class="${rowClass}" style="${rowStyle}">
                    <td>${formattedDate}</td>
                    <td>${descriptionText}</td>
                    <td>${transaction.so_number || 'N/A'}</td>
                    <td class="text-end">${amountDisplay}</td>
                    <td class="text-end" style="color: ${paidColor};">${paidDisplay}</td>
                    <td class="text-end" style="color: ${balanceColor};">${balanceDisplay}</td>
                    <td>${statusBadge}</td>
                    <td>${transaction.terms || 'N/A'}</td>
                </tr>`;
            tbody.append(row);
        });
        
        // Calculate final balance using correct formula: Amount - Paid = Balance
        const grandTotalBalance = grandTotalAmount - grandTotalPaid;
        
        // Add summary row
        const summaryRow = `
            <tr style="background-color: #f8f9fa; border-top: 2px solid #dee2e6; font-weight: bold;">
                <td colspan="3" class="text-end">TOTALS:</td>
                <td class="text-end" style="color: #dc3545;">${formatPeso(grandTotalAmount)}</td>
                <td class="text-end" style="color: #28a745;">${formatPeso(grandTotalPaid)}</td>
                <td class="text-end" style="color: ${grandTotalBalance >= 0 ? '#dc3545' : '#28a745'};">${formatPeso(grandTotalBalance)}</td>
                <td colspan="2"></td>
            </tr>
        `;
        tbody.append(summaryRow);
        
    } else {
        $('#noTransactionsMessage').show();
        tbody.html('<tr><td colspan="8" class="text-center text-muted">No transactions found for this customer.</td></tr>');
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
    link.download = `CustomerCreditReport_${today}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Add download button click handler
$(document).ready(function() {
    $('#csvDLBtn').on('click', function () {
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        // Show loading state
        $btn.prop('disabled', true).html(`
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span>Generating Report...</span>
            </div>
        `);
        
        // Simulate processing time for user feedback
        setTimeout(() => {
            try {
                downloadToCSV(customersData);
                showNotification('success', 'Customer credit report downloaded successfully');
            } catch (error) {
                showNotification('error', 'Failed to generate report: ' + error.message);
            } finally {
                // Restore button
                setTimeout(() => {
                    $btn.prop('disabled', false).html(originalHtml);
                }, 1000);
            }
        }, 500);
    });    // Print Statement of Account (all transactions)
    $('#printStatementBtn').on('click', function () {
        const customerCode = $('#modalCustomerCode').val();
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        if (customerCode && customerCode !== '-' && customerCode !== 'Loading...') {
            // Show loading state
            $btn.prop('disabled', true).html(`
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Generating Statement...
            `);
            
            const printUrl = `${globalApi}api/customer-credit/${customerCode}/print-statement`;
            window.open(printUrl, '_blank');
            
            // Restore button after delay
            setTimeout(() => {
                $btn.prop('disabled', false).html(originalHtml);
            }, 2000);
        } else {
            showNotification('error', 'Please select a customer first');
        }
    });
    
    // Print Counter Receipt (pending transactions only)
    $('#printCounterReceiptBtn').on('click', function () {
        const customerCode = $('#modalCustomerCode').val();
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        if (customerCode && customerCode !== '-' && customerCode !== 'Loading...') {
            // Show loading state
            $btn.prop('disabled', true).html(`
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Generating Receipt...
            `);
            
            const printUrl = `${globalApi}api/customer-credit/${customerCode}/print-counter-receipt`;
            window.open(printUrl, '_blank');
            
            // Restore button after delay
            setTimeout(() => {
                $btn.prop('disabled', false).html(originalHtml);
            }, 2000);
        } else {
            showNotification('error', 'Please select a customer first');
        }
    });
});

// Note: Payment functionality is handled in the Accounts Payable page
// This page is for viewing transaction history only

// Export functions for external access if needed
window.CustomerCreditModule = {
    showCustomerTransactions: showCustomerTransactions,
    customersData: customersData
};