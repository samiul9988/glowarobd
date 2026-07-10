<?php

namespace Modules\Waitlist\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Waitlist\Services\WaitlistService;
use Modules\Waitlist\Http\Requests\CreateWaitlistRequest;

class WaitlistController extends Controller
{
    protected $waitlistService;

    public function __construct(WaitlistService $waitlistService)
    {
        $this->waitlistService = $waitlistService;
    }

    public function store(CreateWaitlistRequest $request): JsonResponse
    {
        if ($request->validated()) {
            $this->waitlistService->create($request->validated());

            return ResponseHelper::success('Waitlist added successfully.', 201);
        }

        return ResponseHelper::error('Invalid data provided.', 400, $request->errors());
    }
}
