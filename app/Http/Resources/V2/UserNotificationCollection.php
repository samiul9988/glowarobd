<?php

namespace App\Http\Resources\V2;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class UserNotificationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data){
                $web_url = '';
                if($data->type=='product'){
                    $product = Product::find(intval($data->url));
                    if ($product) {
                        $web_url = \Illuminate\Support\Facades\Route::has('product')
                            ? to_frontend(route('product', $product->slug))
                            : url('/product/'.$product->slug);
                    }
                }elseif($data->type=='brand'){
                    $web_url = to_frontend(route('products.brand', Brand::find(intval($data->url))->slug), 'brand');
                }elseif($data->type=='category'){
                    $web_url = to_frontend(route('products.category', Category::find(intval($data->url))->slug), 'category');
                }else{
                    $web_url = to_frontend($data->url);
                }

                $unread = unread_notification(Auth::guard('api')->id());
                return [
                    'id'=>$data->id,
                    'user_id'  => $data->user_id,
                    'title'  => $data->title,
                    'message' => $data->message,
                    'url' => $data->url,
                    'web_url' =>$web_url,
                    'type' => $data->type,
                    'message' => $data->message,
                    'total_unread' => $unread,
                    'image'  => api_asset($data->image),
                    'created_at_date'  => $data->created_at->format('Y-m-d'),
                    'created_at_time'  => $data->created_at->format('H:i:s')
                ];
            })
        ];
    }

    public function with($request){
        return [
            'success' => true,
            'status'  => 200,
            'message' => 'Found notification'
        ];
    }
}
