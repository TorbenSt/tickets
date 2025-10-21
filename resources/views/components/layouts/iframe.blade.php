<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full overflow-auto">
    <!-- Kompaktes iframe Layout ohne große Navigation -->
    <div class="min-h-full">
        <!-- Minimal Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-3">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-lg font-semibold text-gray-900">
                            {{ $title ?? 'Ticket System' }}
                        </h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ auth()->user()->name }}
                        </span>
                    </div>
                    
                    <!-- Mini Navigation -->
                    <nav class="flex space-x-4">
                        @if(auth()->user()->role->isCustomer())
                            <a href="{{ route('iframe.projects.index') }}" 
                               class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium {{ request()->routeIs('iframe.projects.*') ? 'text-indigo-600 font-semibold' : '' }}">
                                Projekte
                            </a>
                            <a href="{{ route('iframe.tickets.pending-approval') }}" 
                               class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium {{ request()->routeIs('iframe.tickets.pending-approval') ? 'text-indigo-600 font-semibold' : '' }}">
                                Freigaben
                            </a>
                        @else
                            <a href="{{ route('iframe.tickets.index') }}" 
                               class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium {{ request()->routeIs('iframe.tickets.*') ? 'text-indigo-600 font-semibold' : '' }}">
                                Tickets
                            </a>
                            <a href="{{ route('iframe.firmas.index') }}" 
                               class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium {{ request()->routeIs('iframe.firmas.*') ? 'text-indigo-600 font-semibold' : '' }}">
                                Firmen
                            </a>
                        @endif
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="px-4 sm:px-6 lg:px-8 py-6">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
    
    <!-- iframe Communication Script -->
    <script>
        // Send height changes to parent window
        function notifyParentOfResize() {
            if (window.parent && window.parent !== window) {
                const height = Math.max(
                    document.body.scrollHeight,
                    document.body.offsetHeight,
                    document.documentElement.clientHeight,
                    document.documentElement.scrollHeight,
                    document.documentElement.offsetHeight
                );
                
                window.parent.postMessage({
                    type: 'resize',
                    height: height + 50 // Add some padding
                }, '*');
            }
        }
        
        // Initial height notification
        document.addEventListener('DOMContentLoaded', notifyParentOfResize);
        
        // Monitor for content changes
        window.addEventListener('resize', notifyParentOfResize);
        
        // Monitor for dynamic content changes (Livewire)
        document.addEventListener('livewire:navigated', notifyParentOfResize);
        document.addEventListener('livewire:load', notifyParentOfResize);
    </script>
</body>
</html>