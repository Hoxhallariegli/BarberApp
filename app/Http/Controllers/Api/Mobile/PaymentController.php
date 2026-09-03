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
        return response()->json(Payment::query()->->with(['booking'])->latest()->paginate(50));
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_payments');
        $validated = $request->validate([
            'booking_id' => 'required',
            'amount' => 'required',
            'status' => 'required',
        ]);
        $item = Payment::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_payments');
        $item = Payment::findOrFail($id);
        $validated = $request->validate([
            'booking_id' => 'required',
            'amount' => 'required',
            'status' => 'required',
        ]);
        $item->update($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_payments');
        $item = Payment::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true]);
    }
}
