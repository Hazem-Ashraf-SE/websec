<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create if user doesn't exist
        if (!User::where('email', 'apitest@example.com')->exists()) {
            User::create([
                'name' => 'API Test User',
                'email' => 'apitest@example.com',
                'password' => Hash::make('password123'),
            ]);
        }
    }
}
