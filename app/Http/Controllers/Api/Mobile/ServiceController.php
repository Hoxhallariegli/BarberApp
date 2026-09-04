<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        $items = Service::query()->latest()->paginate(50);
        $jsonFields = array (
  0 => 'name',
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
        $rules = method_exists(Service::class, 'rules') ? Service::rules() : [];
        $validated = $request->validate($rules ?: collect((new Service)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray());

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $name);
            $validated['photo'] = 'uploads/' . $name;
        }

        $item = Service::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = Service::findOrFail($id);
        $rules = method_exists(Service::class, 'rules') ? Service::rules($id) : [];
        $validated = $request->validate($rules ?: collect((new Service)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray());

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
        $item = Service::findOrFail($id);
        if ($item->photo && file_exists(public_path($item->photo))) @unlink(public_path($item->photo));
        $item->delete();
        return response()->json(['success' => true]);
    }
}
