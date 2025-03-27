<div class="modal fade modal-lg text-dark" id="invCountMainModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content w-100 h-100">
            <div class="modal-header py-0">
                <p class="text-nowrap text-primary text-center mx-auto my-0" style="font-size: 2rem; font-weight: bold;">INVENTORY COUNT</p>
            </div>
            <div class="modal-body overflow-auto" style="height: auto; max-height: 75vh;">
                <form id="modalFields">
                    {{ $form_fields }}
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-1 d-flex justify-content-between" id="delprint">
                <div>
                    <button type="button" class="btn btn-sm btn-danger" id="deleteICBtn">Delete Sheet</button>
                    <button type="button" class="btn btn-sm btn-primary" id="rePrintPage" style="display: none;">Print Sheet</button>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-primary text-white" id="confirmIC">Confrim Sheet</button>
                    <button type="button" class="btn btn-sm btn-primary text-white" id="addICBtn">Add Sheet</button>
                    <button type="button" class="btn btn-sm btn-info text-white" id="editICBtn">Edit Sheet</button>
                    <button type="button" class="btn btn-sm btn-danger text-white" id="cancelEditICBtn">Cancel Changes</button>
                    <button type="button" class="btn btn-sm btn-secondary" id="closeICBtn" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>