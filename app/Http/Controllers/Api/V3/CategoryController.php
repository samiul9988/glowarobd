<?php

namespace App\Http\Controllers\Api\V3;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use App\Utility\CategoryUtility;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\V3\CategoryCollection;
use App\Http\Resources\V3\ProductMiniCollection;

class CategoryController extends Controller
{

    public function index($parent_id = 0)
    {
        if (request()->has('parent_id') && is_numeric(request()->get('parent_id'))) {
            $parent_id = request()->get('parent_id');
        }

        // Cache::forget("app.categories-$parent_id");

        return Cache::remember("app.categories-$parent_id", 86400, function () use ($parent_id) {
            return new CategoryCollection(Category::with('subcategories.childrenCategories', 'content')
            ->where('parent_id', $parent_id)
            ->orderBy('order_level', 'asc')
            ->get());
        });
    }

    public function featured(Request $request)
    {
        $limit = $request->limit ?? null;
        return Cache::remember('app.featured_categories_'.$limit, 86400, function () use($limit) {
            $query = Category::with('subcategories.childrenCategories', 'content')->where('featured', 1);
            if ($limit) {
                $query->limit($limit);
            }
            return new CategoryCollection($query->get());
        });
    }

    public function home()
    {
        $categories = json_decode(get_setting('home_categories'), true);
        $home_categories = array_map(function ($item) {
            return (int)$item['cid'];
        }, $categories);
        return Cache::remember('app.home_categories', 86400, function () use ($home_categories) {
            return new CategoryCollection(Category::with('subcategories.childrenCategories', 'content')->whereIn('id', array_filter($home_categories ?? []))->get());
        });
    }

    public function top()
    {
        $home_categories = json_decode(get_setting('home_categories'), true);
        $top_categories = array_map(function ($item) {
            return (int)$item['cid'];
        }, $home_categories);
        return Cache::remember('app.top_categories', 86400, function () use ($top_categories) {
            return new CategoryCollection(Category::with('subcategories.childrenCategories', 'content')->whereIn('id', array_filter($top_categories ?? []))->limit(20)->get());
        });
    }

    //Category Details by slug or Id
    public function show($id)
    {
        $getTheId = Category::find($id);
        if ($getTheId) {
            $category = Category::with('subcategories.childrenCategories', 'content')->where('id', $id)->get();
        } else {
            $category = Category::with('subcategories.childrenCategories', 'content')->where('slug', $id)->get();
        }

        return new CategoryCollection($category);
    }

    public function left_category()
    {
        $firstLevelCat = cache()->remember('firstLevelCat', 86400, function () {
            return \App\Models\Category::where('level', 0)->orderBy('order_level', 'desc')->get();
        });
        $p_category = [];
        $second_array = [];
        $third_array = [];

        foreach ($firstLevelCat as $key => $category) {
            $p_category[$category->id] = ['id' => $category->id, 'name' => $category->name];

            $s_category = \App\Models\Category::where('parent_id', $category->id)->orderBy('order_level', 'desc')->get();

            foreach ($s_category as $s_key => $s_cat) {
                $second_array[$category->id][] = ['id' => $s_cat->id, 'name' => $s_cat->name];

                $third_category = \App\Models\Category::where('parent_id', $s_cat->id)->orderBy('order_level', 'desc')->get();
                foreach ($third_category as $third_value) {
                    $third_array[$category->id][$s_cat->id][] = ['id' => $third_value->id, 'name' => $third_value->name];
                }
            }
        }
        return response()->json([
            'status' => true,
            'p_category' => $p_category,
            's_category' => $second_array,
            't_category' => $third_array,
        ]);
    }

