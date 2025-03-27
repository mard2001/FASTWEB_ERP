@extends('Layout.layout')

@section('html_title')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<title>Master List Maintenance</title>
@endsection

@section('title_header')
<x-header title="Master List Maintenance" />
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

<x-table>
    <x-slot:td>
        <td class="col">PeriodYear</td>
        <td class="col">PeriodMonth</td>
        <td class="col">BusinessUnit</td>
        <td class="col">PAType</td>
        <td class="col">CustomerClass</td>
        <td class="col">StockCode</td>
        <td class="col">DropSize</td>
        <td class="col">Points</td>
        <td class="col">Amount</td>
        <td class="col">BonusPoint</td>
        <td class="col">MHCount</td>
        <td class="col">UpdatedBy</td>
        <td class="col">DateUpdated</td>
    </x-slot:td>
</x-table>


@endsection

@section('modal')
<x-form_modal>
    <x-slot:form_fields>
        <div class="row h-100 fs15">
            <div class="col mt-1 flex-wrap">

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="PeriodYear">Period Year</label>
                            <input disabled type="number" id="PeriodYear" name="PeriodYear" class="form-control bg-white"
                                required placeholder="Period Year">
                        </div>

                        <div class="col">
                            <label for="PeriodMonth">Period Month</label>
                            <input disabled type="number" id="PeriodMonth" name="PeriodMonth" class="form-control bg-white"
                                required placeholder="Period Month">
                        </div>
                    </div>
                </div>



                <div class="col mt-2">
                    <label for="BusinessUnit">Business Unit</label>
                    <input disabled type="text" id="BusinessUnit" name="BusinessUnit" class="form-control bg-white"
                        required placeholder="Business Unit">
                </div>

                <div class="col mt-2">
                    <label for="PAType">PA Type</label>
                    <input disabled type="text" id="PAType" name="PAType" class="form-control bg-white"
                        required placeholder="PA Type">
                </div>

                <div class="col mt-2">
                    <label for="CustomerClass">Customer Class</label>
                    <input disabled type="number" id="CustomerClass" name="CustomerClass" class="form-control bg-white"
                        required placeholder="Customer Class">
                </div>
            </div>

            <div class="col mt-1">

                <div class="col mt-2">
                    <label for="StockCode">Stock Code</label>
                    <input disabled type="number" id="StockCode" name="StockCode" class="form-control bg-white"
                        required placeholder="Stock Code">
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="DropSize">Drop Size</label>
                            <input disabled type="number" id="DropSize" name="DropSize" class="form-control bg-white"
                                required placeholder="Drop Size">
                        </div>

                        <div class="col">
                            <label for="Points">Points</label>
                            <input disabled type="number" id="Points" name="Points" class="form-control bg-white"
                                required placeholder="Points">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="BonusPoint">Bonus Point</label>
                            <input disabled type="number" id="BonusPoint" name="BonusPoint" class="form-control bg-white"
                                required placeholder="Bonus Point">
                        </div>

                        <div class="col">
                            <label for="MHCount">MHCount</label>
                            <input disabled type="number" id="MHCount" name="MHCount" class="form-control bg-white"
                                required placeholder="MHCount">
                        </div>

                    </div>
                </div>

                <div class="col mt-2">
                    <label for="Amount">Amount</label>
                    <input disabled type="number" id="Amount" name="Amount" class="form-control bg-white"
                        required placeholder="Amount">
                </div>



            </div>
        </div>
    </x-slot:form_fields>
</x-form_modal>
@endsection

@section('pagejs')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="{{ asset('assets/js/maintenance_uploader/pamasterslist.js') }}"></script>


@endsection