<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'jona03278@gmail.com'],
            [
                'name'              => 'Jona Admin',
                'username'          => 'jona03278',
                'email'             => 'jona03278@gmail.com',
                'password'          => Hash::make('Jona@03278'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
