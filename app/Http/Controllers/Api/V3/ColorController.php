<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\ColorCollection;
use App\Models\Color;

class ColorController extends Controller
{
    public function index()
    {
        return new ColorCollection(Color::all());
    }
}
