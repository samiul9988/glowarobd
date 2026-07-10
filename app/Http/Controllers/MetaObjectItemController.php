<?php

namespace App\Http\Controllers;

use App\Models\MetaObject;
use Illuminate\Http\Request;
use App\Models\MetaObjectItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MetaObjectItemController extends Controller
{
    public function index(Request $request)
    {
        $items = MetaObjectItem::with('metaObject:id,name')->latest();
        $search = $request->search ?? null;
        $group = $request->group ?? null;
        $group_id = null;

        if(filled($group)){
            session()->put('group', $group);
            $group_id = MetaObject::where('name', $group)->first()?->id;
        }else{
            session()->forget('group');
        }

        if(filled($group_id)){
            $items->where('meta_object_id', $group_id);
        }

        // Apply search filter if the search term is present
        if ($search) {
            $items->where('title', 'LIKE', '%' . $search . '%');
        }

        $items = $items->paginate(15);

        return view('backend.meta_objects.items.index', compact('items', 'search', 'group', 'group_id'));
    }

    public function create(Request $request)
    {
        $group = $request->group ?? null;
        $selectedMetaObject = null;
        $metaObjects = MetaObject::active()->pluck('name', 'id');
        if (filled($group)) {
            session()->put('group', $group);
            $selectedMetaObject = MetaObject::where('name', $group)->first()?->id;
        }

        // dd($selectedMetaObject);
        return view('backend.meta_objects.items.create', compact('metaObjects', 'selectedMetaObject', 'group'));
    }

    public function store(Request $request)
    {
        $group = session()->pull('group');
        // dd($request->all());
        $request->validate([
            'title' => 'required',
            'meta_object_id' => 'required',
            'is_active' => 'nullable|boolean',
        ], [
            'meta_object_id.required' => 'The meta object field is required.'
        ]);

        if(MetaObjectItem::where('title', $request->title)->where('meta_object_id', $request->meta_object_id)->exists()){
            flash('Item already exists')->error();
            return back()->withInput();
        }
        MetaObjectItem::create($request->all());

        flash('Item created successfully')->success();
        if ($group) {
            return redirect()->route('meta-object-items.index', ['group' => $group]);
        }
        return redirect()->route('meta-object-items.index');
    }

    public function edit($id)
    {
        $item = MetaObjectItem::findOrFail($id);
        $metaObjects = MetaObject::active()->pluck('name', 'id');
        return view('backend.meta_objects.items.edit', compact('item', 'metaObjects'));
    }

    public function update(Request $request, $id)
    {
        $group = session()->pull('group');
        // dd($group);
        $request->validate([
            'title' => 'required',
            'is_active' => 'nullable|boolean',
        ]);

        $item = MetaObjectItem::findOrFail($id);
        $item->update($request->all());

        flash('Item updated successfully')->success();
        if ($group) {
            return redirect()->route('meta-object-items.index', ['group' => $group]);
        }
        return redirect()->route('meta-object-items.index');
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'boolean',
        ]);

        $item = MetaObjectItem::findOrFail($id);
        $item->update([
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
            $group = session()->pull('group');
            // dd($group);
            $item = MetaObjectItem::findOrFail($id);
            $item->delete();

            flash(('Meta object deleted successfully'))->success();
            if ($group) {
                return redirect()->route('meta-object-items.index', ['group' => $group]);
            }
            return redirect()->route('meta-object-items.index');
        } catch(ModelNotFoundException $e) {
            flash(('Meta object not found'))->error();
            return back();
        } catch (\Exception $e) {
            flash(('Something went wrong'))->error();
            return back();
        }
    }

    public function bulk_delete(Request $request) {
        if($request->id) {
            foreach ($request->id as $product_id) {
                $item = MetaObjectItem::find($product_id);
                if($item) {
                    $item->delete();
                }
            }
        }
        flash(('Meta objects deleted successfully'))->success();
        return 1;
    }
}
