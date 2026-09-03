<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        abort_if_cannot('view_customers');
        return response()->json(Customer::query()->->latest()->paginate(50));
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_customers');
        $validated = $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'photo' => 'required',
        ]);
        $item = Customer::create($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_customers');
        $item = Customer::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'photo' => 'required',
        ]);
        $item->update($validated);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        abort_if_cannot('delete_customers');
        $item = Customer::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true]);
    }
}
