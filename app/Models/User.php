<?php

namespace App\Models;

use App\Models\Cart;
use App\Helpers\JWToken;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\EmailVerificationNotification;
use App\Traits\HasCallLogs;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasApiTokens, HasCallLogs;

    public function sendEmailVerificationNotification()
    {
        $this->notify(new EmailVerificationNotification());
    }

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'name', 'email', 'password', 'temp_password', 'address', 'city', 'postal_code', 'phone', 'country', 'provider_id', 'email_verified_at', 'verification_code', 'point_balance', 'app_id', 'app_key', 'ip', 'user_agent', 'skin_concern', 'skin_type', 'satisfaction'
    ];

    /**
    * The attributes that should be hidden for arrays.
    *
    * @var array
    */
    protected $hidden = [
        'password', 'temp_password', 'remember_token', 'verification_code', 'app_id', 'app_key', 'ip', 'user_agent'
    ];

    protected $casts = [
        'skin_concern' => 'array',
    ];

    public function generateAppId()
    {
        $this->app_id = $this->id . 'M' . time();
        $this->app_key = JWToken::generate($this);
        $this->save();
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function apiLogs()
    {
        return $this->hasMany(MerchantApiLog::class);
    }

    public function wishlists()
    {
    return $this->hasMany(Wishlist::class);
    }

    public function customeringroup(){
        return $this->hasOne(Customeringroup::class, 'user_id','id')->where('status',1)->latest();
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function seller()
    {
        return $this->hasOne(Seller::class);
    }

    public function affiliate_user()
    {
        return $this->hasOne(AffiliateUser::class);
    }

    public function affiliate_withdraw_request()
    {
        return $this->hasMany(AffiliateWithdrawRequest::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function latestOrder()
    {
        return $this->hasOne(Order::class)->latest();
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class)->orderBy('created_at', 'desc');
    }

    public function club_point()
    {
        return $this->hasOne(ClubPoint::class);
    }

    public function customer_package()
    {
        return $this->belongsTo(CustomerPackage::class);
    }

    public function customer_package_payments()
    {
        return $this->hasMany(CustomerPackagePayment::class);
    }

    public function customer_products()
    {
        return $this->hasMany(CustomerProduct::class);
    }

    public function seller_package_payments()
    {
        return $this->hasMany(SellerPackagePayment::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function product_bids() {
        return $this->hasMany(AuctionProductBid::class);
    }

    public function customer_group() {
        return $this->hasOne(Customeringroup::class)->where('status', '1');
    }

    public function scopeMerchant($query)
    {
        return $query->where('user_type', 'merchant');
    }

    public function scopeActive($query)
    {
        return $query->where('banned', 0);
    }

    public function payments(){
        return $this->morphMany(Payment::class, 'reference');
    }

    public function packageOrders()
    {
        return $this->hasMany(Order::class, 'packaged_by', 'id');
    }

    public function orderLogs()
    {
        return $this->hasMany(OrderLog::class, 'managed_by', 'id');
    }

    public function metaData()
    {
        return $this->hasMany(UserCrmMetaData::class);
    }

    public function meta($key, $default = null)
    {
        return $this->getMetaValue($key, $default);
    }
    public function getMetaValue($key, $default = null)
    {
        $item = $this->metaData?->where('key', $key)->first();
        $value = $item ? $item->value : $default;
        if (is_string($value) && $this->isJson($value)) {
            return json_decode($value, true);
        }
        return $value;
    }
    public function createMeta(array $metas)
    {
        $rows = [];
        foreach ($metas as $key => $value) {
            $rows[] = [
                'user_id' => $this->id,
                'key'     => $key,
                'value'   => is_array($value) ? json_encode($value) : $value,
            ];
        }
        \App\Models\UserCrmMetaData::upsert(
            $rows,
            ['user_id', 'key'], // Unique constraints for matching
            ['value'] // Columns to update
        );
    }
    protected function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function createdCoupons()
    {
        return $this->hasMany(Coupon::class, 'user_id');
    }

    public function assignedCoupons()
    {
        return $this->hasMany(Coupon::class, 'assigned_to');
    }

    // public function couponsAssignedByMe()
    // {
    //     return $this->hasMany(Coupon::class, 'assigned_by');
    // }

    public function usableCoupons()
    {
        return $this->hasMany(CouponCustomerAssignment::class, 'customer_id');
    }

    public function assignedCustomerCoupons()
    {
        return $this->hasMany(CouponCustomerAssignment::class, 'assigned_by');
    }

    public function referredOrders()
    {
        return $this->hasMany(CouponUsage::class, 'ref_id');
    }

    public function lastCallLog()
    {
        return $this->morphOne(CallLog::class, 'reference')->latestOfMany();
    }
}
