

<?php $__env->startSection('html_title'); ?>
<title>Login</title>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('Components.register_form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scriptjs'); ?>
<script>
    $(document).ready(async function() {
        $('#regBtn').on("click", function() {
            if ($('#register').valid()) {

                var regData = {
                    "name": $('#fname').val().trim() + ' ' + $('#lname').val().trim(),
                    "email": $('#email').val().trim(),
                    "password": $('#password').val().trim(),
                    "password_confirmation": $('#password_confirmation').val().trim(),
                    "mobile": $('#mobile').val().trim(),
                }

                $.ajax({
                    url: 'http://127.0.0.1:8000/api/auth/register',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(regData),
                    success: async function(response) {
                        alert('registered');
                    },
                    error: async function(xhr, status, error) {

                        console.log(xhr, status, error)

                        return xhr, status, error;
                    }
                });

            }
        });


    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('Layout.login', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\FASTERP_2025-03-17\resources\views/Pages/register.blade.php ENDPATH**/ ?>