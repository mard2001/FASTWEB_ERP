<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XML File Uploader</title>

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
        href="{{asset('assets/css/style.css')}}">

    <link rel="stylesheet"
        href="{{secure_asset('assets/css/style.css')}}">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">

</head>

<body>
    <div class="container">
        <div>
            <h2>FFG PRODUCT UPLOADER
                <!-- <div style="float: right;">
                    <button id="toggleView" class="btn btn-info px-4" style="font-size: 16px;"><i
                            class="fa-solid fa-arrow-right-arrow-left"></i></button>
                </div> -->
            </h2>
        </div>

        <div id="viewTable">
            <button class="btn btn-primary px-4 mt-2" style="font-size: 14px;" id="addBtn"></i>Add new</button>
            <button class="btn btn-primary px-4 mt-2" style="font-size: 14px;" id="csvShowBtn"></i>Download Template</button>
            <button class="btn btn-primary px-4 mt-2" style="font-size: 14px;" id="csvUploadShowBtn"></i>Upload Products</button>

            <div id="dataTableDiv" class="upload-status mt-2" style="font-size: 15px;">
                <table class="w-100 h-100 mdl-data-table" id="getXmlData">
                    <thead>
                        <tr>
                            <td class="col">IMAGE</td>
                            <td class="col">STOCKCODE</td>
                            <td class="col">DESCRIPTION</td>
                            <td class="col">PRICE</td>
                            <td class="col">WEIGHT</td>
                            <td class="col">CASE</td>
                            <td class="col">COOKING METHOD</td>
                            <td class="col">HIGHLIGHT</td>
                            <td class="col">ORIGIN</td>

                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="modal fade modal-lg" id="editXmlDataModal">
            <div class="modal-dialog">
                <div class="modal-content w-100">
                    <div class="modal-body h-100">
                        <form>
                            <div class="row h-100">
                                <div class="col mt-1">
                                    <!-- <label for="editImage">Upload Image</label> -->

                                    <input type="file" id="imageHolder" style="display:none;" accept="image/*">

                                    <div class="col h-50 mt-1 d-flex justify-content-center align-items-center px-3 py-2">
                                        <div class="container h-100 w-75 my-3 p-2 d-flex justify-content-center align-items-center"
                                            style="border: 4px dashed rgba(45, 45, 45, 0.5); position: relative;">
                                            <img id="prdImg" class="border-0 p-2 h-auto w-100" style="max-width: 200px; object-fit: cover;  cursor: pointer; "
                                                src="./uploads/upload.png" alt="">
                                        </div>
                                        <!-- <div style="border-style: dashed; border-radius: 25px;" class="h-100">                                            
                                        </div> -->
                                    </div>

                                    <div class="w-100 d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-sm btn-primary text-white" id="uploadImage"
                                            type="file">Choose
                                            Image</button>

                                    </div>


                                    <div class="col mt-2">
                                        <label for="prdInventoryId">Inventory ID</label>
                                        <input disabled type="text" id="prdInventoryId" class="form-control bg-white"
                                            placeholder="Inventory Id">
                                    </div>
                                    <div class="col mt-2">
                                        <label for="prdMdCode">MD Code</label>
                                        <input disabled type="text" id="prdMdCode" class="form-control bg-white"
                                            placeholder="MD Code">
                                    </div>


                                </div>
                                <div class="col mt-1">
                                    <div class="col mt-2">
                                        <label for="prdStockCode">Stock Code</label>
                                        <input disabled type="text" id="prdStockCode" class="form-control bg-white"
                                            placeholder="Stock Code">
                                    </div>
                                    <div class="col mt-2">
                                        <label for="prdQuantity">Quantity</label>
                                        <input disabled type="text" id="prdQuantity" class="form-control bg-white"
                                            placeholder="Quantity">
                                    </div>

                                    <div class="col t-2">
                                        <label for="prdInvStat">INV Status</label>
                                        <input disabled type="text" id="prdInvStat" class="form-control bg-white"
                                            placeholder="INV Status">
                                    </div>

                                    <div class="col mt-2">
                                        <label for="prdSyncStat">Sync Status</label>
                                        <input disabled type="text" id="prdSyncStat" class="form-control bg-white"
                                            placeholder="Sync Status">
                                    </div>
                                    <div class="col mt-2">
                                        <label for="prdDateStamp">Date Stamp</label>
                                        <input disabled type="text" id="prdOrigin" class="form-control bg-white"
                                            placeholder="Origin">
                                    </div>

                                    <div class="col mt-2">
                                        <label for="prdTimeStamp">Time Stamp</label>
                                        <input disabled type="text" id="prdTimeStamp" class="form-control bg-white"
                                            placeholder="Origin">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-info btn-info text-white" id="saveEdit">Edit
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

        <!-- original v1 -->
        <!-- <div class="modal fade modal-lg" id="editXmlDataModal">
            <div class="modal-dialog">
                <div class="modal-content w-100">
                    <div class="modal-body">
                        <form>
                            <div class="row">
                                <div class="col">
                                    <label for="editXmlId">Product Description</label>
                                    <input disabled type="text" id="editXmlId" class="form-control" placeholder="ID">
                                </div>
                                <div class="col">
                                    <label for="editStockCode">Origin</label>
                                    <input disabled type="text" id="editStockCode" class="form-control"
                                        placeholder="Stock Code">
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col">
                                    <label for="editBrandName">Weight</label>
                                    <input disabled type="text" id="editBrandName" class="form-control"
                                        placeholder="Brand Name">
                                </div>
                                <div class="col">
                                    <label for="editProdDescription">Case Con</label>
                                    <input disabled type="text" id="editProdDescription" class="form-control"
                                        placeholder="Prod Description">
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col">

                                    <label for="editStockUom">Highlight</label>
                                    <textarea disabled class="form-control" id="editStockUom" placeholder="Stock Uom Uom"></textarea>
                                        
                                </div>
                                <div class="col mt-1">
                                    <label for="editAlternamteUom">Cooking Method</label>
                                    <input disabled type="text" id="editAlternamteUom" class="form-control"
                                        placeholder="Alternamte Uom">
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col">
                                    <label for="editConvFactAltUom">ConvFact AltUom</label>
                                    <input disabled type="text" id="editConvFactAltUom" class="form-control"
                                        placeholder="ConvFact AltUom">
                                </div>
                                <div class="col">
                                    <label for="editConvFactOthUom">ConvFact OthUom</label>
                                    <input disabled type="text" id="editConvFactOthUom" class="form-control"
                                        placeholder="ConvFact OthUom">
                                </div>
                            </div>
                        </form>
                    </div>

                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-info btn-info text-white" id="saveEdit">Edit
                            details</button>
                        <button type="button" class="btn btn-danger" id="deleteBtn">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div> -->

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

    <script src="{{ asset('assets/js/maintenance_uploader/masterlist_v3_inventory.js') }}"></script>
    <script src="{{ secure_asset('assets/js/maintenance_uploader/masterlist_v3_inventory.js') }}"></script>


</body>

</html>