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
        return response()->json(Barber::latest()->paginate(50));
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_barbers');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'nullable|string',
            'phone' => 'nullable|string',
            'commission_rate' => 'required|numeric',
            'photo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $name);
            $data['photo'] = 'uploads/' . $name;
        }

        $item = Barber::create($data);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_barbers');
        $item = Barber::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'nullable|string',
            'phone' => 'nullable|string',
            'commission_rate' => 'required|numeric',
            'photo' => 'nullable'
        ]);

        if ($request->hasFile('photo')) {
            if ($item->photo && file_exists(public_path($item->photo))) @unlink(public_path($item->photo));
            $file = $request->file('photo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $name);
            $data['photo'] = 'uploads/' . $name;
        } else {
            unset($data['photo']);
        }

        $item->update($data);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_barbers');
        $item = Barber::findOrFail($id);
        if ($item->photo && file_exists(public_path($item->photo))) @unlink(public_path($item->photo));
        $item->delete();
        return response()->json(['success' => true]);
    }
}
