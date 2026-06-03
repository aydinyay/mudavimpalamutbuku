<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(['email' => 'admin@mudavim.com'], [
            'name'     => 'Müdavim Admin',
            'password' => Hash::make('mudavim2024!'),
            'role'     => 'super_admin',
        ]);

        $this->call([
            RestaurantSeeder::class,
            AllergenSeeder::class,
            TableSeeder::class,
            MenuSeeder::class,
        ]);
    }
}
