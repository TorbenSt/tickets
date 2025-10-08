<x-layouts.app :title="$ticket->title">
    <div class="max-w-4xl space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center space-x-3">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $ticket->title }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        @if($ticket->status->color() === 'orange') bg-orange-100 text-orange-800
                        @elseif($ticket->status->color() === 'blue') bg-blue-100 text-blue-800
                        @elseif($ticket->status->color() === 'green') bg-green-100 text-green-800
                        @elseif($ticket->status->color() === 'yellow') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ $ticket->status->label() }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        @if($ticket->priority->value === 'notfall') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        @if($ticket->priority->value === 'notfall')
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        @endif
                        {{ $ticket->priority->label() }}
                    </span>
                </div>
                <p class="text-gray-600 mt-2">
                    Projekt: {{ $ticket->project->name }}
                    @if(auth()->user()->role->isDeveloper())
                        • {{ $ticket->project->firma->name }}
                    @endif
                </p>
            </div>
            
            <div class="flex items-center space-x-2">
                @if($ticket->canBeEditedBy(auth()->user()))
                    <a 
                        href="{{ route('tickets.edit', $ticket) }}" 
                        wire:navigate
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Bearbeiten
                    </a>
                @endif
                
                <a 
                    href="{{ route('tickets.index') }}" 
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

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Beschreibung</h3>
                    </div>
                    <div class="px-6 py-5">
                        <div class="prose max-w-none">
                            {!! nl2br(e($ticket->description)) !!}
                        </div>
                    </div>
                </div>

                <!-- Developer Actions -->
                @if(auth()->user()->role->isDeveloper())
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Developer Aktionen</h3>
                        </div>
                        
                        <div class="px-6 py-5 space-y-4">
                            <!-- Status Update -->
                            <form method="POST" action="{{ route('tickets.update-status', $ticket) }}" class="flex items-center space-x-3">
                                @csrf
                                @method('PATCH')
                                
                                <div class="flex-1">
                                    <select 
                                        name="status" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    >
                                        @foreach(\App\Enums\TicketStatus::cases() as $status)
                                            <option value="{{ $status->value }}" @selected($status === $ticket->status)>
                                                {{ $status->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <button 
                                    type="submit"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Status ändern
                                </button>
                            </form>

                            <!-- Assignment -->
                            <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="flex items-center space-x-3">
                                @csrf
                                @method('PATCH')
                                
                                <div class="flex-1">
                                    <select 
                                        name="assigned_to"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    >
                                        <option value="">Nicht zugewiesen</option>
                                        @foreach(\App\Models\User::where('role', \App\Enums\UserRole::DEVELOPER)->get() as $dev)
                                            <option value="{{ $dev->id }}" @selected($ticket->assigned_to === $dev->id)>
                                                {{ $dev->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <button 
                                    type="submit"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Zuweisen
                                </button>
                            </form>

                            <!-- Actual Hours -->
                            @if($ticket->status->value !== 'open' && $ticket->status->value !== 'todo')
                                <form method="POST" action="{{ route('tickets.update-status', $ticket) }}" class="flex items-center space-x-3">
                                    @csrf
                                    @method('PATCH')
                                    
                                    <div class="flex-1">
                                        <input 
                                            type="number" 
                                            name="actual_hours" 
                                            step="0.5" 
                                            min="0"
                                            value="{{ $ticket->actual_hours }}"
                                            placeholder="Tatsächliche Stunden"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        />
                                    </div>
                                    
                                    <button 
                                        type="submit"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-500"
                                    >
                                        Stunden aktualisieren
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Ticket Info -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Ticket-Informationen</h3>
                    </div>
                    
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-700">Status</div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1
                                @if($ticket->status->color() === 'orange') bg-orange-100 text-orange-800
                                @elseif($ticket->status->color() === 'blue') bg-blue-100 text-blue-800
                                @elseif($ticket->status->color() === 'green') bg-green-100 text-green-800
                                @elseif($ticket->status->color() === 'yellow') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $ticket->status->label() }}
                            </span>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-gray-700">Priorität</div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1
                                @if($ticket->priority->value === 'notfall') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @if($ticket->priority->value === 'notfall')
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                                {{ $ticket->priority->label() }}
                            </span>
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
                </div>

                <!-- Time Tracking -->
                @if($ticket->estimated_hours || $ticket->actual_hours)
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Zeiterfassung</h3>
                        </div>
                        
                        <div class="px-6 py-5 space-y-4">
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
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-sm font-medium">
                                                Überzogen: +{{ $ticket->actual_hours - $ticket->estimated_hours }}h
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex items-center space-x-2 text-green-600">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-sm font-medium">Im Zeitplan</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>