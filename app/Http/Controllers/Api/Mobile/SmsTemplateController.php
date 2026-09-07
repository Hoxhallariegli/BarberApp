<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    public function index()
    {
        $items = SmsTemplate::query()->latest()->paginate(50);
        $items->getCollection()->transform(fn($i) => $this->transformItem($i));
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $data = $this->prepareData($request);
        $rules = method_exists(SmsTemplate::class, 'rules') ? SmsTemplate::rules() : [];
        $validated = validator($data, $rules ?: collect((new SmsTemplate)->getFillable())->mapWithKeys(fn($f)=>[$f=>'required'])->toArray())->validate();

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $name);
            $validated['photo'] = 'uploads/' . $name;
        }

        $item = SmsTemplate::create($validated);
        return response()->json(['success' => true, 'data' => $this->transformItem($item)]);
    }

    public function update(Request $request, $id)
    {
        $item = SmsTemplate::findOrFail($id);
        $data = $this->prepareData($request);
        $rules = method_exists(SmsTemplate::class, 'rules') ? SmsTemplate::rules($id) : [];
        $validated = validator($data, $rules ?: collect((new SmsTemplate)->getFillable())->mapWithKeys(fn($f)=>[$f=>'required'])->toArray())->validate();

        if ($request->hasFile('photo')) {
            if ($item->photo && file_exists(public_path($item->photo))) @unlink(public_path($item->photo));
            $file = $request->file('photo');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $name);
            $validated['photo'] = 'uploads/' . $name;
        }

        $item->update($validated);
        return response()->json(['success' => true, 'data' => $this->transformItem($item)]);
    }

    public function destroy($id)
    {
        $item = SmsTemplate::findOrFail($id);
        if ($item->photo && file_exists(public_path($item->photo))) @unlink(public_path($item->photo));
        $item->delete();
        return response()->json(['success' => true]);
    }

    private function transformItem($item) {
        foreach (array (
  0 => 'body',
) as $f) {
            $val = $item->getRawOriginal($f);
            $item->setAttribute("{$f}_raw", is_string($val) && str_starts_with($val, '{') ? json_decode($val, true) : $val);
        }
        return $item;
    }

    private function prepareData(Request $request) {
        $data = $request->all();
        foreach (array (
  0 => 'body',
) as $f) {
            if (isset($data[$f]) && is_string($data[$f]) && str_starts_with($data[$f], '{')) $data[$f] = json_decode($data[$f], true);
        }
        return $data;
    }
}