<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

class MakeMobileProCommand extends Command
{
    protected $signature = 'make:mobile-pro
        {name : Emri i Modelit (psh. Service)}
        {--force : Mbishkruaj skedarët ekzistues}';

    protected $description = 'Gjeneron një modul Mobile "Premium Pro" (Laravel API + Flutter UI)';

    private string $className;
    private string $snakeName;
    private string $pluralKebab;
    private array $meta = [];

    public function handle(): int
    {
        $this->className = Str::studly($this->argument('name'));
        $this->snakeName = Str::snake($this->className);
        $this->pluralKebab = Str::kebab(Str::plural($this->className));

        $this->info("🚀 Duke përpunuar modulin PRO: {$this->className}");

        if (!$this->resolveMeta()) return self::FAILURE;

        try {
            $this->generateController();
            $this->registerRoutes();
            $this->generateDartModel();
            $this->generateFlutterListPage();
            $this->generateFlutterFormPage();

            // Pastrojmë cache-in e rrugëve që rregullimet e reja të njihen menjëherë
            $this->callSilently('route:clear');

            $this->newLine();
            $this->info("✅ Çdo gjë u përditësua me sukses për {$this->className}!");
        } catch (Throwable $e) {
            $this->error("❌ Gabim gjatë ekzekutimit: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function resolveMeta(): bool
    {
        $modelClass = "App\\Models\\BerberApp\\{$this->className}";
        if (!class_exists($modelClass)) $modelClass = "App\\Models\\{$this->className}";
        if (!class_exists($modelClass)) { $this->error("Modeli {$this->className} nuk ekziston."); return false; }

        $model = new $modelClass();
        $this->meta = [
            'class' => $this->className,
            'model_fqn' => $modelClass,
            'fields' => array_values(array_filter($model->getFillable(), fn($f) => !in_array($f, ['id', 'created_at', 'updated_at', 'deleted_at']))),
            'json_fields' => array_keys(array_filter($model->getCasts(), fn($c) => in_array($c, ['array', 'json', 'object', 'collection']))),
            'relations' => $this->discoverRelations($modelClass),
        ];
        return true;
    }

    private function discoverRelations(string $modelClass): array
    {
        $relations = [];
        $methods = (new ReflectionClass($modelClass))->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($method->class !== $modelClass || $method->getNumberOfParameters() > 0) continue;
            try {
                $return = $method->invoke(new $modelClass());
                if ($return instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                    $relations[$return->getForeignKeyName()] = [
                        'method' => $method->name,
                        'endpoint' => Str::plural(Str::kebab($method->name)),
                    ];
                }
            } catch (Throwable $e) {}
        }
        return $relations;
    }

    private function generateController()
    {
        $path = app_path("Http/Controllers/Api/Mobile/{$this->className}Controller.php");
        File::ensureDirectoryExists(dirname($path));

        $relWith = !empty($this->meta['relations']) ? "->with(" . var_export(collect($this->meta['relations'])->pluck('method')->toArray(), true) . ")" : "";
        $jsonFields = var_export($this->meta['json_fields'], true);

        $stub = <<<PHP
<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use {$this->meta['model_fqn']};
use Illuminate\Http\Request;

class {$this->className}Controller extends Controller
{
    public function index()
    {
        \$items = {$this->className}::query(){$relWith}->latest()->paginate(50);
        \$items->getCollection()->transform(fn(\$i) => \$this->transformItem(\$i));
        return response()->json(\$items);
    }

    public function store(Request \$request)
    {
        \$data = \$this->prepareData(\$request);
        \$rules = method_exists({$this->className}::class, 'rules') ? {$this->className}::rules() : [];
        \$validated = validator(\$data, \$rules ?: collect((new {$this->className})->getFillable())->mapWithKeys(fn(\$f)=>[\$f=>'required'])->toArray())->validate();

        if (\$request->hasFile('photo')) {
            \$file = \$request->file('photo');
            \$name = time() . '_' . \$file->getClientOriginalName();
            \$file->move(public_path('uploads'), \$name);
            \$validated['photo'] = 'uploads/' . \$name;
        }

        \$item = {$this->className}::create(\$validated);
        return response()->json(['success' => true, 'data' => \$this->transformItem(\$item)]);
    }

    public function update(Request \$request, \$id)
    {
        \$item = {$this->className}::findOrFail(\$id);
        \$data = \$this->prepareData(\$request);
        \$rules = method_exists({$this->className}::class, 'rules') ? {$this->className}::rules(\$id) : [];
        \$validated = validator(\$data, \$rules ?: collect((new {$this->className})->getFillable())->mapWithKeys(fn(\$f)=>[\$f=>'required'])->toArray())->validate();

        if (\$request->hasFile('photo')) {
            if (\$item->photo && file_exists(public_path(\$item->photo))) @unlink(public_path(\$item->photo));
            \$file = \$request->file('photo');
            \$name = time() . '_' . \$file->getClientOriginalName();
            \$file->move(public_path('uploads'), \$name);
            \$validated['photo'] = 'uploads/' . \$name;
        }

        \$item->update(\$validated);
        return response()->json(['success' => true, 'data' => \$this->transformItem(\$item)]);
    }

    public function destroy(\$id)
    {
        \$item = {$this->className}::findOrFail(\$id);
        if (\$item->photo && file_exists(public_path(\$item->photo))) @unlink(public_path(\$item->photo));
        \$item->delete();
        return response()->json(['success' => true]);
    }

    private function transformItem(\$item) {
        foreach ({$jsonFields} as \$f) {
            \$val = \$item->getRawOriginal(\$f);
            \$item->setAttribute(\"{\$f}_raw\", is_string(\$val) && str_starts_with(\$val, '{') ? json_decode(\$val, true) : \$val);
        }
        return \$item;
    }

    private function prepareData(Request \$request) {
        \$data = \$request->all();
        foreach ({$jsonFields} as \$f) {
            if (isset(\$data[\$f]) && is_string(\$data[\$f]) && str_starts_with(\$data[\$f], '{')) \$data[\$f] = json_decode(\$data[\$f], true);
        }
        return \$data;
    }
}
PHP;
        File::put($path, $stub);
        $this->line("  ✓ Controller: <info>Http/Controllers/Api/Mobile/{$this->className}Controller.php</info> (Updated)");
    }

    private function registerRoutes() {
        $path = base_path('routes/api.php');
        $content = File::get($path);
        $route = "    Route::apiResource('{$this->pluralKebab}', \\App\\Http\\Controllers\\Api\\Mobile\\{$this->className}Controller::class);";

        if (!Str::contains($content, "apiResource('{$this->pluralKebab}'")) {
            $marker = "Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {";
            $content = str_replace($marker, $marker . "\n" . $route, $content);
            File::put($path, $content);
            $this->line("  ✓ Route: <info>api/mobile/{$this->pluralKebab}</info> (Added)");
        } else {
            $this->line("  → Route: <info>api/mobile/{$this->pluralKebab}</info> (Exists)");
        }
    }

    private function generateDartModel() {
        $path = base_path("mobile-gateway/lib/models/{$this->snakeName}.dart");
        File::ensureDirectoryExists(dirname($path));
        $fields = ""; $fromJson = ""; $toJson = "";
        foreach ($this->meta['fields'] as $f) {
            $type = "dynamic";
            if (in_array($f, $this->meta['json_fields'])) $type = "Map<String, dynamic>";
            elseif (Str::endsWith($f, '_id')) $type = "int";
            $camel = Str::camel($f);
            $fields .= "  final $type? $camel;\n";
            $fromJson .= "      $camel: json['" . ($type == "Map<String, dynamic>" ? "{$f}_raw" : $f) . "'],\n";
            $toJson .= "      '$f': $camel,\n";
        }
        $stub = "class {$this->className} {\n  final int? id;\n$fields\n  {$this->className}({this.id, " . collect($this->meta['fields'])->map(fn($f) => "this." . Str::camel($f))->implode(', ') . "});\n\n  factory {$this->className}.fromJson(Map<String, dynamic> json) => {$this->className}(\n      id: json['id'],\n$fromJson  );\n\n  Map<String, dynamic> toJson() => {\n      'id': id,\n$toJson  };\n}";
        File::put($path, $stub);
        $this->line("  ✓ Dart Model: <info>lib/models/{$this->snakeName}.dart</info> (Updated)");
    }

    private function generateFlutterListPage() {
        $path = base_path("mobile-gateway/lib/modules/dashboard/{$this->snakeName}_list_page.dart");
        $titleLogic = "item['name'] is Map ? (item['name']['sq'] ?? item['name']['en'] ?? 'N/A') : (item['name'] ?? item['customer_name'] ?? 'ID: \${item['id']}')";

        $stub = <<<DART
import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import 'dart:convert';
import '{$this->snakeName}_form_screen.dart';

class {$this->className}ListPage extends StatefulWidget {
  const {$this->className}ListPage({super.key});
  @override State<{$this->className}ListPage> createState() => _{$this->className}ListPageState();
}

class _{$this->className}ListPageState extends State<{$this->className}ListPage> {
  List<dynamic> _items = []; bool _loading = true;

  @override void initState() { super.initState(); _fetch(); }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    final res = await ApiService.get('/{$this->pluralKebab}');
    if (res.statusCode == 200) { setState(() => _items = jsonDecode(res.body)['data']); }
    setState(() => _loading = false);
  }

  @override Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('{$this->className} Management', style: TextStyle(fontWeight: FontWeight.w900))),
    floatingActionButton: FloatingActionButton(backgroundColor: Colors.black, onPressed: () async { final res = await Navigator.push(context, MaterialPageRoute(builder: (c) => const {$this->className}FormScreen())); if (res == true) _fetch(); }, child: const Icon(Icons.add, color: Colors.white)),
    body: _loading ? const Center(child: CircularProgressIndicator(color: Colors.black)) : RefreshIndicator(
      onRefresh: _fetch,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _items.length,
        itemBuilder: (context, index) {
          final item = _items[index];
          return Card(
            elevation: 0, margin: const EdgeInsets.only(bottom: 12),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20), side: BorderSide(color: Colors.grey.shade200)),
            child: ListTile(
              contentPadding: const EdgeInsets.all(16),
              leading: CircleAvatar(backgroundColor: Colors.black, child: const Icon(Icons.folder, color: Colors.white)),
              title: Text($titleLogic, style: const TextStyle(fontWeight: FontWeight.bold)),
              subtitle: Text('ID: \${item['id']} | \${item['updated_at'] ?? ""}'),
              trailing: const Icon(Icons.edit_outlined, size: 20),
              onTap: () async { final res = await Navigator.push(context, MaterialPageRoute(builder: (c) => {$this->className}FormScreen(item: item))); if (res == true) _fetch(); },
            ),
          );
        },
      ),
    ),
  );
}
DART;
        File::put($path, $stub);
        $this->line("  ✓ List Page: <info>dashboard/{$this->snakeName}_list_page.dart</info> (Updated)");
    }

    private function generateFlutterFormPage() {
        $path = base_path("mobile-gateway/lib/modules/dashboard/{$this->snakeName}_form_screen.dart");
        $vars = ""; $init = ""; $widgets = ""; $payload = ""; $loaders = ""; $hasImage = false;

        foreach ($this->meta['fields'] as $f) {
            $label = Str::headline($f);
            if (Str::contains($f, ['photo', 'image'])) {
                $hasImage = true; $vars .= "  String? _imagePath;\n";
                $widgets .= "            const Text('$label', style: TextStyle(fontWeight: FontWeight.bold)), const SizedBox(height: 8),
            GestureDetector(
              onTap: () async { final p = await ImagePicker().pickImage(source: ImageSource.gallery); if(p != null) setState(()=>_imagePath = p.path); },
              child: Container(height: 150, width: double.infinity, decoration: BoxDecoration(color: Colors.grey[100], borderRadius: BorderRadius.circular(15), border: Border.all(color: Colors.grey.shade300)), child: _imagePath != null ? Image.file(File(_imagePath!), fit: BoxFit.cover) : (widget.item?['$f'] != null ? Image.network('\${ApiService.serverUrl}/\${widget.item!['$f']}', fit: BoxFit.cover) : const Icon(Icons.add_a_photo))),
            ), const SizedBox(height: 20),\n";
            } elseif (isset($this->meta['relations'][$f])) {
                $rel = $this->meta['relations'][$f]; $safe = Str::studly($f);
                $vars .= "  List<dynamic> _{$rel['method']}Options = []; dynamic _selected$safe;\n";
                $init .= "    _selected$safe = widget.item?['$f'];\n";
                $loaders .= "      final r$safe = await ApiService.get('/{$rel['endpoint']}'); if(r$safe.statusCode==200) setState(()=>_{$rel['method']}Options = jsonDecode(r$safe.body)['data']);\n";
                $widgets .= "            DropdownButtonFormField(value: _selected$safe, decoration: const InputDecoration(labelText: '$label', border: OutlineInputBorder()), items: _{$rel['method']}Options.map((e)=>DropdownMenuItem(value: e['id'], child: Text(e['name']?.toString() ?? 'ID: \${e['id']}'))).toList(), onChanged: (v)=>setState(()=>_selected$safe=v)), const SizedBox(height: 20),\n";
                $payload .= "    payload['$f'] = _selected$safe;\n";
            } elseif (in_array($f, $this->meta['json_fields'])) {
                $vars .= "  final _{$f}Sq = TextEditingController(); final _{$f}En = TextEditingController();\n";
                $init .= "    final {$f}D = widget.item?['{$f}_raw']; if({$f}D != null) { _{$f}Sq.text = {$f}D['sq'] ?? ''; _{$f}En.text = {$f}D['en'] ?? ''; }\n";
                $widgets .= "            TextFormField(controller: _{$f}Sq, decoration: const InputDecoration(labelText: '$label (AL)', border: OutlineInputBorder())), const SizedBox(height: 10),\n";
                $widgets .= "            TextFormField(controller: _{$f}En, decoration: const InputDecoration(labelText: '$label (EN)', border: OutlineInputBorder())), const SizedBox(height: 20),\n";
                $payload .= "    payload['$f'] = {'sq': _{$f}Sq.text, 'en': _{$f}En.text};\n";
            } else {
                $vars .= "  final _{$f}C = TextEditingController();\n";
                $init .= "    _{$f}C.text = widget.item?['$f']?.toString() ?? '';\n";
                $widgets .= "            TextFormField(controller: _{$f}C, decoration: const InputDecoration(labelText: '$label', border: OutlineInputBorder())), const SizedBox(height: 20),\n";
                $payload .= "    payload['$f'] = _{$f}C.text;\n";
            }
        }

        $saveCall = $hasImage
            ? "await ApiService.postMultipart(widget.item == null ? '/{$this->pluralKebab}' : '/{$this->pluralKebab}/\${widget.item!['id']}', payload, filePath: _imagePath, fieldName: 'photo')"
            : "widget.item == null ? await ApiService.post('/{$this->pluralKebab}', payload) : await ApiService.put('/{$this->pluralKebab}/\${widget.item!['id']}', payload)";

        $stub = <<<DART
import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import 'dart:convert';
import 'dart:io';
import 'package:image_picker/image_picker.dart';

class {$this->className}FormScreen extends StatefulWidget {
  final Map<String, dynamic>? item;
  const {$this->className}FormScreen({super.key, this.item});
  @override State<{$this->className}FormScreen> createState() => _{$this->className}FormState();
}

class _{$this->className}FormState extends State<{$this->className}FormScreen> {
  final _formKey = GlobalKey<FormState>(); bool _isSaving = false; bool _isLoading = true;
$vars
  @override void initState() { super.initState(); $init _loadData(); }
  Future<void> _loadData() async { try { $loaders } catch(_) {} setState(()=>_isLoading=false); }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isSaving = true);
    final payload = <String, dynamic>{}; $payload
    try {
      final res = $saveCall;
      if (res.statusCode >= 200 && res.statusCode < 300) { Navigator.pop(context, true); }
      else { ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ApiService.extractErrorMessage(res)))); }
    } catch (e) { ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: \$e'))); }
    setState(() => _isSaving = false);
  }

  @override Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: Text(widget.item == null ? 'Shto {$this->className}' : 'Edito {$this->className}')),
    body: _isLoading ? const Center(child: CircularProgressIndicator()) : SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Form(key: _formKey, child: Column(children: [ $widgets const SizedBox(height: 30),
            SizedBox(width: double.infinity, height: 55, child: ElevatedButton(style: ElevatedButton.styleFrom(backgroundColor: Colors.black, foregroundColor: Colors.white, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15))), onPressed: _isSaving ? null : _save, child: _isSaving ? const CircularProgressIndicator(color: Colors.white) : const Text('RUAJ', style: TextStyle(fontWeight: FontWeight.bold))))
      ])),
    ),
  );
}
DART;
        File::put($path, $stub);
        $this->line("  ✓ Form Page: <info>dashboard/{$this->snakeName}_form_screen.dart</info> (Updated)");
    }
}
