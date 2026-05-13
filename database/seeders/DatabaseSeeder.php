<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed roles and capture results directly
        $adminRole   = Role::firstOrCreate(['name' => 'admin']);
        $officeRole  = Role::firstOrCreate(['name' => 'office']);
        $citizenRole = Role::firstOrCreate(['name' => 'citizen']);

        // Seed demo users — password is plain text; the User model's 'hashed' cast handles bcrypt
        User::firstOrCreate(
            ['email' => 'admin@bettergov.lb'],
            [
                'name'     => 'Admin User',
                'password' => 'Admin@1234',
                'role_id'  => $adminRole->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'office@bettergov.lb'],
            [
                'name'     => 'Municipality Office',
                'password' => 'Office@1234',
                'role_id'  => $officeRole->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'citizen@bettergov.lb'],
            [
                'name'     => 'John Citizen',
                'password' => 'Citizen@1234',
                'role_id'  => $citizenRole->id,
            ]
        );
    }
}
