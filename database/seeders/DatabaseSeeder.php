<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        // Admin Account
        User::create([
            'name' => 'Admin Long',
            'email' => 'soklongyoung@gmail.com',
            'password' => Hash::make('long12345'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Staff Account
        User::create([
            'name' => 'SokLong',
            'email' => 'soklong260@gmail.com',
            'password' => Hash::make('long12345'),
            'role' => 'staff',
            'status' => 'active',
        ]);

         $this->call([
            RoomTypeSeeder::class,
            AmenitySeeder::class,
        ]);
    }
}
