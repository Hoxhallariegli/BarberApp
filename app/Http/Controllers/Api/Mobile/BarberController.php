<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Barber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BarberController extends Controller
{
    public function index()
    {
        return response()->json(Barber::query()->with(array (
  0 => 'schedules',
))->latest()->paginate(50));
    }

    public function store(Request $request)
    {
        $rules = method_exists(Barber::class, 'rules') ? Barber::rules() : [];
        if (empty($rules)) {
            $rules = collect((new Barber)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray();
        }
        $validated = $request->validate($rules);
        
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $item = Barber::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = Barber::findOrFail($id);
        $rules = method_exists(Barber::class, 'rules') ? Barber::rules($id) : [];
        if (empty($rules)) {
            $rules = collect((new Barber)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray();
        }
        $validated = $request->validate($rules);

        if ($request->hasFile('photo')) {
            if ($item->photo) Storage::disk('public')->delete($item->photo);
            $validated['photo'] = $request->file('photo')->store('uploads', 'public');
        }

        $item->update($validated);
        if ($request->has('schedules') && method_exists($item, 'schedules')) {
            $schedules = is_string($request->schedules) ? json_decode($request->schedules, true) : $request->schedules;
            foreach ($schedules as $s) {
                $item->schedules()->updateOrCreate(['day_of_week' => $s['day_of_week']], collect($s)->except(['id','barber_id','created_at','updated_at'])->toArray());
            }
        }
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        Barber::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
