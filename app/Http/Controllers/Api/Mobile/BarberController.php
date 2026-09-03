<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Barber;
use Illuminate\Http\Request;

class BarberController extends Controller
{
    public function index()
    {
        abort_if_cannot('view_barbers');
        return response()->json(Barber::query()->->latest()->paginate(50));
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_barbers');
        $validated = $request->validate([
            'name' => 'required',
            'specialization' => 'required',
            'phone' => 'required',
            'commission_rate' => 'required',
            'photo' => 'required',
        ]);
        $item = Barber::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_barbers');
        $item = Barber::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required',
            'specialization' => 'required',
            'phone' => 'required',
            'commission_rate' => 'required',
            'photo' => 'required',
        ]);
        $item->update($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_barbers');
        $item = Barber::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true]);
    }
}
