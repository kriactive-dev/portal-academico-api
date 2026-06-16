<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $modules = ['dashboard', 'students', 'courses', 'school_classes', 'trainers', 'fees', 'payments', 'users', 'requests'];
        $actions = ['view', 'create', 'edit', 'delete', 'approve'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }

        $admin = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $secretary = Role::firstOrCreate(['name' => 'secretary', 'guard_name' => 'web']);
        $secretary->syncPermissions([
            'students.view', 'students.create', 'students.edit',
            'courses.view',
            'school_classes.view', 'school_classes.create', 'school_classes.edit',
            'trainers.view', 'trainers.create', 'trainers.edit',
            'requests.view', 'requests.create', 'requests.edit',
            'dashboard.view',
        ]);

        $financial = Role::firstOrCreate(['name' => 'financial', 'guard_name' => 'web']);
        $financial->syncPermissions([
            'fees.view', 'fees.create', 'fees.edit',
            'payments.view', 'payments.create', 'payments.edit',
            'students.view',
            'courses.view',
            'dashboard.view',
        ]);

        $coordinator = Role::firstOrCreate(['name' => 'academic_coordinator', 'guard_name' => 'web']);
        $coordinator->syncPermissions([
            'courses.view', 'courses.create', 'courses.edit', 'courses.delete',
            'school_classes.view', 'school_classes.create', 'school_classes.edit', 'school_classes.delete',
            'trainers.view', 'trainers.create', 'trainers.edit',
            'students.view', 'students.create', 'students.edit',
            'requests.view', 'requests.approve',
            'dashboard.view',
        ]);
    }
}
