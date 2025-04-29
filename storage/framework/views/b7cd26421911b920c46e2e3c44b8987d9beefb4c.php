

<?php $__env->startSection('html_title'); ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://unpkg.com/read-excel-file@5.x/bundle/read-excel-file.min.js"></script>
    <title>Product Maintenance</title>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title_header'); ?>
    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.header','data' => ['title' => 'Product Maintenance']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Product Maintenance']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('table'); ?>
    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table','data' => ['id' => 'ProductTable']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'ProductTable']); ?>
         <?php $__env->slot('td', null, []); ?> 
            <td class="col">StockCode</td>
            <td class="col">Brand</td>
            <td class="col">Description</td>
            <td class="col">LongDesc</td>
            <td class="col">AlternateKey1</td>
            <td class="col">StockUom</td>
            <td class="col">AlternateUom</td>
            <td class="col">OtherUom</td>
            <td class="col">ConvFactAltUom</td>
            <td class="col">ConvFactOthUom</td>
            <td class="col">Mass</td>
            <td class="col">Volume</td>
            <td class="col">ProductClass</td>
            <td class="col">WarehouseToUse</td>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.prod_modal','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('prod_modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
         <?php $__env->slot('form_fields', null, []); ?> 
            <div class="row h-100">
                <div class="col-sm-12 col-md-4 mt-1">
                    <input type="file" id="imageHolder" style="display:none;" accept="image/*">

                    <div class="col mt-1 d-flex justify-content-center align-items-center px-3 py-2" style="height: 200px;">
                        <div class="h-100 w-100 my-3 p-2 d-flex justify-content-center align-items-center" style="border: 4px dashed rgba(45, 45, 45, 0.5); position: relative;">
                            <img id="prdImg" class="border-0 p-2 h-auto w-100" style="max-width: 200px; max-height: 200px; object-fit: cover;  cursor: pointer;" src="./uploads/upload.png" alt="">
                        </div>
                    </div>

                    <div class="w-100 d-flex align-items-center justify-content-center">
                        <button type="button" class="btn btn-sm btn-primary text-white" id="uploadImage" type="file">Choose Image</button>
                    </div>
                </div>
                <div class="col-sm-12 col-md-8 mt-1 pt-4">
                    <div class="col mt-2">
                        <label for="StockCode">Product Stock Code</label>
                        <input disabled type="text" id="StockCode" name="StockCode" class="form-control bg-white" required placeholder="Stockcode">
                    </div>
                    <div class="col mt-2">
                        <label for="priceWithVat">Product Price</label>
                        <input disabled type="number" id="priceWithVat" name="priceWithVat" class="form-control bg-white" required placeholder="Price" min=0>
                    </div>
                    <div class="col mt-2">
                        <label for="Description">Product Description</label>
                        <input disabled type="text" id="Description" name="Description" class="form-control bg-white" required placeholder="Description">
                    </div>
                    
                </div>
                <div class="col-12">
                    <div class="col mt-2">
                        <label for="Brand">Brand</label>
                        <input disabled type="text" id="Brand" name="Brand" class="form-control bg-white" required placeholder="Brand">
                    </div>
                    <div class="col mt-2">
                        <label for="AlternateKey1">AlternateKey1</label>
                        <input disabled type="text" id="AlternateKey1" name="AlternateKey1" class="form-control bg-white" required placeholder="AlternateKey">
                    </div>
                    <div class="col mt-2">
                        <label for="StockCode">Long Description</label>
                        <textarea id="LongDesc" name="LongDesc" class="form-control bg-white" required placeholder="LongDescription"></textarea>
                    </div>
                    <div class="col mt-2">
                        <label for="StockUom">UOM</label>
                        <input disabled type="text" id="StockUom" name="StockUom" class="form-control bg-white" required placeholder="UOM">
                    </div>
                    <div class="row">
                        <div class="col mt-2">
                            <label for="AlternateUom">AlternateUom</label>
                            <input disabled type="text" id="AlternateUom" name="AlternateUom" class="form-control bg-white" required placeholder="Alternate Uom">
                        </div>
                        <div class="col mt-2">
                            <label for="ConvFactAltUom">ConvFactAltUom</label>
                            <input disabled type="text" id="ConvFactAltUom" name="ConvFactAltUom" class="form-control bg-white" required placeholder="ConvFactAltUom">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mt-2">
                            <label for="OtherUom">OtherUom</label>
                            <input disabled type="text" id="OtherUom" name="OtherUom" class="form-control bg-white" required placeholder="OtherUom">
                        </div>
                        <div class="col mt-2">
                            <label for="ConvFactOthUom">ConvFactOthUom</label>
                            <input disabled type="text" id="ConvFactOthUom" name="ConvFactOthUom" class="form-control bg-white" required placeholder="ConvFactOthUom">
                        </div>
                    </div>
                    <div class="col mt-2">
                        <label for="Mass">Mass</label>
                        <input disabled type="text" id="Mass" name="Mass" class="form-control bg-white" required placeholder="Mass">
                    </div>
                    <div class="col mt-2">
                        <label for="Volume">Volume</label>
                        <input disabled type="text" id="Volume" name="Volume" class="form-control bg-white" required placeholder="Volume">
                    </div>
                    <div class="col mt-2">
                        <label for="ProductClass">ProductClass</label>
                        <input disabled type="text" id="ProductClass" name="ProductClass" class="form-control bg-white" required placeholder="ProductClass">
                    </div>
                    <div class="col mt-2">
                        <label for="WarehouseToUse">WarehouseToUse</label>
                        <input disabled type="text" id="WarehouseToUse" name="WarehouseToUse" class="form-control bg-white" required placeholder="WarehouseToUse">
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
<script src="<?php echo e(asset('assets/js/product-maintenance/product-v2.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('Layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\FASTERP_2025-03-17\resources\views/Pages/product-maintenance/product_page.blade.php ENDPATH**/ ?>