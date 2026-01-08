<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create permissions first
        $manageUsers = Permission::create(['name' => 'manage users']);
        $manageSpaces = Permission::create(['name' => 'manage spaces']);
        $manageReservations = Permission::create(['name' => 'manage reservations']);
        $viewReports = Permission::create(['name' => 'view reports']);

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        $ownerRole = Role::create(['name' => 'owner']);

        // Assign permissions to roles
        $adminRole->givePermissionTo([$manageUsers, $manageSpaces, $manageReservations, $viewReports]);
        $ownerRole->givePermissionTo([$manageSpaces, $viewReports]);

        // Create admin user
        $admin = User::factory()->create([
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin@worknest.com',
            'status' => 'ACTIVE',
        ]);
        $admin->assignRole('admin');

        // Create test user
        $testUser = User::factory()->create([
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
            'status' => 'ACTIVE',
        ]);
        $testUser->assignRole('user');
    }
}
