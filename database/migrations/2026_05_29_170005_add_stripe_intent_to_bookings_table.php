<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Only add stripe_intent_id — special_requests already exists
            if (!Schema::hasColumn('bookings', 'stripe_intent_id')) {
                $table->string('stripe_intent_id')->nullable()->after('booking_source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'stripe_intent_id')) {
                $table->dropColumn('stripe_intent_id');
            }
        });
    }
};