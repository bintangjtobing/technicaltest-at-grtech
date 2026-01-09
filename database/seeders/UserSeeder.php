<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@grtech.com',
            'password' => 'password',
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'User',
            'email' => 'user@grtech.com',
            'password' => 'password',
            'is_admin' => false,
        ]);
    }
}
