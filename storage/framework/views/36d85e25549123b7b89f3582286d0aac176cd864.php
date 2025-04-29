<div class="modal fade text-dark" id="warehouseMainModal">
    <div class="modal-dialog ">
        <div class="modal-content w-100 h-100">
            <div class="modal-header py-0">
                <p class="text-nowrap text-primary text-center mx-auto my-0" style="font-size: 2rem; font-weight: bold;">WAREHOUSE DETAILS</p>
            </div>
            <div class="modal-body overflow-auto" style="height: auto; max-height: 75vh;">
                <form id="modalFields">
                    <?php echo e($form_fields); ?>

                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-1 d-flex justify-content-between" id="delprint">
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
            </div>
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\FASTERP_2025-03-17\resources\views/components/warehouse_modal.blade.php ENDPATH**/ ?>