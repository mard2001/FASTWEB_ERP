@extends('Layout.layout')

@section('html_title')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<title>Invoices</title>
@endsection

@section('title_header')
<x-header title="Invoices" />
@endsection

@section('table')
<style>
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

    .fs15 * {
        font-size: 15px;
    }
</style>

<x-table>
    <x-slot:td>
        <td class="col">Invoice Number</td>
        <td class="col">PO Number</td>
        <td class="col">Account Number</td>
        <td class="col">Due Date</td>
        <td class="col">Invoice Date</td>
        <td class="col">Amount Due</td>
        <td class="col">Plant</td>
        <td class="col">Invoice Date</td>
        <td class="col">Delivery Method</td>
        <td class="col">FileName</td>
    </x-slot:td>
</x-table>

@endsection




@section('modal')

<style>
    #editXmlDataModal .modal-dialog {
        width: 70vw !important;
        /* Set width to 90% of viewport width */
        max-width: none !important;
        /* Remove any max-width constraints */
    }

    #editXmlDataModal .modal-content {
        margin: auto !important;
        /* Center the modal content */
    }
</style>

<x-form_modal>
    <x-slot:form_fields>
        <div class="row h-100 fs15">
            <div class="col mt-1">

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="invoiceNumber">Invoice Number</label>
                            <input disabled type="number" id="invoiceNumber" name="invoiceNumber" class="form-control bg-white"
                                required placeholder="Invoice Number">
                        </div>

                        <div class="col">
                            <label for="PONumber">Purchase Order Number</label>
                            <input disabled type="text" id="PONumber" name="PONumber" class="form-control bg-white"
                                required placeholder="PONumber">
                        </div>
                    </div>
                </div>


                <div class="col mt-2">
                    <label for="SoldTo">Sold To</label>
                    <textarea disabled id="SoldTo" name="SoldTo" class="form-control bg-white"
                        required placeholder="Sold To" rows="2" style="resize: none; font-size: 14px"></textarea>
                </div>


                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="totalAmount">Total Amount (Excl. Tax)</label>
                            <input disabled type="number" id="totalAmount" name="totalAmount" class="form-control bg-white"
                                required placeholder="Total Amount">
                        </div>

                        <div class="col">
                            <label for="totalVat">Total Vat</label>
                            <input disabled type="number" id="totalVat" name="totalVat" class="form-control bg-white"
                                required placeholder="Total Vat">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="amountDue">Amount Due</label>
                            <input disabled type="number" id="amountDue" name="amountDue" class="form-control bg-white"
                                required placeholder="Amount Due">
                        </div>

                        <div class="col">
                            <label for="plant">Plant</label>
                            <input disabled type="text" id="plant" name="plant" class="form-control bg-white"
                                required placeholder="Plant">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="dueDate">Due Date</label>
                            <input disabled type="text" id="dueDate" name="dueDate" class="form-control bg-white"
                                required placeholder="Due Date" readonly>
                        </div>

                        <div class="col">
                            <label for="invoiceDate">Invoice Date</label>
                            <input disabled type="text" id="invoiceDate" name="invoiceDate" class="form-control bg-white"
                                required placeholder="Invoice Date" readonly>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col mt-1">
                <div class="col mt-2">
                    <label for="shippedTo">Shipped To</label>
                    <textarea disabled id="shippedTo" name="shippedTo" class="form-control bg-white"
                        required placeholder="Shipped To" rows="3" style="resize: none; font-size: 14px"></textarea>
                </div>

                <div class="col mt-2">
                    <label for="bankDetails">Bank Details</label>
                    <textarea disabled id="bankDetails" name="bankDetails" class="form-control bg-white"
                        required placeholder="Bank Details" rows="3" style="resize: none; font-size: 14px"></textarea>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="deliveryMethod">Delivery Method</label>
                            <input disabled type="text" id="deliveryMethod" name="deliveryMethod" class="form-control bg-white"
                                required placeholder="Delivery Method">
                        </div>

                        <div class="col">
                            <label for="paymentCollectedBy">Payment Collected By</label>
                            <input disabled type="text" id="paymentCollectedBy" name="paymentCollectedBy" class="form-control bg-white"
                                required placeholder="Invoice Date">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <label for="filename">PDF Filename</label>
                    <input disabled type="text" id="filename" name="filename" class="form-control bg-white"
                        required placeholder="PDF Filename">
                </div>


            </div>
        </div>

        <div class="row mt-3">
            <div class="d-flex justify-content-between align-items-center px-3">
                <div>
                    <label for="itemTables">Invoice Item List</label>
                </div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary btn-sm text-white mx-1" id="addItems">Add Item</button>
                    <button class="btn btn-danger btn-sm text-white mx-1" id="itemDelete" disabled>Delete Item</button>
                </div>
            </div>
            <x-sub_table id="itemTables">
                <x-slot:td>
                    <td class="col">ItemCode</td>
                    <td class="col">ItemDescription</td>
                    <td class="col">Box</td>
                    <td class="col">Quantity</td>
                    <td class="col">UnitPrice</td>
                    <td class="col">Discount</td>
                    <td class="col">NetPrice</td>
                    <td class="col">TotalNetPrice</td>
                    <td class="col"></td>
                </x-slot:td>
            </x-sub_table>
        </div>
    </x-slot:form_fields>
