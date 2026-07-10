<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class City extends Model
{
    public function getTranslation($field = '', $lang = false){
        return $this->$field;
        $lang = $lang == false ? App::getLocale() : $lang;
        $city_translation = $this->hasMany(CityTranslation::class)->where('lang', $lang)->first();
        return $city_translation != null ? $city_translation->$field : $this->$field;
    }

    public function city_translations(){
       return $this->hasMany(CityTranslation::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
