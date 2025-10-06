<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Firma extends Model
{
    /** @use HasFactory<\Database\Factories\FirmaFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'email',
        'phone',
        'address',
    ];

    /**
     * Get all users belonging to this firma.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all projects belonging to this firma.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get all tickets for this firma through projects.
     */
    public function tickets(): HasManyThrough
    {
        return $this->hasManyThrough(Ticket::class, Project::class);
    }

    /**
     * Get users available to be added to a project (excluding already assigned ones).
     */
    public function availableUsersForProject(Project $project)
    {
        return $this->users()
            ->whereNotIn('id', $project->users->pluck('id'))
            ->where('id', '!=', $project->created_by)
            ->get();
    }
}
