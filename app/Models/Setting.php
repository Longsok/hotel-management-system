<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    // ── Core helpers ──────────────────────────────────────────────────────────

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }

    // ── Hotel information ─────────────────────────────────────────────────────

    public static function hotelName(): string
    {
        return static::get('hotel_name', 'HotelPro');
    }

    public static function hotelTagline(): string
    {
        return static::get('hotel_tagline', 'Management System');
    }

    public static function hotelAddress(): string
    {
        return static::get('hotel_address', '');
    }

    public static function hotelCity(): string
    {
        return static::get('hotel_city', '');
    }

    public static function hotelPhone(): string
    {
        return static::get('hotel_phone', '');
    }

    public static function hotelEmail(): string
    {
        return static::get('hotel_email', '');
    }

    public static function hotelWebsite(): string
    {
        return static::get('hotel_website', '');
    }

    // ── Financial ─────────────────────────────────────────────────────────────

    public static function taxRate(): float
    {
        return (float) static::get('tax_rate', 10);
    }

    public static function currency(): string
    {
        return static::get('currency', 'USD');
    }

    public static function currencySymbol(): string
    {
        return static::get('currency_symbol', '$');
    }

    public static function depositRate(): float
    {
        return (float) static::get('deposit_rate', 30);
    }

    /**
     * Whether discounts are enabled at all.
     * Admin can always apply discounts regardless of this setting.
     * Staff can only apply when this is true.
     */
    public static function discountEnabled(): bool
    {
        return (bool)(int) static::get('discount_enabled', 0);
    }

    /**
     * Maximum discount % that staff (non-admin) can apply.
     * 0 means disabled for staff entirely.
     * Admins bypass this limit entirely.
     */
    public static function maxDiscountRate(): float
    {
        return (float) static::get('max_discount_rate', 0);
    }

    // ── Operations ────────────────────────────────────────────────────────────

    public static function checkInTime(): string
    {
        return static::get('check_in_time', '14:00');
    }

    public static function checkOutTime(): string
    {
        return static::get('check_out_time', '12:00');
    }

    // ── Invoice ───────────────────────────────────────────────────────────────

    public static function invoicePrefix(): string
    {
        return static::get('invoice_prefix', 'INV');
    }

    public static function invoiceFooterNote(): string
    {
        return static::get('invoice_footer_note', 'Thank you for staying with us.');
    }
}
