<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckOut extends Model
{
    protected $fillable = [
        'booking_id',
        'check_out_time',
        'extra_total',
        'checked_out_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'check_out_time' => 'datetime',
            'extra_total'    => 'decimal:2',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function checkedOutBy()
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }
}