<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        abort_if_cannot('view_payments');
        return response()->json(Payment::query()->with(array (
  0 => 'booking',
))->latest()->paginate(50));
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_payments');
        $rules = method_exists(Payment::class, 'rules') ? Payment::rules() : [];
        if (empty($rules)) {
            $rules = collect((new Payment)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray();
        }
        $validated = $request->validate($rules);
        $item = Payment::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_payments');
        $item = Payment::findOrFail($id);
        $rules = method_exists(Payment::class, 'rules') ? Payment::rules($id) : [];
        if (empty($rules)) {
            $rules = collect((new Payment)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray();
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
        abort_if_cannot('delete_payments');
        Payment::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
