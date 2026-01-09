<?php

namespace App\Policies;

use App\Models\Space;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SpacePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_spaces') || $user->hasRole('space_owner');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Space $space): bool
    {
        if ($user->can('view_spaces')) {
            return true;
        }

        // Space owner can only view their own spaces
        return $user->hasRole('space_owner') && $space->owner_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_spaces');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Space $space): bool
    {
        if ($user->can('edit_spaces')) {
            return true;
        }

        // Space owner can edit their own spaces
        return $user->hasRole('space_owner') && $space->owner_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Space $space): bool
    {
        return $user->can('delete_spaces');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Space $space): bool
    {
        return $user->can('delete_spaces');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Space $space): bool
    {
        return $user->can('delete_spaces');
    }
}
