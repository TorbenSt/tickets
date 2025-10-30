<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Authentication Error</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-red-50 text-red-800 p-6">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
        <div class="flex items-center mb-4">
            <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.314 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <h1 class="text-xl font-semibold">Authentication Error</h1>
        </div>
        
        <p class="text-gray-700 mb-4">{{ $message ?? 'Authentication failed. Please check your token and try again.' }}</p>
        
        <div class="text-sm text-gray-600">
            <p><strong>Possible causes:</strong></p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li>Invalid or expired token</li>
                <li>Token missing from request</li>
                <li>Unauthorized domain</li>
                <li>User account not found</li>
            </ul>
        </div>
        
        <div class="mt-6 pt-4 border-t border-gray-200">
            <p class="text-xs text-gray-500">Please contact your administrator if this issue persists.</p>
        </div>
    </div>
</body>
</html>