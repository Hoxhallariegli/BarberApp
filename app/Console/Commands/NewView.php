<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Permission;

class NewView extends Command
{
    protected $signature = 'new:view {name} {--api : Generate API layer automatically} {--firebase : Enable Firebase notifications}';
    protected $description = 'Universal DDD Scaffolder - God Version (Pro UI, Nested Modals, Hardened)';

    protected array $reserved = [
        'class', 'function', 'array', 'return', 'if', 'else', 'elseif', 'parent', 'self', 'static',
        'string', 'int', 'integer', 'float', 'bool', 'boolean', 'object', 'null', 'true', 'false',
        'interface', 'trait', 'namespace', 'use', 'new', 'clone', 'match', 'enum', 'fn', 'yield',
        'id', 'created_at', 'updated_at', 'deleted_at',
    ];

    public function handle()
    {
        try {
            return $this->scaffold();
        } catch (\Throwable $e) {
            $this->error('Scaffolding failed: ' . $e->getMessage());
            $this->line($e->getFile() . ':' . $e->getLine());
            return 1;
        }
    }

    protected function scaffold()
    {
        $rawName = trim((string) $this->argument('name'));
        $clean = preg_replace('/[^A-Za-z0-9_\- ]/', '', $rawName);
        $name = Str::studly((string) $clean);

        if (!$name || !preg_match('/^[A-Z][A-Za-z0-9]*$/', $name)) {
            $this->error("Invalid model name '$rawName'. Use letters and numbers only.");
            return 1;
        }
        if (in_array(strtolower($name), $this->reserved, true)) {
            $this->error("Error: '$name' is a reserved word.");
            return 1;
        }

        $tableName = Str::snake(Str::pluralStudly($name));
        $pluralName = Str::plural($name);
        $pluralKebab = Str::kebab($pluralName);
        $pluralSnake = Str::snake($pluralName);

        if (Schema::hasTable($tableName)) {
            $this->error("Table '$tableName' already exists. Aborting to avoid a conflicting migration.");
            return 1;
        }

        // --- Overwrite guard ---
        $modelPath = app_path("Models/$name.php");
        $domainPath = app_path("Domain/$name");
        $livewirePath = app_path("Livewire/Admin/$pluralName");
        $viewsPath = resource_path('views/livewire/admin/' . Str::kebab($pluralName));
        $routePath = base_path("routes/admin/$pluralKebab.php");

        $existing = array_filter([
            $modelPath => File::exists($modelPath),
            $domainPath => File::isDirectory($domainPath),
            $livewirePath => File::isDirectory($livewirePath),
            $viewsPath => File::isDirectory($viewsPath),
            $routePath => File::exists($routePath),
        ]);

        if (!empty($existing)) {
            $this->warn('The following already exist and will be OVERWRITTEN:');
            foreach (array_keys($existing) as $p) {
                $this->line(" - $p");
            }
            if (!$this->confirm('Continue and overwrite these?', false)) {
                $this->info('Aborted. Nothing was changed.');
                return 1;
            }
        }

        $fields = [];
        $usedNames = ['id', 'created_at', 'updated_at', 'deleted_at'];
        $this->info("🏗️ Starting ENTERPRISE Scaffolding for $name");

        while (true) {
            $fieldNameRaw = trim((string) $this->ask('Field name (leave empty to finish)'));
            if ($fieldNameRaw === '') {
                break;
            }

            $fieldName = Str::snake($fieldNameRaw);
            if (!preg_match('/^[a-z][a-z0-9_]*$/', $fieldName)) {
                $this->error("Invalid field name '$fieldNameRaw'. Use lowercase letters, numbers and underscores, starting with a letter.");
                continue;
            }
            if (in_array($fieldName, $usedNames, true)) {
                $this->error("Field '$fieldName' is already used or reserved.");
                continue;
            }

            $type = $this->choice('Field type', [
                'string', 'text', 'integer', 'bigInteger', 'boolean', 'decimal', 'date', 'datetime', 'foreignId', 'enum'
            ], 0);

            $extra = ''; $relatedModel = ''; $labelField = 'name'; $options = [];

            if ($type === 'foreignId') {
                $extra = Str::snake(trim((string) $this->ask('Constrained table', Str::snake(Str::pluralStudly(str_replace('_id', '', $fieldName))))));
                if (!Schema::hasTable($extra)) {
                    $this->warn("Warning: table '$extra' doesn't exist yet — migration will fail unless it's created before this one runs.");
                }
                $relatedModel = Str::studly(Str::singular($extra));
                if (!class_exists("App\\Models\\$relatedModel")) {
                    $this->warn("Warning: App\\Models\\$relatedModel doesn't exist yet — the relation/dropdown will error until it's scaffolded too.");
                }
                $labelField = trim((string) $this->ask("Display field for $relatedModel (supports dot notation like employee.name)?", 'name'));
                if (!preg_match('/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)?$/', $labelField)) {
                    $this->warn("Invalid display field '$labelField', falling back to 'name'.");
                    $labelField = 'name';
                }
            }

            if ($type === 'enum') {
                $optRaw = (string) $this->ask('Enum options (comma separated)');
                $options = array_values(array_unique(array_filter(array_map(
                    fn ($o) => trim($o),
                    explode(',', $optRaw)
                ))));
                if (count($options) < 2) {
                    $this->error('Enum needs at least 2 non-empty, unique options. Field skipped.');
                    continue;
                }
            }

            $fields[] = [
                'name' => $fieldName, 'type' => $type, 'constrained' => $extra,
                'relatedModel' => $relatedModel, 'labelField' => $labelField,
                'options' => $options, 'nullable' => $this->confirm('Nullable?', false)
            ];
            $usedNames[] = $fieldName;
        }

        $iconRaw = (string) $this->ask('Menu icon', 'chevron-right');
        $iconClean = preg_replace('/[^a-z0-9\-]/', '', strtolower($iconRaw)) ?: 'chevron-right';
        $iconMap = [
            'box' => 'archive-box', 'file' => 'document', 'clipboard' => 'clipboard-document',
            'office-building' => 'building-office', 'desktop' => 'computer-desktop',
            'pencil' => 'pencil-square', 'chart-bar' => 'chart-bar'
        ];
        $icon = $iconMap[$iconClean] ?? $iconClean;

        $withApi = $this->option('api') || ($this->choice('Generate API?', ['No', 'Yes'], 0) === 'Yes');

        $this->generateDomainStructure($name, $fields);
        $this->generateMigration($tableName, $fields);
        $this->generateModel($name, $fields);
        $this->generateLivewireComponents($name, $pluralSnake, $pluralName, $pluralKebab, $fields);
        $this->generateViews($name, $fields, $pluralSnake, $pluralName);

        if ($withApi) {
            $this->generateApiLayer($name, $pluralName, $pluralKebab, $fields);
        }

        $this->info('💾 Migrating...');
        $exitCode = $this->call('migrate');
        if ($exitCode !== 0) {
            $this->error('Migration failed. Files were generated but the table was not created.');
            $this->warn('Fix database/migrations manually, run "php artisan migrate", then re-add permissions/nav by hand if needed.');
            return 1;
        }

        $this->addPermissions($name, $pluralSnake);
        $this->generateTranslationFiles($pluralKebab, $name, $fields);
        $this->generateRouteFile($pluralName, $pluralKebab, $name, $pluralSnake);
        $this->addNavigation($pluralName, $pluralKebab, $pluralSnake, $icon);

        $this->info("✅ DONE! $name is ready with Modal Support.");
        return 0;
    }

