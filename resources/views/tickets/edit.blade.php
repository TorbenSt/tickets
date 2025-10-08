<x-layouts.app :title="'Ticket bearbeiten • #' . $ticket->id">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Ticket bearbeiten</h1>
            <p class="text-gray-600 mt-2">
                #{{ $ticket->id }} • {{ $ticket->project->name }}
                @if(auth()->user()->role->isDeveloper())
                    • {{ $ticket->project->firma->name }}
                @endif
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-6 py-5">
            <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <!-- Ticket Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Titel *</label>
                    <div class="mt-1">
                        <input 
                            type="text"
                            id="title"
                            name="title" 
                            value="{{ old('title', $ticket->title) }}" 
                            placeholder="z.B. Bug im Login-Bereich, Feature-Request für Dashboard"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('title') border-red-300 @enderror"
                        />
                    </div>
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ticket Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Beschreibung *</label>
                    <div class="mt-1">
                        <textarea 
                            id="description"
                            name="description" 
                            rows="6" 
                            placeholder="Beschreiben Sie das Problem oder die Anforderung detailliert..."
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('description') border-red-300 @enderror"
                        >{{ old('description', $ticket->description) }}</textarea>
                    </div>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">Priorität *</label>
                    <div class="mt-1">
                        <select 
                            id="priority"
                            name="priority" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('priority') border-red-300 @enderror"
                        >
                            @foreach(\App\Enums\TicketPriority::cases() as $priority)
                                <option value="{{ $priority->value }}" @selected(old('priority', $ticket->priority->value) === $priority->value)>
                                    {{ $priority->label() }}
                                    @if($priority->value === 'notfall')
                                        ⚠️
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('priority')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if(auth()->user()->role->isDeveloper())
                <!-- Status (nur für Entwickler) -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <div class="mt-1">
                        <select 
                            id="status"
                            name="status" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('status') border-red-300 @enderror"
                        >
                            @foreach(\App\Enums\TicketStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $ticket->status->value) === $status->value)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <!-- Estimated Hours -->
                <div>
                    <label for="estimated_hours" class="block text-sm font-medium text-gray-700">Geschätzte Stunden</label>
                    <div class="mt-1">
                        <input 
                            type="number" 
                            id="estimated_hours"
                            name="estimated_hours" 
                            value="{{ old('estimated_hours', $ticket->estimated_hours) }}" 
                            step="0.5" 
                            min="0" 
                            max="999"
                            placeholder="z.B. 2.5"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('estimated_hours') border-red-300 @enderror"
                        />
                    </div>
                    @error('estimated_hours')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-3 pt-4 border-t border-gray-200">
                    <button 
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring focus:ring-indigo-300 transition"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Ticket aktualisieren
                    </button>
                    
                    <a 
                        href="{{ route('tickets.show', $ticket) }}" 
                        wire:navigate
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring focus:ring-indigo-300 transition"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Abbrechen
                    </a>
                </div>
            </form>
            </div>
        </div>

        <!-- Current Status Info -->
        <div class="bg-gray-50 overflow-hidden shadow rounded-lg">
            <div class="px-6 py-5">
                <div class="font-medium text-gray-900 mb-3">Ticket-Informationen</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Status:</span>
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->status->color() }}">
                            {{ $ticket->status->label() }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500">Priorität:</span>
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->priority->color() }}">
                            {{ $ticket->priority->label() }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500">Erstellt:</span>
                        <span class="ml-2">{{ $ticket->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if($ticket->assigned_to)
                    <div>
                        <span class="text-gray-500">Zugewiesen:</span>
                        <span class="ml-2">{{ $ticket->assignedTo->name }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>