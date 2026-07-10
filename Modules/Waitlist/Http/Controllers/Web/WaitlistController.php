<?php

namespace Modules\Waitlist\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Waitlist\Entities\Waitlist;
use Illuminate\Contracts\Support\Renderable;
use Modules\Waitlist\Services\WaitlistService;

class WaitlistController extends Controller
{
    protected $waitlistService;

    public function __construct(WaitlistService $waitlistService)
    {
        $this->waitlistService = $waitlistService;
    }
}