    protected function generateDomainStructure($name, $fields)
    {
        $baseDir = app_path("Domain/$name");
        foreach (['Actions', 'DTOs', 'Queries', 'Events'] as $d) {
            File::makeDirectory("$baseDir/$d", 0755, true, true);
        }
        $this->generateDTO($name, $fields);
        $this->generateQuery($name, $fields);
        $this->generateActions($name);
    }

    protected function generateActions($name)
    {
        $dir = app_path("Domain/$name/Actions");
        $plural = Str::plural($name);
        File::put("$dir/Create{$name}Action.php", "<?php\n\nnamespace App\Domain\\$name\Actions;\n\nuse App\Models\\$name;\nuse App\Domain\\$name\DTOs\\{$name}DTO;\nuse App\Models\AuditTrail;\n\nclass Create{$name}Action\n{\n    public function execute({$name}DTO \$dto): $name \n    {\n        \$item = $name::create(\$dto->toArray());\n        AuditTrail::log(\$item, 'create', '$plural');\n        return \$item;\n    }\n}");
        File::put("$dir/Update{$name}Action.php", "<?php\n\nnamespace App\Domain\\$name\Actions;\n\nuse App\Models\\$name;\nuse App\Domain\\$name\DTOs\\{$name}DTO;\nuse App\Models\AuditTrail;\n\nclass Update{$name}Action\n{\n    public function execute($name \$model, {$name}DTO \$dto): $name\n    {\n        \$model->fill(\$dto->toArray());\n        AuditTrail::log(\$model, 'update', '$plural');\n        \$model->save();\n        return \$model->fresh();\n    }\n}");
        File::put("$dir/Delete{$name}Action.php", "<?php\n\nnamespace App\Domain\\$name\Actions;\n\nuse App\Models\\$name;\nuse App\Models\AuditTrail;\n\nclass Delete{$name}Action\n{\n    public function execute($name \$model): bool \n    {\n        AuditTrail::log(\$model, 'delete', '$plural');\n        return \$model->delete(); \n    }\n}");
    }

    protected function generateDTO($name, $fields)
    {
        $props = ''; $args = ''; $toArray = '';
        foreach ($fields as $f) {
            $props .= "        public readonly mixed \${$f['name']},\n";
            $args .= "            {$f['name']}: \$data['{$f['name']}'] ?? null,\n";
            $toArray .= "            '{$f['name']}' => \$this->{$f['name']},\n";
        }
        $stub = "<?php\n\nnamespace App\Domain\\$name\DTOs;\n\nclass {$name}DTO\n{\n    public function __construct(\n$props    ) {}\n    public static function fromArray(array \$data): self { return new self(\n$args        ); }\n    public function toArray(): array { return [\n$toArray        ]; }\n}";
        File::put(app_path("Domain/$name/DTOs/{$name}DTO.php"), $stub);
    }

    protected function generateQuery($name, $fields)
    {
        $with = collect($fields)->filter(fn ($f) => $f['type'] === 'foreignId')->map(function($f) {
            $rel = Str::camel(str_replace('_id', '', $f['name']));
            if (Str::contains($f['labelField'], '.')) {
                $subRel = Str::beforeLast($f['labelField'], '.');
                return "'$rel.$subRel'";
            }
            return "'$rel'";
        })->unique()->implode(', ');

        $searchFields = collect($fields)->filter(fn ($f) => in_array($f['type'], ['string', 'text']))->map(fn ($f) => "                \$query->orWhere('{$f['name']}', 'like', '%' . \$params['search'] . '%');")->implode("\n");
        $filters = collect($fields)->filter(fn ($f) => $f['type'] === 'foreignId')->map(fn ($f) => "        if (isset(\$params['{$f['name']}']) && \$params['{$f['name']}']) \$query->where('{$f['name']}', \$params['{$f['name']}']);")->implode("\n");
        File::put(app_path("Domain/$name/Queries/{$name}ListQuery.php"), "<?php\n\nnamespace App\Domain\\$name\Queries;\n\nuse App\Models\\$name;\nuse Illuminate\Database\Eloquent\Builder;\n\nclass {$name}ListQuery\n{\n    public function handle(array \$params = [], string \$sortField = 'id', string \$sortAsc = 'asc'): Builder\n    {\n        \$query = $name::query()" . ($with ? "->with([$with])" : '') . ";\n        if (isset(\$params['search']) && \$params['search']) {\n            \$query->where(function(\$query) use (\$params) {\n                \$query->where('id', 'like', '%' . \$params['search'] . '%');\n$searchFields\n            });\n        }\n$filters\n        \$sortField = in_array(\$sortField, $name::sortable(), true) ? \$sortField : 'id';\n        \$sortAsc = in_array(strtolower((string) \$sortAsc), ['asc', 'desc'], true) ? \$sortAsc : 'asc';\n        return \$query->orderBy(\$sortField, \$sortAsc);\n    }\n}");
    }

