<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccHead extends Model
{
    use HasFactory;

    // The five types of Account titles are Income, Expense, Liability, Equity, and Assets. These are classified under different circumstances and the nature of the demands. For example, the sale comes under the Income section in types of accounts.
    protected $table = "acc_heads";

    protected $fillable = ['head', 'parent_head', 'sub_head', 'reference_id', 'reference_type', 'user_id'];

    public function scopeParentHeads(){
        return collect([
            'income',
            'expense',
            'liability',
            'equity',
            'assets',
        ]);
    }

    public function scopeFilterSubHeads($search = null){
        if ($search !== null) {
            return $this->query()->select('sub_head')->where('parent_head', '=', $search)->groupBy('sub_head');
        }

        return $this->query()->select('sub_head')->groupBy('sub_head')->get()->toArray();
    }

    public function transactions(){
        return $this->hasMany(AccTransaction::class, 'head', 'head');
    }

    public function getBalance(){
        return $this->transactions()->sum('debit') - $this->transactions()->sum('credit');
    }
}
