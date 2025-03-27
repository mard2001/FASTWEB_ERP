<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Maintenance</title>
    @include('Links.main_stlyles_links')
</head>

<body>
    <div class="wrapper w-100">
        @include('Components.nav')
        <div class="main">
            <x-header title="Inventory Maintenance" />
            <x-table>
                <x-slot:td>
                    <td class="col">INVENTORYID</td>
                    <td class="col">MDCODE</td>
                    <td class="col">STOCKCODE</td>
                    <td class="col">QUANTITY</td>
                    <td class="col">LASTUPDATED</td>
                    <td class="col">INVSAT</td>
                    <td class="col">SYNCSTAT</td>
                    <td class="col">DATESSTAMP</td>
                    <td class="col">TIMESTAMP</td>
                </x-slot:td>
            </x-table>

            <!-- <div class="btn me-1 actionBtn" id="csvShowBtn">
                <div class="d-flex justify-content-around px-2 align-items-center">
                    <div class="btnImg me-2" id="dlImg">
                    </div>
                    <span>Download Template</span>
                </div>

                <div id="dlDropDown" class="d-flex flex-column position-absolute mt-4 px-2 d-none border-dark z-1000">
                    <button type="submit" class="btn btn-info btn-info text-white mt-2">Copy</button>
                    <button type="submit" class="btn btn-info btn-info text-white mt-2">Excel</button>
                    <button type="submit" class="btn btn-info btn-info text-white mt-2">PDF</button>
                    <button type="submit" class="btn btn-info btn-info text-white mt-2">CSV</button>
                </div>

            </div> -->

            <x-form_modal height="auto">
                <x-slot:form_fields>
                    <div class="row h-100 fs15">
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
                </x-slot:form_fields>
            </x-form_modal>

            @include('Components.uploader_modal')
        </div>
</body>

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
<script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script>

<script src="{{ asset('assets/js/maintenance_uploader/uploadertest.js') }}"></script>
<script src="{{ asset('assets/js/maintenance_uploader/inventory.js') }}"></script>

</html>