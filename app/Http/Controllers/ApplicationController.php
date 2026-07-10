<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationTypes;
use App\Models\Application;
use App\Models\JobApplication;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();
        $search = trim($request->string('search')->toString());

        $applications = Application::query()
            ->with([
                'user:id,name',
                'modifier:id,name',
            ])
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($type !== '' && ApplicationTypes::tryFrom($type), function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('subject', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(! (Auth::check() && Auth::user()->user_type === 'admin'), function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->latest()
            ->paginate(15);

        return view('backend.applications.index', compact('applications'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'application_type' => ['required', new Enum(ApplicationTypes::class)],
            'subject' => 'required|string|max:255',
            'reason' => 'required|string',
        ]);

        if ($request->application_type === 'leave') {
            $request->validate([
                'duration' => 'required',
                'single_date' => 'required_if:duration,single',
                'start_date' => 'required_if:duration,multiple',
                'end_date' => 'required_if:duration,multiple|nullable|after_or_equal:start_date',
            ]);
        }

        DB::transaction(function () use ($request) {
            $app = Application::create([
                'user_id' => $request->user_id,
                'type' => $request->application_type,
                'subject' => $request->subject,
                'content' => $request->reason,
                'attachments' => $request->attachments ? explode(',', $request->attachments) : null,
            ]);

            $model = null;
            if ($request->application_type === 'leave') {
                $model = Leave::create([
                    'user_id' => $request->user_id,
                    'start_date' => $request->start_date ?? $request->single_date,
                    'end_date' => $request->end_date ?? $request->start_date ?? $request->single_date,
                ]);
            }

            if ($model) {
                $app->update(['applicable_id' => $model->id, 'applicable_type' => get_class($model)]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
        ]);
    }

    public function show($id)
    {
        $application = Application::query()
            ->with([
                'user:id,name,email,phone',
                'modifier:id,name',
            ])
            ->findOrFail($id);

        if (Auth::user()->user_type !== 'admin' && $application->user_id !== Auth::id()) {
            abort(403);
        }

        return view('backend.applications.show', compact('application'));
    }

    // For Applicant
    public function update(Request $request, $id)
    {
        $application = Application::findOrFail($id);

        if ($application->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($application->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This application cannot be updated'], 400);
        }

        $request->validate([
            'application_type' => ['required', new Enum(ApplicationTypes::class)],
            'subject' => 'required|string|min:2|max:255',
            'reason' => 'required|string|min:5',
        ]);

        if ($request->application_type === 'leave') {
            $request->validate([
                'duration' => 'required|in:single,multiple',
                'single_date' => 'required_if:duration,single',
                'start_date' => 'required_if:duration,multiple',
                'end_date' => 'required_if:duration,multiple|nullable|after_or_equal:start_date',
            ]);
        }

        DB::transaction(function () use ($request, $application) {
            $application->update([
                'type' => $request->application_type,
                'subject' => $request->subject,
                'content' => $request->reason,
                'attachments' => $request->attachments ? explode(',', $request->attachments) : null,
                'modified_by' => $application->user_id === Auth::id() ? null : Auth::id(),
                'modified_at' => $application->user_id === Auth::id() ? null : now(),
            ]);

            if ($request->application_type === ApplicationTypes::LEAVE->value) {
                $startDate = $request->start_date ?? $request->single_date;
                $endDate = $request->end_date ?? $request->start_date ?? $request->single_date;

                $leaveData = [
                    'user_id' => $application->user_id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];

                if ($application->applicable instanceof Leave) {
                    $application->applicable->update($leaveData);
                } else {
                    $leave = Leave::create($leaveData);
                    $application->update([
                        'applicable_id' => $leave->id,
                        'applicable_type' => Leave::class,
                    ]);
                }
            } else {
                $application->applicable->delete();
                $application->update([
                    'applicable_id' => null,
                    'applicable_type' => null,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Application updated successfully',
        ]);
    }

    // For HR Admin
    public function manage(Request $request, $id)
    {
        $application = Application::find($id);

        if (! $application) {
            return response()->json(['success' => false, 'message' => 'Application not found'], 404);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        if ($application->applicable instanceof Leave && $request->status === 'approved') {
            $request->validate([
                'approved_start_date' => 'required|date',
                'approved_end_date' => 'required|date|after_or_equal:approved_start_date',
                'paid_days' => 'nullable|integer|min:0',
            ]);
        }

        DB::transaction(function () use ($request, $application) {
            $application->update([
                'status' => $request->status,
                'note' => $request->note ?: $application->note,
                'modified_by' => Auth::id(),
                'modified_at' => now(),
            ]);

            if ($application->applicable instanceof Leave) {
                $application->applicable->update([
                    'approved_start_date' => $request->status === 'approved' ? $request->approved_start_date : null,
                    'approved_end_date' => $request->status === 'approved' ? $request->approved_end_date : null,
                    'paid_days' => $request->status === 'approved' ? max($request->paid_days, 0) : 0,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Application updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $application = Application::find($id);

        if (! $application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found',
            ], 404);
        }

        if ($application->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($application->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This application cannot be deleted'], 400);
        }

        if ($application->applicable) {
            $application->applicable->delete();
        }
        $application->delete();

        return response()->json([
            'success' => true,
            'message' => 'Application deleted successfully',
        ]);
    }

    public function getByMonth(Request $request, $id)
    {
        $date = $request->month ? Carbon::createFromFormat('Y-m', $request->month) : now();
        $applications = Application::where('user_id', $id)
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->get();

        $html = view('backend.applications.partials.list', compact('applications'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }
}
