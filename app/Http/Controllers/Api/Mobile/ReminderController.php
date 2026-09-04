<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Reminder;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function index()
    {
        $query = Reminder::query()->with(array (
  0 => 'booking',
));
        $items = $query->latest()->paginate(50);
        $jsonFields = array (
);
        
        $items->getCollection()->transform(function($item) use ($jsonFields) {
            foreach ($jsonFields as $f) {
                $val = $item->getRawOriginal($f);
                if (is_string($val) && str_starts_with($val, '{')) {
                    $item->setAttribute("{$f}_raw", json_decode($val, true));
                } elseif (is_array($val)) {
                    $item->setAttribute("{$f}_raw", $val);
                } else {
                     $item->setAttribute("{$f}_raw", $item->getAttributes()[$f] ?? null);
                }
            }
            return $item;
        });
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $rules = method_exists(Reminder::class, 'rules') ? Reminder::rules() : [];
        $validated = $request->validate($rules ?: collect((new Reminder)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray());

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $name);
            $validated['photo'] = 'uploads/' . $name;
        }

        $item = Reminder::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = Reminder::findOrFail($id);
        $rules = method_exists(Reminder::class, 'rules') ? Reminder::rules($id) : [];
        $validated = $request->validate($rules ?: collect((new Reminder)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray());

        if ($request->hasFile('photo')) {
            if ($item->photo && file_exists(public_path($item->photo))) @unlink(public_path($item->photo));
            $file = $request->file('photo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $name);
            $validated['photo'] = 'uploads/' . $name;
        }

        $item->update($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        $item = Reminder::findOrFail($id);
        if ($item->photo && file_exists(public_path($item->photo))) @unlink(public_path($item->photo));
        $item->delete();
        return response()->json(['success' => true]);
    }
}
