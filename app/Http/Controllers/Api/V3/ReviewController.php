<?php

namespace App\Http\Controllers\Api\V3;

use Exception;
use App\Models\User;
use App\Models\Review;
use App\Models\Product;
use App\Utility\FileUpload;
use Illuminate\Http\Request;
use App\Models\ReviewComments;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\V3\ReviewCollection;

class ReviewController extends Controller
{
    public function index(Request $request, $id)
    {
        $perPage = $request->get('per_page', 10); // default 10
        $reviews = Review::published()
            ->where('product_id', $id)
            ->whereRaw("TRIM(comment) != ''")
            ->latest()
            ->paginate($perPage);

        return (new ReviewCollection($reviews))->additional([
            'total_reviews' => Review::published()->where('product_id', $id)->count(),
        ]);
    }

    public function getcomments()
    {
        $reviewComments = ReviewComments::where('status', 1)->orderBy('created_at', 'desc')->pluck('title');
        return response()->json([
            'success' => true,
            'data' => $reviewComments
        ]);
    }

    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'user_id' => 'required',
            'rating' => 'required',
            'comment' => 'required',
            'name' => 'required',
            'photos.*' => 'mimes:jpeg,jpg,png,gif,webp|max:2048'
        ]);

        if($validator->fails()) {
            return response()->json([
                'result' => false,
                'messages' => $validator->errors()
            ]);
        } else {
            $product = Product::find($request->product_id);
            if(!isset($product)){
                return response()->json([
                    'result' => false,
                    'message' => ('Sorry! Invalid product')
                ]);
            }
            $user = User::find($request->user_id);

            $commentable = false;
            $whoCanPostReview = get_setting('who_can_post_reviews');
            if($whoCanPostReview == 'everyone'){
                $commentable = true;
            }elseif($whoCanPostReview == 'all_registered_buyers'){
                // foreach($product->orderDetails as $key => $orderDetail){
                //     if(auth('api')->check() && $orderDetail->order != null && $orderDetail->order->user_id == auth('api')->user()->id && $orderDetail->delivery_status == 'delivered' && \App\Models\Review::where('user_id', auth('api')->user()->id)->where('product_id', $product->id)->first() == null){
                //         $commentable = true;
                //     }
                // }
                $hasPurchased = $user->orders()
                ->whereHas('products', function ($query) use ($product) {
                    $query->where('products.id', $product->id);
                })
                ->where('delivery_status', 'delivered')
                ->exists();

                if ($hasPurchased) {
                    $commentable = true;
                }
            }elseif($whoCanPostReview == 'all_registered_customers'){
                if(auth('api')->check()){
                    $commentable = true;
                }
            }

            $canUploadImage = (get_setting('reviews_image_upload') == 'on') ? true : false;
            if(get_setting('reviews_image_upload_only_user') == 'on'){
                if(auth('api')->check()){
                    $canUploadImage = true;
                }else{
                    $canUploadImage = false;
                }
            }

            if(!$commentable){
                return response()->json([
                    'result' => false,
                    'message' => ('Sorry! You cannot review this product')
                ]);
            }

            $photos = [];
            if($request->has('photos')){
                if($canUploadImage){
                    $allowedfileExtension=['webp','jpg', 'jpeg','png','gif'];
                    $files = $request->file('photos');
                    foreach ($files as $file) {
                        $extension = $file->getClientOriginalExtension();
                        $check = in_array($extension,$allowedfileExtension);
                        try{
                            if($check) {
                                $fileUpload = new FileUpload();
                                $uploadedData = $fileUpload->upload($file);
                                if($uploadedData){
                                    $photos[] = $uploadedData->id;
                                }else{
                                    return response()->json([
                                        'result' => false,
                                        'message' => ('Whoops! Something went wrong please try again')
                                    ]);
                                }
                            } else {
                                return response()->json([
                                    'result' => false,
                                    'message' => ('Only Upload png, jpg, jpeg, webp file')
                                ]);
                            }
                        } catch (Exception $e) {
                            Log::channel('custom')->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
                        }
                    }
                }
            }

            $review = new \App\Models\Review;
            $review->product_id = $request->product_id;
            $review->user_id = isset($user) ? $user->id : null;
            $review->name = isset($user) ? $user->name : $request->name;
            $review->email = isset($user) ? $user->email : $request->email;
            $review->rating = $request->rating;
            $review->comment = $request->comment;
            $review->photos = count($photos) > 0 ? implode(',', $photos) : null;
            $review->status = get_setting('auto_approved_reviews') == 'on' ? 1 : 0;
            $review->viewed = 0;
            $review->save();

            $count = Review::where('product_id', $product->id)->where('status', 1)->count();
            if($count > 0){
                $product->rating = Review::where('product_id', $product->id)->where('status', 1)->sum('rating')/$count;
            }
            else {
                $product->rating = 0;
            }
            $product->save();

            if($product->added_by == 'seller'){
                $seller = $product->user->seller;
                $seller->rating = (($seller->rating*$seller->num_of_reviews)+$review->rating)/($seller->num_of_reviews + 1);
                $seller->num_of_reviews += 1;
                $seller->save();
            }

            return response()->json([
                'result' => true,
                'message' => ('Review  Submitted')
            ]);
        }
    }

    public function featuredReviews(Request $request)
    {
        $limit = $request->input('limit', 10);

        $reviews = Review::published()
            ->featured()
            ->with([
                'user' => fn($query) => $query->select('id', 'name', 'email_verified_at'),
                'product',
            ])
            // ->where('review_type', 'default')
            // ->whereBetween('rating', [3, 5])
            // ->whereNotNull(['product_id', 'user_id', 'comment'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return new ReviewCollection($reviews);
    }
}
