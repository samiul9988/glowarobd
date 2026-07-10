<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VideoPlaylistCollection extends ResourceCollection
{
    public $collects = VideoPlaylistResource::class;

    public function toArray($request)
    {
        return [
            'data' => $this->collection,
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
