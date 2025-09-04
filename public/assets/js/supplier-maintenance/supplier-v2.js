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

let issueTable = `
        <div class='mx-auto' style="font-size:14px">
            <strong>Possible Issues:</strong>
            <div class="mx-3">
                <span> *Duplication of Supplier Code.</span><br>
                <span> *One or more fields contain invalid data.</span>
            </div>
        </div>`;

$(document).ready(async function () {
    // Load Philippine data from global function if not already loaded
    if (!window.PhilippineLocationHelpers.isDataLoaded()) {
        await loadPhilippineData();
    }
    
    await datatables.loadSupplierData();
    await initVS.liteDataVS();
    await initVS.regionVS();
    initVS.provinceVS();
    initVS.municipalityVS();
    initVS.barangayVS();

    // Initialize character counter for address field using the global helper
    window.CharacterCounterHelper.initAddressField('#CompleteAddress', '#completeAddressCharCount', '#supplierMainModal');

    // Contact Number validation - only allow numbers
    $('#ContactNo').on('input', function(e) {
        // Remove any non-numeric characters
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    $('#ContactNo').on('keypress', function(e) {
        // Allow only numeric keys (0-9), backspace, delete, tab, and arrow keys
        const allowedKeys = [8, 9, 37, 38, 39, 40, 46]; // backspace, tab, arrow keys, delete
        const key = e.which || e.keyCode;
        
        if (allowedKeys.indexOf(key) !== -1) {
            return true; // Allow control keys
        }
        
        // Only allow numeric characters (0-9)
        if (key < 48 || key > 57) {
            e.preventDefault();
            return false;
        }
        
        return true;
    });

    // Prevent paste of non-numeric content
    $('#ContactNo').on('paste', function(e) {
        e.preventDefault();
        const paste = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
        const numericPaste = paste.replace(/[^0-9]/g, '');
        
        // Only paste if there are numeric characters and respect maxlength
        if (numericPaste) {
            const currentValue = this.value;
            const maxLength = parseInt(this.getAttribute('maxlength')) || 11;
            const newValue = currentValue + numericPaste;
            
            if (newValue.length <= maxLength) {
                this.value = newValue;
            } else {
                this.value = newValue.substring(0, maxLength);
            }
        }
    });

    $("#supplierTable").on("click", "tbody tr", async function () {
        $("#supplierTable tbody").css('pointer-events', 'none');
        const selectedSupplierCode = $(this).attr('id');

        await ajax('api/supp/vendors/' + selectedSupplierCode, 'GET', null, (response) => { // Success callback

            if (response.success == 1) {
                SupplierModal.viewMode(response.data);
                selectedMain = response.data;
            } else {
                Swal.fire({
                    title: "Opppps..",
                    text: response.message,
                    icon: "error"
                });
            }
            $("#supplierTable tbody").css('pointer-events', 'auto');
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
        SupplierModal.enable(true);
        SupplierModal.clear();
        $('#modalFields #SupplierCode').prop('disabled', false);

        $('#supplierMainModal').modal('show');

        $('#deleteSuppBtn').hide();
        $('#rePrintPage').hide();
        $('#addSuppBtn').show();
        $('#confirmSupp').hide();
        $('#editSuppBtn').hide();
    });

    $("#addSuppBtn").on("click", function () {
        if (SupplierModal.isValid()) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You want to add this Supplier?',
                    icon: 'question',
                    showDenyButton: true,
                    confirmButtonText: "Yes, Add",
                    denyButtonText: `Cancel`
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        SupplierModal.SupplierSave();
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

    $("#deleteSuppBtn").on("click", async function () {
        if ($(this).text().toLowerCase() == 'cancel') {
            $(this).text('Delete');
            $('#editSuppBtn').removeClass('btn-primary').addClass('btn-info');
            $('#editSuppBtn').text('Edit details');

            SupplierModal.fill(selectedMain);
            SupplierModal.enable(false);
            $('#confirmSupp').hide();
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
                    var selectedSupplierCode = $('#SupplierCode').val();
                    // console.log(selectedCustID)
                    ajax('api/supp/vendors/' + selectedSupplierCode, 'POST', JSON.stringify({
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
                                    SupplierModal.hide();
                                    datatables.loadSupplierData();
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

    $("#editSuppBtn").on("click", async function () {
        if ($(this).text().toLocaleLowerCase() == 'edit details') {
            SupplierModal.enable(true);
            $('#SupplierCode').prop('disabled', true);
            $(this).text('Save changes').removeClass('btn-info').addClass('btn-primary');
            $('#deleteSuppBtn').text('Cancel');
            $('#rePrintPage').hide();
            $('#confirmSupp').hide();
        } else {
            //save update
            if (SupplierModal.isValid()) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    showDenyButton: true,
                    confirmButtonText: "Yes, Update",
                    denyButtonText: `Cancel`
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        var selectedSupplierCode = $('#SupplierCode').val();
                        // var mdCode = $('#customerID').val();
                        const supplier = SupplierModal.getData();

                        await ajax('api/supp/vendors/' + selectedSupplierCode, 'POST', JSON.stringify({
                            data: {...supplier},
                            _method: "PUT"
                        }), (response) => { // Success callback
                            if (response.success) {
                                $(this).text('Edit details').removeClass('btn-primary').addClass('btn-info');
                                $('#deleteSuppBtn').text('Delete');

                                Swal.fire({
                                    title: "Success!",
                                    text: response.message,
                                    icon: "success",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    allowEnterKey: false,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        SupplierModal.hide();
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

                                        datatables.loadSupplierData();
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
    loadSupplierData: async () => {
        const supplierData = await ajax('api/supp/vendors', 'GET', null, (response) => { // Success callback
            jsonArr = response.data;
            // console.log(response.data);
            datatables.initSupplierDatatable(response);
            if(isloading){
                Swal.close();
                isloading = false;
            }
        }, (xhr, status, error) => { // Error callback
            console.error('Error:', error);
        });
    },
    initSupplierDatatable: (response) => {
        console.log(response.data);
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
                MainTH.order([10, 'desc']).draw(); // Order by lastUpdated column (index 10) in descending order
            } else {
                MainTH = $('#supplierTable').DataTable({
                    data: response.data,
                    language: {
                        searchPlaceholder: "Search here..."
                    },
                    order: [[10, 'desc']], // Order by lastUpdated column (index 10) in descending order
                    columns: [
                        {
                            data: null,
                            title: 'Supplier Code',
                            render: function(data, type, row){
                                if (!data) return '';

                                return `<strong>${row.SupplierCode}</strong> - <small>${row.SupplierName}</small><br><small><strong>${row.SupplierType}</strong></small>`;
                            }
                        },
                        {
                            data: null,
                            title: 'Contact Person',
                            render: function(data, type, row){
                                if (!data) return '';

                                return `<strong>${row.ContactPerson}</strong><br><small>${row.ContactNo}</small>`;
                            }
                        },
                        { data: 'TermsCode',  title: 'Terms Code' },
                        { data: 'CompleteAddress',  title: 'Complete Address' },
                        { data: 'Region',  title: 'Region' },
                        { data: 'Province',  title: 'Province' },
                        { data: 'Municipality',  title: 'Municipality' },
                        { data: 'City',  title: 'City' },
                        { data: 'holdStatus',  title: 'Hold Status' },
                        { data: 'PriceCode',  title: 'Price Code' },
                        {
                            data: 'lastUpdated',
                            title: 'Last Updated',
                            render: function (data, type, row) {
                                if (!data) return '';

                                const date = new Date(data);
                                const options = { day: '2-digit', month: 'short', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true };

                                return date.toLocaleString('en-GB', options);
                            }
                        },
                    ],
                    columnDefs: [
                        { className: "text-start", targets: [ 0, 1, 2, 3,4, 5, 6, 9 ] },
                        { className: "text-center", targets: [ 7, 8 ] },
                        // { className: "text-end", targets: [ 4 ] },
                        { className: "text-nowrap", targets: '_all' },
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    "createdRow": function (row, data) {
                        $(row).attr('id', data.SupplierCode);
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
                        dtlayoutTE.prepend('<div id="filterPOVS" name="filter" style="width: 150px" class="bg-white p-0 mx-1">Filter</div>');
                        $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                        $('.loadingScreen').remove();
                        $('#dattableDiv').removeClass('opacity-0');

                        const tableDiv = $('.dt-layout-row').first();
                        tableDiv.after('<div style="background: linear-gradient(to right, var(--primary-color, #1b438f), var(--secondary-color, #33336F) ); color: var(--text-color-light, #FFF); margin-top:10px; padding: 10px 15px; border-top-left-radius:10px; border-top-right-radius: 10px;"><p style="margin:0px">List of Suppliers</p></div>');
                    }
                });
            }
        }
    },
};

const SupplierModal = {
    isValid: () => {
        let isFormValid = $('#modalFields').valid();
        let isRegionValid = true;
        let isProvinceValid = true;
        let isMunicipalityValid = true;
        
        // Validate Region
        const regionValue = document.querySelector('#VSregion')?.value;
        if (!regionValue || regionValue === '') {
            isRegionValid = false;
            // Remove existing error message
            $('#VSregion').siblings('.validation-error').remove();
            // Add error message
            $('#VSregion').after('<label class="validation-error error" style="font-size: 8px; text-transform: uppercase;">THIS FIELD IS REQUIRED.</label>');
        } else {
            // Remove error message if valid
            $('#VSregion').siblings('.validation-error').remove();
        }
        
        // Validate Province
        const provinceValue = document.querySelector('#VSprovince')?.value;
        if (!provinceValue || provinceValue === '') {
            isProvinceValid = false;
            // Remove existing error message
            $('#VSprovince').siblings('.validation-error').remove();
            // Add error message
            $('#VSprovince').after('<label class="validation-error error" style="font-size: 8px; text-transform: uppercase;">THIS FIELD IS REQUIRED.</label>');
        } else {
            // Remove error message if valid
            $('#VSprovince').siblings('.validation-error').remove();
        }
        
        // Validate Municipality
        const municipalityValue = document.querySelector('#VSmunicipality')?.value;
        if (!municipalityValue || municipalityValue === '') {
            isMunicipalityValid = false;
            // Remove existing error message
            $('#VSmunicipality').siblings('.validation-error').remove();
            // Add error message
            $('#VSmunicipality').after('<label class="validation-error error" style="font-size: 8px; text-transform: uppercase;">THIS FIELD IS REQUIRED.</label>');
        } else {
            // Remove error message if valid
            $('#VSmunicipality').siblings('.validation-error').remove();
        }
        
        // Validate Barangay
        const barangayValue = document.querySelector('#VSbarangay')?.value;
        let isBarangayValid = true;
        if (!barangayValue || barangayValue === '') {
            isBarangayValid = false;
            // Remove existing error message
            $('#VSbarangay').siblings('.validation-error').remove();
            // Add error message
            $('#VSbarangay').after('<label class="validation-error error" style="font-size: 8px; text-transform: uppercase;">THIS FIELD IS REQUIRED.</label>');
        } else {
            // Remove error message if valid
            $('#VSbarangay').siblings('.validation-error').remove();
        }
        
        return isFormValid && isRegionValid && isProvinceValid && isMunicipalityValid && isBarangayValid;
    },
    hide: () => {
        $('#supplierMainModal').modal('hide');
    },
    show: () => {
        // Clear validation states and error messages
        if ($("#modalFields").data('validator')) {
            $("#modalFields").validate().resetForm();
            $("#modalFields").find('.is-invalid').removeClass('is-invalid');
            $("#modalFields").find('.invalid-feedback').remove();
            $("#modalFields").find('.error').removeClass('error');
        }
        $('#supplierMainModal').modal('show');
    },
    clear: () => {
        $('#modalFields input[type="text"]').val('');
        $('#modalFields input[type="number"]').val('');
        $('#modalFields textarea').val('');
        $('#CreditLimit').val('');
        initVS.regionVS();
        document.querySelector('#VSprovince').setOptions([])
        document.querySelector('#VSmunicipality').setOptions([])
        document.querySelector('#VSbarangay').setOptions([])

    },
    enable: (enable) => {
        $('#modalFields input[type="text"]').prop('disabled', !enable);
        $('#modalFields input[type="number"]').prop('disabled', !enable);
        $('#modalFields textarea').prop('disabled', !enable);
        $('#modalFields #SupplierCode').prop('disabled', true);
        if(!enable){
            document.querySelector('#VSregion').disable();
            document.querySelector('#VSprovince').disable();
            document.querySelector('#VSmunicipality').disable();
            document.querySelector('#VSbarangay').disable();
        } else{
            document.querySelector('#VSregion').enable();
            document.querySelector('#VSprovince').enable();
            document.querySelector('#VSmunicipality').enable();
            document.querySelector('#VSbarangay').enable();
        }

    },
    viewMode: async (custData) => {
        SupplierModal.fill(custData);
        $('#deleteSuppBtn').show();
        $('#addSuppBtn').hide();
        $('#editSuppBtn').show();
        $("#editSuppBtn").text('Edit details').removeClass('btn-primary').addClass('btn-info');
        $('#confirmSupp').hide();
        $('#deleteSuppBtn').text('Delete');
        $('#rePrintPage').hide();

        SupplierModal.enable(false);
        SupplierModal.show();
    },
    fill: async (suppData) => {
        $('#SupplierCode').val(suppData.SupplierCode);
        $('#SupplierName').val(suppData.SupplierName);
        $('#SupplierType').val(suppData.SupplierType);
        $('#ContactPerson').val(suppData.ContactPerson);
        $('#ContactNo').val(suppData.ContactNo);
        $('#TermsCode').val(suppData.TermsCode);
        $('#PriceCode').val(suppData.PriceCode);
        $('#CreditLimit').val(suppData.CreditLimit || 0);
        $('#holdStatus').val(suppData.holdStatus);
        $('#CompleteAddress').val(suppData.CompleteAddress);
        $('#PostalCode').val(suppData.PostalCode || '');

        // Filter and check if data exists before accessing
        var selectedProv = window.philippineData.provinces.filter(prov => prov.province_name == suppData.Province);
        var selectedRegion = [];
        var selectedMunicipality = [];
        
        // Check if province exists
        if (selectedProv.length > 0) {
            selectedRegion = window.philippineData.regions.filter(reg => reg.region_code == selectedProv[0].region_code);
            selectedMunicipality = window.philippineData.cities.filter(muni => muni.city_name == suppData.Municipality);
        }
        
        // Initialize region VS
        initVS.regionVS();
        
        // Set region if found
        if (selectedRegion.length > 0) {
            document.querySelector('#VSregion').setValue(selectedRegion[0].region_code);
        }
        
        setTimeout(() => {
            document.querySelector('#VSprovince').setOptions([]);
            
            // Set province if found
            if (selectedProv.length > 0) {
                document.querySelector('#VSprovince').addOption({
                    label: selectedProv[0].province_name,
                    value: selectedProv[0].province_code
                });
                document.querySelector('#VSprovince').setValue(selectedProv[0].province_code);
            }
        }, 100);

        setTimeout(() => {
            document.querySelector('#VSmunicipality').setOptions([]);
            
            // Set municipality if found
            if (selectedMunicipality.length > 0) {
                document.querySelector('#VSmunicipality').addOption({
                    label: selectedMunicipality[0].city_name,
                    value: selectedMunicipality[0].city_code
                });
                document.querySelector('#VSmunicipality').setValue(selectedMunicipality[0].city_code);
                console.log('Municipality set:', selectedMunicipality[0].city_code);
                
                // Load and set barangay if available
                setTimeout(() => {
                    if (suppData.Barangay) {
                        // Find barangay by name in the selected city
                        const selectedBarangay = window.philippineData.barangays.find(barangay =>
                            barangay.brgy_name.toLowerCase() === suppData.Barangay.toLowerCase() &&
                            barangay.city_code === selectedMunicipality[0].city_code
                        );
                        
                        if (selectedBarangay) {
                            document.querySelector('#VSbarangay').setValue(selectedBarangay.brgy_code);
                        }
                    }
                }, 200);
            } else {
                console.warn('Municipality not found:', suppData.Municipality);
            }
        }, 500);
    },
    SupplierSave: async () => {
        let suppData = SupplierModal.getData();
        // console.log(suppData);
        await ajax('api/supp/vendors', 'POST', JSON.stringify({ data: suppData }), (response) => { // Success callback
            if (response.success) {
                datatables.loadSupplierData();
                SupplierModal.hide();

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
            SupplierCode : $('#SupplierCode').val(),
            SupplierName : $('#SupplierName').val(),
            SupplierType : $('#SupplierType').val().substring(0, 20),
            ContactPerson : $('#ContactPerson').val(),
            ContactNo : $('#ContactNo').val(),
            TermsCode : $('#TermsCode').val(),
            CompleteAddress : $('#CompleteAddress').val(),
            PostalCode : $('#PostalCode').val(),
            PriceCode : $('#PriceCode').val(),
            CreditLimit : $('#CreditLimit').val() || 0,
            holdStatus : $('#holdStatus').val(),
            Region : document.querySelector('#VSregion').getDisplayValue(),
            Municipality : document.querySelector('#VSmunicipality').getDisplayValue(),
            Province : document.querySelector('#VSprovince').getDisplayValue(),
            City : document.querySelector('#VSmunicipality').getDisplayValue(),
            Barangay : document.querySelector('#VSbarangay').getDisplayValue(),
        }
        return data;
    },
}

var filteredRegion = [];
var filteredProvince = [];
var filteredMunicipality = [];
var filteredBarangay = [];
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
            additionalToggleButtonClasses: 'rounded customVS-height' ,
        });

    },

    regionVS: async () => {
        filteredRegion = [];

        filteredRegion = window.philippineData.regions.map(item => {
            return {
                value: item.region_code,
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
            additionalToggleButtonClasses: 'rounded ModalFieldCustomVS',
        });

        $('#VSregion').on('afterClose', function () {
            // Clear validation error when user selects a value
            if (this.value) {
                $('#VSregion').siblings('.validation-error').remove();
                
                filteredProvince = window.philippineData.provinces.filter(prov => prov.region_code == this.value)
                    .map(prov => {
                        return {
                            value: prov.province_code,
                            label: prov.province_name,
                        };
                    });

                initVS.provinceVS();
                filteredMunicipality=[];
                initVS.municipalityVS();
                filteredBarangay=[];
                initVS.barangayVS();
            }
        });

        $('#VSregion').on('change', function () {
            if (!this.value) {
                filteredProvince=[];
                initVS.provinceVS();
                filteredMunicipality=[];
                initVS.municipalityVS();
                filteredBarangay=[];
                initVS.barangayVS();
            } else {
                // Clear validation error when user selects a value
                $('#VSregion').siblings('.validation-error').remove();
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
            additionalToggleButtonClasses: 'rounded ModalFieldCustomVS',
        });

        $('#VSprovince').on('afterClose', function () {
            if (this.value) {
                // Clear validation error when user selects a value
                $('#VSprovince').siblings('.validation-error').remove();
                
                filteredMunicipality = window.philippineData.cities.filter(mul => mul.province_code == this.value)
                    .map(mul => {
                        return {
                            value: mul.city_code,
                            label: mul.city_name,
                        };
                    });

                initVS.municipalityVS();
                filteredBarangay=[];
                initVS.barangayVS();
            }
        });

        $('#VSprovince').on('change', function () {
            if (!this.value) {
                filteredMunicipality=[];
                initVS.municipalityVS();
                filteredBarangay=[];
                initVS.barangayVS();
            } else {
                // Clear validation error when user selects a value
                $('#VSprovince').siblings('.validation-error').remove();
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
            additionalToggleButtonClasses: 'rounded ModalFieldCustomVS',
        });

        // Add validation event listeners for municipality
        $('#VSmunicipality').on('afterClose', function () {
            if (this.value) {
                // Clear validation error when user selects a value
                $('#VSmunicipality').siblings('.validation-error').remove();
            }
        });

        $('#VSmunicipality').on('change', function () {
            if (this.value) {
                // Clear validation error when user selects a value
                $('#VSmunicipality').siblings('.validation-error').remove();
                
                // Filter barangays based on selected city/municipality
                filteredBarangay = window.philippineData.barangays.filter(barangay => barangay.city_code === this.value)
                    .map(barangay => {
                        return {
                            value: barangay.brgy_code,
                            label: barangay.brgy_name,
                        };
                    });

                initVS.barangayVS();
            } else {
                filteredBarangay = [];
                initVS.barangayVS();
            }
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

    barangayVS: () => {

        if (document.querySelector('#VSbarangay')?.virtualSelect) {
            document.querySelector('#VSbarangay').destroy();
        }

        VirtualSelect.init({
            ele: '#VSbarangay',
            options: filteredBarangay,
            multiple: false,
            hideClearButton: false,
            search: true,
            maxWidth: '100%',
            additionalClasses: 'rounded',
            additionalDropboxClasses: 'rounded',
            additionalDropboxContainerClasses: 'rounded',
            additionalToggleButtonClasses: 'rounded ModalFieldCustomVS',
        });

        // Add validation event listeners for barangay
        $('#VSbarangay').on('afterClose', function () {
            if (this.value) {
                // Clear validation error when user selects a value
                $('#VSbarangay').siblings('.validation-error').remove();
            }
        });

        $('#VSbarangay').on('change', function () {
            if (this.value) {
                // Clear validation error when user selects a value
                $('#VSbarangay').siblings('.validation-error').remove();
            }
        });
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
            datatables.loadSupplierData();
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








