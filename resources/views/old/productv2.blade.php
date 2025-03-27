<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Maintenance</title>

    @include('partials.main_stlyles_links')


</head>

<body>
    <div class="wrapper">

        @include('partials.sidebar_links')
        
        <div class="main">

            <div class="row main-top p-4">
                <div class="text-center">
                    <h1 class="h1">
                        Product Maintenance
                    </h1>
                </div>
            </div>

            @include('partials.main_buttons')

            <div class="w-100 overflow-auto" style="font-size: 14px;">
                <table class="mdl-data-table w-100 rmvBorder" id="getXmlData">
                    <thead class="text-white" style="background-color: #33336F;">
                        <tr>
                            <td class="col">IMAGE</td>
                            <td class="col">STOCKCODE</td>
                            <td class="col">DESCRIPTION</td>
                            <td class="col">PRICE</td>
                            <td class="col">WEIGHT</td>
                            <td class="col">CONFIG</td>
                            <td class="col">METHOD</td>
                            <td class="col">HIGHLIGHT</td>
                            <td class="col">ORIGIN</td>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="modal fade modal-lg" id="editXmlDataModal">
                <div class="modal-dialog">
                    <div class="modal-content w-100">
                        <div class="modal-body h-100">
                            <form id="modalFields">
                                <div class="row h-100">
                                    <div class="col mt-1">
                                        <input type="file" id="imageHolder" style="display:none;" accept="image/*">

                                        <div class="col h-50 mt-1 d-flex justify-content-center align-items-center px-3 py-2">
                                            <div class="container h-100 w-75 my-3 p-2 d-flex justify-content-center align-items-center"
                                                style="border: 4px dashed rgba(45, 45, 45, 0.5); position: relative;">
                                                <img id="prdImg" class="border-0 p-2 h-auto w-100" style="max-width: 200px; max-height: 250px; object-fit: cover;  cursor: pointer; "
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

                                        <div class="col h-50 mt-1">
                                            <label for="prdHighlight">Product Highlight</label>
                                            <textarea disabled class="form-control  bg-white" id="prdHighlight"
                                                placeholder="Highlight" rows="6" style="resize: none;" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col mt-1">
                                        <div class="col mt-2">
                                            <label for="prdPrice">Product Price</label>
                                            <input disabled type="number" id="prdPrice" name="prdPrice" class="form-control bg-white"
                                                required placeholder="Price">
                                        </div>

                                        <div class="col mt-2">
                                            <label for="prdStockCode">Product Stock Code</label>
                                            <input disabled type="text" id="prdStockCode" name="prdStockCode" class="form-control bg-white"
                                                required placeholder="Stockcode">
                                        </div>
                                        <div class="col mt-2">
                                            <label for="prdDescription">Product Description</label>
                                            <input disabled type="text" id="prdDescription" name="prdDescription" class="form-control bg-white"
                                                required placeholder="Description">
                                        </div>
                                        <div class="col mt-2">
                                            <label for="prdMethod">Cooking Method</label>
                                            <input disabled type="text" id="prdMethod" name="prdMethod" class="form-control bg-white"
                                                required placeholder="Application / Cooking Method">
                                        </div>
                                        <div class="col mt-2">
                                            <label for="prdWeight">Product Weight</label>
                                            <input disabled type="text" id="prdWeight" name="prdWeight" class="form-control bg-white"
                                                required placeholder="Weight">
                                        </div>
                                        <div class="col mt-2">
                                            <label for="prdCaseConfig">Case Configuration</label>
                                            <input disabled type="text" id="prdCaseConfig" name="prdCaseConfig" class="form-control bg-white"
                                                required placeholder="Case Configuration">
                                        </div>

                                        <div class="col mt-2">
                                            <label for="prdOrigin">Product Origin</label>
                                            <input disabled type="text" id="prdOrigin" name="prdOrigin" class="form-control bg-white"
                                                required placeholder="Origin">
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


        </div>
    </div>

    @include('partials.main_js_library_links')






</body>

</html>