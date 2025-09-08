var MainTH, MainTH2, selectedMain;
var fileCtrTotal = 0;
var insertion = 0;
var jsonArr = [];
var stockInOutResArr = [];
var chartDataArr = [];
var date = ['','Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];
var chartLabels = [];
var chartStockInData = [];
var chartStockOutData = [];
var prodSKUData = [];
var prodFlowChart;

$(document).ready(async function () {

    dayjs.extend(dayjs_plugin_relativeTime);
    const user = localStorage.getItem('user');
    const userObject = JSON.parse(user);
    await datatables.loadInvMovementData(); // 12519920 // 12503732
    await initVS.liteDataVS();
    // await miniDashboard.loadMovements('12519920','M2'); // 12519920 // 12503732
    // await miniDashboard.generateSalesmanPieChart();
    initVS.fetchProductFilterData();

    $("#prodSKU_VS").on("change", async function () {
        if (this.value) {
            console.log('asdas')
            initVS.fetchWarehouseFilterData(this.value);
        } else{
            initVS.warehouseFilterVS([]);
        }
    });

    $("#prodSKU_VS").on("reset", function () {
        initVS.warehouseFilterVS([]);
    });

    $("#warehouse_VS").on("change", function () {
        if (this.value) {
            var productCode = $('#prodSKU_VS').val();
            var warehouseCode = this.value;
            if(productCode){
                this.close();
                MainTH.clear().draw();
                Swal.fire({
                    text: "Please wait... Refreshing Data...",
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                miniDashboard.isLoadingData();
                datatables.loadInvMovementData(productCode,warehouseCode);
                miniDashboard.loadMovements(productCode,warehouseCode);
            }
        }
    });

});

async function ajax(endpoint, method, data, successCallback = () => { }, errorCallback = () => { }) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: globalApi + endpoint,
            type: method,
            Accept: 'application/json',
            contentType: 'application/json',
            data: data,

            success: function (response) {
                successCallback(response);  // Trigger the success callback
                resolve(response);  // Resolve the promise with the response
            },
            error: function (xhr, status, error) {
                errorCallback(xhr, status, error);  // Trigger the error callback
                reject(error);  // Reject the promise with the error
            }
        });
    });
}

