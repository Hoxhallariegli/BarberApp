<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('ba_reminders', function (Blueprint $table) { $table->id();
            $table->foreignId('booking_id')->constrained('ba_bookings');
            $table->string('reminder_type');
            $table->datetime('sent_at')->nullable();
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('ba_reminders'); } };