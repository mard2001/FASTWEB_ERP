

<?php $__env->startSection('html_title'); ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <title>Receiving Report</title>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title_header'); ?>
    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.header','data' => ['title' => 'Receiving Report']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Receiving Report']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table','data' => ['id' => 'rrTable']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'rrTable']); ?>
     <?php $__env->slot('td', null, []); ?> 
        <td class="col">supCode</td>
        <td class="col">supName</td>
        
        <td class="col">supAdd</td>
        <td class="col">rrNo</td>
        <td class="col">Total</td>
        <td class="col">rrDate</td>
        <td class="col">rrRef</td>
        <td class="col">rrStat1</td>
        <td class="col">prepared</td>
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
</style>

<?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rr_modal','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('rr_modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('form_fields', null, []); ?> 
        
        
        <div class="row g-4">
            <div class="col">
                <table style="font-size: 14px">
                    <tbody>
                        <tr>
                            <td></td>
                            <th></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Supplier Code:</td>
                            <th class="px-2"><span class="supCode" style="font-weight: 550"></span></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Supplier Name:</td>
                            <th class="px-2"><span class="supName" style="font-weight: 550"></span></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Supplier TIN:</td>
                            <th class="px-2"><span class="supTin" style="font-weight: 550"></span></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Address:</td>
                            <th class="px-2"><span class="supAdd" style="font-weight: 550"></span></th>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col ">
                <table style="font-size: 14px">
                    <tbody>
                        <tr>
                            <td></td>
                            <th></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">RR No.:</td>
                            <th class="px-2"><span class="rrNo" style="font-weight: 550"></span></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Date:</td>
                            <th class="px-2"><span class="date" style="font-weight: 550"></span></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Reference:</td>
                            <th class="px-2"><span class="reference" style="font-weight: 550"></span></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap;">Status:</td>
                            <th class="px-2"><span class="status" style="font-weight: 550"></span></th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <hr>
        <table class="table" style="font-size: 12px">
            <thead>
                <tr>
                    <th scope="col" class="text-center">No.</th>
                    <th scope="col" class="text-center">Item</th>
                    <th scope="col" class="text-center">Description</th>
                    <th scope="col" class="text-center">Quantity</th>
                    <th scope="col" class="text-center">OuM</th>
                    <th scope="col" class="text-center">WhsCode</th>
                    <th scope="col" class="text-center">Unit Price</th>
                    <th scope="col" class="text-center">Net of Vat</th>
                    <th scope="col" class="text-center">Vat</th>
                    <th scope="col" class="text-center">Gross</th>
                </tr>
            </thead>
            <tbody class="rrTbody">
            </tbody>
        </table>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('pagejs'); ?>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

<script type="text/javascript" src="<?php echo e(asset('assets/js/vendor/virtual-select.min.js')); ?>"></script>

<script src="<?php echo e(asset('assets/js/receiving-report/rr.js')); ?>"></script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('Layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\FASTERP_2025-03-17\resources\views/Pages/receiving-report/receiving_report_page.blade.php ENDPATH**/ ?>