<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $roomTypes = [
            [
                'name'        => 'Standard',
                'description' => 'A comfortable room with essential amenities, ideal for solo travelers or short stays. Features a single or double bed, private bathroom, air conditioning, and TV.',
                'base_price'  => 25.00,
                'max_people'  => 2,
            ],
            [
                'name'        => 'Deluxe',
                'description' => 'A spacious room with upgraded furnishings and modern décor. Features a queen-size bed, work desk, flat-screen TV, minibar, and a larger private bathroom with bathtub.',
                'base_price'  => 40.00,
                'max_people'  => 2,
            ],
            [
                'name'        => 'Superior',
                'description' => 'An enhanced room offering extra space and premium bedding. Includes a king-size bed, seating area, coffee maker, safe, and city or garden view.',
                'base_price'  => 50.00,
                'max_people'  => 3,
            ],
            [
                'name'        => 'Family',
                'description' => 'Designed for families, this room features two double beds or one king and one single bed. Includes extra storage space, a seating area, and all standard amenities.',
                'base_price'  => 60.00,
                'max_people'  => 4,
            ],
        ];

        DB::table('room_types')->insert(
            array_map(fn($type) => array_merge($type, [
                'created_at' => now(),
                'updated_at' => now(),
            ]), $roomTypes)
        );
    }
}