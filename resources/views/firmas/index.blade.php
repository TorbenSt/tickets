<x-layouts.app title="Firmen">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Firmen-Übersicht</h1>
                <p class="text-gray-600 mt-2">Alle Firmen im System verwalten</p>
            </div>
        </div>

        <!-- Firmen Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($firmas as $firma)
                <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                    <div class="px-6 py-5 space-y-4">
                        <!-- Firma Header -->
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-lg text-gray-900">{{ $firma->name }}</h3>
                                @if($firma->email)
                                    <p class="text-sm text-gray-600">{{ $firma->email }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Firma Description -->
                        @if($firma->description)
                            <p class="text-gray-600 text-sm line-clamp-3">{{ $firma->description }}</p>
                        @endif

                        <!-- Contact Info -->
                        <div class="space-y-2 text-sm">
                            @if($firma->phone)
                                <div class="flex items-center space-x-2 text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span>{{ $firma->phone }}</span>
                                </div>
                            @endif
                            
                            @if($firma->address)
                                <div class="flex items-center space-x-2 text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>{{ $firma->address }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                            <div class="text-center">
                                <div class="text-lg font-semibold text-blue-600">{{ $firma->projects_count }}</div>
                                <div class="text-xs text-gray-500">Projekte</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-green-600">{{ $firma->users_count }}</div>
                                <div class="text-xs text-gray-500">Benutzer</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <a 
                                href="{{ route('firmas.show', $firma) }}" 
                                wire:navigate
                                class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring focus:ring-indigo-300 transition flex-1"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Details
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white overflow-hidden shadow rounded-lg text-center py-12">
                        <div class="px-6 space-y-4">
                            <svg class="w-16 h-16 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <div>
                                <h3 class="font-medium text-gray-900">Keine Firmen vorhanden</h3>
                                <p class="text-gray-500">Es sind noch keine Firmen im System registriert.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($firmas->hasPages())
            <div class="flex justify-center">
                {{ $firmas->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>