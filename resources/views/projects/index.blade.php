<x-layouts.app title="Projekte">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Projekte</flux:heading>
                <flux:subheading>
                    @if(auth()->user()->role->isDeveloper())
                        Alle Projekte systemweit
                    @else
                        Projekte Ihrer Firma
                    @endif
                </flux:subheading>
            </div>
            
            @if(auth()->user()->role->isCustomer())
                <flux:button variant="primary" :href="route('projects.create')" wire:navigate>
                    <flux:icon.plus class="size-4" />
                    Neues Projekt
                </flux:button>
            @endif
        </div>

        <!-- Projects Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($projects as $project)
                <flux:card class="hover:shadow-lg transition-shadow">
                    <div class="space-y-4">
                        <!-- Project Header -->
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-lg text-gray-900">{{ $project->name }}</h3>
                                @if(auth()->user()->role->isDeveloper())
                                    <p class="text-sm text-gray-600">{{ $project->firma->name }}</p>
                                @endif
                            </div>
                            <flux:badge variant="outline" size="sm">
                                {{ $project->tickets_count }} Ticket{{ $project->tickets_count !== 1 ? 's' : '' }}
                            </flux:badge>
                        </div>

                        <!-- Project Description -->
                        @if($project->description)
                            <p class="text-gray-600 text-sm line-clamp-3">{{ $project->description }}</p>
                        @else
                            <p class="text-gray-400 text-sm italic">Keine Beschreibung vorhanden</p>
                        @endif

                        <!-- Project Stats -->
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                            <div class="text-center">
                                <div class="text-lg font-semibold text-blue-600">{{ $project->tickets_count }}</div>
                                <div class="text-xs text-gray-500">Tickets</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-green-600">{{ $project->created_at->format('m/Y') }}</div>
                                <div class="text-xs text-gray-500">Erstellt</div>
                            </div>
                        </div>

                        <!-- Project Creator -->
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <flux:icon.user class="size-4" />
                            <span>Erstellt von {{ $project->creator->name }}</span>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <flux:button size="sm" variant="primary" :href="route('projects.show', $project)" wire:navigate class="flex-1">
                                <flux:icon.eye class="size-4" />
                                Anzeigen
                            </flux:button>
                            
                            @if(auth()->user()->role->isDeveloper() || $project->created_by === auth()->id())
                                <flux:button size="sm" variant="ghost" :href="route('projects.users', $project)" wire:navigate>
                                    <flux:icon.users class="size-4" />
                                    Benutzer
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </flux:card>
            @empty
                <div class="col-span-full">
                    <flux:card class="text-center py-12">
                        <div class="space-y-4">
                            <flux:icon.folder-plus class="size-16 text-gray-400 mx-auto" />
                            <div>
                                <h3 class="font-medium text-gray-900">Keine Projekte vorhanden</h3>
                                <p class="text-gray-500">
                                    @if(auth()->user()->role->isCustomer())
                                        Erstellen Sie Ihr erstes Projekt, um Tickets zu verwalten.
                                    @else
                                        Es wurden noch keine Projekte angelegt.
                                    @endif
                                </p>
                            </div>
                            @if(auth()->user()->role->isCustomer())
                                <flux:button variant="primary" :href="route('projects.create')" wire:navigate>
                                    <flux:icon.plus class="size-4" />
                                    Erstes Projekt erstellen
                                </flux:button>
                            @endif
                        </div>
                    </flux:card>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($projects->hasPages())
            <div class="flex justify-center">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>