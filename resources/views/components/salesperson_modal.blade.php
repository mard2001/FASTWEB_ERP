<div class="modal fade modal-lg text-dark" id="salespersonMainModal">
    <div class="modal-dialog">
        <div class="modal-content w-100 h-100">
            <div class="modal-header py-0">
                <p class="text-nowrap text-primary text-center mx-auto my-0" style="font-size: 2rem; font-weight: bold;">SALESMAN DETAILS</p>
            </div>
            <div class="modal-body overflow-auto" style="height: auto; max-height: 75vh;">
                <form id="modalFields">
                    {{ $form_fields }}
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-1 d-flex justify-content-between" id="delprint">
                <div>
                    <button type="button" class="btn btn-sm btn-danger" id="deleteSPBtn">Delete Salesman</button>
                    <button type="button" class="btn btn-sm btn-primary" id="rePrintPage" style="display: none;">Print Details</button>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-primary text-white" id="confirmSP">Confrim Detials</button>
                    <button type="button" class="btn btn-sm btn-primary text-white" id="addSPBtn">Add Salesman</button>
                    <button type="button" class="btn btn-sm btn-info text-white" id="editSPBtn">Edit Details</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>