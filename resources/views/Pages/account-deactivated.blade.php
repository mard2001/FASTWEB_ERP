@extends('Layout.login')

@section('html_title')
<title>Account Deactivated - FASTWEB ERP</title>
@endsection

@section('content')
<div class="p-6 space-y-6 overflow-hidden">
    <img src="{{ asset('assets/resources/ERP_header1t.png') }}" alt="FASET ERP" style="height: 50px; width: auto; margin-left:-10px; margin-top:-15px">
    
    <div class="text-center space-y-4">
        <div class="mb-6">
            <i class="fas fa-user-slash text-red-500" style="font-size: 4rem;"></i>
        </div>
        
        <h2 class="text-2xl font-bold text-red-600">Account Deactivated</h2>
        
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-left">
            <h3 class="font-semibold text-red-800 mb-2">Account Details:</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="font-medium text-gray-700">Mobile Number:</span>
                    <span class="text-gray-900" id="deactivatedMobile">{{ request('mobile', 'N/A') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-gray-700">Status:</span>
                    <span class="text-red-600 font-semibold">Deactivated</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-gray-700">Deactivation Date:</span>
                    <span class="text-gray-900" id="deactivationDate">{{ request('date', 'N/A') }}</span>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-left">
            <h3 class="font-semibold text-yellow-800 mb-2">Reason for Deactivation:</h3>
            <div class="max-h-24 overflow-y-auto scrollbar-thin scrollbar-thumb-yellow-300 scrollbar-track-yellow-100">
                <p class="text-gray-700 break-words" id="deactivationReason">{{ request('reason', 'No reason provided') }}</p>
            </div>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-left">
            <h3 class="font-semibold text-blue-800 mb-2">What to do next:</h3>
            <ul class="text-sm text-gray-700 space-y-1">
                <li>• Contact your system administrator for assistance</li>
                <li>• Request account reactivation if needed</li>
            </ul>
        </div>
        
        <div class="pt-4">
            <a href="{{ route('login') }}" class="inline-block px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Back to Login
            </a>
        </div>
    </div>
</div>
@endsection

@section('scriptjs')
<script>
    // Get URL parameters and update the display
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('mobile')) {
        document.getElementById('deactivatedMobile').textContent = urlParams.get('mobile');
    }
    
    if (urlParams.get('reason')) {
        document.getElementById('deactivationReason').textContent = urlParams.get('reason');
    }
    
    if (urlParams.get('date')) {
        const date = new Date(urlParams.get('date'));
        if (!isNaN(date.getTime())) {
            document.getElementById('deactivationDate').textContent = date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
</script>
@endsection