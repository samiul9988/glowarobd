<?php

namespace App\Http\Controllers\Api\V2;

use Exception;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Artisan;
use App\Http\Resources\V2\BusinessSettingCollection;

class BusinessSettingController extends Controller
{
    public function index()
    {
        return new BusinessSettingCollection(BusinessSetting::all());
    }
}
