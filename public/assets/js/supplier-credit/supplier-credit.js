var MainTH, selectedMain;
var suppliersData = [];

$(document).ready(function() {
    // Ensure loading screen is visible initially
    $('.loadingScreen').show();
    $('#dattableDiv').addClass('opacity-0');
    
    // Small delay to ensure DOM is ready and loading screen shows
    setTimeout(() => {
        // Load suppliers data using the same pattern as supplier maintenance
        datatables.loadSupplierData();
    }, 100);

    // Row click handler for supplier transactions
    $("#supplierCreditTable").on("click", "tbody tr", function() {
        const $row = $(this);
        const selectedSupplierCode = $row.attr('id');
        
        if (selectedSupplierCode && !$row.hasClass('loading')) {
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
                        <span class="text-muted">Loading supplier details...</span>
                    </div>
                </td>
            `;
            $row.html(loadingHtml);
            
            // Disable all row clicks temporarily
            $("#supplierCreditTable tbody").css('pointer-events', 'none');
            
            showSupplierTransactions(selectedSupplierCode).finally(() => {
                // Restore row and enable clicks after loading
                setTimeout(() => {
                    $row.html(originalContent).removeClass('loading').css({
                        'background-color': '',
                        'pointer-events': ''
                    });
                    $("#supplierCreditTable tbody").css('pointer-events', 'auto');
                }, 300);
            });
        }
    });

    // Refresh data handler
    $('#scRefreshBtn').on('click', function() {
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
            $('#supplierCreditTable tbody').html(`
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="text-muted">Refreshing supplier credit data...</div>
                        </div>
                    </td>
                </tr>
            `);
        }
        
        // Clear current data 
        suppliersData = [];
        
        // Call refresh API directly with proper error handling
        ajax('api/supplier-credit', 'GET', null, 
            (response) => {
                // Success callback
                suppliersData = response.data;
                datatables.initSupplierDatatable(response);
                
                // Restore button after short delay
                setTimeout(() => {
                    $btn.prop('disabled', false).html(originalHtml);
                }, 500);
            }, 
            (xhr, status, error) => {
                // Error callback
                console.error('Error refreshing supplier credit data:', error);
                showNotification('error', 'Failed to refresh supplier credit data');
                
                // Show error message in table
                if (MainTH) {
                    $('#supplierCreditTable tbody').html(`
                        <tr>
                            <td colspan="7" class="text-center py-4 text-danger">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <i class="mdi mdi-alert-circle-outline mb-2" style="font-size: 2rem;"></i>
                                    <div>Failed to refresh supplier credit data</div>
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
        // Show enhanced loading message
        $('.loadingScreen .loader').after('<div class="mt-3 text-center"><small class="text-muted">Loading supplier credit data...</small></div>');
        
        const supplierData = await ajax('api/supplier-credit', 'GET', null, (response) => {
            suppliersData = response.data;
            datatables.initSupplierDatatable(response);
        }, (xhr, status, error) => {
            console.error('Error loading supplier credit data:', error);
            console.error('XHR Details:', xhr);
            // Hide loading screen on error
            $('.loadingScreen').remove();
            $('#dattableDiv').removeClass('opacity-0').html(
                '<div class="alert alert-danger m-3"><i class="mdi mdi-alert-circle me-2"></i>Failed to load supplier credit data. Please refresh the page.</div>'
            );
            showNotification('error', 'Failed to load supplier credit data');
        });
    },
    
    initSupplierDatatable: (response) => {
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
                MainTH.order([2, 'asc']).draw(); // Order by Total Credit column in ascending order (latest/largest at bottom)
            } else {
                MainTH = $('#supplierCreditTable').DataTable({
                    data: response.data,
                    language: {
                        searchPlaceholder: "Search suppliers..."
                    },
                    order: [[2, 'asc']], // Order by Total Credit column in ascending order (latest/largest at bottom)
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
                        },
                        { 
                            data: null,
                            title: 'Credit Limit',
                            render: function(data, type, row) {
                                const amount = parseFloat(row.credit_limit) || 0;
                                if (amount === 0) {
                                    return '<span class="text-muted">No Limit</span>';
                                }
                                return '₱' + amount.toLocaleString('en-US', {minimumFractionDigits: 2});
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
                                return `<span class="${colorClass}">₱${creditBalance.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>`;
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
                    }
                });
                
                // Hide loading screen and show table (matching accounts payable pattern)
                $('.loadingScreen').remove();
                $('#dattableDiv').removeClass('opacity-0');

                const tableDiv = $('.dt-layout-row').first();
                tableDiv.after('<div style="background: linear-gradient(to right, var(--primary-color, #1b438f), var(--secondary-color, #33336F) ); color: var(--text-color-light, #FFF); margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px">Supplier Credit Information</p></div>');
            }
        }
    }
};

// This function is now handled by datatables.loadSupplierData() above

async function showSupplierTransactions(supplierCode) {
    // Show loading state
    $('#supplierTransactionsModal').modal('show');
    
    // Enhanced loading animation for modal
    const loadingContent = `
        <tr>
            <td colspan="8" class="text-center py-4">
                <div class="d-flex flex-column align-items-center">
                    <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="text-muted">Loading transaction history...</div>
                    <small class="text-muted mt-1">Please wait while we fetch the data</small>
                </div>
            </td>
        </tr>
    `;
    
    $('#transactionsTableBody').html(loadingContent);
    
    // Clear previous data while loading
    $('#modalSupplierCode, #modalSupplierName, #modalContactPerson, #modalContactNo').val('Loading...');
    $('#totalDebt, #totalPaid, #balanceOwed, #creditMemoBalance').val('Loading...');
    
    try {
        const response = await ajax(`api/supplier-credit/${supplierCode}/transactions`, 'GET', null);
        
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

        // Group payments so they always appear immediately after their corresponding invoice
        const paymentsByParent = {};
        sortedTransactions.forEach((item) => {
            if (item.type === 'payment' && item.parent_transaction_id) {
                const key = item.parent_transaction_id;
                if (!paymentsByParent[key]) paymentsByParent[key] = [];
                paymentsByParent[key].push(item);
            }
        });

        const groupedTransactions = [];
        sortedTransactions.forEach((item) => {
            if (item.type === 'invoice' || item.type === 'transaction') {
                groupedTransactions.push(item);
                const key = item.parent_transaction_id;
                if (paymentsByParent[key] && paymentsByParent[key].length) {
                    paymentsByParent[key].forEach((p) => groupedTransactions.push(p));
                    delete paymentsByParent[key];
                }
            }
        });

        // Append any remaining payments without a matching transaction at the end
        Object.keys(paymentsByParent).forEach((key) => {
            paymentsByParent[key].forEach((p) => groupedTransactions.push(p));
        });
        
        let grandTotalAmount = 0;
        let grandTotalPaid = 0;
        let grandTotalBalance = 0;
        
        // Track unique transactions to avoid double counting
        let processedTransactionIds = new Set();
        
        // Pre-process to identify which invoices should get credit memos applied
        let invoiceCreditMap = new Map(); // Map invoice index to credit amount
        let standbyCredits = []; // Track unused/partial credits
        
        // First pass: identify credit memo payments and their amounts
        for (let i = 0; i < groupedTransactions.length; i++) {
            const trans = groupedTransactions[i];
            if (trans.type === 'payment' && trans.has_credit_memo) {
                // Find the invoice this payment was for by matching RR number or parent ID
                let paidInvoiceAmount = 0;
                let totalPreviousPayments = 0;
                const paymentRR = trans.rr_number;
                const paymentParentId = trans.parent_transaction_id;
                
                // Look through ALL transactions to find the matching invoice
                for (let j = 0; j < groupedTransactions.length; j++) {
                    const checkTrans = groupedTransactions[j];
                    if (checkTrans.type !== 'payment' && 
                        (checkTrans.rr_number === paymentRR || 
                         checkTrans.parent_transaction_id === paymentParentId ||
                         checkTrans.id === paymentParentId)) {
                        paidInvoiceAmount = parseFloat(checkTrans.transaction_amount);
                        break;
                    }
                }
                
                // Calculate total previous payments for this invoice (before this payment)
                for (let k = 0; k < i; k++) {
                    const prevTrans = groupedTransactions[k];
                    if (prevTrans.type === 'payment' && 
                        (prevTrans.rr_number === paymentRR || 
                         prevTrans.parent_transaction_id === paymentParentId)) {
                        totalPreviousPayments += parseFloat(prevTrans.payment_amount);
                        
                        // Debug for DODOY 5 payments
                        if (paymentRR === "RR-20251016112044990924065") {
                            console.log(`Found previous payment for DODOY 5:`, {
                                index: k,
                                amount: prevTrans.payment_amount,
                                description: prevTrans.description,
                                runningTotal: totalPreviousPayments
                            });
                        }
                    }
                }
                
                // Also check for any auto-applied credits for this invoice
                let appliedCredits = 0;
                if (paymentRR === "RR-20251016112044990924065") {
                    // Look for any credits that might have been applied to this invoice
                    for (let c = 0; c < i; c++) {
                        if (invoiceCreditMap && invoiceCreditMap.has(c)) {
                            const creditTrans = groupedTransactions[c];
                            if (creditTrans && creditTrans.rr_number === paymentRR) {
                                appliedCredits += invoiceCreditMap.get(c);
                                console.log(`Found applied credit to DODOY 5:`, {
                                    amount: invoiceCreditMap.get(c)
                                });
                            }
                        }
                    }
                }
                
                // Check for any previously applied credits to this invoice
                let previouslyAppliedCredits = 0;
                for (let invoiceIdx = 0; invoiceIdx < groupedTransactions.length; invoiceIdx++) {
                    const invoiceTrans = groupedTransactions[invoiceIdx];
                    if (invoiceTrans.type !== 'payment' && 
                        (invoiceTrans.rr_number === paymentRR || 
                         invoiceTrans.parent_transaction_id === paymentParentId ||
                         invoiceTrans.id === paymentParentId)) {
                        // Found the invoice, check if it has auto_credit_memo applied
                        if (invoiceTrans.auto_credit_memo && parseFloat(invoiceTrans.auto_credit_memo) > 0) {
                            previouslyAppliedCredits += parseFloat(invoiceTrans.auto_credit_memo);
                        }
                        break;
                    }
                }
                
                const paymentAmount = parseFloat(trans.payment_amount);
                const remainingInvoiceAmount = paidInvoiceAmount - totalPreviousPayments - previouslyAppliedCredits;
                
                // Debug the last payment with CM (should be around index 10)
                if (i >= 9) {
                    console.log(`Payment with CM ${i}:`, {
                        description: trans.description,
                        paymentAmount,
                        paidInvoiceAmount,
                        totalPreviousPayments,
                        previouslyAppliedCredits,
                        remainingInvoiceAmount,
                        rr_number: paymentRR
                    });
                }
                
                // Check if this payment creates a credit
                if (paymentAmount > remainingInvoiceAmount) {
                    const creditAmount = paymentAmount - remainingInvoiceAmount;
                    console.log(`Creating credit from payment ${i}:`, {
                        creditAmount,
                        paymentAmount,
                        remainingInvoiceAmount
                    });
                    standbyCredits.push({
                        amount: creditAmount,
                        paymentIndex: i
                    });
                }
            }
        }
        
        console.log('All standby credits:', standbyCredits);
        
        // Second pass: apply credits to eligible invoices (partial application allowed)
        for (let i = 0; i < groupedTransactions.length; i++) {
            const trans = groupedTransactions[i];
            if (trans.type !== 'payment' && standbyCredits.length > 0) {
                // Debug Invoice DODOY 6 specifically
                if (i >= 10) {
                    console.log(`Checking invoice ${i} (DODOY 6?):`, {
                        description: trans.description,
                        amount: trans.transaction_amount,
                        rr_number: trans.rr_number,
                        availableCredits: standbyCredits.length
                    });
                }
                
                // Look for any standby credit that comes from a payment BEFORE this invoice
                // Process credits in order and stop after applying one credit to avoid double application
                for (let creditIdx = 0; creditIdx < standbyCredits.length; creditIdx++) {
                    const credit = standbyCredits[creditIdx];
                    
                    if (i >= 10) {
                        console.log(`Checking credit ${creditIdx} for invoice ${i}:`, {
                            creditAmount: credit.amount,
                            paymentIndex: credit.paymentIndex,
                            isPaymentBefore: credit.paymentIndex < i
                        });
                    }
                    
                    // Only proceed if: payment is before this invoice, credit has amount, and invoice doesn't already have credit
                    if (credit.paymentIndex < i && credit.amount > 0 && !invoiceCreditMap.has(i)) {
                        // Check if this invoice is already fully paid
                        let isInvoiceAlreadyPaid = false;
                        let invoiceAmount = parseFloat(trans.transaction_amount);
                        let totalPaymentsForThisInvoice = 0;
                        
                        // Check all transactions after this invoice to see if it's already fully paid
                        for (let m = i + 1; m < groupedTransactions.length; m++) {
                            const laterTrans = groupedTransactions[m];
                            if (laterTrans.type === 'payment' && 
                                (laterTrans.rr_number === trans.rr_number || 
                                 laterTrans.parent_transaction_id === trans.parent_transaction_id ||
                                 laterTrans.parent_transaction_id === trans.id)) {
                                totalPaymentsForThisInvoice += parseFloat(laterTrans.payment_amount);
                            }
                        }
                        
                        if (totalPaymentsForThisInvoice >= invoiceAmount) {
                            isInvoiceAlreadyPaid = true;
                        }
                        
                        if (i >= 10) {
                            console.log(`Invoice ${i} eligibility:`, {
                                invoiceAmount,
                                totalPaymentsForThisInvoice,
                                isInvoiceAlreadyPaid
                            });
                        }
                        
                        // Apply credit if invoice is eligible (not already paid and no previous credit)
                        if (!isInvoiceAlreadyPaid) {
                            const creditToApply = Math.min(credit.amount, invoiceAmount);
                            if (i >= 10) {
                                console.log(`APPLYING CREDIT TO INVOICE ${i}:`, {
                                    creditToApply,
                                    originalCreditAmount: credit.amount
                                });
                            }
                            
                            // Apply the credit to the invoice
                            invoiceCreditMap.set(i, creditToApply);
                            
                            // Reduce the standby credit amount
                            credit.amount = Math.max(0, credit.amount - creditToApply);
                            
                            // Remove credit from array if fully used
                            if (credit.amount <= 0) {
                                standbyCredits.splice(creditIdx, 1);
                                if (i >= 10) {
                                    console.log(`Credit fully used, removed from standby credits. Remaining credits: ${standbyCredits.length}`);
                                }
                            }
                            
                            // IMPORTANT: Break after applying one credit to prevent double application
                            break;
                        }
                    }
                }
            }
        }
        
        // Track running balance for display
        let runningBalance = 0;
        
        groupedTransactions.forEach(function(transaction, index) {
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
            const hasCredit = isPayment && transaction.has_credit_memo;
            const rowClass = isPayment ? (hasCredit ? 'table-warning' : 'table-info') : '';
            
            let amountDisplay, paidDisplay, balanceDisplay, descriptionText, statusBadge;
            
            // Calculate running balance properly
            let currentTransactionBalance;
            
            if (isPayment) {
                // For payment rows
                const paymentAmount = parseFloat(transaction.payment_amount);
                runningBalance -= paymentAmount;
                currentTransactionBalance = runningBalance;
                
                if (hasCredit) {
                    // Reset running balance if it went negative (credit memo case)
                    if (runningBalance < 0) {
                        runningBalance = 0;
                    }
                    descriptionText = transaction.description;
                    statusBadge = '<span class="badge bg-success">Fully Paid with CM</span>';
                } else {
                    descriptionText = `Payment - ${transaction.payment_type || 'Cash'}`;
                    statusBadge = runningBalance <= 0 ? 
                        '<span class="badge bg-success">Fully Paid</span>' : 
                        '<span class="badge bg-info">Payment Made</span>';
                }
                
                amountDisplay = '-';
                paidDisplay = `₱${paymentAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                grandTotalPaid += paymentAmount;
            } else {
                // For original transaction rows (invoices)
                const originalAmount = parseFloat(transaction.transaction_amount);
                runningBalance += originalAmount;
                
                // Check if this invoice should get a credit memo applied from our pre-processed map
                const creditAmount = invoiceCreditMap.get(index);
                if (creditAmount && creditAmount > 0) {
                    runningBalance -= creditAmount;
                    paidDisplay = `(-${creditAmount.toLocaleString('en-US', {minimumFractionDigits: 2})})`;;
                    
                    // Calculate the remaining standby credit after this application
                    const currentStandbyCredit = standbyCredits.reduce((sum, credit) => sum + credit.amount, 0);
                    
                    // If invoice is fully paid by credit memo, show remaining credit as negative balance
                    if (creditAmount >= originalAmount) {
                        // Show the remaining total standby credit as negative balance
                        currentTransactionBalance = -currentStandbyCredit;
                    } else {
                        currentTransactionBalance = runningBalance;
                    }
                } else {
                    paidDisplay = '-';
                    currentTransactionBalance = runningBalance;
                }
                descriptionText = `Invoice - ${transaction.reference_number || 'N/A'}`;
                
                // Status badge for original transactions
                if (transaction.status === 'Paid' || currentTransactionBalance <= 0) {
                    statusBadge = '<span class="badge bg-success">Paid</span>';
                } else if (transaction.is_overdue) {
                    statusBadge = '<span class="badge bg-danger">Overdue</span>';
                } else {
                    statusBadge = '<span class="badge bg-warning">Pending</span>';
                }
                
                amountDisplay = `₱${originalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                
                // Add to grand totals - only for transaction types, and only once per unique transaction
                if (!processedTransactionIds.has(transaction.id)) {
                    grandTotalAmount += originalAmount;
                    processedTransactionIds.add(transaction.id);
                }
            }
            
            // Use the calculated running balance for display
            // Handle negative zero display issue
            const displayBalance = currentTransactionBalance === 0 ? 0 : currentTransactionBalance;
            balanceDisplay = `₱${displayBalance.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
            
            // Balance color
            const balanceColor = currentTransactionBalance > 0 ? '#dc3545' : '#28a745';
            
            // Determine row styling
            let rowStyle = '';
            if (isPayment) {
                if (hasCredit) {
                    // Special styling for overpayment rows (payments with credit memo)
                    rowStyle = 'font-style: italic; background-color: rgba(255, 193, 7, 0.2); border-left: 4px solid #ffc107;';
                } else {
                    rowStyle = 'font-style: italic;';
                }
            }

            const row = `
                <tr class="${rowClass}" style="${rowStyle}">
                    <td>${formattedDate}</td>
                    <td>${descriptionText}</td>
                    <td>${transaction.rr_number || 'N/A'}</td>
                    <td class="text-end">${amountDisplay}</td>
                    <td class="text-end" style="color: ${hasCredit ? '#0dcaf0' : '#28a745'};">${paidDisplay}</td>
                    <td class="text-end" style="color: ${balanceColor};">${balanceDisplay}</td>
                    <td>${statusBadge}</td>
                    <td>${transaction.terms || 'N/A'}</td>
                </tr>`;
            tbody.append(row);
        });
        
        // Use the final running balance minus any remaining standby credits as the grand total balance
        const finalStandbyCredit = standbyCredits.reduce((sum, credit) => sum + credit.amount, 0);
        grandTotalBalance = runningBalance - finalStandbyCredit;
        
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
                downloadToCSV(suppliersData);
                showNotification('success', 'Supplier credit report downloaded successfully');
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
        const supplierCode = $('#modalSupplierCode').val();
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        if (supplierCode && supplierCode !== '-' && supplierCode !== 'Loading...') {
            // Show loading state
            $btn.prop('disabled', true).html(`
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Generating Statement...
            `);
            
            const printUrl = `${globalApi}api/supplier-credit/${supplierCode}/print-statement`;
            window.open(printUrl, '_blank');
            
            // Restore button after delay
            setTimeout(() => {
                $btn.prop('disabled', false).html(originalHtml);
            }, 2000);
        } else {
            showNotification('error', 'Please select a supplier first');
        }
    });
    
    // Print Counter Receipt (pending transactions only)
    $('#printCounterReceiptBtn').on('click', function () {
        const supplierCode = $('#modalSupplierCode').val();
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        if (supplierCode && supplierCode !== '-' && supplierCode !== 'Loading...') {
            // Show loading state
            $btn.prop('disabled', true).html(`
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Generating Receipt...
            `);
            
            const printUrl = `${globalApi}api/supplier-credit/${supplierCode}/print-counter-receipt`;
            window.open(printUrl, '_blank');
            
            // Restore button after delay
            setTimeout(() => {
                $btn.prop('disabled', false).html(originalHtml);
            }, 2000);
        } else {
            showNotification('error', 'Please select a supplier first');
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