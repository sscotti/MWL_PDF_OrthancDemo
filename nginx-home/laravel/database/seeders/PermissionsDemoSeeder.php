<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsDemoSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'patient_data']);
        Permission::create(['name' => 'provider_data']);
        Permission::create(['name' => 'reader_data']);
        Permission::create(['name' => 'staff_data']);
        Permission::create(['name' => 'admin_data']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'patient']);
        $role1->givePermissionTo('patient_data');

        $role2 = Role::create(['name' => 'provider']);
        $role2->givePermissionTo('provider_data');

        $role3 = Role::create(['name' => 'reader']);
        $role3->givePermissionTo('reader_data');
        
        $role4 = Role::create(['name' => 'staff']);
        $role4->givePermissionTo('staff_data');
        
        $role5 = Role::create(['name' => 'admin']);
        $role5->givePermissionTo('admin_data');
        // gets all permissions via Gate::before rule; see AuthServiceProvider

    }
}
