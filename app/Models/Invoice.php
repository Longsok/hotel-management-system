<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'booking_id',
        'invoice_number',
        'room_total',
        'extra_total',
        'subtotal',
        'discount_rate',
        'discount_amount',
        'discount_reason',
        'discounted_total',
        'tax_rate',
        'tax_amount',
        'grand_total',
        'settlement_amount',
        'status',
        'created_by',
        'issued_at',
    ];

    const STATUS_DRAFT  = 'draft';
    const STATUS_ISSUED = 'issued';
    const STATUS_PAID   = 'paid';
    const STATUS_VOID   = 'void';

    protected function casts(): array
    {
        return [
            'room_total'       => 'decimal:2',
            'extra_total'      => 'decimal:2',
            'subtotal'         => 'decimal:2',
            'discount_rate'    => 'decimal:2',
            'discount_amount'  => 'decimal:2',
            'discounted_total' => 'decimal:2',
            'tax_rate'         => 'decimal:2',
            'tax_amount'       => 'decimal:2',
            'grand_total'      => 'decimal:2',
            'settlement_amount'=> 'decimal:2',
            'issued_at'        => 'datetime',
        ];
    }

    // ── Boot: auto-generate invoice_number ───────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            }
        });
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