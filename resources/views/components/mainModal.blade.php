<div class="modal fade" id={{ $mainModalTitle }} data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog  {{ $modalDialogClass }}">
        <div class="modal-content w-100 h-100">
            <div class="modal-header py-2" style="border-bottom: none">
                <div>
                    <p class="text-nowrap" style="font-size: 1.5em; font-weight: bold; color: #33336F; margin-bottom:-10px !important;">{{ $modalHeaderTitle }}</p>
                    <small style="font-size:0.7em; color:#929292;">{{ $modalSubHeaderTitle }}</small>
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
                {{ $modalFooterBtns }}
            </div>
        </div>
    </div>
</div>