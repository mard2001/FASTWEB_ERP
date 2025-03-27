var dataTableHolder, selectedData, currentImgurl;
// var globalApi = "https://d6e9-222-127-54-209.ngrok-free.app/";
var globalApi = "https://spc.sfa.w-itsolutions.com/";

var ajaxData, ajaxDataHeader;

$(document).ready(async function () {

    var retrievedUser = localStorage.getItem('dbcon');

    if (retrievedUser) {
        retrievedUser = JSON.parse(retrievedUser);
    } else {

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-primary"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "No Database Config Detected",
            text: "Database operations require proper settings. Set up the configuration to continue.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Set DBConfig",
            cancelButtonText: "Load Default",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = globalApi + 'dbconfig';
                window.NavigationPreloadManager;

            } else if (
                /* Read more about handling dismissals below */
                result.dismiss === Swal.DismissReason.cancel
            ) {
                var dbaccount = {
                    "company": '',
                    "driver": "sqlsrv",
                    "host": '66.42.43.247',
                    "port": '8055',
                    "database": 'FastSFA',
                    "username": 'fastsfa',
                    "password": 'default',
                    "machineIdKey": "default"
                }

                localStorage.setItem('dbcon', JSON.stringify(dbaccount));
                location.reload(true);

            }
        });

        return;
    }

    const hamBurger = document.querySelector(".btn-toggle");

    hamBurger.addEventListener("click", async function () {
        document.querySelector("#sidebar").classList.toggle("expand");

        // Wait for 2 seconds
        // await waitForSeconds(0.2);

        // // Redraw the DataTable
        // if (dataTableHolder) {
        //     dataTableHolder.columns.adjust().draw();

        // } 
    });

    // Function to wait for a specific number of seconds
    async function waitForSeconds(seconds) {
        return new Promise(resolve => setTimeout(resolve, seconds * 1000));
    }
    // Get the pathname part of the URL
    var path = window.location.pathname;

    // Split the path by "/" and get the last segment
    var lastSegment = path.substring(path.lastIndexOf('/') + 1);
    // console.log(lastSegment);

    switch (lastSegment.toLocaleLowerCase()) {
        case 'product':
            returnSideBarItemBaseOnIndex(0);
            break;
        case 'salesman':
            returnSideBarItemBaseOnIndex(1);
            break;
        case 'customer':
            returnSideBarItemBaseOnIndex(2);
            break;
        case 'inventory':
            returnSideBarItemBaseOnIndex(3);
            break;
        case 'picklist':
            returnSideBarItemBaseOnIndex(4);
            break;
        case 'pamasterlist':
            returnSideBarItemBaseOnIndex(5);
            break;

        case 'patarget':
            returnSideBarItemBaseOnIndex(6);
            break;

        case 'invoices':
            returnSideBarItemBaseOnIndex(7);
            break;

        case 'purchase-order':
            returnSideBarItemBaseOnIndex(8);
            break;

        case 'receiving-report':
            returnSideBarItemBaseOnIndex(9);
            break;

    }

    function returnSideBarItemBaseOnIndex(i) {
        var sidebar = $('.sidebar-item').eq(i);
        sidebar.addClass('selectedlink');
        sidebar.find('span').addClass('selectedlinkSpan');
    }

    $('.sidebar-item').hover(
        function () { // On mouse enter
            if (!$(this).hasClass('selectedlink')) {
                $(this).find('img').toggleClass('d-none');
            }
        },
        function () { // On mouse leave
            if (!$(this).hasClass('selectedlink')) {
                $(this).find('img').toggleClass('d-none');
            }
        }
    );

    $(document).on('click', '#csvShowBtn', function () {
        
        $('#dlDropDown').toggleClass('d-none');
        
        // downloadCsv(ajaxData, filename);
    });


    $("#deleteBtn").on("click", async function () {
        if ($(this).text().toLowerCase() == 'cancel') {

            $(this).text('Delete');

            $('#saveEdit').removeClass('btn-primary').addClass('btn-info');
            $('#saveEdit').text('Edit details');
            modalFillField();
            modalDisableField();

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

                    var respond = await apiCommunicationDbChanges(3, selectedData);
                    console.log(JSON.stringify(respond, 0, 2))
                    if (respond.status_response == 1) {
                        modalDisableField();
                        getAllXmlData();
                        $('#editXmlDataModal').modal('hide');

                        Swal.fire({
                            title: "Success!",
                            text: respond.response,
                            icon: "success"
                        });
                    }

                }
            });
        }

    });

    $("#saveEdit").on("click", function () {

        if ($(this).text().toLowerCase() == 'save changes') {
            if (validateModalField()) {
                Swal.fire({
                    title: "Do you want to save the changes?",
                    showDenyButton: true,
                    confirmButtonText: "Yes, save",
                    denyButtonText: `Cancel`
                }).then(async (result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {

                        var response = await update();
                        if (response.status_response == 1) {
                            $('#editXmlDataModal').modal('hide');

                            Swal.fire({
                                title: "Success!",
                                text: response.response,
                                icon: "success"
                            });

                            modalDisableField();
                            getAllXmlData();


                        }
                    }
                });
            }
        }
        else if ($(this).text().toLowerCase() == 'save') {

            if (validateModalField()) {
                Swal.fire({
                    title: "Confirm add data",
                    showDenyButton: true,
                    confirmButtonText: "Yes, save",
                    denyButtonText: `Cancel`
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        var response = await save();

                        if (response.status_response == 1) {
                            $('#editXmlDataModal').modal('hide');

                            Swal.fire({
                                title: "Success!",
                                text: response.response,
                                icon: "success"
                            });

                            modalDisableField();
                            getAllXmlData();
                        }

                    }
                });
            }

        }
        else {

            //make the details editable
            $(this).removeClass('btn-info').addClass('btn-primary');
            $(this).text('Save changes');

            $('#deleteBtn').text('Cancel');

            modalEnableField();
        }
    });

    // $("#getXmlData").on("dblclick", "tbody tr", function () {
    $("#getXmlData").on("click", "tbody tr", function () {

        // console.log($(this).attr('id'));

        selectedData = ajaxData.find(item => item.id == $(this).attr('id'));

        if (!selectedData) {
            selectedData = ajaxData.find(item => item.productID == $(this).attr('id'));
        }

        modalFillField();
        $('#saveEdit').removeClass('btn-primary').addClass('btn-info');
        $('#saveEdit').text('Edit details');
        $('#deleteBtn').text('Delete');
        $('#deleteBtn').removeClass('d-none');

        $('#editXmlDataModal').modal('show');
        modalDisableField();
    });

    $("#uploadImage, #prdImg").on("click", function () {
        $('#imageHolder').click();

    });

    $('#imageHolder').on('change', function () {

        var fileName = $(this).val();
        if (fileName) {

            const file = event.target.files[0];

            if (file) {
                // Create a URL for the image file
                const reader = new FileReader();

                reader.onload = function (e) {

                    // Set the src of the image to the file content
                    $('#prdImg').attr('src', e.target.result).show();
                }

                reader.readAsDataURL(file); // Convert the file into a data URL
            }
        }
    });

    $(document).on('click', '#csvUploadShowBtn', function () {
        $('#uploadCsv').modal('show');
    });

    $(document).on('click', '#addBtn', function () {

        $('#modalFields input[type="text"]').val('');
        $('#modalFields input[type="number"]').val('');
        $('#modalFields textarea').val('');

        modalEnableField();
        $('#editXmlDataModal').modal('show');

        $('#saveEdit').text('Save').addClass('btn-primary').removeClass('btn-info');

        $('#prdImg').attr('src', '');
        $('#imageHolder').val('');

        $('#deleteBtn').addClass('d-none');
        selectedData = null;


    });


    $("#uploadBtn").click(modalUploader);

    var getItemResult = await getAllXmlData();


    $('.loadingScreen').remove();
    $('#dattableDiv').removeClass('opacity-0');

    if (!getItemResult) {
        Swal.fire({
            title: "Database Connection Problem!",
            text: 'Please verify sql connection credentials',
            icon: "warning"
        }).then((result) => {
            window.location.href = globalApi + 'dbconfig';
        });
        //window.location.href = globalApi + 'dbsettings';

    }


});

