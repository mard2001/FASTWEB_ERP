@extends('Layout.layout')

@section('html_title')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<title>Purchase Order</title>
@endsection

@section('title_header')
<x-header title="Purchase Order" />
@endsection

@section('table')
    <style>
        .fa-minus,
        .fa-plus {
            font-size: 20px;
        }

        .fa-minus:hover,
        .fa-plus:hover {
            font-size: 30px;
            cursor: pointer;
        }

        .secBtns .selected {
            background-color: rgba(23, 162, 184, 0.10);
            border-bottom: 2px solid #0275d8;
        }

        .secBtns button {
            border-bottom: 2px solid transparent;
            border-top: 1px solid transparent;
            border-left: 1px solid transparent;
            border-right: 1px solid transparent;
        }

        .secBtns button:hover {
            background-color: rgba(23, 162, 184, 0.10);
            border-bottom: 2px solid #0275d8;
            border-top: 0.5px solid #0275d8;
            border-left: 0.5px solid #0275d8;
            border-right: 0.5px solid #0275d8;
        }

        .autocompleteHover:hover {
            background-color: #3B71CA;
            cursor: pointer;
        }

        .ui-autocomplete {
            z-index: 9999 !important;
        }

        .ResMWidth {
            width: 300px;
        }

        .poheaderform .row div div label{
            font-size: 0.53em;
            margin-bottom: 0;
        }
        
        .poheaderform .row div div input{
            font-size: 0.68em;
            margin-bottom: 0;
        }

        .poheaderSectionTitle{
            font-size: 0.68em;
            text-wrap: nowrap;
            color: #33336F;
            font-weight: 900;
            text-transform: uppercase;
            padding: 0 10px;
        }

        .pofooterform {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
            font-size: 0.65em;
        }

        .pofooterform th,
        .pofooterform td {
            padding: 2px 15px;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
            height: 35px;
            vertical-align: middle !important;
        }

        .pofooterform thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
        }

        .pofooterform tbody + tbody {
            border-top: 2px solid #dee2e6;
        }

        .pofooterform tbody tr:hover {
            background-color: transparent !important;
            /* cursor: pointer; */
        }
        @media (max-width: 992px) {
            .custom-modal-fullscreen {
                width: 100%;
                height: 100%;
                margin: 0;
                max-width: 100%;
            }

            .custom-modal-fullscreen .modal-content {
                height: 100vh;
                display: flex;
                flex-direction: column;
            }

            #editXmlDataModal .modal-dialog{
                max-width: 80% !important;
                margin: auto;
            }
        }
    </style>

    <x-table id="POHeaderTable">
        <x-slot:td>
            <td class="col">OrderNumber</td>
            <td class="col">PONumber</td>
            <td class="col">Status</td>
            <td class="col">POAccount</td>
            <td class="col">PODate</td>
            <td class="col">OrderPlacer</td>
            <td class="col">Discount</td>
            <td class="col">TotalCost</td>

        </x-slot:td>
    </x-table>

@endsection

