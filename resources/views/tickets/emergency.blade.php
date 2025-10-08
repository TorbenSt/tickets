<x-layouts.app title="Notfall-Tickets">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-2">
                    <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <h1 class="text-3xl font-bold text-gray-900">Notfall-Tickets</h1>
                </div>
                <p class="text-red-600 mt-2">
                    Tickets mit höchster Priorität - Sofortige Aufmerksamkeit erforderlich
                </p>
            </div>
            
            <a 
                href="{{ route('tickets.index') }}" 
                wire:navigate
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Zurück zu allen Tickets
            </a>
        </div>

        <!-- Emergency Alert -->
        @if($tickets->count() > 0)
            <div class="border border-red-200 bg-red-50 rounded-lg overflow-hidden shadow">
                <div class="px-6 py-5">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <div class="font-semibold text-red-900">{{ $tickets->count() }} Notfall-Ticket(s) benötigen sofortige Aufmerksamkeit</div>
                            <div class="text-red-700">Diese Tickets haben die höchste Priorität und sollten unverzüglich bearbeitet werden.</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tickets Table -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="overflow-hidden">
                <div class="flow-root">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titel</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projekt</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Firma</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ersteller</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zugewiesen</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Erstellt</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Geschätzt</th>
                                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Aktionen</span></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($tickets as $ticket)
                                        <tr class="bg-red-50 border-l-4 border-red-400 hover:bg-red-100">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                    <div>
                                                        <div class="font-medium text-red-900">{{ $ticket->title }}</div>
                                                        @if($ticket->description)
                                                            <div class="text-sm text-red-700 truncate">{{ Str::limit($ticket->description, 50) }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                                {{ $ticket->project->name }}
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $ticket->project->firma->name }}
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($ticket->status->color() === 'orange') bg-orange-100 text-orange-800
                                                    @elseif($ticket->status->color() === 'blue') bg-blue-100 text-blue-800
                                                    @elseif($ticket->status->color() === 'green') bg-green-100 text-green-800
                                                    @elseif($ticket->status->color() === 'yellow') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ $ticket->status->label() }}
                                                </span>
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $ticket->creator->name }}
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($ticket->assignee)
                                                    <div class="text-sm font-medium text-gray-900">{{ $ticket->assignee->name }}</div>
                                                @else
                                                    <div class="flex items-center space-x-1">
                                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                                        </svg>
                                                        <span class="text-red-600 font-medium text-sm">Nicht zugewiesen</span>
                                                    </div>
                                                @endif
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $ticket->created_at->format('d.m.Y H:i') }}</div>
                                                <div class="text-xs text-gray-500">{{ $ticket->created_at->diffForHumans() }}</div>
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($ticket->estimated_hours)
                                                    {{ $ticket->estimated_hours }}h
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a 
                                                    href="{{ route('tickets.show', $ticket) }}" 
                                                    wire:navigate
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                >
                                                    Sofort bearbeiten
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-6 py-16 text-center" colspan="9">
                                                <div class="flex flex-col items-center space-y-4">
                                                    <svg class="w-12 h-12 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    <div>
                                                        <div class="font-medium text-gray-900">Keine Notfall-Tickets!</div>
                                                        <div class="text-gray-500">Alle kritischen Tickets wurden bearbeitet.</div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            @if($tickets->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>