var MainTH, selectedMain;
var ItemsTH, selectedItems;
var ajaxMainData, ajaxItemsData;
var shippedToData, selectedShippedTo;
var vendordata, selectedVendor;
var productConFact;
var itemTmpSave = [];
var priceCodes;
var fileCtrTotal = 0;
var insertion = 0;
var jsonArr = [];
var detailsDatatable;
var isEditable = false;
var originalSelected = [];
var productConFact;

// FOR THE MEANTIME
selectedVendor = {
    Barangay: "Barangay 2",
    City: "Calamba",
    CompleteAddress: "456 Elm St",
    ContactNo: "09234567890",
    ContactPerson: "Jane Smith",
    Municipality: "Calamba City",
    PriceCode: "1 ",
    Province: "Laguna",
    Region: "Region IV-A",
    SupplierCode: "SUP002",
    SupplierName: "XYZ Furniture",
    SupplierType: "Furniture",
    TermsCode: "NET60",
    cID: "10",
    holdStatus: "1",
    lastUpdated: "2025-02-04T16:57:00.540000Z"
}
$("#VendorContactName").val(selectedVendor.ContactPerson);
$("#vendorAddress").val(selectedVendor.CompleteAddress);
$("#vendorPhone").val(selectedVendor.ContactNo);
$("#shippingTerms").val(selectedVendor.TermsCode);
// ===============================================

const dataTableCustomBtn = `<div class="main-content buttons w-100 overflow-auto d-flex align-items-center px-2" style="font-size: 12px;">
                                <div class="btn d-flex justify-content-around px-2 align-items-center me-1" id="addBtn">
                                    <div class="btnImg me-2" id="addImg">
                                    </div>
                                    <span>Add new</span>
                                </div>
                                <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="csvDLBtn">
                                    <div class="btnImg me-2" id="dlImg">
                                    </div>
                                    <span>Download Report</span>
                                </div>
                                <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="csvUploadShowBtn">
                                    <div class="btnImg me-2" id="ulImg">
                                    </div>
                                    <span>Upload Template</span>
                                </div>
                            </div>`;