@section('modal')
<x-mainModal mainModalTitle="editXmlDataModal" modalDialogClass="modal-xl" modalHeaderTitle="PURCHASE ORDER" modalSubHeaderTitle="All key details related to this purchase order.">
    <x-slot:form_fields>
        <div class="poheaderform">
            <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                <div style="width:100%;"><hr></div>
                <div class="poheaderSectionTitle">REQUISITIONER INFORMATION:</div>
                <div style="width:100%;"><hr></div>
            </div>
            <div class="row mb-2">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="">
                        <label for="requisitioner" class="form-label">REQUISITIONER</label>
                        <input type="text" disabled id="requisitioner" name="requisitioner" class="form-control" required>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="">
                        <label for="requisitioner" class="form-label">SHIP VIA</label>
                        <div id="shipVia" name="shipVia" class="form-control border-0 p-0">Shipper Name</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="">
                        <label for="fob" class="form-label">F.O.B.</label>
                        <input type="text" disabled id="fob" name="fob" class="form-control" required >
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="">
                        <label for="shippingTerms" class="form-label">SHIPPING TERMS</label>
                        <input type="text" disabled id="shippingTerms" name="shippingTerms" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                <div style="width:100%;"><hr></div>
                <div class="poheaderSectionTitle">VENDOR INFORMATION:</div>
                <div style="width:100%;"><hr></div>
            </div>
            <div class="row mb-2">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="">
                        <label for="vendorName" class="form-label">SUPPLIER</label>
                        <div id="vendorName" name="vendorName" class="form-control border-0 p-0">Shipper Name</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="">
                        <label for="VendorContactName" class="form-label">SUPPLIER CONTACT PERSON</label>
                        <input type="text" disabled id="VendorContactName" name="VendorContactName" class="form-control" required>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="">
                        <label for="vendorAddress" class="form-label">SUPPLIER ADDRESS</label>
                        <input type="text" disabled id="vendorAddress" name="vendorAddress" class="form-control" required >
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="">
                        <label for="vendorPhone" class="form-label">SUPPLIER CONTACT NUMBER</label>
                        <input type="text" disabled id="vendorPhone" name="vendorPhone" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                <div style="width:100%;"><hr></div>
                <div class="poheaderSectionTitle">SHIP TO INFORMATION:</div>
                <div style="width:100%;"><hr></div>
            </div>
            <div class="row mb-3">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="">
                        <label for="requisitioner" class="form-label">SHIP TO</label>
                        <div id="shippedToName" name="shippedToName" class="form-control border-0 p-0">Shipper Name</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="">
                        <label for="shippedToContactName" class="form-label">CONSIGNEE</label>
                        <input type="text" disabled id="shippedToContactName" name="shippedToContactName" class="form-control" required>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="">
                        <label for="shippedToAddress" class="form-label">CONSIGNEE ADDRESS</label>
                        <input type="text" disabled id="shippedToAddress" name="shippedToAddress" class="form-control" required >
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="">
                        <label for="shippedToPhone" class="form-label">CONTACT NUMBER</label>
                        <input type="text" disabled id="shippedToPhone" name="shippedToPhone" class="form-control" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-between d-none">

            <div class="col-5">
                <textarea class="form-control" id="exampleFormControlTextarea1 rounded-0" placeholder="Address" rows="2" style="resize: none;"></textarea>
                <input type="text" disabled id="mobile" name="mobile"
                    class="form-control form-control-sm bg-white rounded-0" required placeholder="Mobile">
            </div>

            <div class="col-5 text-end">
                <input type="text" disabled id="date" name="date" readonly
                    class="form-control form-control-sm bg-white rounded-0" required placeholder="Date">

                <input type="text" disabled id="poNumber" name="poNumber"
                    class="form-control form-control-sm bg-white rounded-0" required placeholder="PO #">
            </div>
        </div>

        <div class="row mt-2">
            <div class="d-flex align-items-center px-2">

                <div>
                    <button type="button" class="btn" disabled id="addItems"><span class="mdi mdi-plus-circle"></span> Add Item</button>
                    <!-- <button type="button" class="btn btn-danger btn-sm text-white mx-1" id="itemDelete" disabled>Delete Item</button> -->
                </div>
            </div>

            <x-sub_table id="itemTables" class="">
                <x-slot:td>
                    <td class="col">StockCode</td>
                    <td class="col">Decription</td>
                    <td class="col">Quantity</td>
                    <td class="col">UOM</td>
                    <td class="col">Unit Price</td>
                    <td class="col">Total Price</td>
                    <!-- <td class="col">Action</td> -->
                    <td class="col text-center">
                        Action
                    </td>

                </x-slot:td>
            </x-sub_table>
        </div>

        <div class="row mt-2 mx-0">
            <table class="table pofooterform">
                <tbody>
                    <tr>
                        <td class="col-sm-6 col-md-7 col-lg-9"> Comments or Special Instructions
                        </td>
                        <td class="col">SUB TOTAL: </td>
                        <td id="subTotal" class="col text-end"></td>
                    </tr>
                    <tr>
                        <td rowspan="3" class="p-0">
                            <textarea class="form-control px-2 w-100" id="poComment" rows="5" style="resize: none; margin-top: -6px; height:100px"></textarea>

                        </td>
                        <td>TAX: </td>
                        <td id="taxCost" class="text-end"></td>
                    </tr>
                    <tr class="d-none">
                        <td>OTHER: </td>
                        <td id="others" class="text-end"></td>
                    </tr>
                    <tr>
                        <td>TOTAL ITEM: </td>
                        <td id="totalItemsLabel" class="text-end"></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">TOTAL: </td>
                        <td id="grandTotal" style="font-weight: bold;" class="text-end"></td>
                    </tr>
                </tbody>

            </table>

        </div>

    </x-slot:form_fields>
    <x-slot:modalFooterBtns>
        <div>
            <button type="button" class="btn btn-sm btn-danger" id="deleteBtn">Delete</button>
            <button type="button" class="btn btn-sm btn-primary" id="rePrintPage">Print purchase order</button>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-primary text-white" id="confirmPO">Confrim PO</button>
            <button type="button" class="btn btn-sm btn-primary text-white" id="saveBtn">Save details</button>
            <button type="button" class="btn btn-sm btn-info text-white" id="editBtn">Edit details</button>
            <button type="button" class="btn btn-sm btn-secondary" id="poCloseBtn">Close</button>
        </div>
    </x-slot:modalFooterBtns>
