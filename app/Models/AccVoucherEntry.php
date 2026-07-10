<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccVoucherEntry extends Model
{
    use HasFactory;

    protected $table = "acc_voucher_entries";

    protected $fillable = ['date', 'vno', 'voucher_type', 'entry_type', 'debit', 'credit', 'particular_id', 'particular_type', 'naration', 'note', 'attachement'];

    public function particular(){
        return $this->morphTo('particular', 'particular_type');
    }

    public function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
