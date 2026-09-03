<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeMobileModule extends Command
{
    protected $signature = 'make:mobile-module {name}';
    protected $description = 'Super Pro Scaffolder - CRUD, Permissions, Relationships';

    public function handle()
    {
        $name = $this->argument('name');
        $className = Str::studly($name);
        $pluralName = Str::plural($className);
        $pluralKebab = Str::kebab($pluralName);
        $pluralSnake = Str::snake($pluralName);

        $this->info("🚀 Duke ndertuar Super-Modulin: $className");

        $modelClass = "App\\Models\\BerberApp\\$className";
        if (!class_exists($modelClass)) $modelClass = "App\\Models\\$className";
        if (!class_exists($modelClass)) { $this->error("Modeli $modelClass nuk u gjet!"); return; }

        $model = new $modelClass;
        $fields = $model->getFillable();
        $relations = $this->detectRelations($fields);

        // 1. Backend: Controller me CRUD dhe Permissions
        $this->generateController($className, $pluralName, $fields, $relations, $pluralSnake);

        // 2. Mobile: List Page me Delete/Edit/Add
        $this->generateFlutterList($className, $pluralName, $pluralKebab, $pluralSnake);

        // 3. Mobile: Form Screen
        $this->generateFlutterForm($className, $fields, $pluralKebab, $relations);

        // 4. Shto Permissionat ne DB
        $this->addPermissions($className, $pluralSnake);

        $this->info("✅ Moduli $className u kompletua (CRUD + Permissions + DB Seed)");
    }

    private function detectRelations($fields) {
        $relations = [];
        foreach ($fields as $field) {
            if (str_ends_with($field, '_id')) {
                $rel = str_replace('_id', '', $field);
                $relations[$field] = [
                    'name' => $rel,
                    'method' => Str::camel($rel),
                    'plural_kebab' => Str::kebab(Str::plural($rel)),
                    'model' => Str::studly($rel)
                ];
            }
        }
        return $relations;
    }

    private function generateController($className, $pluralName, $fields, $relations, $permPrefix) {
        $path = app_path("Http/Controllers/Api/Mobile/{$className}Controller.php");
        $with = count($relations) > 0 ? "->with(['" . implode("','", array_column($relations, 'method')) . "'])" : "";
        $rules = ""; foreach ($fields as $f) $rules .= "            '$f' => 'required',\n";

        $content = "<?php\n\nnamespace App\Http\Controllers\Api\Mobile;\n\nuse App\Http\Controllers\Controller;\nuse App\Models\\BerberApp\\$className;\nuse Illuminate\Http\Request;\n\nclass {$className}Controller extends Controller\n{\n    public function index()\n    {\n        abort_if_cannot('view_$permPrefix');\n        return response()->json($className::query()->{$with}->latest()->paginate(50));\n    }\n\n    public function store(Request \$request)\n    {\n        abort_if_cannot('add_$permPrefix');\n        \$validated = \$request->validate([\n$rules        ]);\n        \$item = $className::create(\$validated);\n        return response()->json(['success' => true, 'data' => \$item]);\n    }\n\n    public function update(Request \$request, \$id)\n    {\n        abort_if_cannot('edit_$permPrefix');\n        \$item = $className::findOrFail(\$id);\n        \$validated = \$request->validate([\n$rules        ]);\n        \$item->update(\$validated);\n        return response()->json(['success' => true, 'data' => \$item]);\n    }\n\n    public function destroy(\$id)\n    {\n        abort_if_cannot('delete_$permPrefix');\n        \$item = $className::findOrFail(\$id);\n        \$item->delete();\n        return response()->json(['success' => true]);\n    }\n}\n";
        File::put($path, $content);
    }

    private function generateFlutterList($className, $pluralName, $pluralKebab, $permPrefix) {
        $snake = Str::snake($className);
        $path = base_path("mobile-gateway/lib/modules/dashboard/{$snake}_list_page.dart");
        $content = "import 'package:flutter/material.dart';\nimport '../../services/api_service.dart';\nimport 'dart:convert';\nimport '{$snake}_form_screen.dart';\n\nclass {$className}ListPage extends StatefulWidget {\n  const {$className}ListPage({super.key});\n  @override\n  State<{$className}ListPage> createState() => _{$className}ListPageState();\n}\n\nclass _{$className}ListPageState extends State<{$className}ListPage> {\n  List<dynamic> _items = [];\n  bool _isLoading = true;\n\n  @override\n  void initState() { super.initState(); _fetch(); }\n\n  Future<void> _fetch() async {\n    try {\n      final res = await ApiService.get('/$pluralKebab');\n      if (res.statusCode == 200) setState(() => _items = jsonDecode(res.body)['data'] ?? []);\n    } catch (_) {}\n    setState(() => _isLoading = false);\n  }\n\n  Future<void> _delete(int id) async {\n    final confirm = await showDialog<bool>(context: context, builder: (c) => AlertDialog(title: const Text('Konfirmoni'), content: const Text('A jeni te sigurte?'), actions: [TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Jo')), TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Po'))]));\n    if (confirm == true) {\n      final res = await ApiService.delete('/$pluralKebab/\$id');\n      if (res.statusCode == 200) _fetch();\n    }\n  }\n\n  @override\n  Widget build(BuildContext context) {\n    return Scaffold(\n      appBar: AppBar(title: const Text('$pluralName')),\n      floatingActionButton: FloatingActionButton(onPressed: () async { final res = await Navigator.push(context, MaterialPageRoute(builder: (c) => const {$className}FormScreen())); if (res == true) _fetch(); }, child: const Icon(Icons.add)),\n      body: _isLoading ? const Center(child: CircularProgressIndicator()) : ListView.builder(\n        itemCount: _items.length,\n        itemBuilder: (context, index) {\n          final item = _items[index];\n          return ListTile(\n            title: Text(item['name']?.toString() ?? 'ID: \${item['id']}'),\n            trailing: Row(mainAxisSize: MainAxisSize.min, children: [\n               IconButton(icon: const Icon(Icons.edit, color: Colors.blue), onPressed: () async { final res = await Navigator.push(context, MaterialPageRoute(builder: (c) => {$className}FormScreen(item: item))); if (res == true) _fetch(); }),\n               IconButton(icon: const Icon(Icons.delete, color: Colors.red), onPressed: () => _delete(item['id'])),\n            ]),\n          );\n        },\n      ),\n    );\n  }\n}\n";
        File::put($path, $content);
    }

    private function generateFlutterForm($className, $fields, $pluralKebab, $relations)
    {
        $snake = Str::snake($className);
        $path = base_path("mobile-gateway/lib/modules/dashboard/{$snake}_form_screen.dart");

        $controllers = ""; $init = ""; $widgets = ""; $payload = ""; $loaders = ""; $vars = "";

        foreach ($fields as $f) {
            if (isset($relations[$f])) {
                $rel = $relations[$f];
                $vars .= "  List<dynamic> _{$rel['plural_kebab']} = [];\n";
                $vars .= "  int? _selected" . Str::studly($f) . ";\n";
                $loaders .= "      final res" . Str::studly($f) . " = await ApiService.get('/{$rel['plural_kebab']}');\n";
                $loaders .= "      if (res" . Str::studly($f) . ".statusCode == 200) _{$rel['plural_kebab']} = jsonDecode(res" . Str::studly($f) . ".body)['data'] ?? jsonDecode(res" . Str::studly($f) . ".body);\n";
                $init .= "    _selected" . Str::studly($f) . " = widget.item?['$f'];\n";
                $widgets .= "            DropdownButtonFormField<int>(value: _selected" . Str::studly($f) . ", decoration: const InputDecoration(labelText: '" . Str::headline($rel['name']) . "'), items: _{$rel['plural_kebab']}.map((e) => DropdownMenuItem<int>(value: e['id'], child: Text(e['name']?.toString() ?? 'ID: \${e['id']}'))).toList(), onChanged: (v) => setState(() => _selected" . Str::studly($f) . " = v)),\n";
                $payload .= "      '$f': _selected" . Str::studly($f) . ",\n";
            } else {
                $controllers .= "  final _{$f}Controller = TextEditingController();\n";
                $init .= "    _{$f}Controller.text = widget.item?['$f']?.toString() ?? '';\n";
                $widgets .= "            TextField(controller: _{$f}Controller, decoration: const InputDecoration(labelText: '" . Str::headline($f) . "')),\n";
                $payload .= "      '$f': _{$f}Controller.text,\n";
            }
        }

        $content = "import 'package:flutter/material.dart';\nimport '../../services/api_service.dart';\nimport 'dart:convert';\n\nclass {$className}FormScreen extends StatefulWidget {\n  final Map<String, dynamic>? item;\n  const {$className}FormScreen({super.key, this.item});\n  @override\n  State<{$className}FormScreen> createState() => _{$className}FormScreenState();\n}\n\nclass _{$className}FormScreenState extends State<{$className}FormScreen> {\n$controllers $vars  bool _isSaving = false; bool _isLoading = true;\n\n  @override\n  void initState() {\n    super.initState();\n$init    _loadData();\n  }\n\n  Future<void> _loadData() async {\n    try {\n$loaders    } catch (_) {}\n    setState(() => _isLoading = false);\n  }\n\n  Future<void> _save() async {\n    setState(() => _isSaving = true);\n    final payload = {\n$payload    };\n    try {\n      final response = widget.item == null \n        ? await ApiService.post('/$pluralKebab', payload)\n        : await ApiService.post('/$pluralKebab/\${widget.item!['id']}', payload);\n      if (response.statusCode == 200) Navigator.pop(context, true);\n    } catch (_) {}\n    setState(() => _isSaving = false);\n  }\n\n  @override\n  Widget build(BuildContext context) {\n    return Scaffold(\n      appBar: AppBar(title: Text(widget.item == null ? 'Shto' : 'Edito')),\n      body: _isLoading ? const Center(child: CircularProgressIndicator()) : SingleChildScrollView(padding: const EdgeInsets.all(16), child: Column(children: [\n$widgets          const SizedBox(height: 20),\n          _isSaving ? const CircularProgressIndicator() : ElevatedButton(onPressed: _save, child: const Text('RUAJ'))\n        ])),\n    );\n  }\n}\n";
        File::put($path, $content);
    }

    private function addPermissions($name, $pluralSnake)
    {
        foreach (['view', 'add', 'edit', 'delete'] as $act) {
            \App\Models\Permission::firstOrCreate(
                ['name' => "{$act}_{$pluralSnake}"],
                ['label' => ucfirst($act) . ' ' . $name, 'module' => Str::plural($name)]
            );
        }
    }
}
