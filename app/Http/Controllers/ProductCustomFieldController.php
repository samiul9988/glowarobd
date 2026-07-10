<?php

namespace App\Http\Controllers;

use App\Models\ProductCustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductCustomFieldController extends Controller
{
    public function index(Request $request)
    {
        $customFields = ProductCustomField::latest();
        $search = $request->search ?? null;
        if ($request->search != null){
            $customFields->where('name', 'LIKE', '%'.$request->search.'%');
        }
        $customFields = $customFields->paginate(15);
        return view('backend.product_custom_fields.index', compact('customFields', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:product_custom_fields,name',
            'type' => 'required',
            'banner' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        ProductCustomField::create($validated);
        Cache::forget('custom_fields_for_product_create');
        return response()->json([
            'success' => true,
            'message' => 'Custom field created successfully'
        ]);
        // return redirect()->route('products.custom_fields.index')->with('success', 'Custom field created successfully');
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'banner' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $customField = ProductCustomField::findOrFail($id);
        $customField->update($validated);
        Cache::forget('custom_fields_for_product_create');
        return response()->json([
            'success' => true,
            'message' => 'Custom field updated successfully'
        ]);
        // return redirect()->route('products.custom_fields.index')->with('success', 'Custom field updated successfully');
    }
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'is_active' => 'boolean',
        ]);

        $customField = ProductCustomField::findOrFail($id);
        $customField->update([
            'is_active' => $validated['is_active'] ?? $customField->is_active,
        ]);
        Cache::forget('custom_fields_for_product_create');
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }

    public function destroy($id)
    {
        try{
            $customField = ProductCustomField::findOrFail($id);
            $customField->fieldsData()->delete();
            $customField->delete();
            Cache::forget('custom_fields_for_product_create');
            flash(('Custom field deleted successfully'))->success();
            return redirect()->route('products.custom_fields.index');
        }catch (\Exception $e){
            if($e->getCode() == 23000){
                flash(('You can\'t delete this. It contains items.'))->error();
                return back();
            }
            flash(('Something went wrong'))->error();
            return back();
        }
    }

    public function bulk_delete(Request $request) {
        try{
            DB::beginTransaction();
            if($request->id) {
                foreach ($request->id as $id) {
                    $customField = ProductCustomField::find($id);
                    if($customField) {
                        $customField->fieldsData()->delete();
                        $customField->delete();
                    }
                }
            }
            DB::commit();
            Cache::forget('custom_fields_for_product_create');
            return response()->json([
                'success' => true,
                'message' => 'Custom fields deleted successfully'
            ]);
        }catch (\Exception $e){
            DB::rollBack();
            if($e->getCode() == 23000){
                return response()->json([
                    'success' => false,
                    'message' => 'You can\'t delete this. It contains items.'
                ], 500);
            }
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }
}
