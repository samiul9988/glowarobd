<?php

namespace App\Http\Controllers;

use App\Models\MetaObject;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetaObjectController extends Controller
{
    public function index(Request $request)
    {
        $metaObjects = MetaObject::query();
        $search = $request->search ?? null;
        if ($request->search != null){
            $metaObjects->where('name', 'LIKE', '%'.$request->search.'%');
        }
        $metaObjects = $metaObjects->paginate(15);
        return view('backend.meta_objects.index', compact('metaObjects', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'is_active' => 'nullable|boolean',
        ]);

        MetaObject::create($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Custom field created successfully'
        ]);
        // return redirect()->route('products.custom_fields.index')->with('success', 'Custom field created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'is_active' => 'nullable|boolean',
        ]);

        $metaObject = MetaObject::findOrFail($id);
        $metaObject->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Custom field updated successfully'
        ]);
        // return redirect()->route('products.custom_fields.index')->with('success', 'Custom field updated successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'boolean',
        ]);

        $metaObject = MetaObject::findOrFail($id);
        $metaObject->update([
            'is_active' => $request->is_active
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }

    public function destroy($id)
    {
        try{
            $metaObject = MetaObject::findOrFail($id);
            $metaObject->delete();

            flash(('Meta object deleted successfully'))->success();
            return redirect()->route('meta-objects.index');
        } catch (\Exception $e) {
            flash(("You can't delete this. It contains items."))->error();
            return back();
        }
    }

    public function bulk_delete(Request $request) {
        try{
            DB::beginTransaction();
            if($request->id) {
                foreach ($request->id as $product_id) {
                    $metaObject = MetaObject::find($product_id);
                    if($metaObject) {
                        $metaObject->delete();
                    }
                }
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Meta objects deleted successfully'
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

    public function show($id)
    {
        try{
            $metaObject = MetaObject::with('items')->findOrFail($id);
            $items = $metaObject->items->filter(function($item){
                return $item->is_active;
            })->map(function($item){
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $items->toArray(),
                'metaId' => $metaObject->id,
                'metaName' => $metaObject->name
            ]);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Meta object not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error'
            ], 500);
        }
    }
}
