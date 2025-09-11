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
    </style>

    <x-contentButtonDiv downloadFunc="true">
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
            <form id="accountsPayableForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="supplier_code" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <div id="supplier_code_VS" name="supplier_code" class="form-control bg-white p-0 border-0"></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="rr_number" class="form-label">RR Number</label>
                            <input type="text" class="form-control" id="rr_number" name="rr_number">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="reference_number" class="form-label">Reference Number</label>
                            <input type="text" class="form-control" id="reference_number" name="reference_number">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="total_amount" class="form-label">Total Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="terms" class="form-label">Terms</label>
                            <input type="text" class="form-control" id="terms" name="terms">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <input type="text" class="form-control" id="status" name="status" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="balance_amount" class="form-label">Balance Amount</label>
                            <input type="number" step="0.01" class="form-control" id="balance_amount" name="balance_amount" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot:form_fields>

        <x-slot:modalFooterBtns>
            <div>
                <button type="button" class="btn btn-sm btn-success text-white" id="processPaymentBtn">Process Payment</button>
                <button type="button" class="btn btn-sm btn-danger text-white" id="deleteAPBtn">Delete Record</button>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary text-white" id="saveAPBtn">Save Changes</button>
                <button type="button" class="btn btn-sm btn-info text-white" id="editAPBtn">Edit Record</button>
                <button type="button" class="btn btn-sm btn-danger text-white" id="cancelEditAPBtn">Cancel Changes</button>
                <button type="button" class="btn btn-sm btn-secondary" id="closeAPBtn" data-bs-dismiss="modal">Close</button>
            </div>
        </x-slot:modalFooterBtns>
    </x-mainModal>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <input type="hidden" id="payment_ap_id" name="ap_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Total Amount</label>
                            <div id="payment_total_amount" class="form-control-plaintext fw-bold"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Current Balance</label>
                            <div id="payment_balance_amount" class="form-control-plaintext fw-bold text-danger"></div>
                        </div>
                        
                        <!-- Hidden field for payment type - will be set automatically -->
                        <input type="hidden" id="payment_type" name="payment_type" value="full">
                        
                        <div class="mb-3">
                            <label for="payment_amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                            <input type="text" step="0.01" class="form-control" id="payment_amount" name="payment_amount" required>
                            <div class="form-text">
                                <span id="payment_type_indicator" class="badge bg-success">Full Payment</span>
                                <small class="text-muted">Enter amount less than balance for partial payment, exact amount for full payment</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="payment_method" name="payment_type" required>
                                <option value="">Select payment type</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                                <option value="gcash">GCash</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_reference_number" class="form-label">Reference Number</label>
                            <input type="text" class="form-control" id="payment_reference_number" name="reference_number" placeholder="Enter reference number (optional)">
                            <div class="form-text">
                                <small class="text-muted">Enter check number, transaction ID, or other reference</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_remarks" class="form-label">Payment Remarks</label>
                            <textarea class="form-control" id="payment_remarks" name="remarks" rows="3"></textarea>
                        </div>
                    </form>
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