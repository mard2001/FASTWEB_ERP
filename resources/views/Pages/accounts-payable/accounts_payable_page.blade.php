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
            <div id="dateRange" style="background: #fff; cursor: pointer; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 0.375rem; width: 100%">
                <i class="fa fa-calendar"></i>&nbsp;
                <span></span> <i class="mdi mdi-chevron-down float-end"></i>
            </div>
        </div>
        <div class="mb-1 whMoverangeDiv">
            <label class="form-label">BRANCH</label>
            <div id="dateRange" style="background: #fff; cursor: pointer; padding: 8px 12px; border-radius: 0.375rem; width: 100%">
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
            background-color: #f8f9fa !important;
            transform: scale(1.01);
            transition: all 0.2s ease-in-out;
        }
        .clickable-row {
            transition: all 0.2s ease-in-out;
        }
        
        /* Status styling */
        .status-outstanding {
            background-color: #dc3545;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-settled {
            background-color: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-credit {
            background-color: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        /* Amount styling */
        .amount-positive {
            color: #28a745;
            font-weight: 600;
        }
        
        .amount-negative {
            color: #dc3545;
            font-weight: 600;
        }
        
        .amount-zero {
            color: #6c757d;
            font-weight: 500;
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
            <td class="col">Branch</td>
            <td class="col">Opening Balance</td>
            <td class="col">Invoices</td>
            <td class="col">Debit Notes</td>
            <td class="col">Credit Notes</td>
            <td class="col">Adjustments</td>
            <td class="col">Disbursements</td>
            <td class="col">Closing Balance</td>
            <td class="col">Status</td>
            <td class="col">Report Date</td>
        </x-slot:td>
    </x-table>
@endsection

@section('modal')
    <x-mainModal mainModalTitle="accountsPayableModal" modalDialogClass="modal-lg" modalHeaderTitle="ACCOUNTS PAYABLE RECORD" modalSubHeaderTitle="Add or edit accounts payable record details.">
        <x-slot:form_fields>
            <form id="accountsPayableForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="branch" class="form-label">Branch <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="branch" name="branch" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="report_date" class="form-label">Report Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="report_date" name="report_date" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="opening_balance" class="form-label">Opening Balance</label>
                            <input type="number" step="0.01" class="form-control" id="opening_balance" name="opening_balance" value="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="invoices" class="form-label">Invoices</label>
                            <input type="number" step="0.01" class="form-control" id="invoices" name="invoices" value="0">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="debit_notes" class="form-label">Debit Notes</label>
                            <input type="number" step="0.01" class="form-control" id="debit_notes" name="debit_notes" value="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="credit_notes" class="form-label">Credit Notes</label>
                            <input type="number" step="0.01" class="form-control" id="credit_notes" name="credit_notes" value="0">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="adjustments" class="form-label">Adjustments</label>
                            <input type="number" step="0.01" class="form-control" id="adjustments" name="adjustments" value="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="disbursements" class="form-label">Disbursements</label>
                            <input type="number" step="0.01" class="form-control" id="disbursements" name="disbursements" value="0">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="closing_balance" class="form-label">Closing Balance</label>
                            <input type="number" step="0.01" class="form-control" id="closing_balance" name="closing_balance" value="0">
                        </div>
                    </div>
                </div>
            </form>
        </x-slot:form_fields>

        <x-slot:modalFooterBtns>
            <div>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="accountsPayableForm" class="btn btn-sm btn-primary">Save Record</button>
            </div>
        </x-slot:modalFooterBtns>
    </x-mainModal>

@endsection

@section('pagejs')
<script type="text/javascript" src="{{ asset('assets/js/vendor/virtual-select.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/accounts-payable/accounts-payable.js') }}"></script>
@endsection