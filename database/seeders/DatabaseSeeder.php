<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::create([
            'firstname' => 'Admin',
            'middlename' => 'Admin',
            'lastname' => 'Admin',
            'name' => 'Admin Admin',
            'email' => 'fronteras135@gmail.com',
            'email1' => 'fronteras135@gmail.com',
            'role' => 'admin',
            'password' => Hash::make('fronteras135'),
        ]);
    }
}
