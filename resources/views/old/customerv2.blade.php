<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Maintenance</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />

    @include('partials.main_stlyles_links')


</head>

<body>
    <div class="wrapper">

        @include('partials.sidebar_links')

        <div class="main">


            @include('partials.main_buttons')

            <div class="w-100 overflow-auto" style="font-size: 14px;">
                <table class="mdl-data-table w-100 rmvBorder" id="getXmlData">
                    <thead class="text-white" style="background-color: #33336F;">
                        <tr>
                            <td class="col">CUSTID</td>
                            <td class="col">MDCODE</td>
                            <td class="col">CUSTCODE</td>
                            <td class="col">CUSTNAME</td>
                            <td class="col">CONTACT</td>
                            <td class="col">CONTACTPERSON</td>
                            <td class="col">CONTACTLANDLINE</td>
                            <td class="col">FREQUENCYCATEGORY</td>
                            <td class="col">MCPDAY</td>
                            <td class="col">MCPSSCHEDULE</td>
                            <td class="col">GEOLOCATION</td>
                            <td class="col">LASTUPDATED</td>
                            <td class="col">LASTPURCHASE</td>
                            <td class="col">LATITUDE</td>
                            <td class="col">LONGITUDE</td>
                            <td class="col">SYNCSTAT</td>
                            <td class="col">DATESSTAMP</td>
                            <td class="col">TIMESTAMP</td>
                            <td class="col">ISLOCKON</td>
                            <td class="col">PRICECODE</td>
                            <td class="col">CUSTTYPE</td>
                            <td class="col">ISVISIT</td>
                            <td class="col">DEFAULTORDTYPE</td>
                            <td class="col">CITYMUNCODE</td>
                            <td class="col">REGION</td>
                            <td class="col">PROVINCE</td>
                            <td class="col">MUNICIPALITY</td>
                            <td class="col">BARANGAY</td>
                            <td class="col">AREA</td>
                            <td class="col">WAREHOUSE</td>
                            <td class="col">KASOSYO</td>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="modal fade modal-lg text-dark" id="editXmlDataModal">
                <div class="modal-dialog">
                    <div class="modal-content w-100 h-100">
                        <div class="modal-body  overflow-auto" style="height: 75vh;">
                            <form id="modalFields">
                                <div class="row h-100">
                                    <div class="col mt-1">
                                        <div class="col mt-2">
                                            <div class="row">
                                                <div class="col">
                                                    <label for="customerID">Customer ID</label>
                                                    <input disabled type="number" id="customerID" name="customerID" class="form-control bg-white"
                                                        required placeholder="Customer ID">
                                                </div>
                                                <div class="col">
                                                    <label for="mdCode">MDCode</label>
                                                    <input disabled type="number" id="mdCode" name="mdCode" class="form-control bg-white"
                                                        required placeholder="MDCode">
                                                </div>
                                            </div>

                                        </div>

                                        <div class="col mt-2">
                                            <div class="row">
                                                <div class="col">
                                                    <label for="custCode">Customer Code</label>
                                                    <input disabled type="number" id="custCode" name="custCode" class="form-control bg-white"
                                                        required placeholder="Customer Code">
                                                </div>
                                                <div class="col">
                                                    <label for="contactCellNumber">Contact Mobile</label>
                                                    <input disabled type="number" id="contactCellNumber" name="contactCellNumber" class="form-control bg-white"
                                                        required placeholder="Contact Mobile">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col mt-2">
                                            <label for="custName">Customer Name</label>
                                            <input disabled type="text" id="custName" name="custName" class="form-control bg-white"
                                                required placeholder="Customer Name">
                                        </div>

                                        <div class="col mt-2">
                                            <label for="contactPerson">Contact Person</label>
                                            <input disabled type="text" id="contactPerson" name="contactPerson" class="form-control bg-white"
                                                required placeholder="contactPerson">
                                        </div>

                                        <div class="col mt-2">
                                            <label for="contactLandline">Contact Landline</label>
                                            <input disabled type="text" id="contactLandline" name="contactLandline" class="form-control bg-white"
                                                required placeholder="ContactLandline">
                                        </div>

                                        <div class="col mt-2">
                                            <label for="address">Address</label>
                                            <input disabled type="text" id="address" name="address" class="form-control bg-white"
                                                required placeholder="address">
                                        </div>
                                        <div class="col mt-2">
                                            <div class="row">
                                                <div class="col">

                                                    <label for="CityMunCode">City Mun Code</label>
                                                    <input disabled type="text" id="CityMunCode" name="CityMunCode" class="form-control bg-white"
                                                        required placeholder="CityMunCode">
                                                </div>
                                                <div class="col">

                                                    <label for="REGION">REGION</label>
                                                    <input disabled type="number" id="REGION" name="REGION" class="form-control bg-white"
                                                        required placeholder="REGION">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col mt-2">
                                            <div class="row">
                                                <div class="col">
                                                    <label for="PROVINCE">PROVINCE</label>
                                                    <input disabled type="text" id="PROVINCE" id="PROVINCE" class="form-control bg-white"
                                                        required placeholder="PROVINCE">
                                                </div>
                                                <div class="col">
                                                    <label for="MUNICIPALITY">MUNICIPALITY</label>
                                                    <input disabled type="text" id="MUNICIPALITY" name="MUNICIPALITY" class="form-control bg-white"
                                                        required placeholder="MUNICIPALITY">
                                                </div>
                                            </div>

                                        </div>

                                        <div class="col mt-2">
                                            <div class="row">
                                                <div class="col">
                                                    <label for="BARANGAY">BARANGAY</label>
                                                    <input disabled type="text" id="BARANGAY" id="BARANGAY" class="form-control bg-white"
                                                        required placeholder="BARANGAY">
                                                </div>
                                                <div class="col">
                                                    <label for="Area">Area</label>
                                                    <input disabled type="number" id="Area" name="Area" class="form-control bg-white"
                                                        required placeholder="Area">
                                                </div>
                                            </div>

                                        </div>

                                        <div class="col mt-2">
                                            <div class="row">
                                                <div class="col">
                                                    <label for="warehouse">Warehouse</label>
                                                    <input disabled type="text" id="warehouse" id="warehouse" class="form-control bg-white"
                                                        required placeholder="warehouse">
                                                </div>
                                                <div class="col">
                                                    <label for="KASOSYO">KASOSYO</label>
                                                    <input disabled type="text" id="KASOSYO" name="KASOSYO" class="form-control bg-white"
                                                        required placeholder="KASOSYO">
                                                </div>
                                            </div>

                                        </div>

                                        <div class="col mt-2">
                                            <div class="row">

                                                <div class="col">
                                                    <label for="custType">Customer Type</label>
                                                    <input disabled type="text" id="custType" name="custType" class="form-control bg-white"
                                                        required placeholder="custType">
                                                </div>

                                                <div class="col">
                                                    <label for="isVisit">IsVisit</label>
                                                    <input disabled type="text" id="isVisit" name="isVisit" class="form-control bg-white"
                                                        required placeholder="isVisit">
                                                </div>

                                            </div>
                                        </div>



                                    </div>
                                    <div class="col mt-1">

                                        <div class="col mt-2">
                                            <label for="dates_tamp">Date Stamp</label>
                                            <input disabled type="text" id="dates_tamp" name="dates_tamp" class="form-control bg-white"
                                                required placeholder="dates_tamp">
                                        </div>

                                        <div class="col mt-2">
                                            <label for="time_stamp">Time Stamp</label>
                                            <input disabled type="text" id="time_stamp" name="time_stamp" class="form-control bg-white"
                                                required placeholder="time_stamp">
                                        </div>

                                        <div class="col mt-2">
                                            <div class="row">
                                                <div class="col">
                                                    <label for="frequencyCategory">Frequency Category</label>
                                                    <input disabled type="number" id="frequencyCategory" name="frequencyCategory" class="form-control bg-white"
                                                        required placeholder="Frequency Category">
                                                </div>
                                                <div class="col">
                                                    <label for="mcpDay">MCPDay</label>
                                                    <input disabled type="number" id="mcpDay" name="mcpDay" class="form-control bg-white"
                                                        required placeholder="mcpDay">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col mt-2">
                                            <label for="mcpSchedule">MCPSchedule</label>
                                            <input disabled type="text" id="mcpSchedule" name="mcpSchedule" class="form-control bg-white"
                                                required placeholder="mcpSchedule">
                                        </div>

                                        <div class="col mt-2">
                                            <label for="geolocation">Geolocation</label>
                                            <input disabled type="text" id="geolocation" name="geolocation" class="form-control bg-white"
                                                required placeholder="geolocation">
                                        </div>

                                        <div class="col mt-2">
                                            <label for="lastUpdated">Last Updated</label>
                                            <input disabled type="text" id="lastUpdated" name="lastUpdated" class="form-control bg-white"
                                                required placeholder="lastUpdated">
                                        </div>

                                        <div class="col mt-2">
                                            <div class="col">
                                                <label for="lastPurchase">Last Purchase</label>
                                                <input disabled type="text" id="lastPurchase" name="lastPurchase" class="form-control bg-white"
                                                    required placeholder="lastPurchase">
                                            </div>
                                        </div>

                                        <div class="col mt-2">
                                            <div class="row">
                                                <div class="col">
                                                    <label for="syncstat">Sync Status</label>
                                                    <input disabled type="number" id="syncstat" name="syncstat" class="form-control bg-white"
                                                        required placeholder="syncstat">
                                                </div>

                                                <div class="col">
                                                    <label for="priceCode">Price Code</label>
                                                    <input disabled type="text" id="priceCode" name="priceCode" class="form-control bg-white"
                                                        required placeholder="Price Code">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col mt-2">
                                            <div class="row">
                                                <div class="col">
                                                    <label for="latitude">Latitude</label>
                                                    <input disabled type="text" id="latitude" name="latitude" class="form-control bg-white"
                                                        required placeholder="Latitude">
                                                </div>

                                                <div class="col">
                                                    <label for="longitude">Longitude</label>
                                                    <input disabled type="number" id="longitude" name="longitude" class="form-control bg-white"
                                                        required placeholder="Longitude">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col mt-2">
                                            <div class="col">
                                                <label for="baseGPSLat">BaseGPSLat</label>
                                                <input disabled type="text" id="baseGPSLat" name="baseGPSLat" class="form-control bg-white"
                                                    required placeholder="baseGPSLat">
                                            </div>
                                        </div>

                                        <div class="col mt-2">
                                            <div class="row">
                                                <div class="col">
                                                    <label for="isLockOn">IsLockOn</label>
                                                    <input disabled type="text" id="isLockOn" name="isLockOn" class="form-control bg-white"
                                                        required placeholder="isLockOn">
                                                </div>

                                                <div class="col">
                                                    <label for="DefaultOrdType">Default Or DType</label>
                                                    <input disabled type="text" id="DefaultOrdType" name="isVisit" class="form-control bg-white"
                                                        required placeholder="DefaultOrdType">
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

            <div class="modal fade modal-lg" id="uploadCsv">
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
                            <!-- <button type="button" class="btn btn-primary px-4" id="copyCsv">Copy</button>

                        <button type="button" class="btn btn-primary" id="csvSave">Download Csv</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @include('partials.main_js_library_links')

    <script src="{{ asset('assets/js/maintenance_uploader/customer.js') }}"></script>
    <script src="{{ secure_asset('assets/js/maintenance_uploader/customer.js') }}"></script>

</body>

</html>