<x-layouts.app title="Tickets zur Freigabe">
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-gray-50 p-5 rounded-lg">
            <h1 class="text-3xl font-bold text-gray-900">Tickets zur Freigabe</h1>
            <p class="mt-2 text-gray-600">
                Hier finden Sie alle Tickets, die von Entwicklern erstellt wurden und Ihre Bestätigung benötigen.
            </p>
        </div>

        @if($tickets->count() > 0)
            <!-- Tickets List -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @foreach($tickets as $ticket)
                        <li>
                            <div class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3">
                                                    <h3 class="text-lg font-semibold text-gray-900 truncate">
                                                        #{{ $ticket->id }} - {{ $ticket->title }}
                                                    </h3>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->priority->color() }}">
                                                        {{ $ticket->priority->label() }}
                                                    </span>
                                                </div>
                                                
                                                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                                    <span class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                                        </svg>
                                                        {{ $ticket->project->name }}
                                                    </span>
                                                    <span class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                        {{ $ticket->creator->name }}
                                                    </span>
                                                    <span class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h4a2 2 0 002-2v-4"></path>
                                                        </svg>
                                                        {{ $ticket->created_at->format('d.m.Y H:i') }}
                                                    </span>
                                                    @if($ticket->estimated_hours)
                                                        <span class="flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            ~{{ $ticket->estimated_hours }}h
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <div class="mt-3">
                                                    <p class="text-sm text-gray-600 line-clamp-2">
                                                        {{ Str::limit($ticket->description, 200) }}
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center space-x-3 ml-4">
                                                <!-- Details Button -->
                                                <a href="{{ route('tickets.show', $ticket) }}" 
                                                   wire:navigate
                                                   class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    Details
                                                </a>
                                                
                                                <!-- Approve Button -->
                                                <form method="POST" action="{{ route('tickets.approve', $ticket) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            onclick="return confirm('Möchten Sie dieses Ticket wirklich freigeben? Es wird dann zur Bearbeitung freigegeben.')"
                                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Freigeben
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $tickets->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Keine Tickets zur Freigabe</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Aktuell gibt es keine Tickets, die Ihre Bestätigung benötigen.
                </p>
                <div class="mt-6">
                    <a href="{{ route('projects.index') }}" 
                       wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                        Zurück zu Projekten
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>