var modalUploader = async function () {
    var fileInput = $('#formFileMultiple').prop('files');

    //validate all files if csv file and to fileList
    var acceptedFiles = false;
    var appendTable = '';
    for (var i = 0; i < fileInput.length; i++) {

        appendTable += trNew(fileInput[i].name, i);

        var fileExtension = fileInput[i].name.split('.').pop().toLowerCase();

        if (fileExtension == 'csv') {
            acceptedFiles = true;
        } else {

            acceptedFiles = false;
            break;
        }
    }

    if (acceptedFiles) {
        $('#fileListTable').html(appendTable);
        $('#totalFiles').html(fileInput.length);
        $('#totalFile').html(fileInput.length);

        for (let i = 0; i < fileInput.length; i++) {  // Changed var to let
            if (fileInput[i]) {
                //console.log(`Processed index: ${i}`);

                Papa.parse(fileInput[i], {
                    header: true, // Treat the first row as the header
                    dynamicTyping: true,
                    // transform: function (value) {
                    //     return value.trim(); // Trim each field
                    // },
                    complete: async function (results) {

                        const cleanedData = results.data
                            .map(row => Object.fromEntries(
                                Object.entries(row).filter(([, value]) => {
                                    // Only attempt to trim if value is a string
                                    if (typeof value === 'string') {
                                        value = value.trim(); // Trim the string
                                    }
                                    // Return only non-null, non-empty values
                                    return value !== null && value !== '';
                                })
                            ))
                            .filter(row => Object.keys(row).length > 0); // Only keep non-empty objects

                        //console.log(JSON.stringify(cleanedData, null, 2));


                        const updatedData = cleanedData.map(item => {
                            //     delete item.thumbnail;  // Remove the 'age' property
                            //     delete item.token;  // Remove the 'age' property

                            return Object.fromEntries(
                                Object.entries(item).map(([key, value]) => [key, value.toString().toLowerCase() == "null" ? null : value])
                            );
                        });

                        // Call async API function and process response
                        var response = await apiCommunicationDbChanges(1, JSON.stringify(updatedData), 1);

                        var iconResult = `<span class="mdi mdi-alert-circle text-danger resultIcon"></span>`;
                        var insertedResultColor = `text-danger`;

                        if (response.status_response == 1) {
                            iconResult = `<span class="mdi mdi-check-circle text-success resultIcon"></span>`
                            var incrementSuccess = parseInt($('#totalUploadSuccess').val(), 10) || 0; // Parse the value as an integer, default to 0 if NaN
                            incrementSuccess++;

                            $('#totalUploadSuccess').val(incrementSuccess);
                            $('#totalUploadSuccess').text(incrementSuccess);

                            insertedResultColor = 'text-success';


                        } if (response.status_response == 2) {

                            iconResult = `<span class="mdi mdi-alert-circle text-warning resultIcon"></span>`
                            insertedResultColor = 'text-warning';
                        }

                        $("#fileStatus" + i).html(iconResult);  // Use i here to update the correct element
                        $("#insertedStat" + i).html(`${response.total_inserted} / ${response.tatal_entry}`).addClass(insertedResultColor);

                        if (i == fileInput.length - 1) {
                            $('#formFileMultiple').val('');

                            var allResultIcon = $('#fileListTable').find('.resultIcon');
                            var swal = {
                                title: "Success!",
                                text: 'All data successfully Inserted',
                                icon: "success"
                            };

                            allResultIcon.each(function (index, element) {
                                // console.log($(element).attr('class'));

                                if (!$(element).hasClass('text-success')) {
                                    console.log('fail ' + $(element).attr('class'));

                                    swal = {
                                        title: "Warning!",
                                        text: 'Not all data inserted.\nReview uploaded csv content',
                                        icon: "warning"
                                    };

                                    return false;
                                } else {
                                    console.log('passed ' + $(element).attr('class'));

                                }

                            });


                            Swal.fire(swal);
                            getAllXmlData();


                        }
                    },
                    error: function (error) {
                        console.log("Error parsing the file: ", error.message);
                    }
                });
            }
        }


    } else {
        Swal.fire({
            icon: "error",
            title: "Review files",
            text: "Please select csv files only",
        });
    }
};



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

