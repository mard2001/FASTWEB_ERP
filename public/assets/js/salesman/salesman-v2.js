var MainTH, selectedMain;
var fileCtrTotal = 0;
var insertion = 0;
var jsonArr = [];
var salesmanData = [];
var expectedtotalRows = 0;
var actualtotalRows = 0;
var iconResult;
var errorFile = false;
var isloading = false;

const dataTableCustomBtn = `<div class="main-content buttons w-100 overflow-auto d-flex align-items-center px-2" style="font-size: 12px;">
                                <div class="btn d-flex justify-content-around px-2 align-items-center me-1" id="addBtn">
                                    <div class="btnImg me-2" id="addImg">
                                    </div>
                                    <span>Add new</span>
                                </div>

                                <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="csvDLBtn">
                                    <div class="btnImg me-2" id="dlImg">
                                    </div>
                                    <span>Download Template</span>
                                </div>

                                <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="csvUploadShowBtn">
                                    <div class="btnImg me-2" id="ulImg">
                                    </div>
                                    <span>Upload Template</span>
                                </div>
                            </div>`;

let issueTable = `<div class='mx-auto' style="font-size:14px">
                        <strong>Possible Issues:</strong>
                        <div class="mx-3">
                            <span> *Duplication of Salesperson Code.</span><br>
                            <span> *One or more fields contain invalid data.</span>
                        </div>
                    </div>`;

