<div class="modal fade" id="itemModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content w-100 h-100">
            <div class="modal-body" style="height: auto; max-height: 75vh;">
                <form id="itemModalFields">
                    <div class="row">
                        <div class="col-sm-6 col-md-4">
                            <span class="sectionTitle">Product Description</span>
                        </div>
                        <div class="col-sm-6 col-md-8">
                            <hr>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="">
                                <label for="OrderStatus" class="form-label">STOCK CODE</label>
                                <div id="StockCode" name="StockCode" class="form-control bg-white p-0 w-100"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="">
                                <label for="OrderStatus" class="form-label">Description</label>
                                <input disabled type="text" id="Decription" name="Decription" class="form-control" required placeholder="Description">
                            </div>
                        </div>
                        {{ $customFields ?? "" }}
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-md-4">
                            <span class="sectionTitle">Product Quantity</span>
                        </div>
                        <div class="col-sm-6 col-md-8">
                            <hr>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 UOMField" id="CSDiv">
                            <div class="">
                                <label for="CSQuantity" class="form-label">CS Quantity</label>
                                <div class="input-group">
                                    <span class="input-group-text w-25 rounded-0">CS</span>
                                    <input disabled type="number" id="CSQuantity" name="CSQuantity" class="form-control" min="0" onkeypress="return /[0-9]/.test(event.key)">
                                </div>
                            </div>
                        </div>
                        <div class="col-6 UOMField" id="IBDiv">
                            <div class="">
                                <label for="IBQuantity" class="form-label">IB Quantity</label>
                                <div class="input-group">
                                    <span class="input-group-text w-25 rounded-0">IB</span>
                                    <input disabled type="number" id="IBQuantity" name="IBQuantity" class="form-control" min="0" onkeypress="return /[0-9]/.test(event.key)">
                                </div>
                            </div>
                        </div>
                        <div class="col-6 UOMField" id="PCDiv">
                            <div class="">
                                <label for="PCQuantity" class="form-label">PC Quantity</label>
                                <div class="input-group">
                                    <span class="input-group-text w-25 rounded-0">PC</span>
                                    <input disabled type="number" id="PCQuantity" name="PCQuantity" class="form-control" min="0" onkeypress="return /[0-9]/.test(event.key)">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm text-white" id="itemSave">Save Item</button>
                {{-- <button type="button" class="btn btn-info btn-sm text-white" id="itemEdit">Edit Item</button> --}}
                <button type="button" class="btn btn-secondary btn-sm" id="itemCloseBtn">Close</button>
            </div>

        </div>
    </div>
</div>
