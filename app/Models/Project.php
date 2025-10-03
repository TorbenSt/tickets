<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'firma_id',
        'created_by',
    ];

    /**
     * Get the firma that owns the project.
     */
    public function firma(): BelongsTo
    {
        return $this->belongsTo(Firma::class);
    }

    /**
     * Get the user who created the project.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all users that have access to this project.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Get all tickets for this project.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Check if a user has access to this project.
     */
    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists() 
            || $this->created_by === $user->id 
            || $user->role->isDeveloper();
    }
}
