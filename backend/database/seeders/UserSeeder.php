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
            'name'     => 'Admin StockFlow',
            'email'    => 'admin@stockflow.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Staff StockFlow',
            'email'    => 'staff@stockflow.com',
            'password' => Hash::make('password'),
            'role'     => 'staff',
        ]);
    }
}
