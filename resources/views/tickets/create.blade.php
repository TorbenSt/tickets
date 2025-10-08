<x-layouts.app :title="'Ticket erstellen • ' . $project->name">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Neues Ticket erstellen</h1>
            <p class="text-gray-600 mt-2">
                Projekt: {{ $project->name }}
                @if(auth()->user()->role->isDeveloper())
                    • {{ $project->firma->name }}
                @endif
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-6 py-5">
            <form method="POST" action="{{ route('tickets.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">

                <!-- Ticket Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Titel *</label>
                    <div class="mt-1">
                        <input 
                            type="text"
                            id="title"
                            name="title" 
                            value="{{ old('title') }}" 
                            placeholder="z.B. Bug im Login-Bereich, Feature-Request für Dashboard"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('title') border-red-300 @enderror"
                        />
                    </div>
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">Kurzer, beschreibender Titel für das Ticket</p>
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
                        >{{ old('description') }}</textarea>
                    </div>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">
                        Je detaillierter die Beschreibung, desto schneller kann das Problem gelöst werden. 
                        Fügen Sie Schritte zur Reproduktion, erwartetes Verhalten und Screenshots hinzu.
                    </p>
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
                            <option value="">Priorität auswählen</option>
                            @foreach(\App\Enums\TicketPriority::cases() as $priority)
                                <option value="{{ $priority->value }}" @selected(old('priority') === $priority->value)>
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
                    <p class="mt-2 text-sm text-gray-500">
                        Wählen Sie "Notfall" nur für kritische Probleme, die sofortige Aufmerksamkeit benötigen
                    </p>
                </div>

                <!-- Estimated Hours -->
                <div>
                    <label for="estimated_hours" class="block text-sm font-medium text-gray-700">Geschätzte Stunden</label>
                    <div class="mt-1">
                        <input 
                            type="number" 
                            id="estimated_hours"
                            name="estimated_hours" 
                            value="{{ old('estimated_hours') }}" 
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
                    <p class="mt-2 text-sm text-gray-500">
                        Optional: Ihre Einschätzung, wie lange die Bearbeitung dauern könnte
                    </p>
                </div>

                <!-- Priority Information -->
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="space-y-3">
                        <div class="font-medium text-blue-900">Prioritäts-Richtlinien</div>
                        <div class="text-sm text-blue-800 space-y-2">
                            <div class="flex items-start space-x-2">
                                <div class="w-3 h-3 bg-gray-400 rounded-full mt-1"></div>
                                <div><strong>Überprüfung:</strong> Allgemeine Anfragen und kleine Verbesserungen</div>
                            </div>
                            <div class="flex items-start space-x-2">
                                <div class="w-3 h-3 bg-green-400 rounded-full mt-1"></div>
                                <div><strong>Normal:</strong> Standard-Features und nicht-kritische Bugs</div>
                            </div>
                            <div class="flex items-start space-x-2">
                                <div class="w-3 h-3 bg-yellow-400 rounded-full mt-1"></div>
                                <div><strong>ASAP:</strong> Wichtige Features oder Bugs die die Nutzung beeinträchtigen</div>
                            </div>
                            <div class="flex items-start space-x-2">
                                <div class="w-3 h-3 bg-red-400 rounded-full mt-1"></div>
                                <div><strong>Notfall:</strong> Kritische Bugs, Sicherheitsprobleme oder Systemausfälle</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-3 pt-4 border-t border-gray-200">
                    <button 
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring focus:ring-indigo-300 transition"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Ticket erstellen
                    </button>
                    
                    <a 
                        href="{{ route('projects.show', $project) }}" 
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

        <!-- Help Section -->
        <div class="bg-gray-50 overflow-hidden shadow rounded-lg">
            <div class="px-6 py-5 space-y-3">
                <div class="font-medium text-gray-900">Tipps für ein gutes Ticket</div>
                <div class="text-sm text-gray-600 space-y-2">
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Verwenden Sie einen klaren, beschreibenden Titel</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Beschreiben Sie Schritte zur Reproduktion bei Bugs</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Fügen Sie Screenshots oder Links hinzu, wenn relevant</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Wählen Sie die Priorität entsprechend der Dringlichkeit</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>