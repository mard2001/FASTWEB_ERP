@extends('Layout.layout')

@section('html_title')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <title>Inventory Movements</title>
    <link href="https://cdn.materialdesignicons.com/6.5.95/css/materialdesignicons.min.css" rel="stylesheet">
@endsection

@section('title_header')
    <x-header title="Inventory Movements" />
@endsection

@section('filtering_options')
<div class="filteringOptionDiv">
    <div class="d-flex">
        <div class="mb-1 prodSKU_VS_Div" style="display: none; width: 350px;">
            <div for="prodSKU_VS" id="prodSKU_label" class="VSLabel">PRODUCT SKU</div>
            <div id="prodSKU_VS" class="VSSelect"></div>
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
        <div class="col-sm-12 col-md-3 ">
            <div class="containerStyle">
                <div class="d-flex mx-3 stockIn">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-package-variant-plus'>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Total Stock In</span>
                        <p class="contentValue" id="totalStockInVal">--- PCS.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-3 ">
            <div class="containerStyle">
                <div class="d-flex mx-3 stockOut">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-package-variant-minus'>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Sold Stocks</span>
                        <p class="contentValue" id="totalStockOutVal">--- PCS.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-3 ">
            <div class="containerStyle">
                <div class="d-flex mx-3 totalProfit">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-cash'>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Total Sales Profit</span>
                        <p class="contentValue" id="totalSalesProfVal">PHP ---</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-3 ">
            <div class="containerStyle">
                <div class="d-flex mx-3 availableStock">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-warehouse'>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Available Stocks</span>
                        <p class="contentValue" id="totalAvailStockVal">--- PCS.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-4 ">
            <div class="containerStyle">
                <div class="d-flex mx-3 stockDetails">
                    <div class="iconDiv align-middle">
                        <span class='mdi mdi-package-variant'>
                    </div>
                    <div class="contentDiv">
                        <span class="contentTitle">Product Details</span>
                            <table class="table contentProdDets">
                                <tbody>
                                    <tr>
                                        <th scope="row">StockCode</th>
                                        <td id="stockCodeVal">---</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Description</th>
                                        <td id="descriptionVal">---</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Warehouse</th>
                                        <td id="warehouseVal">---</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Unit Price</th>
                                        <td id="unitPriceVal">---</td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-8 ">
            <div class="containerStyle" id="chartCanvasMainDiv" style="height: 95%;">
                <div id="chartloadingScreen" class="w-100 h-100 d-flex justify-content-center align-items-center loadingScreen">
                    <span class="loader" ></span>
                </div>
                <span class="contentTitle mx-2 canvasTitle" style="display: none">Product Flow Over the Past Year</span>
                <div class="canvasDiv" style="display: none">
                    <canvas id="myChart" style="display: none"></canvas>
                </div>
            </div>
        </div>
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

    <x-contentButtonDiv downloadFunc="true"></x-contentButtonDiv>

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
@endsection

@section('pagejs')

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

<script type="text/javascript" src="{{ asset('assets/js/vendor/virtual-select.min.js') }}"></script>

<!-- Day.js core -->
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

<!-- Plugin: relativeTime -->
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="{{ asset('assets/js/inventory/invMove.js') }}"></script>

@endsection
