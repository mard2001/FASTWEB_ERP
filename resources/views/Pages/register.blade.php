@extends('Layout.login')

@section('html_title')
<title>Register</title>
<style>
    .requiredField{
        border: 1px solid #db1515 !important;
    }
</style>
@endsection

@section('content')
@include('Components.register_form')
@endsection

@section('scriptjs')
<script src="{{ asset('assets/js/mainJS.js') }}"></script>
<script>
    $(document).ready(async function() {
        $('#regBtn').on("click", function() {
            var fnameReq = validateField('fname');
            var lnameReq = validateField('lname');
            var emailReq = validateField('email');
            var mobileReq = validateField('mobile');

            if (fnameReq && lnameReq && emailReq && mobileReq) {
                var regData = {
                    "name": $('#fname').val().trim() + ' ' + $('#lname').val().trim(),
                    "email": $('#email').val().trim(),
                    "mobile": $('#mobile').val().trim(),
                }

                Swal.fire({
                    text: "Please wait...",
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,  
                    allowEnterKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    url: globalApi + 'api/auth/register',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(regData),
                    success: async function(response) {
                        // Handle response from Laravel with response_stat = 1
                        if (response.response_stat === 1) {
                            Swal.fire({
                                title: response.message,
                                text: "Login Now",
                                icon: "success",
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                allowEnterKey: false,
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = globalApi;
                                }
                            });
                        } else {
                            // Laravel returned response_stat = 0 (business logic failure)
                            Swal.fire({
                                title: "Registration Failed",
                                text: response.message || "Please check your input.",
                                icon: "error"
                            });
                        }
                    },
                    error: async function(xhr, status, error) {
                        // Server or network error
                        let message = "An error occurred. Please try again later.";

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            title: "Error",
                            text: message,
                            icon: "error"
                        });

                        $('#'+xhr.responseJSON.field).addClass('requiredField');
                    }
                });
            }
        });
    });

    function validateField(fieldName){
        var value = $('#'+fieldName).val();

        if(!value){
            $('#'+fieldName).addClass('requiredField');
            return false;
        } else{
            $('#'+fieldName).removeClass('requiredField');
            return true;
        }
    }
</script>
@endsection