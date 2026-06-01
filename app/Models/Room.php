<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'floor',
        'room_type_id',
        'status',
        'notes',
        'image',
    ];

    // Status constants
    const STATUS_AVAILABLE    = 'available';
    const STATUS_RESERVED     = 'reserved';
    const STATUS_OCCUPIED     = 'occupied';
    const STATUS_CLEANING     = 'cleaning';
    const STATUS_MAINTENANCE  = 'maintenance';

    public static function statuses(): array
    {
        return [
            self::STATUS_AVAILABLE,
            self::STATUS_RESERVED,
            self::STATUS_OCCUPIED,
            self::STATUS_CLEANING,
            self::STATUS_MAINTENANCE,
        ];
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeByFloor($query, string $floor)
    {
        return $query->where('floor', $floor);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    /**
     * Check if this room is available for the given date range,
     * ignoring a specific booking (useful when editing a booking).
     */
    public function isAvailableForDates(string $checkIn, string $checkOut, ?int $excludeBookingId = null): bool
    {
        $query = $this->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in_date', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                  ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                      $q2->where('check_in_date', '<=', $checkIn)
                         ->where('check_out_date', '>=', $checkOut);
                  });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->doesntExist();
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
    
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_amenities', 'room_id', 'amenity_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBooking()
    {
        return $this->hasOne(Booking::class)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->latest();
    }

    public function statusLogs()
    {
        return $this->hasMany(RoomStatusLog::class);
    }
}