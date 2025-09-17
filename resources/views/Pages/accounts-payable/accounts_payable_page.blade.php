@extends('Layout.layout')

@section('html_title')
    <title>Accounts Payable - FASTWEB ERP</title>
    <link href="https://cdn.materialdesignicons.com/6.5.95/css/materialdesignicons.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('title_header')
    <x-header title="Accounts Payable" />
@endsection

@section('filtering_options')
<div class="filteringOptionDiv">
    <div class="d-flex">
        <div class="mb-1 whMoverangeDiv">
            <label for="daterange" class="form-label">DATE RANGE</label>
            <div id="dateRange" style="background: var(--background-color, #fff); cursor: pointer; padding: 8px 12px; border: 1px solid var(--border-color, #ced4da); border-radius: 0.375rem; width: 100%">
                <i class="fa fa-calendar"></i>&nbsp;
                <span></span> <i class="mdi mdi-chevron-down float-end"></i>
            </div>
        </div>
        <div class="mb-1 whMoverangeDiv">
            <label class="form-label">BRANCH</label>
            <div id="dateRange" style="background: var(--background-color, #fff); cursor: pointer; padding: 8px 12px; border-radius: 0.375rem; width: 100%">
                <input type="text" id="branchFilter" placeholder="Enter branch name">
            </div>
        </div>
        <div class="mb-1 whMoverangeDiv">
            <label class="form-label">STATUS</label>
            <div id="statusFilter_VS" class="VSSelect"></div>
        </div>
    </div>
</div>
@endsection

