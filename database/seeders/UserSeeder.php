<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@projectmanagement.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+1234567890',
            'bio' => 'System Administrator',
            'hourly_rate' => 100.00,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Manager User
        User::create([
            'name' => 'Project Manager',
            'email' => 'manager@projectmanagement.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'phone' => '+1234567891',
            'bio' => 'Senior Project Manager with 5+ years experience',
            'hourly_rate' => 75.00,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Staff Users
        $staffUsers = [
            [
                'name' => 'John Developer',
                'email' => 'john@projectmanagement.com',
                'role' => 'staff',
                'phone' => '+1234567892',
                'bio' => 'Full-stack developer specializing in Laravel and Vue.js',
                'hourly_rate' => 60.00,
            ],
            [
                'name' => 'Sarah Designer',
                'email' => 'sarah@projectmanagement.com',
                'role' => 'staff',
                'phone' => '+1234567893',
                'bio' => 'UI/UX Designer with expertise in modern web design',
                'hourly_rate' => 55.00,
            ],
            [
                'name' => 'Mike Tester',
                'email' => 'mike@projectmanagement.com',
                'role' => 'staff',
                'phone' => '+1234567894',
                'bio' => 'QA Engineer focused on automated testing',
                'hourly_rate' => 50.00,
            ],
        ];

        foreach ($staffUsers as $userData) {
            User::create(array_merge($userData, [
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]));
        }
    }
}
