<div class="p-6 space-y-6 overflow-hidden">
    <img src="<?php echo e(asset('assets/resources/ERP_header1t.png')); ?>" alt="FASET ERP" style="height: 50px; width: auto; margin-left:-10px; margin-top:-15px">


    <form id="login" class="flex w-full" onsubmit="return false;">
        <?php echo csrf_field(); ?>
        <div id="mobileField" class="w-full">
            <h2 class="text-center text-2xl font-bold text-gray-700">WELCOME TO WITS ERP</h2>
            <p class="text-center text-gray-500 !mt-2">Log in to your account</p>

            <div class="flex items-stretch !mt-6">
                <span class="px-4 py-2 font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-l-lg">+63</span>
                <input
                    id="mobileNumber"
                    type="tel"
                    placeholder="Recipient's mobile"
                    aria-label="Recipient's mobile"
                    maxlength="10"
                    inputmode="numeric"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:ring-blue-500 focus:border-blue-500"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"> <!-- JavaScript to allow only numbers -->
            </div>
            <p class="text-sm text-gray-600 !mb-6 ml-1" style="font-size: 11px">Do not have an account yet? <a href="/register" class="text-blue-500 hover:underline">Register now!</a></p>
            <div class="text-right">
                <button
                    id="loginBtn"
                    class="w-50 py-2 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Continue
                </button>
            </div>
        </div>


        <div id="verifyField" class="w-full hidden">
            <h2 class="text-center text-2xl font-bold text-gray-700">Verify your account</h2>
            <p class="text-center text-gray-500 !mt-2">A One Time Password (OTP) has been sent to</p>
            <p class="text-center text-gray-500 !mt-2" id="sendOtpMobile"></p>

            <div id="otpConfirm" class="flex items-center justify-center !my-6">
                <input
                    type="tel"
                    placeholder="X"
                    aria-label="X"
                    maxlength="1"
                    inputmode="numeric"
                    class="w-[5ch] mx-2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">

                <input
                    type="tel"
                    placeholder="X"
                    aria-label="X"
                    maxlength="1"
                    inputmode="numeric"
                    class="w-[5ch] mx-2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">

                <input
                    type="tel"
                    placeholder="X"
                    aria-label="X"
                    maxlength="1"
                    inputmode="numeric"
                    class="w-[5ch] mx-2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">

                <input
                    type="tel"
                    placeholder="X"
                    aria-label="X"
                    maxlength="1"
                    inputmode="numeric"
                    class="w-[5ch] mx-2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">

                <input
                    type="tel"
                    placeholder="X"
                    aria-label="X"
                    maxlength="1"
                    inputmode="numeric"
                    class="w-[5ch] mx-2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">

                <input
                    type="tel"
                    placeholder="X"
                    aria-label="X"
                    maxlength="1"
                    inputmode="numeric"
                    class="w-[5ch] mx-2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">

            </div>
            <div class="flex flex-wrap justify-center my-4">

                <p class="text-sm text-gray-600">No One-Time-Password received?
                    <span href="" id="resendCodeBtn" class="text-blue-500 hover:underline">Request again.</span>
                </p>
            </div>

            <div class="flex flex-wrap justify-between">
                <p class="text-sm text-blue-500 hover:underline cursor-pointer" onclick="changeMobile()">Change number?
                </p>

                <p class="text-sm text-yellow-500">OTP Expires in:
                    <span id="otpExpire">00:00</span>
                </p>

            </div>
        </div>



    </form>

</div><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\FASTERP_2025-03-17\resources\views/Components/login_form.blade.php ENDPATH**/ ?>