</x-form_modal>

<div class="modal fade modal modal-lg text-dark" id="itemModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content w-100 h-100">
            <div class="modal-body overflow-auto" style="height: auto; max-height: 75vh;">
                <form id="itemModalFields">
                    <div class="row h-100 fs15">
                        <div class="col mt-1">

                            <div class="col mt-2">
                                <div class="row">
                                    <div class="col">
                                        <label for="productCode">ProductCode</label>
                                        <div disabled type="text" id="productCode" name="productCode" class="form-control bg-white p-0"
                                            required placeholder="Product Code">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col mt-2">
                                <div class="row">
                                    <div class="col">
                                        <label for="orderNumber">Order Number</label>
                                        <input disabled type="text" id="orderNumber" name="orderNumber" class="form-control bg-white"
                                            required placeholder="Order Number">
                                    </div>

                                    <div class="col">
                                        <label for="deliveryNumber">Delivery Number</label>
                                        <input disabled type="number" id="deliveryNumber" name="deliveryNumber" class="form-control bg-white"
                                            placeholder="Delivery Number">
                                    </div>
                                </div>
                            </div>

                            <div class="col mt-2">
                                <label for="itemDescription">Item Description</label>
                                <input disabled type="text" id="itemDescription" name="itemDescription" class="form-control bg-white"
                                    required placeholder="Item Description" style="font-size: 14px">
                            </div>

                            <div class="col mt-2">
                                <div class="row">
                                    <div class="col">
                                        <label for="uom">Unit of measurement</label>
                                        <input disabled type="text" id="uom" name="uom" class="form-control bg-white"
                                            required placeholder="Unit of measurement">
                                    </div>

                                    <div class="col">
                                        <label for="subUom">Sub unit of measurement</label>
                                        <input disabled type="text" id="subUom" name="subUom" class="form-control bg-white"
                                            required placeholder="Sub unit of measurement">
                                    </div>
                                </div>
                            </div>



                            <div class="col mt-2">
                                <div class="row">
                                    <div class="col">
                                        <label for="orderDate">Order Date</label>
                                        <input disabled type="text" id="orderDate" name="orderDate" class="form-control bg-white"
                                            readonly required placeholder="Order Date">
                                    </div>

                                    <div class="col">
                                        <label for="deliveryDate">Delivery Date</label>
                                        <input disabled type="text" id="deliveryDate" name="deliveryDate" class="form-control bg-white"
                                            readonly required placeholder="Delivery Date">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col mt-1">

                            <div class="col mt-2">
                                <div class="row">
                                    <div class="col">
                                        <label for="quantity">Quantity</label>
                                        <input disabled type="number" id="quantity" name="quantity" class="form-control bg-white"
                                            required placeholder="Quantity">
                                    </div>

                                    <div class="col">
                                        <label for="totalQuantityInUOM">Total sub unit quantity</label>
                                        <input disabled type="number" id="totalQuantityInUOM" name="totalQuantityInUOM" class="form-control bg-white"
                                            required placeholder="Total sub unit quantity">
                                    </div>
                                </div>
                            </div>

                            <div class="col mt-2">
                                <div class="row">

                                    <div class="col">
                                        <label for="pricePerUnit">Price PerUnit</label>
                                        <input disabled type="number" id="pricePerUnit" name="pricePerUnit" class="form-control bg-white"
                                            required placeholder="Price PerUnit">
                                    </div>


                                    <div class="col">
                                        <label for="netPricePerUnit">Net Price PerUnit</label>
                                        <input disabled type="number" id="netPricePerUnit" name="netPricePerUnit" class="form-control bg-white"
                                            required placeholder="Net Price PerUnit">
                                    </div>

                                </div>
                            </div>

                            <div class="col mt-2">
                                <div class="row">
                                    <div class="col">
                                        <label for="discountPerUnit">Discount Per Unit</label>
                                        <input disabled type="number" id="discountPerUnit" name="discountPerUnit" class="form-control bg-white"
                                            required placeholder="Discount Per Unit">
                                    </div>

                                    <div class="col">
                                        <label for="totalDiscountPerUnit">Total Discount Per Unit</label>
                                        <input disabled type="number" id="totalDiscountPerUnit" name="totalDiscountPerUnit" class="form-control bg-white"
                                            required placeholder="Total Discount Per Unit">
                                    </div>
                                </div>
                            </div>



                            <div class="col mt-2">
                                <div class="col">
                                    <label for="totalPrice">Total Price</label>
                                    <input disabled type="number" id="totalPrice" name="totalPrice" class="form-control bg-white"
                                        required placeholder="Total Price">
                                </div>

                            </div>


                            <div class="col mt-2">

                                <div class="col">
                                    <label for="totalNetPrice">Total Net Price</label>
                                    <input disabled type="number" id="totalNetPrice" name="totalNetPrice" class="form-control bg-white"
                                        required placeholder="Total Net Price">
                                </div>
                            </div>



                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="submit" class="btn btn-info btn-info btn-sm text-white" id="itemSaveEdit">Edit
                    details</button>
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

