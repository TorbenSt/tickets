<x-layouts.app :title="$ticket->title">
    <div class="max-w-4xl space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center space-x-3">
                    <flux:heading size="xl">{{ $ticket->title }}</flux:heading>
                    <flux:badge :color="$ticket->status->color()">
                        {{ $ticket->status->label() }}
                    </flux:badge>
                    <flux:badge variant="{{ $ticket->priority->value === 'notfall' ? 'danger' : 'outline' }}" size="sm">
                        @if($ticket->priority->value === 'notfall')
                            <flux:icon.exclamation-triangle class="size-3" />
                        @endif
                        {{ $ticket->priority->label() }}
                    </flux:badge>
                </div>
                <flux:subheading class="mt-2">
                    Projekt: {{ $ticket->project->name }}
                    @if(auth()->user()->role->isDeveloper())
                        • {{ $ticket->project->firma->name }}
                    @endif
                </flux:subheading>
            </div>
            
            <div class="flex items-center space-x-2">
                @if($ticket->canBeEditedBy(auth()->user()))
                    <flux:button variant="outline" href="{{ route('tickets.edit', $ticket) }}" wire:navigate>
                        <flux:icon.pencil class="size-4" />
                        Bearbeiten
                    </flux:button>
                @endif
                
                <flux:button variant="ghost" href="{{ route('tickets.index') }}" wire:navigate>
                    <flux:icon.arrow-left class="size-4" />
                    Zurück
                </flux:button>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                <flux:card>
                    <flux:card.header>
                        <flux:heading size="lg">Beschreibung</flux:heading>
                    </flux:card.header>
                    <div class="prose max-w-none">
                        {!! nl2br(e($ticket->description)) !!}
                    </div>
                </flux:card>

                <!-- Developer Actions -->
                @if(auth()->user()->role->isDeveloper())
                    <flux:card>
                        <flux:card.header>
                            <flux:heading size="lg">Developer Aktionen</flux:heading>
                        </flux:card.header>
                        
                        <div class="space-y-4">
                            <!-- Status Update -->
                            <form method="POST" action="{{ route('tickets.update-status', $ticket) }}" class="flex items-center space-x-3">
                                @csrf
                                @method('PATCH')
                                
                                <div class="flex-1">
                                    <flux:select name="status" value="{{ $ticket->status->value }}">
                                        @foreach(\App\Enums\TicketStatus::cases() as $status)
                                            <option value="{{ $status->value }}" @selected($status === $ticket->status)>
                                                {{ $status->label() }}
                                            </option>
                                        @endforeach
                                    </flux:select>
                                </div>
                                
                                <flux:button type="submit" size="sm" variant="primary">
                                    Status ändern
                                </flux:button>
                            </form>

                            <!-- Assignment -->
                            <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="flex items-center space-x-3">
                                @csrf
                                @method('PATCH')
                                
                                <div class="flex-1">
                                    <flux:select name="assigned_to">
                                        <option value="">Nicht zugewiesen</option>
                                        @foreach(\App\Models\User::where('role', \App\Enums\UserRole::DEVELOPER)->get() as $dev)
                                            <option value="{{ $dev->id }}" @selected($ticket->assigned_to === $dev->id)>
                                                {{ $dev->name }}
                                            </option>
                                        @endforeach
                                    </flux:select>
                                </div>
                                
                                <flux:button type="submit" size="sm" variant="outline">
                                    Zuweisen
                                </flux:button>
                            </form>

                            <!-- Actual Hours -->
                            @if($ticket->status->value !== 'open' && $ticket->status->value !== 'todo')
                                <form method="POST" action="{{ route('tickets.update-status', $ticket) }}" class="flex items-center space-x-3">
                                    @csrf
                                    @method('PATCH')
                                    
                                    <div class="flex-1">
                                        <flux:input 
                                            type="number" 
                                            name="actual_hours" 
                                            step="0.5" 
                                            min="0"
                                            value="{{ $ticket->actual_hours }}"
                                            placeholder="Tatsächliche Stunden"
                                        />
                                    </div>
                                    
                                    <flux:button type="submit" size="sm" variant="ghost">
                                        Stunden aktualisieren
                                    </flux:button>
                                </form>
                            @endif
                        </div>
                    </flux:card>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Ticket Info -->
                <flux:card>
                    <flux:card.header>
                        <flux:heading size="lg">Ticket-Informationen</flux:heading>
                    </flux:card.header>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-700">Status</div>
                            <flux:badge :color="$ticket->status->color()" class="mt-1">
                                {{ $ticket->status->label() }}
                            </flux:badge>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-gray-700">Priorität</div>
                            <flux:badge variant="{{ $ticket->priority->value === 'notfall' ? 'danger' : 'outline' }}" class="mt-1">
                                @if($ticket->priority->value === 'notfall')
                                    <flux:icon.exclamation-triangle class="size-3" />
                                @endif
                                {{ $ticket->priority->label() }}
                            </flux:badge>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-gray-700">Projekt</div>
                            <div class="text-sm text-gray-600 mt-1">
                                <a href="{{ route('projects.show', $ticket->project) }}" class="text-blue-600 hover:text-blue-800" wire:navigate>
                                    {{ $ticket->project->name }}
                                </a>
                            </div>
                        </div>

                        @if(auth()->user()->role->isDeveloper())
                            <div>
                                <div class="text-sm font-medium text-gray-700">Firma</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    <a href="{{ route('firmas.show', $ticket->project->firma) }}" class="text-blue-600 hover:text-blue-800" wire:navigate>
                                        {{ $ticket->project->firma->name }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        <div>
                            <div class="text-sm font-medium text-gray-700">Erstellt von</div>
                            <div class="text-sm text-gray-600 mt-1">{{ $ticket->creator->name }}</div>
                        </div>

                        @if($ticket->assignee)
                            <div>
                                <div class="text-sm font-medium text-gray-700">Zugewiesen an</div>
                                <div class="text-sm text-gray-600 mt-1">{{ $ticket->assignee->name }}</div>
                            </div>
                        @endif

                        <div>
                            <div class="text-sm font-medium text-gray-700">Erstellt am</div>
                            <div class="text-sm text-gray-600 mt-1">
                                {{ $ticket->created_at->format('d.m.Y H:i') }}
                                <span class="text-gray-400">({{ $ticket->created_at->diffForHumans() }})</span>
                            </div>
                        </div>

                        @if($ticket->updated_at != $ticket->created_at)
                            <div>
                                <div class="text-sm font-medium text-gray-700">Aktualisiert am</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    {{ $ticket->updated_at->format('d.m.Y H:i') }}
                                    <span class="text-gray-400">({{ $ticket->updated_at->diffForHumans() }})</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </flux:card>

                <!-- Time Tracking -->
                @if($ticket->estimated_hours || $ticket->actual_hours)
                    <flux:card>
                        <flux:card.header>
                            <flux:heading size="lg">Zeiterfassung</flux:heading>
                        </flux:card.header>
                        
                        <div class="space-y-4">
                            @if($ticket->estimated_hours)
                                <div>
                                    <div class="text-sm font-medium text-gray-700">Geschätzte Stunden</div>
                                    <div class="text-lg font-semibold text-blue-600">{{ $ticket->estimated_hours }}h</div>
                                </div>
                            @endif

                            @if($ticket->actual_hours)
                                <div>
                                    <div class="text-sm font-medium text-gray-700">Tatsächliche Stunden</div>
                                    <div class="text-lg font-semibold text-green-600">{{ $ticket->actual_hours }}h</div>
                                </div>
                            @endif

                            @if($ticket->estimated_hours && $ticket->actual_hours)
                                <div class="pt-2 border-t border-gray-200">
                                    @if($ticket->actual_hours > $ticket->estimated_hours)
                                        <div class="flex items-center space-x-2 text-red-600">
                                            <flux:icon.exclamation-triangle class="size-4" />
                                            <span class="text-sm font-medium">
                                                Überzogen: +{{ $ticket->actual_hours - $ticket->estimated_hours }}h
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex items-center space-x-2 text-green-600">
                                            <flux:icon.check-circle class="size-4" />
                                            <span class="text-sm font-medium">Im Zeitplan</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </flux:card>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>