    public function getCategoryContent(Request $request, $id)
    {
        $category = Category::with('subcategories.childrenCategories', 'content')->find($id);
        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => ('Category not found!')
            ]);
        } else {
            try {
                $content = $category->content;
                $sliders = [];
                $slider_images = $content->where('type', 'home_slider_images_mobile')->first() ? json_decode($content->where('type', 'home_slider_images_mobile')->first()->value, true) : [];
                $slider_links = $content->where('type', 'home_slider_links_mobile')->first() ? json_decode($content->where('type', 'home_slider_links_mobile')->first()->value, true) : [];
                $slider_bgcolors = $content->where('type', 'home_slider_bgcolors_mobile')->first() ? json_decode($content->where('type', 'home_slider_bgcolors_mobile')->first()->value, true) : [];
                foreach ($slider_images as $key => $value) {
                    $sliders[] = [
                        'image' => api_asset($value) ?? '',
                        'link' => $slider_links[$key] ?? '',
                        'bg_color' => $slider_bgcolors[$key] ?? '',
                    ];
                }
                $subcategory_ids = $content->where('type', 'top_categories')->first() ? json_decode($content->where('type', 'top_categories')->first()->value) : null;

                $subcategories = [];
                if ($subcategory_ids) {
                    $subcategories = Category::with('subcategories.childrenCategories', 'content')
                        ->whereIn('id', $subcategory_ids)
                        ->orderByRaw('FIELD(id, ' . implode(',', $subcategory_ids) . ')')
                        ->get();
                    $subcategories = collect((new CategoryCollection($subcategories))->toArray($request)['data'])->toArray();
                }
                $banner = json_decode($category->banner, true);
                $pageBanner = json_decode($category->page_banner, true);
                $icon = json_decode($category->icon, true);
                $featuredIcon = json_decode($category->featured_icon, true);
                $bgImage = json_decode($category->bg_image, true);

                $appSlider = json_decode($category->app_slider, true) ?? [];
                if (count($appSlider) > 0) {
                    foreach ($appSlider as $key => $value) {
                        $appSlider[$key] = api_asset($value) ?? '';
                    }
                }
                $appBanner1 = json_decode($category->app_banner1, true) ?? [];
                if (count($appBanner1) > 0) {
                    foreach ($appBanner1 as $key => $value) {
                        $appBanner1[$key] = api_asset($value) ?? '';
                    }
                }
                $appBanner2 = json_decode($category->app_banner2, true) ?? [];
                if (count($appBanner2) > 0) {
                    foreach ($appBanner2 as $key => $value) {
                        $appBanner2[$key] = api_asset($value) ?? '';
                    }
                }
                $banner_1_images = $content->where('type', 'home_banner1_images_mobile')->first() ? json_decode($content->where('type', 'home_banner1_images_mobile')->first()->value, true) : [];
                $banner_1_links = $content->where('type', 'home_banner1_links_mobile')->first() ? json_decode($content->where('type', 'home_banner1_links_mobile')->first()->value, true) : [];

                $nbanner1_links = [];
                if($banner_1_links){
                    foreach ($banner_1_links as $key => $value) {
                        $nbanner1_links[$key] = getLinkType($value);
                    }
                }
                $banner_1 = array_combine($banner_1_images ?? [], $banner_1_links ?? []);
                if($banner_1_images){
                    $banner_1 = [];
                    foreach ($banner_1_images as $key => $value) {
                        $banner_1[] = [
                            'image' => api_asset($value) ?? '',
                            'id' => $nbanner1_links[$key]['id'] ?? '',
                            'type' => $nbanner1_links[$key]['type'] ?? '',
                            'link' => $nbanner1_links[$key]['link'] ?? ''
                        ];
                    }
                }

                $banner_2_images = $content->where('type', 'home_banner2_images_mobile')->first() ? json_decode($content->where('type', 'home_banner2_images_mobile')->first()->value, true) : [];
                $banner_2_links = $content->where('type', 'home_banner2_links_mobile')->first() ? json_decode($content->where('type', 'home_banner2_links_mobile')->first()->value, true) : [];

                $nbanner2_links = [];
                if($banner_2_links){
                    foreach ($banner_2_links as $key => $value) {
                        $nbanner2_links[$key] = getLinkType($value);
                    }
                }
                $banner_2 = array_combine($banner_2_images ?? [], $banner_2_links ?? []);
                if($banner_2_images){
                    $banner_2 = [];
                    foreach ($banner_2_images as $key => $value) {
                        $banner_2[] = [
                            'image' => api_asset($value) ?? '',
                            'id' => $nbanner2_links[$key]['id'] ?? '',
                            'type' => $nbanner2_links[$key]['type'] ?? '',
                            'link' => $nbanner2_links[$key]['link'] ?? ''
                        ];
                    }
                }

                $home_category_ids = $content->where('type', 'home_categories')->first() ? json_decode($content->where('type', 'home_categories')->first()->value, true) : [];

                $home_categories_query = Category::with('products')
                    ->when(!empty($home_category_ids), function ($query) use ($home_category_ids) {
                        return $query->whereIn('id', $home_category_ids)
                            ->orderByRaw('FIELD(id, ' . implode(',', $home_category_ids) . ')');
                        }, function ($query) {
                        // fallback when home categories empty
                        return $query->where('id', 0); // Returns empty result
                    })
                    ->get();

                $home_categories = collect((new CategoryCollection($home_categories_query))->toArray($request)['data'])->toArray();
                $home_categories_designs = $content->where('type', 'home_categories_designs')->first() ? json_decode($content->where('type', 'home_categories_designs')->first()->value, true) : [];
                $categories = [];
                foreach ($home_categories as $key => &$value) {
                    // dd($this->getProducts($value['id'], $request)->toArray($request)['data']);
                    // dd(collect((new ProductController())->category($value['id'], $request)));
                    $value['products'] = $this->getProducts($value['id'], $request)->toArray($request)['data'] ?? [];
                    $value['design'] = $home_categories_designs[$key] ?? [];
                    $categories[] = $value;
                }

                $data = [
                    'id' => $category->id,
                    'slug' => $category->slug,
                    'name' => $category->name,
                    'banner' => isset($banner['app']) ? api_asset($banner['app']) : null,
                    'page_banner' => isset($pageBanner['app']) ? api_asset($pageBanner['app']) : null,
                    'icon' => isset($icon['app']) ? api_asset($icon['app']) : null,
                    'featured_icon' => isset($featuredIcon['app']) ? api_asset($featuredIcon['app']) : null,
                    'bg_image' => isset($bgImage['app']) ? api_asset($bgImage['app']) : null,
                    'app_slider' => $appSlider,
                    'app_banner1' => $appBanner1,
                    'app_banner2' => $appBanner2,
                    'app_featured_image' => api_asset($category->app_featured_image),
                    'app_home_page_image' => api_asset($category->app_home_page_image),
                    'number_of_children' => CategoryUtility::get_immediate_children_count($category->id),
                    'links' => [
                        'products' => route('api.products.category', $category->id),
                        'sub_categories' => route('subCategories.index', $category->id)
                    ],
                    'sliders' => $sliders,
                    'subcategories' => $subcategories ?? [],
                    'banner_1' => $banner_1,
                    'banner_2' => $banner_2,
                    'categories' => $home_categories
                ];

                return response()->json([
                    'success' => true,
                    'status' => 200,
                    'data' => $data
                ]);
            } catch (\Exception $th) {
                return response()->json([
                    'success' => false,
                    'status' => 500,
                    'message' => 'Internal Server Error',
                    // 'error' => $th->getMessage(),
                    // 'trace' => $th->getTraceAsString()
                ]);
            }
        }
    }

    protected function getProducts($id, Request $request)
    {
        $sort_by = $request->sort_by ?? $request->orderby ?? null;

        $category_ids = [$id];
        $category_ids = CategoryUtility::children_ids($id) + $category_ids;

        $products = Product::published()->with('stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews');

        // $products = $products->where('category_id', $category->id);
        $products = $products->whereIn('category_id', $category_ids);

        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->brand_id) {
            if(Brand::find($request->brand_id)){
                $brand_id = $request->brand_id;
            }else{
                $brand_id = Brand::where('slug', $request->brand_id)->value('id');
            }
            $products->where('brand_id',$brand_id);
        }
        // dd($brand_id);

        if (filled($request->min_price) && filled($request->max_price)) {
            $products->whereBetween('unit_price', [$request->min_price, $request->max_price]);
        }

        if(filled($request->rating) && $request->rating > 0 && $request->rating <= 5) {
            $products->where('rating', '>=', (int)$request->rating);
            // $products->whereHas('reviews', function($query) use ($request) {
            //     $query->where('rating', '>=', (int)$request->rating);
            // });
        }

        switch ($sort_by) {
            case 'newest':      $products->orderBy('created_at', 'desc'); break;
            case 'oldest':      $products->orderBy('created_at', 'asc'); break;
            case 'price-asc':   $products->orderBy('unit_price', 'asc'); break;
            case 'price-desc':  $products->orderBy('unit_price', 'desc'); break;
            // case 'rand':        $products->orderByRaw(DB::raw('RAND()')); break;
            case 'rand':        $products->inRandomOrder(); break;
            default:            $products->orderBy('id', 'desc'); break;
        }

        $priceRange = (clone $products)->selectRaw('MIN(unit_price) as min_price, MAX(unit_price) as max_price')->first();

        $request->merge([
            'min_price_product' => $priceRange->min_price ?? 0,
            'max_price_product' => $priceRange->max_price ?? 0
        ]);

        $limit = $request->limit ?? 10;

        return new ProductMiniCollection($products->paginate($limit));
    }

}
