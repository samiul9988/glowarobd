<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Review;
use App\Models\Product;
use App\Utility\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orderBy = 'created_at';
        $orderDirection = 'desc';
        if (filled($request->rating) && in_array($request->rating, ['asc', 'desc'])) {
            $orderBy = 'rating';
            $orderDirection = $request->rating;
        }
        $reviews = Review::with('product', 'user:id,name,email', 'createdBy:id,name', 'updatedBy:id,name')
            ->when(filled($request->type), function ($query) use ($request) {
                if ($request->type === 'self') {
                    return $query->where('review_type', 'default')->whereNotNull('user_id');
                    // return $query->whereNotNull('user_id')->whereNull('created_by');
                } else {
                    return $query->where('review_type', strtolower($request->type));
                }
            })
            ->when($request->product, function ($query) use ($request) {
                return $query->where('product_id', $request->product);
            })
            ->when(filled($request->date), function ($query) use ($request) {
                $dates = explode(' to ', $request->date);
                if (count($dates) == 2) {
                    $start = Carbon::parse($dates[0])->startOfDay();
                    $end = Carbon::parse($dates[1])->endOfDay();
                    return $query->whereBetween('created_at', [$start, $end]);
                }
            })
            ->orderBy($orderBy, $orderDirection)
            ->paginate(25);

        return view('backend.reviews.index', compact('reviews',));
    }

    public function userIndex(Request $request)
    {
        $videoReviews = Review::latest()->published()
            ->where('review_type', 'video')
            ->whereNotNull('videos')
            ->select('videos')
            ->limit(21)
            ->get();

        $imageReviews = Review::latest()->published()
            ->where('review_type', 'image')
            ->whereNotNull('photos')
            ->select('photos')
            ->limit(21)
            ->get();

        $textReviews = Review::latest()->published()
            ->whereIn('review_type', ['text', 'default'])
            ->with('user:id,name', 'product')
            ->select('id', 'comment', 'rating', 'name', 'created_at', 'user_id', 'product_id')
            ->limit(21)
            ->get();

            // dd($videoReviews, $imageReviews, $textReviews);
        return view('frontend.reviews.index', compact('videoReviews', 'imageReviews', 'textReviews'));
    }

    public function filterType($type)
    {
        abort_if(!in_array($type, ['text', 'image', 'video']), 404);

        $reviews = Review::latest()->published();
        if ($type == 'text') {
            $reviews = $reviews->whereIn('review_type', ['text', 'default'])
                ->with('user:id,name','product')
                ->select('id', 'comment', 'rating', 'name', 'created_at', 'user_id', 'product_id');
        } elseif ($type == 'image') {
            $reviews = $reviews->where('review_type', 'image')
                ->whereNotNull('photos')
                ->select('photos');
        } elseif ($type == 'video') {
            $reviews = $reviews->where('review_type', 'video')
                ->whereNotNull('videos')
                ->select('videos');
        } else {
            $reviews = $reviews->where('review_type', 'default')
                ->with('user:id,name')
                ->select('id', 'comment', 'rating', 'name', 'created_at', 'user_id');
        }
        $reviews = $reviews->paginate(28);

        return view('frontend.reviews.filter_index', compact('reviews', 'type'));
    }


    public function seller_reviews()
    {
        $reviews = DB::table('reviews')
            ->orderBy('id', 'desc')
            ->join('products', 'reviews.product_id', '=', 'products.id')
            ->where('products.user_id', Auth::user()->id)
            ->select('reviews.id')
            ->distinct()
            ->paginate(9);

        foreach ($reviews as $key => $value) {
            $review = \App\Models\Review::find($value->id);
            $review->viewed = 1;
            $review->save();
        }

        return view('frontend.user.seller.reviews', compact('reviews'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.reviews.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'rating' => 'required',
            'comment' => 'required',
            'name' => 'required',
            'reviewPhotos.*' => 'mimes:jpeg,jpg,png,gif,webp|max:2048'
        ]);

        $photos = [];
        if ($files = $request->file('reviewPhotos')) {
            $allowedfileExtension = ['webp', 'jpg', 'jpeg', 'png', 'gif'];
            $fileUpload = new FileUpload();

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $check = in_array($extension, $allowedfileExtension);
                if ($check) {
                    $uploadedData = $fileUpload->upload($file);
                    if ($uploadedData) {
                        $photos[] = $uploadedData->id;
                    } else {
                        flash(('Whoops! Something went wrong please try again'))->warning();
                        return back();
                    }
                } else {
                    flash(('Warning! Sorry Only Upload png , jpg , webp file'))->warning();
                    return back();
                }
            }
        }

        $review = new Review;
        $review->product_id = $request->product_id;
        $review->user_id = Auth::user() ? Auth::user()->id : null;
        $review->name = Auth::user() ? Auth::user()->name : $request->name;
        $review->email = Auth::user() ? Auth::user()->email : $request->email;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->photos = count($photos) > 0 ? implode(',', $photos) : null;
        $review->status = get_setting('auto_approved_reviews') == 'on' ? 1 : 0;
        $review->viewed = '0';
        $review->created_by = Auth::id();
        $review->save();
        $product = Product::findOrFail($request->product_id);
        if (Review::where('product_id', $product->id)->where('status', 1)->count() > 0) {
            $product->rating = Review::where('product_id', $product->id)->where('status', 1)->sum('rating') / Review::where('product_id', $product->id)->where('status', 1)->count();
        } else {
            $product->rating = 0;
        }
        $product->save();

        if ($product->added_by == 'seller') {
            $seller = $product->user->seller;
            $seller->rating = (($seller->rating * $seller->num_of_reviews) + $review->rating) / ($seller->num_of_reviews + 1);
            $seller->num_of_reviews += 1;
            $seller->save();
        }

        flash(('Review has been submitted successfully'))->success();
        return back();
    }

    public function adminStore(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:text,image,video',
                'product' => 'nullable|exists:products,id',
                'customer' => 'nullable|exists:users,id',
                'name' => 'nullable|string',
                'rating' => 'required_if:type,text|nullable|integer|min:1|max:5',
                'comment' => 'required_if:type,text',
                'photos' => 'required_if:type,image|nullable|string',
                'videos' => 'required_if:type,video|nullable|array',
                'videos.*' => 'nullable|string|url',
                'review_date' => 'nullable|date|before_or_equal:today',
            ]);

            if ($request->type === 'text') {
                if (blank($request->customer) && blank($request->name)) {
                    return back()->withErrors([
                        'customer' => 'Either choose a customer or enter a name.',
                        'name' => 'Either choose a customer or enter a name.',
                    ])->withInput();
                }
            }


            $type = strtolower($request->type);

            $review = new Review;
            $review->review_type = $type;
            $review->product_id = $request->product ?? null;
            $review->user_id = $request->customer ?? null;
            $review->name = $request->name ?? null;
            if ($type === 'text') {
                $review->rating = $request->rating ?? null;
                $review->comment = $request->comment ?? null;
                $review->photos = $request->attachments ?? null;
            } elseif ($type === 'image') {
                $review->photos = $request->photos ?? null;
            } elseif ($type === 'video') {
                $review->videos = $request->videos ?? null;
            }
            $review->created_by = Auth::id();
            $review->status = 1; // Assuming admin reviews are auto-approved
            $review->featured = $request->featured ?? 0;
            $review->viewed = '0';
            if ($request->filled('review_date')) {
                $review->created_at = Carbon::parse($request->review_date)->setTimeFrom(now());
                $review->updated_at = Carbon::parse($request->review_date)->setTimeFrom(now());
            }
            if ($review->save()) {
                flash(('Review has been added successfully'))->success();
                return redirect()->route('reviews.index');
            }
            flash(('Whoops! Something went wrong'))->error();
            return back()->withInput();
        } catch (ValidationException $e) {
            // dd($e->errors());
            flash(('Validation Failed'))->error();
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            // dd($e->getMessage());
            flash(('Server Error'))->error();
            return back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $review = Review::with('product:id,name', 'user:id,name')->findOrFail($id);
        // dd($review);
        abort_if($review->review_type === 'default', 403, 'Forbidden! This review type is not editable.');
        return view('backend.reviews.edit', compact('review'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'type' => 'required|in:text,image,video',
            'product' => 'nullable|exists:products,id',
            'customer' => 'nullable|exists:users,id',
            'name' => 'nullable|string',
            'rating' => 'required_if:type,text|nullable|integer|min:1|max:5',
            'comment' => 'required_if:type,text',
            'photos' => 'required_if:type,image|nullable|string',
            'videos' => 'required_if:type,video|nullable|array',
            'videos.*' => 'nullable|string|url',
            'review_date' => 'nullable|date|before_or_equal:today',
        ]);

        if ($request->type === 'text') {
            if (blank($request->customer) && blank($request->name)) {
                return back()->withErrors([
                    'customer' => 'Either choose a customer or enter a name.',
                    'name' => 'Either choose a customer or enter a name.',
                ])->withInput();
            }
        }

        $review = Review::findOrFail($id);
        $type = strtolower($request->type);
        $review->review_type = $type;
        $review->product_id = $request->product ?? null;
        $review->user_id = $request->customer ?? null;
        $review->name = $request->name ?? null;
        if ($type === 'text') {
            $review->rating = $request->rating ?? null;
            $review->comment = $request->comment ?? null;
            $review->photos = $request->attachments ?? null; // Clear photos for text reviews
            $review->videos = null; // Clear videos for text reviews
        } elseif ($type === 'image') {
            $review->photos = $request->photos ?? null;
            $review->rating = null; // Clear rating for image reviews
            $review->comment = null; // Clear comment for image reviews
        } elseif ($type === 'video') {
            $review->videos = $request->videos ?? null;
            $review->rating = null; // Clear rating for video reviews
            $review->comment = null; // Clear comment for video reviews
        }
        $review->updated_by = Auth::id();
        $review->status = $request->status;
        $review->featured = $request->featured ?? 0;
        // $review->viewed = '0';
        if ($request->filled('review_date') && !$review->created_at->isSameDay(Carbon::parse($request->review_date))) {
            $review->created_at = Carbon::parse($request->review_date)->setTimeFrom(now());
        }
        if ($review->save()) {
            flash(('Review has been updated successfully'))->success();
            return redirect()->route('reviews.index');
        }
        flash(('Whoops! Something went wrong please try again'))->error();
        return back()->withInput();
    }

    public function updatePublished(Request $request)
    {
        $review = Review::findOrFail($request->id);
        $review->status = $request->status;
        $review->save();

        $product = Product::findOrFail($review->product->id);
        $product->rating = Review::where('product_id', $product->id)->where('status', 1)->avg('rating');
        $product->save();

        if ($product->added_by == 'seller') {
            $seller = $product->user->seller;
            if ($review->status) {
                $seller->rating = (($seller->rating * $seller->num_of_reviews) + $review->rating) / ($seller->num_of_reviews + 1);
                $seller->num_of_reviews += 1;
            } else {
                $seller->rating = (($seller->rating * $seller->num_of_reviews) - $review->rating) / max(1, $seller->num_of_reviews - 1);
                $seller->num_of_reviews -= 1;
            }

            $seller->save();
        }

        return 1;
    }

    public function updateFeatured(Request $request)
    {
        $review = Review::findOrFail($request->id);
        $review->featured = $request->status;
        $review->save();

        return 1;
    }

    public function destroy($id)
    {
        if(Review::destroy($id)) {
            flash(('Review deleted successfully.'))->success();
        }else{
            flash(('Request Failed.'))->error();
        }

        return redirect()->route('reviews.index');
    }

    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:reviews,id',
            'status' => 'required|in:1,0',
        ]);

        $count = Review::whereIn('id', $request->ids)->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => "$count reviews updated successfully.",
        ]);
    }

    /**
     * Bulk delete notices
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:reviews,id',
        ]);

        $count = Review::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "$count reviews deleted successfully.",
        ]);
    }

    public function fetchProducts(Request $request)
    {
        $products = Product::published()
            ->when(filled($request->search) || filled($request->selected), function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    if (filled($request->search)) {
                        $q->where('name', 'LIKE', '%' . $request->search . '%');
                    }
                    if (filled($request->selected)) {
                        $selected = is_array($request->selected) ? $request->selected : array_filter(explode(',', $request->selected));

                        if (count($selected) > 0) {
                            $q->orWhereIn('id', $selected);
                        }
                    }
                });
            })
            ->orderBy('name')
            ->limit(300)
            ->pluck('name', 'id')
            ->toArray();

        // dd(count($products));
        return response()->json($products);
    }

    public function fetchCustomers(Request $request)
    {
        $customers = \App\Models\User::latest()
            ->where('user_type', 'customer')
            ->where('banned', 0)
            ->whereNotNull('email_verified_at')
            ->when(filled($request->search) || filled($request->selected), function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    if (filled($request->search)) {
                        $q->where('name', 'LIKE', '%' . $request->search . '%');
                    }
                    if (filled($request->selected)) {
                        $q->orWhere('id', $request->selected);
                    }
                });
            })
            ->limit(100)
            ->pluck('name', 'id')
            ->toArray();

        // dd(count($customers));
        return response()->json($customers);
    }
}
