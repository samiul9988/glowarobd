<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\PendingReviewProductCollection;
use App\Http\Resources\V2\UserCollection;
use App\Http\Resources\V2\UserProductReviewsCollection;
use App\Models\Customergroup;
use App\Models\Customeringroup;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token\Parser as TokenParser;
use Razorpay\Api\Order as ApiOrder;

class UserController extends Controller
{
    public function info($id)
    {
        return new UserCollection(User::where('id', $id)->get());
    }

    public function updateName(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->update([
            'name' => $request->name
        ]);
        return response()->json([
            'message' => ('Profile information has been updated successfully')
        ]);
    }

    public function getUserInfoByAccessToken(Request $request)
    {
        $token = $request->bearerToken() ?? $request->access_token;
        // $token = $request->access_token;

        $false_response = [
            'result' => false,
            'id' => 0,
            'name' => "",
            'email' => "",
            'avatar' => "",
            'avatar_original' => "",
            'phone' => ""
        ];

        if($token == "" || $token == null){
            return response()->json($false_response, 401);
        }

        try {
            $parser = new TokenParser(new JoseEncoder());
            $tokenP = $parser->parse($token);
            $token_id = $tokenP->claims()->get('jti');
        } catch (\Exception $e) {
            return response()->json($false_response, 401);
        }

        $oauth_access_token_data =  DB::table('oauth_access_tokens')->where('id', '=', $token_id)->first();

        if($oauth_access_token_data == null){
            return response()->json($false_response, 404);
        }

        $user = User::where('id', $oauth_access_token_data->user_id)->first();

        if ($user == null) {
            return response()->json($false_response, 404);

        }

        $user_group =  Customeringroup::where('user_id','=',$user->id)->where('status', '=', 1)->first();

        return response()->json([
            'result' => true,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'date_of_birth' => isset($user->date_of_birth) ? Carbon::parse($user->date_of_birth)->format('Y-m-d') : null,
            'gender'=>$user->gender,
            'group'=>[
                'id'=>@$user_group->group->id,
                'name'=>@$user_group->group->group_name,
                'icon'=>@$user_group->group->group_icon,
                'image'=>@$user_group->group->group_image
            ],
            'avatar_original' => api_asset($user->avatar_original),
            'phone' => $user->phone
        ]);

    }
    public function deleteuseraccount(Request $request)
    {
        $token = $request->bearerToken() ?? $request->access_token;

        $false_response = [
            'result' => false,
            'message' => 'Token mismatch!',
        ];

        if($token == "" || $token == null){
            return response()->json($false_response);
        }

        try {
            $parser = new TokenParser(new JoseEncoder());
            $tokenP = $parser->parse($token);
            $token_id = $tokenP->claims()->get('jti');
        } catch (\Exception $e) {
            return response()->json($false_response);
        }

        $oauth_access_token_data =  DB::table('oauth_access_tokens')->where('id', '=', $token_id)->first();

        if($oauth_access_token_data == null){
            return response()->json($false_response);
        }

        $user = User::where('id', $oauth_access_token_data->user_id)->first();

        if ($user == null) {
            return response()->json($false_response);
        }else{
            $user->email = 'deleted'.$user->id.'_'.$user->email;
            $user->phone = 'deleted'.$user->id.'_'.$user->phone;
            $user->banned = 1;
            $user->save();
        }

        return response()->json([
            'result' => true,
            'message' => 'Success!'
        ]);

    }
    public function get_group_list_with_user_current_group(Request $request)
    {
        $token = $request->bearerToken() ?? $request->access_token;
        $false_response = [
            'result' => false,
            'message' => 'Token mismatch!',
        ];
        if($token == "" || $token == null){
            return response()->json($false_response);
        }

        /* try {
            $parser = new TokenParser(new JoseEncoder());
            $tokenP = $parser->parse($token);
            $token_id = $tokenP->claims()->get('jti');
        } catch (\Exception $e) {
            return response()->json($false_response);
        } */

        // $oauth_access_token_data =  DB::table('oauth_access_tokens')->where('id', '=', $token_id)->first();
        $user = $request->user();

        $all_groups = Customergroup::orderBy('ordering', 'asc')->get();
        $user_group =  Customeringroup::where('user_id','=',$user->id)->where('status', '=', 1)->first();

        $groups=[];
        if($all_groups->count() > 0 ){
            foreach($all_groups as $key => $group)
            {
                if(@$user_group->customer_groups_id != NULL & @$user_group->customer_groups_id != '')
                {
                    if($user_group->customer_groups_id == $group->id)
                    {
                        $status = 'true';
                    }else{
                        $status = 'false';
                    }
                }else{
                    $status = 'false';
                }

                $groups[]=["id"=>$group->id, "name"=>$group->group_name, "imgae"=>uploaded_asset($group->group_image),"message"=>json_decode($group->message), "status"=>$status];
            }
        }

        return response()->json([
            'groups'=>$groups
        ]);

    }


