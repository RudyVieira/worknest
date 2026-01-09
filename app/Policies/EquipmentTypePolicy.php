<?php

namespace App\Policies;

use App\Models\EquipmentType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentTypePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_equipment_types');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EquipmentType $equipmentType): bool
    {
        return $user->can('view_equipment_types');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_equipment_types');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EquipmentType $equipmentType): bool
    {
        return $user->can('edit_equipment_types');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EquipmentType $equipmentType): bool
    {
        return $user->can('delete_equipment_types');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EquipmentType $equipmentType): bool
    {
        return $user->can('delete_equipment_types');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EquipmentType $equipmentType): bool
    {
        return $user->can('delete_equipment_types');
    }
}
