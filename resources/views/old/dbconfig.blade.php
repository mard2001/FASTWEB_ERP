<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataBase Config</title>
    @include('partials.main_stlyles_links')

</head>

<body>
    <div class="wrapper">
        @include('Navigation.nav')

        <div class="main p-4" style="font-size: 14px">
            
            <div class="text-center">
                <h1 class="h1">
                    Database Settings
                </h1>
            </div>

            <div class="container w-100 p-0 my-3" style="width: 70% !important;">
                <div class="w-100 BDConfigHeaderBG text-white rounded-top p-2 d-flex justify-content-between align-items-centers">
                    <div class="d-flex align-items-center rounded p-1">
                        <span class="material-symbols-outlined">
                            manage_accounts
                        </span>
                        <span class="align-middle px-1">User</span>
                    </div>

                    <div class="d-flex align-items-center rounded p-1 iconbg" id="userConfigBtn" for="userForm" actiondiv="userConfigActions">
                        <span class="material-symbols-outlined px-1">
                            settings
                        </span>
                        <span class="align-middle px-1">Setup</span>
                    </div>

                </div>

                <form id="userForm" class="p-2">
                    <table class="table table-striped m-0">
                        <tbody>
                            <tr>
                                <td class="col-3 align-middle">FULLNAME</td>
                                <td class="col-9">
                                    <input type="text" class="form-control bg-white" name="fullname" aria-label="fullname" disabled required>

                                </td>
                            </tr>
                            <tr>
                                <td class="col-3 align-middle">CONTACT NO.</td>
                                <td class="col-9">
                                    <input type="text" class="form-control bg-white" name="contact" aria-label="contact" disabled required>

                                </td>
                            </tr>
                            <tr>
                                <td class="col-3 align-middle">EMAIL</td>
                                <td class="col-9">
                                    <input type="text" class="form-control bg-white" name="email" aria-label="email" disabled required>

                                </td>
                            </tr>
                            <tr>
                                <td class="col-3 align-middle">ACCOUNT</td>
                                <td class="col-9">
                                    <input type="text" class="form-control bg-white" name="account" aria-label="account" disabled required>

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>

                <div class="px-3 pb-2 actions" style="display: none;" id="userConfigActions">
                    <div class="row">
                        <div class="col-9 d-flex align-items-center">
                            <span>Status:</span> <span class="px-1">No user saved</span>
                        </div>
                        <div class="col-3 d-flex align-items-center justify-content-end">
                            <button type="button" class="btn btn-primary px-4 mx-1">
                                Save
                            </button>

                            <button type="button" class="btn btn-danger mx-1 setupCancel" for="userConfigActions">
                                Cancel
                            </button>
                        </div>

                    </div>
                </div>

            </div>

            <div class="container w-100 p-0 my-3" style="width: 70% !important;">
                <div class="w-100 BDConfigHeaderBG text-white rounded-top p-2 d-flex justify-content-between align-items-centers">
                    <div class="d-flex align-items-center rounded p-1">
                        <span class="mdi mdi-server-network" style="font-size: 18px;"></span>
                        <span class="align-middle px-1">Server</span>
                    </div>

                    <div class="d-flex align-items-center rounded p-1 iconbg" id="serverConfigBtn" for="serverForm" actiondiv="serverConfigActions">
                        <span class="material-symbols-outlined px-1">
                            settings
                        </span>
                        <span class="align-middle px-1">Setup</span>
                    </div>

                </div>

                <form class="p-2" id="serverForm">
                    <table class="table table-striped m-0">
                        <tbody>
                            <tr>
                                <td class="col-3 align-middle">COMPANY</td>
                                <td class="col-9">
                                    <input type="text" class="form-control bg-white" name="companyname" id="companyname" aria-label="company" disabled required>

                                </td>
                            </tr>
                            <tr>
                                <td class="col-3 align-middle">SERVER IP</td>
                                <td class="col-9">
                                    <input type="text" id="serverIp" class="form-control bg-white" name="serverip" aria-label="serverip" disabled required>

                                </td>
                            </tr>
                            <tr>
                                <td class="col-3 align-middle">DATABASE</td>
                                <td class="col-9">
                                    <input type="text" id="serverDb" class="form-control bg-white" name="database" aria-label="database" disabled required>

                                </td>
                            </tr>
                            <tr>
                                <td class="col-3 align-middle">USERNAME</td>
                                <td class="col-9">
                                    <input type="text" id="serverUser" class="form-control bg-white" name="username" aria-label="username" disabled required>

                                </td>
                            </tr>

                            <tr>
                                <td class="col-3 align-middle">PASSWORD</td>
                                <td class="col-9">
                                    <input type="password" id="serverPass" class="form-control bg-white" name="password     " aria-label="password" disabled required>

                                </td>
                            </tr>
                        </tbody>
                    </table>

                </form>

                <div class="px-3 pb-2 actions" style="display: none;" id="serverConfigActions">
                    <div class="row">
                        <div class="col-9 d-flex align-items-center">
                            <span>Status:</span>
                            <span class="px-2 align-middle" id="conStatus">Disconnected</span>
                            <div id="connResult" class="mx-2 h-100 d-flex align-items-center"></div>
                        </div>
                        <div class="col-3 d-flex align-items-center justify-content-end">
                            <button type="button" class="btn btn-primary px-4 mx-1" id="conTestBtn">
                                Connect
                            </button>

                            <button type="button" class="btn btn-danger mx-1 setupCancel" for="serverConfigActions">
                                Cancel
                            </button>
                        </div>

                    </div>
                </div>

            </div>


        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>

        <script>
            var globalApi = "http://127.0.0.1:8000/";

            $(document).ready(async function() {

                fillcondetails();

                $(".toggle-btn").click(function() {
                    $("#sidebar").toggleClass('expand');
                });

                $(".iconbg").click(function() {
                    var childSpan = $(this).find('span');
                    if (childSpan.eq(1).html().toLocaleLowerCase().trim() == 'setup') {
                        childSpan.eq(0).html('cancel').addClass('text-danger');
                        childSpan.eq(1).html('Cancel');
                        $('#' + $(this).attr('for').toString() + ' input[type="text"]').prop('disabled', false);
                        $('#' + $(this).attr('for').toString() + ' input[type="password"]').prop('disabled', false);
                        $(this).closest('.container').find('.actions').slideDown();

                    } else {
                        childSpan.eq(0).html('manage_accounts').removeClass('text-danger');
                        childSpan.eq(1).html('Setup');
                        $('#' + $(this).attr('for').toString() + ' input[type="text"]').prop('disabled', true);
                        $('#' + $(this).attr('for').toString() + ' input[type="password"]').prop('disabled', true);

                        $(this).closest('.container').find('.actions').slideUp();

                        // then fill old data
                    }
                });


                $(".setupCancel").click(function() {
                    $(this).closest('.container').find('.iconbg').click();
                    fillcondetails();
                });

                $("#conTestBtn").click(async function() {
                    if ($('#serverForm').valid()) {

                        $('#conStatus').addClass('text-primary').html(`Connecting...`);
                        $('#connResult').html('<div class="sharigan" style="height: 40px; width: 40px"></div>');
                        $(this).prop('disabled', true);

                        var dbaccount = {
                            "company": $('#companyname').val(),
                            "driver": "sqlsrv",
                            "host": $('#serverIp').val().split(',')[0] ?? $('#serverIp').val(),
                            "port": $('#serverIp').val().split(',')[1] ?? null,
                            "database": $('#serverDb').val(),
                            "username": $('#serverUser').val(),
                            "password": $('#serverPass').val(),
                        }

                        await ajaxCall('testcon', dbaccount)
                            .then(response => {
                                // Show success message with Swal
                                Swal.fire({
                                    title: "Connection Success",
                                    showDenyButton: true,
                                    showCancelButton: true,
                                    confirmButtonText: "Save",
                                    denyButtonText: "Don't save",
                                    text: "Do you want to save connection?",
                                    icon: "success"
                                }).then((result) => {
                                    if (result.isConfirmed) {

                                        dbaccount.machineIdKey = generatePassword(64);

                                        ajaxCall('registerConn', dbaccount)
                                            .then(response => {
                                                dbaccount.password = response.Hashed;
                                                localStorage.setItem('dbcon', JSON.stringify(dbaccount));

                                                Swal.fire("Saved!", "Connection Configuration Saved.", "success");

                                            })
                                            .catch(error => {
                                                console.error("Error occurred:", error);
                                            });


                                    } else if (result.isDenied) {
                                        Swal.fire("Changes are not saved", "", "info");
                                    }
                                });

                                // Update the UI on success
                                $('#conStatus').removeClass('text-warning').addClass('text-success').html(`Connected!`);
                                $('#connResult').html('<span class="mdi mdi-check-circle text-success"></span>');
                                $("#conTestBtn").prop('disabled', false);

                            })
                            .catch(error => {


                                $('#conStatus').removeClass('text-success').addClass('text-warning').html(error.responseJSON.error.replace('[Microsoft][ODBC Driver 17 for SQL Server]', ''));
                                $('#connResult').html('<span class="mdi mdi-alert-circle text-warning"></span>');
                                $("#conTestBtn").prop('disabled', false);
                            });




                    }

                });

            });


            function fillcondetails() {
                var retrievedConn = localStorage.getItem('dbcon');
                if (retrievedConn) {
                    retrievedConn = JSON.parse(retrievedConn);
                    $('#companyname').val(retrievedConn.company);
                    $('#serverIp').val(retrievedConn.host + ',' + retrievedConn.port);
                    $('#serverDb').val(retrievedConn.database);
                    $('#serverUser').val(retrievedConn.username);
                    $('#serverPass').val(retrievedConn.password);

                    $('#conStatus').addClass('text-success').html(`Connected!`);
                    $('#connResult').html('<span class="mdi mdi-check-circle text-success"></span>');
                }

            }

            function generatePassword(length = 12) {
                const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~`|}{[]:;?><,./-=';
                let password = '';

                for (let i = 0; i < length; i++) {
                    password += chars.charAt(Math.floor(Math.random() * chars.length));
                }

                return password;
            }

            async function ajaxCall(url, body) {
                try {
                    const response = await $.ajax({
                        url: globalApi + 'api/'+ url,
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(body),
                        timeout: 5000,
                        success: function(response) {

                        },
                        error: function(xhr, status, error) {
                            // Handle error in the UI
                            console.log(xhr, status, error);

                            // Return error details
                            return {
                                xhr,
                                status,
                                error
                            };
                        }
                    });

                    // Return response for further processing if needed
                    return response;
                } catch (error) {
                    // Return error if AJAX call fails
                    console.error("AJAX call failed:", error);
                    throw error; // To let the calling function handle it as well
                }
            }
        </script>


    </div>

</body>

</html>