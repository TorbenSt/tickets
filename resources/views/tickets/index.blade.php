<x-layouts.app title="Tickets">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between bg-gray-50 p-5 rounded-lg">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Tickets</h1>
                <p class="text-gray-600 mt-2">
                    @if(auth()->user()->role->isDeveloper())
                        Alle Tickets systemweit
                    @else
                        Tickets aus Ihren Projekten
                    @endif
                </p>
            </div>
            
            @if(auth()->user()->role->isDeveloper())
                <div>
                    <a href="{{ route('tickets.emergency') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring focus:ring-red-300 disabled:opacity-25 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Notfall-Tickets
                    </a>
                </div>
            @endif
        </div>

        <!-- Filters & Stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600">{{ $tickets->where('status', \App\Enums\TicketStatus::OPEN)->count() }}</div>
                        <div class="text-sm text-gray-600">Benötigen Bestätigung</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $tickets->where('status', \App\Enums\TicketStatus::IN_PROGRESS)->count() }}</div>
                        <div class="text-sm text-gray-600">In Bearbeitung</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ $tickets->where('status', \App\Enums\TicketStatus::REVIEW)->count() }}</div>
                        <div class="text-sm text-gray-600">Im Review</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $tickets->where('status', \App\Enums\TicketStatus::DONE)->count() }}</div>
                        <div class="text-sm text-gray-600">Fertiggestellt</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projekt</th>
                            @if(auth()->user()->role->isDeveloper())
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Firma</th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priorität</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ersteller</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zugewiesen</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Erstellt</th>
                            <th class="relative px-6 py-3"><span class="sr-only">Aktionen</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tickets as $ticket)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $ticket->title }}</div>
                                    @if($ticket->description)
                                        <div class="text-sm text-gray-500 truncate">{{ Str::limit($ticket->description, 50) }}</div>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-medium">{{ $ticket->project->name }}</span>
                                </td>
                                
                                @if(auth()->user()->role->isDeveloper())
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm">{{ $ticket->project->firma->name }}</span>
                                    </td>
                                @endif
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $ticket->status->value === 'open' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $ticket->status->value === 'todo' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $ticket->status->value === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $ticket->status->value === 'review' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $ticket->status->value === 'done' ? 'bg-green-100 text-green-800' : '' }}">
                                        {{ $ticket->status->label() }}
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-1">
                                        @if($ticket->priority->value === 'notfall')
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        @endif
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $ticket->priority->value === 'notfall' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $ticket->priority->label() }}
                                        </span>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $ticket->creator->name }}</div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($ticket->assignee)
                                        <div class="text-sm text-gray-900">{{ $ticket->assignee->name }}</div>
                                    @else
                                        <span class="text-gray-400">Nicht zugewiesen</span>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $ticket->created_at->format('d.m.Y') }}</div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('tickets.show', $ticket) }}" wire:navigate
                                       class="text-indigo-600 hover:text-indigo-900">
                                        Anzeigen
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-4 text-center text-gray-500" colspan="{{ auth()->user()->role->isDeveloper() ? '9' : '8' }}">
                                    Keine Tickets vorhanden.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            </div>

            <!-- Pagination -->
            @if($tickets->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>