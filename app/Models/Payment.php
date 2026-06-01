<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'payment_type',
        'amount',
        'method',
        'status',
        'transaction_ref',
        'notes',
        'created_by',
        'paid_at',
    ];

    const TYPE_DEPOSIT    = 'deposit';
    const TYPE_SETTLEMENT = 'settlement';

    const METHOD_CASH          = 'cash';
    const METHOD_CARD          = 'card';
    const METHOD_STRIPE        = 'stripe';
    const METHOD_BANK_TRANSFER = 'bank_transfer';

    const STATUS_PENDING  = 'pending';
    const STATUS_PAID     = 'paid';
    const STATUS_FAILED   = 'failed';
    const STATUS_REFUNDED = 'refunded';

    protected function casts(): array
    {
        return [
            'amount'  => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeForStripe($query)
    {
        return $query->where('method', self::METHOD_STRIPE);
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}