<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $casts = [
        'force_apply' => 'boolean',
        'featured' => 'boolean',
        'status' => 'boolean',
        'group_ids' => 'array',
    ];

    public function user(){
    	return $this->belongsTo(User::class, 'user_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function customerAssignments()
    {
        return $this->hasMany(CouponCustomerAssignment::class);
    }

    public function orderCoupons()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function scopeForUser($query, $user)
    {
        if($user->user_type === 'admin'){
            return $query;
        }
        return $query->where('assigned_to', $user->id);
    }

    public function scopeForGroup($query, ?int $groupId = null)
    {
        return $query->whereNotNull('group_ids')
            ->when($groupId, function ($q) use ($groupId) {
                $q->whereJsonContains('group_ids', $groupId);
            });
    }

    public function scopeForAll($query)
    {
        return $query->whereNull('group_ids')
            ->whereNull('assigned_to');
    }

    public function scopeValid($query)
    {
        $currentDate = now()->timestamp;
        return $query->where('status', 1)->where('start_date', '<=', $currentDate)
                    ->where('end_date', '>=', $currentDate);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', 1);
    }

    public function usage()
    {
        return $this->hasMany(CouponUsage::class, 'coupon_id', 'id');
    }
}
