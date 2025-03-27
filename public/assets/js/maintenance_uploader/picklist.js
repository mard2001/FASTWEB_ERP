var removeHeader = [];

var filename = 'invoice_template.csv';
var apiCache;
$(document).ready(async function () {

    //await getAllXmlData();

    $('#deliveredBtn').click(async function () {
        if (!$(this).hasClass('selected')) {
            $('#pendingBtn').removeClass('selected');

            $(this).addClass('selected');
            await fadeOutElement(1);

        }


    });

    $('#status').click(async function () {
        $('#dateDelivered').prop('disabled', !$('#status').prop('checked'));

    });

    $('#pendingBtn').click(async function () {
        if (!$(this).hasClass('selected')) {
            $('#deliveredBtn').removeClass('selected');

            $(this).addClass('selected');
            await fadeOutElement(0);
        }
    });

    async function fadeOutElement(paramStat) {
        // // Await the fadeOut animation first
        // await new Promise((resolve) => {
        //     $('#getXmlData_wrapper div').fadeOut(250, resolve); // Wait for fadeOut to complete
        // });

        $('#getXmlData_wrapper div').fadeOut(250);

        // Determine the show/hide status based on paramStat
        var showOrHideStat = paramStat == 1 ? true : false;

        // Prepare the custom data object for the request
        var customData = {
            "status_response": 1,
            "response": apiCache.response.filter(stat => stat.status == paramStat)
        };

        // Fetch new XML data asynchronously
        await getAllXmlData(customData);

        // Update table column visibility based on the status
        dataTableHolder.column(5).visible(showOrHideStat);
        dataTableHolder.column(6).visible(showOrHideStat);
        dataTableHolder.column(7).visible(showOrHideStat);

        // After all actions, fade the div back in
        $('#getXmlData_wrapper div').fadeIn(20);
    }




});

async function save() {

    var jsonFormData = getFormData();
    console.log(jsonFormData);
    return await apiCommunicationDbChanges(1, JSON.stringify([jsonFormData]));
}

async function update() {
    var data = getFormData();
    data.id = selectedData.id;
    // console.log(data);
    return await apiCommunicationDbChanges(2, data);
}

function modalFillField() {


    $('#custCode').val(selectedData.custCode);
    $('#custName').val(selectedData.custName);
    $('#invoiceNumber').val(selectedData.invoiceNumber);
    $('#invoiceAmount').val(selectedData.invoiceAmount);
    $('#invoiceDate').val(selectedData.invoiceDate);
    $('#driver').val(selectedData.driver);
    $('#vehicle').val(selectedData.vehicle);
    $('#dateDelivered').val(selectedData.dateDelivered);
    $('#address').val(selectedData.address);
}

async function getAllXmlData(customData) {

    let respond;
    if (!customData) {
        apiCache = await apiCommunicationDbChanges(5);

        var currentTab = $('#deliveredBtn').hasClass('selected') ? 1 : 0;

        respond = {
            "status_response": 1,
            "response": apiCache.response.filter(stat => stat.status == currentTab)
        };


    } else {
        respond = customData;

    }

    if (respond.status_response == 1) {
        // apiCache = respond.response;            

        if (dataTableHolder) {
            dataTableHolder.clear().draw();
            dataTableHolder.rows.add(respond.response).draw();
        } else {
            //console.log(respond);
            dataTableHolder = $('#getXmlData').DataTable({
                data: respond.response,
                //search bar right side
                layout: {
                    topStart: function () {
                        return $(customActionButton);
                    }
                },
                autoWidth: true,
                columns: [{
                    data: 'custCode'
                },
                {
                    data: 'custName'
                },
                {
                    data: 'invoiceNumber'
                },
                {
                    data: 'invoiceAmount'
                },
                {
                    data: 'invoiceDate'
                },
                {
                    data: 'driver'
                },
                {
                    data: 'vehicle'
                },
                {
                    data: 'dateDelivered'
                },
                {
                    data: 'address'
                }
                ],
                scrollCollapse: true,
                scrollY: 'auto',
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

    var apidata = {
        conn: retrievedUser,
        data: xmlJson
    }

    switch (method) {
        case 1: // INSERT DATA
            apiMethod = 'POST';
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
            apiId = "/" + xmlJson.id
            apiMethod = 'POST';
            apidata._method = 'DELETE';
            apidata = JSON.stringify(apidata);

            break;
        case 4: // GET SINGLE DATA VIA ID
            apiId = "/" +  xmlJson.id
            apiMethod = 'GET'
            break;
        case 5: // GET ALL DATA
            apiMethod = 'GET'
            break;
    }

    // console.log(apidata);

    return await $.ajax({
        url: globalApi + 'api/picklist' + apiId,
        type: apiMethod,
        Accept: 'application/json',
        contentType: 'application/json',
        data: apidata,

        success: async function (response) {
            if (response.status_response != 1) {
                console.log(JSON.stringify(response, null, 2));

            }

            // console.log(response);
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
    var data = {
        custCode: $('#custCode').val(),
        custName: $('#custName').val(),
        invoiceNumber: $('#invoiceNumber').val(),
        invoiceAmount: $('#invoiceAmount').val(),
        invoiceDate: $('#invoiceDate').val(),
        driver: $('#driver').val(),
        vehicle: $('#vehicle').val(),
        dateDelivered: $('#dateDelivered').val(),
        address: $('#address').val(),
        status: $('#deliveredBtn').hasClass('selected') ? 1 : 0
    }

    return data;
}