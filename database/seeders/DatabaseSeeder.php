<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roleSuperAdmin = Role::create(['name' => 'super-admin']);


        $permissions = [
            'dashboard',
            'manage-users',
            'settings',
            'master-data',
            'manage-permissions',
            'manage-roles',
            'manage-logs',

            'manage-categories',
            'create-category',
            'delete-category',

            'manage-units',
            'create-unit',
            'delete-unit',

            'manage-suppliers',
            'create-supplier',
            'delete-supplier',

            'manage-products',
            'create-product',
            'delete-product',

            'manage-tables',
            'create-table',
            'delete-table',

            'manage-additions',
            'create-addition',
            'delete-addition',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $roleSuperAdmin->givePermissionTo($permissions);

        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@mail.com',
        ]);

        $superAdmin->assignRole($roleSuperAdmin);

        $this->call([
            ProductSeeder::class,
            TableSeeder::class,
        ]);
    }
}
