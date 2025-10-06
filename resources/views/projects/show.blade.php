<x-layouts.app :title="$project->name">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <flux:heading size="xl">{{ $project->name }}</flux:heading>
                <flux:subheading class="mt-2">
                    @if(auth()->user()->role->isDeveloper())
                        {{ $project->firma->name }} • 
                    @endif
                    Erstellt von {{ $project->creator->name }} am {{ $project->created_at->format('d.m.Y') }}
                </flux:subheading>
            </div>
            
            <div class="flex items-center space-x-2">
                @if(auth()->user()->role->isDeveloper() || $project->created_by === auth()->id())
                    <flux:button variant="outline" :href="route('projects.users', $project)" wire:navigate>
                        <flux:icon.users class="size-4" />
                        Benutzer verwalten
                    </flux:button>
                @endif
                
                <flux:button variant="primary" :href="route('tickets.create', $project)" wire:navigate>
                    <flux:icon.plus class="size-4" />
                    Neues Ticket
                </flux:button>
                
                <flux:button variant="ghost" :href="route('projects.index')" wire:navigate>
                    <flux:icon.arrow-left class="size-4" />
                    Zurück
                </flux:button>
            </div>
        </div>

        <!-- Project Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <flux:card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $tickets->total() }}</div>
                    <div class="text-sm text-gray-600">Gesamt Tickets</div>
                </div>
            </flux:card>
            
            <flux:card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $project->users->count() }}</div>
                    <div class="text-sm text-gray-600">Projekt-Mitglieder</div>
                </div>
            </flux:card>
            
            <flux:card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600">
                        {{ $tickets->where('status', \App\Enums\TicketStatus::OPEN)->count() }}
                    </div>
                    <div class="text-sm text-gray-600">Offene Tickets</div>
                </div>
            </flux:card>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Project Description -->
                @if($project->description)
                    <flux:card>
                        <flux:card.header>
                            <flux:heading size="lg">Projektbeschreibung</flux:heading>
                        </flux:card.header>
                        <div class="prose max-w-none">
                            {!! nl2br(e($project->description)) !!}
                        </div>
                    </flux:card>
                @endif

                <!-- Tickets -->
                <flux:card>
                    <flux:card.header>
                        <div class="flex items-center justify-between">
                            <flux:heading size="lg">Tickets</flux:heading>
                            <flux:button size="sm" variant="primary" :href="route('tickets.create', $project)" wire:navigate>
                                <flux:icon.plus class="size-4" />
                                Neues Ticket
                            </flux:button>
                        </div>
                    </flux:card.header>

                    <div class="overflow-hidden">
                        <flux:table>
                            <flux:columns>
                                <flux:column>Titel</flux:column>
                                <flux:column>Status</flux:column>
                                <flux:column>Priorität</flux:column>
                                <flux:column>Ersteller</flux:column>
                                <flux:column>Zugewiesen</flux:column>
                                <flux:column>Erstellt</flux:column>
                                <flux:column></flux:column>
                            </flux:columns>

                            <flux:rows>
                                @forelse($tickets as $ticket)
                                    <flux:row>
                                        <flux:cell>
                                            <div class="font-medium">{{ $ticket->title }}</div>
                                            @if($ticket->description)
                                                <div class="text-sm text-gray-500 truncate">{{ Str::limit($ticket->description, 50) }}</div>
                                            @endif
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
                                            <div class="text-sm">{{ $ticket->creator->name }}</div>
                                        </flux:cell>
                                        
                                        <flux:cell>
                                            @if($ticket->assignee)
                                                <div class="text-sm">{{ $ticket->assignee->name }}</div>
                                            @else
                                                <span class="text-gray-400">Nicht zugewiesen</span>
                                            @endif
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
                                @empty
                                    <flux:row>
                                        <flux:cell class="text-center py-8" colspan="7">
                                            <div class="flex flex-col items-center space-y-2">
                                                <flux:icon.ticket class="size-12 text-gray-400" />
                                                <div class="font-medium text-gray-900">Noch keine Tickets</div>
                                                <div class="text-gray-500">Erstellen Sie das erste Ticket für dieses Projekt.</div>
                                                <flux:button size="sm" variant="primary" :href="route('tickets.create', $project)" wire:navigate class="mt-2">
                                                    <flux:icon.plus class="size-4" />
                                                    Erstes Ticket erstellen
                                                </flux:button>
                                            </div>
                                        </flux:cell>
                                    </flux:row>
                                @endforelse
                            </flux:rows>
                        </flux:table>

                        <!-- Pagination -->
                        @if($tickets->hasPages())
                            <div class="mt-4 border-t border-gray-200 pt-4">
                                {{ $tickets->links() }}
                            </div>
                        @endif
                    </div>
                </flux:card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Project Members -->
                <flux:card>
                    <flux:card.header>
                        <div class="flex items-center justify-between">
                            <flux:heading size="lg">Projekt-Mitglieder</flux:heading>
                            @if(auth()->user()->role->isDeveloper() || $project->created_by === auth()->id())
                                <flux:button size="sm" variant="outline" :href="route('projects.users', $project)" wire:navigate>
                                    <flux:icon.user-plus class="size-4" />
                                    Verwalten
                                </flux:button>
                            @endif
                        </div>
                    </flux:card.header>
                    
                    <div class="space-y-3">
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
                                    <flux:badge size="sm" variant="outline">Creator</flux:badge>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </flux:card>

                <!-- Project Statistics -->
                <flux:card>
                    <flux:card.header>
                        <flux:heading size="lg">Statistiken</flux:heading>
                    </flux:card.header>
                    
                    <div class="space-y-4">
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
                </flux:card>

                @if(auth()->user()->role->isDeveloper())
                    <!-- Firma Info -->
                    <flux:card>
                        <flux:card.header>
                            <flux:heading size="lg">Firma</flux:heading>
                        </flux:card.header>
                        
                        <div class="space-y-2">
                            <div class="font-medium">{{ $project->firma->name }}</div>
                            @if($project->firma->email)
                                <div class="text-sm text-gray-600">{{ $project->firma->email }}</div>
                            @endif
                            @if($project->firma->phone)
                                <div class="text-sm text-gray-600">{{ $project->firma->phone }}</div>
                            @endif
                            <div class="pt-2">
                                <flux:button size="sm" variant="ghost" :href="route('firmas.show', $project->firma)" wire:navigate>
                                    Firma anzeigen
                                </flux:button>
                            </div>
                        </div>
                    </flux:card>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>