$(document).ready(async function () {
    const user = localStorage.getItem('user');
    const userObject = JSON.parse(user);

    await datatables.loadSOData();
    await initVS.liteDataVS();
    await initVS.bigDataVS();
    await getProductPriceCodes();
    SOItemsModal.setValidator();
    // datatables.initDetailsDatatable([]);
    setDate();
    
    

    $("#soTable").on("click", "tbody tr", async function () {
        Swal.fire({
            text: "Please wait... Preparing data...",
            timerProgressBar: true,
            allowOutsideClick: false,
            allowEscapeKey: false,  
            allowEnterKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        const selectedSalesOrderID = $(this).attr('id');
        await ajax('api/sales-order/header/' + selectedSalesOrderID, 'GET', null, (response) => { 
            Swal.close();
            if (response.success == 1) {
                // console.log(response);
                selectedMain = response.data;
                SOModal.buttonsView();
                SOModal.viewMode(response.data);
                // originalSelected = JSON.parse(JSON.stringify(response.data));

            } else {
                Swal.fire({
                    title: "Opppps..",
                    text: response.message,
                    icon: "error"
                });

            }

        }, (xhr, status, error) => { // Error callback
            if (xhr.responseJSON && xhr.responseJSON.message) {
                Swal.fire({
                    title: "Opppps..",
                    text: xhr.responseJSON.message,
                    icon: "error"
                });

            }
        });
    });

    $('#csvDLBtn').on('click', function () {
        downloadToCSV(jsonArr);
    });

    $("#csvUploadShowBtn").on("click", async function () {
        $('#uploadCsv').modal('show');
    });

    $(document).on('click', '#addBtn', async function () {
        SOModal.enable(true);
        SOModal.clear();
        
        // $('#editXmlDataModal').modal('show');

        $('#deleteSOBtn').hide();
        $('#rePrintPage').hide();
        $('#saveSOBtn').show();
        $('#editSOBtn').hide();
        itemTmpSave = [];
        selectedMain = null;
        datatables.initSOItemsDatatable(null);
        $('#confirmSO').hide();
        $('#addItems').show();
        // ItemsTH.column(6).visible(true);
        SOModal.buttonsView();
        SOModal.show();
    });
    
    $('#availableSO').on('click', function () {
        SOStatus.ChangeSOStatus('in-warehouse', selectedMain.SalesOrder, userObject.name);
    });

    $('#unavailableSO').on('click', function () {
        SOStatus.ChangeSOStatus('open-back-order', selectedMain.SalesOrder, userObject.name);
    });

    $('#restockedSO').on('click', function () {
        SOStatus.ChangeSOStatus('release-back-order', selectedMain.SalesOrder, userObject.name);
    });

    $('#suspenseSO').on('click', function () {
        SOStatus.ChangeSOStatus('in-suspense', selectedMain.SalesOrder, userObject.name);
    });

    $('#invoiceSO').on('click', function () {
        SOStatus.ChangeSOStatus('to-invoice', selectedMain.SalesOrder, userObject.name);
    });

    $('#completeSO').on('click', function () {
        SOStatus.ChangeSOStatus('complete', selectedMain.SalesOrder,userObject.name);
    });


    $("#saveSOBtn").on("click", function () {
        if (SOModal.isValid()) {
            if (itemTmpSave.length < 1) {
                Swal.fire({
                title: "No items",
                text: "Please review your order. No items have been added for Sales Order.",
                icon: "error",
                });
            } else {
                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you want to add this data?",
                    icon: "question",
                    showDenyButton: true,
                    confirmButtonText: "Yes, Add",
                    denyButtonText: `Cancel`,
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        SOModal.SOSave();
                    }
                });
            } 
        } else {
            Swal.fire({
                title: "Missing Required Fields!",
                text: "Please fill in all fields. Some required fields are empty.",
                icon: "warning",
            });
        }
    });

    $("#addItems").on("click", function () {
        SOItemsModal.clear();
        SOItemsModal.enable(true);

        $(".UOMField").addClass("d-none");
        $("#itemSave").text("Save Item");
        SOItemsModal.show();

        $("#itemEdit").hide();
        $("#itemSave").show();
    });

    $("#itemSave").on("click", function () {
        if ( SOItemsModal.isValid() && $("#TotalPrice").val() && parseInt(parseMoney($("#TotalPrice").val())) > 0 ) {
            const getItem = SOItemsModal.getData();
            if ($(this).text().toLowerCase() == "update item") {
                SOItemsModal.itemTmpUpdate(getItem);
            } else {
                getItem.PRD_INDEX = itemTmpSave ? itemTmpSave.length + 1 : 1;
                SOItemsModal.itemTmpSave(getItem);
            }
            datatables.initSOItemsDatatable(itemTmpSave);
            calculateCost();
        } else {
            Swal.fire({
                title: "Missing Required Fields!",
                text: "Please fill in all fields. Some required fields are empty.",
                icon: "warning",
            });
        }
    });

    $("#itemCloseBtn").on("click", function () {
        let valid = false;
        const data = SOItemsModal.getData();
    
        // Check for empty values (excluding totalDiscount since it's always 0)
        for (const key in data) {
          if (data[key] === "" || data[key] === null || data[key] === undefined) {
            valid = true;  
          }
        }
    
        if (valid) {
            Swal.fire({
                title: "Are you sure?",
                text: "You want to close? Unsaved data will be erased.",
                icon: "question",
                showDenyButton: true,
                confirmButtonText: "Yes, Close",
                denyButtonText: `Cancel`,
            }).then(async (result) => {
                if (result.isConfirmed) {
                    SOItemsModal.hide();
                }
            });
        }
    });

    $(".fa-minus").on("click", function () {
        const quantityElement = $(this).closest(".input-group").find("input");
        let quantity = quantityElement.val();
    
        if (quantity && parseInt(quantity) > 0) {
          quantityElement.val(parseInt(quantity) - 1);
          autoCalculateTotalPrice();
        }
    });
    
    $(".fa-plus").on("click", function () {
        const quantity = $(this).closest(".input-group").find("input");
        quantity.val(quantity.val() ? parseInt(quantity.val()) + 1 : 1);
        autoCalculateTotalPrice();
    });

    $("#Quantity").on("input", function () {
        autoCalculateTotalPrice();
    });

    $("#CSQuantity, #IBQuantity, #PCQuantity").on("input", function () {
        autoCalculateTotalPrice();
    });

    $(document).on("click", ".itemDeleteIcon", async function () {
        const row = $(this).closest("tr");
        const skuCode = row.find("td:first"); // Get the first <td>
        SOItemsModal.itemTmpDelete(skuCode);
    });

    $(document).on("click", ".itemUpdateIcon", async function () {
        const row = $(this).closest("tr");
        const itemStockCode = row.find("td:first").text().trim();
        SOItemsModal.enable(true);
    
        $("#CSQuantity").val("");
        $("#IBQuantity").val("");
        $("#PCQuantity").val("");
    
        SOItemsModal.show();
    
        const select = document.querySelector("#StockCode");
    
        // Set value programmatically
        select.setValue(itemStockCode);
    
        // Manually trigger the `afterClose` event
        const event = new CustomEvent("afterClose");
        select.dispatchEvent(event);
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
    loadSOData: async () => {
        const SOHeaderData = await ajax('api/sales-order/header', 'GET', null, (response) => { // Success callback
            jsonArr = response.data;
            // console.log(response.data);
            datatables.initSODatatable(response);
        }, (xhr, status, error) => { // Error callback
            console.error('Error:', error);
        });
    },

    initSODatatable: (response) => {
        console.log(response.data);
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
            } else {
                MainTH = $('#soTable').DataTable({
                    data: response.data,
                    layout: {
                        topStart: function () {
                            return $(dataTableCustomBtn);
                        }
                    },
                    columns: [
                        { data: 'OrderDate',  title: 'Order Date',
                            render: function (data, type, row) {
                                return data.split(" ")[0];
                            },
                        },
                        { data: 'SalesOrder',  title: 'Sales Order' },
                        {
                            data: 'OrderStatus',
                            title: 'Status',
                            createdCell: function(td, cellData) {
                                let color = '';
                                let backgroundColor = '';
                                let text = '';
                        
                                switch (cellData) {
                                    case '1': color = '#f39c12'; backgroundColor = '#fde3a7'; text = 'Open Order'; break;
                                    case '2': color = '#f39c12'; backgroundColor = '#fde3a7'; text = 'Open Back Order'; break;
                                    case '3': color = '#f1c40f'; backgroundColor = '#fdf5c7'; text = 'Released Back Order'; break;
                                    case '4': color = '#3498db'; backgroundColor = '#d6eaf8'; text = 'In Warehouse'; break;
                                    case '8': color = '#9b59b6'; backgroundColor = '#e8daef'; text = 'To Invoice'; break;
                                    case 'F': color = '#1abc9c'; backgroundColor = '#d1f2eb'; text = 'Forward Order'; break;
                                    case 'S': color = '#e74c3c'; backgroundColor = '#fadbd8'; text = 'In Suspense'; break;
                                    case '9': color = '#28a745'; backgroundColor = '#d4edda'; text = 'Complete'; break;
                                    default: color = '#808080'; backgroundColor = '#f0f0f0'; text = 'Unknown';
                                }
                        
                                $(td).html(`<span class="statusbadge" style="color: ${color}; background-color: ${backgroundColor};" border: 1px solid ${color}">${text}</span>`);
                            }
                        },
                        { data: 'DocumentType',  title: 'Document Type' },
                        { data: 'Customer',  title: 'Customer ID' },
                        { data: 'CustomerName',  title: 'Customer Name' },
                        { data: 'CustomerPoNumber',  title: 'PO Number' },
                        { data: 'ReqShipDate',  title: 'Req. Ship Date',
                            render: function (data, type, row) {
                                return data.split(" ")[0];
                            },
                        },
                        { data: 'Branch',  title: 'Branch' },
                        { data: 'Warehouse',  title: 'Warehouse' },
                        { data: null,  title: 'Address',
                            render: function (data, type, row) {
                                return row.ShipAddress1 + ', ' + row.ShipAddress2 + ', ' + row.ShipAddress3;
                            }
                         },
                        { data: 'ShipToGpsLat',  title: 'Latitude' },
                        { data: 'ShipToGpsLong',  title: 'Longitude' },
                    ],
                    columnDefs: [
                        { className: "text-start", targets: [ 0, 1, 4, 5, 6, 7, 10] },
                        { className: "text-center", targets: [ 2, 3, 8, 9,  ] },
                        // { className: "text-end", targets: [ 4 ] },
                        { className: "text-nowrap", targets: '_all' },
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    "createdRow": function (row, data) {
                        $(row).attr('id', data.SalesOrder);
                    },

                    "pageLength": 15,
                    "lengthChange": false,

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
                        $('#dattableDiv').removeClass('opacity-0');
                    }
                });
            }
        }
    },

    initDetailsDatatable: (data) => {
        detailsDatatable = new DataTable('#SODetails', {
            data: data,
            layout: {
                // topStart: function () {
                //     return $(dataTableCustomBtn);
                // }
            },
            columns: [
                { data: 'MStockCode',  title: 'Stock Code' },
                { data: 'MStockDes',  title: 'Description' },
                { data: 'MPrice',  title: 'Price' },
                { data: 'MProductClass',  title: 'Product Class' },
                { data: 'MOrderQty',  title: 'Order Qty' },
                { data: 'MPriceCode',  title: 'Price Code' },
                { data: 'MStockQtyToShp',  title: 'Qty to Ship' },
                { data: 'MStockUnitMass',  title: 'Unit Mass' },
                { data: 'MStockUnitVol',  title: 'Unit Volume' },
            ],
            columnDefs: [
                { className: "text-start", targets: [ 0, 1 ] },
                { className: "text-center", targets: [ 3, 4, 5, 6, 7, 8 ] },
                { className: "text-end", targets: [ 2 ] },
                // { className: "text-nowrap", targets: [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ] },
            ],
            scrollCollapse: true,
            // scrollY: '100%',
            // scrollX: '100%',
            "createdRow": function (row, data) {
                $(row).attr('id', data.MStockCode);
            },

            "pageLength": 10,
            "lengthChange": false,

            initComplete: function () {
                $(this.api().table().container()).find('#dt-search-1').addClass('p-1 mx-0 dtDetailssearchInput nofocus');
                $(this.api().table().container()).find('.dt-search label').addClass('py-1 px-3 mx-0 dtDetailssearchLabel');
                $(this.api().table().container()).find('#ICDetails_info').css({'font-size':'11px'});
                $(this.api().table().container()).find('.paging_full_numbers').css({'font-size':'10px'});
            }

        });

        // Make cells editable on click
        $('#ICDetails tbody').on('click', 'td', function() {
            if(isEditable){
                let column = $(this).index();
                let row = $(this).closest('tr').index();
                let cell = detailsDatatable.cell(this);
                let value = cell.data();
                let displayedValue = cell.render('display');

                // Replace cell content with an input field
                if(column == 4 || column == 3 || column == 2){
                    console.log(displayedValue);
                    if(displayedValue != '-'){
                        $(this).html(`<input type="text" value="${value}" class="edit-cell text-center ${column} ${row}" style="width: 30px;height: 20px;" oninput="this.value = this.value.replace(/[^0-9]/g, '')">`);
                        // Focus on the input field
                        $(this).find('input').focus();
                    }
                }
            }
        });

        $('#ICDetails tbody').on('blur', '.edit-cell', function() {
            let newValue = parseInt($(this).val());
            let cell = detailsDatatable.cell($(this).closest('td'));

            cell.data(newValue).draw();
        });
    },

    loadItems: async (SONumber) => {

        const soItems = await ajax('api/orders/po-items/search-items/' + SONumber, 'GET', null, (response) => { // Success callback
            ajaxItemsData = response.data;
            datatables.initSOItemsDatatable(ajaxItemsData);

        }, (xhr, status, error) => { // Error callback
            console.error('Error:', error);
        });


    },

    initSOItemsDatatable: (datas) => {
        if (ItemsTH) {
            ItemsTH.clear().draw();
            datas && ItemsTH.rows.add(datas).draw();
        } else {
            ItemsTH = $('#itemTables').DataTable({
                data: datas,
                columns: [
                    { data: 'MStockCode' },
                    { data: 'MStockDes' },
                    { data: 'MOrderQty' },
                    { data: 'MOrderUom' },
                    {
                        data: 'MUnitCost',
                        render: function (data, type, row) {
                            return formatMoney(data);
                        }
                    },
                    {
                        data: 'MPrice',
                        render: function (data, type, row) {
                            return formatMoney(data);
                        }
                    },
                    {
                        data: null, 
                        render: function (data, type, row) {
                            return ` <div class="d-flex actIcon">
                                        <div class="w-50 d-flex justify-content-center itemUpdateIcon">
                                            <i class="fa-regular fa-pen-to-square fa-lg text-primary m-auto "></i>
                                        </div>
                                        <div class="w-50 d-flex justify-content-center itemDeleteIcon">
                                            <i class="fa-solid fa-trash fa-lg text-danger m-auto"></i>
                                        </div>
                                    </div>`;
                        },
                        orderable: false, // Prevent sorting on the checkbox column
                        searchable: false,  // Disable search on the checkbox column
                        createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
                            // Add class to the parent <td> element dynamically
                            $(cell).addClass('nhover');
                        }
                    },

                ],
                columnDefs: [
                    { className: "text-start", targets: [0, 1] },
                    { className: "text-center", targets: [2, 3] },
                    { className: "text-end", targets: [4, 5] },
                ],
                searching: true,
                scrollCollapse: true,
                responsive: true, // Enable responsive modeoWidth: true, // Enable auto-width calculation
                // scrollY: '100%',
                // scrollX: '100%',
                "createdRow": function (row, data) {
                    $(row).attr('id', data.id);
                },
                "lengthChange": false,  // Hides the per page dropdown
                "info": false,          // Hides the bottom text (like "Showing x to y of z entries")
                "paging": false,        // Hides the pagination controls (Next, Previous, etc.)
            });

        }

        $('#totalItemsLabel').text(datas ? datas.length : 0);
    },
}

