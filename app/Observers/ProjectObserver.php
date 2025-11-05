<?php

namespace App\Observers;

use App\Models\Project;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        // Automatically add the creator to the project users
        if ($project->created_by && !$project->users()->where('user_id', $project->created_by)->exists()) {
            $project->users()->attach($project->created_by);
        }
    }
}