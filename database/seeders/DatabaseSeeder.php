<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create an initial Admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@commissionapparel.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'approved',
            'organization' => 'The Commission Apparel'
        ]);

        // Create a test pending Coach
        User::create([
            'name' => 'Coach Smith',
            'email' => 'coach@example.com',
            'password' => bcrypt('password123'),
            'role' => 'coach',
            'status' => 'pending',
            'organization' => 'Springfield High'
        ]);
    }
}
