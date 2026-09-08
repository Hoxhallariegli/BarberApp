<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Service;
use Illuminate\Http\Request;
use App\Domain\BerberApp\Service\DTOs\ServiceDTO;
use App\Domain\BerberApp\Service\Actions\CreateServiceAction;
use App\Domain\BerberApp\Service\Actions\UpdateServiceAction;


class ServiceController extends Controller
{
    public function index()
    {
        abort_if_cannot('view_services');
        $items = Service::query()->latest()->paginate(50);
        $items->getCollection()->transform(fn($i) => $this->transformItem($i));
        return response()->json($items);
    }
    
    public function store(Request $request, CreateServiceAction $action)
    {
        abort_if_cannot('add_services');
        $data = $this->prepareData($request);
        $dto = ServiceDTO::fromArray($data);
        $item = $action->execute($dto);
        return response()->json(['success' => true, 'data' => $this->transformItem($item)]);
    }
    
    public function update(Request $request, $id, UpdateServiceAction $action)
    {
        abort_if_cannot('edit_services');
        $item = Service::findOrFail($id);
        $data = $this->prepareData($request);
        $dto = ServiceDTO::fromArray($data);
        $item = $action->execute($item, $dto);
        return response()->json(['success' => true, 'data' => $this->transformItem($item)]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_services');
        try {
            $item = Service::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Ky rekord është i lidhur me të dhëna të tjera.'], 400);
        }
    }

    private function transformItem($item) {
        foreach (array (
  0 => 'name',
) as $f) {
            $val = $item->getRawOriginal($f);
            $item->setAttribute("{$f}_raw", is_string($val) && str_starts_with($val, '{') ? json_decode($val, true) : $val);
        }
        return $item;
    }

    private function prepareData(Request $request) {
        $data = $request->all();
        foreach (array (
  0 => 'name',
) as $f) {
            if (isset($data[$f]) && is_string($data[$f]) && str_starts_with($data[$f], '{')) $data[$f] = json_decode($data[$f], true);
        }
        return $data;
    }
}