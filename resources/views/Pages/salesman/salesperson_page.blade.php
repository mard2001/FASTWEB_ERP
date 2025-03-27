@extends('Layout.layout')

@section('html_title')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://unpkg.com/read-excel-file@5.x/bundle/read-excel-file.min.js"></script>
    <title>Salesman Maintenance</title>
@endsection 

@section('title_header')
    <x-header title="Salesman Maintenance" />
@endsection

@section('table')
<x-table id="salespersonTable">
    <x-slot:td>
    </x-slot:td>
</x-table>
@endsection

@section('modal')
    <x-salesperson_modal>
        <x-slot:form_fields>
            <div class="row h-100">
                <div class="row">
                    <div class="col-6 mb-3" id="EmployeeIDDIV">
                        <label for="EmployeeID">EmployeeID</label>
                        <input disabled type="text" id="EmployeeID" name="EmployeeID" class="form-control bg-white" required placeholder="EmployeeID">
                    </div> 
                </div>
                <div class="row">
                    <div class="col-4 mb-3">
                        <label for="mdCode">mdCode</label>
                        <input disabled type="text" id="mdCode" name="mdCode" class="form-control bg-white needField" required placeholder="mdCode" onkeypress="return /[0-9.]/.test(event.key)">
                    </div> 
                    <div class="col-4 mb-3">
                        <label for="Branch">Branch</label>
                        <input disabled type="text" id="Branch" name="Branch" class="form-control bg-white needField" required placeholder="Branch" maxlength="10">
                    </div> 
                    <div class="col-4 mb-3">
                        <label for="Type">Type</label>
                        <input disabled type="text" id="Type" name="Type" class="form-control bg-white needField" required placeholder="Type" maxlength="1">
                    </div> 
                </div>
                <div class="row">
                    <div class="col-3 mb-3">
                        <label for="Salesperson">Salesperson</label>
                        <input disabled type="text" id="Salesperson" name="Salesperson" class="form-control bg-white needField" required placeholder="Salesperson" maxlength="10">
                    </div>
                    <div class="col-9 mb-3">
                        <label for="Name">Name</label>
                        <input disabled type="text" id="Name" name="Name" class="form-control bg-white needField" required placeholder="Name">
                    </div>
                    <div class="col-6 mb-3">
                        <label for="Warehouse">Warehouse</label>
                        <input disabled type="text" id="Warehouse" name="Warehouse" class="form-control bg-white" required placeholder="Warehouse" maxlength="10">
                    </div>
                    <div class="col-6 mb-3">
                        <label for="SourceWarehouse">Source Warehouse</label>
                        <input disabled type="text" id="SourceWarehouse" name="SourceWarehouse" class="form-control bg-white" required placeholder="SourceWarehouse" maxlength="10">
                    </div>
                    <div class="col-6 mb-3">
                        <label for="ContactNo">ContactNo</label>
                        <input disabled type="text" id="ContactNo" name="ContactNo" class="form-control bg-white" required placeholder="ContactNo" onkeypress="return /[0-9]/.test(event.key)">
                    </div>
                    <div class="col-6 mb-3">
                        <label for="ContactHP">ContactHP</label>
                        <input disabled type="text" id="ContactHP" name="ContactHP" class="form-control bg-white" required placeholder="ContactHP" onkeypress="return /[0-9]/.test(event.key)">
                    </div>
                    <div class="col-12 mb-3">
                        <label for="Addr1">Address1</label>
                        <input disabled type="text" id="Addr1" name="Addr1" class="form-control bg-white" required placeholder="Addr1">
                    </div>
                    <div class="col-12 mb-3">
                        <label for="Addr2">Address2</label>
                        <input disabled type="text" id="Addr2" name="Addr2" class="form-control bg-white" required placeholder="Addr2">
                    </div>
                    <div class="col-12 mb-3">
                        <label for="Addr3">Address3</label>
                        <input disabled type="text" id="Addr3" name="Addr3" class="form-control bg-white" required placeholder="Addr3">
                    </div>
                    <div class="col-12 mb-3">
                        <label for="Addr4">Address4</label>
                        <input disabled type="text" id="Addr4" name="Addr4" class="form-control bg-white" required placeholder="Addr4">
                    </div>
                    <div class="col-4 mb-3">
                        <label for="Group1">Group1</label>
                        <input disabled type="text" id="Group1" name="Group1" class="form-control bg-white" required placeholder="Group1" maxlength="15">
                    </div>
                    <div class="col-4 mb-3">
                        <label for="Group2">Group2</label>
                        <input disabled type="text" id="Group2" name="Group2" class="form-control bg-white" required placeholder="Group2" maxlength="15">
                    </div>
                    <div class="col-4 mb-3">
                        <label for="Group3">Group3</label>
                        <input disabled type="text" id="Group3" name="Group3" class="form-control bg-white" required placeholder="Group3" maxlength="15">
                    </div>
                </div>
            </div>
        </x-slot:form_fields>
    </x-salesperson_modal>

    <div class="modal fade modal-lg" id="uploadCsv">
        <div class="modal-dialog">
            <div class="modal-content w-100">
                <div class="modal-body h-100">
                    <form>
                        <div class="row h-100">
                            <div id="uploaderDiv">
                                <div class="upload-container">
                                    <input class="form-control p-2" type="file" id="formFileMultiple" multiple>
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
                    <button id="uploadBtn2" class="btn btn-primary px-4">Upload</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js" integrity="sha512-dfX5uYVXzyU8+KHqj8bjo7UkOdg18PaOtpa48djpNbZHwExddghZ+ZmzWT06R5v6NSk3ZUfsH6FNEDepLx9hPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/js/salesman/salesman-v2.js') }}"></script>
@endsection