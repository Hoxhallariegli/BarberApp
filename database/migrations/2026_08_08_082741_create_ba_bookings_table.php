<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('ba_bookings', function (Blueprint $table) { $table->id();
            $table->foreignId('customer_id')->constrained('ba_customers');
            $table->foreignId('barber_id')->constrained('ba_barbers');
            $table->foreignId('service_id')->constrained('ba_services');
            $table->datetime('appointment_datetime');
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('ba_bookings'); } };