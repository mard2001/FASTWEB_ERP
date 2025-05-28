@extends('Layout.layout')

@section('html_title')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <title>Receiving Report</title>
@endsection

@section('title_header')
    <x-header title="Receiving Report" />
@endsection

@section('table')
<style>
    .secBtns .selected {
        background-color: rgba(23, 162, 184, 0.10);
        border-bottom: 2px solid #0275d8;
    }

    .secBtns button {
        border-bottom: 2px solid transparent;
        border-top: 1px solid transparent;
        border-left: 1px solid transparent;
        border-right: 1px solid transparent;
    }

    .secBtns button:hover {
        background-color: rgba(23, 162, 184, 0.10);
        border-bottom: 2px solid #0275d8;
        border-top: 0.5px solid #0275d8;
        border-left: 0.5px solid #0275d8;
        border-right: 0.5px solid #0275d8;
    }

    .autocompleteHover:hover {
        background-color: #3B71CA;
        cursor: pointer;
    }

    .ui-autocomplete {
        z-index: 9999 !important;
    }

    .fs15 * {
        font-size: 15px;
    }
</style>

<x-table id="rrTable">
    <x-slot:td>
    </x-slot:td>
</x-table>
@endsection

@section('modal')

<style>
    #editXmlDataModal .modal-dialog {
        width: 70vw !important;
        /* Set width to 90% of viewport width */
        max-width: none !important;
        /* Remove any max-width constraints */
    }

    #editXmlDataModal .modal-content {
        margin: auto !important;
        /* Center the modal content */
    }
</style>

<x-mainModal mainModalTitle="rrMainModal" modalDialogClass="modal-xl" modalHeaderTitle="RECEIVING REPORT" modalSubHeaderTitle="All key details related to this receiving transaction.">
    <x-slot:form_fields>
        {{-- <h2 class="text-center mb-5">Receiving Report</h2> --}}
        
        <div class="row g-4">
            <div class="col">
                <table style="font-size: 14px">
                    <tbody>
                        <tr>
                            <td></td>
                            <th></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Supplier Code:</td>
                            <th class="px-2"><span class="supCode" style="font-weight: 550"></span></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Supplier Name:</td>
                            <th class="px-2"><span class="supName" style="font-weight: 550"></span></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Supplier TIN:</td>
                            <th class="px-2"><span class="supTin" style="font-weight: 550"></span></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Address:</td>
                            <th class="px-2"><span class="supAdd" style="font-weight: 550"></span></th>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col ">
                <table style="font-size: 14px">
                    <tbody>
                        <tr>
                            <td></td>
                            <th></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">RR No.:</td>
                            <th class="px-2"><span class="rrNo" style="font-weight: 550"></span></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Date:</td>
                            <th class="px-2"><span class="date" style="font-weight: 550"></span></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Reference:</td>
                            <th class="px-2"><span class="reference" style="font-weight: 550"></span></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Status:</td>
                            <th class="px-2"><span class="status" style="font-weight: 550"></span></th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <hr>
        <table class="table" style="font-size: 12px">
            <thead>
                <tr>
                    <th scope="col" class="text-center">No.</th>
                    <th scope="col" class="text-center">Item</th>
                    <th scope="col" class="text-center">Description</th>
                    <th scope="col" class="text-center">Quantity</th>
                    <th scope="col" class="text-center">OuM</th>
                    <th scope="col" class="text-center">WhsCode</th>
                    <th scope="col" class="text-center">Unit Price</th>
                    <th scope="col" class="text-center">Net of Vat</th>
                    <th scope="col" class="text-center">Vat</th>
                    <th scope="col" class="text-center">Gross</th>
                </tr>
            </thead>
            <tbody class="rrTbody">
            </tbody>
        </table>
    </x-slot:form_fields>

    <x-slot:modalFooterBtns>
        <div>
            <button type="button" class="btn btn-sm btn-primary" id="rrPrintPage">Print Report</button>
            <button type="button" class="btn btn-sm btn-outline-success" id="rrConfirm">Confirm Report</button>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
    </x-slot:modalFooterBtns>
</x-mainModal>

@endsection

@section('pagejs')

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

<script type="text/javascript" src="{{ asset('assets/js/vendor/virtual-select.min.js') }}"></script>

<script src="{{ asset('assets/js/receiving-report/rr.js') }}"></script>

@endsection