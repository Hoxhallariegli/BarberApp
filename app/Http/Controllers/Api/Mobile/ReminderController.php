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
        $items = Reminder::query()->with(['booking.customer'])->orderByRaw("status = 'pending' DESC")->latest()->paginate(50);
        return response()->json($items);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_reminders');
        try {
            $item = Reminder::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Ky rekord nuk mund të fshihet.'], 400);
        }
    }
}