    protected function generateModel($name, $fields)
    {
        $fillable = collect($fields)->map(fn ($f) => "'{$f['name']}'")->implode(', ');
        $relations = ''; $casts = ''; $rules = ''; $sortable = "'id'";
        foreach ($fields as $f) {
            if ($f['type'] === 'foreignId') {
                $rel = Str::camel(str_replace('_id', '', $f['name']));
                $relations .= "\n    public function $rel(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return \$this->belongsTo(\\App\Models\\{$f['relatedModel']}::class, '{$f['name']}'); }\n";
            }
            if ($f['type'] === 'boolean') {
                $casts .= "            '{$f['name']}' => 'boolean',\n";
            }
            if (in_array($f['type'], ['date', 'datetime'])) {
                $casts .= "            '{$f['name']}' => 'datetime',\n";
            }
            if ($f['type'] === 'decimal') {
                $casts .= "            '{$f['name']}' => 'decimal:2',\n";
            }

            $req = $f['nullable'] ? "'nullable'" : "'required'";
            $typeRule = match ($f['type']) {
                'string' => "'string', 'max:255'",
                'text' => "'string'",
                'integer', 'bigInteger' => "'integer'",
                'boolean' => "'boolean'",
                'decimal' => "'numeric'",
                'date', 'datetime' => "'date'",
                'foreignId' => "'integer'",
                'enum' => "\Illuminate\Validation\Rule::in(['" . implode("', '", array_map('addslashes', $f['options'])) . "'])",
                default => null,
            };
            $rules .= "            '{$f['name']}' => [$req" . ($typeRule ? ", $typeRule" : '') . "],\n";
            $sortable .= ", '{$f['name']}'";
        }
        File::put(app_path("Models/$name.php"), "<?php\n\nnamespace App\Models;\n\nuse Illuminate\Database\Eloquent\Factories\HasFactory;\nuse Illuminate\Database\Eloquent\Model;\n\nclass $name extends Model\n{\n    use HasFactory;\n    protected \$fillable = [$fillable];\n    protected function casts(): array { return [\n$casts        ]; }\n    public static function rules(\$id = null): array { return [\n$rules        ]; }\n    public static function sortable(): array { return [$sortable]; }\n$relations\n}");
    }

