<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        abort_if_cannot('view_services');
        return response()->json(Service::query()->latest()->paginate(50));
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_services');
        $rules = method_exists(Service::class, 'rules') ? Service::rules() : [];
        if (empty($rules)) {
            $rules = collect((new Service)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray();
        }
        $validated = $request->validate($rules);
        $item = Service::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_services');
        $item = Service::findOrFail($id);
        $rules = method_exists(Service::class, 'rules') ? Service::rules($id) : [];
        if (empty($rules)) {
            $rules = collect((new Service)->getFillable())->mapWithKeys(fn($f) => [$f => 'required'])->toArray();
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
        abort_if_cannot('delete_services');
        Service::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
