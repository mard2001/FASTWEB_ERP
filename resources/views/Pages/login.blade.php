@extends('Layout.login')

@section('html_title')
<title>Data Maintenance Login</title>
@endsection

@section('content')
@include('Components.login_form')
@endsection

@section('scriptjs')
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
<script>
    var otpCountdown = 299; //5 minutees
    var interval;
    // To Page 2 (Next button click)
    $(document).on('click', '#loginBtn', function() {

        $.ajax({
            url: 'https://spc.sfa.w-itsolutions.com/api/sendOTP',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                mobile: '0' + $('#mobileNumber').val()
            }), // Convert the data to JSON format
            success: function(response) {

                if (response.success) {

                    $('#mobileField').hide(); // Show Page 2
                    // Slide in Page 2
                    $('#verifyField').show(); // Show Page 2
                    anime({
                        targets: '#verifyField',
                        translateX: ['100%', '0%'],
                        duration: 500,
                        easing: 'easeInOutQuad',
                        complete: function() {

                            $('#sendOtpMobile').text('+63' + $('#mobileNumber').val());
                            otpCountdown = 299;
                            $('#otpConfirm').find('input').val('');
                            interval = !interval && setInterval(updateCountdown, 1000);
                        }
                    });
                }
            },
            error: async function(xhr, status, error) {
                alert('login failed');

                console.log(xhr, status, error)

                return xhr, status, error;
            }
        });





    });

    function changeMobile() {
        // Slide in Page 2

        anime({
            targets: '#verifyField',
            translateX: ['0%', '100%'],
            duration: 500,
            easing: 'easeInOutQuad',
            complete: function() {
                $('#verifyField').hide(); // Show Page 2
                $('#mobileField').show(); // Hide Page 1


            }
        });
    }

    $(document).on('input', '#otpConfirm input', function() {
        // Get the current value of the input
        var currentValue = this.value;

        // Get all input elements within the #otpConfirm container
        var inputs = $('#otpConfirm').find('input');

        // Check if the value contains any non-numeric character
        if (/[^0-9]/.test(currentValue)) {

            this.value = currentValue.replace(/[^0-9]/g, '');

        } else {
            // Check if the current input is the last input in the collection
            if (!$(this).is(inputs.last())) {

                if ($(this).val().length > 0) {
                    // Get the current index of the input
                    var currentIndex = inputs.index(this);

                    // Move to the next input if it's not the last one
                    var nextInput = inputs.eq(currentIndex + 1);

                    if (nextInput.length) {
                        // Focus on the next input element
                        nextInput.focus();

                    }
                }

            }

            var allFilled = true;
            var inputedOTP = "";

            inputs.each(function() {
                if (!$(this).val()) {
                    allFilled = false;
                    return false; // exit the loop early if any input is empty
                } else {
                    inputedOTP += $(this).val();
                }
            });

            if (allFilled) {
                console.log('+63' + $('#mobileNumber').val());

                $.ajax({
                    url: 'https://spc.sfa.w-itsolutions.com/api/verifyOTP',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        mobile: '0' + $('#mobileNumber').val(),
                        otp: inputedOTP

                    }), // Convert the data to JSON format
                    success: function(response) {

                        if (response.success) {        
                            localStorage.setItem('api_token', response.token); // Store the token

                            localStorage.setItem('user', JSON.stringify(response.user));

                            window.location.href = 'https://spc.sfa.w-itsolutions.com/dbconfig';

                        }
                    },
                    error: async function(xhr, status, error) {

                        console.log(xhr, status, error)

                        return xhr, status, error;
                    }
                });
            }
        }
    });

    // Detect keypress and check for Backspace within the input
    $(document).on('keydown', '#otpConfirm input', function(event) {
        var inputs = $('#otpConfirm').find('input');

        if (event.key === 'Backspace') {
            // Get the current index of the input
            var currentIndex = inputs.index(this);

            if (!inputs.eq(currentIndex).val()) {
                event.preventDefault();

                // Move to the next input if it's not the last one
                var nextInput = inputs.eq(currentIndex - 1);
                if (nextInput.length) {
                    // Focus on the next input element
                    nextInput.focus();
                    nextInput.val('');

                }
            }


        }
    });

    function updateCountdown() {
        // Calculate minutes and seconds
        const minutes = Math.floor(otpCountdown / 60);
        const seconds = otpCountdown % 60;


        // Format the time to always show two digits (e.g., 05:09)
        $('#otpExpire').text(`${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`);

        // Decrease the time by 1 second
        if (otpCountdown > 0) {
            otpCountdown--;
        } else {
            clearInterval(interval); // Stop the countdown when it reaches 0
            $('#otpExpire').text('00:00');
        }
    }




    $(document).ready(async function() {




    });
</script>
@endsection