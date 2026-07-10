<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'slug'        => $this->slug,
            'description' => $this->description,
            'thumbnail'   => $this->thumbnail ? api_asset($this->thumbnail) : null,
            'video_url'   => $this->attachment ? api_asset($this->attachment) : $this->video_url,
            'views_count' => (int) $this->views,
            'type'        => $this->type,
            'products_count' => $this->products?->count() ?? 0,
            'products'    => SimpleProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