const datatables = {
    loadInvMovementData: async (stockCode,warehouse) => {
        await ajax('api/inv/product-movement/'+ stockCode + '/' + warehouse, 'GET', null, (response) => {
            jsonArr = response.data;
            datatables.initInvMovementDatatable(response);
            miniDashboard.updateValues(response.totalStockIn, response.totalStockOut, response.totalStockAvail, response.stockCode, response.description, response.warehouse, response.ttlPrice, response.unitPrice)

        }, (xhr, status, error) => {
            console.error('Error:', error);
        });
    },

    initInvMovementDatatable: (response) => {
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
            } else {
                MainTH = $('#invMovementTable').DataTable({
                    data: response.data,
                    language: {
                        searchPlaceholder: "Search here..."
                    },
                    columns: [
                        { data: 'EntryDate',  title: 'Date',
                            render: function (data, type, row) {
                                if (!data) return '';

                                const dateObj = new Date(data.split(' ')[0]);

                                if (type === 'display' || type === 'filter') {
                                    return dateObj.toLocaleDateString('en-GB', {
                                        day: '2-digit',
                                        month: 'short',
                                        year: 'numeric'
                                    });
                                }

                                return dateObj.toISOString();
                            }
                        },
                        { data: 'MovementType',  title: 'Type',
                            render: function (data, type, row) {
                                var result;
                                if(data == "I" ){
                                    if(row.TrnType == "T"){
                                        if(row.NewWarehouse == " "){
                                            result = "<span class='statusBadge1 align-middle' style='width:38.3167px;'><span class='mdi mdi-package-variant-plus'> IN </span></span>";
                                        } else{
                                            result = "<span class='statusBadge2 align-middle'><span class='mdi mdi-package-variant-minus'> OUT</span></span>";
                                        }
                                    } else if(row.TrnType == "A"){
                                        result = "<span class='statusBadge4 align-middle'><span class='mdi mdi-package-check'> ADJ</span></span>";
                                    } else{
                                        result = "<span class='statusBadge1 align-middle' style='width:38.3167px;'><span class='mdi mdi-package-variant-plus'> IN </span></span>";
                                    }
                                } else{
                                    result = "<span class='statusBadge2 align-middle'><span class='mdi mdi-package-variant-minus'> OUT</span></span>";
                                }
                                return result;
                            }
                        },
                        { data: 'TrnQty',  title: 'Transaction Quantity',
                            render: function (data, type, row) {
                                if (!data || data.trim() === "") return '<span style="color:var(--muted-color, #808080)">---</span>';
                                
                                const qty = Math.floor(data);
                                
                                var result;
                                var sign = '';
                                var color = '';
                                
                                if(row.MovementType == "I"){
                                    if(row.TrnType == "T" && row.NewWarehouse != " "){
                                        sign = '-';
                                        color = 'var(--danger-color, #df3639)';
                                    } else if(row.TrnType == "A"){
                                        sign = '';
                                        color = 'var(--info-color, #076aff)';
                                    } else{
                                        sign = '+';
                                        color = 'var(--success-color, #22bb33)';
                                    }
                                } else {
                                    sign = '-';
                                    color = 'var(--danger-color, #df3639)';
                                }
                                
                                const displayText = `${sign}${qty.toLocaleString('en-US')} pcs`;
                                
                                result = `<span style="color:${color}">${displayText}</span>`;
                                
                                return result;
                            }
                        },
                        { data: 'previousBalance',  title: 'Previous Balance',
                            render: function (data, type, row) {
                                if (!row.previousBal) return '<span style="color:var(--muted-color, #808080)">---</span>';
                                
                                const cs = row.previousBal.inCS || 0;
                                const ib = row.previousBal.inIB || 0;
                                const pc = row.previousBal.inPC || 0;
                                
                                return `<span class="balance-display">${cs} CS / ${ib > 0 ? ib + ' IB' : '0'} / ${pc} PC</span>`;
                            }
                        },
                        { data: 'newBalance',  title: 'New Balance',
                            render: function (data, type, row) {
                                if (!row.runningBal) return '<span style="color:var(--muted-color, #808080)">---</span>';
                                
                                const cs = row.runningBal.inCS || 0;
                                const ib = row.runningBal.inIB || 0;
                                const pc = row.runningBal.inPC || 0;
                                
                                return `<span class="balance-display font-weight-bold">${cs} CS / ${ib > 0 ? ib + ' IB' : '0'} / ${pc} PC</span>`;
                            }
                        },
                        { data: 'CustomerPoNumber',  title: 'PO Number',
                            render: function (data, type, row){
                                // Only show PO Number for non-sales order transactions
                                if (row.MovementType == "S" && row.SalesOrder && row.SalesOrder.trim() !== "") {
                                    return '<span style="font-size:10px; color:var(--muted-color, #808080)">---</span>';
                                }
                                return (data && data.trim() !== "") ? data : '<span style="font-size:10px; color:var(--muted-color, #808080)">---</span>';
                            }
                        },
                        { data: 'Reference',  title: 'Reference',
                            render: function (data, type, row){
                                return (data && data.trim() !== "" && row.MovementType == "I") ? data : '<span style="font-size:10px; color:var(--muted-color, #808080);">---</span>';
                            }
                        },
                        { data: 'SalesOrder',  title: 'SO Number',
                            render: function (data, type, row){
                                return (data && data.trim() !== "" && row.MovementType == "S") ? data : '<span style="font-size:10px; color:var(--muted-color, #808080)">---</span>';
                            }
                        },
                        { data: 'Customer',  title: 'Customer',
                            render: function (data, type, row){
                                return (data && data.trim() !== "" && row.MovementType == "S") ? data : '<span style="font-size:10px; color:var(--muted-color, #808080)">---</span>';
                            }
                        },
                        { data: 'salesmandetails',  title: 'Salesperson',
                            render: function (data, type, row){
                                return (data != null && row.MovementType == "S") ? data.Name : '<span style="font-size:10px; color:var(--muted-color, #808080)">---</span>';
                            }
                        },

                    ],
                    columnDefs: [
                        { className: "text-start", targets: [ 0, 5, 6, 7, 8, 9 ] }, // Date, PO Number, Reference, SO Number, Customer, Salesperson
                        { className: "text-center", targets: [ 1, 2, 3, 4 ] }, // Type, Transaction Qty, Previous Balance, New Balance
                        { className: "text-nowrap", targets: '_all' }, // This targets all columns
                        { width: "120px", targets: [ 2 ] }, // Transaction Quantity
                        { width: "140px", targets: [ 3, 4 ] }, // Previous Balance, New Balance
                        { responsivePriority: 1, targets: [ 0, 1, 2 ] }, // Date, Type, Transaction Qty - always visible
                        { responsivePriority: 2, targets: [ 4, 5 ] }, // New Balance, PO Number
                        { responsivePriority: 3, targets: [ 3, 6, 7 ] }, // Previous Balance, Reference, SO Number
                        { responsivePriority: 4, targets: [ 8, 9 ] } // Customer, Salesperson
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    "createdRow": function (row, data) {
                        $(row).attr('id', data.StockCode);
                    },

                    "pageLength": 5,
                    "lengthChange": false,
                    order: [],
                    initComplete: function () {
                        $(this.api().table().container()).find('#dt-search-0').addClass('p-1 mx-0 dtsearchInput nofocus');
                        $(this.api().table().container()).find('.dt-search label').addClass('py-1 px-3 mx-0 dtsearchLabel').html('<span class="mdi mdi-magnify"></span>');
                        $(this.api().table().container()).find('.dt-layout-row').first().find('.dt-layout-cell').each(function() { this.style.setProperty('height', '38px', 'important'); });
                        $(this.api().table().container()).find('.dt-layout-table').removeClass('px-4');
                        $(this.api().table().container()).find('.dt-scroll-body').addClass('rmvBorder');
                        $(this.api().table().container()).find('.dt-layout-table').addClass('btmdtborder');

                        const dtlayoutTE = $('.dt-layout-cell.dt-end').first();
                        dtlayoutTE.addClass('d-flex justify-content-end');
                        dtlayoutTE.prepend('<div id="filterPOVS" name="filter" style="width: 150px" class="bg-white p-0 mx-1">Filter</div>');
                        $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                        $('.loadingScreen').remove();
                        // $('#chartloadingScreen').remove();
                        $('#myChart, .canvasDiv, .canvasTitle').show();
                        $('#dattableDiv').removeClass('opacity-0');

                        const tableDiv = $('.dt-layout-row').first();
                        tableDiv.after('<div style="background: linear-gradient(to right, var(--primary-color, #1b438f), var(--secondary-color, #33336F) ); color: var(--text-color-light, #FFF); margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px; color: white;">Inventory Movement</p></div>');
                    }

                });

            }
            if (Swal.isVisible()) {
                Swal.close();
            }
        }
    },

}

