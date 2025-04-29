<div class="modal fade text-dark" id="stockTransferMainModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content h-100 mx-auto" style="width: 95%;">
            <div class="modal-header py-0">
                <p class="text-nowrap text-primary text-center mx-auto my-0" style="font-size: 2rem; font-weight: bold;">STOCK TRANSFER</p>
            </div>
            <div class="modal-body overflow-auto" style="height: auto;">
                <form id="modalFields">
                    <?php echo e($form_fields); ?>

                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-1 d-flex justify-content-end">
                <div>
                    <button type="button" class="btn btn-sm btn-primary text-white" id="saveSTBtn">Transfer Stocks</button>
                    
                    <button type="button" class="btn btn-sm btn-secondary" id="closeSTBtn" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\FASTERP_2025-03-17\resources\views/components/stockTransfer_modal.blade.php ENDPATH**/ ?>