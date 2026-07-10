<?php

namespace App\Http\Controllers\Api\V3;

use Illuminate\Http\Request;
use App\Models\VideoPlaylist;
use App\Http\Controllers\Controller;
use App\Http\Resources\V3\VideoPlaylistCollection;

class VideoPlaylistController extends Controller
{
    public function featuredPlaylists(Request $request)
    {
        $playlists = VideoPlaylist::active()
            ->featured()
            ->latest()
            ->whereHas('videos', function ($q) use ($request) {
                $q->active()->featured();

                if (filled($request->video_type)) {
                    $q->where('type', $request->video_type === 'reels' ? 'reel' : 'default');
                }
            })
            ->with([
                'videos' => function ($query) use ($request) {
                    $query->active()
                        ->featured()
                        ->when(filled($request->video_type), function ($q) use ($request) {
                            return $q->where('type', $request->video_type === 'reels' ? 'reel' : 'default');
                        })
                        ->inRandomOrder();
                },
                'videos.products' => function ($query) {
                    $query->published();
                },
            ])
            ->limit($request->limit ?? 10)
            ->get();

        return new VideoPlaylistCollection($playlists);
    }
}
