<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

class MakeMobileModule extends Command
{
    protected $signature = 'make:mobile-module
        {name : Emri i Modelit}
        {--force : Mbishkruaj skedarët}
        {--build : Build APK}';

    protected $description = 'Enterprise Scaffolder for Mobile - Final Clean Version';

    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $className = Str::studly($name);
        $snake = Str::snake($className);
        $plural = Str::plural($className);
        $pluralKebab = Str::kebab($plural);
        $pluralSnake = Str::snake($plural);
        $force = (bool) $this->option('force');

        [$modelClass, $modelNamespace] = $this->resolveModel($className);
        if (!$modelClass) {
            $this->error("Modeli {$className} nuk u gjet.");
            return self::FAILURE;
        }

        $model = new $modelClass();
        $fields = array_values(array_filter($model->getFillable(), static fn ($f) => is_string($f) && !in_array($f, ['id', 'created_at', 'updated_at', 'deleted_at'])));

        $relations = $this->detectRelations($modelClass, $fields);
        $allRelations = $this->discoverAllRelations($modelClass);
        $jsonFields = array_keys(array_filter($model->getCasts(), fn($c) => in_array($c, ['array', 'json', 'object', 'collection'])));

        $meta = compact('className', 'snake', 'plural', 'pluralKebab', 'pluralSnake', 'fields', 'relations', 'allRelations', 'modelNamespace', 'jsonFields');

