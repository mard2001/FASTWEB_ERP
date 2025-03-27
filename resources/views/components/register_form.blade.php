<div class="p-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-700">Create an account </h2>
    <form id="register" class="space-y-4" onsubmit="return false;">
        <div class="flex flex-col mb-6 space-y-6 md:flex-row md:space-y-0 md:space-x-6">
            <div class="w-full">
                <label for="email" class="block text-sm font-medium text-gray-700">First name</label>
                <input type="text" id="fname" name="fname" required class="mt-1 w-full space-x px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">

            </div>
            <div class="w-full">
                <label for="email" class="block text-sm font-medium text-gray-700 flex">Last name</label>
                <input type="text" id="lname" name="lname" required class="mt-1 w-full space-x px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" id="email" name="email" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="password" name="password" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" id="cpassword" name="cpassword" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex items-start mb-6">
            <div class="flex items-center h-5"><input required="" id="terms" aria-describedby="terms" name="terms" type="checkbox" class="w-4 h-4 bg-gray-50 rounded border-gray-300 focus:ring-3 focus:ring-blue-300 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600"></div>
            <div class="ml-3 text-sm"><label for="terms" class="font-medium text-gray-900 dark:text-white">I accept the<a class="ml-1 text-blue-700 dark:text-blue-500 hover:underline" href="/terms-and-conditions/">Terms and Conditions</a></label></div>
        </div>

        <button id="regBtn" class="w-full py-2 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Register</button>
    </form>
</div>
<div class="bg-gray-100 px-6 py-4">
    <p class="text-sm text-gray-600">Already have an account? <a href="/login" class="text-blue-500 hover:underline">Login here</a></p>
</div>