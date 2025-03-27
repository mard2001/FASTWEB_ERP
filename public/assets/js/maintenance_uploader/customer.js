var removeHeader = ['id', 'uploaded_image_file', 'image_filename'];
var filename = 'customer_maintenance_template.csv';

async function save() {
    var data = getFieldData();

    return await apiCommunicationDbChanges(1, JSON.stringify([data]));
}

async function update() {  
    var data = getFieldData();
    data.id = selectedData.id;
    return await apiCommunicationDbChanges(2, data);
}

function modalFillField() {

    $('#customerID').val(selectedData.customerID);
    $('#mdCode').val(selectedData.mdCode);
    $('#custCode').val(selectedData.custCode);
    $('#custName').val(selectedData.custName);
    $('#contactCellNumber').val(selectedData.contactCellNumber);
    $('#contactPerson').val(selectedData.contactPerson);
    $('#contactLandline').val(selectedData.contactLandline);
    $('#address').val(selectedData.address);
    $('#frequencyCategory').val(selectedData.frequencyCategory);
    $('#mcpDay').val(selectedData.mcpDay);
    $('#mcpSchedule').val(selectedData.mcpSchedule);
    $('#geolocation').val(selectedData.geolocation);
    $('#lastUpdated').val(selectedData.lastUpdated);
    $('#lastPurchase').val(selectedData.lastPurchase);
    $('#latitude').val(selectedData.latitude);
    $('#longitude').val(selectedData.longitude);
    $('#dates_tamp').val(selectedData.dates_tamp);
    $('#time_stamp').val(selectedData.time_stamp);
    $('#syncstat').val(selectedData.syncstat);
    $('#isLockOn').val(selectedData.isLockOn);
    $('#priceCode').val(selectedData.priceCode);
    $('#baseGPSLat').val(selectedData.baseGPSLat);

    $('#custType').val(selectedData.custType);
    $('#isVisit').val(selectedData.isVisit);
    $('#DefaultOrdType').val(selectedData.DefaultOrdType);
    $('#CityMunCode').val(selectedData.CityMunCode);
    $('#REGION').val(selectedData.REGION);
    $('#PROVINCE').val(selectedData.PROVINCE);
    $('#MUNICIPALITY').val(selectedData.MUNICIPALITY);
    $('#BARANGAY').val(selectedData.BARANGAY);
    $('#Area').val(selectedData.Area);
    $('#warehouse').val(selectedData.warehouse);
    $('#KASOSYO').val(selectedData.KASOSYO);

    //$('#prdImg').attr('src', selectedData.uploaded_image ? globalApi + 'storage/' + selectedData.uploaded_image : null);


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
                // "dom": '<"top"f>rt<"bottom"ilp><"clear">',
                layout: {
                    topStart: function () {
                        return $(customActionButton);
                    }
                },
                responsive: true,
                columns: [
                    { data: 'customerID' },
                    { data: 'mdCode' },
                    { data: 'custCode' },
                    { data: 'custName' },
                    { data: 'contactCellNumber' },
                    { data: 'contactPerson' },
                    { data: 'contactLandline' },
                    // { data: 'address' },
                    { data: 'frequencyCategory' },
                    { data: 'mcpDay' },
                    { data: 'mcpSchedule' },
                    { data: 'geolocation' },
                    { data: 'lastUpdated' },
                    { data: 'lastPurchase' },
                    { data: 'latitude' },
                    { data: 'longitude' },
                    { data: 'syncstat' },
                    { data: 'dates_tamp' },
                    { data: 'time_stamp' },
                    { data: 'isLockOn' },
                    { data: 'priceCode' },
                    { data: 'custType' },
                    { data: 'isVisit' },
                    { data: 'DefaultOrdType' },
                    { data: 'CityMunCode' },
                    { data: 'REGION' },
                    { data: 'PROVINCE' },
                    { data: 'MUNICIPALITY' },
                    { data: 'BARANGAY' },
                    { data: 'Area' },
                    { data: 'warehouse' },
                    { data: 'KASOSYO' },
                ],
                scrollCollapse: true,
                scrollY: '100%',
                scrollX: '100%  ',
                columnDefs: [
                    // {
                    //     target: 7,
                    //     className: 'd-none'
                    // },
                    // {
                    //     target: 8,
                    //     className: 'd-none'
                    // },
                    // {
                    //     target: 0,
                    //     // className: 'd-flex justify-content-center align-items-center',

                    //     render: function (data, type, row) {
                    //         return `<img class="m-0" src="${data ? globalApi + 'storage/' + data : null}" alt="" width="50px" height="auto">`;
                    //     },
                    // }
                ],
                "createdRow": function (row, data) {
                    $(row).attr('id', data.id);
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

    }

    return respond.status_response == 1 ? true : false;
}

