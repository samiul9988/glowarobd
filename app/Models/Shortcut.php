<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shortcut extends Model
{
    use HasFactory;

    protected $fillable = [
        'shortcut_module_id',
        'name',
        'icon',
        'url',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function module()
    {
        return $this->belongsTo(ShortcutModule::class);
    }
}
