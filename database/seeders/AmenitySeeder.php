<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('amenities')->insert([
            [
                'name' => 'WiFi',
                'icon' => 'fas fa-wifi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Swimming Pool',
                'icon' => 'fas fa-swimming-pool',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Parking',
                'icon' => 'fas fa-parking',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Air Conditioning',
                'icon' => 'fas fa-snowflake',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gym',
                'icon' => 'fas fa-dumbbell',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Spa',
                'icon' => 'fas fa-spa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'toiletries',
                'icon' => 'fa-solid fa-soap',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'TV',
                'icon' => 'fas fa-tv',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}