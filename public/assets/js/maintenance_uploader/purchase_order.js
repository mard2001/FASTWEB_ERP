    


var removeHeader = ['id', 'lastupdated', 'invstat', 'syncstat', 'dates_tamp', 'time_stamp'];
var filename = 'purchase_order_maintenance_template.csv';
var itemDataTableHolder;
var itemSelectedData, itemAjaxData;
var vandordata, selectedVendor;

var itemList = [];
var tmpItemList = [];

const elementAndColumn = [{
    element: "#StockCode",
    autoComplete: [{
        element: "#PricePerUnit",
        column: "priceWithVat"
    }, {
        element: "#UOMDropDown",
        column: ["StockUom", "AlternateUom", "OtherUom"]
    }, {
        element: "#Decription",
        column: "Description"
    }]
}];

$(document).on('click', '#addBtn', function () {

    if (document.querySelector('#vendorName')?.virtualSelect) {
        document.querySelector('#vendorName').reset();

    }

    if (document.querySelector('#shippedToName')?.virtualSelect) {
        document.querySelector('#shippedToName').reset();

    }



});


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


$(document).on('click', '#CustomerNoDataFoundBtn', function () {
    $('#newVendorModal').modal('show');

});


$('#StockCode').on('reset', function () {
    document.querySelector('#UOMDropDown').reset();
    document.querySelector("#UOMDropDown").disable();
    $('#Decription').val('');
    $('#PricePerUnit').val('');
    $('#Quantity').val('');
    $('#TotalPrice').val('');

});



function autoFillData(element, data) {

    itemSelectedData = data;

    if (Array.isArray(element.column)) {

        // Create a new object with the existing properties and the new column
        var newData = element.column.map(item => {
            return {
                value: data[item], // Spread the existing properties
                label: data[item], // Copy the value from sourceKey to targetKey
            };
        });

        // Remove duplicates from `newData` based on the `value` property
        newData = newData.filter((item, index, self) =>
            index === self.findIndex(other => other.value === item.value)
        );

        // Check if the VirtualSelect instance exists before destroying
        if (document.querySelector(element.element)?.virtualSelect) {
            document.querySelector(element.element).destroy();
        }

        // Initialize VirtualSelect
        VirtualSelect.init({
            ele: element.element,             // Attach to the element
            options: newData,                 // Provide options
            maxWidth: '100%',                 // Set maxWidth
            multiple: false,                   // Enable multiselect
            hideClearButton: true,            // Hide clear button
            selectedValue: [newData[0]?.value], // Preselect (must match `value`)
        });

        document.querySelector(element.element).enable();

        //custom behavior function
    } else if (element.column == 'priceWithVat') {

        var retrievedUser = JSON.parse(localStorage.getItem('dbcon'));

        $.ajax({
            url: globalApi + 'api/getProductPrice',
            type: 'GET',
            data: {
                conn: retrievedUser,
                stockCode: data.StockCode,
                priceCode: selectedVendor.PriceCode

            },

            success: async function (response) {
                if (response.status_response == '1') {
                    $('#PricePerUnit').val(response.response.UNITPRICE);

                } else if (response.status_response == '2') {
                    //no data found generate random prices
                    const randomPrice = Math.floor(Math.random() * 300) + 1;
                    $(element.element).val(randomPrice);
                } else {
                    //error
                    console.log(response);

                }

                $('#TotalPrice').val((+$('#Quantity').val() || 0) * (+$('#PricePerUnit').val() || 0).toFixed(2));
            },
            error: async function (xhr, status, error) {

                console.log(xhr, status, error)

                return xhr, status, error;
            }
        });


        let totalPrice = (+$('#Quantity').val() || 0) * (+$('#PricePerUnit').val() || 0);

        // Disable the button if totalPrice is 0 or negative, enable if positive
        $('#itemSaveEdit').prop('disabled', totalPrice <= 0);
        $('#TotalPrice').val(totalPrice.toFixed(2));





    } else {
        $(element.element).val(data[element.column]);


    }







}


$(document).off('input', '#PricePerUnit');

