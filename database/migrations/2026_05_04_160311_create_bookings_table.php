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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignID('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignID('room_id')->constrained()->cascadeOnDelete();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('nights');
            $table->decimal('room_price', 10, 2);
            $table->decimal('room_total', 10, 2);
            $table->enum('booking_source', [
                'walk_in',
                'online'
            ])->default('walk_in');
            $table->enum('status', ['pending','confirmed','checked_in','checked_out','cancelled','no_show'])->default('pending');
            $table->text('special_requests')->nullable();
            $table->foreignId('created_by') ->nullable() ->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
