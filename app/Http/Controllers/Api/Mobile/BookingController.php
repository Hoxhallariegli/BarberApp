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
        return response()->json(Booking::query()->->with(['customer','barber','service'])->latest()->paginate(50));
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_bookings');
        $validated = $request->validate([
            'token' => 'required',
            'customer_id' => 'required',
            'barber_id' => 'required',
            'service_id' => 'required',
            'appointment_datetime' => 'required',
            'customer_name' => 'required',
            'customer_phone' => 'required',
            'status' => 'required',
            'locale' => 'required',
            'reminder_enabled' => 'required',
            'reminder_minutes' => 'required',
            'fcm_token' => 'required',
        ]);
        $item = Booking::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_bookings');
        $item = Booking::findOrFail($id);
        $validated = $request->validate([
            'token' => 'required',
            'customer_id' => 'required',
            'barber_id' => 'required',
            'service_id' => 'required',
            'appointment_datetime' => 'required',
            'customer_name' => 'required',
            'customer_phone' => 'required',
            'status' => 'required',
            'locale' => 'required',
            'reminder_enabled' => 'required',
            'reminder_minutes' => 'required',
            'fcm_token' => 'required',
        ]);
        $item->update($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_bookings');
        $item = Booking::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true]);
    }
}
