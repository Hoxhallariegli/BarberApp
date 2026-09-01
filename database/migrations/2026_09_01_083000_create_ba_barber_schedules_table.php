<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ba_barber_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_id')->constrained('ba_barbers')->onDelete('cascade');
            $table->integer('day_of_week'); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->time('break_start_time')->nullable();
            $table->time('break_end_time')->nullable();
            $table->boolean('is_working')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ba_barber_schedules');
    }
};
