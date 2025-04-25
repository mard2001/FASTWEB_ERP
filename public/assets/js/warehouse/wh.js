var MainTH, selectedMain;
var fileCtrTotal = 0;
var insertion = 0;
var jsonArr = [];
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
                            
let issueTable = `
                <div class='mx-auto' style="font-size:14px">
                    <strong>Possible Issues:</strong>
                    <div class="mx-3">
                        <span> *Duplication of Warehouse.</span><br>
                        <span> *One or more fields contain invalid data.</span>
                    </div>
                </div>`;

$(document).ready(async function () {
    await datatables.loadWHData();
    await initVS.liteDataVS();
    await initVS.regionVS();
    initVS.provinceVS();
    initVS.municipalityVS();

    $("#whTable").on("click", "tbody tr", async function () {
        $("#whTable tbody").css('pointer-events', 'none');
        const selectedSupplierCode = $(this).attr('id');

        await ajax('api/wh/warehouse/' + selectedSupplierCode, 'GET', null, (response) => { // Success callback

            if (response.success == 1) {
                WHModal.viewMode(response.data);
                selectedMain = response.data;
            } else {
                Swal.fire({
                    title: "Opppps..",
                    text: response.message,
                    icon: "error"
                });
            }
            $("#whTable tbody").css('pointer-events', 'auto');
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
        WHModal.enable(true);
        WHModal.clear();
        $('#modalFields #warehouseMainModal').prop('disabled', false);
        
        $('#warehouseMainModal').modal('show');

        $('#deleteWHBtn').hide();
        $('#rePrintPage').hide();
        $('#addWHBtn').show();
        $('#confirmWH').hide();
        $('#editWHBtn').hide();
    });

    $("#addWHBtn").on("click", function () {
        if (WHModal.isValid()) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You want to add this Supplier?',
                    icon: 'question',
                    showDenyButton: true,
                    confirmButtonText: "Yes, Add",
                    denyButtonText: `Cancel`
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        WHModal.WHSave();
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

    $("#deleteWHBtn").on("click", async function () {
        if ($(this).text().toLowerCase() == 'cancel') {
            $(this).text('Delete');
            $('#editWHBtn').removeClass('btn-primary').addClass('btn-info');
            $('#editWHBtn').text('Edit details');

            WHModal.fill(selectedMain);
            WHModal.enable(false);
            $('#confirmWH').hide();
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
                    var selectedWH = $('#Warehouse').val();
                    // console.log(selectedCustID)
                    ajax('api/wh/warehouse/' + selectedWH, 'POST', JSON.stringify({ 
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
                                    WHModal.hide();
                                    datatables.loadWHData();
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

    $("#editWHBtn").on("click", async function () {
        if ($(this).text().toLocaleLowerCase() == 'edit details') {
            WHModal.enable(true);
            $('#Warehouse').prop('disabled', true);
            $(this).text('Save changes').removeClass('btn-info').addClass('btn-primary');
            $('#deleteWHBtn').text('Cancel');
            $('#rePrintPage').hide();
            $('#confirmWH').hide();
        } else {
            //save update
            if (WHModal.isValid()) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    showDenyButton: true,
                    confirmButtonText: "Yes, Update",
                    denyButtonText: `Cancel`
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        var selectedWH = $('#Warehouse').val();
                        const warehouse = WHModal.getData();

                        await ajax('api/wh/warehouse/' + selectedWH, 'POST', JSON.stringify({
                            data: {...warehouse},
                            _method: "PUT"
                        }), (response) => { // Success callback
                            if (response.success) {
                                $(this).text('Edit details').removeClass('btn-primary').addClass('btn-info');
                                $('#deleteWHBtn').text('Delete');

                                Swal.fire({
                                    title: "Success!",
                                    text: response.message,
                                    icon: "success",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,  
                                    allowEnterKey: false,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        WHModal.hide();
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
        
                                        datatables.loadWHData();
                                    }
                                });
                                // ItemsTH.column(6).visible(false);
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
    loadWHData: async () => {
        const supplierData = await ajax('api/wh/warehouse', 'GET', null, (response) => { // Success callback
            jsonArr = response.data;
            // console.log(response.data);
            datatables.initWHDatatable(response);
            if(isloading){
                Swal.close();
                isloading = false;
            }
        }, (xhr, status, error) => { // Error callback
            console.error('Error:', error);
        });
    },
    initWHDatatable: (response) => {
        console.log(response.data);
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
            } else {
                MainTH = $('#whTable').DataTable({
                    data: response.data,
                    layout: {
                        topStart: function () {
                            return $(dataTableCustomBtn);
                        }
                    },
                    columns: [
                        { data: 'Warehouse',  title: 'Warehouse' },
                        { data: 'WHType',  title: 'Warehouse Type' },
                        { data: 'WHGroupCode',  title: 'Warehouse GroupCode' },
                        { data: 'WHGroupDesc',  title: 'Warehouse GroupDesc' },
                        { data: 'Municipality',  title: 'Municipality' },
                        { data: 'Status',  title: 'Status', 
                            render: function(data, type, row){
                                return (data == "A") ? "<span class='statusBadge1 align-middle'> Active </span>" : "";
                            }
                        },
                        { data: 'DateUpdated',  title: 'Date Updated',
                            render: function(data,type, row){
                                return data.split("T")[0];
                            }
                        },
                    ],
                    columnDefs: [
                        { className: "text-start", targets: [ 1, 3, 4, 5, 6 ] },
                        { className: "text-center", targets: [ 0, 2 ] },
                        // { className: "text-end", targets: [ 4 ] },
                        { className: "text-nowrap", targets: '_all' },
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    "createdRow": function (row, data) {
                        $(row).attr('id', data.Warehouse);
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
                        dtlayoutTE.prepend('<div id="filterSupplier" name="filter" style="width: 200px" class="form-control bg-white p-0 mx-1">Filter</div>');
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

const WHModal = {
    isValid: () => {
        return $('#modalFields').valid();
    },
    hide: () => {
        $('#warehouseMainModal').modal('hide');
    },
    show: () => {
        $('#warehouseMainModal').modal('show');
    },
    clear: () => {
        $('#modalFields input[type="text"]').val('');
        $('#modalFields input[type="number"]').val('');
        $('#modalFields textarea').val('');
        initVS.regionVS();
        document.querySelector('#VSprovince').setOptions([])
        document.querySelector('#VSmunicipality').setOptions([])

    },
    enable: (enable) => {
        $('#modalFields input[type="text"]').prop('disabled', !enable);
        $('#modalFields input[type="number"]').prop('disabled', !enable);
        $('#modalFields select').prop('disabled', !enable);
        $('#modalFields textarea').prop('disabled', !enable);
        $('#modalFields #SupplierCode').prop('disabled', true);
        if(!enable){
            document.querySelector('#VSregion').disable();
            document.querySelector('#VSprovince').disable();
            document.querySelector('#VSmunicipality').disable();
        } else{
            document.querySelector('#VSregion').enable();
            document.querySelector('#VSprovince').enable();
            document.querySelector('#VSmunicipality').enable();
        }
        
    },
    viewMode: async (whData) => {
        WHModal.fill(whData);
        $('#deleteWHBtn').show();
        $('#addWHBtn').hide();
        $('#editWHBtn').show();
        $("#editWHBtn").text('Edit details').removeClass('btn-primary').addClass('btn-info');
        $('#confirmWH').hide();
        $('#deleteWHBtn').text('Delete');
        $('#rePrintPage').hide();

        WHModal.enable(false);
        WHModal.show();
    },
    fill: async (whData) => {
        $('#Warehouse').val(whData.Warehouse);
        $('#WHType').val(whData.WHType);
        $('#WHGroupCode').val(whData.WHGroupCode);
        $('#WHGroupDesc').val(whData.WHGroupDesc);
        $('#Municipality').val(whData.Municipality);
        $('#Status').val(whData.Status);
        $('#DateUpdated').val(whData.DateUpdated);

        var selectedMunicipality = Municipality.filter(muni => muni.municipality_name == whData.Municipality);
        var selectedProv = Province.filter(prov => prov.province_id == selectedMunicipality[0].province_id);
        var selectedRegion = Region.filter(reg => reg.region_id == selectedProv[0].region_id);
        initVS.regionVS();
        document.querySelector('#VSregion').setValue(selectedRegion[0].region_id);
        setTimeout(() => {
            document.querySelector('#VSprovince').setOptions([])
            document.querySelector('#VSprovince').addOption({
                label: selectedProv[0].province_name,
                value: selectedProv[0].province_id 
            });
            document.querySelector('#VSprovince').setValue(selectedProv[0].province_id);
        }, 100);

        setTimeout(() => {
            document.querySelector('#VSmunicipality').setOptions([])
            document.querySelector('#VSmunicipality').addOption({
                label: selectedMunicipality[0].municipality_name,
                value: selectedMunicipality[0].municipality_id 
            });
            document.querySelector('#VSmunicipality').setValue(selectedMunicipality[0].municipality_id );
        }, 500);
    },
    WHSave: async () => {
        let suppData = WHModal.getData();
        // console.log(suppData);
        await ajax('api/wh/warehouse', 'POST', JSON.stringify({ data: suppData }), (response) => { // Success callback
            if (response.success) {
                datatables.loadWHData();
                WHModal.hide();

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
            Warehouse : $('#Warehouse').val(),
            WHType : $('#WHType').val(),
            WHGroupCode : $('#WHGroupCode').val(),
            WHGroupDesc : $('#WHGroupDesc').val(),
            Municipality : $('#VSmunicipality').val(),
            Status : $('#Status').val(),
        }
        return data;
    },
}

var filteredRegion = [];
var filteredProvince = [];
var filteredMunicipality = [];
const initVS = {
    liteDataVS: async () => {
        // Initialize VirtualSelect for ship via
        VirtualSelect.init({
            ele: '#filterSupplier',                   // Attach to the element
            options: [
                { label: "Active", value: 'A' },
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

        $("#filterSupplier").on("change", async function () {
            if (this.value) {
                // console.log(this.value);
                var filteredData = { data:[], success: true };
                var filterValues = this.value;
                if(filterValues.length == 0){
                    filteredData.data = jsonArr;
                } else{
                    filteredData.data = jsonArr.filter(item => filterValues.includes(item.Status));
                }
                datatables.initWHDatatable(filteredData);
                
            }
        });
    },

    

    regionVS: async () => {
        filteredRegion = [];

        filteredRegion = Region.map(item => {
            return {
                value: item.region_id, 
                label: item.region_name,
            };
        });

        if (document.querySelector('#VSregion')?.virtualSelect) {
            document.querySelector('#VSregion').destroy();
        }
        
        VirtualSelect.init({
            ele: '#VSregion',
            options: filteredRegion, 
            multiple: false, 
            hideClearButton: false, 
            search: true,
            maxWidth: '100%', 
            additionalClasses: 'rounded',
            additionalDropboxClasses: 'rounded',
            additionalDropboxContainerClasses: 'rounded',
            additionalToggleButtonClasses: 'rounded',
        });

        $('#VSregion').on('afterClose', function () {
            if (this.value) {
                filteredProvince = Province.filter(prov => prov.region_id == this.value)
                    .map(prov => {
                        return {
                            value: prov.province_id, 
                            label: prov.province_name,
                        };
                    });

                initVS.provinceVS();
                filteredMunicipality=[];
                initVS.municipalityVS();
            }
        });

        $('#VSregion').on('change', function () {
            if (!this.value) {
                filteredProvince=[];
                initVS.provinceVS();
                filteredMunicipality=[];
                initVS.municipalityVS();
            }
        });
    },

    provinceVS: () => {
        if (document.querySelector('#VSprovince')?.virtualSelect) {
            document.querySelector('#VSprovince').destroy();
        }
        
        VirtualSelect.init({
            ele: '#VSprovince',
            options: filteredProvince, 
            multiple: false, 
            hideClearButton: false, 
            search: true,
            maxWidth: '100%', 
            additionalClasses: 'rounded',
            additionalDropboxClasses: 'rounded',
            additionalDropboxContainerClasses: 'rounded',
            additionalToggleButtonClasses: 'rounded',
        });

        $('#VSprovince').on('afterClose', function () {
            if (this.value) {
                filteredMunicipality = Municipality.filter(mul => mul.province_id == this.value)
                    .map(mul => {
                        return {
                            value: mul.municipality_id, 
                            label: mul.municipality_name,
                        };
                    });
                    
                initVS.municipalityVS();
            }
        });

        $('#VSprovince').on('change', function () {
            if (!this.value) {
                filteredMunicipality=[];
                initVS.municipalityVS();
            }
        });
    },

    municipalityVS: () => {

        if (document.querySelector('#VSmunicipality')?.virtualSelect) {
            document.querySelector('#VSmunicipality').destroy();
        }
        
        VirtualSelect.init({
            ele: '#VSmunicipality',
            options: filteredMunicipality, 
            multiple: false, 
            hideClearButton: false, 
            search: true,
            maxWidth: '100%', 
            additionalClasses: 'rounded',
            additionalDropboxClasses: 'rounded',
            additionalDropboxContainerClasses: 'rounded',
            additionalToggleButtonClasses: 'rounded',
        });

        // $('#VSprovince').on('afterClose', function () {
        //     if (this.value) {
        //         filteredMunicipality = Municipality.filter(mul => mul.province_id == this.value)
        //             .map(mul => {
        //                 return {
        //                     value: mul.municipality_id, 
        //                     label: mul.municipality_name,
        //                 };
        //             });
                    
        //         initVS.municipalityVS();
        //     }
        // });
    },
}

async function ajaxCall(method, formDataArray = null, id) {
    let formData = new FormData();
    formData.append('customers', JSON.stringify(formDataArray));

    return await $.ajax({
        url: globalApi + 'api/maintenance/v2/customer/upload',
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
                    let message = `Some data could not be inserted. <br>Please review the uploaded CSV file.<br><strong>${unsucc}</strong> customer${unsucc > 1 ? 's' : ''} were not inserted.<br><br><br>${issueTable}`;

                    Swal.fire({
                        title: "Warning!",
                        html: message,
                        icon: "warning"
                    });
                }
            }
            datatables.loadWHData();
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
    link.download = `CustomerMaintenance_${today}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

}
