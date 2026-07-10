<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot the trait.
     */
    protected static function bootHasSlug()
    {
        static::creating(function ($model) {
            $slugable = filled($model->{$model->slugSourceColumn()})
                ? $model->{$model->slugSourceColumn()}
                : $model->{$model->slugColumn()};

            $model->generateSlug($slugable);
        });

        static::updating(function ($model) {
            if ($model->isDirty($model->slugColumn()) || $model->isDirty($model->slugSourceColumn())) {
                $slugable = filled($model->{$model->slugSourceColumn()})
                    ? $model->{$model->slugSourceColumn()}
                    : $model->{$model->slugColumn()};

                $model->generateSlug($slugable);
            }
        });
    }

    /**
     * Generate a unique slug for the model.
     */
    public function generateSlug($slugable = null)
    {
        $slug = Str::slug($slugable ?? $this->{$this->slugSourceColumn()});
        $originalSlug = $slug;
        $count = 1;

        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $count++;
        }

        $this->{$this->slugColumn()} = $slug;
    }

    /**
     * Check if a slug already exists in the database.
     */
    protected function slugExists($slug)
    {
        $query = static::where($this->slugColumn(), $slug);

        if ($this->exists) {
            $query->where($this->getKeyName(), '!=', $this->getKey());
        }

        return $query->exists();
    }

    /**
     * Get the name of the column that stores the slug.
     *
     * @return string
     */
    protected function slugColumn()
    {
        return property_exists($this, 'slugColumn') ? $this->slugColumn : 'slug';
    }

    /**
     * Get the name of the column used to generate the slug.
     *
     * @return string
     */
    protected function slugSourceColumn()
    {
        return property_exists($this, 'slugSourceColumn') ? $this->slugSourceColumn : 'name';
    }
}
