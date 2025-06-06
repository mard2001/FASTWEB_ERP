var MainTH, selectedMain;
var ItemsTH, selectedItems;
var ajaxMainData, ajaxItemsData;
var shippedToData, selectedShippedTo;
var vendordata, selectedVendor;
var itemTmpSave = [];
var priceCodes;
var fileCtrTotal = 0;
var insertion = 0;
var jsonArr = [];
var detailsDatatable;
var isEditable = false;
var originalSelected = [];
var productConFact;
var isEditing = false;
var isSelectedEdited = false;
var tmpUpdatedMain;
var filteredStartDate;
var filteredEndDate;
var options = [];

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
const dataTableFilter = `<div class="FilterBtnDiv">
                            <button type="button" id="filterBtn" class="btn FilterRES" style="font-size: 0.9em">
                                <i class="fa-solid fa-filter"></i> Filter Sales Orders 
                            </button>
                        </div>`;

$(document).ready(async function () {
    const user = localStorage.getItem('user');
    const userObject = JSON.parse(user);

    await datatables.loadSOData();
    setTimeout(() => {
        const select = document.querySelector("#filterPOVS");
        select.setValue("8");
    },100);
    await initVS.liteDataVS();
    await initVS.bigDataVS();
    await getProductPriceCodes();
    SOItemsModal.setValidator();
    initVS.initFilterDataVS();
    setDate();
    datePicker();
    
    
    $('#filterBtn').on("click", function () {
        $('.reportrangeDiv').hide();
        $('#checkboxDateFiltering').prop('checked', false);
        $('#filterSOModal').modal('show');
    });

    $("#soTable").on("click", "tbody tr", async function () {
        $("#soTable tbody").css('pointer-events', 'none');
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
                isEditing = false;
                isSelectedEdited = false;
                // console.log(response);
                selectedMain = response.data;
                SOModal.buttonsView();
                SOModal.enable(false);
                SOModal.viewMode(response.data);
                // originalSelected = JSON.parse(JSON.stringify(response.data));

            } else {
                Swal.fire({
                    title: "Opppps..",
                    text: response.message,
                    icon: "error"
                });

            }
            $("#soTable tbody").css('pointer-events', 'auto');
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

    $("#getFilteredBtn").on("click", async function () {
        getFilteredSO();
    });

    $('#checkboxDateFiltering').change(function() {
        if ($(this).is(':checked')) {
            $('.reportrangeDiv').show();
            console.log('checked');
        } else {
            $('.reportrangeDiv').hide();
            console.log('unchecked');
        }
        options = [];
        initVS.initFilterDataVS();
    });

    $(document).on('click', '#addBtn', async function () {
        SOModal.clear();
        SOModal.enable(true);
        
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
        // ItemsTH.column(5).visible(true);
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
        SOStatus.ChangeSOStatus('complete', selectedMain.SalesOrder,userObject.name, selectedMain);
    });

    $("#editSOBtn").on("click", function () {
        if($(this).text().toLowerCase() == "edit order") {
            isEditing = true;
            $(this).text("Save Changes");
            SOModal.enable(true);
            ItemsTH.column(5).visible(true);
            $('.statBtns').hide();
        } else {
            const updatedData = SOModal.checkData();
            console.log(updatedData);
            hasChanges(selectedMain, updatedData);
            if(isEditing && isSelectedEdited){
                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you want to update this data?",
                    icon: "question",
                    showDenyButton: true,
                    confirmButtonText: "Yes, Update",
                    denyButtonText: `Cancel`,
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        SOModal.SOUpdate();
                        Swal.fire({
                            text: "Please wait... Saving New Changes...",
                            timerProgressBar: true,
                            allowOutsideClick: false,
                            allowEscapeKey: false,  
                            allowEnterKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });
                    } else{
                        SOModal.fill(selectedMain);
                    }
                    if($('#editSOBtn').text().toLowerCase() == "save changes"){
                        $(this).text("Edit Order");
                        SOModal.enable(false);
                        ItemsTH.column(5).visible(false);
                        SOModal.buttonsView();
                        isEditing = false;
                        isSelectedEdited = false;
                    }
                });
            } else{
                if($('#editSOBtn').text().toLowerCase() == "save changes"){
                    $(this).text("Edit Order");
                    SOModal.enable(false);
                    ItemsTH.column(5).visible(false);
                    SOModal.buttonsView();
                    isEditing = false;
                    isSelectedEdited = false;
                }
            }
            
        }
        
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
                        SOModal.hide();
                        Swal.fire({
                            text: "Please wait... Saving Sales Order...",
                            timerProgressBar: true,
                            allowOutsideClick: false,
                            allowEscapeKey: false,  
                            allowEnterKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });
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
        ItemsTH.column(5).visible(true);
    });

    $("#itemSave").on("click", function () {
        if ( SOItemsModal.isValid() && $("#TotalPrice").val() && parseInt(parseMoney($("#TotalPrice").val())) > 0 ) {
            const getItem = SOItemsModal.getData();
            if ($(this).text().toLowerCase() == "update item") {
                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you want to update the item?",
                    icon: "question",
                    showDenyButton: true,
                    confirmButtonText: "Yes, Update",
                    denyButtonText: `Cancel`,
                }).then(async (result) => {
                    if(result.isConfirmed){
                        SOItemsModal.itemTmpUpdate(getItem);
                    }
                });
            } else {
                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you want to save the item?",
                    icon: "question",
                    showDenyButton: true,
                    confirmButtonText: "Yes, Save",
                    denyButtonText: `Cancel`,
                }).then(async (result) => {
                    if(result.isConfirmed){
                        getItem.PRD_INDEX = itemTmpSave ? itemTmpSave.length + 1 : 1;
                        SOItemsModal.itemTmpSave(getItem);
                    }
                });
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
        isSelectedEdited = true;
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
        Swal.fire({
            title: "Are you sure?",
            text: "Do you want to delete this product?",
            icon: "question",
            showDenyButton: true,
            confirmButtonText: "Yes, Delete",
            denyButtonText: `Cancel`,
        }).then(async (result) => {
            if (result.isConfirmed) {
                const row = $(this).closest("tr");
                // const skuCode = row.find("td:first"); // Get the first <td>
                const skuCode = row.attr("id");
                SOItemsModal.itemTmpDelete(skuCode);
                isSelectedEdited = true;
            }
        });
    });

    $(document).on("click", ".itemUpdateIcon", async function () {
        const row = $(this).closest("tr");
        // const itemStockCode = row.find("td:first").text().trim();
        const itemStockCode = row.attr("id");
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
        // console.log(response.data);
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
            } else {
                MainTH = $('#soTable').DataTable({
                    data: response.data,
                    layout: {
                        topStart: function () {
                            return $(dataTableFilter);
                        }
                    },
                    columns: [
                        {
                            data: 'OrderDate',
                            title: 'Order Date',
                            render: function (data, type, row) {
                                if (!data) return '';

                                const dateObj = new Date(data);
                                return dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short',   year: 'numeric'});
                            }
                        },
                        {
                            data: 'null',
                            title: 'Sales Order',
                            render: function (data, type, row) {
                                let color = '';
                                let backgroundColor = '';
                                let text = '';
                        
                                switch (row.OrderStatus	) {
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
                        
                                var bardgeStatus = `<span class="statusbadge" style="color: ${color}; background-color: ${backgroundColor};" border: 1px solid ${color}">${text}</span>`;
                                return `<strong>${row.SalesOrder}</strong><br><small>${bardgeStatus}</small>`;

                            }
                        },
                        { data: 'DocumentType',  title: 'Document Type' },
                        {
                            data: null,
                            title: 'Customer',
                            render: function (data, type, row) {
                                return `<strong>${row.Customer}</strong><br><small>${row.CustomerName}</small>`;
                            }
                        },
                        { data: 'CustomerPoNumber',  title: 'SO Reference' },
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
                        { className: "text-start", targets: [ 0, 1, 3, 4, 5, 8,] },
                        { className: "text-center", targets: [ 2, 6, 7, 9, 10  ] },
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
                        $(this.api().table().container()).find('.dt-search label').addClass('py-1 px-3 mx-0 dtsearchLabel').html('<span class="mdi mdi-magnify"></span>');
                        $(this.api().table().container()).find('.dt-layout-row').first().find('.dt-layout-cell').each(function() { this.style.setProperty('height', '45px', 'important'); });
                        $(this.api().table().container()).find('.dt-layout-table').removeClass('px-4');
                        $(this.api().table().container()).find('.dt-scroll-body').addClass('rmvBorder');
                        $(this.api().table().container()).find('.dt-layout-table').addClass('btmdtborder');

                        const dtlayoutTE = $('.dt-layout-cell.dt-end').first();
                        dtlayoutTE.addClass('d-flex justify-content-end');
                        dtlayoutTE.prepend('<div id="filterPOVS" name="filter" style="width: 200px;" class="form-control bg-white p-0 mx-1">Filter</div>');
                        $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                        $('.loadingScreen').remove();
                        $('#dattableDiv').removeClass('opacity-0');
                        // $('#soTable thead tr td .dt-column-title').css('white-space','nowrap !important');
                    }
                });
            }
        }
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
                dom: "rt<'d-flex justify-content-between' ip>",
                data: datas,
                columns: [
                    { 
                        data: null,
                        title: 'Product Item',
                        render: function (data, type, row){
                            if (!data) return '';

                            return `<strong>${row.MStockCode}</strong><br><small>${data.MStockDes}</small>`;
                        }
                    },
                    { 
                        data: 'MOrderQty',
                        render: function (data, type, row){
                            return parseFloat(data);
                        }
                    },
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
                    { className: "text-start", targets: [ 0 ] },
                    { className: "text-center", targets: [ 1, 2, 5 ] },
                    { className: "text-end", targets: [ 3, 4 ] },
                    { className: "text-nowrap", targets: '_all' },
                ],
                searching: true,
                scrollCollapse: true,
                responsive: true, // Enable responsive modeoWidth: true, // Enable auto-width calculation
                // scrollY: '100%',
                // scrollX: '100%',
                "pageLength": 5,
                "createdRow": function (row, data) {
                    $(row).attr('id', data.MStockCode);
                },
                "lengthChange": false,  // Hides the per page dropdown
                initComplete: function () {
                    $(this.api().table().container()).find('#itemTables_info').css({'font-size':'11px'});
                    $(this.api().table().container()).find('.paging_full_numbers').css({'font-size':'10px','padding-top':'10px'});
                }
            });
            // Move search input to #searchContainer
            $('#searchBar').html('<input type="text" id="customItemSearchBox" placeholder="Search...">');

            // Custom search event
            $('#customItemSearchBox').on('keyup', function() {
                ItemsTH.search(this.value).draw();
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
                { label: "Open Order", value: "1" },
                { label: "Open Back Order", value: "2" },
                { label: "Release Back Order", value: "3" },
                { label: "In Warehouse", value: "4" },
                { label: "To Invoice", value: "8" },
                { label: "Forward Order", value: "F" },
                { label: "In Suspense", value: "S" },
                { label: "Complete", value: "9" },

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

        $("#filterPOVS").on("change", async function () {
            if (this.value) {
                // console.log(this.value);
                var filteredData = { data:[], success: true };
                var filterValues = this.value;
                if(filterValues.length == 0){
                    filteredData.data = jsonArr;
                } else{
                    filteredData.data = jsonArr.filter(item => filterValues.includes(item.OrderStatus));
                }
                datatables.initSODatatable(filteredData);
                
            }
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
                additionalClasses: 'rounded',
                additionalDropboxClasses: 'rounded',
                additionalDropboxContainerClasses: 'rounded dropboxCont',
                additionalToggleButtonClasses: 'rounded ModalFieldCustomVS',

            });

            $("#shippedToName").on("reset", function () {
                $("#shippedToContactName").val("");
                $("#shippedToAddress").val("");
                $("#shippedToPhone").val("");
            });

            $('#shippedToName').on('afterClose', function () {
                if (this.value) {
                    var findCustomer = response.data.find(item => item.Customer == this.value);
                    selectedShippedTo = findCustomer;

                    $('#shippedToContactName').val(findCustomer.Contact.trim());
                    $('#shippedToAddress').val((findCustomer.SoldToAddr1	+ ", " + findCustomer.SoldToAddr2 + ", " + findCustomer.SoldToAddr3).trim());

                    var mobileContact = findCustomer.Telephone = /^9\d{9}$/.test(findCustomer.Telephone) ? findCustomer.Telephone.replace(/^9/, "09") : findCustomer.Telephone;

                    $('#shippedToPhone').val(mobileContact);
                }
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
                additionalClasses: 'rounded',
                additionalDropboxClasses: 'rounded',
                additionalDropboxContainerClasses: 'rounded',
                additionalToggleButtonClasses: 'rounded ModalFieldCustomVS',
            });
    
            $("#StockCode").on("afterClose", async function () {
                if (this.value) {
                    const stockCode = this.value;
                    var findProduct = products.find(
                        (item) => item.StockCode == stockCode
                    );
                    // console.log(findProduct);
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
                        console.log(productConFact);

                        $("#PricePerUnit").val(response.response.UNITPRICE);
                        $("#itemSave").prop("disabled", false);
      
                        const isAlreadyExist = itemTmpSave.find(
                            (item) => item.MStockCode == stockCode
                        );
                        // console.log(isAlreadyExist);
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

    initFilterDataVS: () => {
        if (document.querySelector('#salesOrderList')?.virtualSelect) {
            document.querySelector('#salesOrderList').destroy();
        }

        VirtualSelect.init({
            ele: '#salesOrderList',
            multiple: true,
            options: options,
            markSearchResults: true,
            maxWidth: '100%',
            search: true,
            autofocus: true,
            allowNewOption: true,
            hasOptionDescription: true,
            noOptionsText: 'No sales orders found.'
        });
    },

    filterDataVS: () => {
        var filterDate = {filteredStartDate,filteredEndDate};

        Swal.fire({
            text: "Please wait... Fetching Sales Orders within the date range...",
            timerProgressBar: true,
            allowOutsideClick: false,
            allowEscapeKey: false,  
            allowEnterKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        ajax( "api/sales-order/orderstatus/filtered/", "POST", JSON.stringify({ 
                data: filterDate
            }),
            (response) => {
                options = [];
                document.querySelector('#salesOrderList').virtualSelect.reset();
                if(response.data.length > 0){
                    options = response.data.map((item) => {
                        return {
                            value: item.SalesOrder, 
                            label: item.SalesOrder + " - " + item.CustomerName, 
                        };
                    });
                }

                // Check if the VirtualSelect instance exists before destroying
                if (document.querySelector('#salesOrderList')?.virtualSelect) {
                    document.querySelector('#salesOrderList').destroy();
                }

                VirtualSelect.init({
                    ele: '#salesOrderList',
                    multiple: true,
                    options: options,
                    markSearchResults: true,
                    maxWidth: '100%',
                    search: true,
                    autofocus: true,
                    allowNewOption: true,
                    hasOptionDescription: true,
                    noOptionsText: 'No sales orders found for the specified date range.'
                });

                Swal.close();
                $('.salesOrderListDiv').show();
            }
        )
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
        $('#modalFields input[type="date"]').val('');
        $('#modalFields textarea').val('');
        $('#subTotal').html('0');
        $('#taxCost').html('0');
        $('#totalItemsLabel').html('0');
        $('#grandTotal').html('0');
        // $('#shippedToContactName').val('');
        // $('#shippedToAddress').val('');
        // $('#shippedToPhone').val('');
        if (document.querySelector('#shippedToName')?.virtualSelect) {
            document.querySelector('#shippedToName').reset();
        }
    },
    enable: (enable) => {
        $('#modalFields input[type="text"]').prop('disabled', !enable);
        $('#modalFields input[type="number"]').prop('disabled', !enable);
        $('#modalFields input[type="date"]').prop('disabled', !enable);
        $('#modalFields textarea').prop('disabled', !enable);
        $('#modalFields #OrderStatus').prop('disabled', true);
        $('#modalFields #SalesOrder').prop('disabled', true);
        $('#modalFields #CustomerPONumber').prop('disabled', true);
        $('#addItems').prop('disabled', !enable);
        
        if (enable) {
            document.querySelector("#shippedToName").enable();
        } else {
            document.querySelector("#shippedToName").disable();
        }
    },
    viewMode: async (SOData) => {
        SOModal.fill(SOData);
        itemTmpSave = SOData.details;
        // datatables.initSOItemsDatatable(SOData.details);
        $('#rePrintPage').show();
        $('#editSOBtn').text("Edit Order");
        SOItemsModal.enable(false);
        ItemsTH.column(5).visible(false);
        SOModal.show();
    },
    fill: async (SODetails) => {
        SOModal.clear();
        
        $('#OrderStatus').val(orderStatusSting(SODetails.OrderStatus));
        $('#Branch').val(SODetails.Branch);
        $('#CustomerPONumber').val(SODetails.CustomerPoNumber);
        $('#Warehouse').val(SODetails.Warehouse);
        $('#SalesOrder').val(SODetails.SalesOrder);
        $('#shippedToContactName').val(SODetails.CustomerName);
        $('#shippedToAddress').val(SODetails.ShipAddress1+", "+SODetails.ShipAddress2+", "+SODetails.ShipAddress3);
        // $('#shippedToPhone').val(SODetails.)
        $('#subTotal').html((SODetails.grandTotal).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#grandTotal').html((SODetails.grandTotal).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#OrderDate').val(SODetails.OrderDate.substring(0, 10)); 
        $('#ReqShipDate').val(SODetails.ReqShipDate.substring(0, 10)); 

        datatables.initSOItemsDatatable(SODetails.details);
        setTimeout(() => {
            const select = document.querySelector("#shippedToName");
            select.setValue(SODetails.Customer);

            const event = new CustomEvent("afterClose");
            select.dispatchEvent(event);
        }, 200);
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
                    Swal.close();
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
    SOUpdate: async () => {
        let updateBody = SOModal.getData();
        updateBody.Items = itemTmpSave;
        var salesOrderID = selectedMain.SalesOrder;

        // console.log(updateBody);
        await ajax( "api/sales-order/header/" + salesOrderID, "POST", JSON.stringify({ 
            data: updateBody,
            _method: "PUT",
            }),
            (response) => {
                if (response.success) {
                    Swal.close();
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
            OrderDate: $('#OrderDate').val(),
            ReqShipDate: $('#ReqShipDate').val(),
            SupplierCode: selectedVendor.SupplierCode.trim(),
            SupplierName: selectedVendor.SupplierName.trim(),
            productType: selectedVendor.SupplierType.trim(),
            // FOB: $("#fob").val(),
            deliveryAddress: $("#shippedToAddress").val(),
            contactPerson: $("#shippedToContactName").val(),
            contactNumber: $("#shippedToPhone").val(),
            shippedToName: $("#shippedToName").val(),
            totalDiscount: 0,
            totalTax: parseMoney($("#taxCost").text()),
            SpecialInstruction: $("#poComment").val(),
            // EncoderID: user.user_id,
            // orderPlacer: $("#requisitioner").val(),
            subTotal: parseMoney($("#subTotal").text()),
            // TermsCode: $("#shippingTerms").val(),
            totalCost: parseMoney($("#grandTotal").text()),
            Branch: $('#Branch').val(),
            Warehouse: $('#Warehouse').val()
        };
    
        return data;
    },
    checkData: () => {
        var data = {
            SalesOrder: $('#SalesOrder').val(),
            OrderStatus: $('#OrderStatus').val(),
            CustomerPoNumber: $('#CustomerPONumber').val(),
            Branch: $('#Branch').val(),
            Warehouse: $('#Warehouse').val(),
            OrderDate: $("#OrderDate").val(),
            ReqShipDate: $("#ReqShipDate").val(),
            Customer: $("#shippedToName").val(),
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
            $('#editSOBtn').show();
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
            } else if(selectedMain.OrderStatus == "9"){
                $('#editSOBtn').hide();
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
            // console.log(isAlreadyExist.QTYinPCS);
            isAlreadyExist.UomAndQuantity = SOItemsModal.reverseItemCalculateUOM(
                uoms,
                isAlreadyExist.QTYinPCS
            );
        //   console.log(isAlreadyExist.UomAndQuantity);
        }
    
        Object.entries(isAlreadyExist.UomAndQuantity).forEach(([key, value]) => {
          $(`#${key}Quantity`).val(value);
        });
    
        $("#itemSave").text("Update Item");
    },
    reverseItemCalculateUOM: (uoms, totalInPieces) => {
        console.log(uoms);
    
        let UomAndQuantity = {};
        let moduloResult = totalInPieces % productConFact.ConvFactAltUom;
    
        const { ConvFactAltUom, ConvFactOthUom } = productConFact;
    
        uoms.forEach((element) => {
            switch (element.value) {
                case "CS":
                    UomAndQuantity.CS = Math.floor(totalInPieces / ConvFactAltUom);
                    break;
    
                case "IB":
                    const conFact = ConvFactAltUom / ConvFactOthUom;
                    UomAndQuantity.IB = Math.floor((moduloResult === 0 ? totalInPieces : moduloResult) / conFact);
                    break;
    
                case "PC":
                    const hasIB = uoms.some(item => item.value === "IB");
                    let remainingForPC = hasIB ? moduloResult % (ConvFactAltUom / ConvFactOthUom) : totalInPieces % ConvFactOthUom;
    
                    UomAndQuantity.PC = remainingForPC;
                    break;
            }
        });
    
        // console.log(UomAndQuantity);
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
        console.log("getItem",getItem);
        const uomsAndQty = getItem.UomAndQuantity;
        const ConvFactAltUom = productConFact.ConvFactAltUom;
        const ConvFactOthUom = productConFact.ConvFactOthUom;
    
        const totalInPieces = SOItemsModal.getTotalQuantity(uomsAndQty);
        if (uomsAndQty.CS) {
            getItem.MOrderUom = "CS";
            getItem.MOrderQty = (totalInPieces / ConvFactAltUom);
        } else if (uomsAndQty.IB) {
            getItem.MOrderUom = "IB";
            getItem.MOrderQty = ( totalInPieces / (ConvFactAltUom / ConvFactOthUom) );
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
          (item) => item.MStockCode != skuCode
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
    ChangeSOStatus: async (status, salesOrder, lastOperator, sodata = null) => {
        await ajax("api/sales-order/orderstatus/"+status, "POST", JSON.stringify({ salesOrder, lastOperator, sodata }),
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
                } else{
                    Swal.fire({
                        title: "Warning!",
                        text: response.message,
                        icon: "warning",
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
}
  

function orderStatusSting(status){
    let text = '';
    switch (String(status)) {
        case '1': text = 'Open Order'; break;
        case '2': text = 'Open Back Order'; break;
        case '3': text = 'Released Back Order'; break;
        case '4': text = 'In Warehouse'; break;
        case '8': text = 'To Invoice'; break;
        case 'F': text = 'Forward Order'; break;
        case 'S': text = 'In Suspense'; break;
        case '9': text = 'Complete'; break;
        default: text = 'Unknown';
    }

    return text;
}

function hasChanges(original, modified) {
    const keysToCheck = ["SalesOrder", "CustomerPoNumber", "Branch", "Warehouse", "Customer", "shippedToName", "OrderDate", "ReqShipDate"];  

    for (let key of keysToCheck) {
        let originalValue = original[key];
        let modifiedValue = modified[key];

        if (modified.hasOwnProperty(key)) {
            if (key === "OrderDate" || key === "ReqShipDate") {
                originalValue = originalValue ? originalValue.split(" ")[0] : "";
                modifiedValue = modifiedValue ? modifiedValue.split(" ")[0] : "";
            }

            if (originalValue !== modifiedValue) {
                return isSelectedEdited = true;
            }
        }
    }
    return isSelectedEdited = false;
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
        initVS.filterDataVS();
    });

    $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    cb(start, end);
}

function getFilteredSO(){
    var salesOrderList = $('#salesOrderList').val();

    ajax( "api/sales-order/filtered-sales-order", "POST", JSON.stringify({ 
            salesOrder: salesOrderList,
        }),
        (response) => {
            $('#filterSOModal').modal('hide');
            if(response.success == 3){
                Swal.fire({
                    title: "Warning!",
                    text: response.message,
                    icon: "warning",
                });
            } else if (response.success) {
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                });

                jsonArr = response.data;
                datatables.initSODatatable(response);
                if (document.querySelector('#filterPOVS')?.virtualSelect) {
                    document.querySelector('#filterPOVS').reset();
                }
            }
        }
    )


}