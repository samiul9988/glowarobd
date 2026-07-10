<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ACCBank extends Model
{
    use HasFactory;

    protected $table = 'acc_banks';
    protected $fillable = ['bank_name', 'acc_name', 'acc_no', 'type', 'address', 'contact_no'];
    protected $appends = array('balance');

    public function getHeadAttribute(){
        return $this->attributes['bank_name'] . ' ' . $this->attributes['acc_no'];
    }

    public function transactions(){
        return $this->hasMany(AccTransaction::class, 'head', 'head');
    }

    public function getBalanceAttribute(){
        return $this->transactions()->sum('debit') - $this->transactions()->sum('credit');
    }
}
