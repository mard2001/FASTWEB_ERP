var removeHeader = ['id', 'uploaded_image_file', 'image_filename'];
var filename = 'salesman_maintenance_template.csv';

$(document).ready(async function () {

});

async function save() {

    var jsonFormData = getFormData();

    return await apiCommunicationDbChanges(6, jsonFormData);
}

async function update() {
    var jsonFormData = getFormData();

    jsonFormData.append('id', selectedData.id);
    jsonFormData.append('_method', 'PUT');  // Spoofing the PUT request

    return await apiCommunicationDbChanges(2, jsonFormData);
}

function modalFillField() {
    $('#mdCode').val(selectedData.mdCode);
    $('#mdPassword').val(selectedData.mdPassword);
    $('#mdLevel').val(selectedData.mdLevel);
    $('#mdSalesmancode').val(selectedData.mdSalesmancode);
    $('#mdName').val(selectedData.mdName);
    $('#siteCode').val(selectedData.siteCode);
    $('#eodNumber1').val(selectedData.eodNumber1);
    $('#eodNumber2').val(selectedData.eodNumber2);
    $('#contactCellNumber').val(selectedData.contactCellNumber);
    $('#mdColor').val(selectedData.mdColor);
    $('#priceCode').val(selectedData.priceCode);
    $('#StockTakeCL').val(selectedData.StockTakeCL);
    $('#EOD').val(selectedData.EOD);
    $('#DefaultOrdType').val(selectedData.DefaultOrdType);
    $('#stkRequired').val(selectedData.stkRequired);
    $('#calltime').val(selectedData.calltime);
    $('#loadingCap').val(selectedData.loadingCap);
    $('#isActive').val(selectedData.isActive);
    $('#PhoneSN').val(selectedData.PhoneSN);
    $('#verNumber').val(selectedData.verNumber);
    $('#ImmediateHead').val(selectedData.ImmediateHead);
    $('#SalesmanType').val(selectedData.SalesmanType);
    $('#WarehouseCode').val(selectedData.WarehouseCode);

    $('#prdImg').attr('src', selectedData.uploaded_image ? globalApi + 'storage/' + selectedData.uploaded_image : null);


}

