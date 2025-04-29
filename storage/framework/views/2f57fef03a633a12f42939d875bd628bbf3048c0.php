

<?php $__env->startSection('html_title'); ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <title>Stock Transfer</title>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title_header'); ?>
    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.header','data' => ['title' => 'Stock Transfer']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Stock Transfer']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('table'); ?>
<style>
    .secBtns .selected {
        background-color: rgba(23, 162, 184, 0.10);
        border-bottom: 2px solid #0275d8;
    }

    .secBtns button {
        border-bottom: 2px solid transparent;
        border-top: 1px solid transparent;
        border-left: 1px solid transparent;
        border-right: 1px solid transparent;
    }

    .secBtns button:hover {
        background-color: rgba(23, 162, 184, 0.10);
        border-bottom: 2px solid #0275d8;
        border-top: 0.5px solid #0275d8;
        border-left: 0.5px solid #0275d8;
        border-right: 0.5px solid #0275d8;
    }

    .autocompleteHover:hover {
        background-color: #3B71CA;
        cursor: pointer;
    }

    .ui-autocomplete {
        z-index: 9999 !important;
    }

    .fs15 * {
        font-size: 15px;
    }
</style>

<?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table','data' => ['id' => 'transferTable']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'transferTable']); ?>
     <?php $__env->slot('td', null, []); ?> 
        <td class="col">Transfer Date</td>
        <td class="col">Origin Warehouse</td>
        <td class="col">Destination Warehouse</td>
        <td class="col">StockCode</td>
        <td class="col">Description</td>
        <td class="col">Quantity</td>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('modal'); ?>
<style>
    #editXmlDataModal .modal-dialog {
        width: 70vw !important;
        /* Set width to 90% of viewport width */
        max-width: none !important;
        /* Remove any max-width constraints */
    }

    #editXmlDataModal .modal-content {
        margin: auto !important;
        /* Center the modal content */
    }

    .soTableHeader tbody tr:hover{
        background: transparent
    }

    #SODetails th {
        white-space: nowrap; /* Prevents text from wrapping */
    }

    #itemTables_wrapper #dt-search-1{
        height: 10px;
    }

    .stheaderform .row div div label{
        font-size: 10px;
        margin-bottom: 0;
    }
    
    .stheaderform .row div div input{
        font-size: 13px;
        margin-bottom: 0;
    }

    #customItemSearchBox{
        margin-right: 0.3em;
        width: auto;
        background-color: whitesmoke;
        padding: 5px 10px;
        border: 1px solid #aaa;
        border-radius: 5px;
        font-size: 12px;
    }
    
    .soofooterform {
        width: 100%;
        margin-bottom: 1rem;
        color: #212529;
        border-collapse: collapse;
        font-size: 13px;
    }

    .soofooterform th,
    .soofooterform td {
        padding: 2px 15px;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
        height: 35px;
        vertical-align: middle !important;
    }

    .soofooterform thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
    }

    .soofooterform tbody + tbody {
        border-top: 2px solid #dee2e6;
    }

    .soofooterform tbody tr:hover {
        background-color: transparent !important;
        /* cursor: pointer; */
    }

</style>

