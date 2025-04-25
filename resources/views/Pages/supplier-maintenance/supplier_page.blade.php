@extends('Layout.layout')

@section('html_title')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://unpkg.com/read-excel-file@5.x/bundle/read-excel-file.min.js"></script>
    <title>Supplier Maintenance</title>
@endsection 

@section('title_header')
    <x-header title="Supplier Maintenance" />
@endsection

@section('table')
<x-table id="supplierTable">
    <x-slot:td>
        <td class="col">Supplier Code</td>
        <td class="col">Supplier Name</td>
        <td class="col">Supplier Type</td>
        <td class="col">Terms Code</td>
        <td class="col">Contact Person</td>
        <td class="col">Contact No.</td>
        <td class="col">Complete Address</td>
        <td class="col">Region</td>
        <td class="col">Province</td>
        <td class="col">Municipality</td>
        <td class="col">City</td>
        <td class="col">Hold Status</td>
        <td class="col">Price Code</td>
        <td class="col">Last Updated</td>
    </x-slot:td>
</x-table>
@endsection

@section('modal')
<style>
    #customerMainModal .modal-body label{
        font-size: 12px;
    }
    #supplierTable thead{
        white-space: nowrap;
    }

    .supplierForm div div label{
        font-size: 10px;
        margin-bottom: 0;
    }
    
    .supplierForm div div input{
        font-size: 13px;
        margin-bottom: 0;
    }
</style>
    <x-supplier_modal>
        <x-slot:form_fields>
            <div class="row h-100 supplierForm">
                <div class="col-3">
                    <div class="mb-3">
                        <label for="SupplierCode">SupplierCode</label>
                        <input disabled type="text" id="SupplierCode" name="SupplierCode" class="form-control bg-white needField" required>
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-3">
                        <label for="SupplierName">Supplier Name</label>
                        <input disabled type="text" id="SupplierName" name="SupplierName" class="form-control bg-white needField" required>
                    </div>    
                </div>
                <div class="col-3">
                    <div class="mb-3">
                        <label for="SupplierType">Supplier Type</label>
                        <input disabled type="text" id="SupplierType" name="SupplierType" class="form-control bg-white" required>
                    </div>
                </div>
                <div class="col-5">
                    <div class="mb-3">
                        <label for="ContactPerson">Contact Person</label>
                        <input disabled type="text" id="ContactPerson" name="ContactPerson" class="form-control bg-white" required>
                    </div>    
                </div>
                <div class="col-4">
                    <div class="mb-3">
                        <label for="ContactNo">Contact Number</label>
                        <input disabled type="text" id="ContactNo" name="ContactNo" class="form-control bg-white" required>
                    </div>    
                </div>
                <div class="col-3">
                    <div class="mb-3">
                        <label for="TermsCode">Terms Code</label>
                        <input disabled type="text" id="TermsCode" name="TermsCode" class="form-control bg-white needField" required>
                    </div>    
                </div>
                <div class="col-4">
                    <div class="mb-3">
                        <label for="address">Region</label>
                        <div id="VSregion" name="filter" style="width: 100%" class="form-control bg-white p-0 mx-1 needField"></div>
                    </div>    
                </div>
                <div class="col-4">
                    <div class="mb-3">
                        <label for="address">Province</label>
                        <div id="VSprovince" name="filter" style="width: 100%" class="form-control bg-white p-0 mx-1 needField"></div>
                    </div>    
                </div>
                <div class="col-4">
                    <div class="mb-3">
                        <label for="address">Municipaliy</label>
                        <div id="VSmunicipality" name="filter" style="width: 100%" class="form-control bg-white p-0 mx-1 needField"></div>
                    </div>    
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label for="CompleteAddress">Home # / Street / Bldg. / Brgy.</label>
                        <input disabled type="text" id="CompleteAddress" name="CompleteAddress" class="form-control bg-white needField" required placeholder="address">
                    </div>    
                </div>
                <div class="col-6">
                    <div class="mb-3">
                        <label for="PriceCode">Price Code</label>
                        <input disabled type="text" id="PriceCode" name="PriceCode" class="form-control bg-white needField" required onkeypress="return /[0-9]/.test(event.key)" maxlength="2">
                    </div>    
                </div>
                <div class="col-6">
                    <div class="mb-3">
                        <label for="holdStatus">Hold Status</label>
                        <input disabled type="text" id="holdStatus" name="holdStatus" class="form-control bg-white needField" required>
                    </div>    
                </div>
            </div>
        </x-slot:form_fields>
    </x-supplier_modal>

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
    <script src="{{ asset('assets/js/supplier-maintenance/supplier-v2.js') }}"></script>
@endsection