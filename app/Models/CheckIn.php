<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    protected $fillable = [
        'booking_id',
        'check_in_time',
        'deposit_amount',
        'checked_in_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'check_in_time'  => 'datetime',
            'deposit_amount' => 'decimal:2',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}