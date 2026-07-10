<?php
namespace Modules\Waitlist\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class Waitlist extends Model
{
    use Prunable;

    protected $table = 'waitlists';

    protected $fillable = ['product_id', 'contact', 'contact_type', 'notified'];

    protected $casts = [
        'notified' => 'boolean',
        'notified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $with = ['product'];

    public function prunable()
    {
        return static::where('notified', 1)->where('notified_at', '<=', now()->subDays(30));
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function scopeFiltered($query, $filters)
    {
        if (isset($filters['date'])) {
            $dateRange = explode(' to ', $filters['date']);
            if (count($dateRange) === 2) {
                $start = \Carbon\Carbon::parse($dateRange[0])->startOfDay();
                $end = \Carbon\Carbon::parse($dateRange[1])->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            }
        }

        if (isset($filters['notified'])) {
            $query->where('notified', $filters['notified']);
        }

        if (isset($filters['product'])) {
            $query->where('product_id', $filters['product']);
        }

        if (isset($filters['channel']) && in_array($filters['channel'], ['email', 'phone'])) {
            $query->where('contact_type', $filters['channel']);
        }

        if (isset($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('contact', 'like', "%{$searchTerm}%")
                  ->orWhereHas('product', function ($q2) use ($searchTerm) {
                      $q2->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        return $query;
    }
}
