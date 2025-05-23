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
var prodBarGraph = null;
var filteredStartDate;
var filteredEndDate;
var barLabels = [];
var barValues = [];
var barColors = [];
var topProdResArr = [];
var warehouseData = [];

const dataTableCustomBtn = `<div class="main-content buttons w-100 overflow-auto d-flex align-items-center px-2" style="font-size: 12px;">
                                <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="csvDLBtn">
                                    <div class="btnImg me-2" id="dlImg">
                                    </div>
                                    <span>Download Report</span>
                                </div>
                            </div>`;

$(document).ready(async function () {
    
    dayjs.extend(dayjs_plugin_relativeTime);
    const user = localStorage.getItem('user');
    const userObject = JSON.parse(user);
    // await datatables.loadInvMovementData(); // 12519920 // 12503732
    
    initVS.fetchWarehouseFilterData();
    datePicker();

    $("#warehouse_VS").on("change", function () {
        if (this.value) {
            var warehouseCode = this.value;
            if(filteredStartDate && filteredEndDate){
                this.close();
                if(MainTH){
                    MainTH.clear().draw();
                }
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
                datatables.loadWHMovementData(warehouseCode, filteredStartDate, filteredEndDate);
                // miniDashboard.loadTopProducts(warehouseCode, filteredStartDate, filteredEndDate);
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
    loadWHMovementData: async (warehouse, start, end) => {
        await ajax('api/inv/warehouse-movement/'+ warehouse + '/' + start + '/' + end, 'GET', null, (response) => {  
            jsonArr = response.data;
            datatables.initWHMovementDatatable(response);
            initVS.liteDataVS();
            miniDashboard.updateValues(response.totalStockIn, response.totalStockOut, response.totalStockAvail)
            
        }, (xhr, status, error) => { 
            console.error('Error:', error);
        });
    },

    initWHMovementDatatable: (response) => {
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
            } else {
                MainTH = $('#invMovementTable').DataTable({
                    data: response.data,
                    layout: {
                        topStart: function () {
                            return $(dataTableCustomBtn);
                        }
                    },
                    columns: [
                        { data: 'EntryDate',  title: 'Date',
                            render: function(data, type, row){
                                if (!data) return ''; 

                                return data.split(' ')[0];
                            }
                        },
                        { data: 'MovementType',  title: 'Type',
                            // render: function (data, type, row) {
                            //     return (data == "I") ? "<span class='statusBadge1 align-middle' style='width:47.4833px;'><span class='mdi mdi-package-variant-plus' > IN </span></span>" : "<span class='statusBadge2 align-middle'><span class='mdi mdi-package-variant-minus'> OUT</span></span>";
                            // }
                            render: function (data, type, row) {
                                var result;
                                if(data == "I" ){
                                    if(row.TrnType == "T"){
                                        if(row.NewWarehouse == " "){
                                            result = "<span class='statusBadge1 align-middle' style='width:47.4833px;'><span class='mdi mdi-package-variant-plus'> IN </span></span>";
                                        } else{
                                            result = "<span class='statusBadge2 align-middle'><span class='mdi mdi-package-variant-minus'> OUT</span></span>";
                                        }
                                    } else if(row.TrnType == "A"){
                                        result = "<span class='statusBadge4 align-middle'><span class='mdi mdi-package-check'> ADJ</span></span>";
                                    } else{
                                        result = "<span class='statusBadge1 align-middle' style='width:47.4833px;'><span class='mdi mdi-package-variant-plus'> IN </span></span>";
                                    }
                                } else{
                                    result = "<span class='statusBadge2 align-middle'><span class='mdi mdi-package-variant-minus'> OUT</span></span>";
                                }
                                return result;
                            }
                        },
                        { data: 'TrnQty',  title: 'Transac Qty',
                            // render: function (data, type, row){
                            //     return (data.trim() != "" && row.MovementType == "S")? `<span style="color:#df3639">-${Math.floor(data)} pcs.</span>` : `<span style="color:#22bb33">+${Math.floor(data)} pcs.</span>`;
                            // }
                            render: function (data, type, row) {
                                var result;
                                if(data.trim() != "" && row.MovementType != "S"){
                                    if(row.TrnType == "T"){
                                        if(row.NewWarehouse == " "){
                                            result = `<span style="color:#22bb33">+${Math.floor(data)} pcs.</span>`;
                                        } else{
                                            result = `<span style="color:#df3639">-${Math.floor(data)} pcs.</span>`;
                                        }
                                    } else if(row.TrnType == "A"){
                                        result = `<span style="color:#076aff">${Math.floor(data)} pcs.</span>`;
                                    } else{
                                        result = `<span style="color:#22bb33">+${Math.floor(data)} pcs.</span>`;
                                    }
                                } else{
                                    result = `<span style="color:#df3639">-${Math.floor(data)} pcs.</span>`;
                                }
                                return result;
                            }
                        },
                        { data: 'StockCode',  title: 'StockCode'},
                        { data: 'productdetails.Description',  title: 'Description'},
                        { data: 'Reference',  title: 'Reference',
                            render: function (data, type, row){
                                // return (data.trim() !== "" && row.MovementType == "I")? data : "<span style='font-size:10px;color:#808080;'>n/a</span>";
                                return (data.trim() !== "" && row.MovementType == "I")? data : '<span style="font-size:10px; color:#808080;">---</span>';
                            }
                        },
                        { data: 'SalesOrder',  title: 'SO Number',
                            render: function (data, type, row){
                                return (data.trim() != "" && row.MovementType == "S")? data : '<span style="font-size:10px; color:#808080">---</span>';
                            }
                        },
                        { data: 'CustomerPoNumber',  title: 'PO Number',
                            render: function (data, type, row){
                                return (data.trim() != "" && row.MovementType == "S")? data : '<span style="font-size:10px; color:#808080">---</span>';
                            }
                        },
                        { data: 'Customer',  title: 'Customer',
                            render: function (data, type, row){
                                return (data.trim() != "" && row.MovementType == "S")? data : '<span style="font-size:10px; color:#808080">---</span>';
                            }
                        },
                        { data: 'salesmandetails',  title: 'Salesperson',
                            render: function (data, type, row){
                                return (data != null && row.MovementType == "S")? data.Name : '<span style="font-size:10px; color:#808080">---</span>';
                            }
                        },
                        
                    ],
                    columnDefs: [
                        // { className: "text-start", targets: [ 0, 6, 7, 8, 9, 10  ] },
                        // { className: "text-center", targets: [ 1, 2, 3, 4, 5 ] },
                        // { className: "text-end", targets: [ 4 ] },
                        { className: "text-nowrap", targets: '_all' } // This targets all columns
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    "createdRow": function (row, data) {
                        $(row).attr('id', data.StockCode);
                    },

                    "pageLength": 10,
                    "lengthChange": false,
                    order: [],
                    initComplete: function () {
                        $(this.api().table().container()).find('#dt-search-0').addClass('p-1 mx-0 dtsearchInput nofocus');
                        $(this.api().table().container()).find('.dt-search label').addClass('py-1 px-3 mx-0 dtsearchLabel');
                        $(this.api().table().container()).find('.dt-layout-row').addClass('px-4');
                        $(this.api().table().container()).find('.dt-layout-table').removeClass('px-4');
                        $(this.api().table().container()).find('.dt-scroll-body').addClass('rmvBorder');
                        $(this.api().table().container()).find('.dt-layout-table').addClass('btmdtborder');

                        const dtlayoutTE = $('.dt-layout-cell.dt-end').first();
                        dtlayoutTE.addClass('d-flex justify-content-end');
                        dtlayoutTE.prepend('<div id="filterPOVS" name="filter" style="width: 200px" class="form-control bg-white p-0 mx-1">Filter</div>');
                        $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                        $('.loadingScreen').remove();
                        // $('#chartloadingScreen').remove();
                        $('#lineChart, .canvasDiv, .canvasTitle').show();
                        $('#dattableDiv').removeClass('opacity-0');
                        // $('.dt-layout-table').addClass('mt-4');

                        
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
            additionalToggleButtonClasses: 'rounded',
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
        ajax('api/inv/available/warehouse', 'GET', null, (response) => { 
            var uniqueWarehouses = response.response;

            var data = uniqueWarehouses.map((item) => {
                return {
                  value: item.Warehouse, 
                  label: item.Warehouse, 
                };
            });
            warehouseData = data;
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
        $('#inboundVal').slideUp(200, function() {
            $(this).html("---").slideDown(200);
        });
        $('#outboundVal').slideUp(200, function() {
            $(this).html("---").slideDown(200);
        });
        $('#availSkuVal').slideUp(200, function() {
            $(this).html("---").slideDown(200);
        });
    },
    isFinishLoadingData: () => {
        $('.canvasTitle').show();
        $('.canvasDiv').show();         
        $('#chartloadingScreen').remove();
    },
    loadMovements: async (warehouse, start, end) => {
        await ajax('api/inv/warehouse-movement-chart/'+ warehouse + '/' + start + '/' + end, 'GET', null, (response) => {  
            stockInOutResArr = response;
            miniDashboard.setLineChartData(stockInOutResArr);
        }, (xhr, status, error) => { 
            console.error('Error:', error);
        });
    },
    loadTopProducts: async (warehouse, start, end) => {
        await ajax('api/inv/warehouse-top-products/'+ warehouse + '/' + start + '/' + end, 'GET', null, (response) => {  
            topProdResArr = response.response;
            miniDashboard.setBarChartData(topProdResArr);
        }, (xhr, status, error) => { 
            console.error('Error:', error);
        });
    },
    updateValues: async (totalStockIn, totalStockOut, totalStockAvail) => {
        $('#inboundVal').slideUp(200, function() {
            $(this).html(totalStockIn.toLocaleString('en-US')).slideDown(200);
        });
        $('#outboundVal').slideUp(200, function() {
            $(this).html(totalStockOut.toLocaleString('en-US')).slideDown(200);
        });
        $('#availSkuVal').slideUp(200, function() {
            $(this).html(totalStockAvail.toLocaleString('en-US')).slideDown(200);
        });
    },
    generateLineChart: async () => {
        const ctx = document.getElementById('lineChart');
    
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
    setLineChartData: (resData) =>{
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

        miniDashboard.generateLineChart();
        // miniDashboard.isFinishLoadingData();
    },
    generateBarGraph: async () => {
        const ctx2 = document.getElementById('barGraph');
    
        var newData = {
            labels: barLabels,
            datasets: [
                {
                    label: 'StockCodes',
                    data: barValues,
                    borderColor: barColors,
                    backgroundColor: barColors,
                    borderWidth: 1
                },
            ]
        };
    
        if (prodBarGraph) {
            prodBarGraph.data = newData;
            prodBarGraph.update(); 
        } else {
            prodBarGraph = new Chart(ctx2, {
                type: 'bar',
                data: newData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        },
                    }
                }
            });
        }
    },
    setBarChartData: (resData) =>{
        barLabels = [];
        barValues = [];
        barColors = [];
    
        resData.forEach(item => {
            var hex = "#" + Math.floor(Math.random() * 16777215).toString(16).padStart(6, '0');

            barLabels.push(item.StockCode);
            barValues.push(parseFloat(item.TotalMoved));
            barColors.push(hex);
        });

        miniDashboard.generateBarGraph();
        // miniDashboard.isFinishLoadingData();
    }
}

function datePicker(){
    var start = moment().subtract(29, 'days');
    filteredStartDate = start.format('YYYY-MM-DD');
    var end = moment();
    filteredEndDate = end.format('YYYY-MM-DD')

    function cb(start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        autoUpdateInput: false,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
        filteredStartDate = picker.startDate.format('YYYY-MM-DD');
        filteredEndDate = picker.endDate.format('YYYY-MM-DD');
        $('.salesOrderListDiv').hide();

        initVS.warehouseFilterVS(warehouseData);
    });

    $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    cb(start, end);
}
