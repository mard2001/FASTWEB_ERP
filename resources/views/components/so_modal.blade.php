<div class="modal fade modal-lg text-dark" id="salesOrderMainModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content w-100 h-100">
            <div class="modal-header py-0">
                <p class="text-nowrap text-primary text-center mx-auto my-0" style="font-size: 2rem; font-weight: bold;">SALES ORDER</p>
            </div>
            <div class="modal-body overflow-auto" style="height: auto; max-height: 75vh;">
                <form id="modalFields">
                    {{ $form_fields }}
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-1 d-flex justify-content-between" id="delprint">
                <div>
                    <button type="button" class="btn btn-sm btn-danger" id="deleteSOBtn">Delete Order</button>
                    <button type="button" class="btn btn-sm btn-primary" id="rePrintPage" style="display: none;">Print Sheet</button>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-primary text-white" id="confirmSO">Confrim Order</button>
                    <button type="button" class="btn btn-sm btn-primary text-white" id="saveSOBtn">Save Details</button>
                    <button type="button" class="btn btn-sm btn-info text-white" id="editSOBtn">Edit Order</button>
                    <button type="button" class="btn btn-sm btn-danger text-white" id="cancelEditSOBtn">Cancel Changes</button>
                    <button type="button" class="btn btn-sm btn-secondary" id="closeSOBtn" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>