    protected function generateLivewireComponents($name, $pluralSnake, $pluralName, $pluralKebab, $fields)
    {
        $camel = Str::camel($name);
        $dir = app_path("Livewire/Admin/$pluralName");
        File::makeDirectory($dir, 0755, true, true);
        $viewPath = 'livewire.admin.' . Str::kebab($pluralName);

        $hasFile = collect($fields)->contains(fn ($f) => Str::contains(strtolower($f['name']), ['file', 'document', 'image', 'photo']));
        $availableListsIndex = ''; $availableListsForm = ''; $props = ''; $dataMap = ''; $fileHandlers = '';
        $filterReset = ''; $filterProps = ''; $renderFilters = '';

        foreach ($fields as $f) {
            $props .= "    public \${$f['name']} = '';\n";
            $dataMap .= "            '{$f['name']}' => \$this->{$f['name']},\n";
            if ($f['type'] === 'foreignId') {
                $rv = Str::plural(Str::camel(str_replace('_id', '', $f['name'])));

                if (Str::contains($f['labelField'], '.')) {
                    $relPart = explode('.', $f['labelField'])[0];
                    $availableListsIndex .= "            '{$rv}' => \\App\\Models\\{$f['relatedModel']}::with('$relPart')->get()->pluck('{$f['labelField']}', 'id')->toArray(),\n";
                } else {
                    $availableListsIndex .= "            '{$rv}' => \\App\\Models\\{$f['relatedModel']}::pluck('{$f['labelField']}', 'id')->toArray(),\n";
                }

                $availableListsForm .= "            '{$rv}' => \$this->get{$rv}List(),\n";
                $filterReset .= "'{$f['name']}', ";
                $filterProps .= "    #[Url(history: true)] public \${$f['name']} = '';\n";
                $renderFilters .= "            '{$f['name']}' => \$this->{$f['name']},\n";
            }
            if (Str::contains(strtolower($f['name']), ['file', 'document'])) {
                $fileHandlers .= "        if (\$this->{$f['name']} && !is_string(\$this->{$f['name']})) { \$this->{$f['name']} = \$this->{$f['name']}->store('uploads/$pluralKebab', 'public'); }\n";
            }
        }

        $imports = "use Livewire\Component;\nuse Livewire\WithPagination;\nuse Livewire\Attributes\Title;\nuse Livewire\Attributes\Url;\nuse Livewire\Attributes\On;\n";
        if ($hasFile) {
            $imports .= "use Livewire\WithFileUploads;\n";
        }
        $traits = '    use WithPagination' . ($hasFile ? ', WithFileUploads' : '') . ";\n";

        // $onEvents / $formHelperMethods are shared by EVERY component that has a
        // foreignId field, including QuickCreate. This is what makes nested \"create
        // related record from inside a modal\" propagate the new id back into the
        // field that opened it, at any nesting depth.
        $formHelperMethods = ''; $onEvents = ''; $updatedHooks = '';
        foreach ($fields as $f) {
            if ($f['type'] === 'foreignId') {
                $rv = Str::plural(Str::camel(str_replace('_id', '', $f['name'])));
                $onEvents .= "\n    #[On('" . Str::kebab($f['relatedModel']) . "-created')] \n    public function refresh" . Str::studly($rv) . "(\$id) { \$this->{$f['name']} = \$id; \$this->updated" . Str::studly($f['name']) . "(\$id); }\n";

                // Add updated hook for auto-filling related fields
                $updatedHooks .= "\n    public function updated" . Str::studly($f['name']) . "(\$value)\n    {\n        if (!\$value) return;\n        \$related = \\App\\Models\\{$f['relatedModel']}::find(\$value);\n        if (!\$related) return;\n";
                foreach ($fields as $otherField) {
                    if ($otherField['name'] !== $f['name'] && $otherField['type'] === 'foreignId') {
                        // If the related model has a field with the same name as our other field, auto-fill it
                        $updatedHooks .= "        if (isset(\$related->{$otherField['name']})) { \$this->{$otherField['name']} = \$related->{$otherField['name']}; }\n";
                    }
                }
                $updatedHooks .= "    }\n";

                $formHelperMethods .= "\n    protected function get{$rv}List() {\n";
                if (Str::contains($f['labelField'], '.')) {
                    $relPart = explode('.', $f['labelField'])[0];
                    $formHelperMethods .= "        return \\App\\Models\\{$f['relatedModel']}::with('$relPart')->get()->pluck('{$f['labelField']}', 'id')->toArray();\n";
                } else {
                    $formHelperMethods .= "        return \\App\\Models\\{$f['relatedModel']}::pluck('{$f['labelField']}', 'id')->toArray();\n";
                }
                $formHelperMethods .= "    }\n";
            }
        }

        $indexStub = "<?php\n\nnamespace App\Livewire\Admin\\$pluralName;\n\nuse App\Models\\$name;\nuse App\Domain\\$name\Queries\\{$name}ListQuery;\nuse App\Domain\\$name\Actions\\Delete{$name}Action;\n$imports\n#[Title('$pluralName')]\nclass $pluralName extends Component\n{\n    $traits\n    public int \$paginate = 10;\n    #[Url(history: true)] public string \$search = '';\n$filterProps    public bool \$openFilter = false;\n    public string \$sortField = 'id';\n    public bool \$sortAsc = true;\n\n    public function resetFilters() { \$this->reset(['search', 'openFilter', $filterReset]); \$this->resetPage(); }\n\n    public function render()\n    {\n        abort_if_cannot('view_{$pluralSnake}');\n        \$query = (new {$name}ListQuery())->handle(['search' => \$this->search, $renderFilters], \$this->sortField, \$this->sortAsc ? 'asc' : 'desc');\n\n        return view('$viewPath.index', [\n            'items' => \$query->paginate(\$this->paginate),\n            'sortableFields' => $name::sortable(),\n$availableListsIndex        ])->layout('components.layouts.app');\n    }\n\n    public function sortBy(\$field) { if (!in_array(\$field, $name::sortable(), true)) return; if (\$this->sortField === \$field) { \$this->sortAsc = ! \$this->sortAsc; } \$this->sortField = \$field; }\n\n    public function delete$name(\$id, Delete{$name}Action \$action) \n    {\n        abort_if_cannot('delete_{$pluralSnake}');\n        \$item = $name::find(\$id);\n        if (!\$item) { \$this->dispatch('toast', message: __('$pluralKebab.not_found'), type: 'error'); return; }\n        try { \$action->execute(\$item); \$this->dispatch('toast', message: __('$pluralKebab.deleted'), type: 'success'); \$this->resetPage(); } \n        catch (\\Illuminate\\Database\\QueryException \$e) { \$this->dispatch('toast', message: __('$pluralKebab.delete_error_referenced'), type: 'error'); }\n        catch (\\Exception \$e) { \$this->dispatch('toast', message: __('$pluralKebab.delete_error'), type: 'error'); }\n    }\n}";
        File::put("$dir/$pluralName.php", $indexStub);

        File::put("$dir/Create.php", "<?php\n\nnamespace App\Livewire\Admin\\$pluralName;\n\nuse App\Models\\$name;\nuse App\Domain\\$name\DTOs\\{$name}DTO;\nuse App\Domain\\$name\Actions\\Create{$name}Action;\n$imports\n#[Title('Add $name')]\nclass Create extends Component\n{\n    $traits $props $onEvents $updatedHooks $formHelperMethods\n    public function render() { abort_if_cannot('add_{$pluralSnake}'); return view('$viewPath.create', [\n$availableListsForm        ])->layout('components.layouts.app'); }\n    public function store(Create{$name}Action \$action) { \$this->validate(); $fileHandlers \$dto = {$name}DTO::fromArray([\n$dataMap        ]); \$action->execute(\$dto); session()->flash('success', __('$pluralKebab.created')); return to_route('admin.$pluralKebab.index'); }\n    protected function rules(): array { return $name::rules(); }\n}");

        $dateFills = collect($fields)->filter(fn ($f) => in_array($f['type'], ['date', 'datetime']))->map(function ($f) use ($camel) {
            $fmt = $f['type'] === 'datetime' ? "'Y-m-d\\TH:i'" : "'Y-m-d'";
            return "\$this->{$f['name']} = \$" . $camel . "->{$f['name']}?->format($fmt);";
        })->implode(' ');
        File::put("$dir/Edit.php", "<?php\n\nnamespace App\Livewire\Admin\\$pluralName;\n\nuse App\Models\\$name;\nuse App\Domain\\$name\DTOs\\{$name}DTO;\nuse App\Domain\\$name\Actions\\Update{$name}Action;\n$imports\n#[Title('Edit $name')]\nclass Edit extends Component\n{\n    $traits public $name \$item;\n$props $onEvents $updatedHooks $formHelperMethods\n    public function mount($name \$" . $camel . ") { \$this->item = \$" . $camel . "; \$this->fill(\$" . $camel . "->toArray()); $dateFills }\n    public function render() { abort_if_cannot('edit_{$pluralSnake}'); return view('$viewPath.edit', [\n$availableListsForm        ])->layout('components.layouts.app'); }\n    public function update(Update{$name}Action \$action) { \$this->validate(); $fileHandlers \$dto = {$name}DTO::fromArray([\n$dataMap        ]); \$action->execute(\$this->item, \$dto); session()->flash('success', __('$pluralKebab.updated')); return to_route('admin.$pluralKebab.index'); }\n    protected function rules(): array { return $name::rules(\$this->item->id); }\n}");
        File::put("$dir/Row.php", "<?php\n\nnamespace App\Livewire\Admin\\$pluralName;\n\nuse App\Models\\$name;\nuse Livewire\Component;\n\nclass Row extends Component { public $name \$item; public function render() { return view('$viewPath.row'); } }");

        // QuickCreate — the modal/nested version. It now gets $onEvents + $formHelperMethods
        // too, exactly like Create/Edit, so any foreignId field inside a modal can ALSO
        // listen for its own nested \"-created\" events and resolve its own dropdown lists.
        //
        // It no longer dispatches 'close-modal' — the modal stays open after a successful
        // save, shows an inline success state (see quick-create.blade.php), and is closed
        // only by the user (via the modal's own X button). \$this->reset() only clears the
        // input fields, not \$created/\$createdId/\$createdLabel, so the success state sticks.
        $displayField = 'id';
        foreach ($fields as $f) {
            if (in_array($f['name'], ['name', 'title', 'label'], true)) {
                $displayField = $f['name'];
                break;
            }
        }
        $resetList = collect($fields)->map(fn ($f) => "'{$f['name']}'")->implode(', ');

        $kebabName = Str::kebab($name);
        File::put("$dir/QuickCreate.php", "<?php\n\nnamespace App\Livewire\Admin\\$pluralName;\n\nuse App\Models\\$name;\nuse App\Domain\\$name\DTOs\\{$name}DTO;\nuse App\Domain\\$name\Actions\\Create{$name}Action;\n$imports\nclass QuickCreate extends Component\n{\n    $traits $props $onEvents $updatedHooks $formHelperMethods\n    public bool \$created = false;\n    public ?int \$createdId = null;\n    public string \$createdLabel = '';\n\n    public function render() { return view('$viewPath.quick-create', [\n$availableListsForm        ]); }\n\n    public function store(Create{$name}Action \$action)\n    {\n        \$this->validate();\n$fileHandlers        \$dto = {$name}DTO::fromArray([\n$dataMap        ]);\n        \$item = \$action->execute(\$dto);\n        \$this->dispatch('$kebabName-created', id: \$item->id);\n        \$this->js(\"Livewire.dispatch('$kebabName-created', { id: {\$item->id} })\");\n        \$this->dispatch('toast', message: __('$pluralKebab.created'), type: 'success');\n        \$this->created = true;\n        \$this->createdId = \$item->id;\n        \$this->createdLabel = (string) (\$item->{$displayField} ?? \$item->id);\n        \$this->reset([$resetList]);\n    }\n\n    public function addAnother()\n    {\n        \$this->created = false;\n        \$this->createdId = null;\n        \$this->createdLabel = '';\n    }\n\n    protected function rules(): array { return $name::rules(); }\n}");
    }

