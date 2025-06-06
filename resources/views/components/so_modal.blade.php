<div class="modal fade modal-lg text-dark" id="salesOrderMainModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content w-100 h-100">
            <div class="modal-header py-0" style="border-bottom: none">
                <div>
                    <p class="text-nowrap" style="font-size: 1.5em; font-weight: bold; color: #33336F; margin-bottom:-10px;">SALES ORDER</p>
                    <small style="">Sales Order Entry & Details</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body overflow-auto" style="height: auto;">
                <form id="modalFields">
                    {{ $form_fields }}
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-1 d-flex justify-content-between" id="delprint">
                <div>
                    <button type="button" class="btn btn-sm btn-danger" id="deleteSOBtn">Delete Order</button>
                    {{-- <button type="button" class="btn btn-sm btn-primary" id="rePrintPage" style="display: none;">Print Sheet</button> --}}
                    <button type="button" class="btn btn-sm btn-primary text-white statBtns" id="availableSO">Available</button>
                    <button type="button" class="btn btn-sm btn-secondary text-white statBtns" id="unavailableSO">Unavailable</button>
                    <button type="button" class="btn btn-sm btn-primary text-white statBtns" id="restockedSO">Restocked</button>
                    <button type="button" class="btn btn-sm btn-danger text-white statBtns" id="suspenseSO">Suspense Order</button>
                    <button type="button" class="btn btn-sm btn-success text-white statBtns" id="invoiceSO">Proceed to Invoice</button>
                    <button type="button" class="btn btn-sm btn-success text-white statBtns" id="completeSO">Completed Order</button>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-primary text-white" id="saveSOBtn">Save Details</button>
                    <button type="button" class="btn btn-sm btn-info text-white" id="editSOBtn">Edit Order</button>
                    <button type="button" class="btn btn-sm btn-danger text-white" id="cancelEditSOBtn">Cancel Changes</button>
                    <button type="button" class="btn btn-sm btn-secondary" id="closeSOBtn" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>