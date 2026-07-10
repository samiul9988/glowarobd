<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortcutModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'dashboard',
        'status',
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function shortcuts()
    {
        return $this->hasMany(Shortcut::class);
    }
}