</x-mainModal>

<div class="modal fade" id="itemModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content w-100 h-100">
            <div class="modal-body" style="height: auto; max-height: 75vh;">
                <form id="itemModalFields">
                    <div class="row">
                        <div class="col-sm-6 col-md-4">
                            <span class="sectionTitle">Product Description</span>
                        </div>
                        <div class="col-sm-6 col-md-8">
                            <hr>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="">
                                <label for="OrderStatus" class="form-label">STOCK CODE</label>
                                <div id="StockCode" name="StockCode" class="form-control bg-white p-0 w-100"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="">
                                <label for="OrderStatus" class="form-label">Description</label>
                                <input disabled type="text" id="Decription" name="Decription" class="form-control" required placeholder="Description">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="">
                                <label for="PricePerUnit" class="form-label">Price Per Unit</label>
                                <input disabled type="text" id="PricePerUnit" name="PricePerUnit" class="form-control" required placeholder="Price Per Unit">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="">
                                <label for="TotalPrice" class="form-label">Total Price</label>
                                <input disabled type="text" id="TotalPrice" name="TotalPrice" class="form-control" required placeholder="Total Price">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-md-4">
                            <span class="sectionTitle">Product Quantity</span>
                        </div>
                        <div class="col-sm-6 col-md-8">
                            <hr>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 UOMField" id="CSDiv">
                            <div class="">
                                <label for="CSQuantity" class="form-label">CS Quantity</label>
                                <div class="input-group">
                                    <span class="input-group-text w-25 rounded-0">CS</span>
                                    <input disabled type="number" id="CSQuantity" name="CSQuantity" class="form-control" min="0" onkeypress="return /[0-9]/.test(event.key)">
                                </div>
                            </div>
                        </div>
                        <div class="col-6 UOMField" id="IBDiv">
                            <div class="">
                                <label for="IBQuantity" class="form-label">IB Quantity</label>
                                <div class="input-group">
                                    <span class="input-group-text w-25 rounded-0">IB</span>
                                    <input disabled type="number" id="IBQuantity" name="IBQuantity" class="form-control" min="0" onkeypress="return /[0-9]/.test(event.key)">
                                </div>
                            </div>
                        </div>
                        <div class="col-6 UOMField" id="PCDiv">
                            <div class="">
                                <label for="PCQuantity" class="form-label">PC Quantity</label>
                                <div class="input-group">
                                    <span class="input-group-text w-25 rounded-0">PC</span>
                                    <input disabled type="number" id="PCQuantity" name="PCQuantity" class="form-control" min="0" onkeypress="return /[0-9]/.test(event.key)">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm text-white" id="itemSave">Save Item</button>
                {{-- <button type="button" class="btn btn-info btn-sm text-white" id="itemEdit">Edit Item</button> --}}
                <button type="button" class="btn btn-secondary btn-sm" id="itemCloseBtn">Close</button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade modal modal-lg text-dark" id="newVendorModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content w-100 h-100">
            <div class="modal-body" style="height: auto; max-height: 75vh;">
                <form id="newVendorForm">
                    <div class="row">
                        <div class="col d-flex flex-column">

                            <input type="text" id="SupplierType" name="SupplierType" class="form-control bg-white mt-2 rounded-0"
                                required placeholder="Supplier Type">


                            <input type="text" id="SupplierName" name="SupplierName" class="form-control bg-white mt-2 rounded-0"
                                required placeholder="Supplier Name">

                            <input type="text" id="TermsCode" name="TermsCode" class="form-control bg-white mt-2 rounded-0"
                                required placeholder="Terms Code">


                            <input type="text" id="ContactPerson" name="ContactPerson" class="form-control bg-white mt-2 rounded-0"
                                required placeholder="Contact Person">

                            <input type="text" id="ContactNo" name="ContactNo" class="form-control bg-white mt-2 rounded-0"
                                required placeholder="ContactNo">

                        </div>
                        <div class="col d-flex flex-column">

                            <div id="Region" name="Region" class="form-control bg-white p-0 w-100 mt-2 rounded-0">
                                <input disabled type="text" class="form-control bg-white rounded-0"
                                    required placeholder="Region" readonly>
                            </div>

                            <div id="Province" name="Province" class="form-control bg-white p-0 w-100 mt-2 rounded-0">
                                <input disabled type="text" class="form-control bg-white rounded-0"
                                    required placeholder="Province" readonly>
                            </div>

                            <div id="CityMunicipality" name="CityMunicipality" class="form-control bg-white p-0 w-100 mt-2 rounded-0">
                                <input disabled type="text" class="form-control bg-white rounded-0"
                                    required placeholder="City / Municipality" readonly>
                            </div>

                            <div id="Barangay" name="Barangay" class="form-control bg-white p-0 w-100 mt-2 rounded-0">
                                <input disabled type="text" class="form-control bg-white rounded-0"
                                    required placeholder="Barangay" readonly>
                            </div>
                            <textarea class="form-control mt-2 rounded-0" id="NVCompleteAddress" placeholder="Address" rows="2" style="resize: none;"></textarea>

                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-sm text-white px-3" id="newVendorSaveBtn">Save</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('pagejs')

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

