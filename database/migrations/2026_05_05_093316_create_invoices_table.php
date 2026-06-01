<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('room_total', 10, 2);
            // BUG FIX: was `decimal('extra_total')` — missing precision/scale,
            // which defaults to (8,2) on some drivers but is ambiguous.
            $table->decimal('extra_total', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            // BUG FIX: was NOT NULL with no default, causing strict-mode errors
            // when CheckOutController passes an empty string at checkout time.
            $table->string('discount_reason')->nullable()->default('');
            $table->decimal('discounted_total', 10, 2);
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2);
            $table->decimal('settlement_amount', 10, 2);
            $table->enum('status', [
                'draft',
                'issued',
                'paid',
                'void'
            ])->default('draft');
            $table->foreignId('created_by') ->nullable() ->constrained('users');
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