async function getAllXmlData() {

    var respond = await apiCommunicationDbChanges(5);

    if (respond.status_response == 1) {
        if (dataTableHolder) {
            dataTableHolder.clear().draw();
            dataTableHolder.rows.add(respond.response).draw();
        } else {
            //console.log(respond);
            dataTableHolder = $('#getXmlData').DataTable({
                data: respond.response,
                layout: {
                    topStart: function () {
                        return $(customActionButton);
                    }
                },
                columns: [
                    {
                        data: 'uploaded_image',
                        render: function (data, type, row) {
                            return `<div class="w-100 text-center"><img class="m-0" src="${data ? globalApi + 'storage/' + data : null}" alt="" width="50px" height="auto"></div>`;
                        }
                    },
                    { data: 'mdCode' },
                    { data: 'mdPassword' },
                    { data: 'mdLevel' },
                    { data: 'mdSalesmancode' },
                    { data: 'mdName' },
                    { data: 'siteCode' },
                    { data: 'eodNumber1' },
                    { data: 'eodNumber2' },
                    { data: 'contactCellNumber' },
                    { data: 'mdColor' },
                    { data: 'priceCode' },
                    { data: 'StockTakeCL' },
                    { data: 'EOD' },
                    { data: 'DefaultOrdType' },
                    { data: 'stkRequired' },
                    { data: 'calltime' },
                    { data: 'loadingCap' },
                    { data: 'isActive' },
                    { data: 'PhoneSN' },
                    { data: 'verNumber' },
                    { data: 'ImmediateHead' },
                    { data: 'SalesmanType' },
                    { data: 'WarehouseCode' },
                ],
                scrollCollapse: true,
                scrollY: '80%',
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
            // console.log(data);
        }

        if (respond.response.length > 0) {
            ajaxData = respond.response;

        }




    }

    return respond.status_response == 1 ? true : false;
}

async function apiCommunicationDbChanges(method, xmlJson = null) {
    var apiMethod = "";
    var apiId = '';

    var retrievedUser = JSON.parse(localStorage.getItem('dbcon'));
    var apidata;


    switch (method) {
        case 1: // BULK INSERT
            apiMethod = 'POST';

            apidata = {
                conn: retrievedUser,
                data: xmlJson
            }

            apidata.data = JSON.parse(xmlJson);
            apidata = JSON.stringify(apidata);
            apiId = 'bulk';

            break;
        case 2: // UPDATE DATA
            apiId = "/" + xmlJson.get('id');
            apiMethod = 'POST';

            apidata = xmlJson;
            apidata.append('conn', JSON.stringify(retrievedUser));

            break;
        case 3: // DELETE DATA
            apiId = "/" + xmlJson.id;
            apiMethod = 'POST';

            apidata = {
                conn: retrievedUser,
            }

            apidata._method = 'DELETE';
            apidata = JSON.stringify(apidata);

            break;


        case 4: // GET SINGLE DATA VIA ID
            apiId = xmlJson.id
            apiMethod = 'GET'
            break;
        case 5: // GET ALL DATA
            apiMethod = 'GET'
            apidata = {
                conn: retrievedUser,
                data: xmlJson
            }
            break;
        case 6: // INSERT DATA
            apiMethod = 'POST'
            apidata = xmlJson;
            apidata.append('conn', JSON.stringify(retrievedUser));
            break;
    }

    return await $.ajax({
        url: globalApi + 'api/salesman' + apiId,
        type: apiMethod,
        processData: method == 6 || method == 2 ? false : true, // Required for FormData
        contentType: method == 6 || method == 2 ? false : 'application/json', // Required for FormData
        data: apidata, // Convert the data to JSON format

        // data: xmlJson, // Convert the data to JSON format
        success: async function (response) {
            if (response.status_response != 1) {
            }

            //console.log(response);
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

function getFormData() {
    var jsonFormData = new FormData();

    jsonFormData.append('mdCode', $('#mdCode').val());
    jsonFormData.append('mdPassword', $('#mdPassword').val());
    jsonFormData.append('mdLevel', $('#mdLevel').val());
    jsonFormData.append('mdSalesmancode', $('#mdSalesmancode').val());
    jsonFormData.append('mdName', $('#mdName').val());
    jsonFormData.append('siteCode', $('#siteCode').val());
    jsonFormData.append('eodNumber1', $('#eodNumber1').val());
    jsonFormData.append('eodNumber2', $('#eodNumber2').val());
    jsonFormData.append('contactCellNumber', $('#contactCellNumber').val());
    jsonFormData.append('mdColor', $('#mdColor').val());
    jsonFormData.append('priceCode', $('#priceCode').val());
    jsonFormData.append('StockTakeCL', $('#StockTakeCL').val());
    jsonFormData.append('EOD', $('#EOD').val());
    jsonFormData.append('DefaultOrdType', $('#DefaultOrdType').val());
    jsonFormData.append('stkRequired', $('#stkRequired').val());
    jsonFormData.append('calltime', $('#calltime').val());
    jsonFormData.append('loadingCap', $('#loadingCap').val());
    jsonFormData.append('isActive', $('#isActive').val());
    jsonFormData.append('PhoneSN', $('#PhoneSN').val());
    jsonFormData.append('verNumber', $('#verNumber').val());
    jsonFormData.append('ImmediateHead', $('#ImmediateHead').val());
    jsonFormData.append('SalesmanType', $('#SalesmanType').val());
    jsonFormData.append('WarehouseCode', $('#WarehouseCode').val());

    var files = $('#imageHolder').prop('files');

    if (files.length > 0) {
        jsonFormData.append('image_file', files[0]);
    }

    return jsonFormData;
}



