

<?php $__env->startSection('html_title'); ?>
<title>Salesman Maintenance</title>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title_header'); ?>
<?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.header','data' => ['title' => 'Salesman Maintenance']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Salesman Maintenance']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('table'); ?>
<?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('td', null, []); ?> 
        <td class="col">IMAGE</td>
        <td class="col">MDCODE</td>
        <td class="col">MDPASSWORD</td>
        <td class="col">MDLEVEL</td>
        <td class="col">MDSALESMANCODE</td>
        <td class="col">MDNAME</td>
        <td class="col">SITECODE</td>
        <td class="col">EOD#1</td>
        <td class="col">EOD#2</td>
        <td class="col">CONTACT</td>
        <td class="col">MDCOLOR</td>
        <td class="col">PRICECODE</td>
        <td class="col">STOCKTAKECL</td>
        <td class="col">EOD</td>
        <td class="col">DEFAULTORDTYPE</td>
        <td class="col">STKREQUIRED</td>
        <td class="col">CALLTIME</td>
        <td class="col">LOADINGCAP</td>
        <td class="col">ISACTIVE</td>
        <td class="col">PHONESN</td>
        <td class="col">VERNUMBER</td>
        <td class="col">IMMEDIATEHEAD</td>
        <td class="col">SALESMANTYPE</td>
        <td class="col">WAREHOUSECODE</td>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('modal'); ?>
<?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form_modal','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form_modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('form_fields', null, []); ?> 
        <div class="row h-100">
            <div class="col mt-1">
                <input type="file" id="imageHolder" style="display:none;" accept="image/*">

                <div class="col mt-1 d-flex justify-content-center align-items-center px-3 py-2 imgDiv" style="height: 245px;">
                    <div class="container h-100 w-75 my-3 p-2 d-flex justify-content-center align-items-center"
                        style="border: 4px dashed rgba(45, 45, 45, 0.5); position: relative;">
                        <img id="prdImg" class="border-0 p-2 h-auto w-100" style="max-width: 200px; object-fit: cover;  cursor: pointer; "
                            src="./uploads/upload.png" alt="">
                    </div>
                </div>

                <div class="w-100 d-flex align-items-center justify-content-center">
                    <button type="button" class="btn btn-sm btn-primary text-white" id="uploadImage"
                        type="file">Choose
                        Image</button>
                </div>

                <div class="col mt-2">
                    <label for="ImmediateHead">Immediate Head</label>
                    <input disabled type="text" id="ImmediateHead" name="ImmediateHead" class="form-control bg-white"
                        required placeholder="Immediate Head">
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">

                            <label for="SalesmanType">Salesman Type</label>
                            <input disabled type="text" id="SalesmanType" name="SalesmanType" class="form-control bg-white"
                                required placeholder="Salesman Type">
                        </div>
                        <div class="col">

                            <label for="WarehouseCode">Warehouse Code</label>
                            <input disabled type="text" id="WarehouseCode" name="WarehouseCode" class="form-control bg-white"
                                required placeholder="WarehouseCode">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="calltime">Call Time</label>
                            <input disabled type="text" id="calltime" name="calltime" class="form-control bg-white"
                                required placeholder="Call Time">
                        </div>
                        <div class="col">
                            <label for="EOD">End of Day</label>
                            <input disabled type="number" id="EOD" name="EOD" class="form-control bg-white"
                                required placeholder="End of Day">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <label for="loadingCap">Loading Cap</label>
                    <input disabled type="number" id="loadingCap" name="loadingCap" class="form-control bg-white"
                        required placeholder="Loading Cap">
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">

                            <label for="verNumber">Version</label>
                            <input disabled type="text" id="verNumber" name="verNumber" class="form-control bg-white"
                                required placeholder="Version Number">
                        </div>
                        <div class="col">

                            <label for="isActive">isActive</label>
                            <input disabled type="number" id="isActive" name="isActive" class="form-control bg-white"
                                required placeholder="isActive">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <label for="PhoneSN">PhoneSN</label>
                    <input disabled type="text" id="PhoneSN" name="PhoneSN" class="form-control bg-white"
                        required placeholder="PhoneSN">
                </div>

            </div>
            <div class="col mt-1">
                <div class="col mt-2">
                    <label for="mdCode">MDCode</label>
                    <input disabled type="number" id="mdCode" name="mdCode" class="form-control bg-white"
                        required placeholder="mdCode">
                </div>

                <div class="col mt-2">
                    <label for="mdName">Name</label>
                    <input disabled type="text" id="mdName" name="mdName" class="form-control bg-white"
                        required placeholder="Name">
                </div>

                <div class="col mt-2">
                    <label for="mdPassword">Password</label>
                    <input disabled type="number" id="mdPassword" name="mdPassword" class="form-control bg-white"
                        required placeholder="Password">
                </div>

                <div class="col mt-2">
                    <label for="contactCellNumber">Contact Mobile</label>
                    <input disabled type="number" id="contactCellNumber" name="contactCellNumber" class="form-control bg-white"
                        required placeholder="Contact Mobile">
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="mdLevel">Level</label>
                            <input disabled type="number" id="mdLevel" name="mdLevel" class="form-control bg-white"
                                required placeholder="Level">
                        </div>
                        <div class="col">
                            <label for="DefaultOrdType">Default Or dType</label>
                            <input disabled type="text" id="DefaultOrdType" name="DefaultOrdType" class="form-control bg-white"
                                required placeholder="DefaultOrdType">
                        </div>
                    </div>
                </div>
                <div class="col mt-2">
                    <div class="row">
                        <div class="col">

                            <label for="mdSalesmancode">Salesman Code</label>
                            <input disabled type="text" id="mdSalesmancode" name="mdSalesmancode" class="form-control bg-white"
                                required placeholder="Salesman Code">
                        </div>
                        <div class="col">

                            <label for="siteCode">Site Code</label>
                            <input disabled type="text" id="siteCode" name="siteCode" class="form-control bg-white"
                                required placeholder="Site Code">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="eodNumber1">End of Day #1</label>
                            <input disabled type="number" id="eodNumber1" name="eodNumber1" class="form-control bg-white"
                                required placeholder="End of Day #1">
                        </div>
                        <div class="col">

                            <label for="eodNumber2">End of Day #2</label>
                            <input disabled type="number" id="eodNumber2" name="eodNumber2" class="form-control bg-white"
                                required placeholder="End of Day #2">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">

                            <label for="mdColor">Color Hex</label>
                            <input disabled type="text" id="mdColor" name="mdColor" class="form-control bg-white"
                                required placeholder="Color Hex">
                        </div>
                        <div class="col">

                            <label for="priceCode">Price Code</label>
                            <input disabled type="number" id="priceCode" name="priceCode" class="form-control bg-white"
                                required placeholder="Price Code">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="stkRequired">stkRequired</label>
                            <input disabled type="number" id="stkRequired" id="stkRequired" class="form-control bg-white"
                                required placeholder="stkRequired">
                        </div>
                        <div class="col">
                            <label for="StockTakeCL">StockTakeCL</label>
                            <input disabled type="number" id="StockTakeCL" name="StockTakeCL" class="form-control bg-white"
                                required placeholder="StockTakeCL">
                        </div>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('pagejs'); ?>
<script src="<?php echo e(asset('assets/js/maintenance_uploader/salesman.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('Layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\backupFASTERP_2025-03-17\resources\views/Pages/salesman_page.blade.php ENDPATH**/ ?>