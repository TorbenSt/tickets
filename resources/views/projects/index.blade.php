<x-layouts.app title="Projekte">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between bg-gray-50 p-5 rounded-lg">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Projekte</h1>
                <p class="text-gray-600 mt-2">
                    @if(auth()->user()->role->isDeveloper())
                        Alle Projekte systemweit
                    @else
                        Projekte Ihrer Firma
                    @endif
                </p>
            </div>
            
            @if(auth()->user()->role->isCustomer())
                <a href="{{ route('projects.create') }}" wire:navigate
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Neues Projekt
                </a>
            @endif
        </div>

        <!-- Projects Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($projects as $project)
                <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                    <div class="px-6 py-5 space-y-4">
                        <!-- Project Header -->
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-lg text-gray-900">{{ $project->name }}</h3>
                                @if(auth()->user()->role->isDeveloper())
                                    <p class="text-sm text-gray-600">{{ $project->firma->name }}</p>
                                @endif
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $project->tickets_count }} Ticket{{ $project->tickets_count !== 1 ? 's' : '' }}
                            </span>
                        </div>

                                                <!-- Description -->
                        @if($project->description)
                            <p class="text-sm text-gray-600 line-clamp-2">{{ $project->description }}</p>
                        @endif

                        <!-- Project Stats -->
                        @if($project->tickets_count > 0)
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                @php
                                    $openTickets = $project->tickets()->where('status', \App\Enums\TicketStatus::OPEN)->count();
                                    $inProgressTickets = $project->tickets()->where('status', \App\Enums\TicketStatus::IN_PROGRESS)->count();
                                @endphp
                                @if($openTickets > 0)
                                    <span class="flex items-center">
                                        <div class="w-2 h-2 bg-red-400 rounded-full mr-1"></div>
                                        {{ $openTickets }} Offen
                                    </span>
                                @endif
                                @if($inProgressTickets > 0)
                                    <span class="flex items-center">
                                        <div class="w-2 h-2 bg-yellow-400 rounded-full mr-1"></div>
                                        {{ $inProgressTickets }} In Bearbeitung
                                    </span>
                                @endif
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                            <a 
                                href="{{ route('projects.show', $project) }}" 
                                wire:navigate
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                            >
                                Tickets anzeigen
                            </a>
                            
                            @if(auth()->user()->role->isDeveloper() || $project->created_by === auth()->id())
                                <div class="flex space-x-2">
                                    <a 
                                        href="{{ route('projects.users', $project) }}" 
                                        wire:navigate
                                        class="text-sm text-gray-600 hover:text-gray-500"
                                        title="Benutzer verwalten"
                                    >
                                        Benutzer
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white overflow-hidden shadow rounded-lg text-center py-12">
                        <div class="px-6 space-y-4">
                            <svg class="size-16 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            <div>
                                <h3 class="font-medium text-gray-900">Keine Projekte vorhanden</h3>
                                <p class="text-gray-500">
                                    @if(auth()->user()->role->isCustomer())
                                        Erstellen Sie Ihr erstes Projekt, um Tickets zu verwalten.
                                    @else
                                        Es wurden noch keine Projekte angelegt.
                                    @endif
                                </p>
                            </div>
                            @if(auth()->user()->role->isCustomer())
                                <a 
                                    href="{{ route('projects.create') }}" 
                                    wire:navigate
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring focus:ring-indigo-300 transition"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Erstes Projekt erstellen
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($projects->hasPages())
            <div class="flex justify-center">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>