<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\SettingsCollection;
use App\Models\AppSettings;

class SettingsController extends Controller
{
    public function index()
    {
        return new SettingsCollection(AppSettings::all());
    }
}
