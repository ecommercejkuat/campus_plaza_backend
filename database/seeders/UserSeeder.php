<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Campus Plaza Admin',
            'email' => 'admin@campusplaza.com',
            'password' => Hash::make('Admin@123'),
            'role' => 'superuser',
            'phone' => '+254712345678',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'John Seller',
            'email' => 'seller@campusplaza.com',
            'password' => Hash::make('Seller@123'),
            'role' => 'seller',
            'phone' => '+254712345679',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Jane Buyer',
            'email' => 'buyer@campusplaza.com',
            'password' => Hash::make('Buyer@123'),
            'role' => 'buyer',
            'phone' => '+254712345680',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}