<?php

namespace App\Http\Resources\Merchant;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Utility\CategoryUtility;

class CategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($category) {
                $data = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ];
                if($category->parent_id == 0){
                    $data['sub_categories'] = $category->childrens->map(function($subCategory){
                        return [
                            'id' => $subCategory->id,
                            'name' => $subCategory->name,
                            'slug' => $subCategory->slug
                        ];
                    });
                }
            })
        ];
    }

    // exclude the 'meta' key from the response
    public function withResponse($request, $response)
    {
        $data = json_decode($response->getContent(), true);
        unset($data['meta'], $data['links']);
        $response->setContent(json_encode($data));
    }

    public function with($request)
    {
        return [
            'success' => true,
            'message' => 'Categories fetched successfully',
            'pagination' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage()
            ]
        ];
    }
}
