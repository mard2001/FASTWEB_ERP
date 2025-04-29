

<?php $__env->startSection('html_title'); ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://unpkg.com/read-excel-file@5.x/bundle/read-excel-file.min.js"></script>
    <title>Customer Maintenance</title>
<?php $__env->stopSection(); ?> 

<?php $__env->startSection('title_header'); ?>
    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.header','data' => ['title' => 'Customer Maintenance']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Customer Maintenance']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('table'); ?>
<?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table','data' => ['id' => 'customerTable']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'customerTable']); ?>
     <?php $__env->slot('td', null, []); ?> 
        <td class="col">Customer</td>
        <td class="col">Name</td>
        <td class="col">Salesperson</td>
        <td class="col">PriceCode</td>
        <td class="col">CustomerClass</td>
        <td class="col">Telephone</td>
        <td class="col">Contact</td>
        <td class="col">SoldToAddr1</td>
        <td class="col">SoldToAddr2</td>
        <td class="col">SoldToAddr3</td>
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
    #customerMainModal .modal-body label{
        font-size: 12px;
    }
    #customerTable thead{
        white-space: nowrap;
    }
</style>
    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.cust_modal','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('cust_modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
         <?php $__env->slot('form_fields', null, []); ?> 
            <div class="row h-100">
                <div class="row">
                    <span style="font-size:12px;" class="text-secondary">Salesman Assigned:</span>
                    <div class="col-4">
                        <div class="mb-3">
                            <label for="mdCode">Salesman Code</label>
                            <div id="VSmdCode" name="filter" style="width: 100%" class="form-control bg-white p-0 mx-1 needField">MdCode</div>
                            <input disabled type="text" id="mdCode" name="mdCode" class="form-control bg-white" required placeholder="mdCode">
                        </div> 
                    </div>
                    <div class="col-8">
                        <div class="mb-3">
                            <label for="Salesman">Salesman Name</label>
                            <input disabled type="text" id="Salesman" name="Salesman" class="form-control bg-white" required placeholder="Salesman">
                        </div>    
                    </div>
                </div>
                <hr>
                <div class="row">
                    <span style="font-size:12px;" class="text-secondary">Customer Details:</span>
                    
                    <div class="col-3">
                        <div class="mb-3">
                            <label for="custCode">CustCode</label>
                            <input disabled type="text" id="custCode" name="custCode" class="form-control bg-white needField" required placeholder="custCode">
                        </div>
                    </div>
                    <div class="col-9">
                        <div class="mb-3">
                            <label for="Name">Name</label>
                            <input disabled type="text" id="Name" name="Name" class="form-control bg-white needField" required placeholder="custName">
                        </div>    
                    </div>
                    <div class="col-7">
                        <div class="mb-3">
                            <label for="Contact">Contact Person</label>
                            <input disabled type="text" id="Contact" name="Contact" class="form-control bg-white" required placeholder="contactPerson">
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="mb-3">
                            <label for="Telephone">Contact Cell Number</label>
                            <input disabled type="text" id="Telephone" name="Telephone" class="form-control bg-white" required placeholder="contactCellNumber">
                        </div>    
                    </div>
                    <div class="col-4">
                        <div class="mb-3">
                            <label for="address">Region</label>
                            <div id="VSregion" name="filter" style="width: 100%" class="form-control bg-white p-0 mx-1 needField">MdCode</div>
                        </div>    
                    </div>
                    <div class="col-4">
                        <div class="mb-3">
                            <label for="address">Province</label>
                            <div id="VSprovince" name="filter" style="width: 100%" class="form-control bg-white p-0 mx-1 needField">MdCode</div>
                        </div>    
                    </div>
                    <div class="col-4">
                        <div class="mb-3">
                            <label for="address">Municipaliy</label>
                            <div id="VSmunicipality" name="filter" style="width: 100%" class="form-control bg-white p-0 mx-1 needField">MdCode</div>
                        </div>    
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="address">Home # / Street / Bldg. / Brgy.</label>
                            <input disabled type="text" id="address" name="address" class="form-control bg-white needField" required placeholder="address">
                        </div>    
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="PriceCode">Price Code</label>
                            <input disabled type="text" id="PriceCode" name="PriceCode" class="form-control bg-white needField" required placeholder="priceCode" onkeypress="return /[0-9]/.test(event.key)" maxlength="2">
                        </div>    
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="CustomerClass">Customer Class</label>
                            <input disabled type="text" id="CustomerClass" name="CustomerClass" class="form-control bg-white needField" required placeholder="CustomerClass">
                        </div>    
                    </div>
                </div>
            </div>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

    <div class="modal fade modal-lg" id="uploadCsv">
        <div class="modal-dialog">
            <div class="modal-content w-100">
                <div class="modal-body h-100">
                    <form>
                        <div class="row h-100">
                            <div id="uploaderDiv">
                                <div class="upload-container">
                                    <input class="form-control p-2" type="file" id="formFileMultiple" multiple>
                                </div>
                                <div id="uploadStatus" class="upload-status">
                                    <div class="d-flex">
                                        <div class="col-10">
                                            <span style="font-size: 16px;">Uploaded files (<span id="totalFiles"
                                                    class="text-primary">0</span></span>)
                                        </div>
                                        <div style="font-size: 14px;" class="col-2 text-end px-3">
                                            <span id="totalUploadSuccess">0</span>
                                            /
                                            <span id="totalFile">0</span>
                                        </div>
                                    </div>
                                    <hr class="my-1">

                                    <div id="fileListDiv" class="p-1">
                                        <table class="table fs-6">
                                            <tbody id="fileListTable">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button id="uploadBtn2" class="btn btn-primary px-4">Upload</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js" integrity="sha512-dfX5uYVXzyU8+KHqj8bjo7UkOdg18PaOtpa48djpNbZHwExddghZ+ZmzWT06R5v6NSk3ZUfsH6FNEDepLx9hPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="<?php echo e(asset('assets/js/customer-maintenance/customer-v2.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('Layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\backupFASTERP_2025-03-17\resources\views/Pages/customer-maintenance/cust_page.blade.php ENDPATH**/ ?>