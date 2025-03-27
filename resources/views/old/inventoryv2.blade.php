<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Maintenance</title>

    @include('partials.main_stlyles_links')

</head>

<body>
    <div class="wrapper">

        @include('partials.sidebar_links')

        <div class="main">
            <div class="row main-top p-4">
                <div class="text-center">
                    <h1 class="h1">
                        Inventory Maintenance
                    </h1>
                </div>
            </div>

            @include('partials.main_buttons')

            <div class="w-100 overflow-auto" style="font-size: 14px;">
                <table class="mdl-data-table w-100 rmvBorder" id="getXmlData">
                    <thead class="text-white" style="background-color: #33336F;">
                        <tr>
                            <td class="col">INVENTORYID</td>
                            <td class="col">MDCODE</td>
                            <td class="col">STOCKCODE</td>
                            <td class="col">QUANTITY</td>
                            <td class="col">LASTUPDATED</td>
                            <td class="col">INVSAT</td>
                            <td class="col">SYNCSTAT</td>
                            <td class="col">DATESSTAMP</td>
                            <td class="col">TIMESTAMP</td>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="modal fade modal-lg text-dark" id="editXmlDataModal">
                <div class="modal-dialog">
                    <div class="modal-content w-100 h-100">
                        <div class="modal-body  overflow-auto">
                            <form id="modalFields">
                                <div class="row h-100">
                                    <div class="col mt-1">
                                        <div class="col mt-2">
                                            <label for="inventoryID">Inventory ID</label>
                                            <input disabled type="number" id="inventoryID" name="inventoryID" class="form-control bg-white"
                                                required placeholder="inventoryID">
                                        </div>

                                        <div class="col mt-2">
                                            <label for="mdCode">MD Code</label>
                                            <input disabled type="number" id="mdCode" name="mdCode" class="form-control bg-white"
                                                required placeholder="Md Code">
                                        </div>

                                        <div class="col mt-2">
                                            <label for="stockCode">Stock Code</label>
                                            <input disabled type="number" id="stockCode" name="stockCode" class="form-control bg-white"
                                                required placeholder="Stock Code">
                                        </div>

                                        <div class="col mt-2">
                                            <label for="quantity">Quantity</label>
                                            <input disabled type="number" id="quantity" name="quantity" class="form-control bg-white"
                                                required placeholder="Quantity">
                                        </div>

                                    </div>
                                    <div class="col mt-1">
                                        <div class="col mt-2">
                                            <label for="lastupdated">Last Updated</label>
                                            <input disabled type="text" id="lastupdated" name="lastupdated" class="form-control bg-white"
                                                required placeholder="Last Updated">
                                        </div>

                                        <div class="col mt-2">
                                            <label for="dates_tamp">Dates Stamp</label>
                                            <input disabled type="text" id="dates_tamp" name="dates_tamp" class="form-control bg-white"
                                                required placeholder="Dates Stamp">
                                        </div>
                                        <div class="col mt-2">
                                            <label for="time_stamp">Time Stamp</label>
                                            <input disabled type="text" id="time_stamp" name="time_stamp" class="form-control bg-white"
                                                required placeholder="Time Stamp">
                                        </div>

                                        <div class="col mt-2">
                                            <div class="row">
                                                <div class="col">
                                                    <label for="invstat">Inventory Status</label>
                                                    <input disabled type="number" id="invstat" name="invstat" class="form-control bg-white"
                                                        required placeholder="Inventory Status">
                                                </div>

                                                <div class="col">
                                                    <label for="syncstat">Sync Status</label>
                                                    <input disabled type="number" id="syncstat" name="syncstat" class="form-control bg-white"
                                                        required placeholder="Sync Status">
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

        </div>
    </div>

    @include('partials.main_js_library_links')

    <script src="{{ asset('assets/js/maintenance_uploader/inventory.js') }}"></script>
    <script src="{{ secure_asset('assets/js/maintenance_uploader/inventory.js') }}"></script>


</body>

</html>