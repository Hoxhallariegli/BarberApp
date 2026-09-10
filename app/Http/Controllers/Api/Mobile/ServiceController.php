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
        $items = Service::latest()->paginate(50);
        $items->getCollection()->transform(function($i) {
            $i->append('translated_name');
            return $i;
        });
        return response()->json($items);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_services');
        $data = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'duration_minutes' => 'required|integer',
            'image' => 'nullable|image|max:2048'
        ]);

        if (is_string($data['name'])) {
            $data['name'] = json_decode($data['name'], true) ?? $data['name'];
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $name);
            $data['image'] = 'uploads/' . $name;
        }

        $item = Service::create($data);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_services');
        $item = Service::findOrFail($id);

        $data = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'duration_minutes' => 'required|integer',
            'image' => 'nullable'
        ]);

        if (is_string($data['name'])) {
            $data['name'] = json_decode($data['name'], true) ?? $data['name'];
        }

        if ($request->hasFile('image')) {
            if ($item->image && file_exists(public_path($item->image))) @unlink(public_path($item->image));
            $file = $request->file('image');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $name);
            $data['image'] = 'uploads/' . $name;
        } else {
            unset($data['image']);
        }

        $item->update($data);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_services');
        $item = Service::findOrFail($id);
        if ($item->image && file_exists(public_path($item->image))) @unlink(public_path($item->image));
        $item->delete();
        return response()->json(['success' => true]);
    }
}
