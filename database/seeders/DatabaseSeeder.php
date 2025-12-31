<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'Regular User',
            'email' => 'cikguinfo@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);
    }
}
