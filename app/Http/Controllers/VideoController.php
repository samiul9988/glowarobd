<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Models\Video;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\VideoPlaylist;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $videos = Video::latest()
            ->with('playlists:id,name')
            ->withCount([
                'products',
                'playlists'
            ])
            ->when(filled($request->category), function ($query) use ($request) {
                return $query->whereHas('playlists', function ($q) use ($request) {
                    $q->where('video_playlists.id', $request->category);
                });
            })
            ->when(filled($request->search), function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    if (filled($request->search)) {
                        $q->where('title', 'LIKE', '%' . $request->search . '%');
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
                return $query->where('status', $request->status);
            })
            ->paginate(25);

        return view('backend.videos.index', compact('videos'));
    }

    public function create(Request $request)
    {
        $videoFile = Upload::find($request->video_id);
        return view('backend.videos.create', compact('videoFile'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:videos,slug',
                'description' => 'nullable|string',
                'thumbnail' => 'required',
                'video_url' => 'nullable|url',
                'attachment' => 'nullable|required_if:video_url,null|integer',
                'type' => 'sometimes|in:default,reel',
                'playlists' => 'required|array',
                'playlists.*' => 'required|integer',
                'products' => 'nullable|array',
                'products.*' => 'nullable|integer',
                'status' => 'boolean',
            ]);

            // Generate slug if not provided
            $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
            $validated['status'] = $validated['status'] ?? 1;
            $validated['featured'] = $request->has('featured') ? 1 : 0;

            $video = Video::create($validated);

            if(!empty($request->products)){
                $video->products()->sync($request->products);
            }
            if(!empty($request->playlists)){
                $video->playlists()->sync($request->playlists);
            }

            Cache::forget('filter_video_playlists');

            if($request->ajax() || $request->wantsJson()){
                return response()->json([
                    'success' => true,
                    'message' => 'Video created successfully.',
                    'data' => $video
                ]);
            }

            flash(('Video created successfully.'))->success();
            return redirect()->route('videos.index');
        } catch (ValidationException $e) {
            if($request->ajax() || $request->wantsJson()){
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->first(),
                    'errors' => $e->errors()
                ], 422);
            }
            flash(('Failed to create video.'))->error();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if($request->ajax() || $request->wantsJson()){
                return response()->json([
                    'success' => false,
                    'message' => 'Server Error: '.$e->getMessage(),
                ], 500);
            }
            flash(('Failed to create video.'))->error();
            return redirect()->back()->withInput();
        }
    }

    public function show($slug)
    {
        $video = Video::with('product', 'playlist:id,name')->where('slug', $slug)->firstOrFail();

        dd($video);
        return view('frontend.videos.show', compact('video'));
    }

    public function edit($id)
    {
        $video = Video::with('products', 'playlists')->find($id);

        abort_if(!$video, 404);

        $videoFile = Upload::find($video->attachment);
        $selectedProducts = $video->products->pluck('id')->toArray();
        $selectedPlaylists = $video->playlists->pluck('id')->toArray();

        return view('backend.videos.edit', compact('video', 'videoFile', 'selectedProducts', 'selectedPlaylists'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:videos,slug,'.$id,
                'description' => 'nullable|string',
                'thumbnail' => 'required',
                'video_url' => 'nullable|url',
                'attachment' => 'nullable|required_if:video_url,null|integer',
                'type' => 'sometimes|in:default,reel',
                'playlists' => 'required|array',
                'playlists.*' => 'required|integer',
                'products' => 'nullable|array',
                'products.*' => 'nullable|integer',
                'status' => 'boolean',
            ]);

            // Generate slug if not provided
            $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
            $validated['status'] = $validated['status'] ?? 1;
            $validated['featured'] = $request->has('featured') ? 1 : 0;

            $video = Video::find($id);
            if(!$video){
                if($request->ajax() || $request->wantsJson()){
                    return response()->json([
                        'success' => false,
                        'message' => 'Video not found.',
                    ], 404);
                }
                abort(404);
            }
            $video->update($validated);

            if(!empty($request->products)){
                $video->products()->sync($request->products);
            }
            if(!empty($request->playlists)){
                $video->playlists()->sync($request->playlists);
            }

            Cache::forget('filter_video_playlists');
            if($request->ajax() || $request->wantsJson()){
                return response()->json([
                    'success' => true,
                    'message' => 'Video updated successfully.',
                    'data' => $video
                ]);
            }

            flash(('Video updated successfully.'))->success();
            return redirect()->route('videos.index');
        } catch (ValidationException $e) {
            if($request->ajax() || $request->wantsJson()){
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->first(),
                    'errors' => $e->errors()
                ], 422);
            }
            flash(('Failed to update video.'))->error();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if($request->ajax() || $request->wantsJson()){
                return response()->json([
                    'success' => false,
                    'message' => 'Server Error: '.$e->getMessage(),
                ], 500);
            }
            flash('Server Error!')->error();
            return redirect()->back()->withInput();
        }
    }

    public function touch(Request $request)
    {
        $video = Video::findOrFail($request->id);

        if($request->has('status')) {
            $video->status = $request->status ? 1 : 0;
            $message = "Status updated successfully.";
        } elseif($request->has('featured')) {
            $video->featured = $request->featured ? 1 : 0;
            $message = "Featured status updated successfully.";
        } else {
            return response()->json([
                'success' => false,
                'message' => "No valid field to update.",
            ], 400);
        }

        $video->save();

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function destroy($id)
    {
        $video = Video::findOrFail($id);
        $video->products()->detach();
        $video->playlists()->detach();
        $video->delete();
        Cache::forget('filter_video_playlists');
        flash(('Video deleted successfully.'))->success();
        return redirect()->route('videos.index');
    }


    /**
     * Bulk delete notices
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:videos,id',
        ]);

        $videos = Video::whereIn('id', $request->ids)->get()->each(function($video) {
            $video->products()->detach();
            $video->playlists()->detach();
            $video->delete();
        });
        Cache::forget('filter_video_playlists');
        return response()->json([
            'success' => true,
            'message' => "{$videos->count()} videos deleted successfully.",
        ]);
    }

    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:videos,id',
            'status' => 'required|boolean',
        ]);

        $count = Video::whereIn('id', $request->ids)->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => "$count videos status updated successfully.",
        ]);
    }
}
