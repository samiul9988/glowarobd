<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class SafeCache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'safecache';
    }
}
