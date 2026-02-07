<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'superadmin@labtech.lk'],
            [
                'name' => 'Labtech Admin',
                'password' => Hash::make('Demo@123'),
                'is_active' => true,
            ]
        );

        $permissions = [
            'admin.dashboard',
            'banners.manage',
            'departments.manage',
            'centers.manage',
            'doctors.manage',
            'tests.manage',
            'billing.access',
            'billing.create',
            'clinic.billing',
            'results.entry',
            'results.validate',
            'results.approve',
            'results.edit',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        $role = Role::firstOrCreate([
            'name' => 'Super Admin',
        ]);

        $role->permissions()->sync(Permission::pluck('id'));
        $user->roles()->sync([$role->id]);

        $defaultRoles = [
            ['name' => 'Admin', 'description' => 'Full lab administration access'],
            ['name' => 'Receptionist', 'description' => 'Front desk and registration access'],
            ['name' => 'Phelobotomist', 'description' => 'Sample collection access'],
            ['name' => 'Accountant', 'description' => 'Billing and finance access'],
        ];

        $adminRole = null;
        foreach ($defaultRoles as $defaultRole) {
            $roleItem = Role::firstOrCreate(['name' => $defaultRole['name']], $defaultRole);
            if ($roleItem->name === 'Admin') {
                $adminRole = $roleItem;
            }
        }

        if ($adminRole) {
            $adminPermissionIds = Permission::query()
                ->where('name', '!=', 'clinic.billing')
                ->pluck('id');
            $adminRole->permissions()->sync($adminPermissionIds);
        }

        $departments = [
            ['code' => 'BIO', 'name' => 'Biochemistry'],
            ['code' => 'HEM', 'name' => 'Hematology'],
            ['code' => 'MIC', 'name' => 'Microbiology'],
            ['code' => 'HIS', 'name' => 'Histopathology'],
            ['code' => 'SER', 'name' => 'Serology'],
            ['code' => 'OPD', 'name' => 'OPD'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(['code' => $department['code']], $department);
        }
    }
}
