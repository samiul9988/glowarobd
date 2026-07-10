<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\CustomerResource;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function show($id)
    {
        return new CustomerResource(Customer::find($id));
    }
}
