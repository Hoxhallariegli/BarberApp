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
        // Marrim rezervimin bashke me klientin dhe sherbimin
        $items = Payment::query()->with(['booking.customer', 'booking.service'])->latest()->paginate(50);
        return response()->json($items);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_payments');
        $validated = $request->validate(['booking_id'=>'required','amount'=>'required|numeric','payment_method'=>'required','status'=>'required']);
        $item = Payment::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_payments');
        $item = Payment::findOrFail($id);
        $validated = $request->validate(['amount'=>'sometimes|numeric','payment_method'=>'sometimes','status'=>'sometimes']);
        $item->update($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy(Request $request, $id)
    {
        abort_if_cannot('delete_payments');
        $item = Payment::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true]);
    }
}
