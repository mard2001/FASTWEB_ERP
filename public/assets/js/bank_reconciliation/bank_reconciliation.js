var MainTH, TransactionTH;
var jsonArr = [];
var selectedBank = null;
var isloading = false;

$(document).ready(async function () {
    // Load bank reconciliation data (all banks auto-populated)
    await datatables.loadBankReconData();

    // Handle "Add Beginning Balance" button click in table
    $("#bankReconTable").on("click", ".addBalanceBtn", async function (e) {
        e.stopPropagation(); // Prevent row click event
        const bankId = $(this).data('bank-id');
        const bankName = $(this).data('bank-name');
        const accountName = $(this).data('account-name');

        openBeginningBalanceModal(bankId, bankName, accountName);
    });

    // Handle row click to show details
    $("#bankReconTable").on("click", "tbody tr", async function (e) {
        // Don't trigger if clicking on button
        if ($(e.target).closest('.addBalanceBtn').length) {
            return;
        }

        const bankId = $(this).attr('data-bank-id');
        const hasReconciliation = $(this).attr('data-has-recon') === 'true';

        if (!hasReconciliation) {
            // No reconciliation yet, show message
            Swal.fire({
                title: "No Reconciliation Data",
                text: "Please set a beginning balance first.",
                icon: "info"
            });
            return;
        }

        // Load and show bank details with transaction history
        await showBankDetails(bankId);
    });

    // Save beginning balance
    $("#saveBeginningBalanceBtn").on("click", async function () {
        if (validateBeginningBalance()) {
            const data = {
                BankID: parseInt($('#BalanceBankID').val()),
                BeginningBalance: parseFloat($('#BalanceBeginningBalance').val()),
                ReconciliationDate: $('#BalanceReconciliationDate').val(),
                Notes: $('#BalanceNotes').val() || null
            };

            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to set/update the beginning balance?',
                icon: 'question',
                showDenyButton: true,
                confirmButtonText: "Yes, Save",
                denyButtonText: `Cancel`
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await saveBeginningBalance(data);
                }
            });
        }
    });

    // Update beginning balance from details modal
    $("#updateBeginningBalanceBtn").on("click", async function () {
        const bankId = parseInt($('#DetailsBankID').val());
        const bank = jsonArr.find(b => b.BankID === bankId);
        
        if (bank) {
            openBeginningBalanceModal(bank.BankID, bank.BankName, bank.AccountName, {
                BeginningBalance: bank.BeginningBalance,
                ReconciliationDate: bank.LastReconciliationDate,
                Notes: bank.Notes
            });
            $('#bankDetailsModal').modal('hide');
        }
    });

    // CSV Download
    $('#csvDLBtn').on('click', function () {
        downloadToCSV(jsonArr);
    });
});

function openBeginningBalanceModal(bankId, bankName, accountName, existingData = null) {
    $('#BalanceBankID').val(bankId);
    $('#balanceBankName').text(bankName);
    $('#balanceAccountName').text(accountName);
    
    if (existingData) {
        $('#BalanceBeginningBalance').val(existingData.BeginningBalance || '');
        if (existingData.ReconciliationDate) {
            const date = new Date(existingData.ReconciliationDate);
            $('#BalanceReconciliationDate').val(date.toISOString().split('T')[0]);
        } else {
            $('#BalanceReconciliationDate').val('');
        }
        $('#BalanceNotes').val(existingData.Notes || '');
    } else {
        $('#BalanceBeginningBalance').val('');
        $('#BalanceReconciliationDate').val(new Date().toISOString().split('T')[0]);
        $('#BalanceNotes').val('');
    }
    
    $('#beginningBalanceModal').modal('show');
}

function validateBeginningBalance() {
    const balance = $('#BalanceBeginningBalance').val();
    const date = $('#BalanceReconciliationDate').val();

    if (!balance || parseFloat(balance) < 0) {
        Swal.fire({
            title: "Validation Error",
            text: "Please enter a valid beginning balance",
            icon: "warning"
        });
        return false;
    }

    if (!date) {
        Swal.fire({
            title: "Validation Error",
            text: "Please select a reconciliation date",
            icon: "warning"
        });
        return false;
    }

    return true;
}

