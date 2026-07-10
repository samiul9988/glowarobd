<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'frontend_color',
        'logo',
        'footer_logo',
        'admin_logo',
        'admin_login_background',
        'admin_login_sidebar',
        'favicon',
        'site_name',
        'address',
        'description',
        'phone',
        'email',
        'facebook',
        'instagram',
        'twitter',
        'youtube',
        'google_plus',
    ];
}
