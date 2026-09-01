<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('ba_barbers', function (Blueprint $table) { $table->id();
            $table->string('name');
            $table->string('specialization')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('commission_rate')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('ba_barbers'); } };
