<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Reminder;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function index()
    {
        abort_if_cannot('view_reminders');
        $items = Reminder::query()->with(array (
  0 => 'booking',
))->latest()->paginate(50);
        $items->getCollection()->transform(fn($i) => $this->transformItem($i));
        return response()->json($items);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_reminders');
        $data = $this->prepareData($request);
        $rules = method_exists(Reminder::class, 'rules') ? Reminder::rules() : [];
        $validated = validator($data, $rules ?: collect((new Reminder)->getFillable())->mapWithKeys(fn($f)=>[$f=>'required'])->toArray())->validate();

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $name);
            $validated['photo'] = 'uploads/' . $name;
        }

        $item = Reminder::create($validated);
        return response()->json(['success' => true, 'data' => $this->transformItem($item)]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_reminders');
        $item = Reminder::findOrFail($id);
        $data = $this->prepareData($request);
        $rules = method_exists(Reminder::class, 'rules') ? Reminder::rules($id) : [];
        $validated = validator($data, $rules ?: collect((new Reminder)->getFillable())->mapWithKeys(fn($f)=>[$f=>'required'])->toArray())->validate();

        if ($request->hasFile('photo')) {
            if ($item->photo && file_exists(public_path($item->photo))) @unlink(public_path($item->photo));
            $file = $request->file('photo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $name);
            $validated['photo'] = 'uploads/' . $name;
        }

        $item->update($validated);
        return response()->json(['success' => true, 'data' => $this->transformItem($item)]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_reminders');
        try {
            $item = Reminder::findOrFail($id);
            if ($item->photo && file_exists(public_path($item->photo))) @unlink(public_path($item->photo));
            $item->delete();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Ky rekord është i lidhur me të dhëna të tjera.'], 400);
        }
    }

    private function transformItem($item) {
        foreach (array (
) as $f) {
            $val = $item->getRawOriginal($f);
            $item->setAttribute("{$f}_raw", is_string($val) && str_starts_with($val, '{') ? json_decode($val, true) : $val);
        }
        return $item;
    }

    private function prepareData(Request $request) {
        $data = $request->all();
        foreach (array (
) as $f) {
            if (isset($data[$f]) && is_string($data[$f]) && str_starts_with($data[$f], '{')) $data[$f] = json_decode($data[$f], true);
        }
        return $data;
    }
}