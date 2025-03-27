

var removeHeader = ['id', 'lastupdated', 'invstat', 'syncstat', 'dates_tamp', 'time_stamp'];
var filename = 'receving_reports_maintenance_template.csv';
var itemDataTableHolder;
var itemSelectedData, itemAjaxData;
var itemList = [];
var tmpItemList = [];

const elementAndColumn = [{
    element: "#SKU",
    autoComplete: [{
        element: "#UnitPrice",
        column: "priceWithVat"
    }, {
        element: "#UOM",
        column: "StockUom"
    }, {
        element: "#Decription",
        column: "Description"
    }]
}];

$(document).ready(async function () {

    const vat = 0.12;

    $(document).on('input', '#Quantity, #UnitPrice', function () {

        if ($('#Quantity').val() && $('#UnitPrice').val()) {
            const netVatPrice = $('#Quantity').val() * $('#UnitPrice').val();
            const vatPrice = netVatPrice * vat;
            const grossPrice = netVatPrice + vatPrice;

            $('#NetVat').val(netVatPrice.toFixed(2));
            $('#Vat').val(vatPrice.toFixed(2));
            $('#Gross').val(grossPrice.toFixed(2));

        } else {

            $('#NetVat').val(0);
            $('#Vat').val(0);
            $('#Gross').val(0);
        }


    });

    loadAutoSuggest(elementAndColumn);
});



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

    if ($(this).text().toLowerCase() == 'save changes') {
        if ($("#itemModalFields").valid()) {
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

        if ($("#itemModalFields").valid()) {
            Swal.fire({
                title: "Confirm add data",
                showDenyButton: true,
                confirmButtonText: "Yes, save",
                denyButtonText: `Cancel`
            }).then(async (result) => {
                if (result.isConfirmed) {

                    //chache local then insert into database
                    if ($('#saveEdit').text().toLowerCase() == 'save') {

                        var data = getItemFieldData();
                        tmpItemList.push(data);
                        data.id = tmpItemList.length - 1;

                        //dummy data
                        showItems({
                            status_response: 1,
                            response: tmpItemList
                        });

                        $('#itemModal').modal('hide');

                    }

                    //directly change data into database api
                    else {
                        var data = getItemFieldData();

                        if (!data.RRNo) {
                            Swal.fire({
                                title: "Warning!",
                                text: "Missing RR Number",
                                icon: "warning"
                            });

                            return;
                        }

                        data.PRD_INDEX = itemList.length + 1;

                        var response = await apiGetItems(1, data);

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
        document.querySelector('#SKU').enable();
    }
});




$(".form-check-input").on("click", function () {

    console.log('test');

});

$("#addItems").on("click", function () {
    event.preventDefault();
    $('#itemModal').modal('show');

    $('#itemModalFields input[type="text"]').val('');
    $('#itemModalFields input[type="number"]').val('');

    $('#itemModalFields input[type="text"]').prop('disabled', false);
    $('#itemModalFields input[type="number"]').prop('disabled', false);
    document.querySelector('#SKU').enable();
    document.querySelector('#SKU').setValue('');

    $('#itemSaveEdit').text('Save').addClass('btn-primary').removeClass('btn-info');

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

            //directly change data into database api
            else {
                showItems();

            }




        }
    });

});

$("#itemTables").on("click", "tbody tr", function (event) {

    // Find the checkbox inside the row
    var checkbox = $(this).find('.form-check-input');

    // Check if the clicked element is the checkbox itself
    if (!$(event.target).is(checkbox)) {
        // Toggle the checkbox state


        var data = $('#saveEdit').text().toLowerCase() == 'save' ? tmpItemList : itemAjaxData
        itemSelectedData = data.find(item => item.id == $(this).attr('id'));


        itemModalFillField();
        $('#itemModal').modal('show');
        $('#itemSaveEdit').text('Edit details').addClass('btn-info').removeClass('btn-primary');
        

        $('#itemModalFields input[type="text"]').prop('disabled', true);
        $('#itemModalFields input[type="number"]').prop('disabled', true);
        document.querySelector('#SKU').disable();



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

    data.Items = tmpItemList.map(({
        id, ...rest
    }, index) => ({
        ...rest, // Spread the remaining properties
        RRNo: $('#RRNo').val(), // Add the new key with its value
        PRD_INDEX: index // Add the index to each item
    }));

    return await apiCommunicationDbChanges(1, data);
}

async function update() {

    var data = getFieldData();
    data.id = selectedData.id;
    return await apiCommunicationDbChanges(2, data);
}

function modalFillField() {

    $('#SupplierCode').val(selectedData.SupplierCode);
    $('#SupplierName').val(selectedData.SupplierName);
    $('#SupplierTIN').val(selectedData.SupplierTIN);
    $('#Address').val(selectedData.Address);
    $('#RRNo').val(selectedData.RRNo);
    $('#Date').val(selectedData.Date);
    $('#Reference').val(selectedData.Reference);
    $('#Status').val(selectedData.Status);
    $('#Total').val(selectedData.Total);
    $('#ApprovedBy').val(selectedData.ApprovedBy);
    $('#PreparedBy').val(selectedData.PreparedBy);
    $('#PrintedBy').val(selectedData.PrintedBy);
    $('#CheckedBy').val(selectedData.CheckedBy);
    $('#DateUploaded').val(selectedData.DateUploaded);
    $('#filename').val(selectedData.FileName);

    showItems();

}

function itemModalFillField() {

    $('#Decription').val(itemSelectedData.Decription);
    $('#Quantity').val(itemSelectedData.Quantity);
    $('#UOM').val(itemSelectedData.UOM);
    $('#WhsCode').val(itemSelectedData.WhsCode);
    $('#UnitPrice').val(itemSelectedData.UnitPrice);
    $('#NetVat').val(itemSelectedData.NetVat);
    $('#Vat').val(itemSelectedData.Vat);
    $('#Gross').val(itemSelectedData.Gross);
    $('#RRNo').val(itemSelectedData.RRNo);
    document.querySelector('#SKU').setValue(itemSelectedData.SKU);
    // $('#SKU').val(itemSelectedData.SKU);


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
                    { data: 'SupplierCode' },
                    { data: 'SupplierTIN' },
                    { data: 'Address' },
                    { data: 'RRNo' },
                    { data: 'Date' },
                    { data: 'Reference' },
                    { data: 'Status' },
                    { data: 'Total' },
                    { data: 'PreparedBy' },
                    { data: 'FileName' },
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

                }

            });

        }

        if (respond.response.length > 0) {
            ajaxData = respond.response;

        }

        return respond.status_response == 1 ? true : false;
    }
}

