<?php

namespace App\Http\Controllers;

use App\Enums\ShiftEnum;
use App\Exports\SalesContributionReportExport;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Mail\StaffMail;
use App\Models\Attendance;
use App\Models\CallLog;
use App\Models\CouponCustomerAssignment;
use App\Models\Order;
use App\Models\OrderFeedback;
use App\Models\OrderLog;
use App\Models\Role;
use App\Models\Staff;
use App\Models\StaffAttachment;
use App\Models\StaffEvent;
use App\Models\Template;
use App\Models\TicketLog;
use App\Models\User;
use App\Services\StaffReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class StaffController extends Controller
{
    protected $DASHBOARDS = [
        'Admin Dashboard' => 'admin_dashboard',
        'Customer Care Dashboard' => 'customer_care_dashboard',
        'Packaging Dashboard' => 'packaging_dashboard',
        'Account & Inventory Dashboard' => 'account_inventory_dashboard',
    ];

    public function index(Request $request)
    {
        $status = $request->input('status', 'active');
        $role = $request->input('role');
        $search = $request->input('search');
        $staffs = Staff::query()
            ->select('staff.*')
            ->join('users', 'users.id', '=', 'staff.user_id')
            ->with('user', 'role')
            ->where('users.banned', $status === 'active' ? 0 : 1)
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('staff.employee_id', 'like', "%{$search}%")
                        ->orWhere('users.name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%")
                        ->orWhere('users.phone', 'like', "%{$search}%");
                });
            })
            ->when(!empty($role), function ($query) use ($role) {
                $query->where('staff.role_id', $role);
            })
            ->orderByDesc('users.recent_login')
            ->paginate(20)
            ->appends(request()->query());

        $roles = Role::pluck('name', 'id');

        $counts = Cache::remember('staff_status_counts', 600, function () {
            $result = Staff::join('users', 'users.id', '=', 'staff.user_id')
                ->selectRaw('
                    COUNT(CASE WHEN users.banned = 0 THEN 1 END) as active,
                    COUNT(CASE WHEN users.banned = 1 THEN 1 END) as banned
                ')
                ->first();

            return [
                'active' => $result->active,
                'banned' => $result->banned,
            ];
        });

        return view('backend.staff.staffs.index', compact('staffs', 'roles', 'counts'));
    }

    public function ban(Request $request, $id)
    {
        $isAdmin = Auth::check() && Auth::user()->user_type == 'admin';
        $permissions = json_decode(Auth::user()?->staff?->role?->permissions ?? '[]', true) ?? [];
        if (! $isAdmin && ! in_array('edit_staff', $permissions)) {
            abort(403, 'You do not have permission to edit staff.');
        }
        $user = User::find($id);

        if (! $user) {
            flash('User not found.')->error();

            return back();
        }

        $user->banned = ! $user->banned;
        $user->save();

        Cache::forget('staff_status_counts');

        flash($user->banned ? 'Staff Deactivated Successfully' : 'Staff Activated Successfully')->success();

        return back();
    }

    public function create()
    {
        $isAdmin = Auth::check() && Auth::user()->user_type == 'admin';
        $permissions = json_decode(Auth::user()?->staff?->role?->permissions ?? '[]', true) ?? [];
        if (! $isAdmin && ! in_array('create_staff', $permissions)) {
            abort(403, 'You do not have permission to create staff.');
        }
        $roles = Role::all();
        return view('backend.staff.staffs.create', compact('roles'));
    }

    public function store(StoreStaffRequest $request)
    {
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->mobile ?? null;
        $user->gender = $request->gender ?? null;
        $user->date_of_birth = $request->date_of_birth ?: null;
        $user->user_type = 'staff';
        $user->password = Hash::make($request->password);

        if ($user->save()) {
            $saved = DB::transaction(function () use ($request, $user) {
                $staff = new Staff;
                $staff->user_id = $user->id;
                $staff->role_id = $request->role_id;
                $staff->employee_id = Staff::generateEmployeeId();
                $staff->personal_email = $request->personal_email ?? null;
                $staff->profile_picture = $request->profile_picture ?: null;
                $staff->address = $request->address ?: null;
                $staff->salary = $request->salary ?: null;
                $staff->educational_background = $request->educational_background ?: null;
                $staff->joining_date = $request->joining_date ?: null;
                $staff->shift = ShiftEnum::tryFrom($request->shift)?->value ?? ShiftEnum::DAY->value;
                $staff->working_hours = $request->working_hours ?? 8;
                $staff->weekly_offday = $request->weekly_offday ? array_values(array_filter($request->weekly_offday)) : null;
                $staff->emergency_contact = [
                    'father_name' => $request->ec_father_name ?? null,
                    'mother_name' => $request->ec_mother_name ?? null,
                    'spouse_name' => $request->ec_spouse_name ?? null,
                    'contact_number' => $request->ec_contact_number ?? null,
                ];
                $staff->employment_status = $request->employment_status ?? 'active';
                $staff->blood_group = $request->blood_group ?? null;
                $staff->resign_date = $request->resign_date ?: null;
                $staff->resignation_letter = $request->resignation_letter ?: null;
                $staff->termination_date = $request->termination_date ?: null;
                $staff->termination_reason = $request->termination_reason ?? null;
                $staff->bank_account = [
                    'bank_name' => $request->bank_name ?? null,
                    'account_no' => $request->account_no ?? null,
                    'branch' => $request->bank_branch ?? null,
                ];
                $staff->note = $request->note;

                if ($staff->save()) {
                    $this->syncStaffEvents($staff, $request);
                    $this->syncStaffAttachments($staff, $request);

                    if ($request->has('job_application_id') && $request->job_application_id > 0) {
                        $application = \App\Models\JobApplication::find($request->job_application_id);
                        if ($application) {
                            $log = [
                                'type' => 'staff_account_created',
                                'message' => 'Staff account created for the applicant.',
                                'user_id' => auth()->id(),
                                'user_name' => auth()->user()->name,
                                'created_at' => now()->toDateTimeString(),
                            ];
                            $application->logs = array_merge($application->logs ?? [], [$log]);
                            $application->staff_id = $staff->id;
                            $application->save();
                        }
                    }
                    return true;
                }

                return false;
            });

            if ($saved) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Staff has been inserted successfully',
                    ], 201);
                } else {
                    flash(('Staff has been inserted successfully'))->success();
                    return redirect()->route('staffs.index');
                }
            }
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 422);
        } else {
            flash(('Something went wrong'))->error();
            return back();
        }
    }

    public function edit($id)
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Exception $e) {
            abort(404, 'Staff not found.');
        }
        $staff = Staff::with('events', 'attachments')->findOrFail($decryptedId);

        $isAdmin = Auth::check() && Auth::user()->user_type == 'admin';
        $permissions = json_decode(Auth::user()?->staff?->role?->permissions ?? '[]', true) ?? [];
        if (! $isAdmin && ! in_array('edit_staff', $permissions) && $staff->user_id != Auth::id()) {
            abort(403, 'You do not have permission to edit staff.');
        }
        $roles = Role::all();

        return view('backend.staff.staffs.edit', compact('staff', 'roles', 'isAdmin'));
    }

    public function show($id)
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Exception $e) {
            abort(404, 'Staff not found.');
        }
        $staff = Staff::with([
            'user',
            'role',
            'events',
            'attachments',
            'thisMonthAttendances'
        ])->findOrFail($decryptedId);

        $minDate = Attendance::min('date');
        if ($minDate) {
            $minDate = Carbon::parse($minDate)->format('Y-m');
        }

        return view('backend.staff.staffs.show', compact('staff', 'minDate'));
    }

    public function update(UpdateStaffRequest $request, $id)
    {
        $staff = Staff::findOrFail($id);
        $user = $staff->user;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->mobile;
        $user->gender = $request->gender;
        $user->date_of_birth = $request->date_of_birth ?: null;
        if (strlen(trim($request->password)) > 0) {
            $user->password = bcrypt(trim($request->password));
        }
        if ($user->save()) {
            if ($request->role_id) {
                $staff->role_id = $request->role_id;
            }
            $staff->personal_email = $request->personal_email ?? $staff->personal_email ?? null;
            // employee_id is immutable — never updated after creation
            $staff->profile_picture = $request->profile_picture ?: null;
            $staff->address = $request->address ?? $staff->address;
            $staff->salary = $request->salary ?? $staff->salary;
            $staff->educational_background = $request->educational_background ?? $staff->educational_background;
            $staff->joining_date = $request->joining_date ?? $staff->joining_date;
            $staff->shift = ShiftEnum::tryFrom($request->shift)?->value ?? $staff->shift ?? ShiftEnum::DAY->value;
            $staff->working_hours = $request->working_hours ?? $staff->working_hours;
            $staff->weekly_offday = $request->weekly_offday ? array_values(array_filter($request->weekly_offday)) : $staff->weekly_offday;
            $staff->emergency_contact = [
                'father_name' => $request->ec_father_name ?? $staff->emergency_contact['father_name'] ?? null,
                'mother_name' => $request->ec_mother_name ?? $staff->emergency_contact['mother_name'] ?? null,
                'spouse_name' => $request->ec_spouse_name ?? $staff->emergency_contact['spouse_name'] ?? null,
                'contact_number' => $request->ec_contact_number ?? $staff->emergency_contact['contact_number'] ?? null,
            ];
            $staff->employment_status = $request->employment_status ?? $staff->employment_status ?? 'active';
            $staff->blood_group = $request->blood_group ?? $staff->blood_group;
            $staff->resign_date = $request->resign_date ?? $staff->resign_date;
            $staff->resignation_letter = $request->resignation_letter ?? $staff->resignation_letter;
            $staff->termination_date = $request->termination_date ?? $staff->termination_date;
            $staff->termination_reason = $request->termination_reason ?? $staff->termination_reason;
            $staff->bank_account = [
                'bank_name' => $request->bank_name ?? $staff->bank_account['bank_name'] ?? null,
                'account_no' => $request->account_no ?? $staff->bank_account['account_no'] ?? null,
                'branch' => $request->bank_branch ?? $staff->bank_account['branch'] ?? null,
            ];
            $staff->note = $request->note ?? $staff->note;

            if ($staff->save()) {
                $this->syncStaffEvents($staff, $request);
                $this->syncStaffAttachments($staff, $request);

                flash(('Staff has been updated successfully'))->success();

                $isAdmin = Auth::check() && Auth::user()->user_type == 'admin';
                $permissions = json_decode(Auth::user()?->staff?->role?->permissions ?? '[]', true) ?? [];
                if ($isAdmin || any_in_array(['20', 'create_staff', 'edit_staff', 'view_staff'], $permissions)) {
                    return redirect()->route('staffs.index');
                } else {
                    return redirect()->back();
                }
            }
        }

        flash(('Something went wrong'))->error();

        return back();
    }

    public function destroy($id)
    {
        User::destroy(Staff::findOrFail($id)->user->id);
        if (Staff::destroy($id)) {
            flash(('Staff has been deleted successfully'))->success();

            return redirect()->route('staffs.index');
        }

        flash(('Something went wrong'))->error();

        return back();
    }

    public function report(Request $request)
    {
        $roles = Role::pluck('name', 'id');
        $filter_date = $request->input('filter_date');
        // $start_date = now()->subDays(7)->format('Y-m-d 00:00:00');
        $start_date = now()->format('Y-m-d 00:00:00');
        $end_date = now()->format('Y-m-d 23:59:59');
        if (filled($filter_date)) {
            $start_date = Carbon::parse(explode(' to ', $filter_date)[0])->startOfDay()->format('Y-m-d H:i:s');
            $end_date = Carbon::parse(explode(' to ', $filter_date)[1])->endOfDay()->format('Y-m-d H:i:s');
        }

        // Search parameter
        $search = $request->input('search');
        $role = $request->input('role');

        // Sort parameters
        $sortColumn = $request->input('sort', 'call_count');
        $sortDirection = $request->input('direction', 'desc');

        // Adjust query cache key based on all parameters
        $cache_key = 'staff_report_'.$start_date.'_'.$end_date;
        if (! empty($search)) {
            $cache_key .= '_search_'.md5($search);
        }
        if (! empty($role)) {
            $cache_key .= '_filter_'.$role;
        }
        $cache_key .= '_sort_'.$sortColumn.'_'.$sortDirection;

        // Get perPage settings
        $perPage = $request->input('per_page', 25);
        $currentPage = $request->input('page', 1);

        // Calculate offset for pagination
        $offset = ($currentPage - 1) * $perPage;
        Cache::forget($cache_key);

        // Get staff reports with pagination directly from cache or generate
        $reports = Cache::remember($cache_key, now()->addHour(), function () use ($start_date, $end_date, $search, $sortColumn, $sortDirection, $perPage, $offset, $role) {
            $staffs = Staff::query()
                ->select('staff.*')
                ->join('users', 'users.id', '=', 'staff.user_id')
                ->with('user', 'role')
                ->where('users.banned', 0)
                ->when(!empty($search), function ($query) use ($search) {
                    $query->where('users.name', 'like', "%{$search}%");
                })
                ->when(!empty($role), function ($query) use ($role) {
                    $query->where('staff.role_id', $role);
                })
                ->orderByDesc('users.recent_login')
                ->get();

            // $staffQuery = Staff::with(['user:id,name', 'role:id,name']);
            // if (! empty($search)) {
            //     $staffQuery->whereHas('user', function ($query) use ($search) {
            //         $query->where('name', 'like', "%{$search}%");
            //     });
            // }
            // if (! empty($role)) {
            //     $staffQuery->whereHas('role', function ($query) use ($role) {
            //         $query->where('id', $role);
            //     });
            // }
            // $staffss = $staffQuery->get();


            $userIds = $staffs->pluck('user.id')->unique();

            $orderCallLogs = CallLog::with('reference')
                ->whereHas('reference')
                ->whereIn('called_by', $userIds)
                ->where('duration', '>', 0)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->where('reference_type', Order::class)->get();

            $feedBackCounts = OrderFeedback::whereIn('created_by', $userIds)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->get();

            $orderLogs = OrderLog::whereIn('managed_by', $userIds)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->get();
            $ticketLogs = TicketLog::whereIn('user_id', $userIds)
                ->where('action', '!=', 'deleted')
                ->whereBetween('created_at', [$start_date, $end_date])
                ->get();

            // Map the data to staff
            $reports = $staffs->map(function ($staff) use ($orderCallLogs, $orderLogs, $ticketLogs, $feedBackCounts) {
                $userId = $staff->user->id;

                return [
                    'id' => $userId,
                    'staff_id' => $staff->id,
                    'role_id' => $staff->role?->id,
                    'role' => $staff->role?->name ?? 'N/A',
                    'name' => $staff->user?->name ?? 'N/A',
                    'order_count' => $this->getOrderLogCounts($orderLogs, null, $userId),
                    'call_count' => $orderCallLogs->where('called_by', $userId)->pluck('reference_id')->unique()->count(),
                    'feedback_count' => $feedBackCounts->where('created_by', $userId)->pluck('order_id')->unique()->count(),
                    'create_count' => $this->getOrderLogCounts($orderLogs, 'created', $userId),
                    'update_count' => $this->getOrderLogCounts($orderLogs, ['updated', 'cancelled', 'delivery_status', 'payment_status'], $userId),
                    'package_count' => $this->getOrderLogCounts($orderLogs, 'packaged', $userId),
                    'ticket_count' => $ticketLogs->where('user_id', $userId)->pluck('ticket_id')->unique()->count(),
                ];
            });

            $callCount = $reports->sum('call_count');
            $feedbackCount = $reports->sum('feedback_count');
            $createCount = $reports->sum('create_count');
            $updateCount = $reports->sum('update_count');
            $packageCount = $reports->sum('package_count');
            $ticketCount = $reports->sum('ticket_count');
            // Sort the collection
            if ($sortColumn == 'name' || $sortColumn == 'role') {
                $reports = $reports->sortBy($sortColumn, SORT_REGULAR, $sortDirection === 'desc');
            } else {
                $reports = $reports->sortBy($sortColumn, SORT_NUMERIC, $sortDirection === 'desc');
            }

            // Get the total count before slicing
            $total = $reports->count();

            // Manual pagination
            $slice = $reports->slice($offset, $perPage)->values();

            return [
                'data' => $slice,
                'total' => $total,
                'counts' => [
                    'call_count' => $callCount,
                    'feedback_count' => $feedbackCount,
                    'create_count' => $createCount,
                    'update_count' => $updateCount,
                    'package_count' => $packageCount,
                    'ticket_count' => $ticketCount,
                ],
            ];
        });

        $counts = $reports['counts'];
        // Create paginator
        $paginatedReports = new \Illuminate\Pagination\LengthAwarePaginator(
            $reports['data'],
            $reports['total'],
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('backend.staff.staffs.report', compact('paginatedReports', 'roles', 'role', 'filter_date', 'search', 'sortColumn', 'sortDirection', 'counts'));
    }

    public function reportById(Request $request, $id)
    {
        $filter_date = $request->filter_date;

        $user = User::find($id);
        if (! $user) {
            flash(('User not found'))->error();

            return redirect()->route('staffs.report');
        }
        $permissions = json_decode($user->staff?->role?->permissions, true) ?? [];
        $filter_url = $request->url();

        if (in_array('packaging_dashboard', $permissions)) {
            $report = StaffReportService::packagingReport($request, $id);

            return view('backend.dashboard.packaging_dashboard', compact('report', 'filter_date', 'filter_url'));
        } elseif (in_array('account_inventory_dashboard', $permissions)) {
            $report = StaffReportService::accountInventoryReport($request, $id);

            return view('backend.dashboard.account_inventory_dashboard', compact('report', 'filter_date', 'filter_url'));
        } else {
            $report = StaffReportService::customerCareReport($request, $id);

            return view('backend.dashboard.customer_care_dashboard', compact('report', 'filter_date', 'filter_url'));
        }
    }

    // Helper method to get counts for different order log actions
    private function getOrderLogCounts($logs, $actions = null, $userId = null)
    {
        if (! $logs) {
            return 0;
        }

        if (! is_null($userId)) {
            $logs = $logs->where('managed_by', $userId);
        }

        if (is_array($actions)) {
            return $logs->whereIn('action', $actions)->pluck('order_id')->unique()->count();
        } elseif (! is_null($actions)) {
            return $logs->where('action', $actions)->pluck('order_id')->unique()->count();
        } else {
            return $logs->pluck('order_id')->unique()->count();
        }
    }

    public function salesContributionReports(Request $request)
    {
        $filter_date = $request->input('filter_date');
        $start_date = now()->subDays(6)->startOfDay();
        $end_date = now()->endOfDay();
        $staff_id = $request->input('staff');

        if (filled($filter_date)) {
            [$start, $end] = explode(' to ', $filter_date);
            $start_date = Carbon::parse($start)->startOfDay();
            $end_date = Carbon::parse($end)->endOfDay();
        }
        $staffs = User::with(['staff.role:id,name'])
            ->where('user_type', 'staff')
            ->when($staff_id, function ($query) use ($staff_id) {
                $query->where('users.id', $staff_id);
            })
            ->select(['users.id', 'users.name'])
            ->selectRaw('(
                SELECT COUNT(*)
                FROM coupon_customer_assignments
                WHERE assigned_by = users.id
                AND created_at BETWEEN ? AND ?
            ) as assigned_coupon_count', [$start_date, $end_date])
            ->selectRaw('(
                SELECT COUNT(*)
                FROM coupon_usages
                JOIN orders ON coupon_usages.order_id = orders.id
                WHERE coupon_usages.ref_id = users.id
                AND orders.delivery_status NOT IN (?, ?)
                AND coupon_usages.order_id IS NOT NULL
                AND coupon_usages.created_at BETWEEN ? AND ?
            ) as used_coupon_count', ['cancelled', 'returned', $start_date, $end_date])
            ->selectRaw('(
                SELECT COALESCE(SUM(o.grand_total), 0)
                FROM orders o
                WHERE o.id IN (
                    SELECT DISTINCT cu.order_id
                    FROM coupon_usages cu
                    WHERE cu.ref_id = users.id
                    AND cu.order_id IS NOT NULL
                    AND cu.created_at BETWEEN ? AND ?
                )
                AND o.delivery_status NOT IN (?, ?)
            ) as order_amount', [$start_date, $end_date, 'cancelled', 'returned'])
            ->selectRaw('(
                SELECT COUNT(*)
                FROM crm_orders_feedbacks
                WHERE created_by = users.id
                AND created_at BETWEEN ? AND ?
            ) as feedback_count', [$start_date, $end_date])
            // ->having('assigned_coupon_count', '>', 0)
            ->orderByDesc('used_coupon_count')
            ->get()
            ->filter(function ($user) {
                return $user->assigned_coupon_count > 0 || $user->used_coupon_count > 0 || $user->feedback_count > 0;
            })
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->staff?->role?->name ?? 'N/A',
                    'assigned_coupon_count' => $user->assigned_coupon_count ?? 0,
                    'used_coupon_count' => $user->used_coupon_count ?? 0,
                    'order_amount' => $user->order_amount ?? 0,
                    'feedback_count' => $user->feedback_count ?? 0,
                ];
            })->values()->toArray();

        $summary = [
            'total_assigned_coupons' => collect($staffs)->sum('assigned_coupon_count'),
            'total_orders' => collect($staffs)->sum('used_coupon_count'),
            'total_order_amount' => collect($staffs)->sum('order_amount'),
            'total_feedbacks' => collect($staffs)->sum('feedback_count'),
        ];

        // dd($staffs);
        return view('backend.staff.staffs.sales-contribution-report', compact('staffs', 'filter_date', 'staff_id', 'summary'));
    }

    public function salesContributionReportDetails(Request $request)
    {
        $staff_id = $request->input('staff');
        $filter_date = $request->input('filter_date');
        $search = $request->input('search');
        $start_date = now()->subDays(7)->startOfDay();
        $end_date = now()->endOfDay();

        if (filled($filter_date)) {
            [$start, $end] = explode(' to ', $filter_date);
            $start_date = Carbon::parse($start)->startOfDay();
            $end_date = Carbon::parse($end)->endOfDay();
        }

        $usageCoupons = \App\Models\CouponUsage::with([
                'order' => function ($query) {
                    $query->withSum([
                        'orderDetails as total_shipping_cost'
                    ], 'shipping_cost')
                    ->latest();
                },
                'coupon:id,code',
            ])
            ->whereHas('order', function ($query) use ($search) {
                $query->whereNotIn('delivery_status', ['cancelled', 'returned'])
                    ->when($search, function ($q) use ($search) {
                        if (str_starts_with($search, 'EMW')) {
                            return $q->where('code', trim($search));
                        }

                        return $q->where(function ($subQuery) use ($search) {
                            $subQuery
                                ->where('shipping_address->name', 'LIKE', "%{$search}%")
                                ->orWhere('shipping_address->email', 'LIKE', "%{$search}%")
                                ->orWhere('shipping_address->phone', 'LIKE', "%{$search}%");
                        });
                    });
            })
            ->latest()
            ->whereNotNull('order_id')
            ->whereNotNull('ref_id')
            ->when($staff_id, function ($query) use ($staff_id) {
                $query->where('ref_id', $staff_id);
            })
            ->whereBetween('created_at', [$start_date, $end_date]);

        if ($request->has('export') && $request->export == 1) {
            $fileName = 'sales_contribution_report_details_'.now()->format('Y_m_d_H_i_s').'.xlsx';

            return Excel::download(new SalesContributionReportExport($usageCoupons->get()), $fileName);
        } else {
            $usageCoupons = $usageCoupons->paginate(20)->appends($request->query());
            return view('backend.staff.staffs.sales-contribution-report-details', compact('usageCoupons', 'filter_date', 'staff_id'));
        }
    }

    public function couponAssignedDetails(Request $request)
    {
        $staff_id = $request->input('staff');
        $filter_date = $request->input('filter_date');
        $start_date = now()->subDays(7)->startOfDay();
        $end_date = now()->endOfDay();

        if (filled($filter_date)) {
            [$start, $end] = explode(' to ', $filter_date);
            $start_date = Carbon::parse($start)->startOfDay();
            $end_date = Carbon::parse($end)->endOfDay();
        }

        $details = CouponCustomerAssignment::with([
            'customer:id,name,email,phone',
            'customer.orders:id,delivery_status,grand_total,user_id',
            'assigner:id,name',
            'coupon',
        ])
            ->when($staff_id, function ($query) use ($staff_id) {
                $query->where('assigned_by', $staff_id);
            })
            ->whereBetween('created_at', [$start_date, $end_date])
            ->latest()
            ->paginate(20);

        return view('backend.staff.staffs.coupon-assigned-details', compact('details', 'filter_date', 'staff_id'));
    }

    /**
     * Sync staff events from the request (replace all for this staff).
     */
    private function syncStaffEvents(Staff $staff, Request $request): void
    {
        $staff->events()->delete();

        $types = $request->input('event_type', []);
        $dates = $request->input('event_date', []);
        $titles = $request->input('event_title', []);
        $attachments = $request->input('event_attachment', []);

        foreach ($dates as $index => $date) {
            if (empty($date) || empty($titles[$index])) {
                continue;
            }

            StaffEvent::create([
                'staff_id' => $staff->id,
                'event_date' => $date,
                'title' => $titles[$index],
                'attachment' => $attachments[$index] ?? null,
                'event_type' => $types[$index] ?? 'any',
            ]);
        }
    }

    /**
     * Sync staff attachments from the request (replace all for this staff).
     */
    private function syncStaffAttachments(Staff $staff, Request $request): void
    {
        $staff->attachments()->delete();

        $types = ['cv', 'nid', 'certificate'];

        foreach ($types as $type) {
            $raw = $request->input("attachment_{$type}", '');
            $uploadIds = array_filter(explode(',', (string) $raw));

            foreach ($uploadIds as $uploadId) {
                if (is_numeric(trim($uploadId))) {
                    StaffAttachment::create([
                        'staff_id' => $staff->id,
                        'type' => $type,
                        'label' => strtoupper($type),
                        'upload_id' => trim($uploadId),
                    ]);
                }
            }
        }
    }

    public function generateDocuments(Request $request, int $id)
    {
        $staff = Staff::findOrFail($id);

        $rules = [
            'type' => ['required', 'string'],
        ];
        if (in_array($request->input('type'), ['joining-letter', 'appointment-letter'])) {
            $rules['date'] = ['required', 'date'];
            $rules['time'] = ['required', 'date_format:H:i'];
        } elseif ($request->input('type') === 'noc') {
            $rules['purpose_short'] = ['required', 'string'];
            $rules['purpose'] = ['required', 'string'];
        } elseif (in_array($request->input('type'), ['promotion-letter', 'increment-letter'])) {
            $rules['effective_date'] = ['required', 'date'];
            $rules['new_role'] = ['nullable', 'string'];
            $rules['new_salary'] = ['required', 'numeric', 'min:'.$staff->salary];
        }
        $validated = $request->validate($rules);

        if(isset($validated['date']) && isset($validated['time'])) {
            $formattedDateTime = Carbon::parse($validated['date'])->setTimeFromTimeString($validated['time']);
        } else if(isset($validated['effective_date'])) {
            $formattedDateTime = Carbon::parse($validated['effective_date']);
        } else {
            $formattedDateTime = null;
        }

        $data = [
            '[[company_name]]' => config('app.name'),
            '[[date]]' => now()->format('d/m/Y'),
            '[[employee_id]]' => $staff->employee_id,
            '[[employee_name]]' => $staff->user->name ?? 'Employee',
            '[[joining_date]]' => $staff->joining_date ? Carbon::parse($staff->joining_date)->format('F j, Y') : ($formattedDateTime ? $formattedDateTime->format('F j, Y') : ''),
            '[[reporting_time]]' => $staff->shift?->reportingTimeFormatted() ?: ($formattedDateTime ? $formattedDateTime->format('g:i A') : ''),
            '[[role]]' => $staff->role?->name ?? 'N/A',
            '[[salary]]' => round($staff->salary ?? 0),
            '[[working_hours]]' => ($staff->working_hours ?? 8) . ' hours/day',
            '[[working_shift]]' => $staff->shift?->label() . ($staff->shift ? ' (' . $staff->shift->schedule() . ')' : ''),
        ];

        if (isset($validated['type'])) {
            if ($validated['type'] === 'noc') {
                $data['[[purpose_short]]'] = $validated['purpose_short'] ?? '';
                $data['[[purpose]]'] = $validated['purpose'] ?? '';
            } else if (in_array($validated['type'], ['promotion-letter', 'increment-letter'])) {
                $data['[[effective_date]]'] = $formattedDateTime ? $formattedDateTime->format('F j, Y') : now()->format('F j, Y');
                $data['[[new_role]]'] = $validated['new_role'] ?? $staff->role?->name ?? 'N/A';
                $data['[[new_salary]]'] = round($validated['new_salary'] ?? $staff->salary);
                $data['[[increment_amount]]'] = round($data['[[new_salary]]'] - round($staff->salary ?? 0));
            }
        }

        if (is_null($staff->joining_date) && !is_null($formattedDateTime) && $staff->joining_date !== $formattedDateTime->toDateString()) {
            $staff->joining_date = $formattedDateTime->toDateString();
            $staff->save();
        }

        $event = $this->generate($staff, $validated['type'], $data, $validated['date'] ?? now()->toDateString());
        if (is_null($event)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate the document. Please try again.',
            ], 500);
        }
        return response()->json([
            'success' => true,
            'message' => ucfirst(str_replace(['_', '-'], ' ', $validated['type'])).' generated successfully.',
            'event_id' => $event->id,
        ]);
    }

    private function generate(Staff $staff, string $type, array $data, string $date)
    {
        DB::beginTransaction();
        try {
            $fileName = $type . '_' . $staff->id . '_' . time() . '.pdf';
            $filePath = 'uploads/staffs/' . $staff->employee_id . '/' . $fileName;

            $disk = config('filesystems.default') === 's3' ? 's3' : 'local';
            if (Storage::disk($disk)->exists($filePath)) {
                Storage::disk($disk)->delete($filePath);
            }

            $template = Template::active()->where('type', $type)->latest('updated_at')->first();
            $content = $template ? $template->content : view('backend.templates.defaults.' . $type)->render();

            if (!$content) {
                throw new \Exception('No template content found for ' . $type . '. Please set up the template and try again.');
            }

            $placeholders = array_keys($data);
            $values = array_values($data);

            $formattedContent = str_replace($placeholders, $values, $content);

            // dd($template, $content, $placeholders, $values, $formattedContent);

            // Generate PDF content
            // $pdf = PDF::loadView($data['view'], $data, [], [
            //     'format' => 'A4',
            // ]);
            $pdf = PDF::loadHtml($formattedContent, [
                'format' => 'A4',
            ]);

            Storage::disk($disk)->put($filePath, $pdf->output());

            $upload = new \App\Models\Upload();
            $upload->file_original_name = $fileName;
            $upload->extension = 'pdf';
            $upload->file_name = $filePath;
            $upload->user_id = Auth::user()->id ?? null;
            $upload->type = 'document';
            $upload->file_size = Storage::disk($disk)->size($filePath);
            $upload->save();
            $event = StaffEvent::updateOrCreate([
                'staff_id' => $staff->id,
                'event_type' => Str::slug($type),
                'event_date' => $date,
            ],[
                'title' => ucfirst(str_replace(['_', '-'], ' ', $type)) . ' Issued',
                'attachment' => $upload->id ?? null,
            ]);
            DB::commit();
            return $event;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating document: '.$e->getTraceAsString(), $e->getTrace());

            return null;
        }
    }

    public function sendDocuments(Request $request, int $id)
    {
        $staff = Staff::findOrFail($id);

        if (is_null($staff->personal_email)) {
            return response()->json([
                'success' => false,
                'message' => 'Staff does not have a personal email address. Please update the staff information and try again.',
            ], 400);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['joining-letter', 'appointment-letter', 'noc', 'promotion-letter', 'increment-letter'])],
        ]);

        $attachment = StaffEvent::where('staff_id', $staff->id)
            ->where('event_type', $validated['type'])
            ->whereNotNull('attachment')
            ->latest('updated_at')
            ->first();

        if (! $attachment) {
            return response()->json([
                'success' => false,
                'message' => 'No document found to send. Please generate the document first.',
            ], 404);
        }

        $formattedType = str_replace(['-', '_'], ' ', $validated['type']);
        try {
            if ($validated['type'] === 'appointment-letter') {
                $emailTemplate = \App\Models\MailTemplate::where('type', 'Appointment Letter')->first();
            } elseif ($validated['type'] === 'joining-letter') {
                $emailTemplate = \App\Models\MailTemplate::where('type', 'Joining Letter')->first();
            } elseif ($validated['type'] === 'noc') {
                $emailTemplate = \App\Models\MailTemplate::where('type', 'NOC')->first();
            } elseif ($validated['type'] === 'promotion-letter') {
                $emailTemplate = \App\Models\MailTemplate::where('type', 'Promotion Letter')->first();
            } elseif ($validated['type'] === 'increment-letter') {
                $emailTemplate = \App\Models\MailTemplate::where('type', 'Increment Letter')->first();
            }
            if (!$emailTemplate || !$emailTemplate->content) {
                $formattedContent = null;
            } else {
                $formattedContent = str_replace(
                    ['{{candidate_name}}', '{{role}}', '{{company_name}}', '{{joining_date}}', '{{reporting_time}}', '{{working_shift}}', '{{working_hours}}', '{{salary}}', '{{deadline}}', '{{employee_id}}', '{{issue_date}}'],
                    [
                        $staff->user?->name ?? 'Candidate',
                        $staff->role?->name ?? 'Role Name',
                        config('app.name'),
                        $staff->joining_date ? Carbon::parse($staff->joining_date)->format('F j, Y') : '',
                        $staff->shift?->reportingTimeFormatted() ?? '',
                        str_replace('()','',($staff->shift?->label() ?? '') . ' (' . ($staff->shift?->schedule() ?? '') . ')'),
                        round($staff->working_hours ?? 8) . ' hours/day',
                        single_price($staff->salary ?? 0),
                        $staff->joining_date ? Carbon::parse($staff->joining_date)->subDay()->format('F j, Y') : '',
                        $staff->employee_id ?? '',
                        $attachment->event_date ? Carbon::parse($attachment->event_date)->format('F j, Y') : Carbon::now()->format('F j, Y'),
                    ],
                    $emailTemplate->content
                );
            }

            $subject = str_replace(
                ['{{role}}'],
                [$staff->role?->name ?? ''],
                $emailTemplate->subject ?? ''
            );
            $data = [
                'candidate_name' => $staff->user?->name ?? 'Candidate',
                'role' => $staff->role?->name ?? '',
                'joining_date' => $staff->joining_date ? Carbon::parse($staff->joining_date)->format('F j, Y') : '',
                'reporting_time' => $staff->shift?->reportingTimeFormatted() ?? '',
                'employee_id' => $staff->employee_id ?? '',
                'content' => $formattedContent,
                'subject' => $subject,
                'attachment' => $attachment->attachment ? uploaded_asset($attachment->attachment) : null,
            ];

            if($validated['type'] === 'appointment-letter') {
                $data = array_merge($data, [
                    'working_shift' => ($staff->shift?->label() ?? '') . ' (' . ($staff->shift?->schedule() ?? '') . ')',
                    'working_hours' => round($staff->working_hours ?? 8) . ' hours/day',
                    'salary' => single_price($staff->salary ?? 0),
                    'deadline' => $staff->joining_date ? Carbon::parse($staff->joining_date)->subDay()->format('F j, Y') : '',
                ]);
            } elseif($validated['type'] === 'noc') {
                $data = array_merge($data, [
                    'issue_date' => $attachment->event_date ? Carbon::parse($attachment->event_date)->format('F j, Y') : Carbon::now()->format('F j, Y'),
                ]);
            }
            Mail::to($staff->personal_email)->queue(new StaffMail($data, $validated['type']));

            return response()->json([
                'success' => true,
                'message' => ucfirst($formattedType) . ' sent successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error sending ' . $formattedType . ': ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send ' . $formattedType . '. Please try again later.',
            ], 500);
        }
    }
}
