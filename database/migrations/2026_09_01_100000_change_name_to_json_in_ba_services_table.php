<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we can't easily change column type to JSON if it's already string,
        // but in SQLite both are just TEXT. However, we need to convert existing data.

        $services = DB::table('ba_services')->get();

        foreach ($services as $service) {
            // If it's already JSON-like, skip. Otherwise, wrap it in English locale.
            $currentName = $service->name;
            if ($currentName && !str_starts_with(trim($currentName), '{')) {
                $newName = json_encode(['en' => $currentName]);
                DB::table('ba_services')->where('id', $service->id)->update(['name' => $newName]);
            }
        }

        // No schema change needed for SQLite as 'string' and 'json' are both TEXT,
        // but for MySQL/PostgreSQL we would use:
        // Schema::table('ba_services', function (Blueprint $table) {
        //     $table->json('name')->change();
        // });
    }

    public function down(): void
    {
        $services = DB::table('ba_services')->get();

        foreach ($services as $service) {
            $names = json_decode($service->name, true);
            if (is_array($names)) {
                $oldName = $names['en'] ?? array_values($names)[0] ?? '';
                DB::table('ba_services')->where('id', $service->id)->update(['name' => $oldName]);
            }
        }
    }
};
