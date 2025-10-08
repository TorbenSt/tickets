<x-layouts.app title="Neues Projekt">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Neues Projekt erstellen</h1>
            <p class="text-gray-600 mt-2">Erstellen Sie ein neues Projekt für Ihre Firma</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-6 py-5">
            <form method="POST" action="{{ route('projects.store') }}" class="space-y-6">
                @csrf

                <!-- Project Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Projektname *</label>
                    <div class="mt-1">
                        <input 
                            type="text"
                            id="name"
                            name="name" 
                            value="{{ old('name') }}" 
                            placeholder="z.B. Website Relaunch, Mobile App, etc."
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('name') border-red-300 @enderror"
                        />
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">Ein eindeutiger Name für Ihr Projekt</p>
                </div>

                <!-- Project Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Beschreibung</label>
                    <div class="mt-1">
                        <textarea 
                            id="description"
                            name="description" 
                            rows="4" 
                            placeholder="Beschreiben Sie das Projekt, seine Ziele und wichtige Details..."
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('description') border-red-300 @enderror"
                        >{{ old('description') }}</textarea>
                    </div>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">Eine detaillierte Beschreibung hilft Entwicklern, das Projekt besser zu verstehen</p>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
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
                        Projekt erstellen
                    </button>
                    
                    <a 
                        href="{{ route('projects.index') }}" 
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
                <div class="font-medium text-gray-900">Tipps für ein erfolgreiches Projekt</div>
                <div class="text-sm text-gray-600 space-y-2">
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Wählen Sie einen beschreibenden und eindeutigen Projektnamen</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Beschreiben Sie die Projektziele und wichtigsten Anforderungen</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Fügen Sie alle relevanten Teammitglieder nach der Erstellung hinzu</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Erstellen Sie strukturierte Tickets für eine bessere Übersicht</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>