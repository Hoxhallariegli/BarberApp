<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Barber;
use Illuminate\Http\Request;

class BarberController extends Controller
{
    public function index()
    {
        abort_if_cannot('view_barbers');
        return response()->json(Barber::query()->with(array (
  0 => 'schedules',
))->latest()->paginate(50));
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_barbers');
        $rules = method_exists(Barber::class, 'rules') ? Barber::rules() : [];
        if (empty($rules)) {
            $rules = collect((new Barber)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray();
        }
        $validated = $request->validate($rules);
        $item = Barber::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_barbers');
        $item = Barber::findOrFail($id);
        $rules = method_exists(Barber::class, 'rules') ? Barber::rules($id) : [];
        if (empty($rules)) {
            $rules = collect((new Barber)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray();
        }
        $validated = $request->validate($rules);
        $item->update($validated);
        if ($request->has('schedules') && method_exists($item, 'schedules')) {
            foreach ($request->schedules as $s) {
                $item->schedules()->updateOrCreate(['day_of_week' => $s['day_of_week']], collect($s)->except(['id','barber_id','created_at','updated_at'])->toArray());
            }
        }
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_barbers');
        Barber::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
