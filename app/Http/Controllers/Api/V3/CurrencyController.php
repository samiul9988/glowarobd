<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\CurrencyCollection;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        return new CurrencyCollection(Currency::all());
    }
}
