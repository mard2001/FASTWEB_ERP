<div class="modal fade modal-lg text-dark" id="editXmlDataModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2" style="border-bottom: none">
                <div>
                    <p class="text-nowrap modalHeaderTitle" style="color: var(--primary-color); font-family: var(--heading-font); font-weight: 600; margin: 0;">User Management</p>
                    <small style="font-size:0.7em; color:#929292;">Manage user accounts and permissions</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body overflow-auto" style="height: auto;">
                <form id="modalFields">
                    {{ $form_fields }}
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-1 d-flex justify-content-between">
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