    protected function generateViews($name, $fields, $pluralSnake, $pluralName)
    {
        $dir = resource_path('views/livewire/admin/' . Str::kebab($pluralName));
        File::makeDirectory($dir, 0755, true, true);
        $pk = Str::kebab($pluralName);

        File::put("$dir/index.blade.php", $this->getIndexStub($name, $pluralName, $pk, $fields));
        File::put("$dir/create.blade.php", $this->getCreateStub($name, $pk, $fields));
        File::put("$dir/edit.blade.php", $this->getEditStub($name, $pk, $fields));
        File::put("$dir/row.blade.php", $this->getRowStub($name, $pk, $fields, $pluralSnake));
        File::put("$dir/quick-create.blade.php", "<div class=\"p-6\">\n    @if(\$created)\n        <div class=\"flex flex-col items-center text-center py-10\">\n            <div class=\"w-12 h-12 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mb-4\">\n                <x-heroicon-o-check class=\"w-6 h-6 text-green-500\" />\n            </div>\n            <p class=\"font-bold text-gray-900 dark:text-white\">{{ __('$pk.created') }}</p>\n            @if(\$createdLabel)<p class=\"text-sm text-gray-500 dark:text-gray-400 mt-1\">{{ \$createdLabel }}</p>@endif\n            <button type=\"button\" wire:click=\"addAnother\" class=\"mt-6 text-xs font-black uppercase tracking-widest text-blue-600 dark:text-blue-400\">{{ __('$pk.Add $name') }}</button>\n        </div>\n    @else\n        " . $this->getInputs($fields, $pk, true) . "\n        <div class=\"mt-8 flex justify-end\"><x-button wire:click=\"store\" variant=\"blue\">{{ __('$pk.Save') }}</x-button></div>\n    @endif\n</div>");
    }

