var MainTH, selectedMain;
var globalApi = "http://127.0.0.1:8000/";
var fileCtrTotal = 0;
var insertion = 0;
var jsonArr = [];
var selectedRRCode = '';


// Set up CSRF token for AJAX
$.ajaxSetup({
    headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('api_token')
    },
});

// set up auth error redirect
$(document).ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
    if (jqXHR.status === 401) {
        // Redirect to the login page (or any other page)
        window.location.href = "/login"; // Replace with your desired URL
    }
});

$(document).ready( function () {
    selectedRRCode = sessionStorage.getItem('printingRRCode');
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
    
    if(selectedRRCode){
        ajax('api/report/v2/rr/' + selectedRRCode, 'GET', null, (response) => { // Success callback
            if (response.success == 1) {
                RRPrint.viewMode(response.data);
                selectedMain = response.data;
                // var tempRes = jsonArr.filter(item => item.RRNo == selectedRRCode)
                // RRModal.viewMode(tempRes[0]);
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

const RRPrint = {
    viewMode: async (RRData) => {
        // RRPrint.fill(RRData[0]);
    },
    fill: async (RRPrintData) => {
        console.log(RRPrintData);
    }   
}

