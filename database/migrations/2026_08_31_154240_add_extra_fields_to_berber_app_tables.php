<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ba_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('ba_bookings', 'customer_name')) {
                $table->string('customer_name')->nullable();
            }
            if (!Schema::hasColumn('ba_bookings', 'customer_phone')) {
                $table->string('customer_phone')->nullable();
            }
            if (!Schema::hasColumn('ba_bookings', 'status')) {
                $table->string('status')->default('pending');
            }
            if (!Schema::hasColumn('ba_bookings', 'reminder_enabled')) {
                $table->boolean('reminder_enabled')->default(true);
            }
            if (!Schema::hasColumn('ba_bookings', 'reminder_minutes')) {
                $table->integer('reminder_minutes')->default(30);
            }
            if (!Schema::hasColumn('ba_bookings', 'fcm_token')) {
                $table->text('fcm_token')->nullable();
            }
            $table->foreignId('customer_id')->nullable()->change();
        });

        Schema::table('ba_reminders', function (Blueprint $table) {
            if (!Schema::hasColumn('ba_reminders', 'status')) {
                $table->string('status')->default('pending');
            }
            if (!Schema::hasColumn('ba_reminders', 'send_at')) {
                $table->datetime('send_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ba_bookings', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_phone', 'status', 'reminder_enabled', 'reminder_minutes', 'fcm_token']);
        });

        Schema::table('ba_reminders', function (Blueprint $table) {
            $table->dropColumn(['status', 'send_at']);
        });
    }
};