const initVS = {
    liteDataVS: async () => {
        VirtualSelect.init({
            ele: '#filterPOVS',
            options: [
                // { label: "", value: null },
                // { label: "Active", value: 1 },
                // { label: "Deleted", value: 0 },

            ],
            multiple: true,
            hideClearButton: true,
            search: false,
            maxWidth: '100%',
            additionalClasses: 'rounded',
            additionalDropboxClasses: 'rounded',
            additionalDropboxContainerClasses: 'rounded',
            additionalToggleButtonClasses: 'rounded customVS-height',
        });
    },
    fetchProductFilterData: () => {
        prodSKUData = [];

        ajax('api/inv/available/products', 'GET', null, (response) => {
            prodSKUData = response.response;

            var data = prodSKUData.map((item) => {
                return {
                  value: item.StockCode,
                  label: item.StockCode+" - "+item.prodname.Description,
                };
            });

            initVS.productFilterVS(data);
        }, (xhr, status, error) => {
            console.error('Error:', error);
        });
    },
    productFilterVS: (sourceData) => {
        VirtualSelect.init({
            ele: '#prodSKU_VS',
            multiple: false,
            search: true,
            maxWidth: "100%",
            options: sourceData
        });
        $('.prodSKU_VS_Div').show();
    },
    fetchWarehouseFilterData: (StockCode) => {
        ajax('api/inv/available/product-warehouse/'+ StockCode, 'GET', null, (response) => {
            var uniqueWarehouses = response.response;

            var data = uniqueWarehouses.map((item) => {
                return {
                  value: item.Warehouse,
                  label: item.Warehouse,
                };
            });
            initVS.warehouseFilterVS(data);
        }, (xhr, status, error) => {
            console.error('Error:', error);
        });
    },
    warehouseFilterVS: (sourceData) => {
        if (document.querySelector('#warehouse_VS')?.virtualSelect) {
            document.querySelector('#warehouse_VS').destroy();
        }

        VirtualSelect.init({
            ele: '#warehouse_VS',
            multiple: false,
            search: true,
            maxWidth: "100%",
            options: sourceData
        });
        document.querySelector('#warehouse_VS').disable(sourceData);
        $('.warhouse_VS_Div').show();

        setTimeout(() => {
            document.querySelector('#warehouse_VS').enable();
        }, 500);

    }
}

