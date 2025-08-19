<?php

namespace App\Policies;

use App\Models\Housing;
use App\Models\User;

class HousingPolicy
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
    public function view(User $user, Housing $housing): bool
    {
        return $housing->travel->members()->where('user_id', $user->id)->exists();
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
    public function update(User $user, Housing $housing): bool
    {
        return $housing->travel->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Housing $housing): bool
    {
        return $housing->travel->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Housing $housing): bool
    {
        return $housing->travel->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Housing $housing): bool
    {
        return $housing->travel->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create housings for a specific travel.
     */
    public function createForTravel(User $user, $travel): bool
    {
        return $travel->members()->where('user_id', $user->id)->exists();
    }
}
