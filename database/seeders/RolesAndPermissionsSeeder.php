<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Space permissions
            'view_spaces',
            'create_spaces',
            'edit_spaces',
            'delete_spaces',
            
            // User permissions
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'manage_user_roles',
            'assign_space_owner',
            
            // Equipment permissions
            'view_equipment_types',
            'create_equipment_types',
            'edit_equipment_types',
            'delete_equipment_types',
            
            // Space equipment permissions
            'manage_space_equipment',
            
            // Schedule permissions
            'view_schedules',
            'create_schedules',
            'edit_schedules',
            'delete_schedules',
            
            // Reservation permissions
            'view_all_reservations',
            'view_own_space_reservations',
            
            // Invoice permissions
            'view_all_invoices',
            'view_own_space_invoices',
            
            // Statistics permissions
            'view_all_statistics',
            'view_own_space_statistics',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Administrator role
        $adminRole = Role::create(['name' => 'administrator', 'guard_name' => 'web']);
        $adminRole->givePermissionTo([
            'view_spaces',
            'create_spaces',
            'edit_spaces',
            'delete_spaces',
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'manage_user_roles',
            'assign_space_owner',
            'view_equipment_types',
            'create_equipment_types',
            'edit_equipment_types',
            'delete_equipment_types',
            'manage_space_equipment',
            'view_schedules',
            'create_schedules',
            'edit_schedules',
            'delete_schedules',
            'view_all_reservations',
            'view_all_invoices',
            'view_all_statistics',
        ]);

        // Create Space Owner role
        $ownerRole = Role::create(['name' => 'space_owner', 'guard_name' => 'web']);
        $ownerRole->givePermissionTo([
            'view_equipment_types',
            'create_equipment_types',
            'edit_equipment_types',
            'delete_equipment_types',
            'manage_space_equipment',
            'view_schedules',
            'create_schedules',
            'edit_schedules',
            'delete_schedules',
            'view_own_space_reservations',
            'view_own_space_invoices',
            'view_own_space_statistics',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
