<x-layouts.app :title="'Benutzer verwalten • ' . $project->name">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <flux:heading size="xl">Benutzer verwalten</flux:heading>
                <flux:subheading>
                    Projekt: {{ $project->name }}
                    @if(auth()->user()->role->isDeveloper())
                        • {{ $project->firma->name }}
                    @endif
                </flux:subheading>
            </div>
            
            <flux:button variant="ghost" :href="route('projects.show', $project)" wire:navigate>
                <flux:icon.arrow-left class="size-4" />
                Zurück zum Projekt
            </flux:button>
        </div>

        <!-- Add User Section -->
        @if($availableUsers->count() > 0)
            <flux:card>
                <flux:card.header>
                    <flux:heading size="lg">Benutzer hinzufügen</flux:heading>
                </flux:card.header>
                
                <form method="POST" action="{{ route('projects.add-user', $project) }}" class="space-y-4">
                    @csrf
                    
                    <div class="flex items-end space-x-3">
                        <div class="flex-1">
                            <flux:field>
                                <flux:label>Verfügbare Benutzer</flux:label>
                                <flux:select name="user_id" required>
                                    <option value="">Benutzer auswählen...</option>
                                    @foreach($availableUsers as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="user_id" />
                            </flux:field>
                        </div>
                        
                        <flux:button type="submit" variant="primary">
                            <flux:icon.user-plus class="size-4" />
                            Hinzufügen
                        </flux:button>
                    </div>
                </form>
            </flux:card>
        @endif

        <!-- Current Members -->
        <flux:card>
            <flux:card.header>
                <flux:heading size="lg">Aktuelle Projekt-Mitglieder ({{ $project->users->count() }})</flux:heading>
            </flux:card.header>
            
            <div class="overflow-hidden">
                <flux:table>
                    <flux:columns>
                        <flux:column>Benutzer</flux:column>
                        <flux:column>Email</flux:column>
                        <flux:column>Rolle im Projekt</flux:column>
                        <flux:column>Hinzugefügt am</flux:column>
                        <flux:column></flux:column>
                    </flux:columns>

                    <flux:rows>
                        @foreach($project->users as $user)
                            <flux:row>
                                <flux:cell>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-600">
                                                {{ substr($user->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->role->label() }}</div>
                                        </div>
                                    </div>
                                </flux:cell>
                                
                                <flux:cell>
                                    <div class="text-sm">{{ $user->email }}</div>
                                </flux:cell>
                                
                                <flux:cell>
                                    @if($user->id === $project->created_by)
                                        <flux:badge variant="primary" size="sm">
                                            <flux:icon.star class="size-3" />
                                            Projekt-Ersteller
                                        </flux:badge>
                                    @else
                                        <flux:badge variant="outline" size="sm">
                                            Mitglied
                                        </flux:badge>
                                    @endif
                                </flux:cell>
                                
                                <flux:cell>
                                    <div class="text-sm text-gray-500">
                                        {{ $user->pivot->created_at ? $user->pivot->created_at->format('d.m.Y') : 'Unbekannt' }}
                                    </div>
                                </flux:cell>
                                
                                <flux:cell>
                                    @if($user->id !== $project->created_by)
                                        <form method="POST" action="{{ route('projects.remove-user', [$project, $user]) }}" 
                                              onsubmit="return confirm('Sind Sie sicher, dass Sie {{ $user->name }} vom Projekt entfernen möchten?')"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <flux:button type="submit" size="sm" variant="danger">
                                                <flux:icon.user-minus class="size-4" />
                                                Entfernen
                                            </flux:button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 text-sm">Nicht entfernbar</span>
                                    @endif
                                </flux:cell>
                            </flux:row>
                        @endforeach
                    </flux:rows>
                </flux:table>
            </div>
        </flux:card>

        <!-- Info Sections -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Permission Info -->
            <flux:card class="bg-blue-50 border-blue-200">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2">
                        <flux:icon.information-circle class="size-5 text-blue-500" />
                        <div class="font-medium text-blue-900">Berechtigungen</div>
                    </div>
                    <div class="text-sm text-blue-800 space-y-2">
                        <div>• Projekt-Mitglieder können alle Tickets im Projekt sehen und bearbeiten</div>
                        <div>• Nur der Projekt-Ersteller und Developer können Benutzer verwalten</div>
                        <div>• Der Projekt-Ersteller kann nicht entfernt werden</div>
                        <div>• Developer haben automatisch Zugriff auf alle Projekte</div>
                    </div>
                </div>
            </flux:card>

            <!-- Available Users Info -->
            <flux:card class="bg-gray-50 border-gray-200">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2">
                        <flux:icon.users class="size-5 text-gray-500" />
                        <div class="font-medium text-gray-900">Verfügbare Benutzer</div>
                    </div>
                    <div class="text-sm text-gray-600 space-y-2">
                        @if($availableUsers->count() > 0)
                            <div>{{ $availableUsers->count() }} Benutzer aus {{ $project->firma->name }} können hinzugefügt werden</div>
                            @if(auth()->user()->role->isDeveloper())
                                <div>Als Developer können Sie alle Firma-Benutzer hinzufügen</div>
                            @else
                                <div>Sie können nur Benutzer Ihrer eigenen Firma hinzufügen</div>
                            @endif
                        @else
                            <div>Alle verfügbaren Benutzer sind bereits Projekt-Mitglieder</div>
                            <div class="text-xs text-gray-500">Neue Benutzer müssen zuerst der Firma hinzugefügt werden</div>
                        @endif
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</x-layouts.app>