async function apiCommunicationDbChanges(method, xmlJson = null) {
    var apiMethod = "";
    var apiId = '';

    var retrievedUser = JSON.parse(localStorage.getItem('dbcon'));

    var apidata = {
        conn: retrievedUser,
        data: xmlJson
    }

    //console.log(JSON.stringify(apidata, null, 2))

    switch (method) {
        case 1: // INSERT DATA
            apiMethod = 'POST';
            apiMethod._method = 'POST';
            apidata.data = JSON.parse(apidata.data);
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
            apiMethod = 'GET';
            break;
        case 5: // GET ALL DATA
            apiMethod = 'GET';
            break;
    }

    return await $.ajax({
        url: globalApi + 'api/customer' + apiId,
        type: apiMethod,
        Accept: 'application/json',
        contentType: 'application/json', // Set content type to JSON
        data: apidata, // Convert the data to JSON format
        // data: xmlJson, // Convert the data to JSON format

        success: async function (response) {

            if (response.status_response != 1) {


                // Swal.fire({
                //     icon: "error",
                //     title: "Response Error",
                //     text: JSON.stringify(response.response, null, 2),
                // });

                console.log(JSON.stringify(response, null, 2));

            }

            //console.log(response);
            return response;

        },
        error: async function (xhr, status, error) {


            Swal.fire({
                icon: "error",
                title: "Api Error",
                text: error,
            });

            console.log(xhr, status, error)

            return xhr, status, error;
        }
    });


}

function getFieldData() {

    var data = {
        customerID: $('#customerID').val(),
        mdCode: $('#mdCode').val(),
        custCode: $('#custCode').val(),
        custName: $('#custName').val(),
        contactCellNumber: $('#contactCellNumber').val(),
        contactPerson: $('#contactPerson').val(),
        contactLandline: $('#contactLandline').val(),
        address: $('#address').val(),
        frequencyCategory: $('#frequencyCategory').val(),
        mcpDay: $('#mcpDay').val(),
        mcpSchedule: $('#mcpSchedule').val(),
        geolocation: $('#geolocation').val(),
        lastUpdated: $('#lastUpdated').val(),
        lastPurchase: $('#lastPurchase').val(),
        latitude: $('#latitude').val(),
        longitude: $('#longitude').val(),
        dates_tamp: $('#dates_tamp').val(),
        time_stamp: $('#time_stamp').val(),
        syncstat: $('#syncstat').val(),
        isLockOn: $('#isLockOn').val(),
        priceCode: $('#priceCode').val(),
        baseGPSLat: $('#baseGPSLat').val(),
        custType: $('#custType').val(),
        isVisit: $('#isVisit').val(),
        DefaultOrdType: $('#DefaultOrdType').val(),
        CityMunCode: $('#CityMunCode').val(),
        REGION: $('#REGION').val(),
        PROVINCE: $('#PROVINCE').val(),
        MUNICIPALITY: $('#MUNICIPALITY').val(),
        BARANGAY: $('#BARANGAY').val(),
        Area: $('#Area').val(),
        warehouse: $('#warehouse').val(),
        KASOSYO: $('#KASOSYO').val(),
    }


    return data;
}






