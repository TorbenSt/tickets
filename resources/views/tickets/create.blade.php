<x-layouts.app :title="'Ticket erstellen • ' . $project->name">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div>
            <flux:heading size="xl">Neues Ticket erstellen</flux:heading>
            <flux:subheading>
                Projekt: {{ $project->name }}
                @if(auth()->user()->role->isDeveloper())
                    • {{ $project->firma->name }}
                @endif
            </flux:subheading>
        </div>

        <!-- Form Card -->
        <flux:card>
            <form method="POST" action="{{ route('tickets.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">

                <!-- Ticket Title -->
                <div>
                    <flux:field>
                        <flux:label>Titel *</flux:label>
                        <flux:input 
                            name="title" 
                            value="{{ old('title') }}" 
                            placeholder="z.B. Bug im Login-Bereich, Feature-Request für Dashboard"
                            required
                        />
                        <flux:error name="title" />
                        <flux:description>Kurzer, beschreibender Titel für das Ticket</flux:description>
                    </flux:field>
                </div>

                <!-- Ticket Description -->
                <div>
                    <flux:field>
                        <flux:label>Beschreibung *</flux:label>
                        <flux:textarea 
                            name="description" 
                            rows="6" 
                            placeholder="Beschreiben Sie das Problem oder die Anforderung detailliert..."
                            required
                        >{{ old('description') }}</flux:textarea>
                        <flux:error name="description" />
                        <flux:description>
                            Je detaillierter die Beschreibung, desto schneller kann das Problem gelöst werden. 
                            Fügen Sie Schritte zur Reproduktion, erwartetes Verhalten und Screenshots hinzu.
                        </flux:description>
                    </flux:field>
                </div>

                <!-- Priority -->
                <div>
                    <flux:field>
                        <flux:label>Priorität *</flux:label>
                        <flux:select name="priority" required>
                            <option value="">Priorität auswählen</option>
                            @foreach(\App\Enums\TicketPriority::cases() as $priority)
                                <option value="{{ $priority->value }}" @selected(old('priority') === $priority->value)>
                                    {{ $priority->label() }}
                                    @if($priority->value === 'notfall')
                                        ⚠️
                                    @endif
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="priority" />
                        <flux:description>
                            Wählen Sie "Notfall" nur für kritische Probleme, die sofortige Aufmerksamkeit benötigen
                        </flux:description>
                    </flux:field>
                </div>

                <!-- Estimated Hours -->
                <div>
                    <flux:field>
                        <flux:label>Geschätzte Stunden</flux:label>
                        <flux:input 
                            type="number" 
                            name="estimated_hours" 
                            value="{{ old('estimated_hours') }}" 
                            step="0.5" 
                            min="0" 
                            max="999"
                            placeholder="z.B. 2.5"
                        />
                        <flux:error name="estimated_hours" />
                        <flux:description>
                            Optional: Ihre Einschätzung, wie lange die Bearbeitung dauern könnte
                        </flux:description>
                    </flux:field>
                </div>

                <!-- Priority Information -->
                <flux:card class="bg-blue-50 border-blue-200">
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
                </flux:card>

                <!-- Actions -->
                <div class="flex items-center space-x-3 pt-4 border-t border-gray-200">
                    <flux:button type="submit" variant="primary">
                        <flux:icon.plus class="size-4" />
                        Ticket erstellen
                    </flux:button>
                    
                    <flux:button type="button" variant="ghost" :href="route('projects.show', $project)" wire:navigate>
                        <flux:icon.x-mark class="size-4" />
                        Abbrechen
                    </flux:button>
                </div>
            </form>
        </flux:card>

        <!-- Help Section -->
        <flux:card class="bg-gray-50">
            <div class="space-y-3">
                <div class="font-medium text-gray-900">Tipps für ein gutes Ticket</div>
                <div class="text-sm text-gray-600 space-y-2">
                    <div class="flex items-start space-x-2">
                        <flux:icon.check class="size-4 text-green-500 mt-0.5" />
                        <span>Verwenden Sie einen klaren, beschreibenden Titel</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <flux:icon.check class="size-4 text-green-500 mt-0.5" />
                        <span>Beschreiben Sie Schritte zur Reproduktion bei Bugs</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <flux:icon.check class="size-4 text-green-500 mt-0.5" />
                        <span>Fügen Sie Screenshots oder Links hinzu, wenn relevant</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <flux:icon.check class="size-4 text-green-500 mt-0.5" />
                        <span>Wählen Sie die Priorität entsprechend der Dringlichkeit</span>
                    </div>
                </div>
            </div>
        </flux:card>
    </div>
</x-layouts.app>