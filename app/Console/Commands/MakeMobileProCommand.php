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
        {name : Emri i Modelit}
        {--force : Mbishkruaj skedarët}';

    protected $description = 'Gjeneron modulin Mobile me UI Kompakte dhe Mbështetje Universale Modelësh';

    private string $className;
    private string $snakeName;
    private string $pluralSnake;
    private string $pluralKebab;
    private array $meta = [];

    public function handle(): int
    {
        $this->className = Str::studly($this->argument('name'));
        $this->snakeName = Str::snake($this->className);
        $this->pluralSnake = Str::plural($this->snakeName);
        $this->pluralKebab = Str::kebab(Str::plural($this->className));

        $this->info("🚀 Duke përpunuar modulin PREMIUM COMPACT: {$this->className}");

        if (!$this->resolveMeta()) return self::FAILURE;

        try {
            $this->generateController();
            $this->registerRoutes();
            $this->generateDartModel();
            $this->generateFlutterListPage();
            $this->generateFlutterFormPage();

            $this->callSilently('route:clear');
            $this->info("✅ Moduli {$this->className} u përfundua me UI Kompakte!");
        } catch (Throwable $e) {
            $this->error("❌ Gabim: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function resolveMeta(): bool
    {
        $candidates = [
            "App\\Models\\BerberApp\\{$this->className}",
            "App\\Models\\{$this->className}",
            "App\\{$this->className}"
        ];

        $modelClass = null;
        foreach ($candidates as $candidate) {
            if (class_exists($candidate)) {
                $modelClass = $candidate;
                break;
            }
        }

        if (!$modelClass) {
            $this->error("Modeli {$this->className} nuk u gjet në asnjë path të mundshëm.");
            return false;
        }

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
        $permPrefix = $this->pluralSnake;

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
        abort_if_cannot('view_{$permPrefix}');
        \$items = {$this->className}::query(){$relWith}->latest()->paginate(50);
        \$items->getCollection()->transform(fn(\$i) => \$this->transformItem(\$i));
        return response()->json(\$items);
    }

    public function store(Request \$request)
    {
        abort_if_cannot('add_{$permPrefix}');
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
        abort_if_cannot('edit_{$permPrefix}');
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
        abort_if_cannot('delete_{$permPrefix}');
        try {
            \$item = {$this->className}::findOrFail(\$id);
            if (\$item->photo && file_exists(public_path(\$item->photo))) @unlink(public_path(\$item->photo));
            \$item->delete();
            return response()->json(['success' => true]);
        } catch (\Throwable \$e) {
            return response()->json(['success' => false, 'message' => 'Ky rekord është i lidhur me të dhëna të tjera.'], 400);
        }
    }

    private function transformItem(\$item) {
        foreach ({$jsonFields} as \$f) {
            \$val = \$item->getRawOriginal(\$f);
            \$item->setAttribute("{\$f}_raw", is_string(\$val) && str_starts_with(\$val, '{') ? json_decode(\$val, true) : \$val);
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
    }

    private function registerRoutes() {
        $path = base_path('routes/api.php');
        $content = File::get($path);
        $route = "    Route::apiResource('{$this->pluralKebab}', \\App\\Http\\Controllers\\Api\\Mobile\\{$this->className}Controller::class);";
        if (!Str::contains($content, "apiResource('{$this->pluralKebab}'")) {
            $marker = "Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {";
            $content = str_replace($marker, $marker . "\n" . $route, $content);
            File::put($path, $content);
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
    }

    private function generateFlutterListPage() {
        $path = base_path("mobile-gateway/lib/modules/dashboard/{$this->snakeName}_list_page.dart");
        $nameLogic = "item['name'] is Map ? (item['name']['sq'] ?? item['name']['en'] ?? 'N/A') : (item['name'] ?? item['customer_name'] ?? item['title'] ?? 'ID: \${item['id']}')";

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
    backgroundColor: Colors.white,
    appBar: AppBar(elevation: 0, backgroundColor: Colors.white, title: const Text('{$this->className}', style: TextStyle(color: Colors.black, fontWeight: FontWeight.w900, fontSize: 20)), iconTheme: const IconThemeData(color: Colors.black)),
    floatingActionButton: FloatingActionButton(backgroundColor: Colors.black, mini: true, onPressed: () async { final res = await Navigator.push(context, MaterialPageRoute(builder: (c) => const {$this->className}FormScreen())); if (res == true) _fetch(); }, child: const Icon(Icons.add, color: Colors.white)),
    body: _loading ? const Center(child: CircularProgressIndicator(color: Colors.black)) : RefreshIndicator(
      onRefresh: _fetch,
      child: ListView.builder(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        itemCount: _items.length,
        itemBuilder: (context, index) {
          final item = _items[index];
          String name = $nameLogic;
          String? photo = item['photo'];

          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.shade100), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 5, offset: const Offset(0, 2))]),
            child: ListTile(
              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              leading: Container(
                width: 45, height: 45,
                decoration: BoxDecoration(color: Colors.grey[50], borderRadius: BorderRadius.circular(12), image: photo != null ? DecorationImage(image: NetworkImage('\${ApiService.serverUrl}/\$photo'), fit: BoxFit.cover) : null),
                child: photo == null ? Center(child: Text(name.isNotEmpty ? name[0].toUpperCase() : '?', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.black54))) : null,
              ),
              title: Text(name, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
              subtitle: Text('ID: \${item['id']}', style: const TextStyle(fontSize: 11, color: Colors.grey)),
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
    }

    private function generateFlutterFormPage() {
        $path = base_path("mobile-gateway/lib/modules/dashboard/{$this->snakeName}_form_screen.dart");
        $vars = ""; $init = ""; $widgets = ""; $payload = ""; $loaders = ""; $hasImage = false;

        foreach ($this->meta['fields'] as $f) {
            $label = Str::headline($f);
            if (Str::contains($f, ['photo', 'image'])) {
                $hasImage = true; $vars .= "  String? _imagePath;\n";
                $widgets .= "            _buildSectionTitle('$label'), const SizedBox(height: 8),
            GestureDetector(
              onTap: () async { final p = await ImagePicker().pickImage(source: ImageSource.gallery); if(p != null) setState(()=>_imagePath = p.path); },
              child: Container(height: 140, width: double.infinity, decoration: BoxDecoration(color: Colors.grey[50], borderRadius: BorderRadius.circular(20), border: Border.all(color: Colors.grey.shade200)), child: _imagePath != null ? ClipRRect(borderRadius: BorderRadius.circular(20), child: Image.file(File(_imagePath!), fit: BoxFit.cover)) : (widget.item?['$f'] != null ? ClipRRect(borderRadius: BorderRadius.circular(20), child: Image.network('\${ApiService.serverUrl}/\${widget.item!['$f']}', fit: BoxFit.cover)) : const Icon(Icons.add_a_photo_outlined, color: Colors.grey))),
            ), const SizedBox(height: 16),\n";
            } elseif (isset($this->meta['relations'][$f])) {
                $rel = $this->meta['relations'][$f]; $safe = Str::studly($f);
                $vars .= "  List<dynamic> _{$rel['method']}Options = []; dynamic _selected$safe; String _selected{$safe}Label = 'Zgjidh...';\n";
                $init .= "    _selected$safe = widget.item?['$f'];\n";
                $loaders .= "      final r$safe = await ApiService.get('/{$rel['endpoint']}'); if(r$safe.statusCode==200) { setState(() { _{$rel['method']}Options = jsonDecode(r$safe.body)['data']; if(_selected$safe != null) { try { var found = _{$rel['method']}Options.firstWhere((e) => e['id'] == _selected$safe); _selected{$safe}Label = found['name'] is Map ? (found['name']['sq'] ?? found['name']['en']) : (found['name'] ?? found['customer_name'] ?? 'ID: \${found['id']}'); } catch(_) {} } }); }\n";
                $widgets .= "            _buildSectionTitle('$label'), const SizedBox(height: 8),
            InkWell(
              onTap: () => _showSearchablePicker(context, '$label', _{$rel['method']}Options, (val) {
                setState(() { _selected$safe = val['id']; _selected{$safe}Label = val['name'] is Map ? (val['name']['sq'] ?? val['name']['en']) : (val['name'] ?? val['customer_name'] ?? 'ID: \${val['id']}'); });
              }),
              child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(color: Colors.grey[50], borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.shade200)), child: Row(children: [const Icon(Icons.search, size: 18, color: Colors.grey), const SizedBox(width: 12), Expanded(child: Text(_selected{$safe}Label, style: const TextStyle(fontSize: 14))), const Icon(Icons.arrow_drop_down)])),
            ), const SizedBox(height: 16),\n";
                $payload .= "    payload['$f'] = _selected$safe;\n";
            } elseif (Str::contains($f, ['_at', 'date', 'time'])) {
                $vars .= "  final _{$f}C = TextEditingController();\n";
                $init .= "    _{$f}C.text = widget.item?['$f']?.toString() ?? '';\n";
                $widgets .= "            _buildDateTimePicker(_{$f}C, '$label'), const SizedBox(height: 16),\n";
                $payload .= "    payload['$f'] = _{$f}C.text;\n";
            } elseif (in_array($f, $this->meta['json_fields'])) {
                $vars .= "  final _{$f}Sq = TextEditingController(); final _{$f}En = TextEditingController();\n";
                $init .= "    final {$f}D = widget.item?['{$f}_raw']; if({$f}D != null) { _{$f}Sq.text = {$f}D['sq'] ?? ''; _{$f}En.text = {$f}D['en'] ?? ''; }\n";
                $widgets .= "            _buildTextField(_{$f}Sq, '$label (AL)', Icons.language), const SizedBox(height: 12),\n";
                $widgets .= "            _buildTextField(_{$f}En, '$label (EN)', Icons.translate), const SizedBox(height: 16),\n";
                $payload .= "    payload['$f'] = {'sq': _{$f}Sq.text, 'en': _{$f}En.text};\n";
            } else {
                $vars .= "  final _{$f}C = TextEditingController();\n";
                $init .= "    _{$f}C.text = widget.item?['$f']?.toString() ?? '';\n";
                $widgets .= "            _buildTextField(_{$f}C, '$label', Icons.edit_note_outlined), const SizedBox(height: 16),\n";
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
import 'package:intl/intl.dart';

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

  void _showSearchablePicker(BuildContext context, String title, List<dynamic> options, Function(dynamic) onSelect) {
    showModalBottomSheet(context: context, isScrollControlled: true, shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))), builder: (context) {
        List<dynamic> filtered = List.from(options);
        return StatefulBuilder(builder: (context, setModalState) {
          return Container(height: MediaQuery.of(context).size.height * 0.7, padding: const EdgeInsets.all(24), child: Column(children: [
              Text('Zgjidh \$title', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 15),
              TextField(decoration: InputDecoration(hintText: 'Kërko...', prefixIcon: const Icon(Icons.search, size: 20), filled: true, fillColor: Colors.grey[100], border: OutlineInputBorder(borderRadius: BorderRadius.circular(15), borderSide: BorderSide.none)), onChanged: (q) { setModalState(() { filtered = options.where((e) { String name = e['name'] is Map ? (e['name']['sq'] ?? e['name']['en'] ?? '') : (e['name'] ?? e['customer_name'] ?? ''); return name.toLowerCase().contains(q.toLowerCase()); }).toList(); }); }),
              const SizedBox(height: 15),
              Expanded(child: ListView.builder(itemCount: filtered.length, itemBuilder: (c, i) {
                  var item = filtered[i];
                  String name = item['name'] is Map ? (item['name']['sq'] ?? item['name']['en'] ?? '') : (item['name'] ?? item['customer_name'] ?? 'ID: \${item['id']}');
                  return ListTile(title: Text(name, style: const TextStyle(fontSize: 14)), leading: const Icon(Icons.check_circle_outline, size: 20), onTap: () { onSelect(item); Navigator.pop(context); });
              }))
          ]));
        });
    });
  }

  Widget _buildDateTimePicker(TextEditingController controller, String label) {
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      _buildSectionTitle(label), const SizedBox(height: 6),
      InkWell(onTap: () async {
          DateTime? pDate = await showDatePicker(context: context, initialDate: DateTime.now(), firstDate: DateTime(2000), lastDate: DateTime(2100));
          if (pDate != null) {
            TimeOfDay? pTime = await showTimePicker(context: context, initialTime: TimeOfDay.now());
            if (pTime != null) {
              final dt = DateTime(pDate.year, pDate.month, pDate.day, pTime.hour, pTime.minute);
              setState(() => controller.text = dt.toIso8601String());
            }
          }
        },
        child: Container(padding: const EdgeInsets.all(14), decoration: BoxDecoration(color: Colors.grey[50], borderRadius: BorderRadius.circular(14), border: Border.all(color: Colors.grey.shade200)), child: Row(children: [const Icon(Icons.calendar_month_outlined, size: 18, color: Colors.black54), const SizedBox(width: 10), Expanded(child: Text(controller.text.isEmpty ? 'Zgjidh datën...' : DateFormat('dd/MM/yyyy HH:mm').format(DateTime.parse(controller.text)), style: const TextStyle(fontSize: 14))), const Icon(Icons.edit_calendar_outlined, size: 18, color: Colors.grey)])),
      )
    ]);
  }

  Widget _buildTextField(TextEditingController controller, String label, IconData icon) {
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      _buildSectionTitle(label), const SizedBox(height: 6),
      TextFormField(controller: controller, style: const TextStyle(fontSize: 14), decoration: InputDecoration(prefixIcon: Icon(icon, size: 18, color: Colors.black54), filled: true, fillColor: Colors.grey[50], border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide.none), enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide(color: Colors.grey.shade200)), contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14))),
    ]);
  }

  Widget _buildSectionTitle(String title) { return Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black54)); }

  Future<void> _delete() async {
    final confirm = await showDialog<bool>(context: context, builder: (c) => AlertDialog(title: const Text('Fshi?'), content: const Text('A jeni të sigurt?'), actions: [TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('JO')), TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('PO', style: TextStyle(color: Colors.red)))]));
    if (confirm == true) {
      setState(() => _isSaving = true);
      try {
        final res = await ApiService.delete('/{$this->pluralKebab}/\${widget.item!['id']}');
        if (res.statusCode == 200) { ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('U fshi! ✅'), backgroundColor: Colors.green)); Navigator.pop(context, true); }
        else { ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ApiService.extractErrorMessage(res)), backgroundColor: Colors.red)); }
      } catch (e) { ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gabim: \$e'))); }
      setState(() => _isSaving = false);
    }
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isSaving = true);
    final payload = <String, dynamic>{}; $payload
    try {
      final res = $saveCall;
      if (res.statusCode >= 200 && res.statusCode < 300) { ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('U ruajt! ✅'), backgroundColor: Colors.green)); Navigator.pop(context, true); }
      else { ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ApiService.extractErrorMessage(res)))); }
    } catch (e) { ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: \$e'))); }
    setState(() => _isSaving = false);
  }

  @override Widget build(BuildContext context) => Scaffold(
    backgroundColor: Colors.white,
    appBar: AppBar(elevation: 0, backgroundColor: Colors.white, title: Text(widget.item == null ? 'Shtim i ri' : 'Edito', style: const TextStyle(color: Colors.black, fontWeight: FontWeight.w900, fontSize: 18)), iconTheme: const IconThemeData(color: Colors.black)),
    body: _isLoading ? const Center(child: CircularProgressIndicator(color: Colors.black)) : SingleChildScrollView(padding: const EdgeInsets.all(20), child: Form(key: _formKey, child: Column(children: [ $widgets const SizedBox(height: 20),
            SizedBox(width: double.infinity, height: 55, child: ElevatedButton(style: ElevatedButton.styleFrom(backgroundColor: Colors.black, foregroundColor: Colors.white, elevation: 0, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16))), onPressed: _isSaving ? null : _save, child: _isSaving ? const CircularProgressIndicator(color: Colors.white) : const Text('RUAJ', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15)))),
            if(widget.item != null) ...[ const SizedBox(height: 12), SizedBox(width: double.infinity, height: 55, child: OutlinedButton(style: OutlinedButton.styleFrom(side: const BorderSide(color: Colors.redAccent), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16))), onPressed: _isSaving ? null : _delete, child: const Text('FSHI', style: TextStyle(color: Colors.redAccent, fontWeight: FontWeight.bold)))), ]
      ]))),
  );
}
DART;
        File::put($path, $stub);
    }
}
