<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $table = 'supplier';
    protected $fillable = ['name', 'contact_number', 'address', 'template_id', 'logo', 'user_id'];
    protected $appends = ['balance'];

    public function getHeadAttribute(){
        return $this->attributes['name'] . ' ' . $this->attributes['contact_number'];
    }

    public function getBalanceAttribute(){
        return $this->transactions->sum('debit') - $this->transactions->sum('credit');
    }

    public function purchaseorders()
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id', 'id');
    }

    public function userinfo()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function payments(){
        return $this->morphMany(Payment::class, 'reference');
    }

    public function transactions(){
        return $this->hasMany(AccTransaction::class, 'head', 'head');
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'id')->where('status', 1);
    }

    public function returnedPurchases()
    {
        return $this->hasMany(ReturnSupplier::class, 'supplier_id', 'id');
    }
}