    public function user_order_status(Request $request)
    {
        $id = $request->id;
        $query = Order::where('id','=', $id)->first();
        $current_status = $query->delivery_status;
        $total_status = [
            "preorder",
            "pending",
            "confirmed",
            "picked_up",
            "on_the_way",
            "delivered",
            "cancelled"
        ];
        $xxx = [];
        foreach($total_status as $v){
            if($v == $current_status){
                $active = "true";
            }
            else{
                $active = "false";
            }
            $xxx[] = ["status"=>$v, "active"=>$active];
        }
        return response()->json(
            $xxx
        );
    }

    public function getDeliverdedProductsWithPendingReview(Request $request)
    {
        $token = $request->bearerToken() ?? $request->access_token;
        $false_response = [
            'result' => false,
            'message' => 'Token mismatch!',
        ];
        if($token == "" || $token == null){
            return response()->json($false_response);
        }

        try {
            $parser = new TokenParser(new JoseEncoder());
            $tokenP = $parser->parse($token);
            $token_id = $tokenP->claims()->get('jti');
        } catch (\Exception $e) {
            return response()->json($false_response);
        }

        $oauth_access_token_data =  DB::table('oauth_access_tokens')->where('id', '=', $token_id)->first();
        $user = $request->user()->load('orders');

        if($user){
            $deliveredOrders = Order::where(['user_id' => $user->id, 'delivery_status' => 'delivered'])->get();
            $deliveredProducts = [];
            foreach($deliveredOrders as $row){
                foreach($row->orderDetails as $item){
                    $deliveredProducts[] = $item->product_id;
                }
            }
            $products = [];
            foreach($deliveredProducts as $product){
                $products[] = Product::where('id','=',$product)->with('reviews')->with('reviews.user')->first();
            }
            return new PendingReviewProductCollection($products);
        }else{
            return response()->json($false_response);
        }

    }

    public function getMyProductReviews(Request $request)
    {
        $token = $request->bearerToken() ?? $request->access_token;
        $false_response = [
            'result' => false,
            'message' => 'Token mismatch!',
        ];
        if($token == "" || $token == null){
            return response()->json($false_response);
        }

        try {
            $parser = new TokenParser(new JoseEncoder());
            $tokenP = $parser->parse($token);
            $token_id = $tokenP->claims()->get('jti');
        } catch (\Exception $e) {
            return response()->json($false_response);
        }

        $oauth_access_token_data =  DB::table('oauth_access_tokens')->where('id', '=', $token_id)->first();
        if($oauth_access_token_data){
            $user = User::find($oauth_access_token_data->user_id);
        }else{
            return response()->json($false_response);
        }

        $limit = 10;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }

        if($user){
            $reviews = $user->reviews()->orderBy('id', 'DESC')->paginate($limit);
            return new UserProductReviewsCollection($reviews);
        }else{
            return response()->json($false_response);
        }

    }
}