<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.min.js"></script> -->

<script src="{{ asset('assets/js/maintenance_uploader/invoices.js') }}"></script>
<script>
    $(document).ready(async function() {
        $("#uploadBtn").off("click", modalUploader);

        //modify the function
        modalUploader = async function() {

            //validate all files if csv file and to fileList
            var appendTable = '';
            var file_data = $('#formFileMultiple').prop('files');

            var pdfOnly = true;
            for (var i = 0; i < file_data.length; i++) {

                appendTable += trNew(file_data[i].name, i);
                var fileExtension = file_data[i].name.split('.').pop().toLowerCase();

                if (!fileExtension == 'pdf') {
                    pdfOnly = false;
                    break;
                }
            }

            if (pdfOnly && file_data.length > 0) {

                $('#fileListTable').html(appendTable);
                $('#totalFiles').html(file_data.length);
                $('#totalFile').html(file_data.length);


                for (var i = 0; i < file_data.length; i++) {

                    var fileFormData = new FormData();
                    fileFormData.append('pdf_file', file_data[i]);

                    var convertedtoString = await readpdf(file_data[i]);
                    var retrievedUser = JSON.parse(localStorage.getItem('dbcon'));

                    fileFormData.append('conn', JSON.stringify(retrievedUser));
                    fileFormData.append('extractedString', convertedtoString);



                    // Call async API function and process response
                    var response = await ajaxCall(1, fileFormData);

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
                    // $("#insertedStat" + i).html(`${response.total_inserted} / ${response.tatal_entry}`).addClass(insertedResultColor);

                    if (i == file_data.length - 1) {
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
                                    text: 'Not all data inserted.\nReview uploaded pdf content',
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

                }

            } else {
                Swal.fire({
                    icon: "error",
                    title: "Review files",
                    text: "Please select csv files only",
                });

            }

        };

        $("#uploadBtn").click(modalUploader);

        async function readpdf(file) {

            try {
                const fileReader = new FileReader();

                const typedArray = await new Promise((resolve, reject) => {
                    fileReader.onload = () => resolve(new Uint8Array(fileReader.result));
                    fileReader.onerror = reject;
                    fileReader.readAsArrayBuffer(file);
                });

                const pdf = await pdfjsLib.getDocument(typedArray).promise;
                let pdfText = '';

                for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                    const page = await pdf.getPage(pageNumber);
                    const textContent = await page.getTextContent();

                    textContent.items.forEach(textItem => {
                        if (textItem.str.trim()) { // Skip empty strings
                            pdfText += textItem.str + '\n';
                        }
                    });
                }

                return pdfText;
            } catch (error) {
                console.error('Error occurred:', error);
                return '';
            }

        }

        async function ajaxCall(method, formData = null) {
            switch (method) {
                case 1: // BULK INSERT
                    apiMethod = 'POST';

                    break;
                case 2: // UPDATE DATA
                    apiMethod = 'POST';

                    break;
                case 3: // DELETE DATA
                    apiMethod = 'POST';

                    break;
                case 4: // GET SINGLE DATA VIA ID
                    apiMethod = 'GET';

                    break;
                case 5: // GET ALL DATA
                    apiMethod = 'GET';

                    break;
                case 6: // INSERT DATA
                    apiMethod = 'POST';

                    break;
            }


            return await $.ajax({
                url: globalApi + 'api/upload-po-pdf',
                type: apiMethod,
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token')
                },
                processData: false, // Required for FormData
                contentType: false, // Required for FormData
                data: formData, // Convert the data to JSON format
                // data: xmlJson, // Convert the data to JSON format

                success: async function(response) {
                    console.log(response);

                    if (response.status_response != 1) {

                        //console.log(JSON.stringify(response, null, 2));
                        //console.log(response.extracted_text);



                    }

                    //console.log(response);
                    return response;

                },
                error: async function(xhr, status, error) {


                    // Swal.fire({
                    //     icon: "error",
                    //     title: "Api Error",
                    //     text: JSON.stringify({ xhr, status, error }, null, 2),
                    // });

                    console.log(xhr, status, error)

                    return xhr, status, error;
                }
            });
        }

        async function loadPdfText(pdfUrl) {
            try {
                const pdf = await pdfjsLib.getDocument(pdfUrl).promise;
                let pdfText = '';

                for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                    const page = await pdf.getPage(pageNumber);
                    const textContent = await page.getTextContent();

                    textContent.items.forEach(textItem => {
                        if (textItem.str.charCodeAt(0) !== 32) {
                            pdfText += textItem.str + '\n';
                        }
                    });
                }

                //console.log(pdfText);
                return pdfText;
            } catch (error) {
                console.error('Error occurred:', error);
                return false;
            }
        }




    });
</script>

@endsection