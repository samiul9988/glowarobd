<?php

namespace App\Http\Resources\V3;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleProductResource extends JsonResource
{
    public function toArray($request)
    {
        $user = Auth::guard('api')->user() ?? null;
        if(!$user && filled($request->header('uid',null))) {
            $user = User::find($request->header('uid'));
        }
        if($user){
            $user = $user->load('customeringroup.group');
        }
        $mainPrice = getMinimumPriceByVariant($this, null, $request->header('source', 'app'), 1, $user);
        $base_price = (float) home_base_price($this, false);
        $base_discounted_price = (float) home_discounted_base_price($this, false, $user?->id ?? null);

        $savings = max($base_price - $mainPrice, 0);
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'thumbnail_image' => api_asset($this->thumbnail_img),
            'base_price' => $base_price,
            'base_discounted_price' => $mainPrice,
            'formatted_base_price' => format_price($base_price),
            'formatted_base_discounted_price' => format_price($mainPrice),
            'save' => (float) number_format($savings, 2),
            'currency' => currency_symbol(),
        ];
    }
}
