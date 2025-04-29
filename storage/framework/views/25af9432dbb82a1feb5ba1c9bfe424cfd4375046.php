

<?php $__env->startSection('html_title'); ?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<title>Master List Maintenance</title>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title_header'); ?>
<?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.header','data' => ['title' => 'Master List Maintenance']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Master List Maintenance']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('td', null, []); ?> 
        <td class="col">PeriodYear</td>
        <td class="col">PeriodMonth</td>
        <td class="col">BusinessUnit</td>
        <td class="col">PAType</td>
        <td class="col">CustomerClass</td>
        <td class="col">StockCode</td>
        <td class="col">DropSize</td>
        <td class="col">Points</td>
        <td class="col">Amount</td>
        <td class="col">BonusPoint</td>
        <td class="col">MHCount</td>
        <td class="col">UpdatedBy</td>
        <td class="col">DateUpdated</td>
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
        <div class="row h-100 fs15">
            <div class="col mt-1 flex-wrap">

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="PeriodYear">Period Year</label>
                            <input disabled type="number" id="PeriodYear" name="PeriodYear" class="form-control bg-white"
                                required placeholder="Period Year">
                        </div>

                        <div class="col">
                            <label for="PeriodMonth">Period Month</label>
                            <input disabled type="number" id="PeriodMonth" name="PeriodMonth" class="form-control bg-white"
                                required placeholder="Period Month">
                        </div>
                    </div>
                </div>



                <div class="col mt-2">
                    <label for="BusinessUnit">Business Unit</label>
                    <input disabled type="text" id="BusinessUnit" name="BusinessUnit" class="form-control bg-white"
                        required placeholder="Business Unit">
                </div>

                <div class="col mt-2">
                    <label for="PAType">PA Type</label>
                    <input disabled type="text" id="PAType" name="PAType" class="form-control bg-white"
                        required placeholder="PA Type">
                </div>

                <div class="col mt-2">
                    <label for="CustomerClass">Customer Class</label>
                    <input disabled type="number" id="CustomerClass" name="CustomerClass" class="form-control bg-white"
                        required placeholder="Customer Class">
                </div>
            </div>

            <div class="col mt-1">

                <div class="col mt-2">
                    <label for="StockCode">Stock Code</label>
                    <input disabled type="number" id="StockCode" name="StockCode" class="form-control bg-white"
                        required placeholder="Stock Code">
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="DropSize">Drop Size</label>
                            <input disabled type="number" id="DropSize" name="DropSize" class="form-control bg-white"
                                required placeholder="Drop Size">
                        </div>

                        <div class="col">
                            <label for="Points">Points</label>
                            <input disabled type="number" id="Points" name="Points" class="form-control bg-white"
                                required placeholder="Points">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="BonusPoint">Bonus Point</label>
                            <input disabled type="number" id="BonusPoint" name="BonusPoint" class="form-control bg-white"
                                required placeholder="Bonus Point">
                        </div>

                        <div class="col">
                            <label for="MHCount">MHCount</label>
                            <input disabled type="number" id="MHCount" name="MHCount" class="form-control bg-white"
                                required placeholder="MHCount">
                        </div>

                    </div>
                </div>

                <div class="col mt-2">
                    <label for="Amount">Amount</label>
                    <input disabled type="number" id="Amount" name="Amount" class="form-control bg-white"
                        required placeholder="Amount">
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
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="<?php echo e(asset('assets/js/maintenance_uploader/pamasterslist.js')); ?>"></script>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('Layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\backupFASTERP_2025-03-17\resources\views/Pages/pamasterlist_page.blade.php ENDPATH**/ ?>