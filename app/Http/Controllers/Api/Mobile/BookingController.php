<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $items = Booking::query()->with(array (
  0 => 'customer',
  1 => 'barber',
  2 => 'service',
))->latest()->paginate(50);
        $items->getCollection()->transform(fn($i) => $this->transformItem($i));
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $data = $this->prepareData($request);
        $rules = method_exists(Booking::class, 'rules') ? Booking::rules() : [];
        $validated = validator($data, $rules ?: collect((new Booking)->getFillable())->mapWithKeys(fn($f)=>[$f=>'required'])->toArray())->validate();

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $name);
            $validated['photo'] = 'uploads/' . $name;
        }

        $item = Booking::create($validated);
        return response()->json(['success' => true, 'data' => $this->transformItem($item)]);
    }

    public function update(Request $request, $id)
    {
        $item = Booking::findOrFail($id);
        $data = $this->prepareData($request);
        $rules = method_exists(Booking::class, 'rules') ? Booking::rules($id) : [];
        $validated = validator($data, $rules ?: collect((new Booking)->getFillable())->mapWithKeys(fn($f)=>[$f=>'required'])->toArray())->validate();

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
        try {
            $item = Booking::findOrFail($id);
            if ($item->photo && file_exists(public_path($item->photo))) @unlink(public_path($item->photo));
            $item->delete();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ky rekord nuk mund të fshihet pasi është i lidhur me të dhëna të tjera në sistem.'
            ], 400);
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