function downloadCsvv1(filename, removeHeader) {
    var headers = Object.keys(ajaxData[0]);
    headers = headers.filter(header => !removeHeader.includes(header));

    // Prepare CSV data with only headers as an array of arrays
    var csvHeader = [headers];

    //console.log(JSON.stringify(ajaxData, null, 2));

    // Generate CSV
    const csv = Papa.unparse(csvHeader);

    // Create a Blob from the CSV string
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    var url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename); // Set the file name
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link); // Remove the link after triggering the download
}

function downloadCsv(data, filename = "data.csv") {

    filename = filename.replace("", "")


    // Convert array to CSV string using PapaParse
    const csv = Papa.unparse(data);

    // Create a Blob with the CSV data
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });

    // Create a link element
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = filename;

    // Trigger the download
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();

    // Clean up
    document.body.removeChild(link);

}


function jsonToFormData(jsonObject) {
    const formData = new FormData();

    for (const key in jsonObject) {
        if (jsonObject.hasOwnProperty(key)) {
            formData.append(key, jsonObject[key]);
        }
    }

    return formData;
}

function modalEnableField() {

    $('#modalFields input[type="text"]').prop('disabled', false);
    $('#modalFields input[type="number"]').prop('disabled', false);
    $('#modalFields input[type="filer"]').prop('disabled', false);
    $('#modalFields textarea').prop('disabled', false);

    var checBox = $('#modalFields input[type="checkbox"]');

    if (checBox.length) {  // Check if the checkbox exists
        checBox.prop('disabled', false);
        $('#dateDelivered').prop('disabled', !checBox.prop('checked'));
    }

}

