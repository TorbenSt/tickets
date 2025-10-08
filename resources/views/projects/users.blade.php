<x-layouts.app :title="'Benutzer verwalten • ' . $project->name">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Benutzer verwalten</h1>
                <p class="text-gray-600 mt-2">
                    Projekt: {{ $project->name }}
                    @if(auth()->user()->role->isDeveloper())
                        • {{ $project->firma->name }}
                    @endif
                </p>
            </div>
            
            <a 
                href="{{ route('projects.show', $project) }}" 
                wire:navigate
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Zurück zum Projekt
            </a>
        </div>

        <!-- Add User Section -->
        @if($availableUsers->count() > 0)
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Benutzer hinzufügen</h3>
                </div>
                
                <div class="px-6 py-5">
                    <form method="POST" action="{{ route('projects.add-user', $project) }}" class="space-y-4">
                        @csrf
                        
                        <div class="flex items-end space-x-3">
                            <div class="flex-1">
                                <label for="user_id" class="block text-sm font-medium text-gray-700">Verfügbare Benutzer</label>
                                <div class="mt-1">
                                    <select 
                                        id="user_id" 
                                        name="user_id" 
                                        required
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('user_id') border-red-300 @enderror"
                                    >
                                        <option value="">Benutzer auswählen...</option>
                                        @foreach($availableUsers as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('user_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <button 
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring focus:ring-indigo-300 transition"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                Hinzufügen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Current Members -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Aktuelle Projekt-Mitglieder ({{ $project->users->count() }})</h3>
            </div>
            
            <div class="overflow-hidden">
                <div class="flow-root">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Benutzer</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rolle im Projekt</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hinzugefügt am</th>
                                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Aktionen</span></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($project->users as $user)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-600">
                                                            {{ substr($user->name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $user->role->label() }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $user->email }}
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($user->id === $project->created_by)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                        </svg>
                                                        Projekt-Ersteller
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        Mitglied
                                                    </span>
                                                @endif
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $user->pivot->created_at ? $user->pivot->created_at->format('d.m.Y') : 'Unbekannt' }}
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                @if($user->id !== $project->created_by)
                                                    <form method="POST" action="{{ route('projects.remove-user', [$project, $user]) }}" 
                                                          onsubmit="return confirm('Sind Sie sicher, dass Sie {{ $user->name }} vom Projekt entfernen möchten?')"
                                                          class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button 
                                                            type="submit"
                                                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                        >
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H8m12 0l-4-4m4 4l-4 4M9 4H7a2 2 0 00-2 2v12a2 2 0 002 2h2"></path>
                                                            </svg>
                                                            Entfernen
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-gray-400 text-sm">Nicht entfernbar</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Sections -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Permission Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg overflow-hidden shadow">
                <div class="px-6 py-5 space-y-3">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <div class="font-medium text-blue-900">Berechtigungen</div>
                    </div>
                    <div class="text-sm text-blue-800 space-y-2">
                        <div>• Projekt-Mitglieder können alle Tickets im Projekt sehen und bearbeiten</div>
                        <div>• Nur der Projekt-Ersteller und Developer können Benutzer verwalten</div>
                        <div>• Der Projekt-Ersteller kann nicht entfernt werden</div>
                        <div>• Developer haben automatisch Zugriff auf alle Projekte</div>
                    </div>
                </div>
            </div>

            <!-- Available Users Info -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg overflow-hidden shadow">
                <div class="px-6 py-5 space-y-3">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
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
            </div>
        </div>
    </div>
</x-layouts.app>