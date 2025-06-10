var MainTH, MainTH2, selectedMain;
var fileCtrTotal = 0;
var insertion = 0;
var jsonArr = [];

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
    await datatables.loadInvWarehouseData();
    // await datatables.loadInvMovementData();
    await initVS.liteDataVS();

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
    loadInvWarehouseData: async () => {
        const invData = await ajax('api/inv', 'GET', null, (response) => {
            jsonArr = response.data;
            datatables.initInvWarehouseDatatable(response);
        }, (xhr, status, error) => {
            console.error('Error:', error);
        });
    },

    initInvWarehouseDatatable: (response) => {
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
            } else {
                MainTH = $('#invTable').DataTable({
                    data: response.data,
                    language: {
                        searchPlaceholder: "Search here..."
                    },
                    columns: [
                        { data: 'Warehouse',  title: 'Warehouse' },
                        {
                            data: null,
                            title: 'Stock Code',
                            render: function(data, type, row){
                                if (!data) return '';

                                return `<strong>${row.StockCode}</strong><br><small>${data.productdetails.Description}</small>`;
                            }
                        },
                        { data: 'productdetails.Brand',  title: 'Brand' },
                        { data: 'productdetails.ProductClass',  title: 'Prodcut Class' },
                        { data: 'conversion.result.inCS',  title: 'in CS',
                            render: function (data, type, row){
                                return (data != null)? data : "-";
                            }
                        },
                        { data: 'conversion.result.inIB',  title: 'in IB',
                            render: function (data, type, row){
                                return (data != null)? data : "-";
                            }
                        },
                        { data: 'conversion.result.inPC',  title: 'in PC',
                            render: function (data, type, row){
                                return (data != null)? data : "-";
                            }
                        },
                        { data: 'DateLastStockMove',  title: 'Last Stock Move',
                            render: function(data, type, row){
                                if (!data) return '';

                                return dayjs(data).fromNow();
                            }
                        },
                        { data: 'QtyOnHand',  title: 'Quantity' },
                    ],
                    columnDefs: [
                        { className: "text-start", targets: [ 1, 2 ] },
                        { className: "text-center", targets: [ 0, 3, 4, 5, 6, 7, 8 ] },
                        { className: "text-nowrap", targets: '_all' } // This targets all columns
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    "createdRow": function (row, data) {
                        $(row).attr('id', data.StockCode);
                        // $(row).css('cursor', 'pointer');
                    },

                    "pageLength": 15,
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
                        $('#dattableDiv').removeClass('opacity-0');

                        const tableDiv = $('.dt-layout-row').first();
                        tableDiv.after('<div style="background: linear-gradient(to right, #1b438f, #33336F ); color: #FFF; margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px">Inventory Warehouse Report</p></div>');
                    }

                });

            }

        }
    },

}

const initVS = {
    liteDataVS: async () => {
        // Initialize VirtualSelect for ship via
        VirtualSelect.init({
            ele: '#filterPOVS',                   // Attach to the element
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
    }
}
