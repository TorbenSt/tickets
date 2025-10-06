<x-layouts.app title="Notfall-Tickets">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="flex items-center space-x-2">
                    <flux:icon.exclamation-triangle class="size-6 text-red-500" />
                    <span>Notfall-Tickets</span>
                </flux:heading>
                <flux:subheading class="text-red-600">
                    Tickets mit höchster Priorität - Sofortige Aufmerksamkeit erforderlich
                </flux:subheading>
            </div>
            
            <flux:button variant="outline" :href="route('tickets.index')" wire:navigate>
                <flux:icon.arrow-left class="size-4" />
                Zurück zu allen Tickets
            </flux:button>
        </div>

        <!-- Emergency Alert -->
        @if($tickets->count() > 0)
            <flux:card class="border-red-200 bg-red-50">
                <div class="flex items-center space-x-3">
                    <flux:icon.exclamation-triangle class="size-6 text-red-500" />
                    <div>
                        <div class="font-semibold text-red-900">{{ $tickets->count() }} Notfall-Ticket(s) benötigen sofortige Aufmerksamkeit</div>
                        <div class="text-red-700">Diese Tickets haben die höchste Priorität und sollten unverzüglich bearbeitet werden.</div>
                    </div>
                </div>
            </flux:card>
        @endif

        <!-- Tickets Table -->
        <flux:card>
            <div class="overflow-hidden">
                <flux:table>
                    <flux:columns>
                        <flux:column>Titel</flux:column>
                        <flux:column>Projekt</flux:column>
                        <flux:column>Firma</flux:column>
                        <flux:column>Status</flux:column>
                        <flux:column>Ersteller</flux:column>
                        <flux:column>Zugewiesen</flux:column>
                        <flux:column>Erstellt</flux:column>
                        <flux:column>Geschätzt</flux:column>
                        <flux:column></flux:column>
                    </flux:columns>

                    <flux:rows>
                        @forelse($tickets as $ticket)
                            <flux:row class="bg-red-50 border-l-4 border-red-400">
                                <flux:cell>
                                    <div class="flex items-center space-x-2">
                                        <flux:icon.exclamation-triangle class="size-4 text-red-500" />
                                        <div>
                                            <div class="font-medium text-red-900">{{ $ticket->title }}</div>
                                            @if($ticket->description)
                                                <div class="text-sm text-red-700 truncate">{{ Str::limit($ticket->description, 50) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </flux:cell>
                                
                                <flux:cell>
                                    <span class="font-medium">{{ $ticket->project->name }}</span>
                                </flux:cell>
                                
                                <flux:cell>
                                    <span class="text-sm">{{ $ticket->project->firma->name }}</span>
                                </flux:cell>
                                
                                <flux:cell>
                                    <flux:badge :color="$ticket->status->color()" size="sm">
                                        {{ $ticket->status->label() }}
                                    </flux:badge>
                                </flux:cell>
                                
                                <flux:cell>
                                    <div class="text-sm">{{ $ticket->creator->name }}</div>
                                </flux:cell>
                                
                                <flux:cell>
                                    @if($ticket->assignee)
                                        <div class="text-sm font-medium">{{ $ticket->assignee->name }}</div>
                                    @else
                                        <div class="flex items-center space-x-1">
                                            <flux:icon.user-plus class="size-4 text-red-500" />
                                            <span class="text-red-600 font-medium">Nicht zugewiesen</span>
                                        </div>
                                    @endif
                                </flux:cell>
                                
                                <flux:cell>
                                    <div class="text-sm">{{ $ticket->created_at->format('d.m.Y H:i') }}</div>
                                    <div class="text-xs text-gray-500">{{ $ticket->created_at->diffForHumans() }}</div>
                                </flux:cell>
                                
                                <flux:cell>
                                    @if($ticket->estimated_hours)
                                        <span class="text-sm">{{ $ticket->estimated_hours }}h</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </flux:cell>
                                
                                <flux:cell>
                                    <flux:button size="sm" variant="primary" :href="route('tickets.show', $ticket)" wire:navigate>
                                        Sofort bearbeiten
                                    </flux:button>
                                </flux:cell>
                            </flux:row>
                        @empty
                            <flux:row>
                                <flux:cell class="text-center py-8" colspan="9">
                                    <div class="flex flex-col items-center space-y-2">
                                        <flux:icon.check-circle class="size-12 text-green-500" />
                                        <div class="font-medium text-gray-900">Keine Notfall-Tickets!</div>
                                        <div class="text-gray-500">Alle kritischen Tickets wurden bearbeitet.</div>
                                    </div>
                                </flux:cell>
                            </flux:row>
                        @endforelse
                    </flux:rows>
                </flux:table>
            </div>

            <!-- Pagination -->
            @if($tickets->hasPages())
                <div class="mt-4 border-t border-gray-200 pt-4">
                    {{ $tickets->links() }}
                </div>
            @endif
        </flux:card>
    </div>
</x-layouts.app>