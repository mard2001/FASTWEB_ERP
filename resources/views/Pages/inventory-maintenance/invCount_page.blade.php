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

<x-invCount_modal>
    <x-slot:form_fields>
        <div class="row g-4">
            <div class="col">
                <table class="invCountTableHeader" style="font-size: 14px">
                    <tbody>
                        <tr>
                            <td style="white-space: nowrap;">Count ID:</td>
                            <th class="px-2"><span class="countID" style="font-weight: 550"></span></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Placed By:</td>
                            <th class="px-2"><span class="countUser text-uppercase" style="font-weight: 550"></span></th>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col ">
                <table class="invCountTableHeader" style="font-size: 14px">
                    <tbody>
                        <tr>
                            <td style="white-space: nowrap;">Date Created:</td>
                            <th class="px-2"><span class="countDate" style="font-weight: 550"></span></th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <hr>
        <table class="table" style="font-size: 12px; width: 100%;" id="ICDetails">
        </table>
    </x-slot:form_fields>
</x-invCount_modal>

@endsection

@section('pagejs')

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

<script type="text/javascript" src="{{ asset('assets/js/vendor/virtual-select.min.js') }}"></script>

<script src="{{ asset('assets/js/inventory-maintenance/invCount.js') }}"></script>

@endsection