<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index()
    {
        $query = Payment::query()->with(array (
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

    protected function prepareData(Request $request)
    {
        $data = $request->all();
        $jsonFields = array (
);
        foreach ($jsonFields as $f) {
            if (isset($data[$f]) && is_string($data[$f]) && str_starts_with($data[$f], '{')) {
                $data[$f] = json_decode($data[$f], true);
            }
        }
        return $data;
    }

    public function store(Request $request)
    {
        $data = $this->prepareData($request);
        $rules = method_exists(Payment::class, 'rules') ? Payment::rules() : [];
        $validated = validator($data, $rules ?: collect((new Payment)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray())->validate();

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $name);
            $validated['photo'] = 'uploads/' . $name;
        }

        $item = Payment::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = Payment::findOrFail($id);
        $data = $this->prepareData($request);
        $rules = method_exists(Payment::class, 'rules') ? Payment::rules($id) : [];
        $validated = validator($data, $rules ?: collect((new Payment)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray())->validate();

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
        $item = Payment::findOrFail($id);
        if ($item->photo && file_exists(public_path($item->photo))) @unlink(public_path($item->photo));
        $item->delete();
        return response()->json(['success' => true]);
    }
}
