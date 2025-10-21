<x-layouts.iframe title="Projekte">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Meine Projekte</h1>
                <p class="mt-1 text-gray-600">
                    Verwalten Sie Ihre Projekte und Tickets
                </p>
            </div>
            
            <a 
                href="{{ route('iframe.projects.create') }}" 
                wire:navigate
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring focus:ring-indigo-300 transition"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Neues Projekt
            </a>
        </div>

        <!-- Projects Grid -->
        @if($projects->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($projects as $project)
                    <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 truncate">
                                    {{ $project->name }}
                                </h3>
                                @if($project->tickets_count > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        {{ $project->tickets_count }} Tickets
                                    </span>
                                @endif
                            </div>
                            
                            @if($project->description)
                                <p class="mt-2 text-sm text-gray-600 line-clamp-2">
                                    {{ Str::limit($project->description, 100) }}
                                </p>
                            @endif
                            
                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    Erstellt {{ $project->created_at->format('d.m.Y') }}
                                </div>
                                <a 
                                    href="{{ route('iframe.projects.show', $project) }}" 
                                    wire:navigate
                                    class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900"
                                >
                                    Anzeigen
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($projects->hasPages())
                <div class="mt-6">
                    {{ $projects->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Keine Projekte</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Erstellen Sie Ihr erstes Projekt um zu beginnen.
                </p>
                <div class="mt-6">
                    <a 
                        href="{{ route('iframe.projects.create') }}" 
                        wire:navigate
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Erstes Projekt erstellen
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-layouts.iframe>