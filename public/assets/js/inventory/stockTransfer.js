var MainTH, MainTH2, selectedMain;
var ItemsTH, selectedItems;
var fileCtrTotal = 0;
var insertion = 0;
var jsonArr = [];
var availWH = [];
var filteredWH = [];
var warehouseInv = null;
var itemTmpSave = [];
var productConFact;
var userObject;



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
                            </div>`;

$(document).ready(async function () {
    dayjs.extend(dayjs_plugin_relativeTime);
    const user = localStorage.getItem('user');
    userObject = JSON.parse(user);
    await datatables.loadInvTransferData();
    await initVS.liteDataVS();
    await initVS.origWHVS();
    await initVS.destWHVS([]);

    $(document).on('click', '#addBtn', async function () {
        STModal.clear();
        STModal.enable(true);

        $('#EntryDate').val(new Date().toISOString().slice(0,10));
        $('#saveSTBtn').show();
        itemTmpSave = [];
        // selectedMain = null;
        datatables.initSTItemsDatatable(null);
        STModal.show();
    });

    $("#addItems").on("click", function () {
        if($('#VSWarehouse').val()){
            if(warehouseInv != null){
                STItemsModal.clear();
                STItemsModal.enable(true);
    
                $(".UOMField").addClass("d-none");
                $("#itemSave").text("Save Item");
                STItemsModal.show();
    
                $("#itemEdit").hide();
                $("#itemSave").show();
                ItemsTH.column(4).visible(true);
            } else{
                Swal.fire({
                    title: "Loading",
                    text: "Fetching products from the selected warehouse is still in progress. Please try again.",
                    icon: "warning",
                });
            }
        } else {
            Swal.fire({
                title: "Opppps..",
                text: "Select Warehouse First",
                icon: "warning",
            });
        }
    });

    $("#itemSave").on("click", function () {
        if ( STItemsModal.isValid() ) {
            const getItem = STItemsModal.getData();
            var checkCurrentItem = STItemsModal.itemCalculateUOM(getItem);
            var isEnough = invStockChecker(checkCurrentItem);
            if(isEnough){
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
                            STItemsModal.itemTmpUpdate(getItem);
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
                            STItemsModal.itemTmpSave(getItem);
                        }
                    });
                }
                datatables.initSTItemsDatatable(itemTmpSave);
            } else{
                Swal.fire({
                    title: "Stock Not Enough!",
                    text: "The quantity you entered exceeds the quantity on hand in the warehouse.",
                    icon: "warning",
                });
            }
        } else {
            Swal.fire({
                title: "Missing Required Fields!",
                text: "Please fill in all fields. Some required fields are empty.",
                icon: "warning",
            });
        }
        isSelectedEdited = true;
    });

    $("#saveSTBtn").on("click", function () {
        if (STModal.isValid()) {
            if (itemTmpSave.length < 1) {
                Swal.fire({
                title: "No items",
                text: "Please review your order. No items have been added for Sales Order.",
                icon: "error",
                });
            } else {
                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you want to transfer this stocks?",
                    icon: "question",
                    showDenyButton: true,
                    confirmButtonText: "Yes, Transfer",
                    denyButtonText: `Cancel`,
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        STModal.STSave();
                        // STModal.hide();
                        // Swal.fire({
                        //     text: "Please wait... Transferring Stocks...",
                        //     timerProgressBar: true,
                        //     allowOutsideClick: false,
                        //     allowEscapeKey: false,  
                        //     allowEnterKey: false,
                        //     didOpen: () => {
                        //         Swal.showLoading();
                        //     },
                        // });
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
    loadInvTransferData: async () => {
        const invData = await ajax('api/inv/transfer/stocks', 'GET', null, (response) => {  
            jsonArr = response.data;
            datatables.initInvTransferDatatable(response);
        }, (xhr, status, error) => { 
            console.error('Error:', error);
        });
    },

    initInvTransferDatatable: (response) => {
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
            } else {
                MainTH = $('#transferTable').DataTable({
                    data: response.data,
                    layout: {
                        topStart: function () {
                            return $(dataTableCustomBtn);
                        }
                    },
                    columns: [
                        { data: 'EntryDate',  title: 'Transfer Date' },
                        { data: 'Warehouse',  title: 'Origin Warehouse' },
                        { data: 'NewWarehouse',  title: 'Destination Warehouse' },
                        { data: 'StockCode',  title: 'StockCode' },
                        { data: 'productdetails.Description',  title: 'Description' },
                        { data: 'TrnQty',  title: 'Qty in PCS' },
                    ],
                    columnDefs: [
                        // { className: "text-start", targets: [ 0, 2, 3, 4, 8 ] },
                        // { className: "text-center", targets: [ 1, 5, 6, 7 ] },
                        // { className: "text-end", targets: [ 4 ] },
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
                        // $('.dt-layout-table').addClass('mt-4');
                    }

                });

            }

        }
    },

    loadItems: async (SONumber) => {

        const stItems = await ajax('api/orders/po-items/search-items/' + SONumber, 'GET', null, (response) => { // Success callback
            ajaxItemsData = response.data;
            datatables.initSTItemsDatatable(ajaxItemsData);

        }, (xhr, status, error) => { // Error callback
            console.error('Error:', error);
        });


    },

    initSTItemsDatatable: (datas) => {
        if (ItemsTH) {
            ItemsTH.clear().draw();
            datas && ItemsTH.rows.add(datas).draw();
        } else {
            ItemsTH = $('#itemTables').DataTable({
                dom: "rt<'d-flex justify-content-between' ip>",
                data: datas,
                columns: [
                    { data: 'StockCode' },
                    { data: 'Description' },
                    { data: 'OrderQty',
                        render: function (data, type, row){
                            return parseFloat(data);
                        }
                    },
                    { data: 'OrderUom' },
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
                    { className: "text-end", targets: [4] },
                    { className: "text-nowrap", targets: '_all' },
                ],
                searching: true,
                scrollCollapse: true,
                responsive: true, // Enable responsive modeoWidth: true, // Enable auto-width calculation
                // scrollY: '100%',
                // scrollX: '100%',
                "pageLength": 5,
                "createdRow": function (row, data) {
                    $(row).attr('id', data.id);
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
            additionalToggleButtonClasses: 'rounded',
        });
    },
    bigDataVS: async () => {
        var selectedWH = $('#VSWarehouse').val();
        await ajax( "api/inv/transfer/warehouse/inventory/"+ selectedWH, "GET", null,
            (response) => {
                warehouseInv = response; 
                const products = response.data;
        
                const newData = products.map((item) => {
                return {
                    description: item.productdetails.Description,
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
                        $("#Decription").val(findProduct.productdetails.Description);

                        var uomColumn = ["StockUom", "AlternateUom", "OtherUom"];
        
                        var uoms = uomColumn.map((item) => {
                            return {
                                value: findProduct.productdetails[item], 
                                label: findProduct.productdetails[item], 
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
        
                        productConFact = {
                            ConvFactAltUom: findProduct.productdetails.ConvFactAltUom,
                            ConvFactOthUom: findProduct.productdetails.ConvFactOthUom
                        };

                        $("#itemSave").prop("disabled", false);

                        const isAlreadyExist = itemTmpSave.find(
                            (item) => item.StockCode == stockCode
                        );

                        if (isAlreadyExist) {
                            selectedItem = isAlreadyExist;
                            STItemsModal.itemEditMode(uoms, isAlreadyExist);
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
                            text: "Select product",
                            icon: "warning",
                        });
                    }
                });

                $("#StockCode").on("reset", function () {
                    $("#Decription").val("");
                    $("#PricePerUnit").val("");
                });
        
            }
        );
    },
    origWHVS: async () => {
        const invData = await ajax('api/wh/all-warehouse', 'GET', null, (response) => {  
            console.log(response);
            availWH = response.data;

            const warehouses = response.data;
    
            const whData = warehouses.map((wh) => {
              return {
                value: wh.Warehouse, 
                label: wh.Warehouse, 
              };
            });
    
            if (document.querySelector("#VSWarehouse")?.virtualSelect) {
              document.querySelector("#VSWarehouse").destroy();
            }

            VirtualSelect.init({
                ele: '#VSWarehouse',   
                options: whData, 
                multiple: false, 
                hideClearButton: false, 
                search: true,
                maxWidth: '100%', 
                additionalClasses: 'rounded',
                additionalDropboxClasses: 'rounded',
                additionalDropboxContainerClasses: 'rounded',
                additionalToggleButtonClasses: 'rounded',
            });
            // datatables.initInvTransferDatatable(response);
        }, (xhr, status, error) => { 
            console.error('Error:', error);
        });

        $("#VSWarehouse").on("afterClose", async function () {
            if (this.value) {
                filteredWH = availWH.filter((item) => { return item.Warehouse != this.value});
                initVS.destWHVS(filteredWH);
                warehouseInv = null;
                await initVS.bigDataVS();

            } else{
                initVS.destWHVS([]);
                warehouseInv = null;
            }
        });

        $("#VSWarehouse").on("reset", function () {
            initVS.destWHVS([]);
            warehouseInv = null;
        });
    },
    destWHVS: async (destData) => {
        const destwhData = destData.map((wh) => {
            return {
                value: wh.Warehouse, 
                label: wh.Warehouse, 
            };
        });
    
        if (document.querySelector("#VSNewWarehouse")?.virtualSelect) {
            document.querySelector("#VSNewWarehouse").destroy();
        }

        VirtualSelect.init({
            ele: '#VSNewWarehouse',   
            options: destwhData, 
            multiple: false, 
            hideClearButton: false, 
            search: true,
            maxWidth: '100%', 
            additionalClasses: 'rounded',
            additionalDropboxClasses: 'rounded',
            additionalDropboxContainerClasses: 'rounded',
            additionalToggleButtonClasses: 'rounded',
        });
    },
}

const STModal = {
    isValid: () => {
        return $("#modalFields").valid();
    },
    show: () => {
        $('#stockTransferMainModal').modal('show');
    },
    hide: () => {
        $('#stockTransferMainModal').modal('hide');
    },
    clear: () => {
        $('#modalFields input[type="text"]').val('');
        $('#modalFields input[type="number"]').val('');
        $('#modalFields input[type="date"]').val('');
        $('#modalFields textarea').val('');
    },
    enable: (enable) => {
        $('#modalFields input[type="text"]').prop('disabled', !enable);
        $('#modalFields input[type="number"]').prop('disabled', !enable);
        $('#modalFields input[type="date"]').prop('disabled', !enable);
        $('#modalFields textarea').prop('disabled', !enable);
        $('#modalFields #Reference').prop('disabled', true);
        $('#modalFields #EntryDate').prop('disabled', true);
        $('#addItems').prop('disabled', !enable);
    },
    getData: () => {
        return {
            Warehouse: $("#VSWarehouse").val().trim(),
            NewWarehouse: $("#VSNewWarehouse").val(),
        };
    },
    STSave: async () => {
        let STData = STModal.getData();
        STData.Items = itemTmpSave.map((item, index) => ({
          ...item,  
          PRD_INDEX: index + 1, 
        }));
    
        STData.LastOperator = userObject.name;
        console.log("STData:", STData);
        await ajax("api/inv/warehouse-movement-transfer", "POST", JSON.stringify({ data: STData }),
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
}

const STItemsModal = {
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
        getItem = STItemsModal.itemCalculateUOM(getItem);
        itemTmpSave.unshift(getItem);
        datatables.initSTItemsDatatable(itemTmpSave);
        STItemsModal.hide();
    },
    itemTmpUpdate: (editedItem) => {
        // Optionally, if you want to reflect the change in currentItems
        editedItem = STItemsModal.itemCalculateUOM(editedItem);
        itemTmpSave = itemTmpSave.map((item) =>
            item.StockCode === selectedItem.StockCode
            ? { ...item, ...editedItem }
            : item
        );

        datatables.initSTItemsDatatable(itemTmpSave);
        STItemsModal.hide();
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
            // console.log(isAlreadyExist.TrnQty);
            isAlreadyExist.UomAndQuantity = STItemsModal.reverseItemCalculateUOM(
                uoms,
                isAlreadyExist.TrnQty
            );
        //   console.log(isAlreadyExist.UomAndQuantity);
        }
    
        Object.entries(isAlreadyExist.UomAndQuantity).forEach(([key, value]) => {
          $(`#${key}Quantity`).val(value);
        });
    
        $("#itemSave").text("Update Item");
    },
    reverseItemCalculateUOM: (uoms, totalInPieces) => {
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
        const getUOM = STItemsModal.getUOM();

        return {
            StockCode: $("#StockCode").val().trim(),
            Description: $("#Decription").val(),
            OrderUom: getUOM,
            UomAndQuantity: getUOM,
            OrderQty: $("#Quantity").val(),
            ProductClass: 'GROC',
            StockUnitMass: 0,
            StockUnitVol: 0,
        };
        
    },
    itemCalculateUOM: (getItem) => {
        const uomsAndQty = getItem.UomAndQuantity;
        const ConvFactAltUom = productConFact.ConvFactAltUom;
        const ConvFactOthUom = productConFact.ConvFactOthUom;
    
        const totalInPieces = STItemsModal.getTotalQuantity(uomsAndQty);
        if (uomsAndQty.CS) {
            getItem.OrderUom = "CS";
            getItem.OrderQty = (totalInPieces / ConvFactAltUom);
        } else if (uomsAndQty.IB) {
            getItem.OrderUom = "IB";
            getItem.OrderQty = ( totalInPieces / (ConvFactAltUom / ConvFactOthUom) );
        } else if (uomsAndQty.PC) {
            getItem.OrderUom = "PC";
            getItem.OrderQty = totalInPieces;
        }
        getItem.PriceUom = getItem.OrderUom;
        getItem.StockingUom = getItem.OrderUom;
        getItem.TrnQty = totalInPieces;
        return getItem;
    },
    itemTmpDelete: (skuCode) => {
        itemTmpSave = itemTmpSave.filter(
          (item) => item.MStockCode != skuCode.text()
        );
    
        datatables.initSTItemsDatatable(itemTmpSave);
    },
}

function invStockChecker(item) {
    var invStockList = warehouseInv.data;

    for (const value of invStockList) {
        if (value.StockCode === item.StockCode) {
            if (parseInt(value.QtyOnHand) >= parseInt(item.TrnQty)) {
                return true; // enough stocks
            } else {
                return false; // not enough stocks
            }
        }
    }
    // if no matching StockCode was found
    return false;
}
