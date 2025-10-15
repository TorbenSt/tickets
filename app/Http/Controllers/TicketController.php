<?php

namespace App\Http\Controllers;

use App\Enums\{TicketPriority, TicketStatus};
use App\Models\{Project, Ticket, User};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets based on user role.
     */
    public function index(): View
    {
        $user = Auth::user();
        
        if ($user->role->isDeveloper()) {
            // Developers see all tickets system-wide
            $tickets = Ticket::with(['project.firma', 'creator', 'assignee'])
                ->latest()
                ->paginate(20);
        } else {
            // Customers see only tickets from projects they have access to
            $tickets = Ticket::with(['project', 'creator', 'assignee'])
                ->whereHas('project', function ($query) use ($user) {
                    $query->where(function ($subQuery) use ($user) {
                        $subQuery->whereHas('users', function ($userQuery) use ($user) {
                            $userQuery->where('user_id', $user->id);
                        })->orWhere('created_by', $user->id);
                    });
                })
                ->latest()
                ->paginate(20);
        }

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show emergency tickets (Developer only).
     */
    public function emergency(): View
    {
        $tickets = Ticket::with(['project.firma', 'creator', 'assignee'])
            ->where('priority', TicketPriority::NOTFALL)
            ->latest()
            ->paginate(20);

        return view('tickets.emergency', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create(Request $request, Project $project): View
    {
        // Ensure user has access to this project
        if (!$project->hasUser(Auth::user())) {
            abort(403, 'No access to this project.');
        }

        return view('tickets.create', compact('project'));
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:' . implode(',', array_column(TicketPriority::cases(), 'value')),
            'estimated_hours' => 'nullable|numeric|min:0',
            'project_id' => 'required|exists:projects,id',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        
        // Ensure user has access to this project
        if (!$project->hasUser(Auth::user())) {
            abort(403, 'No access to this project.');
        }

        // Status depends on who creates the ticket
        $status = Auth::user()->role->isDeveloper() 
            ? TicketStatus::OPEN  // Developer tickets need customer approval
            : TicketStatus::TODO; // Customer tickets are pre-approved

        $ticket = Ticket::create([
            ...$validated,
            'status' => $status,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket wurde erfolgreich erstellt.');
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket): View
    {
        // Check access permissions - user must have access to the project
        if (!Auth::user()->role->isDeveloper() && !$ticket->project->hasUser(Auth::user())) {
            abort(403, 'No access to this ticket.');
        }

        $ticket->load(['project.firma', 'creator', 'assignee']);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket): View
    {
        if (!$ticket->canBeEditedBy(Auth::user())) {
            abort(403, 'No permission to edit this ticket.');
        }

        return view('tickets.edit', compact('ticket'));
    }

    /**
     * Update the specified ticket.
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        if (!$ticket->canBeEditedBy(Auth::user())) {
            abort(403, 'No permission to edit this ticket.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:' . implode(',', array_column(TicketPriority::cases(), 'value')),
            'status' => 'required|in:' . implode(',', array_column(TicketStatus::cases(), 'value')),
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
        ]);

        $ticket->update($validated);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket wurde erfolgreich aktualisiert.');
    }

    /**
     * Remove the specified ticket.
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        if (!$ticket->canBeEditedBy(Auth::user())) {
            abort(403, 'No permission to delete this ticket.');
        }

        $ticket->delete();

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Ticket wurde erfolgreich gelöscht.');
    }

    /**
     * Assign a ticket to a developer (Developer only).
     */
    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Ensure assigned user is a developer if provided
        if ($validated['assigned_to']) {
            $assignee = User::findOrFail($validated['assigned_to']);
            if (!$assignee->role->isDeveloper()) {
                return back()->withErrors(['assigned_to' => 'Can only assign tickets to developers.']);
            }
        }

        $ticket->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => $validated['assigned_to'] ? TicketStatus::TODO : $ticket->status,
        ]);

        return back()->with('success', 'Ticket-Zuordnung wurde aktualisiert.');
    }

    /**
     * Update ticket status (Developer only).
     */
    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_column(TicketStatus::cases(), 'value')),
            'actual_hours' => 'nullable|numeric|min:0',
        ]);

        $ticket->update($validated);

        return back()->with('success', 'Ticket-Status wurde aktualisiert.');
    }

    /**
     * Approve ticket (Customer only - for developer-created tickets).
     */
    public function approve(Ticket $ticket): RedirectResponse
    {
        // Only customers can approve tickets
        if (!Auth::user()->role->isCustomer()) {
            abort(403, 'Only customers can approve tickets.');
        }

        // Only approve tickets that are in "open" status (need confirmation)
        if ($ticket->status !== TicketStatus::OPEN) {
            abort(403, 'This ticket cannot be approved.');
        }

        // Ensure customer has access to this ticket's project
        if (!$ticket->project->hasUser(Auth::user())) {
            abort(403, 'No access to this ticket.');
        }

        $ticket->update(['status' => TicketStatus::TODO]);

        return back()->with('success', 'Ticket wurde freigegeben und ist nun in Bearbeitung.');
    }

    /**
     * Show pending approval tickets for customers.
     */
    public function pendingApproval(): View
    {
        // Only customers can see pending approval tickets
        if (!Auth::user()->role->isCustomer()) {
            abort(403, 'Access denied.');
        }

        $user = Auth::user();
        $tickets = Ticket::with(['project', 'creator'])
            ->where('status', TicketStatus::OPEN)
            ->whereHas('project', function ($query) use ($user) {
                $query->where(function ($subQuery) use ($user) {
                    $subQuery->whereHas('users', function ($userQuery) use ($user) {
                        $userQuery->where('user_id', $user->id);
                    })->orWhere('created_by', $user->id);
                });
            })
            ->latest()
            ->paginate(20);

        return view('tickets.pending-approval', compact('tickets'));
    }
}
