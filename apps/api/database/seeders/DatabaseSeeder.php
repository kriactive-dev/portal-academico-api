<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::firstOrCreate(
            ['email' => 'admin@ya-academico.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('administrator');

        User::firstOrCreate(
            ['email' => 'secretary@ya-academico.com'],
            [
                'name' => 'Secretary',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        )->assignRole('secretary');

        User::firstOrCreate(
            ['email' => 'financial@ya-academico.com'],
            [
                'name' => 'Financial',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        )->assignRole('financial');

        User::firstOrCreate(
            ['email' => 'coordinator@ya-academico.com'],
            [
                'name' => 'Academic Coordinator',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        )->assignRole('academic_coordinator');
    }
}
