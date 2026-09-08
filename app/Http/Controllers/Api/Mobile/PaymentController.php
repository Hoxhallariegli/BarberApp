<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Payment;
use Illuminate\Http\Request;
use App\Domain\BerberApp\Payment\DTOs\PaymentDTO;
use App\Domain\BerberApp\Payment\Actions\CreatePaymentAction;
use App\Domain\BerberApp\Payment\Actions\UpdatePaymentAction;


class PaymentController extends Controller
{
    public function index()
    {
        abort_if_cannot('view_payments');
        $items = Payment::query()->with(array (
  0 => 'booking',
))->latest()->paginate(50);
        $items->getCollection()->transform(fn($i) => $this->transformItem($i));
        return response()->json($items);
    }
    
    public function store(Request $request, CreatePaymentAction $action)
    {
        abort_if_cannot('add_payments');
        $data = $this->prepareData($request);
        $dto = PaymentDTO::fromArray($data);
        $item = $action->execute($dto);
        return response()->json(['success' => true, 'data' => $this->transformItem($item)]);
    }
    
    public function update(Request $request, $id, UpdatePaymentAction $action)
    {
        abort_if_cannot('edit_payments');
        $item = Payment::findOrFail($id);
        $data = $this->prepareData($request);
        $dto = PaymentDTO::fromArray($data);
        $item = $action->execute($item, $dto);
        return response()->json(['success' => true, 'data' => $this->transformItem($item)]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_payments');
        try {
            $item = Payment::findOrFail($id);
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
        foreach (array (
) as $f) {
            if (isset($data[$f]) && is_string($data[$f]) && str_starts_with($data[$f], '{')) $data[$f] = json_decode($data[$f], true);
        }
        return $data;
    }
}