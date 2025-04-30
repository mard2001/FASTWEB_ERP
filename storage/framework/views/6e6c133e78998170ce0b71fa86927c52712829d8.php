<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?php echo e(asset('assets/resources/ERP_icon1.png')); ?>">

    <?php echo $__env->yieldContent('html_title'); ?>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
    .moving-shadow {
        animation: smoothShadowMove 4s infinite ease-in-out;
        box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.15);
    }

    @keyframes smoothShadowMove {
        0% {
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2);
        }
        25% {
            box-shadow: -5px 7px 15px rgba(0, 0, 0, 0.2);
        }
        50% {
            box-shadow: -7px -5px 15px rgba(0, 0, 0, 0.2);
        }
        75% {
            box-shadow: 6px -6px 15px rgba(0, 0, 0, 0.2);
        }
        100% {
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2);
        }
    }
    </style>   
</head>

<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen flex items-center justify-center px-3">
    
    <div class="transition-all duration-500 ease-in-out max-w-auto h-auto min-w-[30%] bg-white shadow-lg rounded-lg moving-shadow">
        <?php echo $__env->yieldContent('content'); ?>
    </div>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php echo $__env->yieldContent('scriptjs'); ?>

</html><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\FASTERP_2025-03-17\resources\views/Layout/login.blade.php ENDPATH**/ ?>