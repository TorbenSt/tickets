<x-layouts.app :title="$project->name">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900">{{ $project->name }}</h1>
                <p class="text-gray-600 mt-2">
                    @if(auth()->user()->role->isDeveloper())
                        {{ $project->firma->name }} • 
                    @endif
                    Erstellt von {{ $project->creator->name }} am {{ $project->created_at->format('d.m.Y') }}
                </p>
            </div>
            
            <div class="flex items-center space-x-2">
                @if(auth()->user()->role->isDeveloper() || $project->created_by === auth()->id())
                    <a 
                        href="{{ route('projects.users', $project) }}" 
                        wire:navigate
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Benutzer verwalten
                    </a>
                @endif
                
                <a 
                    href="{{ route('tickets.create', $project) }}" 
                    wire:navigate
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring focus:ring-indigo-300 transition"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Neues Ticket
                </a>
                
                <a 
                    href="{{ route('projects.index') }}" 
                    wire:navigate
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-500"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Zurück
                </a>
            </div>
        </div>

        <!-- Project Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5 text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $tickets->total() }}</div>
                    <div class="text-sm text-gray-600">Gesamt Tickets</div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5 text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $project->users->count() }}</div>
                    <div class="text-sm text-gray-600">Projekt-Mitglieder</div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5 text-center">
                    <div class="text-3xl font-bold text-orange-600">
                        {{ $tickets->where('status', \App\Enums\TicketStatus::OPEN)->count() }}
                    </div>
                    <div class="text-sm text-gray-600">Offene Tickets</div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Project Description -->
                @if($project->description)
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Projektbeschreibung</h3>
                        </div>
                        <div class="px-6 py-5">
                            <div class="prose max-w-none">
                                {!! nl2br(e($project->description)) !!}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Tickets -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Tickets</h3>
                            <a 
                                href="{{ route('tickets.create', $project) }}" 
                                wire:navigate
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Neues Ticket
                            </a>
                        </div>
                    </div>

                    <div class="overflow-hidden">
                        <div class="flow-root">
                            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titel</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priorität</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ersteller</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zugewiesen</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Erstellt</th>
                                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Aktionen</span></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($tickets as $ticket)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="font-medium text-gray-900">{{ $ticket->title }}</div>
                                                        @if($ticket->description)
                                                            <div class="text-sm text-gray-500 truncate">{{ Str::limit($ticket->description, 50) }}</div>
                                                        @endif
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
                                                    
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center space-x-1">
                                                            @if($ticket->priority->value === 'notfall')
                                                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                </svg>
                                                            @endif
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                                @if($ticket->priority->value === 'notfall') bg-red-100 text-red-800
                                                                @else bg-gray-100 text-gray-800 @endif">
                                                                {{ $ticket->priority->label() }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $ticket->creator->name }}
                                                    </td>
                                                    
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        @if($ticket->assignee)
                                                            {{ $ticket->assignee->name }}
                                                        @else
                                                            <span class="text-gray-400">Nicht zugewiesen</span>
                                                        @endif
                                                    </td>
                                                    
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $ticket->created_at->format('d.m.Y') }}
                                                    </td>
                                                    
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        <a 
                                                            href="{{ route('tickets.show', $ticket) }}" 
                                                            wire:navigate
                                                            class="text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            Anzeigen
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="px-6 py-16 text-center" colspan="7">
                                                        <div class="flex flex-col items-center space-y-4">
                                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                                                            </svg>
                                                            <div>
                                                                <div class="font-medium text-gray-900">Noch keine Tickets</div>
                                                                <div class="text-gray-500">Erstellen Sie das erste Ticket für dieses Projekt.</div>
                                                            </div>
                                                            <a 
                                                                href="{{ route('tickets.create', $project) }}" 
                                                                wire:navigate
                                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                            >
                                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                                </svg>
                                                                Erstes Ticket erstellen
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Project Members -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Projekt-Mitglieder</h3>
                            @if(auth()->user()->role->isDeveloper() || $project->created_by === auth()->id())
                                <a 
                                    href="{{ route('projects.users', $project) }}" 
                                    wire:navigate
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                    Verwalten
                                </a>
                            @endif
                        </div>
                    </div>
                    
                    <div class="px-6 py-5 space-y-3">
                        @foreach($project->users as $user)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-600">
                                            {{ substr($user->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                                @if($user->id === $project->created_by)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Creator
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Project Statistics -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Statistiken</h3>
                    </div>
                    
                    <div class="px-6 py-5 space-y-4">
                        @foreach(\App\Enums\TicketStatus::cases() as $status)
                            @php
                                $count = $tickets->where('status', $status)->count();
                            @endphp
                            @if($count > 0)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 rounded-full" style="background-color: {{ 
                                            match($status->color()) {
                                                'orange' => '#f97316',
                                                'gray' => '#6b7280', 
                                                'blue' => '#3b82f6',
                                                'yellow' => '#eab308',
                                                'green' => '#10b981',
                                                default => '#6b7280'
                                            }
                                        }}"></div>
                                        <span class="text-sm">{{ $status->label() }}</span>
                                    </div>
                                    <span class="text-sm font-medium">{{ $count }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                @if(auth()->user()->role->isDeveloper())
                    <!-- Firma Info -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Firma</h3>
                        </div>
                        
                        <div class="px-6 py-5 space-y-2">
                            <div class="font-medium">{{ $project->firma->name }}</div>
                            @if($project->firma->email)
                                <div class="text-sm text-gray-600">{{ $project->firma->email }}</div>
                            @endif
                            @if($project->firma->phone)
                                <div class="text-sm text-gray-600">{{ $project->firma->phone }}</div>
                            @endif
                            <div class="pt-2">
                                <a 
                                    href="{{ route('firmas.show', $project->firma) }}" 
                                    wire:navigate
                                    class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900"
                                >
                                    Firma anzeigen
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>