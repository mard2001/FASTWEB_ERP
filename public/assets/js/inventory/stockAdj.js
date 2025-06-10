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
var previousVSWarehouseValue = null;

$(document).ready(async function () {
    dayjs.extend(dayjs_plugin_relativeTime);
    const user = localStorage.getItem('user');
    userObject = JSON.parse(user);
    await datatables.loadInvAdjData();
    await initVS.liteDataVS();
    await initVS.origWHVS();
    await initVS.adjTypeVS();
    SAdjitemsModal.setValidator();

    $(document).on('click', '#addBtn', async function () {
        SAdjModal.clear();
        SAdjModal.enable(true);

        $('#ENTRY_DATE').val(new Date().toISOString().slice(0,10));
        $('#saveSAdjBtn').show();
        itemTmpSave = [];
        // selectedMain = null;
        datatables.initSAdjItemsDatatable(null);
        SAdjModal.show();
    });

    $("#addItems").on("click", function () {
        if($('#VSWarehouse').val()){
            if(warehouseInv != null){
                SAdjitemsModal.clear();
                SAdjitemsModal.enable(true);

                $(".UOMField").addClass("d-none");
                $("#itemSave").text("Save Item");
                SAdjitemsModal.show();

                $("#itemEdit").hide();
                $("#itemSave").show();
                ItemsTH.column(4).visible(true);
            } else{
                Swal.fire({
                    title: "Loading...",
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
        if ( SAdjitemsModal.isValid() ) {
            const getItem = SAdjitemsModal.getData();
            var checkCurrentItem = SAdjitemsModal.itemCalculateUOM(getItem);

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
                        SAdjitemsModal.itemTmpUpdate(getItem);
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
                        SAdjitemsModal.itemTmpSave(getItem);
                    }
                });
            }
            datatables.initSAdjItemsDatatable(itemTmpSave);
        } else {
            Swal.fire({
                title: "Missing Required Fields!",
                text: "Please fill in all fields. Some required fields are empty.",
                icon: "warning",
            });
        }
        isSelectedEdited = true;
    });

    $(document).on("click", ".itemUpdateIcon", async function () {
        console.log('itemupdate');
        const row = $(this).closest("tr");
        const itemStockCode = row.find("td:first").text().trim();
        SAdjitemsModal.enable(true);

        $("#CSQuantity").val("");
        $("#IBQuantity").val("");
        $("#PCQuantity").val("");

        SAdjitemsModal.show();

        const select = document.querySelector("#StockCode");

        // Set value programmatically
        select.setValue(itemStockCode);

        // Manually trigger the `afterClose` event
        const event = new CustomEvent("afterClose");
        select.dispatchEvent(event);
    });

    $("#itemCloseBtn").on("click", function () {
        let valid = false;
        const data = SAdjitemsModal.getData();

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
                    SAdjitemsModal.hide();
                }
            });
        }
    });

    $("#saveSAdjBtn").on("click", function () {
        if (SAdjModal.isValid()) {
            if (itemTmpSave.length < 1) {
                Swal.fire({
                    title: "No items",
                    text: "Please review your order. No items have been added for Stock Adjustment.",
                    icon: "error",
                });
            } else {
                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you want to adjust this stocks?",
                    icon: "question",
                    showDenyButton: true,
                    confirmButtonText: "Yes, adjust",
                    denyButtonText: `Cancel`,
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        SAdjModal.SAdjSave();
                        SAdjModal.hide();
                        Swal.fire({
                            text: "Please wait... Adjusting Stocks...",
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
    loadInvAdjData: async () => {
        const invData = await ajax('api/inv/adjust/stocks', 'GET', null, (response) => {
            jsonArr = response.data;
            datatables.initInvAdjDatatable(response);
        }, (xhr, status, error) => {
            console.error('Error:', error);
        });
    },

    initInvAdjDatatable: (response) => {
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
            } else {
                MainTH = $('#adjTable').DataTable({
                    data: response.data,
                    language: {
                        searchPlaceholder: "Search here..."
                    },
                    columns: [
                        {
                            data: 'ENTRY_DATE',
                            title: 'Adjustment Date',
                            render: function (data, type, row) {
                                if (!data) return '';

                                const dateObj = new Date(data);

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
                        { data: 'REFERENCE',  title: 'Adjustment Reference' },
                        {
                            data: null,
                            title: 'StockCode',
                            render: function(data, type, row){
                                if (!data) return '';

                                return `<strong>${row.STOCKCODE}</strong><br><small>${data.productdetails.Description}</small>`;
                            }
                        },
                        { data: 'WAREHOUSE',  title: 'Warehouse' },
                        { data: 'PREV_QTY',  title: 'Previous Qty' },
                        { data: 'NEW_QTY',  title: 'New Qty' },
                        { data: 'ADJUSTED_QTY',  title: 'Adjusted Qty' },
                        { data: 'ADJUSTMENT_TYPE',  title: 'Adjustment Type' },
                        { data: 'HANDLED_BY',  title: 'Adjusted By' },
                        // { data: 'TrnQty',  title: 'Qty in PCS' },
                    ],
                    columnDefs: [
                        { className: "text-start", targets: [ 0, 1, 2, 7, 8 ] },
                        { className: "text-center", targets: [ 3, 4, 5, 6 ] },
                        // { className: "text-end", targets: [ 4 ] },
                        { className: "text-nowrap", targets: '_all' } // This targets all columns
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    "createdRow": function (row, data) {
                        $(row).attr('id', data.REFERENCE+'-'+data.STOCKCODE);
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
                        dtlayoutTE.prepend('<div id="filterTransfer" name="filter" style="width: 150px" class="bg-white p-0 mx-1">Filter</div>');
                        $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                        $('.loadingScreen').remove();
                        $('#dattableDiv').removeClass('opacity-0');

                        const tableDiv = $('.dt-layout-row').first();
                        tableDiv.after('<div style="background: linear-gradient(to right, #1b438f, #33336F ); color: #FFF; margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px">Stock Adjustment Report</p></div>');
                    }
                });
            }
        }
    },

    loadItems: async (SONumber) => {
        const stItems = await ajax('api/orders/po-items/search-items/' + SONumber, 'GET', null, (response) => { // Success callback
            ajaxItemsData = response.data;
            datatables.initSAdjItemsDatatable(ajaxItemsData);

        }, (xhr, status, error) => { // Error callback
            console.error('Error:', error);
        });


    },

    initSAdjItemsDatatable: (datas) => {
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
                            return parseFloat(data).toFixed(2);
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
            ele: '#filterTransfer',                   // Attach to the element
            options: [
                // { label: "Adjustment", value: 0 },
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

        $("#filterTransfer").on("change", async function () {
            if (this.value) {
                var filteredData = { data:[], success: true };
                var filterValues = this.value;
                if(filterValues.length == 0){
                    filteredData.data = jsonArr;
                } else{
                    filteredData.data = jsonArr.filter(item => {
                        if (filterValues.includes("1") && item.NewWarehouse == " ") {
                            return true;
                        } else if (filterValues.includes("0") && item.NewWarehouse != " ") {
                            return true;
                        }
                        return false;
                    });
                }
                datatables.initInvTransferDatatable(filteredData);

            }
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
                        $("#Decription").val(findProduct.productdetails.Description);
                        var qtyExtension = (findProduct.QtyOnHand > 1)? " pcs." : " pc.";
                        $("#actualStock").val(findProduct.QtyOnHand + qtyExtension);

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

                        const uomMap = {
                            CS: 'inCS',
                            IB: 'inIB',
                            PC: 'inPC'
                        };

                        uoms.forEach((item) => {
                            const uomKey = uomMap[item.value];
                            if (!uomKey) return; // skip if not mapped

                            const $div = $(`#${item.value}Div`);
                            const $qty = $(`#${item.value}Quantity`);

                            $div.removeClass('d-none');
                            $qty.attr('placeholder', findProduct.runningBal[uomKey]);
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
                            SAdjitemsModal.itemEditMode(uoms, isAlreadyExist);
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
                additionalToggleButtonClasses: 'rounded ModalFieldCustomVS',
            });
            // datatables.initInvTransferDatatable(response);
        }, (xhr, status, error) => {
            console.error('Error:', error);
        });

        $("#VSWarehouse").on("afterClose", async function () {
            $(`#VSWarehouse div .vscomp-toggle-button`).removeClass('virtual-select-invalid');
            if (this.value) {
                if(itemTmpSave.length > 0){
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You want to change origin warehouse? Unsaved data will be erased.",
                        icon: "question",
                        showDenyButton: true,
                        confirmButtonText: "Yes, Close",
                        denyButtonText: `Cancel`,
                    }).then(async (result) => {
                        console.log(result);
                        if (result.isConfirmed) {
                            previousVSWarehouseValue = this.value;
                            itemTmpSave = [];
                            datatables.initSAdjItemsDatatable(itemTmpSave);

                            filteredWH = availWH.filter((item) => { return item.Warehouse != this.value});
                            warehouseInv = null;
                            await initVS.bigDataVS();
                        }
                    });
                } else{
                    previousVSWarehouseValue = this.value;
                    filteredWH = availWH.filter((item) => { return item.Warehouse != this.value});
                    warehouseInv = null;
                    await initVS.bigDataVS();
                }
            } else{
                warehouseInv = null;
            }
        });

        $("#VSWarehouse").on("focus", function() {
            previousVSWarehouseValue = this.value;
        });

        $("#VSWarehouse").on("reset", function () {
            warehouseInv = null;
        });
    },
    adjTypeVS: async () => {
        VirtualSelect.init({
            ele: '#VSAdjType',                   // Attach to the element
            options: [
                { label: "Cycle Count", value: "Cycle Count" },
                { label: "Damage", value: "Damage" },
                { label: "Loss/Theft", value: "Loss/Theft" },
                { label: "Manual", value: "Manual" },
                { label: "Reclassification", value: "Reclassification" },
                { label: "Return to Stock", value: "Return to Stock" },
                { label: "Stock Take", value: "Stock Take" },
                { label: "Write-Off", value: "Write-Off" },
            ],
            multiple: false,
            hideClearButton: true,
            search: false,
            maxWidth: '100%',
            additionalClasses: 'rounded',
            additionalDropboxClasses: 'rounded',
            additionalDropboxContainerClasses: 'rounded',
            additionalToggleButtonClasses: 'rounded ModalFieldCustomVS',
        });
    },
}

const SAdjModal = {
    isValid: () => {
        var validVSWarehouse = validateVirtualSelect('VSWarehouse');
        var validVSAdjType = validateVirtualSelect('VSAdjType');

        if (validVSWarehouse && validVSAdjType) {
            return true;
        } else {
            return false;
        }
    },
    show: () => {
        $('#stockAdjMainModal').modal('show');
    },
    hide: () => {
        $('#stockAdjMainModal').modal('hide');
    },
    clear: () => {
        if (document.querySelector('#VSWarehouse')?.virtualSelect) {
            document.querySelector('#VSWarehouse').reset();
        }
        if (document.querySelector('#VSAdjType')?.virtualSelect) {
            document.querySelector('#VSAdjType').reset();
        }
    },
    enable: (enable) => {
        $('#modalFields input[type="text"]').prop('disabled', !enable);
        $('#modalFields input[type="number"]').prop('disabled', !enable);
        $('#modalFields input[type="date"]').prop('disabled', !enable);
        $('#modalFields textarea').prop('disabled', !enable);
        $('#modalFields #REFERENCE').prop('disabled', true);
        $('#modalFields #ENTRY_DATE').prop('disabled', true);
        $('#addItems').prop('disabled', !enable);
    },
    getData: () => {
        return {
            Warehouse: $("#VSWarehouse").val().trim(),
            Type: $("#VSAdjType").val().trim(),
        };
    },
    SAdjSave: async () => {
        let SAdjData = SAdjModal.getData();
        SAdjData.Items = itemTmpSave.map((item, index) => ({
          ...item,
          PRD_INDEX: index + 1,
        }));

        SAdjData.LastOperator = userObject.name;
        console.log("SAdjData:", SAdjData);
        await ajax("api/inv/warehouse-movement-adjust", "POST", JSON.stringify({ data: SAdjData }),
            (response) => {
                if (response.success) {
                    datatables.loadInvAdjData();
                    SAdjModal.hide();
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

const SAdjitemsModal = {
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
        getItem = SAdjitemsModal.itemCalculateUOM(getItem);
        itemTmpSave.unshift(getItem);
        datatables.initSAdjItemsDatatable(itemTmpSave);
        SAdjitemsModal.hide();
    },
    itemTmpUpdate: (editedItem) => {
        // Optionally, if you want to reflect the change in currentItems
        editedItem = SAdjitemsModal.itemCalculateUOM(editedItem);
        itemTmpSave = itemTmpSave.map((item) =>
            item.StockCode === selectedItem.StockCode
            ? { ...item, ...editedItem }
            : item
        );

        datatables.initSAdjItemsDatatable(itemTmpSave);
        SAdjitemsModal.hide();
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
            isAlreadyExist.UomAndQuantity = SAdjitemsModal.reverseItemCalculateUOM(
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
        const getUOM = SAdjitemsModal.getUOM();
        var actualStockQty = ($("#actualStock").val()).split(" ")[0];

        return {
            StockCode: $("#StockCode").val().trim(),
            Description: $("#Decription").val(),
            OrderUom: getUOM,
            UomAndQuantity: getUOM,
            OrderQty: $("#Quantity").val(),
            ActualQty: parseInt(actualStockQty)
        };

    },
    itemCalculateUOM: (getItem) => {
        const uomsAndQty = getItem.UomAndQuantity;
        const ConvFactAltUom = productConFact.ConvFactAltUom;
        const ConvFactOthUom = productConFact.ConvFactOthUom;

        const totalInPieces = SAdjitemsModal.getTotalQuantity(uomsAndQty);
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
          (item) => item.StockCode != skuCode.text()
        );

        datatables.initSAdjItemsDatatable(itemTmpSave);
    },
}

function invStockChecker(item) {
    var invStockList = warehouseInv.data;

    for (const value of invStockList) {
        if (value.StockCode === item.StockCode) {
            if (parseInt(value.QtyOnHand) >= parseInt(item.TrnQty)) {
                return true;
            } else {
                return false;
            }
        }
    }

    return false;
}

function validateVirtualSelect(id) {
    var value = $('#' + id).val();

    if (value == "") {
        $(`#${id} div .vscomp-toggle-button`).addClass('virtual-select-invalid');
        return false;
    } else {
        $(`#${id} div .vscomp-toggle-button`).removeClass('virtual-select-invalid');
        return true;
    }
}
