<?php

namespace App\Http\Controllers;

use App\Models\{Firma, Project, User};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Display projects based on user role.
     */
    public function index(): View
    {
        $user = Auth::user();
        
        if ($user->role->isDeveloper()) {
            // Developers see all projects
            $projects = Project::with(['firma', 'creator'])
                ->withCount('tickets')
                ->latest()
                ->paginate(20);
        } else {
            // Customers see only their firma's projects
            $projects = Project::with(['creator'])
                ->where('firma_id', $user->firma_id)
                ->withCount('tickets')
                ->latest()
                ->paginate(20);
        }

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): View
    {
        return view('projects.create');
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = Project::create([
            ...$validated,
            'firma_id' => Auth::user()->firma_id,
            'created_by' => Auth::id(),
        ]);

        // Automatically add creator to project
        $project->users()->attach(Auth::id());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Projekt wurde erfolgreich erstellt.');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): View
    {
        // Check access permissions
        if (!$project->hasUser(Auth::user())) {
            abort(403, 'No access to this project.');
        }

        $project->load(['firma', 'creator', 'users']);
        $tickets = $project->tickets()
            ->with(['creator', 'assignee'])
            ->latest()
            ->paginate(15);

        return view('projects.show', compact('project', 'tickets'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project): View
    {
        if (!$project->hasUser(Auth::user()) || 
            (!Auth::user()->role->isDeveloper() && $project->created_by !== Auth::id())) {
            abort(403, 'No permission to edit this project.');
        }

        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        if (!$project->hasUser(Auth::user()) || 
            (!Auth::user()->role->isDeveloper() && $project->created_by !== Auth::id())) {
            abort(403, 'No permission to edit this project.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project->update($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Projekt wurde erfolgreich aktualisiert.');
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project): RedirectResponse
    {
        // Only developers or project creator can delete
        if (!Auth::user()->role->isDeveloper() && $project->created_by !== Auth::id()) {
            abort(403, 'No permission to delete this project.');
        }

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Projekt wurde erfolgreich gelöscht.');
    }

    /**
     * Show project users management page.
     */
    public function users(Project $project): View
    {
        // Check permissions - project creator or developer
        if (!Auth::user()->role->isDeveloper() && $project->created_by !== Auth::id()) {
            abort(403, 'No permission to manage project users.');
        }

        $project->load(['users', 'firma']);
        
        // Get available users from the same firma (excluding already added users)
        $availableUsers = $project->firma->availableUsersForProject($project);

        return view('projects.users', compact('project', 'availableUsers'));
    }

    /**
     * Add a user to the project.
     */
    public function addUser(Request $request, Project $project): RedirectResponse
    {
        // Check permissions
        if (!Auth::user()->role->isDeveloper() && $project->created_by !== Auth::id()) {
            abort(403, 'No permission to manage project users.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        
        // Ensure user belongs to the same firma (unless developer is adding)
        if (!Auth::user()->role->isDeveloper() && $user->firma_id !== $project->firma_id) {
            return back()->withErrors(['user_id' => 'User must belong to the same firma as the project.']);
        }

        // Check if user is already attached
        if ($project->users()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['user_id' => 'User is already a member of this project.']);
        }

        $project->users()->attach($user->id);

        return back()->with('success', "User '{$user->name}' wurde zum Projekt hinzugefügt.");
    }

    /**
     * Remove a user from the project.
     */
    public function removeUser(Project $project, User $user): RedirectResponse
    {
        // Check permissions
        if (!Auth::user()->role->isDeveloper() && $project->created_by !== Auth::id()) {
            abort(403, 'No permission to manage project users.');
        }

        // Prevent removing the project creator
        if ($user->id === $project->created_by) {
            return back()->withErrors(['user' => 'Cannot remove the project creator.']);
        }

        $project->users()->detach($user->id);

        return back()->with('success', "User '{$user->name}' wurde vom Projekt entfernt.");
    }

    /**
     * Get available users for project (API endpoint for AJAX).
     */
    public function availableUsers(Project $project)
    {
        // Check permissions
        if (!Auth::user()->role->isDeveloper() && $project->created_by !== Auth::id()) {
            abort(403);
        }

        $availableUsers = $project->firma->availableUsersForProject($project);

        return response()->json([
            'users' => $availableUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            })
        ]);
    }
}
