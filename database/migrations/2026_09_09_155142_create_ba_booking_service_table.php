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
        Schema::create('ba_booking_service', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('booking_id')->constrained('ba_bookings')->onDelete('cascade');
            $blueprint->foreignId('service_id')->constrained('ba_services')->onDelete('cascade');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ba_booking_service');
    }
};