<script type="text/javascript" src="{{ asset('assets/js/vendor/virtual-select.min.js') }}"></script>
<script type="module" src="{{ asset('assets/js/PH_Address/virtualSelectAddresses.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/maintenance_uploader/purchase_order-v2.js') }}"></script>

<script>
    $(document).ready(async function() {

        $("#uploadBtn").click(modalUploader);

        var modalUploader = async function() {
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

                for (let i = 0; i < fileInput.length; i++) { // Changed var to let
                    if (fileInput[i]) {
                        //console.log(`Processed index: ${i}`);

                        Papa.parse(fileInput[i], {
                            header: true, // Treat the first row as the header
                            dynamicTyping: true,
                            // transform: function (value) {
                            //     return value.trim(); // Trim each field
                            // },
                            complete: async function(results) {

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


                                }
                                if (response.status_response == 2) {

                                    iconResult = `<span class="mdi mdi-alert-circle text-warning resultIcon"></span>`
                                    insertedResultColor = 'text-warning';
                                }

                                $("#fileStatus" + i).html(iconResult); // Use i here to update the correct element
                                $("#insertedStat" + i).html(`${response.total_inserted} / ${response.tatal_entry}`).addClass(insertedResultColor);

                                if (i == fileInput.length - 1) {
                                    $('#formFileMultiple').val('');

                                    var allResultIcon = $('#fileListTable').find('.resultIcon');
                                    var swal = {
                                        title: "Success!",
                                        text: 'All data successfully Inserted',
                                        icon: "success"
                                    };

                                    allResultIcon.each(function(index, element) {
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
                            error: function(error) {
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
    });
</script>

@endsection