<?php

namespace App\Http\Controllers\Api\V3;

use App\Models\Brand;
use App\Models\Color;
use App\Models\Search;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Models\MetaObjectItem;
use App\Utility\CategoryUtility;
use App\Models\AttributeCategory;
use App\Models\ProductCustomField;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\V3\ProductMiniCollection;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        if (get_setting('enable_meilisearch') == 1) {
            return $this->meiliIndex($request);
        }

        $products = Product::published()
            ->with('thumbnail_image', 'stocks', 'flash_deal_product.flash_deals', 'brand', 'reviews')
            ->when(filled($request->keyword), function ($query) use ($request) {
                $keyword = trim($request->keyword);
                $this->store($keyword);

                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('tags', 'like', "%{$keyword}%");
                });
            })
            ->when(filled($request->brand), function ($query) use ($request) {
                $brand = Brand::where('id', $request->brand)->orWhere('slug', $request->brand)->first();
                if ($brand) {
                    $query->where('brand_id', $brand->id);
                }
            });

        if (filled($request->min_price) && filled($request->max_price)) {
            $products = $products->whereBetween('unit_price', [$request->min_price, $request->max_price]);
        }


        if(filled($request->rating) && $request->rating > 0 && $request->rating <= 5) {
            $products = $products->whereHas('reviews', function($query) use ($request) {
                $query->where('rating', '>=', (int)$request->rating);
            });
        }

        $sort_by = $request->sort_by ?? $request->orderby ?? null;
        switch ($sort_by) {
            case 'newest':      $products->orderBy('created_at', 'desc'); break;
            case 'oldest':      $products->orderBy('created_at', 'asc'); break;
            case 'price-asc':   $products->orderBy('unit_price', 'asc'); break;
            case 'price-desc':  $products->orderBy('unit_price', 'desc'); break;
            case 'rand':        $products->inRandomOrder(); break;
            default:            $products->orderBy('id', 'desc'); break;
        }

        $priceRange = (clone $products)->selectRaw('MIN(unit_price) as min_price, MAX(unit_price) as max_price')->first();

        $request->merge([
            'min_price_product' => $priceRange->min_price ?? 0,
            'max_price_product' => $priceRange->max_price ?? 0
        ]);

        $limit = $request->limit ?? 10;

        return new ProductMiniCollection($products->latest()->paginate($limit));
    }

    // Using Meilisearch
    public function meiliIndex(Request $request)
    {
        $productIds = Product::search($request->keyword ?: '', function ($meilisearch, string $query, array $options) use ($request) {
            $filters = [];
            if (filled($request->brand)) {
                $brand = Brand::where('id', $request->brand)->orWhere('slug', $request->brand)->first();
                if ($brand) {
                    $filters[] = 'brand_id = ' . $brand->id;
                }
            }
            if (filled($request->min_price) && filled($request->max_price)) {
                $filters[] = 'unit_price >= ' . $request->min_price . ' AND unit_price <= ' . $request->max_price;
            } elseif (filled($request->min_price)) {
                $filters[] = 'unit_price >= ' . $request->min_price;
            } elseif (filled($request->max_price)) {
                $filters[] = 'unit_price <= ' . $request->max_price;
            }
            $rating = (int) $request->rating ?? 0;
            if($rating > 0 && $rating <= 5) {
                $filters[] = 'rating >= ' . $rating;
            }
            $options['filter'] = implode(' AND ', $filters);
            $options['limit'] = (int) getMaxProductsCount();
            return $meilisearch->search($query, $options);
        })->keys()->toArray();

        $products = Product::with('thumbnail_image', 'stocks', 'flash_deal_product.flash_deals', 'brand', 'reviews')
            ->whereIn('id', $productIds);

        $priceRange = (clone $products)->selectRaw('MIN(unit_price) as min_price, MAX(unit_price) as max_price')->first();

        $request->merge([
            'min_price_product' => $priceRange->min_price ?? 0,
            'max_price_product' => $priceRange->max_price ?? 0
        ]);

        $sort_by = $request->sort_by ?? $request->orderby ?? null;
        switch ($sort_by) {
            case 'newest':      $products->orderBy('created_at', 'desc'); break;
            case 'oldest':      $products->orderBy('created_at', 'asc'); break;
            case 'price-asc':   $products->orderBy('unit_price', 'asc'); break;
            case 'price-desc':  $products->orderBy('unit_price', 'desc'); break;
            case 'rand':        $products->inRandomOrder(); break;
            default:            $products->orderByRaw('FIELD(id, ' . implode(',', $productIds) . ')'); break;
        }

         return new ProductMiniCollection($products->paginate($request->limit ?? 10));
    }

    private function buildFilteredProductsQuery(Request $request, $brand_id = null, $tag = null)
    {
        $keys = array_keys($request->all());
        $values = array_values($request->all());
        $metaObjectItems = MetaObjectItem::with('metaObject')->whereIn('title', $values)->get()->toArray();
        $customFields = ProductCustomField::select('id', 'slug')
            ->whereIn('type', ['single_select', 'multi_select'])
            ->whereIn('slug', $keys)
            ->get()->toArray();

        $customFieldsValues = array_map(function ($customField) use ($request) {
            return $request[$customField['slug']];
        }, $customFields);

        $filteredCustomFields = array_filter($customFieldsValues, fn($v) => $v !== null);
        $query = trim($request->keyword);
        $sort_by = $request->sort_by;
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $seller_id = $request->seller_id;
        $selected_attribute_values = $request->selected_attribute_values ?? [];
        $selected_color = $request->color;

        $conditions = ['published' => 1];

        if ($brand_id) {
            $conditions['brand_id'] = $brand_id;
        } elseif ($request->brand) {
            $brand_id = Brand::where('slug', $request->brand)->value('id');
            $conditions['brand_id'] = $brand_id;
        }

        if ($seller_id) {
            $conditions['user_id'] = Seller::findOrFail($seller_id)->user->id;
        }

        $products = Product::with('thumbnail_image', 'stocks', 'flash_deal_product', 'productprices', 'customFieldsData.metaObject.items');

        if (!empty($filteredCustomFields)) {
            $products->whereHas('customFieldsData', function (Builder $query) use ($metaObjectItems) {
                $metaObjectIds = array_column($metaObjectItems, 'meta_object_id');
                $idsToSearch = array_column($metaObjectItems, 'id');

                $query->whereIn('meta_object_id', $metaObjectIds)
                    ->where(function ($query) use ($idsToSearch) {
                        foreach ($idsToSearch as $id) {
                            $query->orWhereJsonContains('value', $id)
                                ->orWhere('value', 'LIKE', '%' . json_encode($id) . '%');
                        }
                    });
            });
        }

        $products->where($conditions);

        if ($min_price && $max_price) {
            $products->whereBetween('unit_price', [$min_price, $max_price]);
        }

        if ($query) {
            $this->store($query);
            $products->where(function ($q) use ($query) {
                $q->where('name', 'like', "%$query%")
                ->orWhere('tags', 'like', "%$query%");
            });
        }

        if ($tag) {
            $products->where('tags', 'like', "%$tag%");
        }

        switch ($sort_by) {
            case 'newest':      $products->orderBy('created_at', 'desc'); break;
            case 'oldest':      $products->orderBy('created_at', 'asc'); break;
            case 'price-asc':   $products->orderBy('unit_price', 'asc'); break;
            case 'price-desc':  $products->orderBy('unit_price', 'desc'); break;
            default:            $products->orderBy('id', 'desc'); break;
        }

        if ($selected_color) {
            $products->where('colors', 'like', '%"'.$selected_color.'"%');
        }

        if (!empty($selected_attribute_values)) {
            foreach ($selected_attribute_values as $value) {
                $products->where('choice_options', 'like', '%"'.$value.'"%');
            }
        }

        return [
            'products' => $products->with('taxes'),
            'query' => $query,
            'sort_by' => $sort_by,
            'seller_id' => $seller_id,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'selected_attribute_values' => $selected_attribute_values,
            'selected_color' => $selected_color,
            'tag' => $tag
        ];
    }

    public function store($keyword)
    {
        $search = Search::where('query', $keyword)->first();
        if($search != null){
            $search->count = $search->count + 1;
            $search->save();
        }
        else{
            $search = new Search;
            $search->query = $keyword;
            $search->save();
        }
    }
}
