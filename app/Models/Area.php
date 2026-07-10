<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Area extends Model
{
    public function getTranslation($field = '', $lang = false){
        return $this->$field;
        $lang = $lang == false ? App::getLocale() : $lang;
        $area_translation = $this->hasMany(AreaTranslation::class)->where('lang', $lang)->first();
        return $area_translation != null ? $area_translation->$field : $this->$field;
    }

    public function area_translations(){
       return $this->hasMany(AreaTranslation::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
