var removeHeader = ['id', 'uploaded_image_file', 'image_filename'];
var filename = 'product_maintenance_template.csv';


async function save() {

    var jsonFormData = getFormdata();

    return await apiCommunicationDbChanges(6, jsonFormData);
}

async function update() {
    var jsonFormData = getFormdata();

    jsonFormData.append('productID', selectedData.productID);
    jsonFormData.append('_method', 'PUT');  // Spoofing the PUT request

    return await apiCommunicationDbChanges(2, jsonFormData);
}

function modalFillField() {

    $('#StockCode').val(selectedData.StockCode);
    $('#Description').val(selectedData.Description);
    $('#Brand').val(selectedData.Brand);
    $('#StockUom').val(selectedData.StockUom);
    $('#priceWithVat').val(selectedData.priceWithVat);
    $('#prdImg').attr('src', selectedData.uploaded_image ? globalApi + 'storage/' + selectedData.uploaded_image : null);

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
                    { data: 'StockCode' },
                    { data: 'Description' },
                    { data: 'Brand' },
                    { data: 'priceWithVat' },
                    { data: 'StockUom' }
                ],
                scrollCollapse: true,
                scrollY: '100%',
                scrollX: '100%',

                "createdRow": function (row, data) {
                    $(row).attr('id', data.productID);
                },

                "pageLength": 8,
                "lengthChange": false,

                initComplete: function () {
                    // Modify datatable ui
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

        return respond.status_response == 1 ? true : false;

    }
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
            apiId = "/" + xmlJson.get('productID');
            apiMethod = 'POST';
            apidata = xmlJson;
            apidata.append('conn', JSON.stringify(retrievedUser));

            break;
        case 3: // DELETE DATA
            apiId = "/" + xmlJson.productID;
            apiMethod = 'POST';

            apidata = {
                conn: retrievedUser,
            }
            apidata._method = 'DELETE';
            apidata = JSON.stringify(apidata);

            break;
        case 4: // GET SINGLE DATA VIA ID
            apiId = "/" + xmlJson.productID;
            apiMethod = 'GET'
            break;
        case 5: // GET ALL DATA
            apiMethod = 'GET'
            apidata = {
                conn: retrievedUser,
            }
            break;
        case 6: // INSERT DATA
            apiMethod = 'POST'
            apidata = xmlJson;
            apidata.append('conn', JSON.stringify(retrievedUser));
            break;
    }

    return await $.ajax({
        url: globalApi + 'api/product' + apiId,
        type: apiMethod,
        processData: method == 6 || method == 2 ? false : true, // Required for FormData
        contentType: method == 6 || method == 2 ? false : 'application/json', // Required for FormData
        data: apidata, // Convert the data to JSON format

        success: async function (response) {

            if (response.status_response != 1) {

                console.log(JSON.stringify(response, null, 2));

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

function getFormdata() {
    var jsonFormData = new FormData();

    jsonFormData.append('StockCode', $('#StockCode').val());
    jsonFormData.append('Description', $('#Description').val());
    jsonFormData.append('Brand', $('#Brand').val());
    jsonFormData.append('StockUom', $('#StockUom').val());
    jsonFormData.append('priceWithVat', $('#priceWithVat').val());


    var files = $('#imageHolder').prop('files');

    if (files.length > 0) {
        jsonFormData.append('image_file', files[0]);
    }

    return jsonFormData;
}