@section('mini_dashboard_chart')
<div class="">
    <div class="row gx-2 mb-1">
        <div class="col-sm-12 col-md-3">
            <div class="containerStyle">
                <div class="d-flex mx-3 stockIn">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-currency-php'></span>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Total Outstanding</span>
                        <p class="contentValue" id="total-outstanding">₱--- Outstanding</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-3">
            <div class="containerStyle">
                <div class="d-flex mx-3 stockOut">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-file-document'></span>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Total Invoices</span>
                        <p class="contentValue" id="total-invoices">₱--- Invoices</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-3">
            <div class="containerStyle">
                <div class="d-flex mx-3 totalProfit">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-credit-card'></span>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Credit Balance</span>
                        <p class="contentValue" id="total-credit">₱--- Credit</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-3">
            <div class="containerStyle">
                <div class="d-flex mx-3 availableStock">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-format-list-numbered'></span>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Total Records</span>
                        <p class="contentValue" id="total-records">--- Records</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('table')
    <style>
        #AccountsPayableTable thead tr{
            white-space: nowrap;
        }
        .clickable-row:hover {
            background-color: var(--hover-color, #f8f9fa) !important;
            transform: scale(1.01);
            transition: all 0.2s ease-in-out;
        }
        .clickable-row {
            transition: all 0.2s ease-in-out;
        }
        
        /* Status styling */
        .status-outstanding {
            background-color: var(--danger-color, #dc3545);
            color: var(--text-color-light, white);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-settled {
            background-color: var(--success-color, #28a745);
            color: var(--text-color-light, white);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-credit {
            background-color: var(--info-color, #007bff);
            color: var(--text-color-light, white);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        /* Amount styling */
        .amount-positive {
            color: var(--success-color, #28a745);
            font-weight: 600;
        }
        
        .amount-negative {
            color: var(--danger-color, #dc3545);
            font-weight: 600;
        }
        
        .amount-zero {
            color: var(--muted-color, #6c757d);
            font-weight: 500;
        }
        /* Modal header styling for ACCOUNTS PAYABLE RECORD */
        #accountsPayableModal .modalHeaderTitle {
            color: var(--primary-color) !important;
            font-weight: bold !important;
            font-size: 1.25rem !important;
        }

        /* Accounts Payable Form Styling - Same as Purchase Order */
        .apheaderform .row div div label{
            font-size: 0.53em;
            margin-bottom: 0;
            font-family: var(--body-font, "Inter", sans-serif);
            color: var(--text-color, #212529);
        }

        .apheaderform .row div div input,
        .apheaderform .row div div textarea{
            font-size: 0.68em;
            margin-bottom: 0;
            font-family: var(--body-font, "Inter", sans-serif);
            color: var(--text-color, #212529);
        }

        .apheaderSectionTitle{
            font-size: 0.68em;
            text-wrap: nowrap;
            color: var(--accent-color, #33336F);
            font-weight: 500;
            text-transform: uppercase;
            padding: 0 10px;
            font-family: var(--heading-font, "Inter", sans-serif);
        }

        /* Payment Modal Form Styling - Same as Purchase Order */
        .paymentform .row div div label{
            font-size: 0.53em;
            margin-bottom: 0;
            font-family: var(--body-font, "Inter", sans-serif);
            color: var(--text-color, #212529);
        }

        .paymentform .row div div input,
        .paymentform .row div div select,
        .paymentform .row div div textarea{
            font-size: 0.68em;
            margin-bottom: 0;
            font-family: var(--body-font, "Inter", sans-serif);
            color: var(--text-color, #212529);
        }

        .paymentSectionTitle{
            font-size: 0.68em;
            text-wrap: nowrap;
            color: var(--accent-color, #33336F);
            font-weight: 500;
            text-transform: uppercase;
            padding: 0 10px;
            font-family: var(--heading-font, "Inter", sans-serif);
        }
    </style>

    <x-contentButtonDiv downloadFunc="true" :addFunc="false">
        <x-slot:additionalButtons>
            <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="apPrintAllBtn">
                <div class="btnImg me-2" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48cGF0aCBmaWxsPSJjdXJyZW50Q29sb3IiIGQ9Ik0xOCAzSDZ2NGgxMlYzem0tMiA2SDh2Mmg4VjltMiAydjZIOHYtNmgxMHptLTIgNEg4djJoOHYtMnoiLz48L3N2Zz4='); background-size: 16px; background-repeat: no-repeat; background-position: center; width: 16px; height: 16px;">
                </div>
                <span>Print Report</span>
            </div>
        </x-slot:additionalButtons>
    </x-contentButtonDiv>

    <x-table id="AccountsPayableTable">
        <x-slot:td>
            <!-- Table headers will be created dynamically by DataTables -->
        </x-slot:td>
    </x-table>
@endsection

@section('modal')
    <x-mainModal mainModalTitle="accountsPayableModal" modalDialogClass="modal-lg" modalHeaderTitle="ACCOUNTS PAYABLE RECORD" modalSubHeaderTitle="View and manage accounts payable record details.">
        <x-slot:form_fields>
            <div class="apheaderform">
                <form id="accountsPayableForm">
                    <!-- Transaction Information Section -->
                    <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                        <div style="width:100%;"><hr></div>
                        <div class="apheaderSectionTitle">TRANSACTION INFORMATION:</div>
                        <div style="width:100%;"><hr></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-6 col-md-6">
                            <div class="">
                                <label for="date" class="form-label">DATE <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6">
                            <div class="">
                                <label for="supplier_display" class="form-label">SUPPLIER <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="supplier_display" name="supplier_display" readonly>
                                <div id="supplier_code_VS" name="supplier_code" class="form-control bg-white p-0 border-0" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Reference Information Section -->
                    <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                        <div style="width:100%;"><hr></div>
                        <div class="apheaderSectionTitle">REFERENCE INFORMATION:</div>
                        <div style="width:100%;"><hr></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="">
                                <label for="rr_number" class="form-label">RR NUMBER</label>
                                <input type="text" class="form-control" id="rr_number" name="rr_number">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="">
                                <label for="reference_number" class="form-label">REFERENCE NUMBER</label>
                                <input type="text" class="form-control" id="reference_number" name="reference_number">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="">
                                <label for="terms" class="form-label">TERMS</label>
                                <input type="text" class="form-control" id="terms" name="terms">
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information Section -->
                    <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                        <div style="width:100%;"><hr></div>
                        <div class="apheaderSectionTitle">FINANCIAL INFORMATION:</div>
                        <div style="width:100%;"><hr></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-6 col-md-6">
                            <div class="">
                                <label for="total_amount" class="form-label">TOTAL AMOUNT <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-end fw-bold" id="total_amount" name="total_amount" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6" id="balance_amount_container">
                            <div class="">
                                <label for="balance_amount" class="form-label">BALANCE AMOUNT</label>
                                <input type="text" class="form-control text-end fw-bold text-danger" id="balance_amount" name="balance_amount" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2" id="credit_memo_container" style="display: none;">
                        <div class="col-12 col-sm-6 col-md-6">
                            <div class="">
                                <label for="credit_memo" class="form-label">CREDIT MEMO</label>
                                <input type="text" class="form-control text-end fw-bold text-info" id="credit_memo" name="credit_memo" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6">
                            <div class="">
                                <label for="status" class="form-label">STATUS</label>
                                <input type="text" class="form-control" id="status" name="status" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information Section -->
                    <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                        <div style="width:100%;"><hr></div>
                        <div class="apheaderSectionTitle">ADDITIONAL INFORMATION:</div>
                        <div style="width:100%;"><hr></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="">
                                <label for="remarks" class="form-label">REMARKS</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter any additional notes or remarks..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </x-slot:form_fields>

        <x-slot:modalFooterBtns>
            <div>
                <button type="button" class="btn btn-sm btn-success text-white" id="processPaymentBtn">Process Payment</button>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-secondary" id="closeAPBtn" data-bs-dismiss="modal">Close</button>
            </div>
        </x-slot:modalFooterBtns>
    </x-mainModal>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel"><i class="fas fa-credit-card me-2"></i>Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="paymentform">
                        <form id="paymentForm">
                            <input type="hidden" id="payment_ap_id" name="ap_id">
                            <input type="hidden" id="payment_type" name="payment_type" value="full">
                            
                            <!-- Account Balance Information -->
                            <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                                <div style="width:100%;"><hr></div>
                                <div class="paymentSectionTitle">ACCOUNT BALANCE:</div>
                                <div style="width:100%;"><hr></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12 col-sm-6 col-md-6">
                                    <div class="">
                                        <label class="form-label">TOTAL AMOUNT</label>
                                        <div id="payment_total_amount" class="form-control fw-bold bg-light"></div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-6" id="payment_balance_container">
                                    <div class="">
                                        <label class="form-label">CURRENT BALANCE</label>
                                        <div id="payment_balance_amount" class="form-control fw-bold text-danger bg-light"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Details -->
                            <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                                <div style="width:100%;"><hr></div>
                                <div class="paymentSectionTitle">PAYMENT DETAILS:</div>
                                <div style="width:100%;"><hr></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12 col-sm-6 col-md-6">
                                    <div class="">
                                        <label for="payment_method" class="form-label">PAYMENT TYPE <span class="text-danger">*</span></label>
                                        <select class="form-select" id="payment_method" name="payment_type" required>
                                            <option value="">Select payment type</option>
                                            <option value="cash">Cash</option>
                                            <option value="bank">Bank</option>
                                            <option value="gcash">GCash</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-6">
                                    <div class="">
                                        <label for="payment_reference_number" class="form-label">REFERENCE NUMBER</label>
                                        <input type="text" class="form-control" id="payment_reference_number" name="reference_number" placeholder="Enter reference number">
                                    </div>
                                </div>
                            </div>
                                
                            <!-- Cash Payment Fields - Hidden by default -->
                            <div id="cashPaymentFields" style="display: none;">
                                <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                                    <div style="width:100%;"><hr></div>
                                    <div class="paymentSectionTitle">CASH PAYMENT:</div>
                                    <div style="width:100%;"><hr></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-12 col-sm-8 col-md-8">
                                        <div class="">
                                            <label for="cash_payment_amount" class="form-label">PAYMENT AMOUNT <span class="text-danger">*</span></label>
                                            <input type="text" step="0.01" class="form-control" id="cash_payment_amount" name="cash_payment_amount">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4 col-md-4">
                                        <div class="">
                                            <label class="form-label">PAYMENT STATUS</label>
                                            <span id="cash_payment_type_indicator" class="badge bg-success d-block text-center py-2 mt-1">Full Payment</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <small class="text-muted">Enter amount less than balance for partial payment, exact amount for full payment</small>
                                    </div>
                                </div>
                            </div>

                        <!-- Bank Details Section - Hidden by default -->
                        <div id="bankDetailsSection" class="mb-3" style="display: none;">
                            <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                                <div style="width:100%;"><hr></div>
                                <div class="paymentSectionTitle">BANK DETAILS:</div>
                                <div style="width:100%;"><hr></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <div class="">
                                        <label for="bank_selection" class="form-label">SELECT BANK <span class="text-danger">*</span></label>
                                        <select class="form-select" id="bank_selection" name="bank_selection">
                                            <option value="">Choose Bank...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12 col-sm-6 col-md-6">
                                    <div class="">
                                        <label for="bank_account_name" class="form-label">ACCOUNT NAME</label>
                                        <input type="text" class="form-control" id="bank_account_name" name="bank_account_name" readonly>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-6">
                                    <div class="">
                                        <label for="bank_account_number" class="form-label">ACCOUNT NUMBER</label>
                                        <input type="text" class="form-control" id="bank_account_number" name="bank_account_number" readonly>
                                    </div>
                                </div>
                            </div>
                                    
                            <!-- Check Payment Option -->
                            <div class="row mb-2">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="pay_by_check" name="pay_by_check" disabled>
                                        <label class="form-check-label" for="pay_by_check">
                                            Pay by Check <small class="text-muted">(Select a bank first)</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Check Details Section - Hidden by default -->
                            <div id="checkDetailsSection" class="mt-3" style="display: none;">
                                <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                                    <div style="width:100%;"><hr></div>
                                    <div class="paymentSectionTitle">CHECK DETAILS:</div>
                                    <div style="width:100%;"><hr></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-12 col-sm-6 col-md-6">
                                        <div class="">
                                            <label for="check_payee" class="form-label">PAYEE <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="check_payee" name="check_payee" placeholder="Enter payee name">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-6">
                                        <div class="">
                                            <label for="check_date" class="form-label">CHECK DATE <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="check_date" name="check_date">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-12 col-sm-6 col-md-6">
                                        <div class="">
                                            <label for="check_number" class="form-label">CHECK NUMBER</label>
                                            <input type="text" class="form-control" id="check_number" name="check_number" placeholder="Enter check number (optional)">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-6">
                                        <div class="">
                                            <label for="check_amount_display" class="form-label">CHECK AMOUNT <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="check_amount_display" name="check_amount_display" placeholder="Enter check amount" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="">
                                            <label for="check_amount_in_words" class="form-label">AMOUNT IN WORDS <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="check_amount_in_words" name="check_amount_in_words" rows="3" placeholder="Amount will be converted to words automatically..." readonly></textarea>
                                            <small class="text-muted">This will be automatically filled when you enter the payment amount below</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                    
                            <!-- Bank Payment Amount and Status -->
                            <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                                <div style="width:100%;"><hr></div>
                                <div class="paymentSectionTitle">BANK PAYMENT:</div>
                                <div style="width:100%;"><hr></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12 col-sm-8 col-md-8">
                                    <div class="">
                                        <label for="bank_payment_amount" class="form-label">PAYMENT AMOUNT <span class="text-danger">*</span></label>
                                        <input type="text" step="0.01" class="form-control" id="bank_payment_amount" name="bank_payment_amount">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4 col-md-4">
                                    <div class="">
                                        <label class="form-label">PAYMENT STATUS</label>
                                        <span id="bank_payment_type_indicator" class="badge bg-success d-block text-center py-2 mt-1">Full Payment</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <small class="text-muted">Enter amount less than balance for partial payment, exact amount for full payment</small>
                                </div>
                            </div>
                        </div>

                        <!-- GCash Details Section - Hidden by default -->
                        <div id="gcashDetailsSection" class="mb-3" style="display: none;">
                            <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                                <div style="width:100%;"><hr></div>
                                <div class="paymentSectionTitle">GCASH DETAILS:</div>
                                <div style="width:100%;"><hr></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <div class="">
                                        <label for="gcash_selection" class="form-label">SELECT GCASH ACCOUNT <span class="text-danger">*</span></label>
                                        <select class="form-select" id="gcash_selection" name="gcash_selection">
                                            <option value="">Choose GCash Account...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12 col-sm-6 col-md-6">
                                    <div class="">
                                        <label for="gcash_account_name" class="form-label">ACCOUNT NAME</label>
                                        <input type="text" class="form-control" id="gcash_account_name" name="gcash_account_name" readonly>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-6">
                                    <div class="">
                                        <label for="gcash_account_number" class="form-label">ACCOUNT NUMBER</label>
                                        <input type="text" class="form-control" id="gcash_account_number" name="gcash_account_number" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- GCash Payment Amount and Status -->
                            <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                                <div style="width:100%;"><hr></div>
                                <div class="paymentSectionTitle">GCASH PAYMENT:</div>
                                <div style="width:100%;"><hr></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12 col-sm-8 col-md-8">
                                    <div class="">
                                        <label for="gcash_payment_amount" class="form-label">PAYMENT AMOUNT <span class="text-danger">*</span></label>
                                        <input type="text" step="0.01" class="form-control" id="gcash_payment_amount" name="gcash_payment_amount">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4 col-md-4">
                                    <div class="">
                                        <label class="form-label">PAYMENT STATUS</label>
                                        <span id="gcash_payment_type_indicator" class="badge bg-success d-block text-center py-2 mt-1">Full Payment</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <small class="text-muted">Enter amount less than balance for partial payment, exact amount for full payment</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                            <div style="width:100%;"><hr></div>
                            <div class="paymentSectionTitle">ADDITIONAL INFORMATION:</div>
                            <div style="width:100%;"><hr></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="">
                                    <label for="payment_remarks" class="form-label">PAYMENT REMARKS</label>
                                    <textarea class="form-control" id="payment_remarks" name="remarks" rows="3" placeholder="Enter any additional notes or remarks..."></textarea>
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="paymentForm" class="btn btn-success">Process Payment</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('pagejs')
<script type="text/javascript" src="{{ asset('assets/js/vendor/virtual-select.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/accounts-payable/accounts-payable.js') }}"></script>
@endsection