<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->enum('payment_type', ['deposit', 'settlement']);
            $table->decimal('amount', 10, 2);
            $table->enum('method', [
                'cash',
                'card',
                'stripe',
                'bank_transfer'
            ]);
            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'refunded'
            ])->default('pending');
            $table->string('transaction_ref')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by') ->nullable()
                  ->constrained('users');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
