<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function index(): View
    {
        $isAdmin = Auth::check() && Auth::user()->user_type == 'admin';
        $permissions = json_decode(Auth::user()?->staff?->role?->permissions ?? '[]', true) ?? [];

        $canManage = $isAdmin || in_array('manage_holidays', $permissions);

        return view('backend.holidays.index', compact('canManage'));
    }

    public function events(): JsonResponse
    {
        $holidays = Holiday::all()->map(function (Holiday $holiday) {
            return [
                'id' => $holiday->id,
                'title' => $holiday->title,
                'start' => $holiday->date->format('Y-m-d'),
                'color' => $holiday->color ?? '#dc3545',
            ];
        });

        return response()->json($holidays);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'unique:holidays,date'],
        ]);

        $date = Carbon::parse($request->date)->startOfDay();

        if ($date->lt(Carbon::today())) {
            return response()->json(['message' => 'Cannot create a holiday in the past.'], 422);
        }

        $holiday = Holiday::create([
            'title' => $request->title,
            'date' => $date->toDateString(),
            'color' => $request->color ?? '#dc3545',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Holiday created successfully.',
            'holiday' => [
                'id' => $holiday->id,
                'title' => $holiday->title,
                'start' => $holiday->date->format('Y-m-d'),
                'color' => $holiday->color,
            ],
        ], 201);
    }

    public function storeBulk(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'dates' => ['required', 'array', 'min:1'],
            'dates.*' => ['required', 'date'],
        ]);

        $today = Carbon::today();
        $created = [];
        $skipped = [];

        foreach ($request->dates as $rawDate) {
            $date = Carbon::parse($rawDate)->startOfDay();

            if ($date->lt($today)) {
                $skipped[] = $date->toDateString() . ' (past)';
                continue;
            }

            if (Holiday::where('date', $date->toDateString())->exists()) {
                $skipped[] = $date->toDateString() . ' (already exists)';
                continue;
            }

            $holiday = Holiday::create([
                'title' => $request->title,
                'date' => $date->toDateString(),
                'color' => $request->color ?? '#dc3545',
                'created_by' => auth()->id(),
            ]);

            $created[] = [
                'id' => $holiday->id,
                'title' => $holiday->title,
                'start' => $holiday->date->format('Y-m-d'),
                'color' => $holiday->color,
            ];
        }

        return response()->json([
            'message' => count($created) . ' holiday(s) created' .
                (count($skipped) ? ', ' . count($skipped) . ' skipped.' : '.'),
            'created' => $created,
            'skipped' => $skipped,
        ], 201);
    }

    public function update(Request $request, Holiday $holiday): JsonResponse
    {
        if ($holiday->isPast()) {
            return response()->json(['message' => 'Cannot update a past holiday.'], 422);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $holiday->update(['title' => $request->title]);

        return response()->json([
            'message' => 'Holiday updated successfully.',
            'holiday' => [
                'id' => $holiday->id,
                'title' => $holiday->title,
                'start' => $holiday->date->format('Y-m-d'),
                'color' => $holiday->color,
            ],
        ]);
    }

    public function destroy(Holiday $holiday): JsonResponse
    {
        if ($holiday->isPast()) {
            return response()->json(['message' => 'Cannot delete a past holiday.'], 422);
        }

        $holiday->delete();

        return response()->json(['message' => 'Holiday deleted successfully.']);
    }
}
