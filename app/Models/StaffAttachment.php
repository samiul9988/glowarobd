<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAttachment extends Model
{
    protected $fillable = [
        'staff_id',
        'type',
        'label',
        'upload_id',
    ];

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