const miniDashboard = {
    isLoadingData: () => {
        // MINIDASHBOARD
        var loadingCont1 = `<div id="chartloadingScreen" class="w-100 h-100 d-flex justify-content-center align-items-center loadingScreen">
                                <span class="loader" ></span>
                            </div>`;

        $('.canvasTitle').hide();
        $('.canvasDiv').hide();
        $('#chartCanvasMainDiv').prepend(loadingCont1);

        $('#totalStockInVal').slideUp(200, function() {
            $(this).html("---" + " PCS.").slideDown(200);
        });
        $('#totalStockOutVal').slideUp(200, function() {
            $(this).html("---" + " PCS.").slideDown(200);
        });
        $('#totalSalesProfVal').slideUp(200, function() {
            $(this).html("PHP " + "---").slideDown(200);
        });
        $('#totalAvailStockVal').slideUp(200, function() {
            $(this).html("---" + " PCS.").slideDown(200);
        });
        $('#stockCodeVal').slideUp(200, function() {
            $(this).html("---").slideDown(200);
        });
        $('#descriptionVal').slideUp(200, function() {
            $(this).html("---").slideDown(200);
        });
        $('#warehouseVal').slideUp(200, function() {
            $(this).html("---").slideDown(200);
        });
        $('#unitPriceVal').slideUp(200, function() {
            $(this).html("---").slideDown(200);
        });
    },
    isFinishLoadingData: () => {
        $('.canvasTitle').show();
        $('.canvasDiv').show();
        $('#chartloadingScreen').remove();
    },
    loadMovements: async (stockCode,warehouse) => {
        await ajax('api/inv/product-movement-chart/'+ stockCode + '/' + warehouse, 'GET', null, (response) => {
            stockInOutResArr = response;
            miniDashboard.setChartData(stockInOutResArr);
        }, (xhr, status, error) => {
            console.error('Error:', error);
        });
    },
    updateValues: async (totalStockIn, totalStockOut, totalStockAvail, stockCode, description, warehouse, ttlPrice, unitPrice) => {
        $('#totalStockInVal').slideUp(200, function() {
            $(this).html(totalStockIn.toLocaleString('en-US') + " PCS.").slideDown(200);
        });
        $('#totalStockOutVal').slideUp(200, function() {
            $(this).html(totalStockOut.toLocaleString('en-US') + " PCS.").slideDown(200);
        });
        $('#totalSalesProfVal').slideUp(200, function() {
            $(this).html("PHP " + ttlPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2 })).slideDown(200);
        });
        $('#totalAvailStockVal').slideUp(200, function() {
            $(this).html(totalStockAvail.toLocaleString('en-US') + " PCS.").slideDown(200);
        });
        $('#stockCodeVal').slideUp(200, function() {
            $(this).html(stockCode).slideDown(200);
        });
        $('#descriptionVal').slideUp(200, function() {
            $(this).html(description).slideDown(200);
        });
        $('#warehouseVal').slideUp(200, function() {
            $(this).html(warehouse).slideDown(200);
        });
        $('#unitPriceVal').slideUp(200, function() {
            $(this).html("PHP " + unitPrice.toLocaleString('en-US')).slideDown(200);
        });
    },
    generateSalesmanPieChart: async () => {
        const ctx = document.getElementById('myChart');

        var newData = {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Stock In',
                    data: chartStockInData,
                    fill: false,
                    borderColor: '#22bb33',
                    backgroundColor: '#22bb33',
                    tension: 0.1
                },
                {
                    label: 'Sold Stocks',
                    data: chartStockOutData,
                    fill: false,
                    borderColor: '#BC2023',
                    backgroundColor: '#BC2023',
                    tension: 0.1
                }
            ]
        };

        if (prodFlowChart) {
            prodFlowChart.data = newData;
            prodFlowChart.update();
        } else {
            prodFlowChart = new Chart(ctx, {
                type: 'line',
                data: newData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Transaction Month'
                            }
                        }
                    }
                }
            });
        }
    },
    setChartData: (resData) =>{
        var summaryMap = {};
        chartLabels = [];
        chartStockInData = [];
        chartStockOutData = [];

        resData.forEach(item => {
            var monthKey = `${date[item.trnMonth]},${item.trnYear}`;

            if (!summaryMap[monthKey]) {
                summaryMap[monthKey] = { month: monthKey, in: 0, out: 0 };
            }
            (item.MovementType == 'I') ? summaryMap[monthKey].in += parseInt(item.TotalTrn) : summaryMap[monthKey].out += parseInt(item.TotalTrn);
        });

        chartDataArr = Object.values(summaryMap);
        chartDataArr.forEach(item => {
            chartLabels.push(item.month);
            chartStockInData.push(item.in);
            chartStockOutData.push(item.out);
        });

        miniDashboard.generateSalesmanPieChart();
        miniDashboard.isFinishLoadingData();
    }
}