const initVS = {
    liteDataVS: async () => {
        // Initialize VirtualSelect for ship via
        VirtualSelect.init({
            ele: '#shipVia',                   // Attach to the element
            options: [
                { label: "Road Delivery", value: "road_delivery" },
                { label: "Air Freight", value: "air_freight" }
            ],                                 // Provide options
            maxWidth: '100%',                  // Set maxWidth
            multiple: false,                   // Enable multiselect
            hideClearButton: true,             // Hide clear button
            disabledOptions: ['air_freight'],
            selectedValue: 'road_delivery',    // Preselect (must match `value`)
        });

        // Initialize VirtualSelect for Filter
        VirtualSelect.init({
            ele: '#filterPOVS',                   // Attach to the element
            options: [
                // { label: "", value: null },
                // { label: "", value: 1 },
                // { label: "", value: "2" },

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

        vendorModal.loadVendorVS();

        $("#vendorName").on("afterClose", function () {
            if (this.value) {
                var findVendor = vendordata.find((item) => item.cID == 10);
                const validPriceCode = priceCodes.some(
                    (item) => item.PRICECODE == findVendor.PriceCode.trim()
                );
              
                if (validPriceCode) {
                    $("#VendorContactName").val(findVendor.ContactPerson);
                    $("#vendorAddress").val(findVendor.CompleteAddress);
      
                    var mobileContact = (findVendor.ContactNo = /^9\d{9}$/.test(
                        findVendor.ContactNo
                    )
                    ? findVendor.ContactNo.replace(/^9/, "09")
                    : findVendor.ContactNo);
      
                selectedVendor = findVendor;
      
                $("#vendorPhone").val(mobileContact);
      
                if (this.value && $("#shippedToName").value) {
                    $("#addItems").prop("disabled", false);
                }
      
                $("#shippingTerms").val(findVendor.TermsCode);
            } else {
                selectedVendor = null;
                Swal.fire({
                  title: "Opppps..",
                  text: "The selected vendor has an invalid price code.",
                  icon: "warning",
                });
                $("#vendorName").trigger("reset");
              }
            } 
        });
      
        $("#vendorName").on("reset", function () {
            $("#VendorContactName").val("");
            $("#vendorAddress").val("");
            $("#vendorPhone").val("");
            selectedVendor = null;
        });

        //shippedToData
        await ajax('api/maintenance/v2/customer', 'GET', null, (response) => { // Success callback
            shippedToData = response.data;
            

            const newData = response.data.map(item => {
                // Create a new object with the existing properties and the new column
                return {
                    value: item.Customer, // Spread the existing properties
                    label: item.Name , // Copy the value from sourceKey to targetKey
                };
            });

            // Check if the VirtualSelect instance exists before destroying
            if (document.querySelector('#shippedToName')?.virtualSelect) {
                document.querySelector('#shippedToName').destroy();
            }

            VirtualSelect.init({
                ele: '#shippedToName',
                options: newData,
                markSearchResults: true,
                maxWidth: '100%',
                search: true,
                autofocus: true,
                hasOptionDescription: true,
                noSearchResultsText: `<div class="w-100 d-flex justify-content-around align-items-center mt-2">
                                    <div class="w-auto text-center">
                                         No result found. Add new?
                                    </div>
                                    <div class="w-auto">
                                        <button id="ShipperNoDataFoundBtn" type="button" class="btn btn-primary btn-sm">Add new</button>
                                    </div>
                                </div>`,

            });

            $('#shippedToName').on('afterClose', function () {
                if (this.value) {
                    var findCustomer = response.data.find(item => item.Customer == this.value);
                    selectedShippedTo = findCustomer;

                    $('#shippedToContactName').val(findCustomer.Contact.trim());
                    $('#shippedToAddress').val((findCustomer.SoldToAddr1	+ ", " + findCustomer.SoldToAddr2 + ", " + findCustomer.SoldToAddr3).trim());

                    var mobileContact = findCustomer.Telephone = /^9\d{9}$/.test(findCustomer.Telephone) ? findCustomer.Telephone.replace(/^9/, "09") : findCustomer.Telephone;

                    $('#shippedToPhone').val(mobileContact);

                    // if (this.value && document.querySelector('#vendorName').value) {
                    //     $('#addItems').prop("disabled", false);
                    // }

                }

            });

            $('#shippedToName').on('reset', function () {
                $('#shippedToContactName').val('');
                $('#shippedToAddress').val('');
                $('#shippedToPhone').val('');
            });

        }, (xhr, status, error) => { // Error callback
            console.error('Error:', error);
        });
    },

    bigDataVS: async () => {
        await ajax( "api/product", "GET", null,
          (response) => {
            const products = response.data;
    
            const newData = products.map((item) => {
              return {
                description: item.Description,
                value: item.StockCode, 
                label: item.StockCode, 
              };
            });
    
            if (document.querySelector("#StockCode")?.virtualSelect) {
              document.querySelector("#StockCode").destroy();
            }
    
            // Initialize VirtualSelect
            VirtualSelect.init({
                ele: "#StockCode", // Attach to the element
                options: newData, // Provide options
                maxWidth: "100%", // Set maxWidth
                autofocus: true,
                search: true,
                hasOptionDescription: true,
            });
    
            $("#StockCode").on("afterClose", async function () {
                if (this.value) {
                    const stockCode = this.value;
                    var findProduct = products.find(
                        (item) => item.StockCode == stockCode
                    );
                    $("#Decription").val(findProduct.Description);
      
                    let priceCode = selectedVendor.PriceCode.trim();
      
                    const getPriceBody = {
                        stockCode: stockCode,
                        priceCode: priceCode,
                    };
      
                    await ajax( "api/getProductPrice", "GET", getPriceBody, (response) => {
                      if (response.status_response == 1) {
                        var uomColumn = ["StockUom", "AlternateUom", "OtherUom"];
      
                        var uoms = uomColumn.map((item) => {
                            return {
                                value: findProduct[item], 
                                label: findProduct[item], 
                            };
                        });
      
                        uoms = uoms.filter(
                            (item, index, self) =>
                                index === self.findIndex((other) => other.value === item.value)
                        );
      
                        if (!$(".UOMField").hasClass("d-none")) {
                            $(".UOMField").addClass("d-none");
                            $("#itemModalFields").validate().resetForm();
                        }
      
                        uoms.forEach((item) => {
                            $(`#${item.value}Div`).removeClass("d-none");
                        });
      
                        productConFact = response.convertionFactor;

                        $("#PricePerUnit").val(response.response.UNITPRICE);
                        $("#itemSave").prop("disabled", false);
      
                        const isAlreadyExist = itemTmpSave.find(
                            (item) => item.MStockCode == stockCode
                        );
                        console.log(isAlreadyExist);
                        if (isAlreadyExist) {
                            selectedItem = isAlreadyExist;
                            SOItemsModal.itemEditMode(uoms, isAlreadyExist);
                        } else {
                            if ($("#itemSave").text().toLowerCase() == "update item") {
                                $("#itemSave").text("Save Item");
                            }
                        }
                      } else {
                            $("#PricePerUnit").val("");
                            $("#itemSave").prop("disabled", true);
      
                            Swal.fire({
                                title: "Opppps..",
                                text: "No price maintained for this product",
                                icon: "warning",
                            });
                        }
                    },
                    (xhr, status, error) => {
                        $("#PricePerUnit").val("");
                        $("#itemSave").prop("disabled", true);
      
                        Swal.fire({
                            title: "Opppps..",
                            text: "No price maintained for this product",
                            icon: "warning",
                        });
                    }
                  );
      
                  autoCalculateTotalPrice();
                }
            });
    
            $("#StockCode").on("reset", function () {
                $("#Decription").val("");
                $("#PricePerUnit").val("");
            });
          },
          (xhr, status, error) => {
            // Error callback
            console.error("Error:", error);
          }
        );
    },
}

const SOModal = {
    isValid: () => {
        return $("#modalFields").valid();
    },
    show: () => {
        $('#salesOrderMainModal').modal('show');
    },
    hide: () => {
        $('#salesOrderMainModal').modal('hide');
    },
    clear: () => {
        $('#modalFields input[type="text"]').val('');
        $('#modalFields input[type="number"]').val('');
        $('#modalFields textarea').val('');

        // $('#shippedToContactName').val('');
        // $('#shippedToAddress').val('');
        // $('#shippedToPhone').val('');
        // document.querySelector('#shippedToName').virtualSelect.setValue(null);
        // document.querySelector('#shipVia').virtualSelect.setValue(null);
    },
    enable: (enable) => {
        $('#modalFields input[type="text"]').prop('disabled', !enable);
        $('#modalFields input[type="number"]').prop('disabled', !enable);
        $('#modalFields input[type="date"]').prop('disabled', !enable);
        $('#modalFields textarea').prop('disabled', !enable);
    },
    viewMode: async (SOData) => {
        SOModal.fill(SOData);
        itemTmpSave = SOData.details;
        // datatables.initSOItemsDatatable(SOData.details);
        $('#rePrintPage').show();
        SOModal.show();
    },
    fill: async (SODetails) => {
        console.log(SODetails);
        $('#Branch').html(SODetails.Branch);
        $('#Warehouse').html(SODetails.Warehouse);
        $('#Customer').html(SODetails.Customer);
        $('#CustomerName').html(SODetails.CustomerName);
        $('#ShipAddress1').html(SODetails.ShipAddress1);
        $('#SalesOrder').html(SODetails.SalesOrder);
        $('#OrderStatus').html(SODetails.OrderStatus);
        $('#OrderDate').val(SODetails.OrderDate.substring(0, 10)); 
        $('#ReqShipDate').val(SODetails.ReqShipDate.substring(0, 10)); 

        // detailsDatatable.clear().rows.add(SODetails.details).draw();
        datatables.initSOItemsDatatable(SODetails.details);
    },
    SOSave: async () => {
        const user = localStorage.getItem('user');
        const userObject = JSON.parse(user);
        let SOData = SOModal.getData();
        SOData.Items = itemTmpSave.map((item, index) => ({
          ...item,  
          PRD_INDEX: index + 1, 
        }));
    
        SOData.LastOperator = userObject.name;
        SOData.CustomerInfo = selectedShippedTo;
        await ajax( "api/sales-order/header", "POST", JSON.stringify({ data: SOData }),
            (response) => {
                if (response.success) {
                    datatables.loadSOData();
                    SOModal.hide();
            
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                    });
                }
            },
            (xhr, status, error) => {
                // Error callback
                if (xhr.responseJSON && xhr.responseJSON.message) {
                Swal.fire({
                    title: "Opppps..",
                    text: xhr.responseJSON.message,
                    icon: "error",
                });
                }
            }
        );
    },
    getData: () => {
        var user = JSON.parse(localStorage.getItem("user"));
        var data = {
            // PODate: moment().format("YYYY-MM-DD"),
            OrderDate: $('#OrderDate').val(),
            ReqShipDate: $('#ReqShipDate').val(),
            SupplierCode: selectedVendor.SupplierCode.trim(),
            SupplierName: selectedVendor.SupplierName.trim(),
            productType: selectedVendor.SupplierType.trim(),
            FOB: $("#fob").val(),
            deliveryAddress: $("#shippedToAddress").val(),
            contactPerson: $("#shippedToContactName").val(),
            contactNumber: $("#shippedToPhone").val(),
            deliveryMethod: $("#shipVia").val(),
            totalDiscount: 0,
            totalTax: parseMoney($("#taxCost").text()),
            SpecialInstruction: $("#poComment").val(),
            EncoderID: user.user_id,
            orderPlacer: $("#requisitioner").val(),
            // orderPlacerEmail: user.email,
            subTotal: parseMoney($("#subTotal").text()),
            TermsCode: $("#shippingTerms").val(),
            totalCost: parseMoney($("#grandTotal").text()),
        };
    
        return data;
    },
    buttonsView: () => {
        if(!selectedMain){
            $('.statBtns').hide();
            $('#saveSOBtn').show();
            $('#editSOBtn').hide();
            $('#cancelEditSOBtn').hide();
            $('#deleteSOBtn').hide();
            $('#rePrintPage').hide();
            $('#statBtns').hide();
        } else{
            $('.statBtns').hide();
            $('#saveSOBtn').hide();
            $('#editSOBtn').hide();
            $('#cancelEditSOBtn').hide();
            $('#deleteSOBtn').hide();
            $('#rePrintPage').hide();
            $('#statBtns').hide();
            if(selectedMain.OrderStatus == "1"){
                $('#availableSO').show();
                $('#unavailableSO').show();
            } else if(selectedMain.OrderStatus == "2"){
                $('#restockedSO').show();
            } else if(selectedMain.OrderStatus == "4"){
                $('#suspenseSO').show();
                $('#invoiceSO').show();
            } else if(selectedMain.OrderStatus == "8"){
                $('#completeSO').show();
            }
        }
    }
}

const SOItemsModal = {
    setValidator: () => {
        $.validator.addMethod( "atLeastOneFilled",
          function (value, element) {
                var csQuantity = $("#CSQuantity").val();
                var ibQuantity = $("#IBQuantity").val();
                var pcQuantity = $("#PCQuantity").val();
        
                // Check if at least one has a value
                return csQuantity !== "" || ibQuantity !== "" || pcQuantity !== "";
          },
          "At least one quantity field is required."
        ); // Custom error message
    
        $("#itemModalFields").validate({
            rules: {
                CSQuantity: {
                    atLeastOneFilled: true,
                },
                IBQuantity: {
                    atLeastOneFilled: true,
                },
                PCQuantity: {
                    atLeastOneFilled: true,
                },
            },
            messages: {
                CSQuantity: {
                    atLeastOneFilled: "At least one quantity field is required.",
                },
                IBQuantity: {
                    atLeastOneFilled: "At least one quantity field is required.",
                },
                PCQuantity: {
                    atLeastOneFilled: "At least one quantity field is required.",
                },
            },
            submitHandler: function (form) {
                alert("Form is valid!");
                form.submit();
            },
        });
    },
    isValid: () => {
        return $('#itemModalFields').valid();
    },
    hide: () => {
        $('#itemModal').modal('hide');
    },
    show: () => {
        $('#itemModal').modal('show');
    },
    fill: (itemData) => {
        $('#Decription').val(itemData.Decription);
        $('#Quantity').val(itemData.Quantity);
        $('#UOM').val(itemData.UOM);
        $('#ItemVolume').val(itemData.ItemVolume);
        $('#ItemWeight').val(itemData.ItemWeight);
        $('#TotalPrice').val(itemData.TotalPrice);
        $('#PricePerUnit').val(itemData.PricePerUnit);
        document.querySelector('#StockCode').setValue(itemData.StockCode)
    },
    clear: () => {
        $('#itemModalFields input[type="text"]').val('');
        $('#itemModalFields input[type="number"]').val('');
        $('#itemModalFields textarea').val('');

        if (document.querySelector('#UOMDropDown')?.virtualSelect) {
            document.querySelector('#UOMDropDown').reset();
        }

        if (document.querySelector('#StockCode')?.virtualSelect) {
            document.querySelector('#StockCode').reset();
        }

    },
    enable: (enable) => {
        $('#itemModalFields input[type="text"]').prop('disabled', !enable);
        $('#itemModalFields input[type="number"]').prop('disabled', !enable);
        $('#itemModalFields textarea').prop('disabled', !enable);
    },
    itemTmpSave: (getItem) => {
        getItem = SOItemsModal.itemCalculateUOM(getItem);
        itemTmpSave.unshift(getItem);
        datatables.initSOItemsDatatable(itemTmpSave);
        calculateCost();
        SOItemsModal.hide();
    },
    itemTmpUpdate: (editedItem) => {
        // Optionally, if you want to reflect the change in currentItems
        editedItem = SOItemsModal.itemCalculateUOM(editedItem);
        itemTmpSave = itemTmpSave.map((item) =>
            item.MStockCode === selectedItem.MStockCode
            ? { ...item, ...editedItem }
            : item
        );

        datatables.initSOItemsDatatable(itemTmpSave);
        SOItemsModal.hide();
    },
    getUOM: () => {
        let UomAndQuantity = {
          CS: $("#CSQuantity").val(),
          IB: $("#IBQuantity").val(),
          PC: $("#PCQuantity").val(),
        };
    
        UomAndQuantity = Object.fromEntries(
          Object.entries(UomAndQuantity).filter(([_, value]) => value)
        );
    
        return UomAndQuantity;
    },
    getTotalQuantity: (UomAndQuantity) => {
        const ConvFactAltUom = productConFact.ConvFactAltUom;
        const ConvFactOthUom = productConFact.ConvFactOthUom;
        let totalInPieces = 0;
    
        Object.entries(UomAndQuantity).forEach(([key, uom]) => {
          if (key.toUpperCase() === "PC") {
            totalInPieces += Number(uom);
          } else if (key.toUpperCase() === "IB") {
            totalInPieces += (ConvFactAltUom / ConvFactOthUom) * Number(uom);
          } else if (key.toUpperCase() === "CS") {
            totalInPieces += Number(uom) * ConvFactAltUom;
          }
        });
        return totalInPieces;
    },
    itemEditMode: (uoms, isAlreadyExist) => {
        console.log(isAlreadyExist);
        
        if (!isAlreadyExist.UomAndQuantity) {
            console.log(isAlreadyExist.QTYinPCS);
            isAlreadyExist.UomAndQuantity = SOItemsModal.reverseItemCalculateUOM(
                uoms,
                isAlreadyExist.QTYinPCS
            );
          console.log(isAlreadyExist.UomAndQuantity);
        }
    
        Object.entries(isAlreadyExist.UomAndQuantity).forEach(([key, value]) => {
          $(`#${key}Quantity`).val(value);
        });
    
        $("#itemSave").text("Update Item");
    },
    reverseItemCalculateUOM: (uoms, totalInPieces) => {
        console.log("totalINPIECS: "+ totalInPieces);
        var moduloResult = 0;
        totalInPieces = selectedItem.QTYinPCS;
        let UomAndQuantity = {};
    
        const ConvFactAltUom = productConFact.ConvFactAltUom;
        const ConvFactOthUom = productConFact.ConvFactOthUom;
    
        uoms.forEach((element) => {
            if (element.value === "CS") {
                // Handle Case (CS) - Largest unit
                const getCS = Math.floor(totalInPieces / ConvFactAltUom);
                UomAndQuantity.CS = getCS;
                moduloResult = totalInPieces % ConvFactAltUom;
            } else if (element.value === "IB") {
                // Handle Intermediate Unit (IB)
                const conFact = ConvFactAltUom / ConvFactOthUom; // Calculate conversion factor between IB and CS
                moduloResult = moduloResult > 0 ? moduloResult : totalInPieces;
        
                const getIB = Math.floor(moduloResult / conFact);
                UomAndQuantity.IB = getIB;
        
                moduloResult = moduloResult % conFact;
            } else if (element.value === "PC") {
                UomAndQuantity.PC = moduloResult;
                // console.log(`PC: ${moduloResult}`);
            }
        });
        console.log(UomAndQuantity);
        return UomAndQuantity;
    },
    getData: () => {
        const getUOM = SOItemsModal.getUOM();

        return {
            MStockCode: $("#StockCode").val().trim(),
            MStockDes: $("#Decription").val(),
            MOrderUom: getUOM,
            UomAndQuantity: getUOM,
            MOrderQty: $("#Quantity").val(),
            MUnitCost: parseFloat($("#PricePerUnit").val()) || 0,  
            MPrice: parseMoney($("#TotalPrice").val()),
            MProductClass: 'GROC',
            MStockUnitMass: 0,
            MStockUnitVol: 0,
            MPriceCode: selectedVendor.PriceCode,
        };
        
    },
    itemCalculateUOM: (getItem) => {
        const uomsAndQty = getItem.UomAndQuantity;
        const ConvFactAltUom = productConFact.ConvFactAltUom;
        const ConvFactOthUom = productConFact.ConvFactOthUom;
    
        const totalInPieces = SOItemsModal.getTotalQuantity(uomsAndQty);
        if (uomsAndQty.CS) {
            getItem.MOrderUom = "CS";
            getItem.MOrderQty = (totalInPieces / ConvFactAltUom).toFixed(2);
        } else if (uomsAndQty.IB) {
            getItem.MOrderUom = "IB";
            getItem.MOrderQty = ( totalInPieces / (ConvFactAltUom / ConvFactOthUom) ).toFixed(2);
        } else if (uomsAndQty.PC) {
            getItem.MOrderUom = "PC";
            getItem.MOrderQty = totalInPieces;
        }
        getItem.MPriceUom = getItem.MOrderUom;
        getItem.MStockingUom = getItem.MOrderUom;
        getItem.QTYinPCS = totalInPieces;
        return getItem;
    },
    itemTmpDelete: (skuCode) => {
        itemTmpSave = itemTmpSave.filter(
          (item) => item.MStockCode != skuCode.text()
        );
    
        datatables.initSOItemsDatatable(itemTmpSave);
        calculateCost();
    },
}

const vendorModal = {
    loadVendorVS: async () => { await ajax( "api/vendors", "GET", null, (response) => {
          vendordata = response.data;
          const newData = vendordata.map((item) => {
            return {
              description: item.CompleteAddress,
              value: item.cID, 
              label: item.SupplierName, 
            };
          });
  
          if (document.querySelector("#vendorName")?.virtualSelect) {
            document.querySelector("#vendorName").destroy();
          }
  
          VirtualSelect.init({
            ele: "#vendorName",
            options: newData,
            markSearchResults: true,
            maxWidth: "100%",
            search: true,
            autofocus: true,
            hasOptionDescription: true,
            noSearchResultsText: `<div class="w-100 d-flex justify-content-around align-items-center mt-2">
                                    <div class="w-auto text-center">
                                        No result found. Add new?
                                    </div>
                                    <div class="w-auto">
                                        <button id="CustomerNoDataFoundBtn" type="button" class="btn btn-primary btn-sm">Add new</button>
                                    </div>
                                </div>`,
          });
        }, (xhr, status, error) => {
          // Error callback
          console.error("Error:", error);
        }
      );
    },
    isValid: () => {
      return $("#newVendorForm").valid();
    },
    show: () => {
      $("#newVendorModal").modal("show");
    },
    hide: () => {
      $("#newVendorModal").modal("hide");
    },
    clear: () => {
      document.querySelector("#Region").reset();
      document.querySelector("#Province").reset();
      document.querySelector("#CityMunicipality").reset();
      document.querySelector("#Barangay").reset();
  
      $('#newVendorForm input[type="text"]').val("");
      $('#newVendorForm input[type="number"]').val("");
      $("#newVendorForm textarea").val("");
    },
    getData: () => {
      return {
        SupplierName: $("#SupplierName").val(),
        SupplierType: $("#SupplierType").val(),
        TermsCode: $("#TermsCode").val(),
        ContactPerson: $("#ContactPerson").val(),
        ContactNo: $("#ContactNo").val(),
        CompleteAddress: $("#NVCompleteAddress").val(),
        Region: $("#Region").val(),
        Province: $("#Province").val(),
        City: $("#CityMunicipality").val(),
        Municipality: $("#CityMunicipality").val(),
        Barangay: $("#Barangay").val(),
        PriceCode: $("#newVendorPriceCode").val(),
      };
    },
    newVendorSave: async () => {
      const newVendor = vendorModal.getData();
  
      await ajax(
        "api/vendors",
        "POST",
        JSON.stringify({ newVendor }),
        (response) => {
          // Success callback
          if (response.success) {
            vendorModal.loadVendorVS();
            vendorModal.hide();
  
            Swal.fire({
              title: "Success!",
              text: response.message,
              icon: "success",
            });
          }
        },
        (xhr, status, error) => {
          // Error callback
  
          if (xhr.responseJSON && xhr.responseJSON.message) {
            Swal.fire({
              title: "Opppps..",
              text: xhr.responseJSON.message,
              icon: "error",
            });
          }
        }
      );
    },
};
  
function downloadToCSV(jsonArr){
    const csvData = Papa.unparse(jsonArr); // Convert JSON to CSV
    var today = new Date().toISOString().split('T')[0];

    // Create a blob and trigger download
    const blob = new Blob([csvData], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `SalesOrder_${today}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

}

async function ajaxCall(method, formDataArray = null, id) {
    let formData = new FormData();
    formData.append('salesman', JSON.stringify(formDataArray));

    return await $.ajax({
        url: globalApi + 'api/maintenance/v2/salesperson/upload',
        type: method,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token')
        },
        processData: false, // Required for FormData
        contentType: false, // Required for FormData
        data: JSON.stringify(formDataArray), // Convert the data to JSON format

        success: async function(response) {
            insertion++;
            expectedtotalRows += response.totalFileLength;
            actualtotalRows += response.successful;

            iconResult = `<span class="mdi mdi-alert-circle text-danger resultIcon"></span>`;
            var insertedResultColor = `text-danger`;

            if (response.status_response == 1) {
                iconResult = `<span class="mdi mdi-check-circle text-success resultIcon"></span>`
                insertedResultColor = 'text-success';


            } else if (response.status_response == 2) {
                iconResult = `<span class="mdi mdi-alert-circle text-warning resultIcon"></span>`
                insertedResultColor = 'text-warning';
            }

            $('#totalUploadSuccess').text(insertion);
            $("#fileStatus" + id).html(iconResult); 
            $("#insertedStat" + id).html(`${response.successful} / ${response.totalFileLength}`).addClass(insertedResultColor);
            
            if(fileCtrTotal>0 && fileCtrTotal==insertion){
                console.log('1')
                if(expectedtotalRows>0 && expectedtotalRows == actualtotalRows){
                    Swal.fire({
                        title: "Success!",
                        text: 'All data successfully Inserted',
                        icon: "success"
                    });
                } else {
                    var unsucc = expectedtotalRows-actualtotalRows;
                    let message = `Some data could not be inserted. <br>Please review the uploaded CSV file.<br><strong>${unsucc}</strong> Salesman${unsucc > 1 ? 's' : ''} were not inserted.<br><br><br>${issueTable}`;

                    Swal.fire({
                        title: "Warning!",
                        html: message,
                        icon: "warning"
                    });
                }
            }
            datatables.loadSalesmanData();
        },
        error: async function(xhr, subTotal, error) {
            Swal.fire({
                icon: "error",
                title: "Api Error",
                text: xhr.responseJSON?.message || xhr.statusText,

            });
            return xhr, subTotal, error;
        }
    });
}

const uploadconfirmUpload = document.getElementById('uploadBtn2')
    .addEventListener('click', () => {
        var appendTable = '';
        insertion = 0;
        fileCtrTotal = 0;
        expectedtotalRows = 0;
        actualtotalRows = 0; 
        errorFile = false;
        // Get all the files selected in the file input
        var files = document.getElementById('formFileMultiple').files;

        $('#totalUploadSuccess').html(insertion);
        $('#totalFiles').html(files.length);
        $('#totalFile').html(files.length);
        fileCtrTotal = files.length;
        // Loop over each file and check the extension
        for(let i=0; i < files.length; i++){
            var fileExtension = files[i].name.split('.').pop().toLowerCase();

            appendTable += trNew(files[i].name, i);
            if(!['csv','xlsx'].includes(fileExtension)){
                setTimeout(function() {
                    iconResult = `<span class="mdi mdi-alpha-x-circle text-danger resultIcon"></span>`;
                    $("#fileStatus" + i).html(iconResult); 
                }, 100);
                errorFile = true;
            }

            $('#fileListTable').html(appendTable);
        }

        if(!errorFile){
            for(let i=0; i < files.length; i++){
                var fileExtension = files[i].name.split('.').pop().toLowerCase();

                appendTable += trNew(files[i].name, i);
                if (fileExtension === 'csv') {
                    // processCSVFile(files[i], i); // Process CSV
                    console.log('CSV file.')
                }
                else if(fileExtension === 'xlsx'){
                    // processExcelFile(files[i], i); // Process CSV
                    console.log('Excel file.')
                }
                // $('#fileListTable').html(appendTable);
            }
            $('#uploadBtn2').html('Upload');
        } else{
            Swal.fire({
                icon: "error",
                title: "Review files",
                text: "Please select .csv files only",
            });
            $('#uploadBtn2').html('Reupload');
        }
    });

function processCSVFile(file, ctr) {
    Papa.parse(file, {
        header: true,
        skipEmptyLines: true,
        complete: function(results) {
            ajaxCall('POST', results.data, ctr);
        }
    });
}

function processExcelFile(file, ctr) {
    readXlsxFile(file).then((rows) => {
        let keys = rows[0]; // First row contains the keys
        let result = rows.slice(1).map(row => {
            return keys.reduce((obj, key, index) => {
                obj[key] = row[index]; // Map key to corresponding value in row
                return obj;
            }, {});
        });
        ajaxCall('POST', result, ctr);
    });
}


function trNew(fileName, indexId) {
    return `<tr id="fileRow${indexId}">
            <td class="imgSizeContainer col-1">
                <span class="mdi mdi-file-document-outline"></span>
            </td>
            <td class = "col-9" style="padding-left: 0px;">
                <span>${fileName}</span>
            </td>
            <td id="insertedStat${indexId}" class="text-end col-2">    
            
            </td>
            <td id="fileStatus${indexId}" class="text-center col-1">       
                <span class="loader">                                    
                </span>              
            </td>
        </tr>`;
}

const isStatSaveNew = () => {
    return $("#saveBtn").is(":visible");
};

function autoCalculateTotalPrice() {
    const uoms = SOItemsModal.getUOM();
    const totalInPieces = SOItemsModal.getTotalQuantity(uoms);
    $("#TotalPrice").val(
      formatMoney(($("#PricePerUnit").val() || 0) * totalInPieces)
    );
}

async function getProductPriceCodes() { await ajax( "api/getProductPriceCodes", "GET", null,
      (response) => {
        priceCodes = response.success && response.data;
      },
      (xhr, status, error) => {
        // Error callback
        console.error("Error:", error);
      }
    );
}

function formatMoney(amount, locale = "en-PH", currency = "PHP") {
    return new Intl.NumberFormat(locale, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}

function parseMoney(formattedAmount) {
    return parseFloat(formattedAmount.replace(/[^0-9.-]+/g, ""));
}

function calculateCost() {
    const taxCost = 0;
    const shippingCost = 0;
    const othersCost = 0;
    const grandTotal = ItemsTH.rows()
      .data()
      .toArray()
      .reduce((sum, item) => sum + parseFloat(item.MPrice), 0);
  
    $("#taxCost").text(formatMoney(taxCost));
    $("#shippingCost").text(formatMoney(shippingCost));
    $("#othersCost").text(formatMoney(othersCost));
    $("#grandTotal").text(formatMoney(grandTotal));
    $("#subTotal").text(formatMoney(grandTotal));
}

function setDate(){
    let today = new Date().toISOString().split('T')[0];
    document.getElementById("ReqShipDate").setAttribute("min", today);
    document.getElementById("ReqShipDate").addEventListener("input", function () {
        let selectedDate = new Date(this.value);
        
        if (selectedDate.getDay() === 0) {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "No Shipment on Sundays... Select Another Day",
            });
            this.value = ""; 
        }
    });
}


const SOStatus = {
    ChangeSOStatus: async (status, salesOrder, lastOperator) => {
        await ajax("api/sales-order/orderstatus/"+status, "POST", JSON.stringify({ salesOrder, lastOperator }),
            (response) => {
            // Success callback
            if (response.success) {
    
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                });

                SOModal.hide();
                datatables.loadSOData();
            }
            },
            (xhr, status, error) => {
            // Error callback
    
            if (xhr.responseJSON && xhr.responseJSON.message) {
                Swal.fire({
                title: "Opppps..",
                text: xhr.responseJSON.message,
                icon: "error",
                });
            }
            }
        );
    },
}
  