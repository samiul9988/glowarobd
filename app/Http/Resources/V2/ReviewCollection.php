<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReviewCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $photo_paths = isset($data->photos) ? get_images_path($data->photos) : [];
                $photos = [];
                if (!empty($photo_paths)) {
                    for ($i = 0; $i < count($photo_paths); $i++) {
                        if ($photo_paths[$i] != "" ) {
                            $item = array();
                            $item['path'] = $photo_paths[$i];
                            $photos[]= $item;
                        }
                    }
                }
                return [
                    'user_id'=> $data->user_id ?? $data->user?->id ?? '',
                    'user_name'=> $data->hide_username ? 'Anonymous User' : ($data->name ?? $data->user?->name ?? ''),
                    'hide_username'=> $data->hide_username == false ? false:true,
                    'avatar'=> $data->hide_username == false?api_asset($data->user?->avatar_original ?? '') : '',
                    'rating' => floatval(number_format($data->rating,1,'.','')),
                    'comment' => $data->comment,
                    'photos' => $photos,
                    'time' => $data->updated_at->diffForHumans()
                ];
            })
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
