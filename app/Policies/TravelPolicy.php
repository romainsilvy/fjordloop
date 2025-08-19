<?php

namespace App\Policies;

use App\Models\Travel;
use App\Models\User;

class TravelPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Travel $travel): bool
    {
        return $travel->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Travel $travel): bool
    {
        return $travel->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Travel $travel): bool
    {
        return $travel->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Travel $travel): bool
    {
        return $travel->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Travel $travel): bool
    {
        return $travel->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can invite members to the travel.
     */
    public function inviteMembers(User $user, Travel $travel): bool
    {
        return $travel->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can manage members of the travel.
     */
    public function manageMembers(User $user, Travel $travel): bool
    {
        return $travel->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create activities for this travel.
     */
    public function createActivity(User $user, Travel $travel): bool
    {
        return $travel->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create housings for this travel.
     */
    public function createHousing(User $user, Travel $travel): bool
    {
        return $travel->members()->where('user_id', $user->id)->exists();
    }
}
