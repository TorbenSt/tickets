<x-layouts.app :title="$firma->name">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <flux:heading size="xl">{{ $firma->name }}</flux:heading>
                <flux:subheading class="mt-2">
                    Firmen-Details und Übersicht
                </flux:subheading>
            </div>
            
            <flux:button variant="ghost" :href="route('firmas.index')" wire:navigate>
                <flux:icon.arrow-left class="size-4" />
                Zurück zur Übersicht
            </flux:button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <flux:card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $firma->projects->count() }}</div>
                    <div class="text-sm text-gray-600">Projekte</div>
                </div>
            </flux:card>
            
            <flux:card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $firma->users->count() }}</div>
                    <div class="text-sm text-gray-600">Benutzer</div>
                </div>
            </flux:card>
            
            <flux:card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600">{{ $recentTickets->count() }}</div>
                    <div class="text-sm text-gray-600">Aktuelle Tickets</div>
                </div>
            </flux:card>
            
            <flux:card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600">
                        {{ $recentTickets->where('priority.value', 'notfall')->count() }}
                    </div>
                    <div class="text-sm text-gray-600">Notfall-Tickets</div>
                </div>
            </flux:card>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Company Info -->
                <flux:card>
                    <flux:card.header>
                        <flux:heading size="lg">Firmeninformationen</flux:heading>
                    </flux:card.header>
                    
                    <div class="space-y-4">
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
                </flux:card>

                <!-- Projects -->
                <flux:card>
                    <flux:card.header>
                        <flux:heading size="lg">Projekte ({{ $firma->projects->count() }})</flux:heading>
                    </flux:card.header>

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
                                            <flux:badge variant="outline" size="sm">
                                                {{ $project->tickets_count }} Tickets
                                            </flux:badge>
                                            <flux:button size="sm" variant="ghost" :href="route('projects.show', $project)" wire:navigate>
                                                Anzeigen
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <flux:icon.folder-plus class="size-12 text-gray-400 mx-auto mb-4" />
                            <div class="font-medium text-gray-900">Keine Projekte</div>
                            <div class="text-gray-500">Diese Firma hat noch keine Projekte angelegt.</div>
                        </div>
                    @endif
                </flux:card>

                <!-- Recent Tickets -->
                @if($recentTickets->count() > 0)
                    <flux:card>
                        <flux:card.header>
                            <flux:heading size="lg">Aktuelle Tickets</flux:heading>
                        </flux:card.header>

                        <div class="overflow-hidden">
                            <flux:table>
                                <flux:columns>
                                    <flux:column>Titel</flux:column>
                                    <flux:column>Projekt</flux:column>
                                    <flux:column>Status</flux:column>
                                    <flux:column>Priorität</flux:column>
                                    <flux:column>Erstellt</flux:column>
                                    <flux:column></flux:column>
                                </flux:columns>

                                <flux:rows>
                                    @foreach($recentTickets as $ticket)
                                        <flux:row>
                                            <flux:cell>
                                                <div class="font-medium">{{ Str::limit($ticket->title, 40) }}</div>
                                            </flux:cell>
                                            
                                            <flux:cell>
                                                <span class="text-sm">{{ $ticket->project->name }}</span>
                                            </flux:cell>
                                            
                                            <flux:cell>
                                                <flux:badge :color="$ticket->status->color()" size="sm">
                                                    {{ $ticket->status->label() }}
                                                </flux:badge>
                                            </flux:cell>
                                            
                                            <flux:cell>
                                                <div class="flex items-center space-x-1">
                                                    @if($ticket->priority->value === 'notfall')
                                                        <flux:icon.exclamation-triangle class="size-4 text-red-500" />
                                                    @endif
                                                    <flux:badge variant="{{ $ticket->priority->value === 'notfall' ? 'danger' : 'outline' }}" size="sm">
                                                        {{ $ticket->priority->label() }}
                                                    </flux:badge>
                                                </div>
                                            </flux:cell>
                                            
                                            <flux:cell>
                                                <div class="text-sm text-gray-500">{{ $ticket->created_at->format('d.m.Y') }}</div>
                                            </flux:cell>
                                            
                                            <flux:cell>
                                                <flux:button size="sm" variant="ghost" :href="route('tickets.show', $ticket)" wire:navigate>
                                                    Anzeigen
                                                </flux:button>
                                            </flux:cell>
                                        </flux:row>
                                    @endforeach
                                </flux:rows>
                            </flux:table>
                        </div>
                    </flux:card>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Firma Users -->
                <flux:card>
                    <flux:card.header>
                        <flux:heading size="lg">Benutzer ({{ $firma->users->count() }})</flux:heading>
                    </flux:card.header>
                    
                    <div class="space-y-3">
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
                                <flux:badge size="sm" variant="{{ $user->role->isDeveloper() ? 'primary' : 'outline' }}">
                                    {{ $user->role->label() }}
                                </flux:badge>
                            </div>
                        @endforeach
                    </div>
                </flux:card>

                <!-- Quick Stats -->
                <flux:card>
                    <flux:card.header>
                        <flux:heading size="lg">Ticket-Status Übersicht</flux:heading>
                    </flux:card.header>
                    
                    <div class="space-y-3">
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
                </flux:card>

                <!-- Quick Actions -->
                <flux:card>
                    <flux:card.header>
                        <flux:heading size="lg">Aktionen</flux:heading>
                    </flux:card.header>
                    
                    <div class="space-y-2">
                        <flux:button variant="outline" class="w-full justify-start" :href="route('tickets.index')" wire:navigate>
                            <flux:icon.ticket class="size-4" />
                            Alle Tickets anzeigen
                        </flux:button>
                        
                        @if($recentTickets->where('priority.value', 'notfall')->count() > 0)
                            <flux:button variant="danger" class="w-full justify-start" :href="route('tickets.emergency')" wire:navigate>
                                <flux:icon.exclamation-triangle class="size-4" />
                                Notfall-Tickets ({{ $recentTickets->where('priority.value', 'notfall')->count() }})
                            </flux:button>
                        @endif
                    </div>
                </flux:card>
            </div>
        </div>
    </div>
</x-layouts.app>