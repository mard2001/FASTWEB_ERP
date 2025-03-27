<div class="modal fade modal-lg text-dark" id="editXmlDataModal">
    <div class="modal-dialog">
        <div class="modal-content w-100 h-100">
            <div class="modal-body overflow-auto" style="height: auto; max-height: 75vh;">
                <form id="modalFields">
                    {{ $form_fields }}
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-danger" id="deleteBtn">Delete</button>
                </div>
                <div>
                    <button type="submit" class="btn btn-info btn-info text-white" id="saveEdit">Edit
                        details</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>
</div>