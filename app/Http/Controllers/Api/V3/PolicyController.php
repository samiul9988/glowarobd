<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\PolicyCollection;
use App\Models\Page;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function sellerPolicy()
    {
        return new PolicyCollection(Page::where('type', 'seller_policy_page')->get());
    }

    public function supportPolicy()
    {
        return new PolicyCollection(Page::where('type', 'support_policy_page')->get());
    }

    public function returnPolicy()
    {
        return new PolicyCollection(Page::where('type', 'return_policy_page')->get());
    }
}
