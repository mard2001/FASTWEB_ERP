<!doctype html>
<html lang="en">

<head>
    <title>Sidebar 02</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/material-components-web/14.0.0/material-components-web.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.material.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    <link rel="stylesheet"
        href="{{asset('assets/css/main_style.css')}}">

    <link rel="stylesheet"
        href="{{asset('assets/css/salesman.css')}}">

    <!-- 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"> -->

    <!-- 
    <link rel="stylesheet"
        href="{{secure_asset('assets/css/main_style.css')}}"> -->

</head>

<body>
    <div class="wrapper d-flex align-items w-100">
        <nav id="sidebar">
            <div class="custom-menu">
                <button type="button" id="sidebarCollapse" class="btn btn-primary">
                    <i class="fa fa-bars"></i>
                    <span class="sr-only">Toggle Menu</span>
                </button>
            </div>
            <div class="p-4 pt-5">
                <h5><a href="index.html" class="logo">Maintenance Uploader</a></h5>
                <ul class="list-unstyled components mb-5">
                    <li class="active">
                        <a href="#">Products</a>
                    </li>
                    <li>
                        <a href="#">Salesman</a>
                    </li>
                    <li>
                        <a href="#">Inventory</a>
                    </li>

                    <li>
                        <a href="#">Customer</a>
                    </li>

                </ul>


                <div class="footer">
                    <p><!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                        Copyright &copy;<script>
                            document.write(new Date().getFullYear());
                        </script> All rights reserved </p>
                </div>

            </div>
        </nav>

        <!-- Page Content  -->
        <div id="content" class="p-5 pt-5 overflow-auto">
            <div id="viewTable">
                <button class="btn btn-primary px-4 mt-2" style="font-size: 14px;" id="addBtn"></i>Add new</button>
                <button class="btn btn-primary px-4 mt-2" style="font-size: 14px;" id="csvShowBtn"></i>Download Template</button>
                <button class="btn btn-primary px-4 mt-2" style="font-size: 14px;" id="csvUploadShowBtn"></i>Upload Products</button>
                <div class="w-100 overflow-auto mt-3">
                    <table class="table-bordered" id="getXmlData">
                        <thead>
                            <tr>
                                <td class="col">customerID</td>
                                <td class="col">mdCode</td>
                                <td class="col">custCode</td>
                                <td class="col">custName</td>
                                <td class="col">contactCellNumber</td>
                                <td class="col">contactPerson</td>
                                <td class="col">contactLandline</td>
                                <td class="col">address</td>
                                <td class="col">frequencyCategory</td>
                                <td class="col">mcpDay</td>
                                <td class="col">mcpSchedule</td>
                                <td class="col">geolocation</td>
                                <td class="col">lastUpdated</td>
                                <td class="col">lastPurchase</td>
                                <td class="col">latitude</td>
                                <td class="col">longitude</td>
                                <td class="col">syncstat</td>
                                <td class="col">dates_tamp</td>
                                <td class="col">time_stamp</td>
                                <td class="col">isLockOn</td>
                                <td class="col">priceCode</td>
                                <td class="col">custType</td>
                                <td class="col">isVisit</td>
                                <td class="col">DefaultOrdType</td>
                                <td class="col">CityMunCode</td>
                                <td class="col">REGION</td>
                                <td class="col">PROVINCE</td>
                                <td class="col">MUNICIPALITY</td>
                                <td class="col">BARANGAY </td>
                                <td class="col">Area</td>
                                <td class="col">warehouse</td>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade modal-lg text-dark" id="editXmlDataModal">
        <div class="modal-dialog">
            <div class="modal-content w-100 h-100">
                <div class="modal-body  overflow-auto" style="height: 75vh;">
                    <form id="modalFields">
                        <div class="row h-100">
                            <div class="col mt-1">
                                <input type="file" id="imageHolder" style="display:none;" accept="image/*">

                                <div class="col mt-1 d-flex justify-content-center align-items-center px-3 py-2" style="height: 245px;">
                                    <div class="container h-100 w-75 my-3 p-2 d-flex justify-content-center align-items-center"
                                        style="border: 4px dashed rgba(45, 45, 45, 0.5); position: relative;">
                                        <img id="prdImg" class="border-0 p-2 h-auto w-100" style="max-width: 200px; object-fit: cover;  cursor: pointer; "
                                            src="./uploads/upload.png" alt="">
                                    </div>
                                </div>

                                <div class="w-100 d-flex align-items-center justify-content-center">
                                    <button type="button" class="btn btn-sm btn-primary text-white" id="uploadImage"
                                        type="file">Choose
                                        Image</button>
                                </div>

                                <div class="col mt-2">
                                    <label for="ImmediateHead">Immediate Head</label>
                                    <input disabled type="text" id="ImmediateHead" name="ImmediateHead" class="form-control bg-white"
                                        required placeholder="Immediate Head">
                                </div>

                                <div class="col mt-2">
                                    <div class="row">
                                        <div class="col">

                                            <label for="SalesmanType">Salesman Type</label>
                                            <input disabled type="text" id="SalesmanType" name="SalesmanType" class="form-control bg-white"
                                                required placeholder="Salesman Type">
                                        </div>
                                        <div class="col">

                                            <label for="WarehouseCode">Warehouse Code</label>
                                            <input disabled type="text" id="WarehouseCode" name="WarehouseCode" class="form-control bg-white"
                                                required placeholder="WarehouseCode">
                                        </div>
                                    </div>
                                </div>

                                <div class="col mt-2">
                                    <div class="row">
                                        <div class="col">
                                            <label for="calltime">Call Time</label>
                                            <input disabled type="text" id="calltime" name="calltime" class="form-control bg-white"
                                                required placeholder="Call Time">
                                        </div>
                                        <div class="col">
                                            <label for="EOD">End of Day</label>
                                            <input disabled type="number" id="EOD" name="EOD" class="form-control bg-white"
                                                required placeholder="End of Day">
                                        </div>
                                    </div>
                                </div>

                                <div class="col mt-2">
                                    <label for="loadingCap">Loading Cap</label>
                                    <input disabled type="number" id="loadingCap" name="loadingCap" class="form-control bg-white"
                                        required placeholder="Loading Cap">
                                </div>

                                <div class="col mt-2">
                                    <div class="row">
                                        <div class="col">

                                            <label for="verNumber">Version</label>
                                            <input disabled type="text" id="verNumber" name="verNumber" class="form-control bg-white"
                                                required placeholder="Version Number">
                                        </div>
                                        <div class="col">

                                            <label for="isActive">isActive</label>
                                            <input disabled type="number" id="isActive" name="isActive" class="form-control bg-white"
                                                required placeholder="isActive">
                                        </div>
                                    </div>
                                </div>

                                <div class="col mt-2">
                                    <label for="PhoneSN">PhoneSN</label>
                                    <input disabled type="text" id="PhoneSN" name="PhoneSN" class="form-control bg-white"
                                        required placeholder="PhoneSN">
                                </div>

                            </div>
                            <div class="col mt-1">
                                <div class="col mt-2">
                                    <label for="mdCode">MDCode</label>
                                    <input disabled type="number" id="mdCode" name="mdCode" class="form-control bg-white"
                                        required placeholder="mdCode">
                                </div>

                                <div class="col mt-2">
                                    <label for="mdName">Name</label>
                                    <input disabled type="text" id="mdName" name="mdName" class="form-control bg-white"
                                        required placeholder="Name">
                                </div>

                                <div class="col mt-2">
                                    <label for="mdPassword">Password</label>
                                    <input disabled type="number" id="mdPassword" name="mdPassword" class="form-control bg-white"
                                        required placeholder="Password">
                                </div>

                                <div class="col mt-2">
                                    <label for="contactCellNumber">Contact Mobile</label>
                                    <input disabled type="number" id="contactCellNumber" name="contactCellNumber" class="form-control bg-white"
                                        required placeholder="Contact Mobile">
                                </div>

                                <div class="col mt-2">
                                    <div class="row">
                                        <div class="col">
                                            <label for="mdLevel">Level</label>
                                            <input disabled type="number" id="mdLevel" name="mdLevel" class="form-control bg-white"
                                                required placeholder="Level">
                                        </div>
                                        <div class="col">
                                            <label for="DefaultOrdType">Default Or dType</label>
                                            <input disabled type="text" id="DefaultOrdType" name="DefaultOrdType" class="form-control bg-white"
                                                required placeholder="DefaultOrdType">
                                        </div>
                                    </div>
                                </div>
                                <div class="col mt-2">
                                    <div class="row">
                                        <div class="col">

                                            <label for="mdSalesmancode">Salesman Code</label>
                                            <input disabled type="text" id="mdSalesmancode" name="mdSalesmancode" class="form-control bg-white"
                                                required placeholder="Salesman Code">
                                        </div>
                                        <div class="col">

                                            <label for="siteCode">Site Code</label>
                                            <input disabled type="text" id="siteCode" name="siteCode" class="form-control bg-white"
                                                required placeholder="Site Code">
                                        </div>
                                    </div>
                                </div>

                                <div class="col mt-2">
                                    <div class="row">
                                        <div class="col">
                                            <label for="eodNumber1">End of Day #1</label>
                                            <input disabled type="number" id="eodNumber1" name="eodNumber1" class="form-control bg-white"
                                                required placeholder="End of Day #1">
                                        </div>
                                        <div class="col">

                                            <label for="eodNumber2">End of Day #2</label>
                                            <input disabled type="number" id="eodNumber2" name="eodNumber2" class="form-control bg-white"
                                                required placeholder="End of Day #2">
                                        </div>
                                    </div>
                                </div>

                                <div class="col mt-2">
                                    <div class="row">
                                        <div class="col">

                                            <label for="mdColor">Color Hex</label>
                                            <input disabled type="text" id="mdColor" name="mdColor" class="form-control bg-white"
                                                required placeholder="Color Hex">
                                        </div>
                                        <div class="col">

                                            <label for="priceCode">Price Code</label>
                                            <input disabled type="number" id="priceCode" name="priceCode" class="form-control bg-white"
                                                required placeholder="Price Code">
                                        </div>
                                    </div>
                                </div>

                                <div class="col mt-2">
                                    <div class="row">
                                        <div class="col">
                                            <label for="stkRequired">stkRequired</label>
                                            <input disabled type="number" id="stkRequired" id="stkRequired" class="form-control bg-white"
                                                required placeholder="stkRequired">
                                        </div>
                                        <div class="col">
                                            <label for="StockTakeCL">StockTakeCL</label>
                                            <input disabled type="number" id="StockTakeCL" name="StockTakeCL" class="form-control bg-white"
                                                required placeholder="StockTakeCL">
                                        </div>
                                    </div>

                                </div>



                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info btn-info text-white" id="saveEdit">Edit
                        details</button>
                    <button type="button" class="btn btn-danger" id="deleteBtn">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade modal-lg text-dark" id="uploadCsv">
        <div class="modal-dialog">
            <div class="modal-content w-100">
                <div class="modal-body h-100">
                    <form>
                        <div class="row h-100">

                            <div id="uploaderDiv">
                                <div class="upload-container">
                                    <!-- <input type="file" id="fileInput" class="file-input"> -->
                                    <input class="form-control p-2" type="file" id="formFileMultiple" multiple>
                                    <!-- <button id="uploadBtn" class="upload-btn">Upload</button> -->

                                </div>
                                <div id="uploadStatus" class="upload-status">
                                    <div class="d-flex">
                                        <div class="col-10">
                                            <span style="font-size: 16px;">Uploaded files (<span id="totalFiles"
                                                    class="text-primary">0</span></span>)
                                        </div>
                                        <div style="font-size: 14px;" class="col-2 text-end px-3">
                                            <span id="totalUploadSuccess">0</span>
                                            /
                                            <span id="totalFile">0</span>
                                        </div>
                                    </div>
                                    <hr class="my-1">

                                    <div id="fileListDiv" class="p-1">
                                        <table class="table fs-6">
                                            <tbody id="fileListTable">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">

                    <button id="uploadBtn" class="btn btn-primary px-4">Upload</button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.0/papaparse.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>

    <script>
        (function($) {

            "use strict";

            var fullHeight = function() {

                $('.js-fullheight').css('height', $(window).height());
                $(window).resize(function() {
                    $('.js-fullheight').css('height', $(window).height());
                });

            };
            fullHeight();

            $('#sidebarCollapse').on('click', async function() {
                // Toggle the sidebar active class
                $('#sidebar').toggleClass('active');

                // Wait for 2 seconds
                await waitForSeconds(0.2);

                // Redraw the DataTable
                if (typeof dataTableHolder !== 'undefined' && dataTableHolder != null) {
                    dataTableHolder.draw();
                } else {
                    console.error('DataTable instance not found.');
                }
            });

            // Function to wait for a specific number of seconds
            async function waitForSeconds(seconds) {
                return new Promise(resolve => setTimeout(resolve, seconds * 1000));
            }
        })(jQuery);
    </script>

    <script src="{{ asset('assets/js/maintenance_uploader/masterlist_v3_customer.js') }}"></script>
    <script src="{{ secure_asset('assets/js/maintenance_uploader/masterlist_v3_customer.js') }}"></script>



</body>

</html>