    protected function getIndexStub($name, $pluralName, $pk, $fields)
    {
        $searchable = collect($fields)->filter(fn ($f) => in_array($f['type'], ['string', 'text']))->map(fn ($f) => Str::title($f['name']))->prepend('ID')->implode(', ');
        $filters = collect($fields)->filter(fn ($f) => $f['type'] === 'foreignId')->map(function ($f) use ($pk) {
            $rv = Str::plural(Str::camel(str_replace('_id', '', $f['name'])));
            $label = Str::title(str_replace('_', ' ', $f['name']));
            return "<div><label class=\"block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100\">$label</label><x-form.dropdown-search name=\"{$f['name']}\" wire:model.live=\"{$f['name']}\" label=\"none\" :data=\"\${$rv}\" placeholder=\"Filter $label\" /></div>";
        })->implode("\n");

        return "<div x-data=\"{ openFilter: @entangle('openFilter') }\">\n    <div class=\"card !p-0 overflow-hidden shadow-none border-gray-200 dark:border-gray-700 dark:bg-gray-800\">\n        <div class=\"p-6\">\n            <div class=\"flex flex-col sm:flex-row sm:items-center justify-between gap-4\">\n                <div><x-h1>{{ __('$pk.$pluralName') }}</x-h1><x-short-description class=\"dark:text-gray-400\">{{ __('$pk.List of') }} ".strtolower($pluralName)."</x-short-description></div>\n                <div class=\"flex items-center gap-3\">\n                    @if(\$search || \$openFilter)\n                        <button wire:click=\"resetFilters\" class=\"inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-none shadow-none\"><span>{{ __('$pk.Reset') }}</span></button>\n                    @endif\n                    <button @click=\"openFilter = !openFilter\" class=\"inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-600 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm transition-none\"><span>{{ __('$pk.Filters') }}</span></button>\n                    <x-btn :href=\"route('admin.$pk.create')\" icon=\"plus\">{{ __('$pk.Add $name') }}</x-btn>\n                </div>\n            </div>\n\n            <div x-show=\"openFilter\" x-cloak class=\"mt-6 p-6 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-2xl\">\n                <div class=\"grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6\">\n                    <div>\n                        <label class=\"block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100\">{{ __('$pk.Search') }}</label>\n                        <input name=\"search\" wire:model.live.debounce.300ms=\"search\" type=\"text\" placeholder=\"Search by $searchable\" class=\"w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white\">\n                    </div>\n                    $filters\n                </div>\n            </div>\n        </div>\n\n        @include('errors.messages')\n\n        <div class=\"overflow-x-auto border-t border-gray-100 dark:border-gray-700\">\n            <table class=\"w-full text-sm text-left text-gray-500 dark:text-gray-400\">\n                <thead class=\"bg-gray-100/50 dark:bg-gray-700/50\"><tr><x-table.th name=\"id\" :label=\"__('$pk.ID')\" :\$sortField :\$sortAsc :sortable=\"true\" />" . collect($fields)->map(fn ($f) => "<x-table.th name=\"{$f['name']}\" :label=\"__('$pk.".Str::title(str_replace('_', ' ', $f['name']))."')\" :\$sortField :\$sortAsc :sortable=\"in_array('{$f['name']}', \$sortableFields)\" />")->implode("\n") . "<th class=\"px-6 py-4 text-right text-[10px] font-black uppercase text-gray-400 tracking-widest\">{{ __('$pk.Action') }}</th></tr></thead>\n                <tbody class=\"divide-y divide-gray-50 dark:divide-gray-700/50\">@forelse(\$items as \$item) <livewire:admin.$pk.row :\$item :key=\"\$item->id\" /> @empty <tr><td colspan=\"100\" class=\"px-6 py-10 text-center text-sm text-gray-400\">{{ __('$pk.No records found.') }}</td></tr> @endforelse</tbody>\n            </table>\n        </div>\n        <div class=\"p-4 border-t border-gray-50 dark:border-gray-700/50\">{{ \$items->links() }}</div>\n    </div>\n</div>";
    }

    protected function getCreateStub($name, $pk, $fields)
    {
        return "<div class=\"space-y-10\">\n    <div class=\"flex items-center justify-between gap-4 px-1\"><div><x-h1>{{ __('$pk.Add $name') }}</x-h1><x-short-description class=\"dark:text-gray-400\">{{ __('$pk.New record') }}</x-short-description></div><x-back-btn route=\"admin.$pk.index\" /></div>\n    @include('errors.errors')\n    <div class=\"bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700\"><form wire:submit.prevent=\"store\" class=\"space-y-8\">".$this->getInputs($fields, $pk, true)."<div class=\"mt-10 flex justify-end\"><x-button type=\"submit\" variant=\"blue\" class=\"w-full sm:w-auto !px-12 !py-4 !rounded-2xl\">{{ __('$pk.Save') }}</x-button></div></form></div>\n</div>";
    }

    protected function getEditStub($name, $pk, $fields)
    {
        return "<div class=\"space-y-10\">\n    <div class=\"flex items-center justify-between gap-4 px-1\"><div><x-h1>{{ __('$pk.Edit $name') }}</x-h1><x-short-description class=\"dark:text-gray-400\">{{ __('$pk.Update info') }}</x-short-description></div><x-back-btn route=\"admin.$pk.index\" /></div>\n    @include('errors.errors')\n    <div class=\"bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700\"><form wire:submit.prevent=\"update\" class=\"space-y-8\">".$this->getInputs($fields, $pk, false)."<div class=\"mt-10 flex justify-end\"><x-button type=\"submit\" variant=\"blue\" class=\"w-full sm:w-auto !px-12 !py-4 !rounded-2xl\">{{ __('$pk.Update') }}</x-button></div></form></div>\n</div>";
    }