        try {
            $this->writeGenerated(app_path("Http/Controllers/Api/Mobile/{$className}Controller.php"), $this->controllerTemplate($meta), $force);
            $this->ensureRoute($pluralKebab, $className);
            $this->addPermissions($className, $pluralSnake);

            $this->writeGenerated(base_path("mobile-gateway/lib/modules/dashboard/{$snake}_list_page.dart"), $this->listTemplate($meta), $force);
            $this->writeGenerated(base_path("mobile-gateway/lib/modules/dashboard/{$snake}_form_screen.dart"), $this->formTemplate($meta), $force);

            if ($this->option('build')) $this->runBuild();
        } catch (Throwable $e) {
            $this->error('Gjenerimi dështoi: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("✅ Moduli {$className} u rikrijua me sukses!");
        return self::SUCCESS;
    }

    private function resolveModel(string $className): array
    {
        $candidates = ["App\\Models\\BerberApp\\{$className}", "App\\Models\\{$className}"];
        foreach ($candidates as $c) {
            if (class_exists($c)) return [$c, Str::beforeLast($c, '\\')];
        }
        return [null, null];
    }

    private function detectRelations(string $modelClass, array $fields): array
    {
        $relations = [];
        foreach ($fields as $field) {
            if (Str::endsWith($field, '_id')) {
                $method = Str::camel(Str::beforeLast($field, '_id'));
                if (method_exists($modelClass, $method)) {
                    $relations[$field] = ['method' => $method, 'endpoint' => Str::kebab(Str::plural($method)), 'label' => Str::headline($method)];
                }
            }
        }
        return $relations;
    }

    private function discoverAllRelations(string $modelClass): array
    {
        $methods = (new ReflectionClass($modelClass))->getMethods(ReflectionMethod::IS_PUBLIC);
        $relations = [];
        foreach ($methods as $method) {
            if ($method->class !== $modelClass || $method->getNumberOfParameters() > 0) continue;
            try {
                $instance = new $modelClass();
                $return = $method->invoke($instance);
                if ($return instanceof \Illuminate\Database\Eloquent\Relations\Relation) $relations[] = $method->name;
            } catch (Throwable $e) {}
        }
        return array_unique($relations);
    }

    private function writeGenerated(string $path, string $content, bool $force): void
    {
        if (File::exists($path) && !$force) return;
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
    }

    private function ensureRoute(string $endpoint, string $className): void
    {
        $path = base_path('routes/api.php');
        if (!File::exists($path)) return;
        $content = File::get($path);
        $route = "    Route::apiResource('{$endpoint}', \\App\\Http\\Controllers\\Api\\Mobile\\{$className}Controller::class);";
        if (!Str::contains($content, "apiResource('{$endpoint}'")) {
            $marker = "Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {";
            if (Str::contains($content, $marker)) {
                $content = str_replace($marker, $marker . "\n" . $route, $content);
                File::put($path, $content);
            }
        }
    }

    private function addPermissions(string $name, string $prefix): void
    {
        foreach (['view', 'add', 'edit', 'delete'] as $action) {
            \App\Models\Permission::firstOrCreate(['name' => "{$action}_{$prefix}"], ['label' => ucfirst($action) . ' ' . $name, 'module' => Str::plural($name)]);
        }
    }

    private function runBuild(): void
    {
        $path = base_path('mobile-gateway');
        $commands = ["cd {$path} && flutter clean", "cd {$path} && flutter pub get", "cd {$path} && flutter build apk --release"];
        foreach ($commands as $cmd) {
            $process = proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (is_resource($process)) {
                while ($line = fgets($pipes[1])) $this->line(trim($line));
                fclose($pipes[0]); fclose($pipes[1]); fclose($pipes[2]); proc_close($process);
            }
        }
    }

    private function controllerTemplate(array $m): string
    {
        $import = $m['modelNamespace'] . '\\' . $m['className'];
        $withStr = !empty($m['allRelations']) ? "->with(" . var_export($m['allRelations'], true) . ")" : "";
        $jsonFieldsArr = var_export($m['jsonFields'], true);

        return "<?php\n\nnamespace App\\Http\\Controllers\\Api\\Mobile;\n\nuse App\\Http\\Controllers\\Controller;\nuse {$import};\nuse Illuminate\\Http\\Request;\n\nclass {$m['className']}Controller extends Controller\n{\n    public function index()\n    {\n        \$items = {$m['className']}::query(){$withStr}->latest()->paginate(50);\n        \$jsonFields = {$jsonFieldsArr};\n        \$items->getCollection()->transform(function(\$item) use (\$jsonFields) {\n            foreach (\$jsonFields as \$f) {\n                \$val = \$item->getRawOriginal(\$f);\n                if (is_string(\$val) && str_starts_with(\$val, '{')) {\n                    \$item->setAttribute(\"{\$f}_raw\", json_decode(\$val, true));\n                } elseif (is_array(\$val)) {\n                    \$item->setAttribute(\"{\$f}_raw\", \$val);\n                } else {\n                     \$item->setAttribute(\"{\$f}_raw\", \$item->getAttributes()[\$f] ?? null);\n                }\n            }\n            return \$item;\n        });\n        return response()->json(\$items);\n    }\n\n    public function store(Request \$request)\n    {\n        \$rules = method_exists({$m['className']}::class, 'rules') ? {$m['className']}::rules() : [];\n        \$validated = \$request->validate(\$rules ?: collect((new {$m['className']})->getFillable())->mapWithKeys(fn(\$f) => [\$f => 'required'])->toArray());\n\n        if (\$request->hasFile('photo')) {\n            \$file = \$request->file('photo');\n            \$name = time() . '_' . \$file->getClientOriginalName();\n            \$file->move(public_path('uploads'), \$name);\n            \$validated['photo'] = 'uploads/' . \$name;\n        }\n\n        \$item = {$m['className']}::create(\$validated);\n        return response()->json(['success' => true, 'data' => \$item]);\n    }\n\n    public function update(Request \$request, \$id)\n    {\n        \$item = {$m['className']}::findOrFail(\$id);\n        \$rules = method_exists({$m['className']}::class, 'rules') ? {$m['className']}::rules(\$id) : [];\n        \$validated = \$request->validate(\$rules ?: collect((new {$m['className']})->getFillable())->mapWithKeys(fn(\$f) => [\$f => 'required'])->toArray());\n\n        if (\$request->hasFile('photo')) {\n            if (\$item->photo && file_exists(public_path(\$item->photo))) @unlink(public_path(\$item->photo));\n            \$file = \$request->file('photo');\n            \$name = time() . '_' . \$file->getClientOriginalName();\n            \$file->move(public_path('uploads'), \$name);\n            \$validated['photo'] = 'uploads/' . \$name;\n        }\n\n        \$item->update(\$validated);\n        return response()->json(['success' => true, 'data' => \$item]);\n    }\n\n    public function destroy(\$id)\n    {\n        \$item = {$m['className']}::findOrFail(\$id);\n        if (\$item->photo && file_exists(public_path(\$item->photo))) @unlink(public_path(\$item->photo));\n        \$item->delete();\n        return response()->json(['success' => true]);\n    }\n}\n";
    }

    private function listTemplate(array $m): string
    {
        $subtitleLogic = "";
        foreach($m['relations'] as $r) $subtitleLogic .= "item['{$r['method']}']?['name']?.toString() ?? ";
        $subtitleLogic .= "item['phone'] ?? item['appointment_datetime'] ?? 'ID: \${item['id']}'";

        $extraFieldsLogic = "";
        foreach ($m['fields'] as $f) {
            if (in_array($f, ['id', 'created_at', 'updated_at', 'name', 'token', 'fcm_token', 'customer_name', 'customer_phone', 'photo'])) continue;
            if (isset($m['relations'][$f])) continue;
            $label = Str::headline($f);
            $extraFieldsLogic .= "if (item['$f'] != null) ...[const SizedBox(height: 4), Row(children: [Text('$label: ', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey)), Expanded(child: Text(_getName(item['$f']), style: const TextStyle(fontSize: 12), overflow: TextOverflow.ellipsis))])],\n";
        }

        $template = <<<'DART'
import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import 'dart:convert';
import 'package:intl/intl.dart';
import '##SNAKE##_form_screen.dart';

class ##CLASS##ListPage extends StatefulWidget {
  const ##CLASS##ListPage({super.key});
  @override State<##CLASS##ListPage> createState() => _##CLASS##ListPageState();
}

class _##CLASS##ListPageState extends State<##CLASS##ListPage> {
  List<dynamic> _items = []; List<dynamic> _filtered = []; bool _loading = true;
  final _search = TextEditingController();

  @override void initState() { super.initState(); _fetch(); }

  Future<void> _fetch() async {
    final res = await ApiService.get('/##PLURAL_KEBAB##');
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body)['data'] ?? [];
      setState(() { _items = data; _filtered = data; });
    }
    setState(() => _loading = false);
  }

  void _filter(String q) { setState(() => _filtered = _items.where((i) => i.toString().toLowerCase().contains(q.toLowerCase())).toList()); }

  String _getName(dynamic n) {
    if (n == null) return '';
    try {
      if (n is String && n.trim().startsWith('{')) {
        final Map<String, dynamic> decoded = jsonDecode(n);
        return (decoded['sq'] ?? decoded['en'] ?? decoded.values.firstOrNull ?? '').toString();
      }
      if (n is Map) return (n['sq'] ?? n['en'] ?? n.values.firstOrNull ?? '').toString();
    } catch (_) {}
    return n.toString();
  }

  String _getDisplayTitle(Map item) {
    String name = _getName(item['name']);
    if (name.isEmpty) name = _getName(item['customer_name']);
    if (name.isEmpty) name = _getName(item['customer']?['name']);
    if (name.isEmpty) name = _getName(item['title']);
    if (name.isEmpty) name = _getName(item['label']);
    return name.isEmpty ? 'ID: ${item['id']}' : name;
  }

  Future<void> _delete(int id) async {
    final ok = await showDialog<bool>(context: context, builder: (c) => AlertDialog(title: const Text('Fshi?'), content: const Text('A jeni të sigurt?'), actions: [TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Jo')), TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Po'))]));
    if (ok == true) { await ApiService.delete('/##PLURAL_KEBAB##/$id'); _fetch(); }
  }

  @override Widget build(BuildContext context) => Scaffold(
    backgroundColor: Colors.white,
    appBar: AppBar(elevation: 0, backgroundColor: Colors.white, title: const Text('##PLURAL##', style: TextStyle(color: Colors.black, fontWeight: FontWeight.w900, fontSize: 24))),
    floatingActionButton: FloatingActionButton(backgroundColor: Colors.black, onPressed: () async { final res = await Navigator.push(context, MaterialPageRoute(builder: (c) => const ##CLASS##FormScreen())); if (res == true) _fetch(); }, child: const Icon(Icons.add, color: Colors.white)),
    body: _loading ? const Center(child: CircularProgressIndicator(color: Colors.black)) : RefreshIndicator(
      onRefresh: _fetch,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _filtered.length,
        itemBuilder: (context, index) {
          final item = _filtered[index];
          return Container(
            margin: const EdgeInsets.only(bottom: 16),
            decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(24), border: Border.all(color: Colors.grey.shade200), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 10, offset: const Offset(0, 4))]),
            child: InkWell(
              borderRadius: BorderRadius.circular(24),
              onTap: () async { final res = await Navigator.push(context, MaterialPageRoute(builder: (c) => ##CLASS##FormScreen(item: item))); if (res == true) _fetch(); },
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    CircleAvatar(
                      radius: 24, backgroundColor: Colors.black,
                      backgroundImage: item['photo'] != null ? NetworkImage('${ApiService.serverUrl}/${item['photo']}') : null,
                      child: item['photo'] == null ? Text(_getDisplayTitle(item).isNotEmpty ? _getDisplayTitle(item)[0].toUpperCase() : '?', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)) : null,
                    ),
                    const SizedBox(width: 16),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(_getDisplayTitle(item), style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 18)),
                      Text(##SUBTITLE_LOGIC##, style: TextStyle(color: Colors.grey[600], fontSize: 13)),
                    ])),
                    IconButton(icon: const Icon(Icons.delete_outline, color: Colors.redAccent, size: 20), onPressed: () => _delete(item['id'])),
                  ]),
                  const Padding(padding: EdgeInsets.symmetric(vertical: 12), child: Divider(height: 1)),
                  ##EXTRA_FIELDS##
                ]),
              ),
            ),
          );
        },
      ),
    ),
  );
}
DART;

        return str_replace(['##SNAKE##', '##CLASS##', '##PLURAL_KEBAB##', '##PLURAL##', '##SUBTITLE_LOGIC##', '##EXTRA_FIELDS##'], [$m['snake'], $m['className'], $m['pluralKebab'], $m['plural'], $subtitleLogic, $extraFieldsLogic], $template);
    }

    private function formTemplate(array $m): string
    {
        $vars = ""; $init = ""; $loaders = ""; $payloadExtra = ""; $widgets = "";
        $hasImage = false;

        foreach ($m['fields'] as $f) {
            if (in_array($f, ['id', 'created_at', 'updated_at', 'token', 'fcm_token', 'customer_name', 'customer_phone'])) continue;
            $safe = Str::studly($f); $label = Str::headline($f);
            $isImage = Str::contains($f, ['photo', 'image', 'avatar', 'picture']);
            $isTranslatable = in_array($f, $m['jsonFields']);

            if ($isImage) {
                $hasImage = true; $vars .= "  String? _imagePath$safe;\n";
                $widgets .= "            const Text('$label', style: TextStyle(fontWeight: FontWeight.bold)), const SizedBox(height: 8),
            GestureDetector(
              onTap: () async {
                final picked = await ImagePicker().pickImage(source: ImageSource.gallery);
                if (picked != null) setState(() => _imagePath$safe = picked.path);
              },
              child: Container(
                height: 150, width: double.infinity,
                decoration: BoxDecoration(color: Colors.grey[100], borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.shade300)),
                child: _imagePath$safe != null ? ClipRRect(borderRadius: BorderRadius.circular(16), child: Image.file(File(_imagePath$safe!))) : (widget.item?['$f'] != null ? ClipRRect(borderRadius: BorderRadius.circular(16), child: Image.network('\${ApiService.serverUrl}/\${widget.item!['$f']}', fit: BoxFit.cover)) : const Icon(Icons.add_a_photo, size: 40, color: Colors.grey)),
              ),
            ), const SizedBox(height: 20),\n";
            } elseif (isset($m['relations'][$f])) {
                $rel = $m['relations'][$f];
                $vars .= "  List<dynamic> _{$rel['method']}Options = []; dynamic _selected{$safe};\n";
                $init .= "    _selected{$safe} = widget.item?['$f'];\n";
                $loaders .= "      final res{$safe} = await ApiService.get('/{$rel['endpoint']}'); if (res{$safe}.statusCode == 200) setState(() => _{$rel['method']}Options = (jsonDecode(res{$safe}.body)['data'] ?? jsonDecode(res{$safe}.body)) as List);\n";
                $widgets .= "            DropdownButtonFormField<dynamic>(
              value: _{$rel['method']}Options.any((e) => e['id'] == _selected{$safe}) ? _selected{$safe} : null,
              validator: (v) => v == null ? 'Fusha $label është e detyrueshme' : null,
              decoration: InputDecoration(labelText: '$label', prefixIcon: const Icon(Icons.link, color: Colors.black), border: OutlineInputBorder(borderRadius: BorderRadius.circular(16))),
              items: _{$rel['method']}Options.map((e) => DropdownMenuItem<dynamic>(value: e['id'], child: Text(e['name'] is Map ? (e['name']['sq'] ?? e['name']['en'] ?? 'N/A') : (e['name']?.toString() ?? 'ID: \${e['id']}')))).toList(),
              onChanged: (v) => setState(() => _selected{$safe} = v)
            ), const SizedBox(height: 20),\n";
                $payloadExtra .= "    payload['$f'] = _selected{$safe};\n";
            } elseif (str_contains($f, '_at') || str_contains($f, 'date')) {
                $init .= "    _controllers['$f'] = TextEditingController(text: widget.item?['$f']?.toString() ?? '');\n";
                $widgets .= "            TextFormField(
              controller: _controllers['$f'], readOnly: true, validator: (v) => (v == null || v.isEmpty) ? 'Zgjidhni datën për $label' : null,
              decoration: InputDecoration(labelText: '$label', prefixIcon: const Icon(Icons.calendar_today, color: Colors.black), border: OutlineInputBorder(borderRadius: BorderRadius.circular(16))),
              onTap: () async {
                DateTime? p = await showDatePicker(context: context, initialDate: DateTime.now(), firstDate: DateTime(2000), lastDate: DateTime(2100));
                if(p != null) {
                  TimeOfDay? t = await showTimePicker(context: context, initialTime: TimeOfDay.now());
                  if(t != null) setState(() => _controllers['$f']!.text = DateTime(p.year, p.month, p.day, t.hour, t.minute).toIso8601String());
                }
              }
            ), const SizedBox(height: 20),\n";
            } else {
                if ($isTranslatable) {
                    $vars .= "  final Map<String, TextEditingController> _{$f}LangControllers = {'sq': TextEditingController(), 'en': TextEditingController()};\n";
                    $init .= "    dynamic {$f}Data = widget.item?['{$f}_raw'] ?? widget.item?['$f'];
    if ({$f}Data is String && {$f}Data.trim().startsWith('{')) { try { {$f}Data = jsonDecode({$f}Data); } catch(_) {} }
    if({$f}Data is Map) { _{$f}LangControllers['sq']!.text = {$f}Data['sq'] ?? ''; _{$f}LangControllers['en']!.text = {$f}Data['en'] ?? ''; }\n";
                    $widgets .= "            TextFormField(controller: _{$f}LangControllers['sq'], validator: (v) => (v == null || v.isEmpty) ? '$label (SQ) i detyrueshëm' : null, decoration: InputDecoration(labelText: '$label (AL)', border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)))), const SizedBox(height: 12),\n";
                    $widgets .= "            TextFormField(controller: _{$f}LangControllers['en'], decoration: InputDecoration(labelText: '$label (EN)', border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)))), const SizedBox(height: 20),\n";
                    $payloadExtra .= "    payload['$f'] = {'sq': _{$f}LangControllers['sq']!.text, 'en': _{$f}LangControllers['en']!.text};\n";
                } else {
                    $init .= "    _controllers['$f'] = TextEditingController(text: widget.item?['$f']?.toString() ?? '');\n";
                    $widgets .= "            TextFormField(
              controller: _controllers['$f'], validator: (v) => (v == null || v.isEmpty) ? 'Plotësoni $label' : null,
              keyboardType: '$f'.contains('price') || '$f'.contains('rate') || '$f'.contains('minutes') ? TextInputType.number : TextInputType.text,
              decoration: InputDecoration(labelText: '$label', border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)))
            ), const SizedBox(height: 20),\n";
                }
            }
        }

        $imgStrLogic = "";
        foreach ($m['fields'] as $f) {
             if (Str::contains($f, ['photo', 'image', 'avatar', 'picture'])) {
                 $safe = Str::studly($f);
                 $imgStrLogic .= "    String? _imagePathStr = _imagePath$safe;\n";
                 break;
             }
        }

        $saveCall = $hasImage
            ? "await ApiService.postMultipart(widget.item == null ? '/##PLURAL_KEBAB##' : '/##PLURAL_KEBAB##/\${widget.item!['id']}', payload, filePath: _imagePathStr, fieldName: 'photo')"
            : "await ApiService.post(widget.item == null ? '/##PLURAL_KEBAB##' : '/##PLURAL_KEBAB##/\${widget.item!['id']}', payload)";

        $template = <<<'DART'
