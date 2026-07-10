<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CampaignCategory;
use Illuminate\Validation\ValidationException;

class CampaignCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = CampaignCategory::with('campaigns')->latest()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->paginate(25);
        $status = $request->status;
        $search = $request->search;

        return view('backend.campaigns.categories.index', compact('categories', 'status', 'search'));
    }

    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:campaign_categories,name',
                'status' => 'required|in:0,1',
            ]);

            $validated['slug'] = filled($request->slug) ? Str::slug($request->slug) : Str::slug($validated['name']);

            CampaignCategory::create($validated);
            flash(('Category created successfully.'))->success();
            return redirect()->route('campaign-categories.index');
        } catch (ValidationException $e) {
            flash(($e->validator->errors()->first()))->error();
            return redirect()->back()->withInput();
        }
        catch (\Exception $e) {
            flash(('Server Error.'))->error();
            return redirect()->back()->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:campaign_categories,name,' . $id,
                'status' => 'required|in:0,1',
            ]);

            $validated['slug'] = filled($request->slug) ? Str::slug($request->slug) : Str::slug($validated['name']);

            $category = CampaignCategory::findOrFail($id);

            $category->update($validated);
            flash(('Category updated successfully.'))->success();
            return redirect()->route('campaign-categories.index');
        } catch (ValidationException $e) {
            flash(($e->validator->errors()->first()))->error();
            return redirect()->back()->withInput();
        }
        catch (\Exception $e) {
            flash(('Server Error.'))->error();
            return redirect()->back()->withInput();
        }
    }

    public function destroy($id)
    {
        $category = CampaignCategory::findOrFail($id);
        $category->campaigns()->delete();
        $category->delete();
        flash(('Category deleted successfully.'))->success();
        return redirect()->route('campaign-categories.index');
    }

    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:campaigns,id',
            'status' => 'required|in:0,1',
        ]);

        $count = CampaignCategory::whereIn('id', $request->ids)->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => "$count categories updated successfully.",
        ]);
    }

    /**
     * Bulk delete notices
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:notices,id',
        ]);

        $categories = CampaignCategory::whereIn('id', $request->ids)->get();
        $count = $categories->count();

        foreach($categories as $category){
            $category->campaigns()->delete();
            $category->delete();
        }

        return response()->json([
            'success' => true,
            'message' => "$count categories deleted successfully.",
        ]);
    }
}