    protected function getRowStub($name, $pk, $fields, $pluralSnake)
    {
        $cells = collect($fields)->map(function ($f) {
            if ($f['type'] === 'foreignId') {
                $labelPath = str_replace('.', '?->', $f['labelField']);
                return "<td class=\"px-6 py-5 font-bold text-gray-900 dark:text-white\">{{ \$item->".Str::camel(str_replace('_id', '', $f['name']))."?->{$labelPath} ?? '-' }}</td>";
            }
            if (Str::contains(strtolower($f['name']), ['file', 'document'])) {
                return "<td class=\"px-6 py-5\">@if(\$item->{$f['name']}) <a href=\"{{ asset('storage/'.\$item->{$f['name']}) }}\" target=\"_blank\" rel=\"noopener\"><x-heroicon-o-arrow-down-tray class=\"w-5 h-5 text-blue-500\" /></a> @else - @endif</td>";
            }
            if (in_array($f['type'], ['date', 'datetime'])) {
                return "<td class=\"px-6 py-5 text-gray-600 dark:text-gray-300\">{{ \$item->{$f['name']}?->format('d/m/Y H:i') ?? '-' }}</td>";
            }
            return "<td class=\"px-6 py-5 text-gray-600 dark:text-gray-300\">{{ \$item->{$f['name']} }}</td>";
        })->implode("\n");

        $displayNameField = 'id';
        foreach ($fields as $f) {
            if (in_array($f['name'], ['name', 'title', 'label'], true)) {
                $displayNameField = $f['name'];
            }
        }

        return "<tr class=\"hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none\">\n    <td class=\"px-6 py-5 font-bold text-blue-600 dark:text-blue-400\">{{ \$item->id }}</td>\n    $cells\n    <td class=\"px-6 py-5 text-right !transition-none\">\n        <div class=\"flex justify-end gap-3 !transition-none\">\n            @can('edit_{$pluralSnake}')\n                <x-a href=\"{{ route('admin.$pk.edit', \$item) }}\" class=\"!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none\">Edit</x-a>\n            @endcan\n            @can('delete_{$pluralSnake}')\n                <div x-data=\"{ confirmation: '' }\" x-cloak class=\"inline-block\">\n                    <x-modal>\n                        <x-slot name=\"trigger\"><button @click=\"on = true\" class=\"text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300\">Delete</button></x-slot>\n                        <x-slot name=\"modalTitle\"><div class=\"text-left dark:text-white\">Delete {{ \$item->{$displayNameField} }}?</div></x-slot>\n                        <x-slot name=\"content\"><div class=\"text-left space-y-2\"><p class=\"text-sm text-gray-500 dark:text-gray-400\">This action cannot be undone.</p><input x-model=\"confirmation\" placeholder=\"Type {{ \$item->{$displayNameField} }} to confirm\" class=\"w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none\"></div></x-slot>\n                        <x-slot name=\"footer\"><x-button variant=\"gray\" @click=\"on = false\">Cancel</x-button><x-button variant=\"red\" x-bind:disabled=\"confirmation !== '{{ \$item->{$displayNameField} }}'\" wire:click=\"\$parent.delete$name('{{ \$item->id }}')\" @click=\"on = false\">Delete</x-button></x-slot>\n                    </x-modal>\n                </div>\n            @endcan\n        </div>\n    </td>\n</tr>";
    }

    /**
     * NOTE: the old \$inModal flag used to HIDE the \"+\" add button whenever a field
     * was rendered inside a QuickCreate modal — that's what blocked adding e.g. a
     * missing Brand while inside the Vehicle quick-create. The \"+\" now always renders
     * for foreignId fields, so modals can nest (Vehicle -> Model field -> Brand modal)
     * as deep as the data actually needs.
     */
    protected function getInputs($fields, $pk, $isCreating = true)
    {
        $inputs = '<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">';
        $inputs .= collect($fields)->map(function ($f) use ($pk, $isCreating) {
            $label = Str::title(str_replace('_', ' ', $f['name']));
            if ($f['type'] === 'foreignId') {
                $rv = Str::plural(Str::camel(str_replace('_id', '', $f['name'])));
                $comp = 'admin.' . Str::kebab(Str::plural($f['relatedModel'])) . '.quick-create';

                return "<div>\n    <div class=\"flex items-end gap-2\">\n        <div class=\"flex-1\"><x-form.dropdown-search name=\"{$f['name']}\" wire:model.live=\"{$f['name']}\" :label=\"__('$pk.$label')\" :data=\"\${$rv}\" /></div>\n        <x-modal>\n            <x-slot name=\"trigger\"><button type=\"button\" @click=\"on = true\" class=\"mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform\"><x-heroicon-o-plus class=\"w-5 h-5\" /></button></x-slot>\n            <x-slot name=\"modalTitle\"><div class=\"dark:text-white px-6 pt-6\">Add New " . $f['relatedModel'] . "</div></x-slot>\n            <x-slot name=\"content\"><livewire:$comp /></x-slot>\n        </x-modal>\n    </div>\n</div>";
            }
            if (Str::contains(strtolower($f['name']), ['file', 'document'])) {
                return "<div><x-form.file-upload name=\"{$f['name']}\" wire:model=\"{$f['name']}\" :label=\"__('$pk.$label')\" id=\"{$f['name']}\" :isEditing=\"!\" . ($isCreating ? 'true' : 'false') . \" /></div>";
            }
            if ($f['type'] === 'text') {
                return "<div class=\"md:col-span-2\"><x-form.textarea name=\"{$f['name']}\" wire:model=\"{$f['name']}\" :label=\"__('$pk.$label')\" class=\"dark:bg-gray-900\" /></div>";
            }
            if ($f['type'] === 'boolean') {
                return "<div><x-form.checkbox name=\"{$f['name']}\" wire:model=\"{$f['name']}\" :label=\"__('$pk.$label')\" /></div>";
            }
            if ($f['type'] === 'enum') {
                $opts = collect($f['options'])->map(fn ($o) => '<option value=\"' . e($o) . '\">' . e($o) . '</option>')->implode('');
                return "<div><label class=\"block mb-1.5 text-[10px] font-bold uppercase tracking-widest\">{{ __('$pk.$label') }}</label><select name=\"{$f['name']}\" wire:model=\"{$f['name']}\" class=\"w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl\"><option value=\"\">--</option>$opts</select></div>";
            }
            $type = ($f['type'] === 'datetime') ? 'datetime-local' : (($f['type'] === 'date') ? 'date' : 'text');
            return "<div><x-form.input name=\"{$f['name']}\" type=\"$type\" wire:model=\"{$f['name']}\" :label=\"__('$pk.$label')\" class=\"dark:bg-gray-900\" /></div>";
        })->implode("\n");
        return $inputs . '</div>';
    }

