<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccTransaction extends Model
{
    use HasFactory;

    protected $table = 'acc_transactions';

    protected $fillable = ['date', 'user_id', 'vno', 'head', 'head_type', 'head_id', 'debit', 'credit', 'note', 'image', 'description'];

    public function scopeGetBalance($head = null){

        $totalDebit = 0;
        $totalCredit = 0;
        if ($head !== null) {
            $totalDebit = $this->query()->where('head', '=', $head)->sum('debit');
            $totalCredit = $this->query()->where('head', '=', $head)->sum('credit');
        }

        return $totalDebit - $totalCredit;
    }
}