<?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stockTransfer_modal','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('stockTransfer_modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('form_fields', null, []); ?> 
        <div class="stheaderform">
            <div class="row">
                <div class="col-6">
                    <div class="">
                        <label for="Reference" class="form-label">TRANSFER REFERENCE</label>
                        <input type="text" disabled class="form-control" id="Reference" name="Reference">
                    </div>
                </div>
                <div class="col-6">
                    <div class="">
                        <label for="EntryDate" class="form-label">TRANSFER DATE</label>
                        <input type="date" disabled id="EntryDate" name="EntryDate" class="form-control" required>
                    </div>
                </div>
                <div class="col-6">
                    <div class="">
                        <label for="Warehouse" class="form-label">ORIGIN WAREHOUSE</label>
                        
                        <div id="VSWarehouse" name="filter" style="width: 100%" class="form-control bg-white p-0 mx-1 needField"></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="">
                        <label for="NewWarehouse" class="form-label">DESTINATION WAREHOUSE</label>
                        
                        <div id="VSNewWarehouse" name="filter" style="width: 100%" class="form-control bg-white p-0 mx-1 needField"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <hr style="margin: 15px 0 0 0"/>
            <span style="font-size: 12px; margin-bottom: 15px;">Items:</span>
            <div class="d-flex align-items-center justify-content-between px-2 fs12 mb-1">
                <button type="button" class="btn btn-primary btn-sm text-white mx-1" id="addItems">Add Item</button>
                <div id="searchBar"></div>
            </div>
            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sub_table','data' => ['id' => 'itemTables','class' => 'fs12 table-bordered']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sub_table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'itemTables','class' => 'fs12 table-bordered']); ?>
                 <?php $__env->slot('td', null, []); ?> 
                    <td class="col">StockCode</td>
                    <td class="col">Description</td>
                    <td class="col">Quantity</td>
                    <td class="col">UOM</td>
                    <!-- <td class="col">Action</td> -->
                    <td class="col text-center">
                        Action
                    </td>
                 <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
        </div>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

<div class="modal fade modal modal-lg text-dark" id="itemModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content w-100 h-100">
            <div class="modal-body" style="height: auto; max-height: 75vh;">
                <form id="itemModalFields">
                    <div class="row h-100">
                        <div class="d-flex justify-content-between">
                            <div class="col">
                                <div class="px-1 py-0 w-100">
                                    <label for="StockCode">StockCode</label>
                                    <div id="StockCode" name="StockCode" class="form-control bg-white p-0 w-100">
                                        <span class="loader d-flex align-self-center" style="height: 15px; width: 15px"></span>
                                    </div>
                                </div>
                                <div class="px-1 py-0 w-100">
                                    <label for="Decription">Description</label>
                                    <input disabled type="text" id="Decription" name="Decription" class="form-control bg-white rounded-0" required placeholder="Decription">
                                </div>
                            </div>
                            <div class="col">
                                <div class="row mx-1 UOMField" id="CSDiv">
                                    <label for="CSQuantity">CS Quantity</label>
                                    <div class="input-group">
                                        <span class="input-group-text w-25 rounded-0">CS</span>
                                        <input disabled type="number" id="CSQuantity" name="CSQuantity" class="form-control bg-white rounded-0" min="0" onkeypress="return /[0-9]/.test(event.key)">
                                        <div class="w-25 d-flex justify-content-evenly align-items-center">
                                            <i class="text-danger fa-solid fa-minus"></i>
                                            <i class="text-primary fa-solid fa-plus"></i>
                                        </div>
                                    </div>
                                    <label id="CSQuantity-error" class="error px-1" for="CSQuantity"></label>
                                </div>

                                <div class="row mx-1 UOMField" id="IBDiv">
                                    <label for="IBQuantity">IB Quantity</label>
                                    <div class="input-group">
                                        <span class="input-group-text w-25 rounded-0">IB</span>
                                        <input disabled type="number" id="IBQuantity" name="IBQuantity" class="form-control bg-white rounded-0" min="0" onkeypress="return /[0-9]/.test(event.key)">
                                        <div class="w-25 d-flex justify-content-evenly align-items-center">
                                            <i class="text-danger fa-solid fa-minus"></i>
                                            <i class="text-primary fa-solid fa-plus"></i>
                                        </div>
                                    </div>
                                    <label id="IBQuantity-error" class="error px-1" for="IBQuantity"></label>
                                </div>

                                <div class="row mx-1 UOMField" id="PCDiv">
                                    <label for="PCQuantity">PC Quantity</label>
                                    <div class="input-group">
                                        <span class="input-group-text w-25 rounded-0">PC</span>
                                        <input disabled type="number" id="PCQuantity" name="PCQuantity" class="form-control bg-white rounded-0" min="0" onkeypress="return /[0-9]/.test(event.key)">
                                        <div class="w-25 d-flex justify-content-evenly align-items-center">
                                            <i class="text-danger fa-solid fa-minus"></i>
                                            <i class="text-primary fa-solid fa-plus"></i>
                                        </div>
                                    </div>
                                    <label id="PCQuantity-error" class="error px-1" for="PCQuantity"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm text-white" id="itemSave">Save Item</button>
                
                <button type="button" class="btn btn-secondary btn-sm" id="itemCloseBtn">Close</button>
            </div>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('pagejs'); ?>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

    <script type="text/javascript" src="<?php echo e(asset('assets/js/vendor/virtual-select.min.js')); ?>"></script>

    <!-- Day.js core -->
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

    <!-- Plugin: relativeTime -->
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>

    <script src="<?php echo e(asset('assets/js/inventory/stockTransfer.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('Layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\FASTERP_2025-03-17\resources\views/Pages/inventory/invTransfer_page.blade.php ENDPATH**/ ?>