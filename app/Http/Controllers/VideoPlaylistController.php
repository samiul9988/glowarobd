<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\VideoPlaylist;

class VideoPlaylistController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status === 'active' ? 1 : ($request->status === 'inactive' ? 0 : null);
        $playlists = VideoPlaylist::latest()
            ->withCount('videos')
            ->when(filled($request->search), function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    if (filled($request->search)) {
                        $q->where('name', 'LIKE', '%' . $request->search . '%');
                    }
                });
            })
            ->when(filled($request->date), function ($query) use ($request) {
                $daterange = explode(' to ', $request->date);
                if(count($daterange) == 2) {
                    $startDate = \Carbon\Carbon::parse($daterange[0])->startOfDay();
                    $endDate = \Carbon\Carbon::parse($daterange[1])->endOfDay();
                    return $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            })
            ->when(filled($request->status), function ($query) use ($request) {
                return $query->where('status', $request->status === 'active' ? 1 : 0);
            })
            ->paginate(25);
        return view('backend.videos.playlists.index', compact('playlists'));
    }

    public function create()
    {
        return view('backend.videos.playlists.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:video_playlists,name',
            'slug' => 'nullable|string|max:255|unique:video_playlists,slug',
            'description' => 'nullable|string',
            'thumbnail' => 'required',
            'status' => 'required|boolean',
        ]);

        // Generate slug if not provided
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['featured'] = $request->has('featured') ? 1 : 0;

        $playlist = VideoPlaylist::create($validated);

        flash('Video Playlist created successfully.')->success();
        return redirect()->route('video-playlists.index');
    }

    public function show($id)
    {
        // Logic to show a specific video playlist
        return view('admin.video_playlists.show', compact('id'));
    }

    public function edit($id)
    {
        $playlist = VideoPlaylist::findOrFail($id);
        return view('backend.videos.playlists.edit', compact('playlist'));
    }

    public function update(Request $request, $id)
    {
        $playlist = VideoPlaylist::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:video_playlists,name,' . $playlist->id,
            'slug' => 'nullable|string|max:255|unique:video_playlists,slug,' . $playlist->id,
            'description' => 'nullable|string',
            'thumbnail' => 'required',
            'status' => 'required|boolean',
        ]);

        // Generate slug if not provided
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['featured'] = $request->has('featured') ? 1 : 0;

        $playlist->update($validated);

        flash('Video Playlist updated successfully.')->success();
        return redirect()->route('video-playlists.index');
    }

    public function destroy($id)
    {
        $playlist = VideoPlaylist::withCount('videos')->findOrFail($id);

        if ($playlist->videos_count > 0) {
            flash('This playlist cannot be deleted because it contains videos.')->error();
        }else{
            $playlist->delete();
            flash('Video Playlist deleted successfully.')->success();
        }

        return redirect()->route('video-playlists.index');
    }


    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:video_playlists,id',
            'status' => 'required|boolean',
        ]);

        $count = VideoPlaylist::whereIn('id', $request->ids)->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => "$count playlists status updated successfully.",
        ]);
    }

    public function touch(Request $request)
    {
        $playlist = VideoPlaylist::findOrFail($request->id);

        if($request->has('status')) {
            $playlist->status = $request->status ? 1 : 0;
            $message = "Status updated successfully.";
        } elseif($request->has('featured')) {
            $playlist->featured = $request->featured ? 1 : 0;
            $message = "Featured status updated successfully.";
        } else {
            return response()->json([
                'success' => false,
                'message' => "No valid field to update.",
            ], 400);
        }

        $playlist->save();

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function getPlaylists(Request $request)
    {
        $playlists = VideoPlaylist::latest()->active()
            ->when(filled($request->search) || filled($request->selected), function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    if (filled($request->search)) {
                        $q->where('name', 'LIKE', '%' . $request->search . '%');
                    }
                    if (filled($request->selected)) {
                        $q->orWhere('id', $request->selected);
                    }
                });
            })
            ->limit(100)
            ->pluck('name', 'id')
            ->toArray();

        return response()->json($playlists);
    }
}
