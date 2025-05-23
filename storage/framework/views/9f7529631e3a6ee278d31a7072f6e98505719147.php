<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?php echo e(asset('assets/resources/ERP_icon1.png')); ?>">

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <?php echo $__env->yieldContent('html_title'); ?>
    <?php echo $__env->make('Links.main_stlyles_links', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</head>

<body>
    
    <div class="wrapper w-100">
        <?php echo $__env->make('Components.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="main">
            <?php echo $__env->yieldContent('title_header'); ?>
            <?php echo $__env->yieldContent('filtering_options'); ?>
            <?php echo $__env->yieldContent('mini_dashboard_chart'); ?>
            <div class="container-fluid mainInnerDiv">
                <?php echo $__env->yieldContent('table'); ?>
            </div>
            
            <?php echo $__env->yieldContent('modal'); ?>
            <?php echo $__env->make('Components.uploader_modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>
    
</body>

<?php echo $__env->make('Links.main_js_library_links', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->yieldContent('pagejs'); ?>

</html><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\FASTERP_2025-03-17\resources\views/Layout/layout.blade.php ENDPATH**/ ?>