<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalarySheet extends Model
{
    protected $table = 'salary_sheets';

    public $with = ['details', 'generatedBy'];

    protected $guarded = [];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(SalarySheetDetails::class, 'salary_sheets_id', 'id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by')->withDefault([
            'name' => 'System',
        ]);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForPeriod(Builder $query, int $month, int $year): Builder
    {
        return $query->where('month', $month)->where('year', $year);
    }
}
