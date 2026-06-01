<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingService extends Model
{
    protected $fillable = [
        'booking_id',
        'service_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity'    => 'integer',
            'unit_price'  => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    // Auto-calculate total_price before saving
    protected static function booted(): void
    {
        static::saving(function (BookingService $bs) {
            $bs->total_price = $bs->quantity * $bs->unit_price;
        });
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function service()
    {
        return $this->belongsTo(ExtraService::class, 'service_id');
    }
}