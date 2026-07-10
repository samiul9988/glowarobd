<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Notice;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $notices = Notice::with('category')->latest()
            ->when($request->search, function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->search . '%');
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->visibility, function ($query) use ($request) {
                $query->where('visibility', $request->visibility);
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
        $visibility = $request->visibility;
        $date = $request->date;
        $search = $request->search;
        $category = $request->category;

        return view('backend.notices.index', compact('notices', 'status', 'visibility', 'date', 'search', 'category'));
    }

    public function create()
    {
        return view('backend.notices.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:published,draft,scheduled',
            'publish_at' => 'nullable|required_if:status,scheduled|date|after_or_equal:now',
            'visibility' => 'required|in:customers,staffs,both',
            'category' => 'required|exists:notice_categories,id'
        ]);
        // dd($validated, Carbon::parse($request->publish_at)->format('Y-m-d H:i'));
        $validated['notice_category_id'] = $request->category;
        $validated['slug'] = filled($request->slug) ? $request->slug : Str::slug($validated['title']);

        Notice::create($validated);
        flash(('Notice created successfully.'))->success();
        return redirect()->route('notices.index');
    }

    public function show(Request $request, $slug)
    {
        $notice = \App\Models\Notice::with('category:id,name,slug')
            ->where('slug', $slug)
            ->published()
            ->when(!$request->user() || $request->user()->user_type == 'customer', function($query) {
                return $query->visibleFor('customers');
            })
            ->firstOrFail();

        return view('frontend.notice.show', compact('notice'));
        // $notice = Notice::with('category')->findOrFail($id);
        // return view('backend.notices.show', compact('notice'));
    }

    public function edit($id)
    {
        $notice = Notice::findOrFail($id);
        return view('backend.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:published,draft,scheduled',
            'publish_at' => 'nullable|required_if:status,scheduled|date|after_or_equal:now',
            'visibility' => 'required|in:customers,staffs,both',
            'category' => 'required|exists:notice_categories,id'
        ]);

        $validated['notice_category_id'] = $request->category;
        $validated['slug'] = filled($request->slug) ? $request->slug : Str::slug($validated['title']);
        $notice->update($validated);
        flash(('Notice updated successfully.'))->success();
        return redirect()->route('notices.index');
    }

    public function destroy($id)
    {
        $notice = Notice::findOrFail($id);
        $notice->delete();
        flash(('Notice deleted successfully.'))->success();
        return redirect()->route('notices.index');
    }

    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:notices,id',
            'status' => 'required|in:published,draft',
        ]);

        $count = Notice::whereIn('id', $request->ids)->update([
            'status' => $request->status,
            'publish_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "$count notices updated successfully.",
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

        $count = Notice::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "$count notices deleted successfully.",
        ]);
    }

    public function customerIndex()
    {
        abort(404, ('No page found'));
        $categories = \App\Models\NoticeCategory::active()
            ->latest()
            ->get();
        $notices = \App\Models\Notice::published()
            ->visibleFor('customers')
            ->latest()
            ->get();

        return view('frontend.notice.index', compact('notices', 'categories'));
    }
}