function modalDisableField() {
    $('#modalFields input[type="text"]').prop('disabled', true);
    $('#modalFields input[type="number"]').prop('disabled', true);
    $('#modalFields textarea').prop('disabled', true);
    $('#modalFields input[type="filer"]').prop('disabled', false);
    $('#modalFields input[type="checkbox"]').prop('disabled', true);

    $('#imageHolder').val('');
    $('#imageHolder').prop('disabled', true);
}

function validateModalField() {
    return $("#modalFields").valid();
}

function isValidJson(variable) {
    if (typeof variable !== 'string') {
        return false; // Not a string
    }
    try {
        JSON.parse(variable); // Try to parse the JSON string
        return true; // If parsing succeeds, it's valid JSON
    } catch (e) {
        return false; // If parsing fails, it's not valid JSON
    }
}

async function loadAutoSuggest(elementAndColumn, autoCompleteElementAndColumn) {
    var retrievedUser = JSON.parse(localStorage.getItem('dbcon'));

    var apidata = {
        conn: retrievedUser
    }

    await $.ajax({
        url: globalApi + 'api/product',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token')
        },
        Accept: 'application/json',
        contentType: 'application/json',
        data: apidata,

        success: async function (response) {
            if (response.status_response == 1) {

                function initializeAutocomplete(selector, labelKey) {
                    $(selector).autocomplete({
                        source: function (request, callback) {

                            // Filter the data based on the user's input (case-insensitive match)
                            const filteredData = response.response.filter(item =>
                                item[labelKey].toLowerCase().includes(request.term.toLowerCase())
                            );

                            // Map the filtered data to the format required for autocomplete
                            callback($.map(filteredData, function (item) {
                                return {
                                    label: item[labelKey], // Display this in the dropdown
                                    ...item                // Spread other properties if necessary
                                };
                            }));
                        },

                        select: function (event, ui) {

                            //prevent the default autocomplete action
                            event.preventDefault();

                            // Set the value of the selected item in the input field
                            autoCompleteElementAndColumn.forEach(data => {
                                // $(data.element).val(ui.item[data.column]);
                                autoFillData(data, ui.item);
                            });

                        },
                        open: function () {

                            // Adjust the width of the autocomplete dropdown
                            $(".ui-autocomplete").width($(selector).width()).css({
                                "background-color": "rgb(13, 110, 253)",
                                "cursor": "pointer",
                                "max-height": "200px", // Set the maximum height of the dropdown
                                "overflow-y": "auto", // Enable vertical scrolling
                                "overflow-x": "hidden" // Prevent horizontal scrolling
                            });

                            // Add Bootstrap classes to the autocomplete menu
                            $(".ui-autocomplete").addClass("list-group").removeClass("ui-widget ui-widget-content ui-corner-all");
                            $(".ui-autocomplete li").addClass("list-group-item autocompleteHover");
                        }
                    });
                }

                // Initialize autocomplete for each element and column
                elementAndColumn.forEach(data => {
                    initializeAutocomplete(data.element, data.column);
                });

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

var customActionButton = `<div class="main-content buttons w-100 overflow-auto d-flex align-items-center px-2" style="font-size: 12px;">
    <div class="btn d-flex justify-content-around px-2 align-items-center me-1" id="addBtn">
        <div class="btnImg me-2" id="addImg">
        </div>
        <span>Add new</span>
    </div>

            <div class="btn me-1 actionBtn" id="csvShowBtn">
                <div class="d-flex justify-content-around px-2 align-items-center">
                    <div class="btnImg me-2" id="dlImg">
                    </div>
                    <span>Download Template</span>
                </div>

                <div id="dlDropDown" class="d-flex flex-column position-absolute mt-4 px-2 d-none border-dark z-1000">
                    <button type="submit" class="btn btn-info btn-info text-white mt-2">Copy</button>
                    <button type="submit" class="btn btn-info btn-info text-white mt-2">Excel</button>
                    <button type="submit" class="btn btn-info btn-info text-white mt-2">PDF</button>
                    <button type="submit" class="btn btn-info btn-info text-white mt-2">CSV</button>
                </div>

            </div>      

    <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="csvUploadShowBtn">
        <div class="btnImg me-2" id="ulImg">
        </div>
        <span>Upload Template</span>
    </div>
</div>`;




$(document).on('input', 'input, textarea', function () {

    const value = $(this).val();
    const allowSlash = $(this).attr('permit-fs') !== undefined;

    // Define the base allowed characters
    let baseRegex = allowSlash ? /[^a-zA-Z0-9./]/g : /[^a-zA-Z0-9.]/g;

    // Filter the value based on the allowed characters
    let filteredValue = value.replace(baseRegex, '');

    // If slashes are allowed, ensure only one slash remains
    if (allowSlash) {
        filteredValue = filteredValue
            .replace(/\/{2,}/g, '/') // Replace multiple slashes with a single slash
            .replace(/(\/.*)\//, '$1'); // Keep only the first slash if multiple exist
    }

    // Update the input value if it has changed
    if (value !== filteredValue) {
        $(this).val(filteredValue);
    }


});

// Set up CSRF token for AJAX
$.ajaxSetup({
    headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('api_token')
    },
});


$(document).ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
    if (jqXHR.status === 401) {
        // Redirect to the login page (or any other page)
        window.location.href = "/login"; // Replace with your desired URL
    }
});