<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_all_reservations') || $user->can('view_own_space_reservations');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        if ($user->can('view_all_reservations')) {
            return true;
        }

        // Space owner can view reservations for their spaces
        if ($user->can('view_own_space_reservations')) {
            return $user->ownedSpaces()->where('id', $reservation->space_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false; // Reservations are created through the booking process
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reservation $reservation): bool
    {
        return $user->can('view_all_reservations');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Reservation $reservation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reservation $reservation): bool
    {
        return false;
    }
}
