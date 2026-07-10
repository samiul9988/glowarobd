<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $currentlyAuthenticatedUser;

    public function __construct() 
    {
        // Fetch the currently Authenticated User with relation
        $this->currentlyAuthenticatedUser = auth()->check() ? auth()->user()->load('customeringroup.group') : null;
        view()->share('currentlyAuthenticatedUser', $this->currentlyAuthenticatedUser);
    }
}
