<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\HighlightedItem;
use App\Http\Requests\HighlightedItemRequest;
use Illuminate\Validation\ValidationException;

class HighlightedItemController extends Controller
{
    public function index(Request $request)
    {
        $highlightedItems = HighlightedItem::with('linkable')
            ->when(filled($request->type), function ($q) use ($request) {
                $type = match ($request->type) {
                    'product' => 'App\Models\Product',
                    'brand' => 'App\Models\Brand',
                    'category' => 'App\Models\Category',
                    'custom' => null,
                };
                return $q->where('linkable_type', $type);
            })
            ->when(filled(trim($request->search)), function ($q) use ($request) {
                return $q->where('title', 'like', '%'.$request->search.'%');
            })
            ->latest()
            ->orderBy('position', 'asc')
            // ->get();
            ->paginate(20);

        // dd($highlightedItems->toArray());
        return view("backend.product.highlighted.index", compact('highlightedItems'));
    }

    public function create()
    {
        return view("backend.product.highlighted.create");
    }

    public function store(HighlightedItemRequest $request)
    {
        // dd($request->all());
        try {
            $data = $this->prepareData($request);
            HighlightedItem::create($data);

            flash('Highlighted item created successfully.')->success();
            return redirect()->route('highlightedProduct.index');
        } catch (\Exception $e) {
            flash($e->getMessage())->error();
            return redirect()->route('highlightedProduct.index');
        }
    }

    public function edit($id)
    {
        $highlightedItem = HighlightedItem::findOrFail($id);
        return view("backend.product.highlighted.edit", compact('highlightedItem'));
    }

    public function update(HighlightedItemRequest $request, $id)
    {
        // dd($request->all());
        try {
            $highlightedProduct = HighlightedItem::findOrFail($id);
            $data = $this->prepareData($request, $highlightedProduct);
            $data['position'] = $request->position ?? $highlightedProduct->position;
            $highlightedProduct->update($data);

            flash('Item updated successfully.')->success();
            return redirect()->route('highlightedProduct.index');
        } catch (\Exception $e) {
            flash($e->getMessage())->error();
            return back();
        }
    }

    private function prepareData(HighlightedItemRequest $request, $existing = null): array
    {
        $highlights = [];
        foreach ($request->highlight_icons as $index => $icon) {
            $label = $request->highlight_labels[$index] ?? '';
            if ($icon && $label) {
                $highlights[] = ['icon' => $icon, 'label' => $label];
            }
        }

        $allowedTypes = ['product', 'brand', 'category'];
        if (in_array($request->linkable_type, $allowedTypes)) {
            $linkableType = "App\\Models\\" . ucfirst($request->linkable_type);
            $linkableId   = $request->linkable_id;
            $customLink   = null;
        } else {
            $linkableType = null;
            $linkableId   = null;
            $customLink   = $request->custom_link;
        }

        return [
            'title'         => trim($request->title),
            'subtitle'      => trim($request->subtitle),
            'description'   => trim($request->description),
            'linkable_type' => $linkableType,
            'linkable_id'   => $linkableId,
            'custom_link'   => trim($customLink),
            'banner_img'    => $request->banner,
            'highlights'    => $highlights,
            'status'        => $request->status ?? true,
            'button_text'   => trim($request->button_text),
        ];
    }

    public function destroy($id)
    {
        $highlightedProduct = HighlightedItem::findOrFail($id);
        $highlightedProduct->delete();

        flash('Item deleted successfully.')->success();
        return redirect()->route('highlightedProduct.index');
    }

    public function bulkDestroy(Request $request) {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No items selected for deletion.',
            ], 400);
        }

        HighlightedItem::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected items deleted successfully.',
        ]);
    }

    public function touch(Request $request, $id)
    {
        $highlightedItem = HighlightedItem::findOrFail($id);
        $highlightedItem->status = $request->status ?? $highlightedItem->status;
        $highlightedItem->position = $request->position ?? $highlightedItem->position;
        $highlightedItem->save();

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $highlightedItem->status,
                'position' => $highlightedItem->position,
            ]
        ]);
    }
}
