<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'content',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'type' => \App\Enums\TemplateTypes::class,
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            $template->name = self::generateUniqueName($template->name);
            $template->created_by = auth()->user()->id ?? null;
            $template->updated_by = auth()->user()->id ?? null;
        });

        static::updating(function ($template) {
            $template->name = self::generateUniqueName($template->name, $template->id);
            $template->updated_by = auth()->user()->id ?? null;
        });
    }

    public static function generateUniqueName(string $name, ?int $id = null): string
    {
        $baseName = $name;
        $counter = 1;

        while (self::where('name', $name)->when($id, fn ($query) => $query->where('id', '!=', $id))->exists()) {
            $name = $baseName . ' (' . $counter . ')';
            $counter++;
        }

        return $name;
    }
}
