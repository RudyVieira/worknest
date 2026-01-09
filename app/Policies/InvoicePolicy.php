<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_all_invoices') || $user->can('view_own_space_invoices');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->can('view_all_invoices')) {
            return true;
        }

        // Space owner can view invoices for their spaces
        if ($user->can('view_own_space_invoices')) {
            $reservation = $invoice->reservation;
            if ($reservation) {
                return $user->ownedSpaces()->where('id', $reservation->space_id)->exists();
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return false;
    }
}