$(document).ready(async function () {

    $('#PODate').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        minYear: 1950,
        maxYear: parseInt(moment().format('YYYY'), 10),
        maxDate: moment(),
        autoApply: true,
        locale: {
            format: 'YYYY-MM-DD' // Set the desired date format
        }
    });

    $('#EDDate').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        minYear: 1950,
        maxYear: parseInt(moment().format('YYYY'), 10),
        autoApply: true,
        locale: {
            format: 'YYYY-MM-DD' // Set the desired date format
        }
    });

    $('#Quantity').on('input', function () {

        let totalPrice = (+$('#Quantity').val() || 0) * (+$('#PricePerUnit').val() || 0);

        // Disable the button if totalPrice is 0 or negative, enable if positive
        $('#itemSaveEdit').prop('disabled', totalPrice <= 0);
        $('#TotalPrice').val(totalPrice.toFixed(2));

    });


    loadAutoSuggest(elementAndColumn);

    loadVendors();
    loadSupplierShipTo();
});

async function vendorAPI(method, data) {
    var retrievedUser = JSON.parse(localStorage.getItem('dbcon'));

    if (method == 'GET') {
        data = { conn: retrievedUser };

    } else if (method == 'POST') {
        data.conn = retrievedUser

    }

    return await $.ajax({
        url: globalApi + 'api/vendors',
        type: method,
        data: data,

        success: async function (response) {

            return response;

        },
        error: async function (xhr, status, error) {
            Swal.fire({
                icon: "error",
                title: "Api Error",
                text: xhr.responseJSON?.message || xhr.statusText,
            });

            console.log(xhr, status, error)

            return xhr, status, error;
        }
    });
}


