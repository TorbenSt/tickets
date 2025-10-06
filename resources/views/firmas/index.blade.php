<x-layouts.app title="Firmen">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Firmen-Übersicht</flux:heading>
                <flux:subheading>Alle Firmen im System verwalten</flux:subheading>
            </div>
        </div>

        <!-- Firmen Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($firmas as $firma)
                <flux:card class="hover:shadow-lg transition-shadow">
                    <div class="space-y-4">
                        <!-- Firma Header -->
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-lg text-gray-900">{{ $firma->name }}</h3>
                                @if($firma->email)
                                    <p class="text-sm text-gray-600">{{ $firma->email }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Firma Description -->
                        @if($firma->description)
                            <p class="text-gray-600 text-sm line-clamp-3">{{ $firma->description }}</p>
                        @endif

                        <!-- Contact Info -->
                        <div class="space-y-2 text-sm">
                            @if($firma->phone)
                                <div class="flex items-center space-x-2 text-gray-600">
                                    <flux:icon.phone class="size-4" />
                                    <span>{{ $firma->phone }}</span>
                                </div>
                            @endif
                            
                            @if($firma->address)
                                <div class="flex items-center space-x-2 text-gray-600">
                                    <flux:icon.map-pin class="size-4" />
                                    <span>{{ $firma->address }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                            <div class="text-center">
                                <div class="text-lg font-semibold text-blue-600">{{ $firma->projects_count }}</div>
                                <div class="text-xs text-gray-500">Projekte</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-green-600">{{ $firma->users_count }}</div>
                                <div class="text-xs text-gray-500">Benutzer</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <flux:button size="sm" variant="primary" :href="route('firmas.show', $firma)" wire:navigate class="flex-1">
                                <flux:icon.eye class="size-4" />
                                Details
                            </flux:button>
                        </div>
                    </div>
                </flux:card>
            @empty
                <div class="col-span-full">
                    <flux:card class="text-center py-12">
                        <div class="space-y-4">
                            <flux:icon.building-office class="size-16 text-gray-400 mx-auto" />
                            <div>
                                <h3 class="font-medium text-gray-900">Keine Firmen vorhanden</h3>
                                <p class="text-gray-500">Es sind noch keine Firmen im System registriert.</p>
                            </div>
                        </div>
                    </flux:card>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($firmas->hasPages())
            <div class="flex justify-center">
                {{ $firmas->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>