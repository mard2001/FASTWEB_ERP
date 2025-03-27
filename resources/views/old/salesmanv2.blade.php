<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Salesman Maintenance</title>

    @include('partials.main_stlyles_links')

</head>

<body>
    <div class="wrapper">

        @include('partials.sidebar_links')

        <div class="main">

            <div class="row main-top p-4">
                <div class="text-center">
                    <h1 class="h1">
                        Salesman Maintenance
                    </h1>
                </div>
            </div>

            @include('partials.main_buttons')

            <div class="w-100 overflow-auto" style="font-size: 14px;">
                <table class="mdl-data-table w-100 rmvBorder" id="getXmlData">
                    <thead class="text-white" style="background-color: #33336F;">
                        <tr>
                            
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="modal fade modal-lg text-dark" id="editXmlDataModal">
                <div class="modal-dialog">
                    <div class="modal-content w-100 h-100">
                        <div class="modal-body  overflow-auto" style="height: 75vh;">
                            <form id="modalFields">

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




</body>

</html>