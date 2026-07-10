<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoPlaylistResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'title'       => $this->name,
            'slug'       => $this->slug,
            'description' => $this->description,
            'thumbnail'   => $this->thumbnail ? api_asset($this->thumbnail) : null,
            'videos_count' => $this->videos?->count() ?? 0,
            'videos'      => VideoResource::collection($this->whenLoaded('videos')),
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
