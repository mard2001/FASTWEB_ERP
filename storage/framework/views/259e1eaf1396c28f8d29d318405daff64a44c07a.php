<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <title>Customer Maintenance</title> -->
    <?php echo $__env->yieldContent('html_title'); ?>
    <?php echo $__env->yieldContent('internal_css'); ?>
    <?php echo $__env->make('Links.main_stlyles_links', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</head>

<body>
    <div class="wrapper">
        <?php echo $__env->make('Components.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="main">
            <?php echo $__env->yieldContent('title_header'); ?>
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>    

    <!-- <?php echo $__env->make('Links.main_js_library_links', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> -->
    <?php echo $__env->yieldContent('pagejs'); ?>

</body>

</html><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\FASTERP_2025-03-17\resources\views/Layout/db_layout.blade.php ENDPATH**/ ?>