@extends('Layout.layout')

@section('html_title')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://unpkg.com/read-excel-file@5.x/bundle/read-excel-file.min.js"></script>
    <title>Warehouse Maintenance</title>
@endsection 

@section('title_header')
    <x-header title="Warehouse Maintenance" />
@endsection

@section('table')
    <x-table id="whTable">
        <x-slot:td>
            <td class="col">Warehouse</td>
            <td class="col">Warehouse Type</td>
            <td class="col">Warehouse GroupCode</td>
            <td class="col">Warehouse GroupDesc</td>
            <td class="col">Municipality</td>
            <td class="col">Status</td>
            <td class="col">Date Updated</td>
        </x-slot:td>
    </x-table>
@endsection

@section('modal')
    <style>
        #warehouseMainModal .modal-body label{
            font-size: 12px;
        }
        #whTable thead{
            white-space: nowrap;
        }

        .warehouseForm div div label{
            font-size: 10px;
            margin-bottom: 0;
        }
        
        .warehouseForm div div input{
            font-size: 13px;
            margin-bottom: 0;
        }
    </style>

    <x-mainModal mainModalTitle="warehouseMainModal" modalDialogClass="" modalHeaderTitle="WAREHOUSE DETAILS" modalSubHeaderTitle="Manage warehouse data including codes, addresses, and storage status.">
        <x-slot:form_fields>
            <div class="row h-100 warehouseForm">
                <div class="col-6">
                    <div class="mb-3">
                        <label for="Warehouse">Warehouse</label>
                        <input disabled type="text" id="Warehouse" name="Warehouse" class="form-control bg-white needField" required>
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-3">
                        <label for="WHType">Warehouse Type</label>
                        <input disabled type="text" id="WHType" name="WHType" class="form-control bg-white needField" required>
                    </div>    
                </div>
                <div class="col-4">
                    <div class="mb-3">
                        <label for="WHGroupCode">WH Group Code</label>
                        <input disabled type="text" id="WHGroupCode" name="WHGroupCode" class="form-control bg-white" required>
                    </div>
                </div>
                <div class="col-8">
                    <div class="mb-3">
                        <label for="WHGroupDesc">WH Group Description</label>
                        <input disabled type="text" id="WHGroupDesc" name="WHGroupDesc" class="form-control bg-white" required>
                    </div>    
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label for="address">Region</label>
                        <div id="VSregion" name="filter" style="width: 100%" class="form-control bg-white p-0 mx-1 needField"></div>
                    </div>    
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label for="address">Province</label>
                        <div id="VSprovince" name="filter" style="width: 100%" class="form-control bg-white p-0 mx-1 needField"></div>
                    </div>    
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label for="address">Municipaliy</label>
                        <div id="VSmunicipality" name="filter" style="width: 100%" class="form-control bg-white p-0 mx-1 needField"></div>
                    </div>    
                </div>
                <div class="col-6">
                    <div class="mb-3">
                        <label for="Status">Status</label>
                        {{-- <input disabled type="text" id="Status" name="Status" class="form-control bg-white needField" required onkeypress="return /[0-9]/.test(event.key)" maxlength="2"> --}}
                        <select disabled class="form-select" aria-label="Select Status" id="Status" name="Status" required>
                            <option value="A">Active</option>
                        </select>
                    </div>    
                </div>
            </div>
        </x-slot:form_fields>
        <x-slot:modalFooterBtns>
            <div>
                <button type="button" class="btn btn-sm btn-danger" id="deleteWHBtn">Delete Warehouse</button>
                <button type="button" class="btn btn-sm btn-primary" id="rePrintPage" style="display: none;">Print Details</button>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary text-white" id="confirmWH">Confrim Detials</button>
                <button type="button" class="btn btn-sm btn-primary text-white" id="addWHBtn">Add Warehouse</button>
                <button type="button" class="btn btn-sm btn-info text-white" id="editWHBtn">Edit Details</button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </x-slot:modalFooterBtns>
    </x-mainModal>

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
    <script src="{{ asset('assets/js/warehouse/wh.js') }}"></script>
@endsection