async function saveBeginningBalance(data) {
    await ajax('api/bank-reconciliation/set-beginning-balance', 'POST', JSON.stringify({ data: data }), (response) => {
        if (response.success) {
            $('#beginningBalanceModal').modal('hide');
            
            Swal.fire({
                title: "Success!",
                text: response.message,
                icon: "success"
            }).then(() => {
                isloading = true;
                Swal.fire({
                    text: "Please wait... reloading data...",
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
                datatables.loadBankReconData();
            });
        } else {
            Swal.fire({
                title: "Error",
                text: response.message,
                icon: "error"
            });
        }
    }, (xhr, status, error) => {
        if (xhr.responseJSON && xhr.responseJSON.message) {
            Swal.fire({
                title: "Error",
                text: xhr.responseJSON.message,
                icon: "error"
            });
        }
    });
}

async function showBankDetails(bankId) {
    $("#bankReconTable tbody").css('pointer-events', 'none');

    await ajax('api/bank-reconciliation/' + bankId, 'GET', null, (response) => {
        if (response.success) {
            const bank = response.data;
            selectedBank = bank;

            // Fill bank information (using .val() for input fields)
            $('#DetailsBankID').val(bank.BankID);
            $('#detailsBankName').val(bank.BankName);
            $('#detailsAccountName').val(bank.AccountName);
            $('#detailsAccountNumber').val(bank.AccountNumber);
            $('#detailsAccountType').val(bank.AccountType || 'N/A');

            // Fill reconciliation summary (using .val() for input fields)
            $('#summaryBeginningBalance').val('₱' + formatNumber(bank.BeginningBalance || 0));
            $('#summaryTotalInflows').val('₱' + formatNumber(bank.TotalInflows || 0));
            $('#summaryTotalOutflows').val('₱' + formatNumber(bank.TotalOutflows || 0));
            $('#summaryAvailableBalance').val('₱' + formatNumber(bank.AvailableBalance || 0));

            // Load transaction history
            loadTransactionHistory(bank.transactions || []);

            $('#bankDetailsModal').modal('show');
        } else {
            Swal.fire({
                title: "Error",
                text: response.message,
                icon: "error"
            });
        }
        $("#bankReconTable tbody").css('pointer-events', 'auto');
    }, (xhr, status, error) => {
        $("#bankReconTable tbody").css('pointer-events', 'auto');
        if (xhr.responseJSON && xhr.responseJSON.message) {
            Swal.fire({
                title: "Error",
                text: xhr.responseJSON.message,
                icon: "error"
            });
        }
    });
}

function loadTransactionHistory(transactions) {
    if (TransactionTH) {
        TransactionTH.clear().destroy();
    }

    TransactionTH = $('#transactionHistoryTable').DataTable({
        data: transactions,
        columns: [
            { 
                data: 'payment_date',
                render: function (data, type, row) {
                    if (!data) return '';
                    const dateObj = new Date(data);
                    if (type === 'display' || type === 'filter') {
                        return dateObj.toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                    }
                    return dateObj.toISOString();
                }
            },
            { 
                data: 'transaction_type',
                render: function(data, type, row) {
                    // Payable = OUT (red badge), Receivable = IN (green badge)
                    if (data === 'OUT') {
                        return '<span class="statusBadge2">OUT</span>';
                    } else if (data === 'IN') {
                        return '<span class="statusBadge1">IN</span>';
                    }
                    return '<span class="badge bg-secondary">N/A</span>';
                },
                className: 'text-center'
            },
            { data: 'supplier_name' },
            { data: 'ap_reference' },
            { 
                data: 'payment_type',
                render: function(data, type, row) {
                    if (!data) return 'N/A';
                    
                    // Log the actual data to console for debugging
                    console.log('Payment Type Data:', data, 'Check Number:', row.check_number);
                    
                    // Check if there's a check number associated with this payment
                    if (row.check_number && row.check_number.trim() !== '') {
                        return 'Bank Check';
                    }
                    
                    // Check if payment type is Bank with Check (various formats)
                    const lowerData = data.toLowerCase();
                    if (lowerData === 'check' || lowerData === 'bank check' || lowerData === 'bank-check') {
                        return 'Bank Check';
                    } else if (lowerData === 'bank' || lowerData.includes('bank')) {
                        return 'Bank';
                    }
                    
                    // For other payment types, capitalize first letter
                    return data.charAt(0).toUpperCase() + data.slice(1);
                }
            },
            { 
                data: 'payment_amount',
                render: function(data) {
                    return '₱' + formatNumber(data || 0);
                },
                className: 'text-end'
            },
            { data: 'reference_number',
                render: function(data) {
                    return data || 'N/A';
                }
            },
            { data: 'remarks',
                render: function(data) {
                    return data || '-';
                }
            }
        ],
        order: [[0, 'desc']], // Sort by date descending
        pageLength: 10,
        lengthChange: false,
        searching: true,
        language: {
            searchPlaceholder: "Search transactions..."
        }
    });
}

function formatNumber(num) {
    return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

async function ajax(endpoint, method, data, successCallback = () => { }, errorCallback = () => { }) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: globalApi + endpoint,
            type: method,
            Accept: 'application/json',
            contentType: 'application/json',
            data: data,

            success: function (response) {
                successCallback(response);
                resolve(response);
            },
            error: function (xhr, status, error) {
                errorCallback(xhr, status, error);
                reject(error);
            }
        });
    });
}

