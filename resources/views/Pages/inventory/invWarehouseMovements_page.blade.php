@extends('Layout.layout')

@section('html_title')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <title>Warehouse Movements</title>
    <link href="https://cdn.materialdesignicons.com/6.5.95/css/materialdesignicons.min.css" rel="stylesheet">
@endsection

@section('title_header')
    <x-header title="Warehouse Movements" />
@endsection

@section('filtering_options')
<div class="filteringOptionDiv">
    <div class="d-flex">
        <div class="mb-1 prodSKU_VS_Div" style="display: none; width: 350px;">
            <div for="prodSKU_VS" id="prodSKU_label" class="VSLabel">PRODUCT SKU</div>
            <div id="prodSKU_VS" class="VSSelect"></div>
        </div>
        <div class="mb-1 whMoverangeDiv">
            <label for="reportrange" class="form-label">DATE RANGE</label>
            <div id="reportrange" >
                <i class="fa fa-calendar"></i>&nbsp;
                <span></span> <i class="mdi mdi-chevron-down"></i>
            </div>
        </div>
        <div class="mb-1 mx-3 warhouse_VS_Div" style="display: none; width: 150px;">
            <div for="warehouse_VS" id="warehouse_label" class="VSLabel">WAREHOUSE</div>
            <div id="warehouse_VS" class="VSSelect"></div>
        </div>
    </div>
</div>
@endsection

@section('mini_dashboard_chart')
<div class="">
    <div class="row gx-2 mb-1">
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3 ">
            <div class="containerStyle">
                <div class="d-flex pb-2 mx-3 stockIn">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-package-variant-plus'>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Inbound Movements</span>
                        <p class="contentValue" id="inboundVal">---</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3 ">
            <div class="containerStyle">
                <div class="d-flex pb-2 mx-3 stockOut">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-package-variant-minus'>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Outbound Movements</span>
                        <p class="contentValue" id="outboundVal">---</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3 ">
            <div class="containerStyle">
                <div class="d-flex pb-2 mx-3 totalProfit">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-package-check'>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Total Avaiable SKUs</span>
                        <p class="contentValue" id="availSkuVal">---</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3 ">
            <div class="containerStyle">
                <div class="d-flex pb-2 mx-3 totalAdj">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-package-up'>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Total Ajustment Movements</span>
                        <p class="contentValue" id="availAdjVal">---</p>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="col-sm-12 col-md-6 ">
            <div class="containerStyle" id="chartCanvasMainDiv" style="height: 200px;">
                <div id="chartloadingScreen" class="w-100 h-100 d-flex justify-content-center align-items-center loadingScreen">
                    <span class="loader" ></span>
                </div>
                <span class="contentTitle mx-2 canvasTitle" style="display: none">Top Moving Products</span>
                <div class="canvasDiv" style="display: none">
                    <canvas id="barGraph" style="display: none"></canvas>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-6 ">
            <div class="containerStyle" id="chartCanvasMainDiv" style="height: 200px;">
                <div id="chartloadingScreen" class="w-100 h-100 d-flex justify-content-center align-items-center loadingScreen">
                    <span class="loader" ></span>
                </div>
                <span class="contentTitle mx-2 canvasTitle" style="display: none">Product Flow Over the Past Year With in the Warehouse</span>
                <div class="canvasDiv" style="display: none">
                    <canvas id="lineChart" style="display: none"></canvas>
                </div>
            </div>
        </div> --}}
    </div>
</div>
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

    #invMovementTable thead tr{
        white-space: nowrap;
    }
</style>

<x-table id="invMovementTable">
    <x-slot:td>
        <td class="col">Date</td>
        <td class="col">Type</td>
        <td class="col">Warehouse</td>
        <td class="col">StockCode</td>
        <td class="col">Reference</td>
        <td class="col">SalesOrder</td>
        <td class="col">CustomerPoNumber</td>
        <td class="col">Customer</td>
        <td class="col">SalesPerson</td>
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

<x-rr_modal>
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
</x-rr_modal>

@endsection

@section('pagejs')

{{-- Date Picker --}}
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

{{-- AJAX --}}
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

<script type="text/javascript" src="{{ asset('assets/js/vendor/virtual-select.min.js') }}"></script>

<!-- Day.js core -->
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

<!-- Plugin: relativeTime -->
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="{{ asset('assets/js/inventory/invWarehouseMove.js') }}"></script>

@endsection