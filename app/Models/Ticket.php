<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'estimated_hours',
        'actual_hours',
        'project_id',
        'created_by',
        'assigned_to',
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'priority' => TicketPriority::class,
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
    ];

    /**
     * Get the project that owns the ticket.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who created the ticket.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user assigned to the ticket.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the firma through the project relationship.
     */
    public function firma(): BelongsTo
    {
        return $this->project->firma();
    }

    /**
     * Check if the ticket can be edited by the given user.
     */
    public function canBeEditedBy(User $user): bool
    {
        return $user->role->isDeveloper() || 
            ($this->project->hasUser($user) && $this->created_by === $user->id);
    }

    /**
     * Check if the ticket is overdue (actual > estimated hours).
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->estimated_hours && $this->actual_hours && 
                         $this->actual_hours > $this->estimated_hours
        );
    }
}