async function loadVendors() {

    var vendorResponse = await vendorAPI('GET');

    console.log(vendorResponse);

    if (vendorResponse.status_response == 1) {
        vandordata = vendorResponse.response;

        const newData = vandordata.map(item => {
            // Create a new object with the existing properties and the new column
            return {
                description: item.CompleteAddress,
                value: item.cID, // Spread the existing properties
                label: item.SupplierName, // Copy the value from sourceKey to targetKey
            };
        });

        // Check if the VirtualSelect instance exists before destroying
        if (document.querySelector('#vendorName')?.virtualSelect) {
            document.querySelector('#vendorName').destroy();
        }

        VirtualSelect.init({
            ele: '#vendorName',
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
                                                <button id="CustomerNoDataFoundBtn" type="button" class="btn btn-primary btn-sm">Add new</button>
                                            </div>
                                        </div>`,

        });

        $('#vendorName').on('afterClose', function () {
            if (this.value) {
                var findVendor = vandordata.find(item => item.cID == this.value);

                $('#contactName').val(findVendor.ContactPerson);
                $('#vendorAddress').val(findVendor.CompleteAddress);

                var mobileContact = findVendor.ContactNo = /^9\d{9}$/.test(findVendor.ContactNo) ? findVendor.ContactNo.replace(/^9/, "09") : findVendor.ContactNo;

                selectedVendor = findVendor;

                $('#vendorPhone').val(mobileContact);


                if (this.value && $('#shippedToName').value) {
                    $('#addItems').prop("disabled", false);
                }

                $('#shippingTerms').val(findVendor.TermsCode);

            }

        });

        $('#vendorName').on('reset', function () {
            $('#contactName').val('');
            $('#vendorAddress').val('');
            $('#vendorPhone').val('');
        });
    }




}

async function loadSupplierShipTo() {

    var retrievedUser = JSON.parse(localStorage.getItem('dbcon'));

    await $.ajax({
        url: globalApi + 'api/supplier-shipped-to',
        type: 'GET',
        data: { conn: retrievedUser },

        success: async function (response) {
            if (response.status_response != 1) {
                console.log(JSON.stringify(response, null, 2));
            }

            const newData = response.response.map(item => {
                // Create a new object with the existing properties and the new column
                return {
                    description: item.CompleteAddress,
                    value: item.cID, // Spread the existing properties
                    label: item.SupplierCode, // Copy the value from sourceKey to targetKey
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
                    var findSupplier = response.response.find(item => item.cID == this.value);

                    $('#SupplierContactName').val(findSupplier.ContactPerson);
                    $('#shippedToAddress').val(findSupplier.CompleteAddress);

                    var mobileContact = findSupplier.ContactNo = /^9\d{9}$/.test(findSupplier.ContactNo) ? findSupplier.ContactNo.replace(/^9/, "09") : findSupplier.ContactNo;

                    $('#shippedToPhone').val(mobileContact);

                    if (this.value && document.querySelector('#vendorName').value) {
                        $('#addItems').prop("disabled", false);
                    }
                }

            });


            $('#shippedToName').on('reset', function () {
                $('#SupplierContactName').val('');
                $('#shippedToAddress').val('');
                $('#shippedToPhone').val('');
            });




            return response;

        },
        error: async function (xhr, status, error) {
            Swal.fire({
                icon: "error",
                title: "Api Error",
                text: xhr.responseJSON?.message || xhr.statusText,
            });

            console.log(xhr, status, error)

            return xhr, status, error;
        }
    });

}





$(document).on('click', '#addBtn', function () {

    if (itemDataTableHolder) {
        itemDataTableHolder.clear().draw();

    } else {

        //dummy data
        showItems({
            status_response: 1,
            response: null
        });


    }

    tmpItemList = [];

});

$('#totalQuantityInUOM, #discountPerUnit, #pricePerUnit').on('input', function () {
    calculatePrices();
});

function calculatePrices() {
    var pricePerUnit = parseFloat($('#pricePerUnit').val()) || 0;
    var discountPerUnit = parseFloat($('#discountPerUnit').val()) || 0;
    var totalQuantityInUOM = parseFloat($('#totalQuantityInUOM').val()) || 0;

    var netPricePerUnit = $('#netPricePerUnit');
    var totalDiscountPerUnit = $('#totalDiscountPerUnit');
    var totalPrice = $('#totalPrice');
    var totalNetPrice = $('#totalNetPrice');

    // Calculate net price per unit
    var netPrice = (pricePerUnit - discountPerUnit).toFixed(2);
    netPricePerUnit.val(netPrice);

    // Calculate total discount per unit
    var totalDiscount = (discountPerUnit * totalQuantityInUOM).toFixed(2);
    totalDiscountPerUnit.val(totalDiscount);

    // Calculate total price
    var totalPriceValue = (pricePerUnit * totalQuantityInUOM).toFixed(2);
    totalPrice.val(totalPriceValue);

    // Calculate total net price
    var totalNetPriceValue = (parseFloat(totalPriceValue) - parseFloat(totalDiscount)).toFixed(2);
    totalNetPrice.val(totalNetPriceValue);

}

$(document).on('click', '#itemSaveEdit', function () {
    event.preventDefault();

    let validateSaveItems = false;
    let validateUpdateItem = false;

    if ($("#itemModalFields").valid() && itemDataTableHolder?.column(1).data().toArray().length > 0) {
        const stockCode = $('#StockCode').val();
        const itemTableData = itemDataTableHolder.column(1).data().toArray();
        const findMatchSKU = itemTableData.find(sku => sku == stockCode); // true
        validateSaveItems = findMatchSKU ? true : false;
        validateUpdateItem = findMatchSKU && findMatchSKU != itemSelectedData.StockCode; // Allow update if item exists and is different
    }


    if ($(this).text().toLowerCase() == 'save changes') {
        if (validateUpdateItem) {
            Swal.fire({
                title: "Item exist",
                text: "Item exist already in the order list cannot saved.",
                icon: "warning"
            });
        } else {
            Swal.fire({
                title: "Do you want to save the changes?",
                showDenyButton: true,
                confirmButtonText: "Yes, save",
                denyButtonText: `Cancel`
            }).then(async (result) => {
                if (result.isConfirmed) {

                    //chache local then insert into database
                    if ($('#saveEdit').text().toLowerCase() == 'save') {

                        var data = getItemFieldData();
                        data.id = itemSelectedData.id;

                        tmpItemList = tmpItemList.map(item => {
                            if (item.id === data.id) {
                                return data;
                            }

                            return item;
                        });

                        showItems({
                            status_response: 1,
                            response: tmpItemList
                        });

                        //0 as of now idk these details
                        const taxCost = 0;
                        const shippingCost = 0;
                        const othersCost = 0;

                        const grandTotal = tmpItemList.reduce((sum, item) => sum + parseInt(item.TotalPrice), 0);

                        $('#taxCost').val(taxCost);
                        $('#shippingCost').val(shippingCost);
                        $('#othersCost').val(othersCost);
                        $('#grandTotal').val(grandTotal);
                        $('#subTotal').val(grandTotal);

                        $('#itemModal').modal('hide');
                    }

                    //directly change data into database api
                    else {
                        var data = getItemFieldData();
                        data.id = itemSelectedData.id;

                        var response = await apiGetItems(2, data);

                        if (response.status_response == 1) {
                            showItems();
                            $('#itemModal').modal('hide');

                            Swal.fire({
                                title: "Success!",
                                text: response.response,
                                icon: "success"
                            });
                        }
                    }
                }
            });
        }

    }
    else if ($(this).text().toLowerCase() == 'save') {
        if (validateSaveItems) {
            Swal.fire({
                title: "Item exist",
                text: "Item exist already in the order list cannot saved.",
                icon: "warning"
            });
        } else {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to add this data?',
                icon: 'question',
                showDenyButton: true, // Show the deny (cancel) button
                confirmButtonText: 'Yes, Add', // Text for the confirm button
                denyButtonText: 'Cancel', // Text for the deny button
            }).then(async (result) => {
                if (result.isConfirmed) {

                    //cache local then insert into database
                    if ($('#saveEdit').text().toLowerCase() == 'save') {

                        var data = getItemFieldData();
                        tmpItemList.push(data);
                        data.id = tmpItemList.length - 1;

                        //dummy data
                        showItems({
                            status_response: 1,
                            response: tmpItemList
                        });

                        //0 as of now idk these details
                        const taxCost = 0;
                        const shippingCost = 0;
                        const othersCost = 0;
                        const grandTotal = tmpItemList.reduce((sum, item) => sum + parseInt(item.TotalPrice), 0);

                        $('#taxCost').val(taxCost);
                        $('#shippingCost').val(shippingCost);
                        $('#othersCost').val(othersCost);
                        $('#grandTotal').val(grandTotal);
                        $('#subTotal').val(grandTotal);

                        $('#itemModal').modal('hide');

                    }

                    //directly change data into database api
                    else {
                        var data = getItemFieldData();

                        if (!data.PONumber) {
                            Swal.fire({
                                title: "Warning!",
                                text: "Missing PO Number",
                                icon: "warning"
                            });

                            return;
                        }

                        data.PRD_INDEX = itemList.length + 1;

                        var response = await apiGetItems(1, [data]);

                        if (response.status_response == 1) {
                            showItems();
                            $('#itemModal').modal('hide');

                            Swal.fire({
                                title: "Success!",
                                text: response.response,
                                icon: "success"
                            });
                        }
                    }
                }
            });
        }

    }
    else {

        //make the details editable
        $(this).text('Save changes').removeClass('btn-info').addClass('btn-primary');

        $('#itemModalFields input[type="text"]').prop('disabled', false);
        $('#itemModalFields input[type="number"]').prop('disabled', false);

        elementAndColumn.forEach(element => {
            document.querySelector(element.element).enable();
        });

        if (document.querySelector('#UOMDropDown')?.virtualSelect) {
            document.querySelector('#UOMDropDown').enable();
        }

    }
});


$(".form-check-input").on("click", function () {

    console.log('test');

});

$("#addItems").on("click", function () {
    event.preventDefault();

    elementAndColumn.forEach(element => {
        document.querySelector(element.element).enable();
        document.querySelector(element.element).setValue('');
    });

    if (document.querySelector('#UOMDropDown')?.virtualSelect) {
        document.querySelector('#UOMDropDown').destroy();
        $('#UOMDropDown').html(`<input disabled type="number" class="form-control bg-white border-0"
                                            required placeholder="UOM" style="border-radius: 0;" readonly>`);
    }

    $('#itemModal').modal('show');

    $('#itemModalFields input[type="text"]').val('');
    $('#itemModalFields input[type="number"]').val('');

    $('#itemModalFields input[type="text"]').prop('disabled', false);
    $('#itemModalFields input[type="number"]').prop('disabled', false);

    $('#itemSaveEdit').text('Save').addClass('btn-primary').removeClass('btn-info');

    $('#itemSaveEdit').prop('disabled', true);


    // selectedData = null;

});


$("#itemDelete").on("click", function () {
    event.preventDefault();

    Swal.fire({
        title: "Are you sure?",
        text: "Are you sure you want to delete all selected data? You won't be able to revert this!", // Use <br> for line breaks
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then(async (result) => {
        if (result.isConfirmed) {

            var totalItemDeleted = 0;

            const promises = [];


            itemDataTableHolder.rows().every(async function () {
                var row = this.node();
                var checkbox = $(row).find('input.row-checkbox');

                if (checkbox.is(':checked')) {
                    // Push the promise into the array
                    promises.push(
                        (async () => {

                            //chache local then insert into database
                            if ($('#saveEdit').text().toLowerCase() == 'save') {
                                tmpItemList = tmpItemList.filter(item => item.id != row.id);
                            }

                            //directly change data into database api
                            else {
                                var result = await apiGetItems(3, row);
                                if (result.status_response == '1') {
                                    totalItemDeleted++;
                                }
                            }

                        })()
                    );
                }

            });

            await Promise.all(promises);

            // Show success message after deletion
            Swal.fire({
                title: "Deleted!",
                text: `Total ${totalItemDeleted} items have been deleted.`,
                icon: "success",
                confirmButtonColor: "#3085d6"
            });

            $(this).prop('disabled', true);

            //chache local then insert into database
            if ($('#saveEdit').text().toLowerCase() == 'save') {
                showItems({
                    status_response: 1,
                    response: tmpItemList
                });

            }

            else {
                showItems();

            }

            // Show success message after deletion
            Swal.fire({
                title: "Deleted!",
                text: `Total ${totalItemDeleted} items have been deleted.`,
                icon: "success",
                confirmButtonColor: "#3085d6"
            });

            $(this).prop('disabled', true);


        }
    });

});

$("#itemTables").on("click", "tbody tr", function (event) {

    // Find the checkbox inside the row
    var checkbox = $(this).find('.form-check-input');

    // Check if the clicked element is the checkbox itself
    if (!$(event.target).is(checkbox)) {
        // Toggle the checkbox state

        if (itemDataTableHolder && itemDataTableHolder.data().any()) {

            var data = $('#saveEdit').text().toLowerCase() == 'save' ? tmpItemList : itemAjaxData
            itemSelectedData = data.find(item => item.id == $(this).attr('id'));

            $('#itemModal').modal('show');
            $('#itemSaveEdit').text('Edit details').addClass('btn-info').removeClass('btn-primary');

            $('#itemModalFields input[type="text"]').prop('disabled', true);
            $('#itemModalFields input[type="number"]').prop('disabled', true);

            elementAndColumn.forEach(element => {
                document.querySelector(element.element).disable();
            });

            if (document.querySelector('#UOMDropDown')?.virtualSelect) {
                document.querySelector('#UOMDropDown').disable();
            }

            itemModalFillField();

            $('#PricePerUnit').val(itemSelectedData.PricePerUnit);
        }


    } else {
        // checkbox.prop('checked', !checkbox.prop('checked'));
        // Toggle the row's selected class
        $(this).toggleClass('rowSelected');

        let isAnyChecked = false;

        // Loop through each row in the DataTable
        itemDataTableHolder.rows().every(function () {
            var row = this.node();
            var checkbox = $(row).find('input.row-checkbox');

            if (checkbox.is(':checked')) {
                isAnyChecked = true; // At least one checkbox is checked
                return false; // Exit the loop early since we found a checked checkbox
            }
        });

        $('#itemDelete').prop('disabled', !isAnyChecked);


    }


});

async function save() {

    var data = getFieldData();

    data.Items = tmpItemList.map((item, index) => ({
        ...item,        // ✅ Spread all properties from item
        PRD_INDEX: index // ✅ Add the new index property
    }));

    return await apiCommunicationDbChanges(1, data);
}

async function update() {

    var data = getFieldData();
    data.id = selectedData.id;
    return await apiCommunicationDbChanges(2, data);
}

function modalFillField() {

    $('#OrderNumber').val(selectedData.OrderNumber);
    $('#PONumber').val(selectedData.PONumber);
    $('#PODate').val(selectedData.PODate);
    $('#POAccount').val(selectedData.POAccount);
    $('#productType').val(selectedData.productType);
    $('#orderPlacer').val(selectedData.orderPlacer);
    $('#orderPlacerEmail').val(selectedData.orderPlacerEmail);
    $('#deliveryAddress').val(selectedData.deliveryAddress);
    $('#deliveryMethod').val(selectedData.deliveryMethod);
    $('#totalNetVol').val(selectedData.totalNetVol);
    $('#totalNetWeight').val(selectedData.totalNetWeight);
    $('#totalGrossWeight').val(selectedData.totalGrossWeight);
    $('#subTotal').val(selectedData.subTotal);
    $('#totalDiscount').val(selectedData.totalDiscount);
    $('#totalTax').val(selectedData.totalTax);
    $('#totalCost').val(selectedData.totalCost);
    $('#usedCurrency').val(selectedData.usedCurrency);

    showItems();


}

function itemModalFillField() {

    $('#Decription').val(itemSelectedData.Decription);
    $('#Quantity').val(itemSelectedData.Quantity);
    $('#UOM').val(itemSelectedData.UOM);
    $('#ItemVolume').val(itemSelectedData.ItemVolume);
    $('#ItemWeight').val(itemSelectedData.ItemWeight);
    $('#TotalPrice').val(itemSelectedData.TotalPrice);
    $('#PricePerUnit').val(itemSelectedData.PricePerUnit);
    document.querySelector('#StockCode').setValue(itemSelectedData.StockCode)

}

async function getAllXmlData() {
    var respond = await apiCommunicationDbChanges(5);

    if (respond.status_response == 1) {
        if (dataTableHolder) {
            dataTableHolder.clear().draw();
            dataTableHolder.rows.add(respond.response).draw();
        } else {
            dataTableHolder = $('#getXmlData').DataTable({
                data: respond.response,
                //search bar right side
                // "dom": '<"top"f>rt<"bottom"ilp><"clear">',
                layout: {
                    topStart: function () {
                        return $(customActionButton);
                    }
                },
                columns: [
                    { data: 'OrderNumber' },
                    { data: 'PONumber' },
                    { data: 'POAccount' },
                    { data: 'PODate' },
                    { data: 'orderPlacer' },
                    { data: 'totalDiscount' },
                    { data: 'totalCost' },
                    { data: 'FileName' },
                    // { data: 'DateUploaded' },   

                ],
                scrollCollapse: true,
                scrollY: '100%',
                scrollX: '100%',
                "createdRow": function (row, data) {
                    $(row).attr('id', data.id);
                },

                "pageLength": 8,
                "lengthChange": false,

                initComplete: function () {
                    $(this.api().table().container()).find('#dt-search-0').addClass('p-1 mx-0 dtsearchInput nofocus');
                    $(this.api().table().container()).find('.dt-search label').addClass('py-1 px-3 mx-0 dtsearchLabel');
                    $(this.api().table().container()).find('.dt-layout-row').addClass('px-4');
                    $(this.api().table().container()).find('.dt-layout-table').removeClass('px-4');
                    $(this.api().table().container()).find('.dt-scroll-body').addClass('rmvBorder');
                    $(this.api().table().container()).find('.dt-layout-table').addClass('btmdtborder');

                    // Select the label element and replace it with a div
                    $('.dt-search label').replaceWith(function () {
                        return $('<div>', {
                            html: $(this).html(),
                            id: $(this).attr('id'),
                            class: $(this).attr('class')
                        });
                    });

                    $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                    // dataTableHolder.draw();
                }

            });

        }

        if (respond.response.length > 0) {
            ajaxData = respond.response;

        }

        return respond.status_response == 1 ? true : false;
    }
}

// Set up a MutationObserver to watch for changes in the container's visibility
const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
        if (mutation.attributeName === 'style') {
            const isVisible = $('#editXmlDataModal').is(':visible');
            if (isVisible && itemDataTableHolder) {
                itemDataTableHolder.columns.adjust();
                itemDataTableHolder.draw();

            }
        }
    });
});

// Start observing the container for attribute changes
observer.observe(document.getElementById('editXmlDataModal'), {
    attributes: true // Configure it to listen to attribute changes
});

async function showItems(data = null) {

    var item = data ? data : {
        id: selectedData.PONumber
    };

    var respond = data ? data : await apiGetItems(4, item, true);

    if (respond.status_response == 1) {
        if (itemDataTableHolder) {
            itemDataTableHolder.clear().draw();
            itemDataTableHolder.rows.add(respond.response).draw();
        } else {
            itemDataTableHolder = $('#itemTables').DataTable({
                data: respond.response,
                columns: [
                    {
                        data: null, // Placeholder for checkbox
                        render: function (data, type, row) {
                            return '<div class="form-check d-flex justify-content-center"><input type="checkbox" class="form-check-input row-checkbox cursor-pointer hover:bg-light" data-id="' + row.id + '"></div>';
                        },
                        orderable: false, // Prevent sorting on the checkbox column
                        searchable: false  // Disable search on the checkbox column
                    },
                    { data: 'StockCode' },
                    { data: 'Decription' },
                    { data: 'Quantity' },
                    { data: 'UOM' },
                    { data: 'PricePerUnit' },
                    { data: 'TotalPrice' },

                ],
                searching: false,
                scrollCollapse: true,
                responsive: true, // Enable responsive mode
                autoWidth: true, // Enable auto-width calculation
                scrollY: '100%',
                scrollX: '100%',
                "createdRow": function (row, data) {
                    $(row).attr('id', data.id);
                },
                "lengthChange": false,  // Hides the per page dropdown
                "info": false,          // Hides the bottom text (like "Showing x to y of z entries")
                "paging": false,        // Hides the pagination controls (Next, Previous, etc.)

                initComplete: function () {
                    $(this.api().table().container()).find('#dt-search-0').addClass('p-1 mx-0 dtsearchInput nofocus');
                    $(this.api().table().container()).find('.dt-search label').addClass('py-1 px-3 mx-0 dtsearchLabel');
                    $(this.api().table().container()).find('.dt-layout-row').addClass('px-4');
                    $(this.api().table().container()).find('.dt-layout-table').removeClass('px-4');
                    $(this.api().table().container()).find('.dt-scroll-body').addClass('rmvBorder');
                    $(this.api().table().container()).find('.dt-layout-table').addClass('btmdtborder');

                    // Select the label element and replace it with a div
                    $('.dt-search label').replaceWith(function () {
                        return $('<div>', {
                            html: $(this).html(),
                            id: $(this).attr('id'),
                            class: $(this).attr('class')
                        });
                    });
                    $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                    // itemDataTableHolder.draw();
                    // itemDataTableHolder.columns.adjust();
                }

            });

        }

        if (respond.response && respond.response.length > 0) {
            itemAjaxData = respond.response;
            itemList = itemAjaxData;

        }

        return respond.status_response == 1 ? true : false;
    }
}

async function apiCommunicationDbChanges(method, xmlJson = null) {
    var apiMethod = "";
    var apiId = '';

    var retrievedUser = JSON.parse(localStorage.getItem('dbcon'));

    var apidata = {
        conn: retrievedUser,
        data: xmlJson
    }



    switch (method) {
        case 1: // INSERT DATA
            apiMethod = 'POST';
            apidata = JSON.stringify(apidata);
            break;
        case 2: // UPDATE DATA
            apiId = "/" + xmlJson.id;
            apiMethod = 'POST';
            apidata._method = 'PUT';
            apidata = JSON.stringify(apidata);

            break;
        case 3: // DELETE DATA
            apiId = "/" + xmlJson.id;
            apiMethod = 'POST';
            apidata._method = 'DELETE';

            apidata = JSON.stringify(apidata);

            break;
        case 4: // GET SINGLE DATA VIA ID
            apiId = "/" + xmlJson.id;
            apiMethod = 'GET'
            break;
        case 5: // GET ALL DATA
            apiMethod = 'GET'
            break;
    }

    return await $.ajax({
        url: globalApi + 'api/shell/po' + apiId,
        type: apiMethod,
        Accept: 'application/json',
        contentType: 'application/json',
        data: apidata,

        success: async function (response) {
            if (response.status_response != 1) {
                console.log(JSON.stringify(response, null, 2));

            }

            return response;

        },
        error: async function (xhr, status, error) {
            Swal.fire({
                icon: "error",
                title: "Api Error",
                text: xhr.responseJSON?.message || xhr.statusText,
            });

            console.log(xhr, status, error)

            return xhr, status, error;
        }
    });


}

async function apiGetItems(method, xmlJson = null, searchByPO = false) {
    var apiMethod = "";
    var apiId = '';

    var retrievedUser = JSON.parse(localStorage.getItem('dbcon'));

    var apidata = {
        conn: retrievedUser,
        data: xmlJson
    }

    switch (method) {
        case 1: // INSERT DATA
            apiMethod = 'POST';
            apidata = JSON.stringify(apidata);

            break;
        case 2: // UPDATE DATA
            apiId = "/" + xmlJson.id;

            apiMethod = 'POST';
            apidata._method = 'PUT';
            apidata = JSON.stringify(apidata);

            break;
        case 3: // DELETE DATA
            apiId = "/" + xmlJson.id;
            apiMethod = 'POST';
            apidata._method = 'DELETE';

            apidata = JSON.stringify(apidata);

            break;
        case 4: // GET SINGLE DATA VIA ID
            apiId = (searchByPO ? '/search-items/' : '') + xmlJson.id;
            apiMethod = 'GET'
            break;
        case 5: // GET ALL DATA
            apiMethod = 'GET'
            break;
    }

    return await $.ajax({
        url: globalApi + 'api/shell/po-items' + apiId,
        type: apiMethod,
        Accept: 'application/json',
        contentType: 'application/json',
        data: apidata,

        success: async function (response) {
            if (response.status_response != 1) {
                console.log(JSON.stringify(response, null, 2));
            }

            return response;

        },
        error: async function (xhr, status, error) {
            Swal.fire({
                icon: "error",
                title: "Api Error",
                text: xhr.responseJSON?.message || xhr.statusText,
            });

            console.log(xhr, status, error)

            return xhr, status, error;
        }
    });

}


function insertNewVendor() {

    var newVendor = {
        SupplierCode: $('#SupplierCode').val(),
        SupplierName: $('#SupplierName').val(),
        SupplierType: $('#SupplierType').val(),
        TermsCode: $('#TermsCode').val(),
        ContactPerson: $('#ContactPerson').val(),
        ContactNo: $('#ContactNo').val(),
        CompleteAddress: $('#CompleteAddress').val(),
        Region: $('#Region').val(),
        Province: $('#Province').val(),
        City: $('#City').val(),
        Municipality: $('#Municipality').val(),
        Barangay: $('#Barangay').val(),
    }

    var vendorInsertResult = vendorAPI('POST', newVendor);

    if (vendorInsertResult.status_response == 1) {

        Swal.fire({
            title: "Success!",
            text: response.response,
            icon: "success"
        });
    }

}


function getFieldData() {

    var user = JSON.parse(localStorage.getItem('user'));

    var data = {
        PODate: moment().format('YYYY-MM-DD'),
        SupplierCode: selectedVendor.SupplierCode,
        SupplierName: selectedVendor.SupplierName,
        productType: selectedVendor.SupplierType,
        FOB: $('#fob').val(),
        deliveryAddress: $('#shippedToAddress').val(),
        deliveryMethod: $('#shipVia').val(),
        subTotal: $('#subTotal').val(),
        totalDiscount: 0,
        totalTax: $('#taxCost').val(),
        totalCost: $('#grandTotal').val(),
        SpecialInstruction: $('#poComment').val(),
        EncoderID: $('#requisitioner').val(),
        orderPlacer: user.name,
        orderPlacerEmail: user.email

    }

    return data;
}


function getItemFieldData() {

    var data = {
        StockCode: $('#StockCode').val(),
        Decription: $('#Decription').val(),
        UOM: $('#UOMDropDown').val(),
        Quantity: $('#Quantity').val(),
        PricePerUnit: $('#PricePerUnit').val(),
        TotalPrice: $('#TotalPrice').val(),

    }

    return data;
}






