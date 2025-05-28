var MainTH, selectedMain;
var fileCtrTotal = 0;
var insertion = 0;
var jsonArr = [];
var detailsDatatable;
var isEditable = false;
var originalSelected = [];

const dataTableCustomBtn = `<div class="main-content buttons w-100 overflow-auto d-flex align-items-center px-2" style="font-size: 12px;">
                                <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="csvDLBtn">
                                    <div class="btnImg me-2" id="dlImg">
                                    </div>
                                    <span>Download Report</span>
                                </div>

                                <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="manualSheetDLBtn">
                                    <div class="btnImg me-2" id="dlImg">
                                    </div>
                                    <span>Download Manual Sheet</span>
                                </div>
                            </div>`;

$(document).ready(async function () {
    await datatables.loadCountData();
    await initVS.liteDataVS();
    datatables.initDetailsDatatable([]);

    $("#icTable").on("click", "tbody tr", async function () {
        Swal.fire({
            text: "Please wait... Preparing data...",
            timerProgressBar: true,
            allowOutsideClick: false,
            allowEscapeKey: false,  
            allowEnterKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        const selectedICID = $(this).attr('id');
        await ajax('api/report/v2/countsheet/' + selectedICID, 'GET', null, (response) => { 
            Swal.close();
            if (response.success == 1) {
                ICModal.viewMode(response.data);
                selectedMain = response.data;
                originalSelected = JSON.parse(JSON.stringify(response.data));

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

    $('#csvDLBtn').on('click', function () {
        downloadToCSV(jsonArr);
    });

    $('#manualSheetDLBtn').on('click', function () {
        downloadManualCountSheet(0);
    });

    $('#rePrintPage').on('click', function () {
        downloadManualCountSheet(1);
    });

    $('#deleteICBtn').on('click', function () {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
          }).then((result) => {
            if (result.isConfirmed) {
                let userData = localStorage.getItem('user');
                let user = JSON.parse(userData);
                ajax('api/report/v2/countsheet/' + selectedMain.CNTHEADER_ID, 'POST', JSON.stringify({ 
                    data: {
                        userID: user.name
                    },
                    _method: 'DELETE' 
                }), (response) => { // Success callback
                    if(response.success){
                        Swal.fire({
                            title: "Deleted!",
                            text: response.message,
                            icon: "success"
                        });
                    } else{
                        Swal.fire({
                            title: "Opppps..",
                            text: response.message,
                            icon: "error"
                        });
                    }
                }, (xhr, status, error) => { // Error callback
                    console.error('Error:', error);
                });

                
            }
        }); 
    })

    $('#editICBtn').on('click', function () {
        if ($(this).text().toLowerCase() == 'edit sheet') {
            isEditable = true;
            $('#deleteICBtn').hide();
            $('#addICBtn').hide();
            $('#editICBtn').show();
            $("#editICBtn").text('Save Changes').removeClass('btn-info').addClass('btn-primary');
            $('#confirmIC').hide();
            $('#rePrintPage').hide();
            $('#cancelEditICBtn').show();
        } else{
            Swal.fire({
                title: "Are you sure to save new changes?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Save Changes"
            }).then((result) => {
                if (result.isConfirmed) {
                    isEditable = false;
                    $('#deleteICBtn').show();
                    $('#addICBtn').hide();
                    $('#editICBtn').show();
                    $("#editICBtn").text('Edit Sheet').removeClass('btn-primary').addClass('btn-info');
                    $('#confirmIC').hide();
                    $('#rePrintPage').show();
                    $('#cancelEditICBtn').hide();
        
                    let updatedData = detailsDatatable.rows().data().toArray();
                    updateCount(updatedData);
                }
            }); 
            
        }
    })

    $('#cancelEditICBtn').on('click', function () {
        isEditable = false;
        $('#deleteICBtn').show();
        $('#addICBtn').hide();
        $('#editICBtn').show();
        $("#editICBtn").text('Edit Sheet').removeClass('btn-primary').addClass('btn-info');
        $('#confirmIC').hide();
        $('#deleteICBtn').text('Delete Sheet');
        $('#rePrintPage').show();
        $('#cancelEditICBtn').hide();
        selectedMain = JSON.parse(JSON.stringify(originalSelected))
        detailsDatatable.clear().rows.add(selectedMain.details).draw();
    })

    $('#closeICBtn').on('click', function () {
        isEditable = false;
    })

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
    loadCountData: async () => {
        const CountData = await ajax('api/report/v2/countsheet', 'GET', null, (response) => { // Success callback
            jsonArr = response.data;
            // console.log(response.data);
            datatables.initICDatatable(response);
        }, (xhr, status, error) => { // Error callback
            console.error('Error:', error);
        });
    },

    initICDatatable: (response) => {
        if (response.success) {
            if (MainTH) {
                MainTH.clear().draw();
                MainTH.rows.add(response.data).draw();
            } else {
                MainTH = $('#icTable').DataTable({
                    data: response.data,
                    layout: {
                        topStart: function () {
                            return $(dataTableCustomBtn);
                        }
                    },
                    columns: [
                        { data: 'CNTHEADER_ID',  title: 'Count ID' },
                        { data: 'STATUS',  title: 'Status',
                            render: function(data, type, row) {
                                if (!data || isNaN(data)) return '-1';
                                return parseFloat(data) != 0 ? "<span class='statusBadge1'>Active</span>" : "<span class='statusBadge2'>Deleted</span>";
                            }
                        },
                        { data: 'user.FULLNAME',  title: 'User' },
                        { data: 'MOTATION',  title: 'Motation' },
                        { data: 'DATECREATED',  title: 'Date Created' },
                    ],
                    columnDefs: [
                        // { className: "text-start", targets: [ 0, 1, 2, 6 ] },
                        { className: "text-center", targets: [ 1 ] },
                        // { className: "text-end", targets: [ 4 ] },
                        { className: "text-uppercase", targets: [ 2 ] },
                    ],
                    scrollCollapse: true,
                    scrollY: '100%',
                    scrollX: '100%',
                    "createdRow": function (row, data) {
                        $(row).attr('id', data.CNTHEADER_ID);
                    },

                    "pageLength": 15,
                    "lengthChange": false,

                    initComplete: function () {
                        $(this.api().table().container()).find('#dt-search-0').addClass('p-1 mx-0 dtsearchInput nofocus');
                        $(this.api().table().container()).find('.dt-search label').addClass('py-1 px-3 mx-0 dtsearchLabel').html('<span class="mdi mdi-magnify"></span>');
                        $(this.api().table().container()).find('.dt-layout-row').first().find('.dt-layout-cell').each(function() { this.style.setProperty('height', '45px', 'important'); });
                        $(this.api().table().container()).find('.dt-layout-table').removeClass('px-4');
                        $(this.api().table().container()).find('.dt-scroll-body').addClass('rmvBorder');
                        $(this.api().table().container()).find('.dt-layout-table').addClass('btmdtborder');

                        const dtlayoutTE = $('.dt-layout-cell.dt-end').first();
                        dtlayoutTE.addClass('d-flex justify-content-end');
                        dtlayoutTE.prepend('<div id="filterPOVS" name="filter" style="width: 200px" class="form-control bg-white p-0 mx-1">Filter</div>');
                        $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                        $('.loadingScreen').remove();
                        $('#dattableDiv').removeClass('opacity-0');
                    }
                });
            }
        }
    },

    initDetailsDatatable: (data) => {
        detailsDatatable = new DataTable('#ICDetails', {
            data: data,
            layout: {
                // topStart: function () {
                //     return $(dataTableCustomBtn);
                // }
            },
            columns: [
                { data: 'STOCKCODE',  title: 'StockCode' },
                { data: 'proddetails.Description',  title: 'Description' },
                { data: 'calculated_units.inCS',  title: 'in CS',
                    render: function(data, type, row) {
                        if(row.uom.includes("CS")) {
                            return `<span class="pointer">${data}</span>`;
                        } else{
                            return "-";
                        }
                    }
                 },
                { data: 'calculated_units.inIB',  title: 'in IB',
                    render: function(data, type, row) {
                        if(row.uom.includes("IB")) {
                            return `<span class="pointer">${data}</span>`;
                        } else{
                            return "-";
                        }
                    }
                },
                { data: 'calculated_units.inPC',  title: 'in PC',
                    render: function(data, type, row) {
                        if(row.uom.includes("PC")) {
                            return `<span class="pointer">${data}</span>`;
                        } else{
                            return "-";
                        }
                    }
                },
            ],
            columnDefs: [
                { className: "text-start", targets: [ 0, 1 ] },
                { className: "text-center", targets: [ 2, 3, 4 ] },
                // { className: "text-end", targets: [ 4 ] },
                // { className: "text-uppercase", targets: [ 2 ] },
            ],
            scrollCollapse: true,
            // scrollY: '100%',
            // scrollX: '100%',
            "createdRow": function (row, data) {
                $(row).attr('id', data.STOCKCODE);
            },

            "pageLength": 10,
            "lengthChange": false,

            initComplete: function () {
                $(this.api().table().container()).find('#dt-search-1').addClass('p-1 mx-0 dtDetailssearchInput nofocus');
                $(this.api().table().container()).find('.dt-search label').addClass('py-1 px-3 mx-0 dtDetailssearchLabel');
                $(this.api().table().container()).find('#ICDetails_info').css({'font-size':'11px'});
                $(this.api().table().container()).find('.paging_full_numbers').css({'font-size':'10px'});
            }

        });

        // Make cells editable on click
        $('#ICDetails tbody').on('click', 'td', function() {
            if(isEditable){
                let column = $(this).index();
                let row = $(this).closest('tr').index();
                let cell = detailsDatatable.cell(this);
                let value = cell.data();
                let displayedValue = cell.render('display');

                // Replace cell content with an input field
                if(column == 4 || column == 3 || column == 2){
                    console.log(displayedValue);
                    if(displayedValue != '-'){
                        $(this).html(`<input type="text" value="${value}" class="edit-cell text-center ${column} ${row}" style="width: 30px;height: 20px;" oninput="this.value = this.value.replace(/[^0-9]/g, '')">`);
                        // Focus on the input field
                        $(this).find('input').focus();
                    }
                }
            }
        });

        $('#ICDetails tbody').on('blur', '.edit-cell', function() {
            let newValue = parseInt($(this).val());
            let cell = detailsDatatable.cell($(this).closest('td'));

            cell.data(newValue).draw();
        });
    }
}

const ICModal = {
    show: () => {
        $('#invCountMainModal').modal('show');
    },
    enable: (enable) => {
        $('#modalFields input[type="text"]').prop('disabled', !enable);
        $('#modalFields input[type="number"]').prop('disabled', !enable);
        $('#modalFields textarea').prop('disabled', !enable);
    },
    viewMode: async (InvCountData) => {
        ICModal.fill(InvCountData);
        $('#deleteICBtn').show();
        $('#addICBtn').hide();
        $('#editICBtn').show();
        $("#editICBtn").text('Edit Sheet').removeClass('btn-primary').addClass('btn-info');
        $('#confirmIC').hide();
        $('#deleteICBtn').text('Delete Sheet');
        $('#rePrintPage').show();
        $('#cancelEditICBtn').hide();
        

        // ICModal.enable(false);
        ICModal.show();
    },
    fill: async (InvCountData) => {
        var tbody = $(".invCountTbody");
        tbody.empty();
        $('.countID').html(InvCountData.CNTHEADER_ID);
        $('.countUser').html(InvCountData.user.FULLNAME);
        $('.countDate').html(InvCountData.DATECREATED);
        detailsDatatable.clear().rows.add(InvCountData.details).draw();
        // (InvCountData.details).forEach((item, index) => {
        //     var tr = `
        //     <tr>
        //         <th scope="row" class="text-center">${index + 1}</th>
        //         <td>${item["STOCKCODE"]}</td>
        //         <td>${item.proddetails["Description"]}</td>
        //         <td class="text-center">${item.calculated_units["inCS"]}</td>
        //         <td class="text-center">${item.calculated_units["inIB"]}</td>
        //         <td class="text-center">${item.calculated_units["inPC"]}</td>
        //     </tr>`;
        
        //     tbody.append(tr);
        // });
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

function downloadToCSV(jsonArr){
    console.log('clicked');
    const csvData = Papa.unparse(jsonArr); // Convert JSON to CSV
    var today = new Date().toISOString().split('T')[0];

    // Create a blob and trigger download
    const blob = new Blob([csvData], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `ManualCountSheet_${today}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

}

function downloadManualCountSheet(manual){
    if(manual == 1){
        sessionStorage.setItem('printingCNTHeader', selectedMain.CNTHEADER_ID);
        $.ajax({
            url: "/api/setCNTHeader",
            type: "POST",
            data: { CNTHeader: selectedMain.CNTHEADER_ID },
            success: function(response) {
                if (response.success) {
                    console.log(response)
                    // window.open('/print/rr', '_blank'); // Open without RRNum in URL
                    window.open('/print/countsheet/manual', '_blank');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
            }
        });
    } else{
        ajax('api/remCNTHeader', 'GET', null, (response) => { // Success callback
            if (response.success == 1) {
                window.open('/print/countsheet/manual', '_blank');
            } 
        }, (xhr, status, error) => { // Error callback
            console.error('Error:', error);
        });
    }
    
}

function updateCount(updated) {
    var SKUList = getUpdatedSKUs(originalSelected, updated)
    let userData = localStorage.getItem('user');
    let user = JSON.parse(userData);

    Swal.fire({
        text: "Please wait... Preparing data...",
        timerProgressBar: true,
        allowOutsideClick: false,
        allowEscapeKey: false,  
        allowEnterKey: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
    
    ajax('api/report/v2/countsheet/' + selectedMain.CNTHEADER_ID, 'POST', JSON.stringify({
        data: {
            userID: user.name,
            SKUList
        },
        _method: "PUT"
    }), (response) => { // Success callback
        Swal.close();
        if (response.success) {
            Swal.fire({
                title: "Success!",
                text: response.message,
                icon: "success"
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

function getUpdatedSKUs(originalList, updatedList){
    var oldDetails = originalList.details;

    let updateDetailsItem = updatedList.filter(newItem => {
        // Find the matching item in oldArray by stockcode and headerID
        let oldItem = oldDetails.find(old => 
            old.STOCKCODE == newItem.STOCKCODE && old.CNTDETAILS_ID == newItem.CNTDETAILS_ID
        );

        return oldItem && (
            oldItem.calculated_units.inCS !== newItem.calculated_units.inCS ||
            oldItem.calculated_units.inIB !== newItem.calculated_units.inIB ||
            oldItem.calculated_units.inPC !== newItem.calculated_units.inPC
        );

    });

    $.each(updateDetailsItem, function(index, item) {
        var quantity = 0;
        var csVal = parseInt(item.calculated_units.inCS);
        var ibVal = parseInt(item.calculated_units.inIB);
        var pcVal = parseInt(item.calculated_units.inPC);
        var altUOM = parseInt(item.altUOM);
        var othUOM = parseInt(item.othUOM);
        if(csVal > 0){
            // console.log('csVal:'+csVal+"  altUOM:"+altUOM);
            quantity += csVal*(altUOM);
            // console.log(quantity);
        }
        if(ibVal > 0){
            // console.log('ibVal:'+ibVal+"  altUOM:"+altUOM+"  othUOM:"+othUOM);
            quantity += (altUOM/othUOM)*ibVal;
            // console.log(quantity);
        }
        if(pcVal > 0){
            // console.log('PCVal:'+pcVal);
            quantity += pcVal;
            // console.log(quantity);
        }

        item.convMNLCOUNT = quantity;
    });

    return updateDetailsItem;
    // console.log(updateDetailsItem);
}