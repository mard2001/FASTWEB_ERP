@extends('Layout.layout')

@section('html_title')
    <title>Payment History - FASTWEB ERP</title>
    <link href="https://cdn.materialdesignicons.com/6.5.95/css/materialdesignicons.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('title_header')
    <x-header title="Payment History" />
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
            <label class="form-label">SUPPLIER</label>
            <div id="supplierFilter_VS" class="VSSelect"></div>
        </div>
        <div class="mb-1 whMoverangeDiv">
            <label class="form-label">PAYMENT STATUS</label>
            <div id="paymentStatusFilter_VS" class="VSSelect"></div>
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
                        <span class="contentTitle">Today's Payments</span>
                        <p class="contentValue" id="total-today">₱--- Today</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-3">
            <div class="containerStyle">
                <div class="d-flex mx-3 stockOut">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-calendar-month'></span>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">This Month</span>
                        <p class="contentValue" id="total-month">₱--- Month</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-3">
            <div class="containerStyle">
                <div class="d-flex mx-3 totalProfit">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-currency-php'></span>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Total Amount</span>
                        <p class="contentValue" id="total-amount">₱--- Total</p>
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
        #PaymentHistoryTable thead tr{
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
        
        /* Amount styling */
        .amount-positive {
            color: var(--success-color, #28a745);
            font-weight: 600;
        }
        
        /* Modal header styling */
        #paymentHistoryModal .modalHeaderTitle {
            color: var(--primary-color) !important;
            font-weight: bold !important;
            font-size: 1.25rem !important;
        }

        /* Supplier info styling */
        .supplier-info {
            max-width: 200px;
        }
        .supplier-info .fw-bold {
            font-size: 0.6rem;
            line-height: 1.2;
        }
        .supplier-info small {
            font-size: 0.7rem;
        }

        /* Date range picker positioning fix */
        .daterangepicker {
            z-index: 9999 !important;
            position: absolute !important;
        }
        
        /* Filter container styling */
        .filteringOptionDiv {
            position: relative;
            z-index: 1;
        }
        
        /* Ensure proper positioning for date range */
        #dateRange {
            position: relative;
        }
    </style>

    <x-contentButtonDiv downloadFunc="true">
        <x-slot:additionalButtons>
            <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="phPrintAllBtn">
                <div class="btnImg me-2" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48cGF0aCBmaWxsPSJjdXJyZW50Q29sb3IiIGQ9Ik0xOCAzSDZ2NGgxMlYzem0tMiA2SDh2Mmg4VjltMiAydjZIOHYtNmgxMHptLTIgNEg4djJoOHYtMnoiLz48L3N2Zz4='); background-size: 16px; background-repeat: no-repeat; background-position: center; width: 16px; height: 16px;">
                </div>
                <span>Print Report</span>
            </div>
        </x-slot:additionalButtons>
    </x-contentButtonDiv>

    <x-table id="PaymentHistoryTable">
        <x-slot:td>
            <!-- Table headers will be created dynamically by DataTables -->
        </x-slot:td>
    </x-table>
@endsection

@section('modal')
    <x-mainModal mainModalTitle="paymentHistoryModal" modalDialogClass="modal-lg" modalHeaderTitle="PAYMENT HISTORY RECORD" modalSubHeaderTitle="View payment record details.">
        <x-slot:form_fields>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" class="form-control" id="view_payment_date" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Supplier Code</label>
                        <input type="text" class="form-control" id="view_supplier_code" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" id="view_supplier_name" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">RR Number</label>
                        <input type="text" class="form-control" id="view_rr_number" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" class="form-control" id="view_reference_number" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Invoice Date</label>
                        <input type="date" class="form-control" id="view_invoice_date" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Invoice Amount</label>
                        <input type="text" class="form-control" id="view_invoice_amount" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Payment Amount</label>
                        <input type="text" class="form-control" id="view_payment_amount" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Payment Type</label>
                        <input type="text" class="form-control" id="view_payment_type" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <input type="text" class="form-control" id="view_payment_method" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Terms</label>
                        <input type="text" class="form-control" id="view_terms" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Process By</label>
                        <input type="text" class="form-control" id="view_process_by" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" id="view_remarks" rows="3" readonly></textarea>
                    </div>
                </div>
            </div>
        </x-slot:form_fields>

        <x-slot:modalFooterBtns>
            <div></div>
            <div>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </x-slot:modalFooterBtns>
    </x-mainModal>

@endsection

@section('pagejs')
<script type="text/javascript" src="{{ asset('assets/js/vendor/virtual-select.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/payment-history/payment-history.js') }}"></script>
@endsection
