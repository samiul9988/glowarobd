<?php

namespace App\Models;

use App\Traits\HasCallLogs;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasCallLogs;

    protected $casts = [
        'notes' => 'array',
    ];

    public function lock(User $user): int
    {
        $duration = get_setting('order_lock_duration', 10);

        // Check if order is already locked by the same user and within duration
        if ($this->locked &&
            $this->locked_by === $user->id &&
            $this->locked_at &&
            now()->diffInMinutes($this->locked_at) < $duration
        ) {
            return $duration; // Return current duration
        }

        // Apply new lock
        // $this->delivery_status = 'processing';
        $this->locked = true;
        $this->locked_at = now();
        $this->locked_by = $user->id;
        $this->save();

        return $duration;
    }

    public function unlock() : bool
    {
        $this->locked = false;
        $this->locked_at = null;
        $this->locked_by = null;
        // $this->delivery_status = 'pending';
        $this->save();
        return true;
    }

    public function isLocked() : bool
    {
        $duration = get_setting('order_lock_duration', 10);
        if ($duration > 0) {
            return $this->locked &&
                $this->locked_at &&
                now()->diffInMinutes($this->locked_at) < $duration;
        }
        return $this->locked;
    }

    public function unlockIn(): int
    {
        if (!$this->locked || !$this->locked_at) {
            return 0;
        }

        $durationMinutes = get_setting('order_lock_duration', 10);
        $durationSeconds = $durationMinutes * 60;
        $elapsedSeconds = now()->diffInSeconds($this->locked_at);
        $remainingSeconds = $durationSeconds - $elapsedSeconds;

        return max(0, $remainingSeconds);
    }

    public function extendLock() : bool
    {
        // $duration = get_setting('order_lock_duration', 10);
        if ($this->locked && $this->locked_at) {
            $this->locked_at = now();
            $this->save();
            return true;
        }
        return false;
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function packagedBy()
    {
        return $this->belongsTo(User::class, 'packaged_by');
    }

    public function scopeMerchant($query)
    {
        return $query->whereNotNull(['merchant_source', 'merchant_order_id']);
    }
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class)->where('quantity', '>', 0);
    }
    public function allOrderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function refund_requests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(OrderLog::class);
    }

    public function seller()
    {
        return $this->hasOne(Shop::class, 'user_id', 'seller_id');
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function club_point()
    {
        return $this->hasMany(ClubPoint::class);
    }

    public function delivery_boy()
    {
        return $this->belongsTo(User::class, 'assign_delivery_boy', 'id');
    }
    public function user_group()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function proxy_cart_reference_id()
    {
        return $this->hasMany(ProxyPayment::class)->select('reference_id');
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_details');
    }

    // Only active payments
    public function payments(){
        return $this->morphMany(Payment::class, 'reference')->where('status', 1);
    }
    // Only active last payment
    public function lastPayment(){
        return $this->morphOne(Payment::class, 'reference')->where('status', 1)->latestOfMany();
    }
    // All payments
    public function allPayments(){
        return $this->morphMany(Payment::class, 'reference');
    }

    public function feedback() {
        return $this->hasOne(OrderFeedback::class);
    }

    public function getRatingAttribute(): int
    {
        return $this->feedback->rating ?? 0;
    }

    public function orderCoupons()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'order_coupons');
    }

    // Relation to order cancellations
    public function cancellation()
    {
        return $this->hasOne(OrderCancellation::class);
    }

    public function returnRequest()
    {
        return $this->hasOne(OrderReturn::class);
    }

    public function pendingReturnRequest()
    {
        return $this->hasOne(OrderReturn::class)->whereIn('status', ['pending', 'processing']);
    }

    public function orderTrack()
    {
        return $this->hasOne(OrderTrack::class);
    }

    protected static function booted()
    {
        static::saving(function ($order) {
            if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
                $order->due_amount = 0;
            }
        });
    }
}
