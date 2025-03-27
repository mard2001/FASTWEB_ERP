@extends('Layout.layout')

@section('html_title')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<title>Inventory Maintenance</title>
@endsection

@section('title_header')
<x-header title="Inventory Maintenance" />
@endsection

@section('table')

<div class="m-3 d-flex secBtns">
    <button id="deliveredBtn" class="selected px-4 py-2 rounded rounded-0 btn text-primary">Delivered</button>
    <button id="pendingBtn" class="px-4 py-2 rounded rounded-0 btn text-primary">Pending</button>
</div>



<x-table>
    <x-slot:td>
        <td class="col">CustCode</td>
        <td class="col">Customer Name</td>
        <td class="col">Invoice No.</td>
        <td class="col">Invoice Amount</td>
        <td class="col">Invoice Date</td>
        <td class="col">Driver</td>
        <td class="col">Vehicle</td>
        <td class="col">Date Delivered</td>
        <td class="col">Address</td>
    </x-slot:td>
</x-table>


@endsection

@section('modal')
<x-form_modal>
    <x-slot:form_fields>
        <div class="row h-100 fs15">
            <div class="col mt-1 flex-wrap">
                <div class="col mt-2">
                    <label for="custCode">Cust Code</label>
                    <input disabled type="text" id="custCode" name="custCode" class="form-control bg-white"
                        required placeholder="Cust Code">
                </div>

                <div class="col mt-2">
                    <label for="custName">Customer Name</label>
                    <input disabled type="text" id="custName" name="custName" class="form-control bg-white"
                        required placeholder="Customer Name">
                </div>

                <div class="col mt-2">
                    <label for="address">Address</label>
                    <input disabled type="text" id="address" name="address" class="form-control bg-white"
                        required placeholder="Address">
                </div>


            </div>

            <div class="col mt-1">
                <div class="col mt-2">
                    <label for="invoiceNumber">Invoice Number</label>
                    <input disabled type="number" id="invoiceNumber" name="invoiceNumber" class="form-control bg-white"
                        required placeholder="Invoice Number">
                </div>

                <div class="col mt-2">
                    <label for="invoiceAmount">Invoice Amount</label>
                    <input disabled type="number" id="invoiceAmount" name="invoiceAmount" class="form-control bg-white"
                        required placeholder="Invoice Amount">
                </div>

                <div class="col mt-2">
                    <label for="invoiceDate">Invoice Date</label>
                    <input disabled type="text" id="invoiceDate" name="invoiceDate" class="form-control bg-white"
                        required placeholder="Invoice Date">
                </div>

            </div>
        </div>
    </x-slot:form_fields>
</x-form_modal>
@endsection

@section('pagejs')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="{{ asset('assets/js/maintenance_uploader/picklist.js') }}"></script>
<script>
    var loadCustomersData

    $(document).on('click', '#addBtn', function() {

        // Wait for the modal to be shown
        $('#editXmlDataModal').one('shown.bs.modal', function() {
            if (loadCustomersData) {
                loadCustomersData.columns.adjust().draw()
            }
        });




    });

    $(document).ready(async function() {

        $('#invoiceDate').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1901,
            maxYear: parseInt(moment().format('YYYY'), 10),
            autoApply: true,
            locale: {
                format: 'YYYY-MM-DD' // Set the desired date format
            }
        });
        loadCustomerNamesSuggestion();


    });

    async function loadItems() {
        var respond = await getItems();

        if (respond.status_response == 1) {

            if (loadCustomersData) {
                loadCustomersData.clear().draw();
                loadCustomersData.rows.add(respond.response).draw();
            } else {
                //console.log(respond);
                loadCustomersData = $('#itemsTable').DataTable({
                    data: respond.response,
                    columns: [{
                            data: 'stockCode'
                        },
                        {
                            data: 'price'
                        },
                        {
                            data: 'case_con'
                        }
                    ],
                    scrollCollapse: true,
                    scrollY: 'auto',
                    scrollX: '100%',
                    "createdRow": function(row, data) {
                        $(row).attr('id', data.id);
                    },
                    // "columnDefs": [{
                    //     "targets": 0, // Column index (e.g., first column)
                    //     "createdCell": function(td, cellData, rowData, row, col) {
                    //         $(rowData).addClass('d-none'); // Add a class to each cell in this column
                    //     }
                    // }],
                    // "dom": 'lfrtp',
                    dom: 'lfrt',
                    "lengthChange": false,
                    initComplete: function() {
                        $(this.api().table().container()).find('#dt-search-1').addClass('p-1 mx-0 dtsearchInput nofocus mb-2');


                        $(this.api().table().container()).find('.dt-search label').addClass('py-1 px-3 mx-0 dtsearchLabel');
                        $(this.api().table().container()).find('.dt-layout-row').addClass('px-4');
                        $(this.api().table().container()).find('.dt-layout-table').removeClass('px-4');
                        $(this.api().table().container()).find('.dt-scroll-body').addClass('rmvBorder');
                        $(this.api().table().container()).find('.dt-layout-table').addClass('btmdtborder');
                        this.api().columns.adjust().draw();

                        // Select the label element and replace it with a div
                        $('.dt-search label').replaceWith(function() {
                            return $('<div>', {
                                html: $(this).html(),
                                id: $(this).attr('id'),
                                class: $(this).attr('class')
                            });
                        });
                        $(this.api().table().container()).find('.dt-search').addClass('d-flex justify-content-end');
                    }

                });
            }
        }

    }

    async function loadCustomerNamesSuggestion() {
        var retrievedUser = JSON.parse(localStorage.getItem('dbcon'));

        var apidata = {
            conn: retrievedUser
        }

        await $.ajax({
            url: globalApi + 'api/customerGetNames',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('api_token')
            },
            Accept: 'application/json',
            contentType: 'application/json',
            data: apidata,

            success: async function(response) {
                if (response.status_response == 1) {

                    var newArray = response.response.map(item => {
                        return {
                            label: item.custname,
                            ...item // Spread other properties if necessary
                        };
                    });

                    // Initialize autocomplete
                    $("#custName").autocomplete({
                        source: newArray,
                        select: function(event, ui) {

                            $('#address').val(ui.item.address);
                        },
                        open: function() {


                            // // Adjust the width of the autocomplete dropdown
                            $(".ui-autocomplete").width($("#custName").width()).css({
                                "background-color": "rgb(13, 110, 253)",
                                "cursor": "pointer",
                            });


                            // Add Bootstrap classes to the autocomplete menu
                            $(".ui-autocomplete").addClass("list-group").removeClass("ui-widget ui-widget-content ui-corner-all");
                            $(".ui-autocomplete li").addClass("list-group-item autocompleteHover");

                        }


                    });

                }

                // console.log(response);
                return response;

            },
            error: async function(xhr, status, error) {

                console.log(xhr, status, error)

                return xhr, status, error;
            }
        });
    }

    async function getItems() {
        var retrievedUser = JSON.parse(localStorage.getItem('dbcon'));

        var apidata = {
            conn: retrievedUser
        }

        return await $.ajax({
            url: globalApi + 'api/productGetItems',
            type: 'GET',
            Accept: 'application/json',
            contentType: 'application/json',
            data: apidata,

            // data: xmlJson, // Convert the data to JSON format
            success: async function(response) {
                if (response.status_response != 1) {}

                //console.log(response);
                return response;

            },
            error: async function(xhr, status, error) {

                console.log(xhr, status, error)

                return xhr, status, error;
            }
        });
    }
</script>
@endsection