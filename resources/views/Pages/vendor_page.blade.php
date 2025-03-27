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
        <td class="col">Type</td>
        <td class="col">PeriodYear</td>
        <td class="col">PeriodMonth</td>
        <td class="col">StockCode</td>
        <td class="col">Description</td>
        <td class="col">OutletClassCode</td>
        <td class="col">Target</td>
        <td class="col">PAType</td>
        <td class="col">NewProduct</td>
        <td class="col">BusinessUnit</td>
        <td class="col">DropSize</td>
        <td class="col">Points</td>
        <td class="col">Amount</td>
        <td class="col">BonusPoint</td>
        <td class="col">MHCount</td>
        <td class="col">Teir</td>
        <td class="col">Activity_Type</td>
        <td class="col">Start_date</td>
        <td class="col">End_Date</td>
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
                    <div class="row">
                        <div class="col">
                            <label for="Type">Type</label>
                            <input disabled type="text" id="Type" name="Type" class="form-control bg-white"
                                required placeholder="Type">
                        </div>

                        <div class="col">
                            <label for="PeriodMonth">Outlet Class Code</label>
                            <input disabled type="text" id="OutletClassCode" name="OutletClassCode" class="form-control bg-white"
                                required placeholder="Outlet Class Code">
                        </div>
                    </div>
                </div>


                <div class="col mt-2">
                    <label for="Description">Description</label>
                    <input disabled type="text" id="Description" name="Description" class="form-control bg-white"
                        required placeholder="Description">
                </div>

                <div class="col mt-2">
                    <label for="Target">Target</label>
                    <input disabled type="number" id="Target" name="Target" class="form-control bg-white"
                        required placeholder="Target">
                </div>

                <div class="col mt-2">
                    <label for="PAType">PA Type</label>
                    <input disabled type="text" id="PAType" name="PAType" class="form-control bg-white"
                        required placeholder="PA Type">
                </div>

                <div class="col mt-2">
                    <label for="NewProduct">New Product</label>
                    <input disabled type="text" id="NewProduct" name="NewProduct" class="form-control bg-white"
                        required placeholder="New Product">
                </div>

                <div class="col mt-2">
                    <label for="BusinessUnit">Business Unit</label>
                    <input disabled type="text" id="BusinessUnit" name="BusinessUnit" class="form-control bg-white"
                        required placeholder="Business Unit">
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

                <div class="col mt-2">
                    <label for="Teir">Teir</label>
                    <input disabled type="text" id="Teir" name="Teir" class="form-control bg-white"
                        required placeholder="Teir">
                </div>

                <div class="col mt-2">
                    <label for="Teir">Activity Type</label>
                    <input disabled type="text" id="Activity_Type" name="Activity_Type" class="form-control bg-white"
                        required placeholder="Activity Type">
                </div>

                <div class="col mt-2">
                    <label for="promoPeriod">Promo Period</label>
                    <input disabled type="text" id="promoPeriod" name="promoPeriod" class="form-control bg-white"
                        required placeholder="Promo Period">
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
<script src="{{ asset('assets/js/maintenance_uploader/patarget.js') }}"></script>

<script>
    $('#promoPeriod').daterangepicker({
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        "alwaysShowCalendars": true,
        "startDate": moment(), // Replace with your dynamic value
        "endDate": moment(), // Replace with your dynamic value
        locale: {
            format: 'YYYY/MM/DD' // Set format to yyyy-mm-dd
        },
        "drops": "auto"
    });
</script>


@endsection