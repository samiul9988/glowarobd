<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    protected $fillable = [
        'code',
        'order_id',
        'user_id',
        'ticket_category_id',
        'name',
        'phone',
        'issue',
        'subject',
        'details',
        'files',
        'priority',
        'status',
        'assign_to',
        'rating',
        'review',
        'viewed',
        'client_viewed',
        'created_at',
        'updated_at',
        'closed_at',
        'closed_by',
    ];
    public function user(){
    	return $this->belongsTo(User::class);
    }

    public function ticketReplies()
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at', 'desc');
    }

    public function order() : HasOne
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function logs()
    {
        return $this->hasMany(TicketLog::class);
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }
}