const datatables = {
    loadBankReconData: async () => {
        await ajax('api/bank-reconciliation', 'GET', null, (response) => {
            jsonArr = response.data;
            console.log('Bank reconciliation data loaded:', response.data);
            datatables.initBankReconDatatable(response);
            if(isloading){
                Swal.close();
                isloading = false;
            }
        }, (xhr, status, error) => {
            console.error('Error loading bank reconciliation data:', error);
            Swal.fire({
                title: "Error",
                text: "Failed to load bank reconciliation data.",
                icon: "error"
            });
        });
    },
    initBankReconDatatable: (response) => {
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
            } else {
                MainTH = $('#bankReconTable').DataTable({
                    data: response.data,
                    language: {
                        searchPlaceholder: "Search here..."
                    },
                    columns: [
                        { data: 'BankName', title: 'Bank Name' },
                        { data: 'AccountName', title: 'Account Name' },
                        { data: 'AccountNumber', title: 'Account Number' },
                        { 
                            data: 'BeginningBalance', 
                            title: 'Beginning Balance',
                            render: function(data, type, row) {
                                if (!data && data !== 0) return '<span class="text-muted">Not Set</span>';
                                return '₱' + formatNumber(data);
                            },
                            className: 'text-end'
                        },
                        { 
                            data: 'TotalOutflows', 
                            title: 'Total Outflows',
                            render: function(data, type, row) {
                                if (!data && data !== 0) return '₱0.00';
                                return '₱' + formatNumber(data);
                            },
                            className: 'text-end'
                        },
                        { 
                            data: 'AvailableBalance', 
                            title: 'Available Balance',
                            render: function(data, type, row) {
                                if (!data && data !== 0) return '<span class="text-muted">-</span>';
                                return '₱' + formatNumber(data);
                            },
                            className: 'text-end'
                        },
                        { 
                            data: 'LastReconciliationDate', 
                            title: 'Last Reconciliation',
                            render: function (data, type, row) {
                                if (!data) return '<span class="text-muted">Never</span>';
                                const dateObj = new Date(data);
                                if (type === 'display' || type === 'filter') {
                                    return dateObj.toLocaleDateString('en-GB', {
                                        day: '2-digit',
                                        month: 'short',
                                        year: 'numeric'
                                    });
                                }
                                return dateObj.toISOString();
                            }
                        },
                        {
                            data: null,
                            title: 'Action',
                            orderable: false,
                            render: function(data, type, row) {
                                if (!row.HasReconciliation) {
                                    return `<button class="btn btn-xs btn-primary addBalanceBtn" style="font-size: 9px; padding: 2px 8px;"
                                                data-bank-id="${row.BankID}" 
                                                data-bank-name="${row.BankName}"
                                                data-account-name="${row.AccountName}">
                                                <i class="mdi mdi-plus"></i> Add Balance
                                            </button>`;
                                } else {
                                    return `<button class="btn btn-xs btn-info addBalanceBtn" style="font-size: 9px; padding: 2px 8px;"
                                                data-bank-id="${row.BankID}" 
                                                data-bank-name="${row.BankName}"
                                                data-account-name="${row.AccountName}">
                                                <i class="mdi mdi-pencil"></i> Update
                                            </button>`;
                                }
                            },
                            className: 'text-center'
                        }
                    ],
                    columnDefs: [
                        { className: "text-start", targets: [0, 1, 2] },
                        { className: "text-nowrap", targets: '_all' },
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    createdRow: function (row, data) {
                        $(row).attr('data-bank-id', data.BankID);
                        $(row).attr('data-has-recon', data.HasReconciliation);
                        
                        // Add pointer cursor only if has reconciliation
                        if (data.HasReconciliation) {
                            $(row).css('cursor', 'pointer');
                        }
                    },
                    pageLength: 15,
                    lengthChange: false,
                    initComplete: function () {
                        $(this.api().table().container()).find('#dt-search-0').addClass('p-1 mx-0 dtsearchInput nofocus');
                        $(this.api().table().container()).find('.dt-search label').addClass('py-1 mx-0 dtsearchLabel').html('<span class="mdi mdi-magnify"></span>');
                        $(this.api().table().container()).find('.dt-layout-row').first().find('.dt-layout-cell').each(function() { 
                            this.style.setProperty('height', '38px', 'important'); 
                        });
                        $(this.api().table().container()).find('.dt-layout-table').removeClass('px-4');
                        $(this.api().table().container()).find('.dt-scroll-body').addClass('rmvBorder');
                        $(this.api().table().container()).find('.dt-layout-table').addClass('btmdtborder');
                        $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                        
                        $('.loadingScreen').remove();
                        $('#dattableDiv').removeClass('opacity-0');
                        
                        const tableDiv = $('.dt-layout-row').first();
                        tableDiv.after('<div style="background: linear-gradient(to right, var(--primary-color, #1b438f), var(--secondary-color, #33336F) ); color: var(--text-color-light, #FFF); margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px">Bank Reconciliation - All Banks</p></div>');
                    }
                });
            }
        }
    },
};

function downloadToCSV(jsonArr) {
    const csvData = Papa.unparse(jsonArr);
    var today = new Date().toISOString().split('T')[0];

    const blob = new Blob([csvData], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `BankReconciliation_${today}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
