<?php

namespace Modules\Waitlist\Http\Controllers\Admin;

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

    public function index(Request $request): Renderable
    {
        $filters = $request->all();
        $filters['notified'] ??= 0;
        $waitlists = $this->waitlistService->paginated($filters);

        return view('waitlist::admin.index', compact('waitlists'));
    }

    public function destroy($id): JsonResponse
    {
        if ($this->waitlistService->delete($id)) {
            return ResponseHelper::success('Successfully deleted.', 200);
        }

        return ResponseHelper::error('Entry not found.', 404);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        if ($this->waitlistService->deleteAll($request->ids)) {
            return ResponseHelper::success('Successfully deleted.', 200);
        }

        return ResponseHelper::error('Entry not found.', 404);
    }

    public function notify(Request $request): JsonResponse
    {
        $id = $request->id;
        $notified = is_array($id) ? $this->waitlistService->notifyAll($id, $request->boolean('silent')) : $this->waitlistService->notify($id, $request->boolean('silent'));

        if ($notified) {
            $message = is_array($id) ?
            "$notified entries notified successfully." :
            'Successfully notified.';
            return ResponseHelper::success($message, 200);
        }

        return ResponseHelper::error('Nothing to notify.', 404);
    }
}