    protected function generateTranslationFiles($pk, $name, $fields)
    {
        foreach (['en', 'sq'] as $lang) {
            $dir = lang_path($lang);
            File::makeDirectory($dir, 0755, true, true);
            $path = "$dir/$pk.php";

            $existing = [];
            if (File::exists($path)) {
                try {
                    $loaded = include $path;
                    $existing = is_array($loaded) ? $loaded : [];
                } catch (\Throwable $e) {
                    $this->warn("Could not read existing $path ({$e->getMessage()}) — treating as empty, nothing will be lost since we still merge below.");
                }
            }

            $defaults = [
                'ID' => 'ID', $name => $name, Str::plural($name) => Str::plural($name), 'Action' => 'Action',
                'Reset' => 'Reset', 'Filters' => 'Filters', 'Search' => 'Search', 'List of' => 'List of',
                'Save' => 'Save', 'Update' => 'Update', 'Add ' . $name => 'Add ' . $name, 'Edit ' . $name => 'Edit ' . $name,
                'New record' => 'New record', 'Update info' => 'Update info', 'No records found.' => 'No records found.',
                'created' => $name . ' created.', 'updated' => $name . ' updated.', 'deleted' => $name . ' deleted.',
                'not_found' => 'Record not found.',
                'delete_error_referenced' => 'Record is referenced by other items and cannot be deleted.',
                'delete_error' => 'Could not delete record.',
            ];
            foreach ($fields as $f) {
                $l = Str::title(str_replace('_', ' ', $f['name']));
                $defaults[$l] = $l;
            }

            // Existing values win for any key already translated. Only genuinely
            // new keys (new fields, new UI strings) get the generated default.
            $merged = array_merge($defaults, $existing);

            if ($existing !== [] && $merged === $existing) {
                $this->line("$path already has every key — left untouched.");
                continue;
            }
            if ($existing !== []) {
                $this->line("$path merged — existing translations preserved, new keys added.");
            }

            $content = "<?php\n\nreturn " . var_export($merged, true) . ";\n";
            $content = str_replace(['array (', ')'], ['[', ']'], $content);
            File::put($path, $content);
        }
    }

    protected function generateMigration($tableName, $fields)
    {
        $schema = collect($fields)->map(function ($f) {
            if ($f['type'] === 'enum') {
                $opts = "['" . implode("', '", array_map('addslashes', $f['options'])) . "']";
                $line = "\$table->enum('{$f['name']}', $opts)";
            } elseif ($f['type'] === 'foreignId') {
                $line = "\$table->foreignId('{$f['name']}')->constrained('{$f['constrained']}')";
            } else {
                $line = "\$table->{$f['type']}('{$f['name']}')";
            }
            if ($f['nullable']) {
                $line .= '->nullable()';
            }
            return "            $line;";
        })->implode("\n");
        File::put(
            'database/migrations/' . date('Y_m_d_His') . "_create_{$tableName}_table.php",
            "<?php\nuse Illuminate\Database\Migrations\Migration;\nuse Illuminate\Database\Schema\Blueprint;\nuse Illuminate\Support\Facades\Schema;\nreturn new class extends Migration { public function up() { Schema::create('$tableName', function (Blueprint \$table) { \$table->id();\n$schema\n            \$table->timestamps(); }); } public function down() { Schema::dropIfExists('$tableName'); } };"
        );
    }

    protected function generateRouteFile($pluralName, $pluralKebab, $name, $pluralSnake)
    {
        $camel = Str::camel($name);
        File::put(base_path("routes/admin/$pluralKebab.php"), "<?php\nuse Illuminate\Support\Facades\Route;\nuse App\Livewire\Admin\\$pluralName\\$pluralName;\nuse App\Livewire\Admin\\$pluralName\\Create;\nuse App\Livewire\Admin\\$pluralName\\Edit;\nRoute::prefix('$pluralKebab')->group(function () {\n    Route::get('/', $pluralName::class)->name('admin.$pluralKebab.index');\n    Route::get('create', Create::class)->name('admin.$pluralKebab.create');\n    Route::get('/{' . '$camel' . '}/edit', Edit::class)->name('admin.$pluralKebab.edit');\n});");
    }

    protected function addPermissions($name, $pluralSnake)
    {
        foreach (['view', 'add', 'edit', 'delete'] as $act) {
            Permission::firstOrCreate(['name' => "{$act}_{$pluralSnake}"], ['label' => ucfirst($act) . ' ' . $name, 'module' => Str::plural($name)]);
        }
    }

    protected function addNavigation($pluralName, $pluralKebab, $pluralSnake, $icon)
    {
        $navPath = resource_path('views/components/layouts/app/navigation.blade.php');
        if (!File::exists($navPath)) {
            $this->warn("Navigation file not found at $navPath — skipped.");
            return;
        }
        $content = File::get($navPath);
        if (str_contains($content, "admin.{$pluralKebab}.index")) {
            $this->line("Nav link for $pluralKebab already exists — skipped.");
            return;
        }
        $newLink = "\n@can('view_{$pluralSnake}')\n    <x-nav.link route=\"admin.{$pluralKebab}.index\" icon=\"$icon\">{{ __('$pluralKebab.$pluralName') }}</x-nav.link>\n@endcan\n";
        $search = "<x-nav.divider>{{ __('admin.Account') }}</x-nav.divider>";
        if (str_contains($content, $search)) {
            File::put($navPath, str_replace($search, $newLink . $search, $content));
        } else {
            File::append($navPath, $newLink);
            $this->warn('Divider marker not found in navigation.blade.php — appended link at the end, please review placement.');
        }
    }

    protected function generateApiLayer($name, $pluralName, $pluralKebab, $fields)
    {
        $dir = app_path('Http/Controllers/Api');
        File::makeDirectory($dir, 0755, true, true);
        File::put("$dir/{$name}Controller.php", "<?php\nnamespace App\Http\Controllers\Api;\nuse App\Http\Controllers\Controller;\nuse App\Models\\$name;\nclass {$name}Controller extends Controller { public function index() { return $name::paginate(); } }");
        $apiRoutePath = base_path('routes/api.php');
        $existing = File::exists($apiRoutePath) ? File::get($apiRoutePath) : '';
        if (!str_contains($existing, "apiResource('$pluralKebab'")) {
            File::append($apiRoutePath, "\nRoute::apiResource('$pluralKebab', \App\Http\Controllers\Api\\{$name}Controller::class);");
        } else {
            $this->line("API route for $pluralKebab already registered — skipped.");
        }
    }
}
