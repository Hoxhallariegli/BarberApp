<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Booking;
use Illuminate\Http\Request;
use App\Domain\BerberApp\Booking\DTOs\BookingDTO;
use App\Domain\BerberApp\Booking\Actions\CreateBookingAction;
use App\Domain\BerberApp\Booking\Actions\UpdateBookingAction;


class BookingController extends Controller
{
    public function index()
    {
        abort_if_cannot('view_bookings');
        $items = Booking::query()->with(['customer', 'barber', 'service', 'services'])->latest()->paginate(50);
        $items->getCollection()->transform(fn($i) => $this->transformItem($i));
        return response()->json($items);
    }

    public function store(Request $request, CreateBookingAction $action)
    {
        abort_if_cannot('add_bookings');
        $data = $this->prepareData($request);
        $dto = BookingDTO::fromArray($data);
        $item = $action->execute($dto);
        return response()->json(['success' => true, 'data' => $this->transformItem($item)]);
    }

    public function update(Request $request, $id, UpdateBookingAction $action)
    {
        abort_if_cannot('edit_bookings');
        $item = Booking::findOrFail($id);
        $data = $this->prepareData($request);
        $dto = BookingDTO::fromArray($data);
        $item = $action->execute($item, $dto);
        return response()->json(['success' => true, 'data' => $this->transformItem($item)]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_bookings');
        try {
            $item = Booking::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Ky rekord është i lidhur me të dhëna të tjera.'], 400);
        }
    }

    private function transformItem($item) {
        foreach (array (
) as $f) {
            $val = $item->getRawOriginal($f);
            $item->setAttribute("{$f}_raw", is_string($val) && str_starts_with($val, '{') ? json_decode($val, true) : $val);
        }
        return $item;
    }

    private function prepareData(Request $request) {
        $data = $request->all();

        if (isset($data['service_ids']) && is_string($data['service_ids'])) {
            $data['service_ids'] = json_decode($data['service_ids'], true);
        }

        foreach (array (
) as $f) {
            if (isset($data[$f]) && is_string($data[$f]) && str_starts_with($data[$f], '{')) $data[$f] = json_decode($data[$f], true);
        }
        return $data;
    }
}
