<x-layouts.app title="Neues Projekt">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div>
            <flux:heading size="xl">Neues Projekt erstellen</flux:heading>
            <flux:subheading>Erstellen Sie ein neues Projekt für Ihre Firma</flux:subheading>
        </div>

        <!-- Form Card -->
        <flux:card>
            <form method="POST" action="{{ route('projects.store') }}" class="space-y-6">
                @csrf

                <!-- Project Name -->
                <div>
                    <flux:field>
                        <flux:label>Projektname *</flux:label>
                        <flux:input 
                            name="name" 
                            value="{{ old('name') }}" 
                            placeholder="z.B. Website Relaunch, Mobile App, etc."
                            required
                        />
                        <flux:error name="name" />
                        <flux:description>Ein eindeutiger Name für Ihr Projekt</flux:description>
                    </flux:field>
                </div>

                <!-- Project Description -->
                <div>
                    <flux:field>
                        <flux:label>Beschreibung</flux:label>
                        <flux:textarea 
                            name="description" 
                            rows="4" 
                            placeholder="Beschreiben Sie das Projekt, seine Ziele und wichtige Details..."
                        >{{ old('description') }}</flux:textarea>
                        <flux:error name="description" />
                        <flux:description>Eine detaillierte Beschreibung hilft Entwicklern, das Projekt besser zu verstehen</flux:description>
                    </flux:field>
                </div>

                <!-- Info Box -->
                <flux:card class="bg-blue-50 border-blue-200">
                    <div class="flex items-start space-x-3">
                        <flux:icon.information-circle class="size-5 text-blue-500 mt-0.5" />
                        <div class="text-blue-900">
                            <div class="font-medium">Projektinformationen</div>
                            <div class="text-sm text-blue-700 mt-1">
                                <ul class="space-y-1">
                                    <li>• Sie werden automatisch als Projektmitglied hinzugefügt</li>
                                    <li>• Das Projekt wird Ihrer Firma ({{ auth()->user()->firma->name }}) zugeordnet</li>
                                    <li>• Sie können später weitere Teammitglieder hinzufügen</li>
                                    <li>• Tickets können nur von Projektmitgliedern erstellt und bearbeitet werden</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </flux:card>

                <!-- Actions -->
                <div class="flex items-center space-x-3 pt-4 border-t border-gray-200">
                    <flux:button type="submit" variant="primary">
                        <flux:icon.plus class="size-4" />
                        Projekt erstellen
                    </flux:button>
                    
                    <flux:button type="button" variant="ghost" :href="route('projects.index')" wire:navigate>
                        <flux:icon.x-mark class="size-4" />
                        Abbrechen
                    </flux:button>
                </div>
            </form>
        </flux:card>

        <!-- Help Section -->
        <flux:card class="bg-gray-50">
            <div class="space-y-3">
                <div class="font-medium text-gray-900">Tipps für ein erfolgreiches Projekt</div>
                <div class="text-sm text-gray-600 space-y-2">
                    <div class="flex items-start space-x-2">
                        <flux:icon.check class="size-4 text-green-500 mt-0.5" />
                        <span>Wählen Sie einen beschreibenden und eindeutigen Projektnamen</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <flux:icon.check class="size-4 text-green-500 mt-0.5" />
                        <span>Beschreiben Sie die Projektziele und wichtigsten Anforderungen</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <flux:icon.check class="size-4 text-green-500 mt-0.5" />
                        <span>Fügen Sie alle relevanten Teammitglieder nach der Erstellung hinzu</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <flux:icon.check class="size-4 text-green-500 mt-0.5" />
                        <span>Erstellen Sie strukturierte Tickets für eine bessere Übersicht</span>
                    </div>
                </div>
            </div>
        </flux:card>
    </div>
</x-layouts.app>