<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemoveView extends Command
{
    protected $signature = 'remove:view {name}';
    protected $description = 'Surgically remove all generated files (including API layer) and database tables for a specific CRUD';

    public function handle()
    {
        $inputName = $this->argument('name');

        // We check both singular and plural to be sure
        $singularName = Str::studly(Str::singular($inputName));
        $pluralName = Str::studly(Str::plural($inputName));

        $pluralKebab = Str::kebab($pluralName);
        $pluralSnake = Str::snake($pluralName);
        $tableName = Str::snake($pluralName);

        $this->warn("🗑️  Removing CRUD ecosystem for $singularName/$pluralName...");

        // 0. Delete Domain Layer (Check both)
        foreach([$singularName, $pluralName] as $n) {
            $domainPath = app_path("Domain/$n");
            if (File::isDirectory($domainPath)) {
                File::deleteDirectory($domainPath);
                $this->info("✓ Deleted Domain Layer: app/Domain/$n");
            }
        }

        // 1. Delete Model (Check both)
        foreach([$singularName, $pluralName] as $n) {
            $modelPath = app_path("Models/$n.php");
            if (File::exists($modelPath)) {
                File::delete($modelPath);
                $this->info("✓ Deleted Model: app/Models/$n.php");
            }
        }

        // 2. Delete Livewire Components
        $livewirePath = app_path("Livewire/Admin/$pluralName");
        if (File::isDirectory($livewirePath)) {
            File::deleteDirectory($livewirePath);
            $this->info("✓ Deleted Livewire Components: app/Livewire/Admin/$pluralName");
        }

        // 3. Delete Views
        $viewPath = resource_path("views/livewire/admin/$pluralKebab");
        if (File::isDirectory($viewPath)) {
            File::deleteDirectory($viewPath);
            $this->info("✓ Deleted Views: resources/views/livewire/admin/$pluralKebab");
        }

        // 4. Delete Route Files
        $routeFile = base_path("routes/admin/$pluralKebab.php");
        if (File::exists($routeFile)) {
            File::delete($routeFile);
            $this->info("✓ Deleted Route File: routes/admin/$pluralKebab.php");
        }

        // 5. API Layer Cleanup
        foreach([$singularName, $pluralName] as $n) {
            $apiController = app_path("Http/Controllers/Api/{$n}Controller.php");
            if (File::exists($apiController)) {
                File::delete($apiController);
                $this->info("✓ Deleted API Controller: $n" . "Controller");
            }

            $apiResource = app_path("Http/Resources/{$n}Resource.php");
            if (File::exists($apiResource)) {
                File::delete($apiResource);
                $this->info("✓ Deleted API Resource: $n" . "Resource");
            }

            $apiRequestDir = app_path("Http/Requests/Api/$n");
            if (File::isDirectory($apiRequestDir)) {
                File::deleteDirectory($apiRequestDir);
                $this->info("✓ Deleted API Requests: Http/Requests/Api/$n");
            }
        }

        // Remove API Route entry
        $apiRoutePath = base_path("routes/api.php");
        if (File::exists($apiRoutePath)) {
            $apiContent = File::get($apiRoutePath);
            $apiPattern = "/Route::apiResource\('$pluralKebab'.*?;/s";
            $newApiContent = preg_replace($apiPattern, '', $apiContent);
            File::put($apiRoutePath, $newApiContent);
            $this->info("✓ Cleaned up routes/api.php");
        }

        // 6. Delete Translation Files
        if (File::exists(lang_path())) {
            foreach (File::directories(lang_path()) as $dir) {
                $langFile = $dir . "/$pluralKebab.php";
                if (File::exists($langFile)) {
                    File::delete($langFile);
                    $this->info("✓ Deleted Translation File: " . basename($dir) . "/$pluralKebab.php");
                }
            }
        }

        // 7. Delete Migration & Drop Table
        if (Schema::hasTable($tableName)) {
            Schema::dropIfExists($tableName);
            $this->info("✓ Dropped Table: $tableName");
        }

        $migrations = File::files(database_path('migrations'));
        foreach ($migrations as $migration) {
            if (str_contains($migration->getFilename(), "create_{$tableName}_table")) {
                File::delete($migration->getPathname());
                $this->info("✓ Deleted Migration File: " . $migration->getFilename());
                DB::table('migrations')->where('migration', 'like', '%' . str_replace('.php', '', $migration->getFilename()) . '%')->delete();
            }
        }

        // 8. Remove Navigation Link
        $navPath = resource_path('views/components/layouts/app/navigation.blade.php');
        if (File::exists($navPath)) {
            $content = File::get($navPath);
            $pattern = "/@can\('view_{$pluralSnake}'\).*?@endcan/s";
            $newContent = preg_replace($pattern, '', $content);
            File::put($navPath, $newContent);
            $this->info("✓ Removed sidebar navigation link.");
        }

        // 9. Remove Permissions from DB
        Permission::where('module', $pluralName)
            ->orWhere('module', $singularName)
            ->orWhere('name', 'like', "%_{$pluralSnake}")
            ->delete();
        $this->info("✓ Removed permissions from database.");

        $this->info("✅ ALL components for $singularName have been surgically removed!");
    }
}
