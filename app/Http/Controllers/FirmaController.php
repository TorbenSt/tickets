<?php

namespace App\Http\Controllers;

use App\Models\Firma;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class FirmaController extends Controller
{
    /**
     * Display all firmas (Developer only).
     */
    public function index(Request $request): View
    {
        $query = Firma::withCount(['projects', 'users']);
        
        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        $firmas = $query->latest()->paginate(20);

        return view('firmas.index', compact('firmas'));
    }

    /**
     * Display the specified firma with projects and tickets.
     */
    public function show(Firma $firma): View
    {
        $firma->load([
            'projects' => function ($query) {
                $query->withCount('tickets')->latest();
            }
        ]);

        // Get recent tickets from this firma
        $recentTickets = $firma->tickets()
            ->with(['project', 'creator', 'assignee'])
            ->latest()
            ->limit(10)
            ->get();

        return view('firmas.show', compact('firma', 'recentTickets'));
    }

    /**
     * Show the form for creating a new firma (if needed).
     */
    public function create(): View
    {
        return view('firmas.create');
    }

    /**
     * Store a newly created firma (if needed).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:firmas,name',
            'email' => 'required|email|max:255|unique:firmas,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $firma = Firma::create($validated);

        return redirect()
            ->route('firmas.show', $firma)
            ->with('success', 'Firma wurde erfolgreich erstellt.');
    }

    /**
     * Show the form for editing the specified firma.
     */
    public function edit(Firma $firma): View
    {
        return view('firmas.edit', compact('firma'));
    }

    /**
     * Update the specified firma.
     */
    public function update(Request $request, Firma $firma): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:firmas,name,' . $firma->id,
            'email' => 'required|email|max:255|unique:firmas,email,' . $firma->id,
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $firma->update($validated);

        return redirect()
            ->route('firmas.show', $firma)
            ->with('success', 'Firma wurde erfolgreich aktualisiert.');
    }

    /**
     * Remove the specified firma.
     */
    public function destroy(Firma $firma): RedirectResponse
    {
        // Check if firma has users or projects
        if ($firma->users()->exists() || $firma->projects()->exists()) {
            return back()->withErrors([
                'firma' => 'Firma kann nicht gelöscht werden, da noch Benutzer oder Projekte zugeordnet sind.'
            ]);
        }

        $firma->delete();

        return redirect()
            ->route('firmas.index')
            ->with('success', 'Firma wurde erfolgreich gelöscht.');
    }
}
