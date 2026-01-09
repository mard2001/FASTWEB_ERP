@extends('Layout.layout')

@section('html_title')
    <title>Accounts Payable - FASTWEB ERP</title>
    <link href="https://cdn.materialdesignicons.com/6.5.95/css/materialdesignicons.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Custom Light Blue Tooltip Styles */
        .custom-tooltip {
            position: absolute;
            background: #DFE9FF;
            color: #6E6E6E;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            max-width: 250px;
            z-index: 1060;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #5188FD;
            display: none;
            word-wrap: break-word;
        }

        .custom-tooltip::before {
            content: '';
            position: absolute;
            top: -6px;
            right: 15px;
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-bottom: 6px solid #DFE9FF;
        }

        .custom-tooltip::after {
            content: '';
            position: absolute;
            top: -7px;
            right: 15px;
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-bottom: 6px solid #5188FD;
        }

        .custom-tooltip.show {
            display: block;
            animation: fadeInTooltip 0.2s ease-in-out;
        }

        @keyframes fadeInTooltip {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .custom-tooltip-trigger:hover {
            color: #5188FD !important;
        }
    </style>
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
            background-color: var(--info-color, #5188FD);
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

        /* Read-only field styling in AP modal */
        #accountsPayableModal .form-control[readonly],
        #accountsPayableModal .form-select[readonly] {
            background-color: #f8f9fa !important;
            cursor: not-allowed;
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

        /* Accounts Payable Modal Custom Styling - Matching Process Payment Modal */
        #accountsPayableModal .modal-dialog {
            max-width: 550px !important;
            width: 550px !important;
        }

        #accountsPayableModal .modal-content {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
            overflow: hidden;
            background: #ffffff;
            width: 550px !important;
            height: 540px !important;
            max-height: 606px !important;
        }

        #accountsPayableModal .modal-body {
            padding: 5px 15px !important;
            background: #f8f9fa;
            overflow-y: auto !important;
            max-height: calc(606px - 120px) !important; /* Subtract header and footer height */
        }

        #accountsPayableModal .form-control:focus,
        #accountsPayableModal .form-select:focus {
            border-color: #5188FD;
            box-shadow: 0 0 0 0.2rem rgba(81, 136, 253, 0.1);
            outline: none;
        }

        #accountsPayableModal .form-check-input:checked {
            background-color: #5188FD;
            border-color: #5188FD;
        }

        #accountsPayableModal .form-check-input:focus {
            border-color: #5188FD;
            box-shadow: 0 0 0 0.2rem rgba(81, 136, 253, 0.1);
        }

        #accountsPayableModal .btn:hover {
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }

        #accountsPayableModal .btn:focus {
            box-shadow: 0 0 0 0.2rem rgba(81, 136, 253, 0.25);
        }

        /* Poppins font for entire accounts payable modal */
        #accountsPayableModal,
        #accountsPayableModal * {
            font-family: 'Poppins', sans-serif !important;
        }

        #accountsPayableModal .modal-title {
            font-family: 'Poppins', sans-serif !important;
            font-weight: 600 !important;
            color: #333 !important;
        }

        /* Section title styling to match screenshot */
        #accountsPayableModal .apheaderSectionTitle {
            font-family: 'Poppins', sans-serif !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #5188FD !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 !important;
            padding: 0 !important;
            text-align: left !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
        }

        /* Section divider line styling */
        #accountsPayableModal .section-divider {
            border: none !important;
            border-top: 1px solid #dee2e6 !important;
            margin: 0 !important;
            height: 1px !important;
        }

        /* Form labels styling */
        #accountsPayableModal .form-label {
            font-family: 'Poppins', sans-serif !important;
            font-size: 10px !important;
            font-weight: 500 !important;
            color: #666 !important;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 5px !important;
        }

        /* Input field styling to match screenshot */
        #accountsPayableModal .form-control {
            font-family: 'Poppins', sans-serif !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            height: 30px !important;
            width: 245px !important;
            padding: 0px 11px !important;
            border: 1px solid #ddd !important;
            border-radius: 5px !important;
            background: #ffffff !important;
            color: #333 !important;
            gap: 10px !important;
            opacity: 1 !important;
            transition: all 0.2s ease;
        }

        /* Select dropdown styling */
        #accountsPayableModal .form-select {
            font-family: 'Poppins', sans-serif !important;
            font-size: 13px !important;
            font-weight: 400 !important;
            height: 30px !important;
            width: 245px !important;
            padding: 9px 11px !important;
            border: 1px solid #ddd !important;
            border-radius: 5px !important;
            background: #ffffff !important;
            color: #333 !important;
            gap: 10px !important;
            opacity: 1 !important;
        }

        /* Read-only fields styling to match screenshot */
        #accountsPayableModal .form-control[readonly] {
            background-color: #e9ecef !important;
            color: #495057 !important;
            border-color: #ced4da !important;
        }

        /* Textarea styling */
        #accountsPayableModal textarea.form-control {
            width: 517px !important;
            min-height: 76px !important;
            padding: 9px 11px !important;
            border-radius: 5px !important;
            gap: 10px !important;
            opacity: 1 !important;
            resize: vertical;
        }

        /* Amount fields styling */
        #accountsPayableModal #total_amount {
            background-color: #e3f2fd !important;
            font-weight: 600 !important;
            color: #1976d2 !important;
        }

        #accountsPayableModal #balance_amount {
            background-color: #ffebee !important;
            font-weight: 600 !important;
            color: #d32f2f !important;
        }

        #accountsPayableModal #credit_memo {
            background-color: #e8f5e8 !important;
            font-weight: 600 !important;
            color: #2e7d32 !important;
        }

        /* Section dividers */
        #accountsPayableModal .d-flex.align-items-center hr {
            border-color: #dee2e6 !important;
            margin: 0 !important;
        }

        /* Row spacing */
        #accountsPayableModal .row.mb-2 {
            margin-bottom: 1rem !important;
        }

        #accountsPayableModal .row.mb-3 {
            margin-bottom: 1.5rem !important;
        }

        /* Button styling */
        #accountsPayableModal .btn {
            font-family: 'Poppins', sans-serif !important;
            font-weight: 500 !important;
            border-radius: 6px !important;
            transition: all 0.2s ease;
        }

        /* Modal footer */
        #accountsPayableModal .modal-footer {
            background: #ffffff !important;
            border-top: 1px solid #dee2e6 !important;
            padding: 8px 13px !important;
            display: flex !important;
            justify-content: flex-end !important;
        }

        /* Modal footer buttons alignment */
        #accountsPayableModal .modal-footer .d-flex {
            align-items: center !important;
        }

        #accountsPayableModal .modal-footer .btn {
            margin: 0 !important;
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

        /* Process Payment Modal Custom Styling */
        #paymentModal .modal-content {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
            overflow: hidden;
        }

        #paymentModal .form-control:focus,
        #paymentModal .form-select:focus {
            border-color: #5188FD;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
            outline: none;
        }

        #paymentModal .form-check-input:checked {
            background-color: #5188FD;
            border-color: #5188FD;
        }

        #paymentModal .form-check-input:focus {
            border-color: #5188FD;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
        }

        #paymentModal .btn:hover {
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }

        #paymentModal .btn:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Poppins font for entire payment modal */
        #paymentModal,
        #paymentModal * {
            font-family: 'Poppins', sans-serif !important;
        }

        #paymentModal .modal-title {
            font-family: 'Poppins', sans-serif !important;
        }

        #paymentModal h6,
        #paymentModal .form-label,
        #paymentModal .form-control,
        #paymentModal .form-select,
        #paymentModal textarea,
        #paymentModal input,
        #paymentModal button,
        #paymentModal .btn {
            font-family: 'Poppins', sans-serif !important;
        }

        /* Custom input styling to match Figma specifications */
        #paymentModal .form-control {
            width: 245px !important;
            height: 32px !important;
            padding: 0 11px !important;
            border-radius: 5px !important;
            border: 1px solid #C9C9C9 !important;
            background: #FFFFFF !important;
            color: #000000 !important;
            gap: 10px;
            opacity: 1;
            font-size: 12px !important;
            line-height: 30px !important;
            display: flex !important;
            align-items: center !important;
        }

        /* Separate styling for select dropdowns to preserve arrows */
        #paymentModal .form-select {
            width: 245px !important;
            height: 32px !important;
            padding: 0px 11px !important;
            border-radius: 5px !important;
            border: 1px solid #C9C9C9 !important;
            background: #FFFFFF !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23000000' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 11px center !important;
            background-size: 16px 12px !important;
            color: #000000 !important;
            opacity: 1;
            font-size: 12px !important;
        }

        /* Special handling for read-only fields */
        #paymentModal .form-control[readonly] {
            background-color: #f8f9fa !important;
        }

        /* Override for textarea to maintain proper height */
        #paymentModal textarea.form-control {
            height: auto !important;
            min-height: 32px !important;
        }

        /* Override for full-width display fields like payment amounts */
        #paymentModal #payment_total_amount,
        #paymentModal #payment_original_amount {
            width: 100% !important;
        }

        /* Override for full-width textarea fields */
        #paymentModal #payment_remarks,
        #paymentModal #check_amount_in_words {
            width: 100% !important;
        }
    </style>

    <x-contentButtonDiv downloadFunc="true" :addFunc="false">
        <x-slot:additionalButtons>
            <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="apRefreshBtn">
                <div class="btnImg me-2" style="width: 16px; height: 16px; display: flex; align-items: center; justify-content: center;">
                    <span class="mdi mdi-refresh" style="font-size: 16px;"></span>
                </div>
                <span>Refresh Data</span>
            </div>
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
                    <div class="mb-1">
                        <div class="d-flex align-items-center">
                            <div class="apheaderSectionTitle">TRANSACTION INFORMATION</div>
                            <div class="flex-grow-1 ms-3">
                                <hr class="section-divider">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-6 col-md-6">
                            <div class="">
                                <label for="supplier_display" class="form-label">SUPPLIER <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="supplier_display" name="supplier_display" readonly>
                                <div id="supplier_code_VS" name="supplier_code" class="form-control bg-white p-0 border-0" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6">
                            <div class="">
                                <label for="date" class="form-label">DATE <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date" name="date" required readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Reference Information Section -->
                    <div class="mb-1">
                        <div class="d-flex align-items-center">
                            <div class="apheaderSectionTitle">REFERENCE INFORMATION</div>
                            <div class="flex-grow-1 ms-3">
                                <hr class="section-divider">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="mb-1">
                                <label for="rr_number" class="form-label">RR NUMBER</label>
                                <input type="text" class="form-control" id="rr_number" name="rr_number" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="mb-1">
                                <label for="reference_number" class="form-label">REFERENCE NUMBER</label>
                                <input type="text" class="form-control" id="reference_number" name="reference_number" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-6">
                            <div class="">
                                <label for="terms" class="form-label">TERMS</label>
                                <input type="text" class="form-control" id="terms" name="terms" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information Section -->
                    <div class="mb-1">
                        <div class="d-flex align-items-center">
                            <div class="apheaderSectionTitle">FINANCIAL INFORMATION</div>
                            <div class="flex-grow-1 ms-3">
                                <hr class="section-divider">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-6 col-md-6">
                            <div class="">
                                <label for="total_amount" class="form-label">TOTAL AMOUNT <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-end fw-bold" id="total_amount" name="total_amount" required readonly>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6" id="balance_amount_container">
                            <div class="">
                                <label for="balance_amount" class="form-label">BALANCE AMOUNT</label>
                                <input type="text" class="form-control text-end fw-bold text-danger" id="balance_amount" name="balance_amount" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-6 col-md-6" id="total_paid_container">
                            <div class="">
                                <label for="total_paid" class="form-label">TOTAL PAID</label>
                                <input type="text" class="form-control text-end fw-bold text-success" id="total_paid" name="total_paid" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6">
                            <div class="">
                                <label for="credit_memo" class="form-label">CREDIT MEMO</label>
                                <input type="text" class="form-control text-end fw-bold text-info" id="credit_memo" name="credit_memo" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2" id="credit_memo_container" style="display: none;">
                        <div class="col-12 col-sm-6 col-md-6">
                            <div class="">
                                <label for="status" class="form-label">STATUS</label>
                                <input type="text" class="form-control" id="status" name="status" readonly>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </x-slot:form_fields>

        <x-slot:modalFooterBtns>
            <div class="d-flex justify-content-end" style="gap: 10px;">
                <button type="button" class="btn btn-sm btn-secondary" id="closeAPBtn" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-sm btn-success text-white" id="processPaymentBtn">Process Payment</button>
            </div>
        </x-slot:modalFooterBtns>
    </x-mainModal>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 550px;">
            <div class="modal-content" style="max-height: 857px; height: auto; min-height: 400px;">
                <div class="modal-header d-flex justify-content-between align-items-center" style="background: #f8f9fa; padding: 14px 24px; border-bottom: 1px solid #e9ecef;">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-receipt me-2" style="font-size: 20px; color: #495057;"></i>
                        <h5 class="modal-title mb-0" id="paymentModalLabel" style="font-weight: 600; color: #495057; font-size: 16px;">Process Payment</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="width: 30px; height: 30px; padding: 7px; border-radius: 5px; opacity: 1;"></button>
                </div>
                <div class="modal-body" style="padding: 20px 20px 0px; overflow-y: auto; max-height: calc(90vh - 120px);">
                    <div class="paymentform">
                        <form id="paymentForm">
                            <input type="hidden" id="payment_ap_id" name="ap_id">
                            <input type="hidden" id="payment_type" name="payment_type" value="full">
                            
                            <!-- Account Balance Section -->
                            <div class="mb-2">
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0 me-2" style="color: #5188FD; font-size: 14px; text-transform: uppercase; white-space: nowrap;">ACCOUNT BALANCE</h6>
                                    <hr style="flex: 1; border: 0; border-top: 1px solid #dee2e6; margin: 0;">
                                    <i class="mdi mdi-information-outline ms-2 custom-tooltip-trigger" style="color: #6c757d; font-size: 18px; cursor: pointer;" data-tooltip="Total Amount and current balance information for the selected customer account"></i>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">TOTAL AMOUNT</label>
                                        <div id="payment_original_amount" class="form-control" style="background: #fff; border: 1px solid #dee2e6; padding: 12px; font-weight: 600; font-size: 16px; color: #495057;">P130,582.94</div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">TOTAL BALANCE</label>
                                        <div id="payment_total_amount" class="form-control" style="background: #fff; border: 1px solid #dee2e6; padding: 12px; font-weight: 600; font-size: 16px; color: #495057;">P130,582.94</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Details Section -->
                            <div class="mb-2">
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0 me-2" style="color: #5188FD; font-size: 14px; text-transform: uppercase; white-space: nowrap;">PAYMENT DETAILS</h6>
                                    <hr style="flex: 1; border: 0; border-top: 1px solid #dee2e6; margin: 0;">
                                    <i class="mdi mdi-information-outline ms-2 custom-tooltip-trigger" style="color: #6c757d; font-size: 18px; cursor: pointer;" data-tooltip="Select payment type and reference number for this transaction"></i>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label for="payment_method" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">PAYMENT TYPE</label>
                                        <select class="form-select" id="payment_method" name="payment_type" required>
                                            <option value="" disabled selected>Select Payment Type</option>
                                            <option value="bank">BANK</option>
                                            <option value="cash">CASH</option>
                                            <option value="gcash">GCASH</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label for="payment_reference_number" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">REFERENCE NUMBER</label>
                                        <input type="text" class="form-control" id="payment_reference_number" name="reference_number" placeholder="Enter reference number" style="padding: 12px; border: 1px solid #dee2e6; font-size: 14px;">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bank Details Section -->
                            <div class="mb-2" id="bankDetailsSection" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0 me-2" style="color: #5188FD; font-size: 14px; text-transform: uppercase; white-space: nowrap;">BANK DETAILS</h6>
                                    <hr style="flex: 1; border: 0; border-top: 1px solid #dee2e6; margin: 0;">
                                    <i class="mdi mdi-information-outline ms-2 custom-tooltip-trigger" style="color: #6c757d; font-size: 18px; cursor: pointer;" data-tooltip="Select a bank account for this payment transaction"></i>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label for="bank_selection" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">SELECT BANK</label>
                                        <select class="form-select" id="bank_selection" name="bank_selection">
                                            <option value="bpi" selected>BPI</option>
                                            <option value="bdo">BDO</option>
                                            <option value="metrobank">Metrobank</option>
                                        </select>
                                    </div>
                                    <div class="col-6" id="bankPaymentAmountColumn">
                                        <label for="bank_payment_amount" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">PAYMENT AMOUNT</label>
                                        <input type="text" class="form-control" id="bank_payment_amount" name="bank_payment_amount" placeholder="0.00" style="padding: 12px; border: 1px solid #dee2e6; font-size: 14px;">
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label for="bank_account_name" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">ACCOUNT NAME</label>
                                        <input type="text" class="form-control" id="bank_account_name" name="bank_account_name" value="FAST UNMERCHANT INC." readonly style="padding: 12px; border: 1px solid #dee2e6; font-size: 14px; background-color: #f8f9fa;">
                                    </div>
                                    <div class="col-6">
                                        <label for="bank_account_number" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">ACCOUNT NUMBER</label>
                                        <input type="text" class="form-control" id="bank_account_number" name="bank_account_number" value="8234-2342-9035" readonly style="padding: 12px; border: 1px solid #dee2e6; font-size: 14px; background-color: #f8f9fa;">
                                    </div>
                                </div>
                                <div class="row g-3 align-items-end">
                                    <div class="col-6">
                                        <div class="form-check" style="padding-bottom: 8px;">
                                            <input class="form-check-input" type="checkbox" id="pay_by_check" name="pay_by_check" style="width: 18px; height: 18px; margin-top: 4px;">
                                            <label class="form-check-label" for="pay_by_check" style="font-size: 14px; font-weight: 500; color: #495057; margin-left: 8px;">
                                                Pay with Check
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- GCASH Details Section - Hidden by default -->
                            <div class="mb-2" id="gcashDetailsSection" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0 me-2" style="color: #5188FD; font-size: 14px; text-transform: uppercase; white-space: nowrap;">GCASH DETAILS</h6>
                                    <hr style="flex: 1; border: 0; border-top: 1px solid #dee2e6; margin: 0;">
                                    <i class="mdi mdi-information-outline ms-2 custom-tooltip-trigger" style="color: #6c757d; font-size: 18px; cursor: pointer;" data-tooltip="Choose a GCash account for payment information and transaction details"></i>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <label for="gcash_selection" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">GCASH ACCOUNT</label>
                                        <select class="form-select" id="gcash_selection" name="gcash_selection">
                                            <option value="">Select GCash Account</option>
                                            <!-- Options will be populated from tblGcash -->
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label for="gcash_account_number" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">ACCOUNT NUMBER</label>
                                        <input type="text" class="form-control" id="gcash_account_number" name="gcash_account_number" placeholder="Select account to view number" readonly style="padding: 12px; border: 1px solid #dee2e6; font-size: 14px; background-color: #f8f9fa;">
                                    </div>
                                </div>
                            </div>

                            <!-- GCASH Payment Section - Hidden by default -->
                            <div class="mb-2" id="gcashPaymentSection" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0 me-2" style="color: #5188FD; font-size: 14px; text-transform: uppercase; white-space: nowrap;">GCASH PAYMENT</h6>
                                    <hr style="flex: 1; border: 0; border-top: 1px solid #dee2e6; margin: 0;">
                                    <i class="mdi mdi-information-outline ms-2 custom-tooltip-trigger" style="color: #6c757d; font-size: 18px; cursor: pointer;" data-tooltip="Enter GCash Amount"></i>
                                </div>
                                <div class="mb-3">
                                    <label for="gcash_amount_display" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">PAYMENT AMOUNT</label>
                                    <input type="text" class="form-control fw-bold" id="gcash_amount_display" name="gcash_amount_display" placeholder="0.00" style="padding: 12px; border: 1px solid #dee2e6; font-size: 14px; background-color: #fff; color: #495057;">
                                </div>
                            </div>

                            <!-- CASH Payment Section - Hidden by default -->
                            <div class="mb-2" id="cashPaymentSection" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0 me-2" style="color: #5188FD; font-size: 14px; text-transform: uppercase; white-space: nowrap;">CASH PAYMENT</h6>
                                    <hr style="flex: 1; border: 0; border-top: 1px solid #dee2e6; margin: 0;">
                                    <i class="mdi mdi-information-outline ms-2 custom-tooltip-trigger" style="color: #6c757d; font-size: 18px; cursor: pointer;" data-tooltip="Record cash payment details and reference information"></i>
                                </div>
                                <div class="mb-3">
                                    <label for="cash_amount_display" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">PAYMENT AMOUNT</label>
                                    <input type="text" class="form-control fw-bold" id="cash_amount_display" name="cash_amount_display" placeholder="0.00" style="padding: 12px; border: 1px solid #dee2e6; font-size: 14px; background-color: #fff; color: #495057;">

                                </div>
                            </div>

                            <!-- Check Details Section -->
                            <div class="mb-2" id="checkDetailsSection" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0 me-2" style="color: #5188FD; font-weight: 600; font-size: 14px; text-transform: uppercase; white-space: nowrap;">CHECK DETAILS</h6>
                                    <hr style="flex: 1; border: 0; border-top: 1px solid #dee2e6; margin: 0;">
                                    <i class="mdi mdi-information-outline ms-2 custom-tooltip-trigger" style="color: #6c757d; font-size: 18px; cursor: pointer;" data-tooltip="Enter check number, date, and amount information"></i>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label for="check_payee" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">PAYEE</label>
                                        <input type="text" class="form-control" id="check_payee" name="check_payee" value="DAVEN NEMENZO" style="padding: 12px; border: 1px solid #dee2e6; font-size: 14px;">
                                    </div>
                                    <div class="col-6">
                                        <label for="check_date" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">ACCOUNT NAME</label>
                                        <input type="date" class="form-control" id="check_date" name="check_date" value="2025-09-22" style="padding: 12px; border: 1px solid #dee2e6; font-size: 14px;">
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label for="check_number" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">CHECK NUMBER</label>
                                        <input type="text" class="form-control" id="check_number" name="check_number" value="8234-2342-9035" style="padding: 12px; border: 1px solid #dee2e6; font-size: 14px;">
                                    </div>
                                    <div class="col-6">
                                        <label for="check_amount_display" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">CHECK AMOUNT</label>
                                        <div style="position: relative;">
                                            <input type="text" class="form-control fw-bold" id="check_amount_display" name="check_amount_display" value="130,582.31" style="padding: 12px; border: 1px solid #ffc107; font-size: 14px; background-color: #fff3cd; color: #856404;">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="check_amount_in_words" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">AMOUNT IN WORDS</label>
                                    <textarea class="form-control" id="check_amount_in_words" name="check_amount_in_words" rows="3" readonly style="padding: 12px; border: 1px solid #dee2e6; font-size: 14px; background-color: #f8f9fa; resize: none; line-height: 1.4;">One hundred thirty thousand five hundred eighty-two pesos and ninety-four centavos</textarea>
                                </div>
                            </div>

                            
                            <!-- Additional Information Section -->
                            <div class="mb-2">
                                <div class="d-flex align-items-center mb-3">
                                    <h6 class="mb-0 me-2" style="color: #5188FD; font-size: 14px; text-transform: uppercase; white-space: nowrap;">ADDITIONAL INFORMATION</h6>
                                    <hr style="flex: 1; border: 0; border-top: 1px solid #dee2e6; margin: 0;">
                                    <i class="mdi mdi-information-outline ms-2 custom-tooltip-trigger" style="color: #6c757d; font-size: 18px; cursor: pointer;" data-tooltip="Additional payment notes and reference information"></i>
                                </div>
                                <div class="mb-2">
                                    <label for="payment_remarks" class="form-label" style="color: #6c757d; font-size: 10px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">PAYMENT REMARKS</label>
                                    <textarea class="form-control" id="payment_remarks" name="remarks" rows="4" placeholder="Enter remarks here..." style="padding: 12px; border: 1px solid #dee2e6; font-size: 14px; resize: none; line-height: 1.4;"></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa; padding: 14px 24px; border-top: 1px solid #e9ecef; border-radius: 0 0 12px 12px;">
                    <div class="d-flex justify-content-end align-items-center" style="gap: 18px;">
                        <button type="button" class="btn" data-bs-dismiss="modal" style="
                            width: 71px; 
                            height: 30px; 
                            padding: 7px 11px; 
                            background-color: #9F9F9F; 
                            color: white; 
                            border: none; 
                            border-radius: 5px; 
                            font-size: 12px; 
                            font-weight: 600;
                            text-align: center;
                            line-height: 16px;
                            display: flex;
                            align-items: center;
                            justify-content: center;">CANCEL</button>
                        <button type="submit" form="paymentForm" class="btn" style="
                            width: 138px; 
                            height: 30px; 
                            padding: 7px 11px; 
                            background-color: #198754; 
                            color: white; 
                            border: none; 
                            border-radius: 5px; 
                            font-size: 12px; 
                            font-weight: 600;
                            text-align: center;
                            line-height: 16px;
                            display: flex;
                            align-items: center;
                            justify-content: center;">CONFIRM PAYMENT</button>
                    </div>
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

