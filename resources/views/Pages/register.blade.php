@extends('Layout.login')

@section('html_title')
<title>Login</title>
@endsection

@section('content')
@include('Components.register_form')
@endsection

@section('scriptjs')
<script>
    $(document).ready(async function() {
        $('#regBtn').on("click", function() {
            alert('test');
            if ($('#register').valid()) {

                var regData = {
                    "name": $('#fname').val().trim() + ' ' + $('#lname').val().trim(),
                    "email": $('#email').val().trim(),
                    "password": $('#password').val().trim(),
                    "confirm_password": $('#cpassword').val().trim()
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
@endsection