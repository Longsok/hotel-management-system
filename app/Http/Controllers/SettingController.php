<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    // GET /settings  [admin only]
    public function index()
    {
        $settings = Setting::orderBy('key')->get()->keyBy('key');

        return view('settings.index', compact('settings'));
    }

    // PUT /settings  [admin only]
    public function update(Request $request)
    {
        $data = $request->validate([
            // Hotel info
            'hotel_name'          => ['required', 'string', 'max:100'],
            'hotel_tagline'       => ['nullable', 'string', 'max:100'],
            'hotel_address'       => ['nullable', 'string', 'max:255'],
            'hotel_city'          => ['nullable', 'string', 'max:100'],
            'hotel_phone'         => ['nullable', 'string', 'max:30'],
            'hotel_email'         => ['nullable', 'email', 'max:100'],
            'hotel_website'       => ['nullable', 'url', 'max:100'],

            // Financial
            'tax_rate'            => ['required', 'numeric', 'min:0', 'max:100'],
            'currency'            => ['required', 'string', 'size:3'],
            'currency_symbol'     => ['required', 'string', 'max:5'],
            'deposit_rate'        => ['required', 'numeric', 'min:0', 'max:100'],

            // Discount rules
            'discount_enabled'    => ['nullable', 'boolean'],
            'max_discount_rate'   => ['required', 'numeric', 'min:0', 'max:100'],

            // Operations
            'check_in_time'       => ['required', 'date_format:H:i'],
            'check_out_time'      => ['required', 'date_format:H:i'],

            // Invoice
            'invoice_prefix'      => ['required', 'string', 'max:10'],
            'invoice_footer_note' => ['nullable', 'string', 'max:500'],
        ]);

        // Checkboxes are not submitted when unchecked — default to 0
        $data['discount_enabled'] = $request->boolean('discount_enabled') ? 1 : 0;

        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        // Flush entire settings cache so all helpers pick up new values immediately
        Cache::flush();

        return back()->with('success', 'Settings saved successfully.');
    }
}