$(document).ready(async function () {
    await datatables.loadSalesmanData();
    await initVS.liteDataVS();
    // await initVS.salesmanVS();

    $("#csvUploadShowBtn").on("click", async function () {
        $('#uploadCsv').modal('show');
    });

    $('#csvDLBtn').on('click', function () {
        downloadToCSV(jsonArr);
    });

    $("#salespersonTable").on("click", "tbody tr", async function () {
        const selectedSalesmanCode = $(this).attr('id');
        $('#EmployeeIDDIV').show();

        await ajax('api/maintenance/v2/salesperson/' + selectedSalesmanCode, 'GET', null, (response) => { // Success callback
            if (response.success == 1) {
                var trimmedData = valueTrimmer(response)
                SalesmanModal.viewMode(trimmedData);
                selectedMain = trimmedData;
            } else {
                Swal.fire({
                    title: "Opppps..",
                    text: response.message,
                    icon: "error"
                });
            }

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

    $("#deleteSPBtn").on("click", async function () {
        if ($(this).text().toLowerCase() == 'cancel') {
            $(this).text('Delete');
            $('#editSPBtn').removeClass('btn-primary').addClass('btn-info');
            $('#editSPBtn').text('Edit details');

            SalesmanModal.fill(selectedMain);
            SalesmanModal.enable(false);
            $('#confirmSP').hide();
        } else {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then(async (result) => {
                if (result.isConfirmed) {
                    var employeeID = $('#EmployeeID').val();
                    // console.log(selectedCustID)
                    ajax('api/maintenance/v2/salesperson/' + employeeID, 'POST', JSON.stringify({ 
                        _method: 'DELETE' 
                    }), (response) => { // Success callback
                        
                        if (response.success) {
                            Swal.fire({
                                title: "Success!",
                                text: response.message,
                                icon: "success",
                                allowOutsideClick: false,
                                allowEscapeKey: false,  
                                allowEnterKey: false,
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    isloading = true;
                                    Swal.fire({
                                        text: "Please wait... reloading data...",
                                        timerProgressBar: true,
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,  
                                        allowEnterKey: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        },
                                    });
                                    SalesmanModal.hide();
                                    datatables.loadSalesmanData();
                                }
                            });
                        } else {
                            Swal.fire({
                                title: "Opppps..",
                                text: response.message,
                                icon: "error"
                            });
                        }
                    }, (xhr, status, error) => { // Error callback
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            Swal.fire({
                                title: "Opppps..",
                                text: xhr.responseJSON.message,
                                icon: "error"
                            });

                        }
                    });
                }
            });
        }
    });

    $('#addBtn').on('click', async function () {
        SalesmanModal.enable(true);
        SalesmanModal.clear();
        
        $('#salespersonMainModal').modal('show');

        $('#deleteSPBtn').hide();
        $('#rePrintPage').hide();
        $('#addSPBtn').show();
        $('#confirmSP').hide();
        $('#editSPBtn').hide();
        $('#EmployeeIDDIV').hide();
    });

    $("#addSPBtn").on("click", function () {
        if (SalesmanModal.isValid()) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to add this Salesman?',
                icon: 'question',
                showDenyButton: true,
                confirmButtonText: "Yes, Add",
                denyButtonText: `Cancel`
            }).then(async (result) => {
                if (result.isConfirmed) {
                    SalesmanModal.SalesmanSave();
                }
            });
        } else{
            console.log('invalid');
        }

    });

    $("#editSPBtn").on("click", async function () {
        if ($(this).text().toLocaleLowerCase() == 'edit details') {
            SalesmanModal.enable(true);
            $('#EmployeeID').prop('disabled', true);
            $(this).text('Save changes').removeClass('btn-info').addClass('btn-primary');
            $('#deleteSPBtn').text('Cancel');
            $('#rePrintPage').hide();
            $('#confirmSP').hide();

        } else {
            //save update
            // if (SalesmanModal.isValid()) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    showDenyButton: true,
                    confirmButtonText: "Yes, Update",
                    denyButtonText: `Cancel`
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        var employeeID = $('#EmployeeID').val();

                        await ajax('api/maintenance/v2/salesperson/' + employeeID, 'POST', JSON.stringify({
                            data: SalesmanModal.getData(),
                            _method: "PUT"
                        }), (response) => { // Success callback
                            if (response.success) {
                                $(this).text('Edit details').removeClass('btn-primary').addClass('btn-info');
                                $('#deleteSPBtn').text('Delete');

                                Swal.fire({
                                    title: "Success!",
                                    text: response.message,
                                    icon: "success",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,  
                                    allowEnterKey: false,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#confirmPO').hide();
                                        SalesmanModal.hide();
                                        isloading = true;
                                        Swal.fire({
                                            text: "Please wait... reloading data...",
                                            timerProgressBar: true,
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,  
                                            allowEnterKey: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            },
                                        });
        
                                        datatables.loadSalesmanData();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "Opppps..",
                                    text: response.message,
                                    icon: "error"
                                });
                            }

                        }, (xhr, status, error) => { // Error callback
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                Swal.fire({
                                    title: "Opppps..",
                                    text: xhr.responseJSON.message,
                                    icon: "error"
                                });

                            }
                        });
                    }
                });
            // }
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
    loadSalesmanData: async () => {
        const SalesmanData = await ajax('api/maintenance/v2/salesperson', 'GET', null, (response) => { // Success callback
            jsonArr = response.data;
            // console.log(response.data);
            datatables.initSalesmanDatatable(response);
            if(isloading){
                Swal.close();
                isloading = false;
            }
        }, (xhr, status, error) => { // Error callback
            console.error('Error:', error);
        });
    },
    initSalesmanDatatable: (response) => {
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
            } else {
                MainTH = $('#salespersonTable').DataTable({
                    data: response.data,
                    layout: {
                        topStart: function () {
                            return $(dataTableCustomBtn);
                        }
                    },
                    columns: [
                        { data: 'EmployeeID',  title: 'Employee ID' },
                        { data: 'mdCode',  title: 'MdCode' },
                        { data: 'Branch',  title: 'Branch' },
                        { data: 'Type',  title: 'Type' },
                        { data: 'Salesperson',  title: 'Salesperson' },
                        { data: 'Name',  title: 'Name' },
                        { data: 'Warehouse',  title: 'Warehouse' },
                        { data: 'SourceWarehouse',  title: 'Source Warehouse' },
                        { data: 'ContactNo',  title: 'Contact Number' },
                        { data: 'ContactHP',  title: 'HP' },
                        { data: 'ContacteMail',  title: 'Email' },
                        { data: 'Addr1',  title: 'Addr1' },
                        { data: 'Addr2',  title: 'Addr2' },
                        { data: 'Addr3',  title: 'Addr3' },
                        { data: 'Group1',  title: 'Group1' },
                        { data: 'Group2',  title: 'Group2' },
                        { data: 'Group3',  title: 'Group3' },
                    ],
                    columnDefs: [
                        { className: "text-start", targets: [ 2, 3, 4, 5, 8, 9 ] },
                        { className: "text-center", targets: [ 0, 1, 6, 7 ] },
                        // { className: "text-end", targets: [ 4 ] },
                        { className: "text-nowrap", targets: [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16 ] },
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    "createdRow": function (row, data) {
                        $(row).attr('id', data.EmployeeID);
                    },

                    "pageLength": 15,
                    "lengthChange": false,

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
                        $('.dt-layout-table').addClass('mt-4');
                    }
                });

            }
        }
    },
};

