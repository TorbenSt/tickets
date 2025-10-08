<x-layouts.app :title="$firma->name">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900">{{ $firma->name }}</h1>
                <p class="text-gray-600 mt-2">
                    Firmen-Details und Übersicht
                </p>
            </div>
            
            <a 
                href="{{ route('firmas.index') }}" 
                wire:navigate
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Zurück zur Übersicht
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5 text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $firma->projects->count() }}</div>
                    <div class="text-sm text-gray-600">Projekte</div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5 text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $firma->users->count() }}</div>
                    <div class="text-sm text-gray-600">Benutzer</div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5 text-center">
                    <div class="text-3xl font-bold text-orange-600">{{ $recentTickets->count() }}</div>
                    <div class="text-sm text-gray-600">Aktuelle Tickets</div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5 text-center">
                    <div class="text-3xl font-bold text-purple-600">
                        {{ $recentTickets->where('priority.value', 'notfall')->count() }}
                    </div>
                    <div class="text-sm text-gray-600">Notfall-Tickets</div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Company Info -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Firmeninformationen</h3>
                    </div>
                    
                    <div class="px-6 py-5 space-y-4">
                        @if($firma->description)
                            <div>
                                <div class="font-medium text-gray-700 mb-2">Beschreibung</div>
                                <div class="prose max-w-none">
                                    {!! nl2br(e($firma->description)) !!}
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($firma->email)
                                <div>
                                    <div class="text-sm font-medium text-gray-700">E-Mail</div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <a href="mailto:{{ $firma->email }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $firma->email }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if($firma->phone)
                                <div>
                                    <div class="text-sm font-medium text-gray-700">Telefon</div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <a href="tel:{{ $firma->phone }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $firma->phone }}
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($firma->address)
                            <div>
                                <div class="text-sm font-medium text-gray-700">Adresse</div>
                                <div class="text-sm text-gray-600 mt-1">{{ $firma->address }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Projects -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Projekte ({{ $firma->projects->count() }})</h3>
                    </div>

                    <div class="px-6 py-5">
                        @if($firma->projects->count() > 0)
                            <div class="space-y-4">
                                @foreach($firma->projects as $project)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-lg">{{ $project->name }}</h4>
                                                @if($project->description)
                                                    <p class="text-gray-600 text-sm mt-1 line-clamp-2">{{ $project->description }}</p>
                                                @endif
                                                <div class="flex items-center space-x-4 text-sm text-gray-500 mt-2">
                                                    <span>{{ $project->tickets_count }} Ticket{{ $project->tickets_count !== 1 ? 's' : '' }}</span>
                                                    <span>Erstellt: {{ $project->created_at->format('d.m.Y') }}</span>
                                                    <span>Ersteller: {{ $project->creator->name }}</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $project->tickets_count }} Tickets
                                                </span>
                                                <a 
                                                    href="{{ route('projects.show', $project) }}" 
                                                    wire:navigate
                                                    class="inline-flex items-center px-3 py-1 text-sm text-gray-700 hover:text-gray-500"
                                                >
                                                    Anzeigen
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                </svg>
                                <div class="font-medium text-gray-900">Keine Projekte</div>
                                <div class="text-gray-500">Diese Firma hat noch keine Projekte angelegt.</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Tickets -->
                @if($recentTickets->count() > 0)
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Aktuelle Tickets</h3>
                        </div>

                        <div class="overflow-hidden">
                            <div class="flow-root">
                                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titel</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projekt</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priorität</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Erstellt</th>
                                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Aktionen</span></th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($recentTickets as $ticket)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <div class="font-medium text-gray-900">{{ Str::limit($ticket->title, 40) }}</div>
                                                        </td>
                                                        
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {{ $ticket->project->name }}
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
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Firma Users -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Benutzer ({{ $firma->users->count() }})</h3>
                    </div>
                    
                    <div class="px-6 py-5 space-y-3">
                        @foreach($firma->users as $user)
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-600">
                                        {{ substr($user->name, 0, 1) }}
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $user->role->isDeveloper() ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $user->role->label() }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Ticket-Status Übersicht</h3>
                    </div>
                    
                    <div class="px-6 py-5 space-y-3">
                        @foreach(\App\Enums\TicketStatus::cases() as $status)
                            @php
                                $count = $recentTickets->where('status', $status)->count();
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

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Aktionen</h3>
                    </div>
                    
                    <div class="px-6 py-5 space-y-2">
                        <a 
                            href="{{ route('tickets.index') }}" 
                            wire:navigate
                            class="w-full inline-flex items-center justify-start px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                            </svg>
                            Alle Tickets anzeigen
                        </a>
                        
                        @if($recentTickets->where('priority.value', 'notfall')->count() > 0)
                            <a 
                                href="{{ route('tickets.emergency') }}" 
                                wire:navigate
                                class="w-full inline-flex items-center justify-start px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                                <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Notfall-Tickets ({{ $recentTickets->where('priority.value', 'notfall')->count() }})
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>