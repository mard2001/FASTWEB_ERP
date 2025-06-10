@extends('Layout.layout')

@section('html_title')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <title>Stock Count</title>
@endsection

@section('title_header')
    <x-header title="Stock Count" />
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

        .dtDetailssearchInput{
            font-size: 10px;
        }

        .dtDetailssearchLabel{
            background-color: #33336F;
            color: #FFF !important;
            font-size: 10.5px !important;
            border-top-left-radius: 5px;
            border-bottom-left-radius: 5px;
        }

    </style>

    <x-contentButtonDiv downloadFunc="true">
        <x-slot:additionalButtons>
            <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="manualSheetDLBtn">
                <div class="btnImg me-2" id="dlImg">
                </div>
                <span>Download Manual Sheet</span>
            </div>
        </x-slot:additionalButtons>
    </x-contentButtonDiv>

    <x-table id="icTable">
        <x-slot:td>
            <td class="col">id</td>
            <td class="col">Status</td>
            <td class="col">User</td>
            <td class="col">Motation</td>
            <td class="col">DATECREATED</td>
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

        .invCountTableHeader:hover{
            background: transparent
        }
    </style>

    <x-mainModal mainModalTitle="invCountMainModal" modalDialogClass="modal-lg" modalHeaderTitle="INVENTORY COUNT" modalSubHeaderTitle="Track discrepancies between recorded and actual stock levels.">
        <x-slot:form_fields>
            <div id="itemModalFields" class="stheaderform">
                <div class="row">
                    <div class="col-6">
                        <div class="">
                            <label for="countDate" class="form-label">Date Created</label>
                            <input type="text" disabled class="form-control" id="countDate" name="countDate">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="">
                            <label for="countUser" class="form-label">Placed By</label>
                            <input type="text" disabled class="form-control" id="countUser" name="countUser">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="">
                            <label for="countID" class="form-label">Stock Count Reference ID</label>
                            <input type="text" disabled class="form-control" id="countID" name="countID">
                        </div>
                    </div>

                </div>
            </div>
            <table class="table" style="font-size: 12px; width: 100%;" id="ICDetails">
            </table>
        </x-slot:form_fields>
        <x-slot:modalFooterBtns>
            <div>
                <button type="button" class="btn btn-sm btn-danger" id="deleteICBtn">Delete Sheet</button>
                <button type="button" class="btn btn-sm btn-primary" id="rePrintPage" style="display: none;">Print Sheet</button>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary text-white" id="confirmIC">Confrim Sheet</button>
                <button type="button" class="btn btn-sm btn-primary text-white" id="addICBtn">Add Sheet</button>
                <button type="button" class="btn btn-sm btn-info text-white" id="editICBtn">Edit Sheet</button>
                <button type="button" class="btn btn-sm btn-danger text-white" id="cancelEditICBtn">Cancel Changes</button>
                <button type="button" class="btn btn-sm btn-secondary" id="closeICBtn" data-bs-dismiss="modal">Close</button>
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

<script src="{{ asset('assets/js/inventory-maintenance/invCount.js') }}"></script>

@endsection
