<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Searchable;

    public function getScoutKey()
    {
        return $this->id;
    }

    public function getScoutKeyName()
    {
        return 'id';
    }

    public function searchableAs()
    {
        return config('scout.prefix') . 'products_index';
    }

    public function shouldBeSearchable()
    {
        return $this->published == 1 && get_setting('enable_meilisearch') == 1;
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            // 'search_name' => str_replace('.', ' ', strtolower($this->name)),
            'search_name' => normalizeMeiliSearchText($this->name),
            'short_description' => strip_tags($this->short_description),
            'tags' => $this->tags,
            'category' => $this->category?->name ?? null,
            'brand' => $this->brand?->name ?? null,
            // These are for filtering
            'id' => (int) $this->id,
            'barcode' => $this->barcode,
            'published' => (int) $this->published,
            'brand_id' => $this->brand_id ? (int) $this->brand_id : null,
            'category_id' => $this->category_id ? (int) $this->category_id : null,
            'unit_price' => (float) $this->unit_price,
            'created_at' => $this->created_at?->timestamp,
            'num_of_sale' => (int) $this->num_of_sale,
            'rating' => (float) $this->rating,
            'approved' => (int) $this->approved,
            'auction_product' => (int) $this->auction_product,
            'added_by' => $this->added_by,
            'user_id' => $this->user_id
        ];
    }

    protected $fillable = [
        'name',
        'added_by',
        'user_id',
        'category_id',
        'brand_id',
        'video_provider',
        'video_link',
        'unit_price',
        'purchase_price',
        'unit',
        'slug',
        'colors',
        'choice_options',
        'variations',
        'thumbnail_img',
        'faq_img',
        'meta_title',
        'meta_description',
        'usage_duration'
    ];

    public $timestamps = false;

    protected $with = ['taxes', 'stocks'];
    // protected $with = ['taxes', 'product_translations'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_viewed_at' => 'datetime',
    ];

    protected $appends = ['app_price', 'web_price', 'current_stock', 'is_new'];

    public static function generateUniqueSlug(string $slugable, ?int $except = null): string
    {
        $slug = Str::slug($slugable);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->when($except, fn ($query) => $query->where('id', '!=', $except))->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    public function getTranslation($field = '', $lang = false)
    {
        return $this->$field;
        $lang = $lang == false ? App::getLocale() : $lang;
        $product_translations = $this->product_translations->where('lang', $lang)->first();
        return $product_translations != null ? $product_translations->$field : $this->$field;
    }

    public function scopeAvailableInStock(Builder $query)
    {
        $now = Carbon::now();

        return $query->where(function ($q) use ($now) {
            // Allow stock out purchases → always allowed
            // $q->where('allow_stock_out_purchases', 1)
              // OR stock must exist OR valid preorder
              $q->where(function ($q) use ($now) {
                  // Normal products with stock
                  $q->whereHas('stocks', function ($stock) {
                      $stock->where('qty', '>', 0);
                  })
                  // OR preorder products in valid window
                  ->orWhere(function ($q) use ($now) {
                      $q->where('pre_order', 1)
                        ->where('preorder_start_date', '<=', $now)
                        ->where('preorder_end_date', '>=', $now);
                  });
              });
        });
    }

    public function scopeNotNew($query)
    {
        return $query->where('created_at', '<=', Carbon::now()->subDays(30));
    }

    public function scopePublished($query)
    {
        return $query->where('published', 1);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', 1);
    }

    public function giftOfferItems()
    {
        return $this->hasMany(GiftOfferItem::class);
    }

    public function customFieldsData()
    {
        return $this->hasMany(ProductsCustomFieldsData::class);
    }

    public function product_translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function last30DaysSales()
    {
        return $this->hasMany(OrderDetail::class)
            ->where('created_at', '>=', now()->subDays(30))
            ->where('delivery_status', 'delivered')
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->groupBy('product_id');
    }

    public function last30DaysVisits()
    {
        return $this->hasMany(ProductVisit::class)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('product_id, COUNT(*) as total_visits')
            ->groupBy('product_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('status', 1)->whereNotNull('product_id');
    }
    public function productprices()
    {
        return $this->hasMany(ProductPrice::class)->where('status', 1);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function latestStock()
    {
        return $this->hasOne(ProductStock::class)->latest('created_at');
    }

    public function taxes()
    {
        return $this->hasMany(ProductTax::class);
    }

    public function flash_deal_product()
    {
        return $this->hasOne(FlashDealProduct::class)->latest('created_at');
    }

    public function bids()
    {
        return $this->hasMany(AuctionProductBid::class);
    }

    public function fiveStarsCount()
    {
        return $this->reviews()->where('rating', 5);
    }

    public function fourStarsCount()
    {
        return $this->reviews()->where('rating', 4);
    }

    public function threeStarsCount()
    {
        return $this->reviews()->where('rating', 3);
    }

    public function twoStarsCount()
    {
        return $this->reviews()->where('rating', 2);
    }

    public function oneStarsCount()
    {
        return $this->reviews()->where('rating', 1);
    }

    public function thumbnail_image()
    {
        return $this->hasOne(Upload::class, 'id', 'thumbnail_img');
    }

    public function getAppPriceAttribute()
    {
        $originalValue = (object) $this->attributes;
        $appPrice = getMinimumPriceByVariant($originalValue, $this->stocks->first(), 'app', 1, null);

        return $appPrice;
    }

    public function getWebPriceAttribute()
    {
        $originalValue = (object) $this->attributes;
        $webPrice = getMinimumPriceByVariant($originalValue, $this->stocks->first(), 'web', 1, null);

        return $webPrice;
    }

    public function getCurrentStockAttribute()
    {
        // return $this->stocks->first()->qty;
        $stock = $this->stocks->first();
        return $stock ? $stock->qty : 0;
    }

    public function getIsNewAttribute()
    {
        return Carbon::parse($this->created_at)->diffInDays() <= 30;
    }

    public function scopeSearch(Builder $query, $queryKey)
    {
        return $query->where(function ($q) use ($queryKey) {
            // Partial matching on the product `name` and `tags`
            $q->where('name', 'LIKE', "%{$queryKey}%")
                ->orWhere('tags', 'LIKE', "%{$queryKey}%");

            // Matching based on the related `brands` and `categories` `name` fields
            $q->orWhereHas('brand', function ($q) use ($queryKey) {
                $q->where('name', 'LIKE', "%{$queryKey}%")
                    ->orWhereRaw("SOUNDEX(name) = SOUNDEX(?)", [$queryKey]);
            });

            $q->orWhereHas('category', function ($q) use ($queryKey) {
                $q->where('name', 'LIKE', "%{$queryKey}%")
                    ->orWhereRaw("SOUNDEX(name) = SOUNDEX(?)", [$queryKey]);
            });

            // Add Soundex for similar pronunciation matching
            $q->orWhereRaw("SOUNDEX(name) = SOUNDEX(?)", [$queryKey])
                ->orWhereRaw("SOUNDEX(tags) = SOUNDEX(?)", [$queryKey]);
        });
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function lastPurchaseOrderItem()
    {
        return $this->hasOne(PurchaseOrderItem::class)->latest('updated_at');
    }

    public function getLastPurchaseOrderItemByVariant($variant = null)
    {
        return $this->purchaseOrderItems()
            ->when($variant, function ($query) use ($variant) {
                $query->where('variant', $variant);
            })
            ->latest('created_at')
            ->first();
    }


    public function merchantProducts()
    {
        return $this->hasMany(MerchantProduct::class);
    }

    public function highlighted_items()
    {
        return $this->morphMany(HighlightedItem::class, 'linkable');
    }

    public function videos()
    {
        return $this->belongsToMany(Video::class, 'product_video', 'product_id', 'video_id');
    }

    // public function waitlists()
    // {
    //     return $this->hasMany(\Modules\Waitlist\Entities\Waitlist::class);
    // }
    public function visits()
    {
        return $this->hasMany(ProductVisit::class, 'product_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