async function showItems(data = null) {

    var item = data ? data : {
        id: selectedData.RRNo
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
                    { data: 'SKU' },
                    { data: 'Decription' },
                    { data: 'Quantity' },
                    { data: 'UOM' },
                    { data: 'WhsCode' },
                    { data: 'UnitPrice' },
                    { data: 'NetVat' },
                    { data: 'Vat' },
                    { data: 'Gross' },
                    {
                        data: null, // Placeholder for checkbox
                        render: function (data, type, row) {
                            return '<div class="form-check"><input type="checkbox" class="form-check-input row-checkbox cursor-pointer hover:bg-light" data-id="' + row.id + '"></div>';
                        },
                        orderable: false, // Prevent sorting on the checkbox column
                        searchable: false  // Disable search on the checkbox column
                    },

                ],
                searching: false,
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

                }

            });

        }

        if (respond.response.length > 0) {
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
            apiId = "/" + xmlJson.id
            apiMethod = 'POST';
            apidata._method = 'DELETE';

            apidata = JSON.stringify(apidata);

            break;
        case 4: // GET SINGLE DATA VIA ID
            apiId = "/" + xmlJson.id
            apiMethod = 'GET'
            break;
        case 5: // GET ALL DATA
            apiMethod = 'GET'
            break;
    }

    return await $.ajax({
        url: globalApi + 'api/shell/rr' + apiId,
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
            apiId = "/" + xmlJson.id
            apiMethod = 'POST';
            apidata._method = 'DELETE';

            apidata = JSON.stringify(apidata);

            break;
        case 4: // GET SINGLE DATA VIA ID
            apiId = (searchByPO ? '/search-rr/' : '') + xmlJson.id;
            apiMethod = 'GET'
            break;
        case 5: // GET ALL DATA
            apiMethod = 'GET'
            break;
    }

    return await $.ajax({
        url: globalApi + 'api/shell/rr-items' + apiId,
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

function getFieldData() {
    var data = {
        SupplierCode: $('#SupplierCode').val(),
        SupplierName: $('#SupplierName').val(),
        SupplierTIN: $('#SupplierTIN').val(),
        Address: $('#Address').val(),

        RRNo: $('#RRNo').val(),
        Date: $('#Date').val(),
        Reference: $('#Reference').val(),
        Status: $('#Status').val(),

        Total: $('#Total').val(),
        ApprovedBy: $('#ApprovedBy').val(),
        PreparedBy: $('#PreparedBy').val(),
        PrintedBy: $('#PrintedBy').val(),
        CheckedBy: $('#CheckedBy').val(),

        DateUploaded: $('#DateUploaded').val(),
        FileName: $('#filename').val(),

    }

    return data;
}


function getItemFieldData() {
    var data = {
        RRNo: $('#RRNo').val(),
        Gross: $('#Gross').val(),
        Vat: $('#Vat').val(),
        NetVat: $('#NetVat').val(),
        UnitPrice: $('#UnitPrice').val(),
        WhsCode: $('#WhsCode').val(),
        UOM: $('#UOM').val(),
        Quantity: $('#Quantity').val(),
        Decription: $('#Decription').val(),
        SKU: $('#SKU').val()
    }

    return data;
}




