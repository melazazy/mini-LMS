<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'Sarah Admin',
            'email' => 'admin@minilms.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now()->subDays(90),
        ]);

        // Create instructor users
        User::create([
            'name' => 'Dr. Michael Chen',
            'email' => 'michael.chen@minilms.com',
            'password' => Hash::make('password'),
            'role' => 'instructor',
            'email_verified_at' => now()->subDays(60),
        ]);

        User::create([
            'name' => 'Prof. Emily Rodriguez',
            'email' => 'emily.rodriguez@minilms.com',
            'password' => Hash::make('password'),
            'role' => 'instructor',
            'email_verified_at' => now()->subDays(75),
        ]);

        User::create([
            'name' => 'David Thompson',
            'email' => 'david.thompson@minilms.com',
            'password' => Hash::make('password'),
            'role' => 'instructor',
            'email_verified_at' => now()->subDays(45),
        ]);

        // Create student users
        User::create([
            'name' => 'Alex Johnson',
            'email' => 'alex.johnson@student.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'email_verified_at' => now()->subDays(30),
        ]);

        User::create([
            'name' => 'Maria Garcia',
            'email' => 'maria.garcia@student.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'email_verified_at' => now()->subDays(25),
        ]);

        User::create([
            'name' => 'James Wilson',
            'email' => 'james.wilson@student.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'email_verified_at' => now()->subDays(20),
        ]);

        User::create([
            'name' => 'Sophie Anderson',
            'email' => 'sophie.anderson@student.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'email_verified_at' => now()->subDays(15),
        ]);

        User::create([
            'name' => 'Ryan Patel',
            'email' => 'ryan.patel@student.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'email_verified_at' => now()->subDays(10),
        ]);
    }
}