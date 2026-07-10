<?php

namespace App\Http\Controllers\Api\V3;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Lcobucci\JWT\Parser;
use App\Models\OrderDetail;
use App\Utility\SmsUtility;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Customergroup;
use App\Models\Customeringroup;
use Illuminate\Support\Facades\DB;
use Razorpay\Api\Order as ApiOrder;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Illuminate\Support\Facades\Session;
use App\Http\Resources\V3\UserCollection;
use Illuminate\Support\Facades\Validator;
use Lcobucci\JWT\Token\Parser as TokenParser;
use App\Http\Controllers\OTPVerificationController;
use App\Http\Resources\V3\UserProductReviewsCollection;
use App\Http\Resources\V3\PendingReviewProductCollection;
use App\Models\TempUser;

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
        $group = $user_group ? $user_group->group : null;

        return response()->json([
            'result' => true,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'date_of_birth' => isset($user->date_of_birth) ? Carbon::parse($user->date_of_birth)->format('Y-m-d') : null,
            'gender'=>$user->gender,
            'group'=>[
                'id'=> $group ? $group->id : null,
                'name'=> $group ? $group->group_name : null,
                'icon'=> $group ? $group->group_icon : null,
                "image" => $group ? uploaded_asset($group->group_name) : null,
                "min_order_qty" => $group ? $group->min_order_qty : null,
                "min_order_amount" => $group ? $group->min_order_amount : null,
                "discount" => $group ? $group->discount : null,
                "discount_type" => $group ? $group->discount_type : null,
                "start_date" => $group ? $group->start_date : null,
                "end_date" => $group ? $group->end_date : null,
                "message" => $group ? json_decode($group->message) : null,
                "delivery_discount" => $group ? $group->delivery_discount : null,
                "delivery_discount_amount" => $group ? $group->delivery_discount_amount : null,
                "ordering" => $group ? $group->ordering : null
            ],
            'avatar_original' => api_asset($user->avatar_original),
            'phone' => $user->phone,
            'reward_point' => $user->point_balance
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
        $user_group = Customeringroup::where('user_id','=',$user->id)->where('status', '=', 1)->first();

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

                $groups[] = [
                    "id" => $group->id,
                    "name" => $group->group_name,
                    "imgae" => uploaded_asset($group->group_image),
                    "icon" => $group->group_icon,
                    "min_order_qty" => $group->min_order_qty,
                    "min_order_amount" => $group->min_order_amount,
                    "discount" => $group->discount,
                    "discount_type" => $group->discount_type,
                    "start_date" => $group->start_date,
                    "end_date" => $group->end_date,
                    "message" => json_decode($group->message),
                    "delivery_discount" => $group->delivery_discount,
                    "delivery_discount_amount" => $group->delivery_discount_amount,
                    "ordering" => $group->ordering,
                    "status" => $status
                ];
            }
        }

        return response()->json([
            'groups' => $groups
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

    public function createBeforeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^(\+88)?01[3-9]\d{8}$/',
            'temp_user_id' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }
        $phone = trim(str_replace('-','',str_replace('+88','',$request->phone)));
        if (strlen($phone) != 11) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number is invalid'
            ], 400);
        }

        $user = User::whereIn('phone', [$phone, '+88'.$phone])->first();
        if(!$user) {
            $user = new User;
            $user->name = $request->name;
            $user->email = null;
            $user->address = $request->address ?? null;
            $user->phone = $phone ?? null;
            $password = Str::random(rand(8,10));
            $user->password = bcrypt($password);
            $user->temp_password = $password;
            $user->email_verified_at = null;
            $user->recent_login = null;
            $user->save();

            $customer = new \App\Models\Customer;
            $customer->user_id = $user->id;
            $customer->save();

            $group = Customergroup::orderBy('ordering', 'asc')->first();

            if($group){
                $first_group = new Customeringroup;
                $first_group->user_id = $user->id;
                $first_group->customer_groups_id = $group->id;
                $first_group->status = 1;
                $first_group->save();
            }
        }

        try{
            TempUser::updateOrCreate(
                ['user_id' => $user->id],
                ['temp_user_id' => $request->temp_user_id]
            );
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Server Error'
            ], 500);
        }

        $this->send_verification_code($user);
        return response()->json([
            'success' => true,
            'message' => 'A verification code has been sent to your phone.',
        ], 200);
    }

    public function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^(\+88)?01[3-9]\d{8}$/',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }
        $phone = trim(str_replace('-','',str_replace('+88','',$request->phone)));
        if (strlen($phone) != 11) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number is invalid'
            ], 400);
        }

        $user = User::whereIn('phone', [$phone, '+88'.$phone])->first();
        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Request'
            ], 400);
        }

        $this->send_verification_code($user);
        return response()->json([
            'success' => true,
            'message' => 'A verification code has been sent to your phone.',
        ], 200);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^(\+88)?01[3-9]\d{8}$/',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }
        $phone = trim(str_replace('-','',str_replace('+88','',$request->phone)));
        if (strlen($phone) != 11) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number is invalid'
            ], 400);
        }

        $user = User::whereIn('phone', [$phone, '+88'.$phone])->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->verification_code != $request->verification_code) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code'
            ], 400);
        }

        if (app()->environment('production') && !is_null($user->temp_password)) {
            SmsUtility::user_created($user, $user->temp_password);
        }

        $user->email_verified_at = now();
        $user->recent_login = now();
        $user->verification_code = null;
        $user->temp_password = null;
        $user->save();

        remove_previous_cart($user->id);

        $tokenResult = $user->createToken('Personal Access Token');

        return $this->loginSuccess($tokenResult, $user);
    }

    protected function loginSuccess($tokenResult, $user)
    {
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(100);
        $token->save();

        $user->loadMissing('customeringroup.group');

        return response()->json([
            'result' => true,
            'message' => 'Verified successfully.',
            'user_id' => $user->id,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'avatar_original' => api_asset($user->avatar_original),
                'phone' => $user->phone,
                'gender' => $user->gender,
                'date_of_birth' => isset($user->date_of_birth) ? Carbon::parse($user->date_of_birth)->format('Y-m-d') : null,
                'customer_group' => [
                    'id' => $user->customeringroup->group->id ?? null,
                    'name' => $user->customeringroup->group->group_name ?? null,
                ]
            ]
        ]);
    }

    private function send_verification_code($user)
    {
        $code = rand(100000, 999999);
        $user->verification_code = $code;
        $user->save();
        if (addon_is_activated('otp_system') && app()->environment('production')) {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }
    }
}