const SalesmanModal = {
    isValid: () => {
        return $('#modalFields .needField').valid();
    },
    hide: () => {
        $('#salespersonMainModal').modal('hide');
    },
    show: () => {
        $('#salespersonMainModal').modal('show');
    },
    clear: () => {
        $('#modalFields input[type="text"]').val('');
        $('#modalFields input[type="number"]').val('');
        $('#modalFields textarea').val('');
    },
    enable: (enable) => {
        $('#modalFields input[type="text"]').prop('disabled', !enable);
        $('#modalFields input[type="number"]').prop('disabled', !enable);
        $('#modalFields textarea').prop('disabled', !enable);
        $('#modalFields #EmployeeID').prop('disabled', true);
    },
    viewMode: async (salesmanData) => {
        SalesmanModal.fill(salesmanData);
        $('#deleteSPBtn').show();
        $('#addSPBtn').hide();
        $('#editSPBtn').show();
        $("#editSPBtn").text('Edit details').removeClass('btn-primary').addClass('btn-info');
        $('#confirmSP').hide();
        $('#deleteSPBtn').text('Delete');
        $('#rePrintPage').hide();

        SalesmanModal.enable(false);
        SalesmanModal.show();
    },
    fill: async (salesmanData) => {
        $('#EmployeeID').val(salesmanData.EmployeeID);
        $('#mdCode').val(salesmanData.mdCode);
        $('#Branch').val(salesmanData.Branch);
        $('#Type').val(salesmanData.Type);
        $('#Salesperson').val(salesmanData.Salesperson);
        $('#Name').val(salesmanData.Name);
        $('#Warehouse').val(salesmanData.Warehouse);
        $('#SourceWarehouse').val(salesmanData.SourceWarehouse);
        $('#ContactNo').val(salesmanData.ContactNo);
        $('#ContactHP').val(salesmanData.ContactHP);
        $('#Addr1').val(salesmanData.Addr1);
        $('#Addr2').val(salesmanData.Addr2);
        $('#Addr3').val(salesmanData.Addr3);
        $('#Addr4').val(salesmanData.Addr4);
        $('#Group1').val(salesmanData.Group1);
        $('#Group2').val(salesmanData.Group2);
        $('#Group3').val(salesmanData.Group3);
    },
    SalesmanSave: async () => {
        let salesmanData = SalesmanModal.getData();
        await ajax('api/maintenance/v2/salesperson', 'POST', JSON.stringify({ data: salesmanData }), (response) => { // Success callback
            if (response.success) {
                datatables.loadSalesmanData();
                SalesmanModal.hide();

                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success"
                });

            }else if(response.success == 409){
                Swal.fire({
                    title: "error",
                    text: response.message,
                    icon: "error"
                });
            }

        }, (xhr, status, error) => { // Error callback

            if (xhr.responseJSON && xhr.responseJSON.message) {
                Swal.fire({
                    title: "Opppps..",
                    text: xhr.responseJSON.message,
                    icon: "error"
                });
            }
        });
    },
    getData: () => {
        var data = {
            mdCode: $('#mdCode').val(),
            Branch: $('#Branch').val(),
            Type: $('#Type').val(),
            Salesperson: $('#Salesperson').val(),
            Name: $('#Name').val(),
            Warehouse: $('#Warehouse').val(),
            SourceWarehouse: $('#SourceWarehouse').val(),
            ContactNo: $('#ContactNo').val(),
            ContactHP: $('#ContactHP').val(),
            Addr1: $('#Addr1').val(),
            Addr2: $('#Addr2').val(),
            Addr3: $('#Addr3').val(),
            Addr4: $('#Addr4').val(),
            Group1: $('#Group1').val(),
            Group2: $('#Group2').val(),
            Group3: $('#Group3').val()
        }
        return data;
    },
}

const initVS = {
    liteDataVS: async () => {
        // Initialize VirtualSelect for ship via
        VirtualSelect.init({
            ele: '#filterPOVS',                   // Attach to the element
            options: [
                // { label: "", value: null },
                // { label: "", value: 1 },
                // { label: "", value: "2" },

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
    }
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
                    processCSVFile(files[i], i); // Process CSV
                    console.log('CSV file.')
                }
                else if(fileExtension === 'xlsx'){
                    processExcelFile(files[i], i); // Process CSV
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

function downloadToCSV(jsonArr){
    const csvData = Papa.unparse(jsonArr); // Convert JSON to CSV
    var today = new Date().toISOString().split('T')[0];

    // Create a blob and trigger download
    const blob = new Blob([csvData], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `SalesmanMaintenance_${today}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

}

function valueTrimmer(response){
        // Trim every string value inside response.data
        $.each(response.data, function (key, value) {
            if (typeof value === "string") {
                response.data[key] = value.trim();
            }
        });
    return response.data
}