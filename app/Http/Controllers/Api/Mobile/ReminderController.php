<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Reminder;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function index()
    {
        abort_if_cannot('view_reminders');
        return response()->json(Reminder::query()->->with(['booking'])->latest()->paginate(50));
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_reminders');
        $validated = $request->validate([
            'booking_id' => 'required',
            'reminder_type' => 'required',
            'sent_at' => 'required',
            'status' => 'required',
            'send_at' => 'required',
        ]);
        $item = Reminder::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_reminders');
        $item = Reminder::findOrFail($id);
        $validated = $request->validate([
            'booking_id' => 'required',
            'reminder_type' => 'required',
            'sent_at' => 'required',
            'status' => 'required',
            'send_at' => 'required',
        ]);
        $item->update($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_reminders');
        $item = Reminder::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true]);
    }
}
