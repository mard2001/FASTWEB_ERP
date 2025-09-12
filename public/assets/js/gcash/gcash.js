var MainTH, selectedMain;
var fileCtrTotal = 0;
var insertion = 0;
var jsonArr = [];
var expectedtotalRows = 0;
var actualtotalRows = 0;
var iconResult;
var errorFile = false;
var isloading = false;

let issueTable = `
                <div class='mx-auto' style="font-size:14px">
                    <strong>Possible Issues:</strong>
                    <div class="mx-3">
                        <span> *Duplication of GCash Account.</span><br>
                        <span> *One or more fields contain invalid data.</span>
                    </div>
                </div>`;

$(document).ready(async function () {
    // Auto-format Account Number (11 digits starting with 09)
    $('#AccountNumber').on('input', function() {
        let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
        if (value.length > 11) {
            value = value.substring(0, 11); // Limit to 11 digits
        }
        
        // Ensure it starts with 09
        if (value.length > 0 && !value.startsWith('09')) {
            if (value.length === 1 && value !== '0') {
                value = '09' + value;
            } else if (value.length === 2 && value.charAt(0) === '0' && value.charAt(1) !== '9') {
                value = '09' + value.charAt(1);
            }
        }
        
        $(this).val(value);
    });

    await datatables.loadGcashData();
    await initVS.liteDataVS();

    $("#gcashTable").on("click", "tbody tr", async function () {
        $("#gcashTable tbody").css('pointer-events', 'none');
        const selectedGcashID = $(this).attr('id');

        await ajax('api/gcash/' + selectedGcashID, 'GET', null, (response) => { // Success callback
            if (response.success == true) {
                GcashModal.viewMode(response.data);
                selectedMain = response.data;
            } else {
                Swal.fire({
                    title: "Opppps..",
                    text: response.message,
                    icon: "error"
                });
            }
            $("#gcashTable tbody").css('pointer-events', 'auto');
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

    $('#addBtn').on('click', async function () {
        GcashModal.enable(true);
        GcashModal.clear();
        $('#modalFields #gcashMainModal').prop('disabled', false);

        $('#gcashMainModal').modal('show');

        $('#deleteGcashBtn').hide();
        $('#rePrintPage').hide();
        $('#addGcashBtn').show();
        $('#confirmGcash').hide();
        $('#editGcashBtn').hide();
    });

    $("#addGcashBtn").on("click", function () {
        if (GcashModal.isValid()) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to add this GCash account?',
                icon: 'question',
                showDenyButton: true,
                confirmButtonText: "Yes, Add",
                denyButtonText: `Cancel`
            }).then(async (result) => {
                if (result.isConfirmed) {
                    GcashModal.GcashSave();
                }
            });
        } else{
            console.log('invalid');
        }
    });

    $("#csvUploadShowBtn").on("click", async function () {
        $('#uploadCsv').modal('show');
    });

    $('#csvDLBtn').on('click', function () {
        downloadToCSV(jsonArr);
    });

    $("#deleteGcashBtn").on("click", async function () {
        if ($(this).text().toLowerCase() == 'cancel') {
            $(this).text('Delete');
            $('#editGcashBtn').removeClass('btn-primary').addClass('btn-info');
            $('#editGcashBtn').text('Edit details');

            GcashModal.fill(selectedMain);
            GcashModal.enable(false);
            $('#confirmGcash').hide();
        } else {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "var(--danger-color, #d33)",
                cancelButtonColor: "var(--primary-color, #3085d6)",
                confirmButtonText: "Yes, delete it!"
            }).then(async (result) => {
                if (result.isConfirmed) {
                    var selectedGcashID = selectedMain.GcashID;
                    
                    ajax('api/gcash/' + selectedGcashID, 'POST', JSON.stringify({
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
                                    $('#gcashMainModal').modal('hide');
                                    datatables.loadGcashData();
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

    $("#editGcashBtn").on("click", async function () {
        if ($(this).text().toLocaleLowerCase() == 'edit details') {
            GcashModal.enable(true);
            $('#GcashID').prop('disabled', true);
            $(this).text('Save changes').removeClass('btn-info').addClass('btn-primary');
            $('#deleteGcashBtn').text('Cancel');
            $('#rePrintPage').hide();
            $('#confirmGcash').hide();
        } else {
            //save update
            if (GcashModal.isValid()) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    showDenyButton: true,
                    confirmButtonText: "Yes, Update",
                    denyButtonText: `Cancel`
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        var selectedGcashID = selectedMain.GcashID;
                        const gcash = GcashModal.getData();

                        await ajax('api/gcash/' + selectedGcashID, 'POST', JSON.stringify({
                            data: {...gcash},
                            _method: "PUT"
                        }), (response) => { // Success callback
                            if (response.success) {
                                $(this).text('Edit details').removeClass('btn-primary').addClass('btn-info');
                                $('#deleteGcashBtn').text('Delete');

                                Swal.fire({
                                    title: "Success!",
                                    text: response.message,
                                    icon: "success",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    allowEnterKey: false,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        selectedMain = response.data;
                                        GcashModal.fill(selectedMain);
                                        GcashModal.enable(false);
                                        datatables.loadGcashData();
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
    loadGcashData: async () => {
        const gcashData = await ajax('api/gcash', 'GET', null, (response) => { // Success callback
            jsonArr = response.data;
            console.log('GCash data loaded:', response.data);
            datatables.initGcashDatatable(response);
            if(isloading){
                Swal.close();
                isloading = false;
            }
        }, (xhr, status, error) => { // Error callback
            console.error('Error loading GCash data:', error);
            console.error('XHR Response:', xhr.responseText);
            
            // Show user-friendly error message
            Swal.fire({
                title: "Error",
                text: "Failed to load GCash data. Please ensure the database table exists.",
                icon: "error"
            });
        });
    },
    initGcashDatatable: (response) => {
        console.log(response.data);
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
            } else {
                MainTH = $('#gcashTable').DataTable({
                    data: response.data,
                    language: {
                        searchPlaceholder: "Search here..."
                    },
                    columns: [
                        { data: 'AccountNumber',  title: 'Account Number' },
                        { data: 'AccountName',  title: 'Account Name' },
                        { data: 'Status',  title: 'Status',
                            render: function(data, type, row){
                                return (data == "A") ? "<span class='statusBadge1 align-middle'> Active </span>" : "<span class='statusBadge2 align-middle'> Inactive </span>";
                            }
                        },
                        { data: 'DateCreated',  title: 'Date Created',
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
                    ],
                    columnDefs: [
                        { className: "text-start", targets: [ 0, 1, 2, 3] },
                        { className: "text-center", targets: [] },
                        { className: "text-nowrap", targets: '_all' },
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    "createdRow": function (row, data) {
                        $(row).attr('id', data.GcashID);
                    },

                    "pageLength": 15,
                    "lengthChange": false,

                    initComplete: function () {
                        $(this.api().table().container()).find('#dt-search-0').addClass('p-1 mx-0 dtsearchInput nofocus');
                        $(this.api().table().container()).find('.dt-search label').addClass('py-1 mx-0 dtsearchLabel').html('<span class="mdi mdi-magnify"></span>');
                        $(this.api().table().container()).find('.dt-layout-row').first().find('.dt-layout-cell').each(function() { this.style.setProperty('height', '38px', 'important'); });
                        $(this.api().table().container()).find('.dt-layout-table').removeClass('px-4');
                        $(this.api().table().container()).find('.dt-scroll-body').addClass('rmvBorder');
                        $(this.api().table().container()).find('.dt-layout-table').addClass('btmdtborder');

                        const dtlayoutTE = $('.dt-layout-cell.dt-end').first();
                        dtlayoutTE.addClass('d-flex justify-content-end');
                        dtlayoutTE.prepend('<div id="filterGcash" name="filter" style="width: 150px" class="bg-white p-0 mx-1">Filter</div>');
                        $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                        $('.loadingScreen').remove();
                        $('#dattableDiv').removeClass('opacity-0');
                        const tableDiv = $('.dt-layout-row').first();
                        tableDiv.after('<div style="background: linear-gradient(to right, var(--primary-color, #1b438f), var(--secondary-color, #33336F) ); color: var(--text-color-light, #FFF); margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px">List of GCash Accounts</p></div>');
                    }
                });
            }
        }
    },
};

const GcashModal = {
    isValid: () => {
        // Simple validation
        const accountNumber = $('#AccountNumber').val().trim();
        const accountName = $('#AccountName').val().trim();
        
        if (!accountNumber) {
            Swal.fire({
                title: "Validation Error",
                text: "Account Number is required",
                icon: "warning"
            });
            return false;
        }
        
        if (!accountName) {
            Swal.fire({
                title: "Validation Error",
                text: "Account Name is required",
                icon: "warning"
            });
            return false;
        }
        
        // Validate account number format (11 digits starting with 09)
        if (!/^09\d{9}$/.test(accountNumber)) {
            Swal.fire({
                title: "Validation Error",
                text: "Account Number must be 11 digits starting with 09",
                icon: "warning"
            });
            return false;
        }
        
        return true;
    },
    hide: () => {
        $('#gcashMainModal').modal('hide');
    },
    show: () => {
        $('#gcashMainModal').modal('show');
    },
    clear: () => {
        $('#modalFields input[type="text"]').val('');
        $('#modalFields select').val('A');
    },
    enable: (enable) => {
        $('#modalFields input[type="text"]').prop('disabled', !enable);
        $('#modalFields select').prop('disabled', !enable);
    },
    viewMode: async (gcashData) => {
        GcashModal.fill(gcashData);
        $('#deleteGcashBtn').show();
        $('#addGcashBtn').hide();
        $('#editGcashBtn').show();
        $("#editGcashBtn").text('Edit details').removeClass('btn-primary').addClass('btn-info');
        $('#confirmGcash').hide();
        $('#deleteGcashBtn').text('Delete');
        $('#rePrintPage').hide();

        GcashModal.enable(false);
        GcashModal.show();
    },
    fill: async (gcashData) => {
        $('#AccountNumber').val(gcashData.AccountNumber);
        $('#AccountName').val(gcashData.AccountName);
        $('#Status').val(gcashData.Status);
    },
    GcashSave: async () => {
        let gcashData = GcashModal.getData();
        await ajax('api/gcash', 'POST', JSON.stringify({ data: gcashData }), (response) => { // Success callback
            if (response.success) {
                datatables.loadGcashData();
                GcashModal.hide();

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
            AccountNumber : $('#AccountNumber').val(),
            AccountName : $('#AccountName').val(),
            Status : $('#Status').val(),
        }
        return data;
    },
}

const initVS = {
    liteDataVS: async () => {
        // Initialize VirtualSelect for gcash status filter
        VirtualSelect.init({
            ele: '#filterGcash',                   // Attach to the element
            options: [
                { label: "Active", value: 'A' },
                { label: "Inactive", value: 'I' },
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

        $("#filterGcash").on("change", async function () {
            if (this.value) {
                var filteredData = { data:[], success: true };
                var filterValues = this.value;
                if(filterValues.length == 0){
                    filteredData.data = jsonArr;
                } else{
                    filteredData.data = jsonArr.filter(item => filterValues.includes(item.Status));
                }
                datatables.initGcashDatatable(filteredData);
            }
        });
    },
}

async function ajaxCall(method, formDataArray = null, id) {
    let formData = new FormData();
    formData.append('gcash', JSON.stringify(formDataArray));

    return await $.ajax({
        url: globalApi + 'api/gcash/upload',
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
                console.log('warning')
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
                    let message = `Some data could not be inserted. <br>Please review the uploaded CSV file.<br><strong>${unsucc}</strong> GCash account${unsucc > 1 ? 's' : ''} ${unsucc > 1 ? 'were' : 'was'} not inserted.<br><br><br>${issueTable}`;

                    Swal.fire({
                        title: "Warning!",
                        html: message,
                        icon: "warning"
                    });
                }
            }
            datatables.loadGcashData();
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
                    processExcelFile(files[i], i); // Process XLXS
                    console.log('Excel file.')
                }
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

function downloadToCSV(jsonArr){
    const csvData = Papa.unparse(jsonArr); // Convert JSON to CSV
    var today = new Date().toISOString().split('T')[0];

    // Create a blob and trigger download
    const blob = new Blob([csvData], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `GcashMaintenance_${today}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
