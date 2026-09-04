<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        abort_if_cannot('view_bookings');
        return response()->json(Booking::query()->with(array (
  0 => 'customer',
  1 => 'barber',
  2 => 'service',
))->latest()->paginate(50));
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_bookings');
        $rules = method_exists(Booking::class, 'rules') ? Booking::rules() : [];
        if (empty($rules)) {
            $rules = collect((new Booking)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray();
        }
        $validated = $request->validate($rules);
        $item = Booking::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_bookings');
        $item = Booking::findOrFail($id);
        $rules = method_exists(Booking::class, 'rules') ? Booking::rules($id) : [];
        if (empty($rules)) {
            $rules = collect((new Booking)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray();
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
        abort_if_cannot('delete_bookings');
        Booking::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
