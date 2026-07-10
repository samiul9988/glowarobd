<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Smsuser extends Model
{
    use HasFactory;
    protected $table = 'sms_user';
    protected $fillable = [
        'mobile_number'
    ];
}
