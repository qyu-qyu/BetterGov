<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed roles
        $roles = ['admin', 'office', 'citizen'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $adminRole   = Role::where('name', 'admin')->first();
        $officeRole  = Role::where('name', 'office')->first();
        $citizenRole = Role::where('name', 'citizen')->first();

        // Seed demo users
        User::firstOrCreate(
            ['email' => 'admin@bettergov.lb'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('Admin@1234'),
                'role_id'  => $adminRole->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'office@bettergov.lb'],
            [
                'name'     => 'Municipality Office',
                'password' => Hash::make('Office@1234'),
                'role_id'  => $officeRole->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'citizen@bettergov.lb'],
            [
                'name'     => 'John Citizen',
                'password' => Hash::make('Citizen@1234'),
                'role_id'  => $citizenRole->id,
            ]
        );
    }
}
