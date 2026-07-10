<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Campaign;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $campaigns = Campaign::with('category')->latest()
            ->when($request->search, function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->search . '%');
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->category, function($query) use ($request) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('id', $request->category);
                });
            })
            ->when($request->date, function ($query) use ($request) {
                $date = explode(' to ', $request->date);
                $start = Carbon::parse($date[0])->startOfDay();
                $end = Carbon::parse($date[1])->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            })
            ->paginate(25);
        $status = $request->status;
        $date = $request->date;
        $search = $request->search;
        $category = $request->category;

        return view('backend.campaigns.index', compact('campaigns', 'status', 'date', 'search', 'category'));
    }

    public function create()
    {
        return view('backend.campaigns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:active,draft,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category' => 'required|exists:campaign_categories,id',
            'thumbnail' => 'required|numeric',
        ]);
        $validated['campaign_category_id'] = $request->category;
        $validated['slug'] = filled($request->slug) ? $request->slug : Str::slug($validated['title']);

        Campaign::create($validated);
        flash(('Campaign created successfully.'))->success();
        return redirect()->route('campaigns.index');
    }

    public function show($slug)
    {
        $campaign = Campaign::with('category')->where('slug', $slug)->active()->firstOrFail();

        $relatedCampaigns = Campaign::with('category')
            ->where('id', '!=', $campaign->id)
            ->where('status', 'active')
            ->latest()
            ->take(3)
            ->get();
        return view('frontend.campaign.show', compact('campaign', 'relatedCampaigns'));
    }

    public function edit($id)
    {
        $campaign = Campaign::findOrFail($id);
        return view('backend.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:active,draft,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category' => 'required|exists:campaign_categories,id',
            'thumbnail' => 'required|numeric',
        ]);

        $validated['campaign_category_id'] = $request->category;
        $validated['slug'] = filled($request->slug) ? $request->slug : Str::slug($validated['title']);
        $campaign->update($validated);
        flash(('Campaign updated successfully.'))->success();
        return redirect()->route('campaigns.index');
    }

    public function destroy($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->delete();
        flash(('Campaign deleted successfully.'))->success();
        return redirect()->route('campaigns.index');
    }

    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:campaigns,id',
            'status' => 'required|in:published,draft',
        ]);

        $count = Campaign::whereIn('id', $request->ids)->update([
            'status' => $request->status,
            'publish_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "$count campaigns updated successfully.",
        ]);
    }

    /**
     * Bulk delete campaigns
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:campaigns,id',
        ]);

        $count = Campaign::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "$count campaigns deleted successfully.",
        ]);
    }

    public function customerIndex()
    {
        $categories = \App\Models\CampaignCategory::active()
            ->latest()
            ->get();
        $campaigns = \App\Models\Campaign::active()
            ->latest()
            ->get();

        return view('frontend.campaign.index', compact('campaigns', 'categories'));
    }
}
