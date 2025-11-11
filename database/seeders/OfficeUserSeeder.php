<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OfficeUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        User::firstOrCreate(
            ['email' => 'erickboyle@superadmin.com'],
            [
                'name' => 'Super Admin',
                'email' => 'erickboyle@superadmin.com',
                'password' => Hash::make('erick2004'),
                'role' => 'super_admin',
                'status' => 'active',
                'phone' => '+1234567890',
            ]
        );

        // Create Office Manager
        User::firstOrCreate(
            ['email' => 'manager@delivery.com'],
            [
                'name' => 'Office Manager',
                'email' => 'manager@delivery.com',
                'password' => Hash::make('manager123'),
                'role' => 'office_manager',
                'status' => 'active',
                'phone' => '+1234567891',
            ]
        );

        // Create Office Staff
        User::firstOrCreate(
            ['email' => 'staff@delivery.com'],
            [
                'name' => 'Office Staff',
                'email' => 'staff@delivery.com',
                'password' => Hash::make('staff123'),
                'role' => 'office_staff',
                'status' => 'active',
                'phone' => '+1234567892',
            ]
        );

        $this->command->info('Office users created successfully!');
        $this->command->info('Super Admin: erickboyle@superadmin.com / erick2004');
        $this->command->info('Office Manager: manager@delivery.com / manager123');
        $this->command->info('Office Staff: staff@delivery.com / staff123');
    }
}

