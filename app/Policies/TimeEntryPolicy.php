<?php

namespace App\Policies;

use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TimeEntryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view time entries
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TimeEntry $timeEntry): bool
    {
        // Users can view their own time entries, or admins/managers can view all
        return $user->id === $timeEntry->user_id || 
               in_array($user->role, ['admin', 'manager']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create time entries
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TimeEntry $timeEntry): bool
    {
        // Users can only update their own time entries within 24 hours, or admins/managers can update any
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }
        
        return $user->id === $timeEntry->user_id && 
               $timeEntry->created_at->diffInHours(now()) <= 24;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TimeEntry $timeEntry): bool
    {
        // Users can only delete their own time entries within 24 hours, or admins/managers can delete any
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }
        
        return $user->id === $timeEntry->user_id && 
               $timeEntry->created_at->diffInHours(now()) <= 24;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TimeEntry $timeEntry): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TimeEntry $timeEntry): bool
    {
        return $user->role === 'admin';
    }
}
