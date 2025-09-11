@extends('Layout.layout')

@section('html_title')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://unpkg.com/read-excel-file@5.x/bundle/read-excel-file.min.js"></script>
    <title>Bank Maintenance</title>
@endsection

@section('title_header')
    <x-header title="Bank Maintenance" />
@endsection

@section('table')

    <x-contentButtonDiv addFunc="true" downloadFunc="true" uploadFunc="true"></x-contentButtonDiv>

    <x-table id="bankTable">
        <x-slot:td>
            <td class="col">Bank Name</td>
            <td class="col">Account Name</td>
            <td class="col">Account Number</td>
            <td class="col">Card Number</td>
            <td class="col">Expiration Date</td>
            <td class="col">Status</td>
            <td class="col">Date Created</td>
        </x-slot:td>
    </x-table>
@endsection

@section('modal')
    <x-mainModal mainModalTitle="bankMainModal" modalDialogClass="" modalHeaderTitle="<span style='color: var(--primary-color, #0275d8);'>BANK DETAILS</span>" modalSubHeaderTitle="Manage bank information including account details, card information, and expiration dates.">
        <x-slot:form_fields>
            <div id="itemModalFields">
                <div class="row h-100 bankForm">
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="BankName">Bank Name</label>
                            <input disabled type="text" id="BankName" name="BankName" class="form-control bg-white needField" required maxlength="100">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="AccountName">Account Name</label>
                            <input disabled type="text" id="AccountName" name="AccountName" class="form-control bg-white needField" required maxlength="100" placeholder="Name on the account">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="AccountNumber">Account Number</label>
                            <input disabled type="text" id="AccountNumber" name="AccountNumber" class="form-control bg-white needField" required maxlength="14" placeholder="XXXX-XXXX-XXXX">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="CardNumber">Card Number</label>
                            <input disabled type="text" id="CardNumber" name="CardNumber" class="form-control bg-white" maxlength="19" placeholder="XXXX-XXXX-XXXX-XXXX (Optional)">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="ExpirationDate">Expiration Date</label>
                            <input disabled type="date" id="ExpirationDate" name="ExpirationDate" class="form-control bg-white">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="CCV">CCV</label>
                            <input disabled type="text" id="CCV" name="CCV" class="form-control bg-white" maxlength="4" placeholder="Optional">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <div class="mb-3">
                            <label for="Status">Status</label>
                            <select disabled class="form-select" aria-label="Select Status" id="Status" name="Status" required>
                                <option value="A">Active</option>
                                <option value="I">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </x-slot:form_fields>
        <x-slot:modalFooterBtns>
            <div>
                <button type="button" class="btn btn-sm btn-danger" id="deleteBankBtn">Delete Bank</button>
                <button type="button" class="btn btn-sm btn-primary" id="rePrintPage" style="display: none;">Print Details</button>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary text-white" id="confirmBank">Confirm Details</button>
                <button type="button" class="btn btn-sm btn-primary text-white" id="addBankBtn">Add Bank</button>
                <button type="button" class="btn btn-sm btn-info text-white" id="editBankBtn">Edit Details</button>
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
    <script src="{{ asset('assets/js/bank/bank.js') }}"></script>
@endsection