import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import 'dart:convert';
import 'package:intl/intl.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';

class ##CLASS##FormScreen extends StatefulWidget {
  final Map<String, dynamic>? item;
  const ##CLASS##FormScreen({super.key, this.item});
  @override State<##CLASS##FormScreen> createState() => _##CLASS##FormState();
}

class _##CLASS##FormState extends State<##CLASS##FormScreen> {
  final _formKey = GlobalKey<FormState>();
  bool _isSaving = false; bool _isLoading = true;
  final Map<String, TextEditingController> _controllers = {};
  ##VARS##

  @override void initState() { super.initState(); ##INIT## _loadData(); }

  Future<void> _loadData() async {
    try { ##LOADERS## } catch (_) {}
    if (mounted) setState(() => _isLoading = false);
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isSaving = true);
    final payload = <String, dynamic>{};
    _controllers.forEach((k, v) => payload[k] = v.text);
    ##PAYLOAD_EXTRA##
    ##IMG_STR_LOGIC##
    try {
      final res = ##SAVE_CALL##;
      if (res.statusCode >= 200 && res.statusCode < 300) {
        if (mounted) Navigator.pop(context, true);
      } else {
        final msg = ApiService.extractErrorMessage(res);
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gabim: $msg'), backgroundColor: Colors.redAccent));
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gabim rrjeti: $e'), backgroundColor: Colors.redAccent));
    }
    setState(() => _isSaving = false);
  }

  @override Widget build(BuildContext context) => Scaffold(
    backgroundColor: Colors.white,
    appBar: AppBar(elevation: 0, backgroundColor: Colors.white, iconTheme: const IconThemeData(color: Colors.black), title: Text(widget.item == null ? 'Shto' : 'Edito', style: const TextStyle(color: Colors.black, fontWeight: FontWeight.w900))),
    body: _isLoading ? const Center(child: CircularProgressIndicator(color: Colors.black)) : SingleChildScrollView(padding: const EdgeInsets.all(24), child: Form(key: _formKey, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      ##WIDGETS##
      const SizedBox(height: 30),
      SizedBox(width: double.infinity, height: 60, child: ElevatedButton(style: ElevatedButton.styleFrom(backgroundColor: Colors.black, foregroundColor: Colors.white, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16))), onPressed: _isSaving ? null : _save, child: _isSaving ? const CircularProgressIndicator(color: Colors.white) : const Text('RUAJ TË DHËNAT', style: TextStyle(fontWeight: FontWeight.w900))))
    ]))),
  );
}
DART;

        return str_replace(['##CLASS##', '##PLURAL_KEBAB##', '##VARS##', '##INIT##', '##LOADERS##', '##PAYLOAD_EXTRA##', '##WIDGETS##', '##SAVE_CALL##', '##IMG_STR_LOGIC##'], [$m['className'], $m['pluralKebab'], $vars, $init, $loaders, $payloadExtra, $widgets, $saveCall, $imgStrLogic], $template);
    }
}
