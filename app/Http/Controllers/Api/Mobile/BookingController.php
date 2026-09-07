<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Booking;
use App\Domain\BerberApp\Booking\Actions\CreateBookingAction;
use App\Domain\BerberApp\Booking\DTOs\BookingDTO;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        abort_if_cannot('view_bookings');
        $items = Booking::query()->with(['customer', 'barber', 'service'])->latest()->paginate(50);
        return response()->json($items);
    }

    public function store(Request $request, CreateBookingAction $action)
    {
        abort_if_cannot('add_bookings');

        $data = $request->all();
        // Sigurohemi qe te dhenat jane ne formatin qe pret DTO
        $dto = BookingDTO::fromArray($data);

        $item = $action->execute($dto);

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_bookings');
        $item = Booking::findOrFail($id);
        $item->update($request->all());
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_bookings');
        try {
            $item = Booking::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Ky rekord nuk mund të fshihet.'], 400);
        }
    }
}
