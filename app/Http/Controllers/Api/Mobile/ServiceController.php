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
        return response()->json(Service::query()->->latest()->paginate(50));
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_services');
        $validated = $request->validate([
            'name' => 'required',
            'price' => 'required',
            'duration_minutes' => 'required',
        ]);
        $item = Service::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_services');
        $item = Service::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required',
            'price' => 'required',
            'duration_minutes' => 'required',
        ]);
        $item->update($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_services');
        $item = Service::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true]);
    }
}
