

var removeHeader = ['id', 'lastupdated', 'invstat', 'syncstat', 'dates_tamp', 'time_stamp'];
var filename = 'inventory_maintenance_template.csv';


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

    $('#PeriodYear').val(selectedData.PeriodYear);
    $('#PeriodMonth').val(selectedData.PeriodMonth);
    $('#BusinessUnit').val(selectedData.BusinessUnit);
    $('#PAType').val(selectedData.PAType);
    $('#CustomerClass').val(selectedData.CustomerClass);
    $('#StockCode').val(selectedData.StockCode);
    $('#DropSize').val(selectedData.DropSize);
    $('#Points').val(selectedData.Points);
    $('#Amount').val(selectedData.Amount);
    $('#BonusPoint').val(selectedData.BonusPoint);
    $('#MHCount').val(selectedData.MHCount);
    $('#UpdatedBy').val(selectedData.UpdatedBy);
    $('#DateUpdated').val(selectedData.DateUpdated);

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
                    { data: 'PeriodYear' },
                    { data: 'PeriodMonth' },
                    { data: 'BusinessUnit' },
                    { data: 'PAType' },
                    { data: 'CustomerClass' },
                    { data: 'StockCode' },
                    { data: 'DropSize' },
                    { data: 'Points' },
                    { data: 'Amount' },
                    { data: 'BonusPoint' },
                    { data: 'MHCount' },
                    { data: 'UpdatedBy' },
                    { data: 'DateUpdated' },

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

    // console.log(apidata);

    return await $.ajax({
        url: globalApi + 'api/pamasterlist' + apiId,
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
                title: "API Error",
                text: xhr.responseJSON?.message || xhr.statusText,
            });

            console.log(xhr, status, error)

            return xhr, status, error;
        }
    });


}

function getFieldData() {


    var data = {
        PeriodYear: $('#PeriodYear').val(),
        PeriodMonth: $('#PeriodMonth').val(),
        BusinessUnit: $('#BusinessUnit').val(),
        PAType: $('#PAType').val(),
        CustomerClass: $('#CustomerClass').val(),
        StockCode: $('#StockCode').val(),
        DropSize: $('#DropSize').val(),
        Points: $('#Points').val(),
        Amount: $('#Amount').val(),
        BonusPoint: $('#BonusPoint').val(),
        MHCount: $('#MHCount').val(),
        UpdatedBy: $('#UpdatedBy').val(),
        DateUpdated: $('#DateUpdated').val(),

    }

    return data;
}



