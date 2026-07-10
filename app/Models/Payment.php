<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'invoice_no', 'date', 'payable_id', 'payable_type', 'seller_id', 'amount', 'payment_details', 'payment_method',
        'txn_code', 'user_id', 'remarks', 'status', 'reference_type', 'reference_id'
    ];

    public function payable(){
        return $this->morphTo('payable');
    }

    public function referenceable(){
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }
}
