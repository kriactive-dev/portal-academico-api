<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('roles')->insert([
            ['name' => 'admin', 'guard_name' => 'web'],
            ['name' => 'gestor', 'guard_name' => 'sanctum'],
            ['name' => 'estudante', 'guard_name' => 'sanctum']
        ]);
        $user = User::create([
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => bcrypt('12345678'),
        ]);

        $user->assignRole('admin');

        $roles = Role::get();
        foreach ($roles as $role) {
            $role->update(['guard_name' => 'sanctum']);
        }
        
    }
}