<script>
$(document).ready(function() {
    // Load GCash accounts when page loads
    loadGcashAccounts();
    
    // GCash selection change handler
    $('#gcash_selection').change(function() {
        const selectedOption = $(this).find('option:selected');
        const gcashData = selectedOption.data('gcash');
        
        if (gcashData) {
            $('#gcash_account_number').val(gcashData.AccountNumber);
        } else {
            $('#gcash_account_number').val('');
        }
    });
});

function loadGcashAccounts() {
    $.ajax({
        url: '/api/gcash',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
        },
        success: function(response) {
            const gcashSelect = $('#gcash_selection');
            gcashSelect.empty().append('<option value="">Select GCash Account</option>');
            
            if (response.success && response.data && response.data.length > 0) {
                response.data.forEach(function(gcash) {
                    const option = $(`<option value="${gcash.GcashID}">${gcash.AccountName}</option>`);
                    // Store the full GCash data in the option element for later use
                    option.data('gcash', {
                        GcashID: gcash.GcashID,
                        AccountName: gcash.AccountName,
                        AccountNumber: gcash.AccountNumber
                    });
                    gcashSelect.append(option);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading GCash accounts:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load GCash accounts'
            });
        }
    });
}

// Initialize Custom Tooltips
$(document).ready(function() {
    let currentTooltip = null;
    
    // Create tooltip element
    const $tooltip = $('<div class="custom-tooltip"></div>').appendTo('body');
    
    // Show tooltip on hover
    $('.custom-tooltip-trigger').on('mouseenter', function(e) {
        const $trigger = $(this);
        const tooltipText = $trigger.data('tooltip');
        
        if (tooltipText) {
            $tooltip.text(tooltipText).addClass('show');
            
            // Position tooltip
            const triggerOffset = $trigger.offset();
            const triggerWidth = $trigger.outerWidth();
            const triggerHeight = $trigger.outerHeight();
            const tooltipWidth = $tooltip.outerWidth();
            
            // Position below the icon with arrow at upper right
            const left = triggerOffset.left + triggerWidth - tooltipWidth + 10;
            const top = triggerOffset.top + triggerHeight + 8;
            
            $tooltip.css({
                left: left + 'px',
                top: top + 'px'
            });
            
            currentTooltip = $tooltip;
        }
    });
    
    // Hide tooltip on mouse leave
    $('.custom-tooltip-trigger').on('mouseleave', function() {
        $tooltip.removeClass('show');
        setTimeout(() => {
            if (!$tooltip.hasClass('show')) {
                $tooltip.text('');
            }
        }, 200);
        currentTooltip = null;
    });
    
    // Hide tooltip when scrolling
    $(window).on('scroll', function() {
        if (currentTooltip) {
            $tooltip.removeClass('show');
            currentTooltip = null;
        }
    });
});

</script>
@endsection