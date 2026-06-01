<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Hotel information
            ['key' => 'hotel_name',          'value' => 'HotelPro',                           'description' => 'Hotel name shown in sidebar and invoices'],
            ['key' => 'hotel_tagline',        'value' => 'Management System',                  'description' => 'Tagline shown below hotel name in sidebar'],
            ['key' => 'hotel_address',        'value' => 'Russian BLVD, Toul kork',          'description' => 'Hotel street address'],
            ['key' => 'hotel_city',           'value' => 'Phnom Penh',                      'description' => 'City, state and postal code'],
            ['key' => 'hotel_phone',          'value' => '(+855)12395837',                  'description' => 'Hotel contact phone number'],
            ['key' => 'hotel_email',          'value' => 'info@hotelpro.com',               'description' => 'Hotel billing / contact email'],
            ['key' => 'hotel_website',        'value' => '',                                   'description' => 'Hotel website URL'],

            // Financial
            ['key' => 'tax_rate',             'value' => '10',                                 'description' => 'Tax rate percentage applied at checkout'],
            ['key' => 'currency',             'value' => 'USD',                                'description' => 'ISO 4217 currency code (USD, EUR, KHR…)'],
            ['key' => 'currency_symbol',      'value' => '$',                                  'description' => 'Currency symbol for display'],
            ['key' => 'deposit_rate',         'value' => '50',                                 'description' => 'Deposit percentage required at check-in'],

             // Discount rules (admin-controlled)
            ['key' => 'discount_enabled',     'value' => '0',                                'description' => '1 = discounts enabled for staff, 0 = admin only'],
            ['key' => 'max_discount_rate',    'value' => '0',                                'description' => 'Max discount % staff can apply (0 = disabled for staff, 100 = no limit)'],

            // Operations
            ['key' => 'check_in_time',        'value' => '14:00',                              'description' => 'Standard check-in time (24h)'],
            ['key' => 'check_out_time',       'value' => '12:00',                              'description' => 'Standard check-out time (24h)'],

            // Invoice
            ['key' => 'invoice_prefix',       'value' => 'INV',                                'description' => 'Prefix used when generating invoice numbers'],
            ['key' => 'invoice_footer_note',  'value' => 'Thank you for staying with us.',     'description' => 'Footer note printed on every invoice'],
        ];

        foreach ($defaults as $row) {
            Setting::updateOrCreate(
                ['key' => $row['key']],
                ['value' => $row['value'], 'description' => $row['description']]
            );
        }
    }
}
