<div class="p-6 space-y-6">
    <h2 class="text-center text-2xl font-bold text-gray-700">Welcome Back!</h2>
    <p class="text-center text-gray-500">Log in to your account</p>

    <form id="login" class="space-y-4" onsubmit="return false;">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" id="email" name="email" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="password" name="password" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember-me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="remember-me" class="ml-2 block text-sm text-gray-700">Remember me</label>
            </div>
            <a href="#" class="text-sm text-blue-500 hover:underline">Forgot password?</a>
        </div>
        <button id="loginBtn" class="w-full py-2 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Log In</button>
    </form>

</div>
<div class="bg-gray-100 px-6 py-4">
    <p class="text-sm text-gray-600">Don’t have an account? <a href="/register" class="text-blue-500 hover:underline">Sign up</a></p>
</div>