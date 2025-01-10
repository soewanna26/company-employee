<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'SuperAdmin',
            'email' => 'superadmin@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        // Ensure the role exists before assigning it
        $role = Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);

        // Assign the role to the user
        $user->assignRole($role);
    }
}
