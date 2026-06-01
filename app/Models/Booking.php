<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'customer_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'nights',
        'room_price',
        'room_total',
        'booking_source',
        'stripe_intent_id',
        'special_requests',
        'status',
        'special_requests',
        'created_by',
    ];

    // Status constants
    const STATUS_PENDING     = 'pending';
    const STATUS_CONFIRMED   = 'confirmed';
    const STATUS_CHECKED_IN  = 'checked_in';
    const STATUS_CHECKED_OUT = 'checked_out';
    const STATUS_CANCELLED   = 'cancelled';
    const STATUS_NO_SHOW     = 'no_show';

    protected function casts(): array
    {
        return [
            'check_in_date'  => 'date',
            'check_out_date' => 'date',
            'nights'         => 'integer',
            'room_price'     => 'decimal:2',
            'room_total'     => 'decimal:2',
        ];
    }

    // ── Boot: auto-generate booking_number ───────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = 'BK-' . strtoupper(uniqid());
            }

            // Auto-calculate nights and room_total
            if ($booking->check_in_date && $booking->check_out_date) {
                $booking->nights     = $booking->check_in_date->diffInDays($booking->check_out_date);
                $booking->room_total = $booking->nights * $booking->room_price;
            }
        });
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_CHECKED_IN,
        ]);
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('check_in_date', today())
                     ->orWhereDate('check_out_date', today());
    }

    public function scopeSearch($query, string $term)
    {
        return $query->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$term}%"))
                     ->orWhere('booking_number', 'like', "%{$term}%");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function canCheckIn(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function canCheckOut(): bool
    {
        return $this->status === self::STATUS_CHECKED_IN;
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function getTotalWithExtrasAttribute(): float
    {
        return (float) $this->room_total + (float) $this->bookingServices->sum('total_price');
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function checkIn()
    {
        return $this->hasOne(CheckIn::class);
    }

    public function checkOut()
    {
        return $this->hasOne(CheckOut::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function bookingServices()
    {
        return $this->hasMany(BookingService::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}