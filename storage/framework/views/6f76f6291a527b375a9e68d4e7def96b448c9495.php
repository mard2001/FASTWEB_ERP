    

    <?php $__env->startSection('html_title'); ?>
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

<?php
$tblColumns = [
'CUSTID','MDCODE','CUSTCODE','CUSTNAME','CONTACT','CONTACTPERSON','CONTACTLANDLINE','FREQUENCYCATEGORY','MCPDAY','MCPSSCHEDULE','GEOLOCATION','LASTUPDATED','LASTPURCHASE','LATITUDE','LONGITUDE','SYNCSTAT','DATESSTAMP','TIMESTAMP','ISLOCKON','PRICECODE','CUSTTYPE','ISVISIT','DEFAULTORDTYPE','CITYMUNCODE','REGION','PROVINCE','MUNICIPALITY','BARANGAY','AREA','WAREHOUSE','KASOSYO'
];
?>

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
        <?php $__currentLoopData = $tblColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <td class="col"><?php echo e($column); ?></td>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="customerID">Customer ID</label>
                            <input disabled type="number" id="customerID" name="customerID" class="form-control bg-white"
                                required placeholder="Customer ID">
                        </div>
                        <div class="col">
                            <label for="mdCode">MDCode</label>
                            <input disabled type="number" id="mdCode" name="mdCode" class="form-control bg-white"
                                required placeholder="MDCode">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="custCode">Customer Code</label>
                            <input disabled type="number" id="custCode" name="custCode" class="form-control bg-white"
                                required placeholder="Customer Code">
                        </div>
                        <div class="col">
                            <label for="contactCellNumber">Contact Mobile</label>
                            <input disabled type="number" id="contactCellNumber" name="contactCellNumber" class="form-control bg-white"
                                required placeholder="Contact Mobile">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <label for="custName">Customer Name</label>
                    <input disabled type="text" id="custName" name="custName" class="form-control bg-white"
                        required placeholder="Customer Name">
                </div>

                <div class="col mt-2">
                    <label for="contactPerson">Contact Person</label>
                    <input disabled type="text" id="contactPerson" name="contactPerson" class="form-control bg-white"
                        required placeholder="contactPerson">
                </div>

                <div class="col mt-2">
                    <label for="contactLandline">Contact Landline</label>
                    <input disabled type="text" id="contactLandline" name="contactLandline" class="form-control bg-white"
                        required placeholder="ContactLandline">
                </div>

                <div class="col mt-2">
                    <label for="address">Address</label>
                    <input disabled type="text" id="address" name="address" class="form-control bg-white"
                        required placeholder="address">
                </div>
                <div class="col mt-2">
                    <div class="row">
                        <div class="col">

                            <label for="CityMunCode">City Mun Code</label>
                            <input disabled type="text" id="CityMunCode" name="CityMunCode" class="form-control bg-white"
                                required placeholder="CityMunCode">
                        </div>
                        <div class="col">

                            <label for="REGION">REGION</label>
                            <input disabled type="number" id="REGION" name="REGION" class="form-control bg-white"
                                required placeholder="REGION">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="PROVINCE">PROVINCE</label>
                            <input disabled type="text" id="PROVINCE" class="form-control bg-white"
                                required placeholder="PROVINCE">
                        </div>
                        <div class="col">
                            <label for="MUNICIPALITY">MUNICIPALITY</label>
                            <input disabled type="text" id="MUNICIPALITY" name="MUNICIPALITY" class="form-control bg-white"
                                required placeholder="MUNICIPALITY">
                        </div>
                    </div>

                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="BARANGAY">BARANGAY</label>
                            <input disabled type="text" id="BARANGAY" id="BARANGAY" class="form-control bg-white"
                                required placeholder="BARANGAY">
                        </div>
                        <div class="col">
                            <label for="Area">Area</label>
                            <input disabled type="number" id="Area" name="Area" class="form-control bg-white"
                                required placeholder="Area">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="warehouse">Warehouse</label>
                            <input disabled type="text" id="warehouse" id="warehouse" class="form-control bg-white"
                                required placeholder="warehouse">
                        </div>
                        <div class="col">
                            <label for="KASOSYO">KASOSYO</label>
                            <input disabled type="text" id="KASOSYO" name="KASOSYO" class="form-control bg-white"
                                required placeholder="KASOSYO">
                        </div>
                    </div>

                </div>

                <div class="col mt-2">
                    <div class="row">

                        <div class="col">
                            <label for="custType">Customer Type</label>
                            <input disabled type="text" id="custType" name="custType" class="form-control bg-white"
                                required placeholder="custType">
                        </div>

                        <div class="col">
                            <label for="isVisit">IsVisit</label>
                            <input disabled type="text" id="isVisit" name="isVisit" class="form-control bg-white"
                                required placeholder="isVisit">
                        </div>

                    </div>
                </div>
            </div>
            <div class="col mt-1">

                <div class="col mt-2">
                    <label for="dates_tamp">Date Stamp</label>
                    <input disabled type="text" id="dates_tamp" name="dates_tamp" class="form-control bg-white"
                        required placeholder="dates_tamp">
                </div>

                <div class="col mt-2">
                    <label for="time_stamp">Time Stamp</label>
                    <input disabled type="text" id="time_stamp" name="time_stamp" class="form-control bg-white"
                        required placeholder="time_stamp">
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="frequencyCategory">Frequency Category</label>
                            <input disabled type="number" id="frequencyCategory" name="frequencyCategory" class="form-control bg-white"
                                required placeholder="Frequency Category">
                        </div>
                        <div class="col">
                            <label for="mcpDay">MCPDay</label>
                            <input disabled type="number" id="mcpDay" name="mcpDay" class="form-control bg-white"
                                required placeholder="mcpDay">
                        </div>
                    </div>
                </div>
                <div class="col mt-2">
                    <label for="mcpSchedule">MCPSchedule</label>
                    <input disabled type="text" id="mcpSchedule" name="mcpSchedule" class="form-control bg-white"
                        required placeholder="mcpSchedule">
                </div>

                <div class="col mt-2">
                    <label for="geolocation">Geolocation</label>
                    <input disabled type="text" id="geolocation" name="geolocation" class="form-control bg-white"
                        required placeholder="geolocation">
                </div>

                <div class="col mt-2">
                    <label for="lastUpdated">Last Updated</label>
                    <input disabled type="text" id="lastUpdated" name="lastUpdated" class="form-control bg-white"
                        required placeholder="lastUpdated">
                </div>

                <div class="col mt-2">
                    <div class="col">
                        <label for="lastPurchase">Last Purchase</label>
                        <input disabled type="text" id="lastPurchase" name="lastPurchase" class="form-control bg-white"
                            required placeholder="lastPurchase">
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="syncstat">Sync Status</label>
                            <input disabled type="number" id="syncstat" name="syncstat" class="form-control bg-white"
                                required placeholder="syncstat">
                        </div>

                        <div class="col">
                            <label for="priceCode">Price Code</label>
                            <input disabled type="text" id="priceCode" name="priceCode" class="form-control bg-white"
                                required placeholder="Price Code">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="latitude">Latitude</label>
                            <input disabled type="text" id="latitude" name="latitude" class="form-control bg-white"
                                required placeholder="Latitude">
                        </div>

                        <div class="col">
                            <label for="longitude">Longitude</label>
                            <input disabled type="number" id="longitude" name="longitude" class="form-control bg-white"
                                required placeholder="Longitude">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="col">
                        <label for="baseGPSLat">BaseGPSLat</label>
                        <input disabled type="text" id="baseGPSLat" name="baseGPSLat" class="form-control bg-white"
                            required placeholder="baseGPSLat">
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="isLockOn">IsLockOn</label>
                            <input disabled type="text" id="isLockOn" name="isLockOn" class="form-control bg-white"
                                required placeholder="isLockOn">
                        </div>

                        <div class="col">
                            <label for="DefaultOrdType">Default Or DType</label>
                            <input disabled type="text" id="DefaultOrdType" name="isVisit" class="form-control bg-white"
                                required placeholder="DefaultOrdType">
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
<script src="<?php echo e(asset('assets/js/maintenance_uploader/customer.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('Layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\backupFASTERP_2025-03-17\resources\views/Pages/customer_page.blade.php ENDPATH**/ ?>