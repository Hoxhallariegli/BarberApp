<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ba_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('ba_bookings', 'token')) {
                $table->string('token')->nullable()->unique()->after('id');
            }
            // status already exists in a previous migration but let's ensure it's there or update it
            // Checked earlier: 2026_08_31_154240_add_extra_fields_to_berber_app_tables.php added status
            // But let's add it if it's missing just in case, or add 'confirmed', 'cancelled' etc to comment
        });
    }

    public function down(): void
    {
        Schema::table('ba_bookings', function (Blueprint $table) {
            $table->dropColumn(['token']);
        });
    }
};
