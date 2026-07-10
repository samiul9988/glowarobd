<?php

namespace App\Http\Controllers;

use Session;
use Carbon\Carbon;
use App\Models\Area;
use App\Models\Cart;
use App\Models\City;
use App\Models\User;
use App\Models\Order;
use App\Models\State;
use App\Models\Coupon;
use App\Models\CallLog;
use App\Models\Customer;
use App\Models\Wishlist;
use App\Utility\SmsUtility;
use Illuminate\Http\Request;
use App\Models\RewardPointLog;
use App\Models\Customeringroup;
use App\Models\UserCrmMetaData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Jobs\CustomerCouponAssignedJob;
use Illuminate\Support\Facades\Artisan;
use App\Mail\CustomerCouponAssignedMail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $sort_search = null;
        $group_id = !empty($request->group) ? $request->group : null;
        $order_login = null;
        $users = User::with('customeringroup.group')->select('users.*')->where('user_type', 'customer')->orderBy('users.recent_login', 'desc');
        if ($request->has('order_login') && $request->order_login == 'order'){
            $users = User::with('customeringroup.group')->select('users.*')->where('user_type', 'customer')->where('email_verified_at', '!=', null)->orderBy('users.delivered_order', 'desc');
            $order_login = $request->order_login;
        }
        if ($request->has('group') && $request->group!=''){
            $users = $users->whereHas('customeringroup', function (Builder $query) use ($group_id) {
                $query->where('customer_groups_id', $group_id);
            });
        }

        if (filled($request->verified)) {
            if ($request->verified === 'true') {
                $users = $users->whereNotNull('email_verified_at');
            } elseif ($request->verified === 'false') {
                $users = $users->whereNull('email_verified_at');
            }
        }

        if (filled($request->search)) {
            $users->where(function ($q) use ($request) {
                $q->where('users.name', 'like', '%'.$request->search.'%')->orWhere('users.email', 'like', '%'.$request->search.'%')->orWhere('users.phone', 'like', '%'.$request->search.'%');
            });
        }

        $users = $users->paginate(25);

        // dd($users);

        return view('backend.customer.customers.index', compact('users', 'group_id', 'order_login'));
    }

    public function changeVerificationStatus(Request $request)
    {
        $status = $request->status ?? 0;
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No customers selected.',
            ], 400);
        }

        try {
            $updatedCount = User::whereIn('id', $ids)
                ->update(['email_verified_at' => $status ? Carbon::now() : null]);

            return response()->json([
                'success' => true,
                'message' => $updatedCount > 1 ? "Verification status updated for {$updatedCount} customers." : "Verification status updated.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update verification status.',
            ], 500);
        }
    }

    public function createByAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3',
            'email' => 'required_if:phone,null|nullable|email',
            'phone' => 'required_if:email,null|nullable|string|regex:/^(\+88)?01[3-9]\d{8}$/',
            'password' => 'required|string|min:6',
        ], [
            'name.required' => 'Name is required',
            'name.min' => 'Name must be at least 3 characters',
            'email.email' => 'Invalid email address',
            'email.required_if' => 'Email or phone number is required',
            'phone.required_if' => 'Email or phone number is required',
            'phone.min' => 'Invalid phone number',
            'phone.max' => 'Invalid phone number',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
        ]);

        try {
            $phone = str_replace(['+88', '-'], '', $request->input('phone', '')) ?: null;
            $email = $request->input('email', null);
            if ($phone && User::whereIn('phone', [$phone, '+88'.$phone])->exists()) {
                return response()->json(['success' => false, 'message' => 'Phone number already exists'], 400);
            }
            if ($email && User::where('email', $email)->exists()) {
                return response()->json(['success' => false, 'message' => 'Email already exists'], 400);
            }
            $user = new User();
            $user->name = $request->input('name');
            $user->email = $email;
            $user->phone = $phone;
            $user->password = bcrypt($request->input('password'));
            $user->user_type = 'customer';
            $user->email_verified_at = Carbon::now();
            $user->recent_login = null;
            $user->save();

            $customer = new \App\Models\Customer;
            $customer->user_id = $user->id;
            $customer->save();

            $group = \App\Models\Customergroup::orderBy('ordering', 'asc')->first();

            if($group->count() > 0){
                $first_group = new \App\Models\Customeringroup;
                $first_group->user_id = $user->id;
                $first_group->customer_groups_id = $group->id;
                $first_group->status = 1;
                $first_group->save();
            }

            if ($request->input('notify_customer', 0) == 1) {
                if (filled($user->email)) {
                    Mail::to($user->email)->send(new \App\Mail\UserCreatedMail($user, $request->input('password')));
                }
                elseif (filled($user->phone)) {
                    SmsUtility::user_created($user, $request->input('password'));
                }
            }

            return response()->json(['success' => true, 'message' => 'Customer created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create customer'], 500);
        }
    }

    public function fixGroups(Request $request)
    {
        \App\Jobs\FixCustomersGroup::dispatch();

        return response()->json(['success' => true, 'message' => 'Customer group fixing process has started in the background.']);
    }

    public function sendFallbackMessage(Request $request)
    {
        $type = $request->input('type');
        $message = $request->input('message');
        $customerId = $request->input('customer');
        if (!in_array($type, ['sms', 'email'])) {
            return response()->json(['success' => false, 'message' => "Invalid fallback channel: $type"], 400);
        }
        if (empty($message)) {
            return response()->json(['success' => false, 'message' => 'Message content is empty'], 400);
        }
        if (empty($customerId)) {
            return response()->json(['success' => false, 'message' => 'Customer ID is required'], 400);
        }

        try {
            $customer = User::with('addresses')->findOrFail($customerId);

            if ($type === 'sms') {
                $phone = $customer->phone;
                if (empty($phone)) {
                    $address = $customer->addresses->where('set_default', 1)->first() ?? $customer->addresses->first();
                    $phone = $address->phone ?? null;
                }
                if (empty($phone)) {
                    return response()->json(['success' => false, 'message' => 'No phone number available for SMS'], 400);
                }
                $res = SmsUtility::send_custom_sms($phone, $message);
                Log::channel('custom')->info('Fallback SMS sent to ' . $phone . '. Response: ' . json_encode($res));
            } elseif ($type === 'email') {
                Mail::to($customer->email)->queue(new \App\Mail\FallbackMail($message));
            }

            return response()->json(['success' => true, 'message' => 'Fallback message sent successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error sending fallback message: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong!'], 500);
        }
    }

    public function filteredCustomers(Request $request)
    {
        // $response = pathao_success_rate('01301264800');
        // dd($response);
        $group_id = $request->group ?? null;
        $state = $request->division ?? null;
        $stateName = $state ? State::find($state)->name ?? null : null;
        $city = $request->city ?? null;
        $cityName = $city ? City::find($city)->name ?? null : null;
        $area = $request->area ?? null;
        $areaName = $area ? Area::find($area)->name ?? null : null;
        $skin_type = $request->skin_type ?? null;
        $skin_concern = array_filter($request->skin_concern ?? []);
        $product = $request->product ?? null;
        $age = $request->age ?? null;
        $gender = $request->gender ?? null;
        $amount = $request->amount ?? null;
        $purchase_date = $request->purchase_date ?? null;
        $date = $request->date ?? null;
        $search = $request->search ?? null;
        $filter_in = $request->filter ?? 'orders';
        $order_source = $request->order_source ?? '';
        $sort_by = $request->sort_by ?? null;
        $reschedule = $request->reschedule ?? null;
        $reschedule_date = $request->reschedule_date ?? null;
        $call_status = $request->call_status ?? null;
        $submit = filled($request->submit);

        $relations = match ($filter_in) {
            'wishlists' => ['metaData', 'callLogs.caller', 'lastCallLog', 'customeringroup.group:id,group_name', 'addresses', 'wishlists.product'],
            'carts'     => ['metaData', 'callLogs.caller', 'lastCallLog', 'customeringroup.group:id,group_name', 'addresses', 'carts.product'],
            default     => ['metaData', 'callLogs.caller', 'lastCallLog', 'customeringroup.group:id,group_name', 'addresses', 'orders.orderDetails.product'],
        };


        $baseQuery = User::with($relations)
            ->where('user_type', 'customer')
            ->when($date, function ($query) use ($date) {
                return $query->where(function ($query) use ($date) {
                    $dateRange = explode(" to ", $date);
                    if (count($dateRange) === 2) {
                        $query->whereDoesntHave('callLogs')
                            ->orWhereHas('callLogs', function (Builder $query) use ($dateRange) {
                                $query->whereNotBetween('created_at', [
                                    Carbon::parse($dateRange[0])->startOfDay(),
                                    Carbon::parse($dateRange[1])->endOfDay()
                                ]);
                            });
                    }
                });
            })
            ->when($group_id, function ($query) use ($group_id) {
                return $query->whereHas('customeringroup', function (Builder $query) use ($group_id) {
                    $query->where('customer_groups_id', $group_id);
                });
            })
            ->when($age, function ($query) use ($age) {
                if ($age === '<18') {
                    return $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18');
                } elseif ($age === '18-25') {
                    return $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 18 AND 25');
                } elseif ($age === '26-35') {
                    return $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 26 AND 35');
                } elseif ($age === '36-50') {
                    return $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 36 AND 50');
                } elseif ($age === '50+') {
                    return $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) > 50');
                }
            })
            ->when($gender, function ($query) use ($gender) {
                return $query->where('gender', $gender);
            })
            ->when($skin_type, function ($query) use ($skin_type) {
                return $query->whereHas('metaData', function (Builder $query) use ($skin_type) {
                    $query->where('key', 'skin_type')->where('value', $skin_type);
                });
            })
            ->when(!empty($skin_concern), function ($query) use ($skin_concern) {
                return $query->whereHas('metaData', function (Builder $query) use ($skin_concern) {
                    $query->where('key', 'skin_concern')->whereJsonContains('value', $skin_concern);
                });
            })
            ->when($state, function ($query) use ($state) {
                return $query->whereHas('addresses', function (Builder $query) use ($state) {
                    $query->where('state_id', $state);
                });
            })
            ->when($city, function ($query) use ($city) {
                return $query->whereHas('addresses', function (Builder $query) use ($city) {
                    $query->where('city_id', $city);
                });
            })
            ->when($area, function ($query) use ($area) {
                return $query->whereHas('addresses', function (Builder $query) use ($area) {
                    $query->where('area_id', $area);
                });
            })
            ->when($filter_in === 'orders', function ($query) use ($stateName, $cityName, $areaName, $product, $purchase_date, $amount, $order_source) {
                return $query->whereHas('orders', function (Builder $query) use ($stateName, $cityName, $areaName, $product, $purchase_date, $amount, $order_source) {
                    if ($amount === '<1000') {
                        $query->havingRaw('SUM(grand_total) < 1000');
                    } else if ($amount === '1000-5000') {
                        $query->havingRaw('SUM(grand_total) BETWEEN 1000 AND 5000');
                    } else if ($amount === '5000-15000') {
                        $query->havingRaw('SUM(grand_total) BETWEEN 5000 AND 15000');
                    } else if ($amount === '15000+') {
                        $query->havingRaw('SUM(grand_total) > 15000');
                    }
                    if($stateName) {
                        $query->whereJsonContains('shipping_address', ['state' => $stateName]);
                    }
                    if ($cityName) {
                        $query->whereJsonContains('shipping_address', ['city'=> $cityName]);
                    }
                    if ($areaName) {
                        $query->whereJsonContains('shipping_address', ['area'=> $areaName]);
                    }
                    if ($purchase_date) {
                        $dateRange = explode(" to ", $purchase_date);
                        if (count($dateRange) === 2) {
                            $query->whereBetween('created_at', [
                                Carbon::parse($dateRange[0])->startOfDay(),
                                Carbon::parse($dateRange[1])->endOfDay()
                            ]);
                        }
                    }
                    if ($product) {
                        $query->whereHas('orderDetails', function (Builder $query) use ($product) {
                            $query->where('product_id', $product);
                        });
                    }
                    if($order_source) {
                        $query->where('order_source', $order_source);
                    }
                });
            })
            ->when($filter_in === 'carts', function ($query) use ($product, $purchase_date) {
                return $query->whereHas('carts', function (Builder $query) use ($product, $purchase_date) {
                    if ($product) {
                        $query->where('product_id', $product);
                    }
                    if ($purchase_date) {
                        $dateRange = explode(" to ", $purchase_date);
                        if (count($dateRange) === 2) {
                            $query->whereBetween('created_at', [
                                Carbon::parse($dateRange[0])->startOfDay(),
                                Carbon::parse($dateRange[1])->endOfDay()
                            ]);
                        }
                    }
                });
            })
            ->when($filter_in === 'wishlists', function ($query) use ($product, $purchase_date) {
                return $query->whereHas('wishlists', function (Builder $query) use ($product, $purchase_date) {
                    if ($product) {
                        $query->where('product_id', $product);
                    }
                    if ($purchase_date) {
                        $dateRange = explode(" to ", $purchase_date);
                        if (count($dateRange) === 2) {
                            $query->whereBetween('created_at', [
                                Carbon::parse($dateRange[0])->startOfDay(),
                                Carbon::parse($dateRange[1])->endOfDay()
                            ]);
                        }
                    }
                });
            })
            ->when((!$submit && blank($request->reschedule)) || filled($request->birthday), function ($query) {
                return $query->where(function ($q) {
                    $q->whereMonth('date_of_birth', now()->month)
                    ->whereDay('date_of_birth', now()->day);
                });
            })
            ->when(filled($reschedule), function ($query) use ($reschedule_date) {
                return $query->whereHas('lastCallLog', function ($q) use ($reschedule_date) {
                    if ($reschedule_date) {
                        $q->whereNotNull('rescheduled_at')
                            ->whereDate('rescheduled_at', Carbon::parse($reschedule_date));
                    } else {
                        $q->whereNotNull('rescheduled_at')->whereDate('rescheduled_at', '>=', now()); // Show all future reschedules
                    }
                });
            })
            ->when($call_status, function ($query) use ($call_status) {
                $query->whereHas('lastCallLog', function ($q) use ($call_status) {
                    $q->where('status', $call_status);
                });
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
                });
            });

        // Sorting Block
        if ($sort_by === 'purchase_date_asc' || $sort_by === 'purchase_date_desc') {
            $direction = $sort_by === 'purchase_date_asc' ? 'asc' : 'desc';

            if ($filter_in === 'orders') {
                $lastOrder = \DB::table('orders')
                    ->selectRaw('user_id, MAX(created_at) as last_purchase_date')
                    ->groupBy('user_id');

                $baseQuery->leftJoinSub($lastOrder, 'last_orders', function ($join) {
                    $join->on('users.id', '=', 'last_orders.user_id');
                })->orderBy('last_orders.last_purchase_date', $direction);

            } elseif ($filter_in === 'carts') {
                $lastCart = \DB::table('carts')
                    ->where('cart_type', 'regular')
                    ->selectRaw('user_id, MAX(created_at) as last_purchase_date')
                    ->groupBy('user_id');

                $baseQuery->leftJoinSub($lastCart, 'last_carts', function ($join) {
                    $join->on('users.id', '=', 'last_carts.user_id');
                })->orderBy('last_carts.last_purchase_date', $direction);

            } elseif ($filter_in === 'wishlists') {
                $lastWishlist = \DB::table('wishlists')
                    ->selectRaw('user_id, MAX(created_at) as last_purchase_date')
                    ->groupBy('user_id');

                $baseQuery->leftJoinSub($lastWishlist, 'last_wishlists', function ($join) {
                    $join->on('users.id', '=', 'last_wishlists.user_id');
                })->orderBy('last_wishlists.last_purchase_date', $direction);
            }

        } elseif ($sort_by === 'reschedule_date_asc' || $sort_by === 'reschedule_date_desc') {
            $direction = $sort_by === 'reschedule_date_asc' ? 'asc' : 'desc';

            $lastReschedule = \DB::table('call_logs')
                ->selectRaw('reference_id, MAX(rescheduled_at) as last_reschedule_date')
                ->whereNotNull('rescheduled_at')
                ->where('reference_type', User::class)
                ->groupBy('reference_id');

            $baseQuery->leftJoinSub($lastReschedule, 'last_reschedules', function ($join) {
                $join->on('users.id', '=', 'last_reschedules.reference_id');
            })->orderBy('last_reschedules.last_reschedule_date', $direction);

        } else {
            // fallback: birthday ordering
            $baseQuery->selectRaw('users.*, DATE_FORMAT(date_of_birth, "%d %b") as dob_formatted')
                ->orderByRaw("
                    (MONTH(date_of_birth) * 100 + DAY(date_of_birth) >= ?) DESC,
                    MONTH(date_of_birth),
                    DAY(date_of_birth)
                ", [now()->format('md')]);
        }
        // End Sorting Block

        $baseQuery->withCount([
                'orders as delivered_count' => fn($q) => $q->where('delivery_status', 'delivered'),
                'orders as cancelled_count' => fn($q) => $q->where('delivery_status', 'cancelled'),
                'orders as returned_count'  => fn($q) => $q->where('delivery_status', 'returned'),
                'orders as others_count'    => fn($q) => $q->whereNotIn('delivery_status', ['delivered', 'cancelled', 'returned']),
            ])
            ->withSum([
                'orders as grand_total_sum' => fn($q) => $q->whereNotIn('delivery_status', ['cancelled','returned']),
            ], 'grand_total');

        // dd($baseQuery->get()->toArray());
        // if(filled($request->submit)){
        //     dd($request->all(), $baseQuery->toSql(), $baseQuery->getBindings(), $baseQuery->get()->toArray());
        // }

        $customers = $baseQuery->paginate(15);

        $customers->getCollection()->transform(function ($customer) use ($filter_in) {
            return [
                'id'        => $customer->id,
                'name'      => $customer->name,
                'email'     => $customer->email,
                'phone'     => $customer->phone,
                'labels'    => $customer->meta('customer_label'),
                'group'     => $customer->customeringroup?->group?->group_name ?? '',
                'order_summary' => [
                    'delivered'   => $customer->delivered_count,
                    'cancelled'   => $customer->cancelled_count,
                    'returned'    => $customer->returned_count,
                    'others'      => $customer->others_count,
                    'grand_total' => $customer->grand_total_sum,
                ],
                'satisfaction' => $customer->meta('satisfaction') ?? null,
                'banned'  => $customer->banned,
                'verified' => !is_null($customer->email_verified_at),
                'dob' => is_null($customer->date_of_birth) ? null : Carbon::parse($customer->date_of_birth)->format('d M'),
                'latest_call' => $customer->callLogs->first() ?? null
            ];
        });

        $birthDayCount = Cache::remember('birthDayCount_'.now()->format('d M'), now()->addHours(24), function () {
            return User::whereNotNull('date_of_birth')
                ->whereMonth('date_of_birth', now()->month)
                ->whereDay('date_of_birth', now()->day)
                ->count();
        });

        Cache::forget('rescheduledCount_'.now()->format('d M'));
        $rescheduledCount = Cache::remember('rescheduledCount_'.now()->format('d M'), now()->addHours(6), function () {
            return User::whereHas('lastCallLog', function ($q) {
                $q->whereNotNull('rescheduled_at')->whereDate('rescheduled_at', '>=', now()); // Show all future reschedules
            })->count();
        });

        return view('backend.customer.customers.filtered_customers', compact('customers', 'group_id', 'state', 'city', 'area', 'skin_type', 'skin_concern', 'product', 'age', 'gender', 'amount', 'purchase_date', 'date', 'search', 'filter_in', 'order_source', 'sort_by', 'reschedule', 'call_status', 'birthDayCount', 'rescheduledCount'));
    }

    public function filteredCustomersDetails(Request $request, $id)
    {
        $customer = User::with('metaData', 'usableCoupons.coupon')
            ->withCount('carts')
            ->withCount('wishlists')
            ->withCount([
                'orders as delivered_orders_count' => fn($q) => $q->where('delivery_status', 'delivered'),
                'orders as cancelled_orders_count' => fn($q) => $q->where('delivery_status', 'cancelled'),
                'orders as returned_orders_count'  => fn($q) => $q->where('delivery_status', 'returned'),
                'orders as others_orders_count'    => fn($q) => $q->whereNotIn('delivery_status', ['delivered', 'cancelled', 'returned']),
            ])
            ->findOrFail($id);

        return view('backend.customer.customers.filtered_customers_details', compact('customer'));
    }

    public function assignCoupon(Request $request, $id)
    {
        $customer = User::with('latestOrder:id,user_id,shipping_address')->select('id', 'name', 'email')->find($id);
        $coupon = Coupon::where('status', 1)->find($request->input('coupon_id'));

        if (!$customer || !$coupon) {
            return response()->json([
                'success' => false,
                'message' => ('Invalid customer or coupon'),
            ], 404);
        }

        try {
            $existingAssignment = \App\Models\CouponCustomerAssignment::where('customer_id', $customer->id)
                ->where('coupon_id', $coupon->id)
                ->first();

            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => ('Coupon already assigned to this customer'),
                ]);
            }

            $assignedCoupon = \App\Models\CouponCustomerAssignment::create([
                'customer_id' => $customer->id,
                'coupon_id' => $coupon->id,
                'assigned_by' => Auth::id(),
                'expire_date' => Carbon::parse($request->input('expiry_date')),
            ]);

            $info['customer'] = $customer->name;
            $info['coupon'] = $coupon->code;
            $info['validity'] = Carbon::parse($request->input('expiry_date'))->format('d F Y');
            $info['email'] = null;
            $info['phone'] = null;

            if(filled($customer->email)) {
                $info['email'] = $customer->email;
            } else {
                $phone = $customer->phone ?? optional($customer->latestOrder)->shipping_address
                        ? json_decode($customer->latestOrder->shipping_address, true)['phone'] ?? null
                        : null;

                if ($phone) {
                    $info['phone'] = $phone;
                }
            }

            CustomerCouponAssignedJob::dispatch($info);

            return response()->json([
                'success' => true,
                'message' => ('Coupon assigned successfully'),
                'data' => [
                    'id' => $assignedCoupon->id,
                    'coupon' => $coupon->code,
                    'discount' => $coupon->discount . ($coupon->discount_type === 'percent' ? '%' : '৳'),
                    'expire_date' => Carbon::parse($assignedCoupon->expire_date)->format('d F Y'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => ('Failed to assign coupon'),
                // 'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function removeCoupon($id)
    {
        try {
            \App\Models\CouponCustomerAssignment::where('id', $id)->delete();
            return response()->json([
                'success' => true,
                'message' => ('Coupon removed successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => ('Failed to remove coupon'),
                // 'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getOrdersByCustomer(Request $request, $id)
    {
        $customer = User::find($id);
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => ('Customer not found'),
            ], 404);
        }
        $orders = Order::latest()->with('feedback', 'orderDetails.product')
            ->where('user_id', $customer->id)
            ->where('delivery_status', 'delivered')
            ->paginate(5);
        $view = view('backend.components.accordian-orders-list', [
            'orders' => $orders->items(),
            'nextPageUrl' => $orders->nextPageUrl(),
        ])->render();
        $products = [];
        foreach ($customer->orders as $order) {
            foreach ($order->orderDetails as $orderDetail) {
                $products[] = [
                    'name' => $orderDetail->product->name ?? '',
                    'quantity' => $orderDetail->quantity,
                    'order_date' => date('d-m-Y', $order->date),
                    'order_code' => $order->code,
                ];
            }
        }
        return response()->json([
            'success' => true,
            'view' => $view,
            'products' => $products,
        ]);
    }

    public function getCartsAndWishlistsByCustomer(Request $request, $id)
    {
        $customer = User::with('carts.product', 'wishlists.product')->find($id);
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => ('Customer not found'),
            ], 404);
        }
        $cart_products = [];
        $cart_total_amount = 0;
        foreach ($customer->carts as $cart) {
            if(!$cart->product){
                $cart->delete();
                continue;
            }
            $cart_products[] = [
                'name' => $cart->product->name ?? 'Product Not Found',
                'url' => to_frontend(route('product', $cart->product?->slug)),
                'image' => uploaded_asset($cart->product->thumbnail_img),
                'price' => single_price($cart->price),
                'tax' => single_price($cart->tax),
                'quantity' => $cart->quantity,
                'total' => single_price($cart->price * $cart->quantity),
            ];
            $cart_total_amount += $cart->price * $cart->quantity;
        }
        $wishlist_products = [];
        foreach ($customer->wishlists as $wishlist) {
            if(!$wishlist->product){
                $wishlist->delete();
                continue;
            }
            $wishlist_products[] = [
                'id' => $wishlist->id,
                'name' => $wishlist->product->name ?? 'Product Not Found',
                'url' => to_frontend(route('product', $wishlist->product?->slug)),
                'image' => uploaded_asset($wishlist->product?->thumbnail_img),
                'rating' => renderStarRating($wishlist->product?->rating),
                'base_price' => home_base_price($wishlist->product),
                'discount_price' => home_discounted_base_price($wishlist->product)
            ];
        }
        return response()->json([
            'success' => true,
            'cart_products' => $cart_products,
            'cart_total_amount' => single_price($cart_total_amount),
            'wishlist_products' => $wishlist_products,
        ]);
    }

    public function getCallLogs($id)
    {
        $user = User::with('callLogs.user')->findOrFail($id);

        $callLogs = $user->callLogs->map(function ($log) {
            return [
                'id' => $log->id,
                'hasCreator' => $log->user ? true : false,
                'creator' => $log->user?->name ?? 'N/A',
                'created_at' => $log->created_at->format('d-m-Y h:i A'),
                'duration' => $log->duration,
                'note' => ucfirst($log->note ?? 'N/A'),
                'status' => $log->status ?? 'N/A',
                'rescheduled_at' => $log->rescheduled_at ? $log->rescheduled_at->format('d-m-Y h:i A') : null,
                'deleteable' => auth()->user()->id === $log->created_by || auth()->user()->user_type == 'admin',
            ];
        });

        return response()->json([
            'success' => true,
            'view' => view('backend.components.orders-call-logs', compact('callLogs'))->render(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required',
            'email'         => 'required|unique:users|email',
            'phone'         => 'required|unique:users',
        ]);

        $response['status'] = 'Error';

        $user = User::create($request->all());

        $customer = new Customer;

        $customer->user_id = $user->id;
        $customer->save();

        if (isset($user->id)) {
            $html = '';
            $html .= '<option value="">
                        '. ("Walk In Customer") .'
                    </option>';
            foreach(Customer::all() as $key => $customer){
                if ($customer->user) {
                    $html .= '<option value="'.$customer->user->id.'" data-contact="'.$customer->user->email.'">
                                '.$customer->user->name.'
                            </option>';
                }
            }

            $response['status'] = 'Success';
            $response['html'] = $html;
        }

        echo json_encode($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        try{
            $request->validate([
                'name'=> 'required',
                'email'=> 'nullable|email|unique:users,email,'.$id,
                'phone'=> 'nullable|unique:users,phone,'.$id,
            ]);
            $user = User::findOrFail($id);

            $user->name = $request->name;
            if(blank($user->email)){
                $user->email = $request->email ?? null;
            }
            // $user->address = $request->address;
            // $user->country = $request->country;
            // $user->city = $request->city;
            // $user->postal_code = $request->postal_code;
            if(blank($user->phone)){
                $user->phone = $request->phone ?? null;
            }
            $user->gender = $request->gender ?? null;
            $user->date_of_birth = $request->date_of_birth ?? null;

            $user->save();

            $user->createMeta([
                'skin_type'      => $request->skin_type,
                'skin_concern'   => $request->skin_concern, // array auto-encoded
                'customer_label' => $request->customer_label,
            ]);

            Cache::forget('birthDayCount_'.now()->format('d M'));

            return response()->json([
                'success' => true,
                'message' => ('Customer updated successfully'),
                'data' => [
                    'name' => $user->name,
                    'email'=> $user->email ?? 'N/A',
                    'phone'=> $user->phone ?? 'N/A',
                    'gender' => ucfirst($user->gender ?? 'N/A'),
                    'date_of_birth' => $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('j M, Y') : 'N/A',
                ]
            ]);
        } catch(ModelNotFoundException $e){
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message'=> ('User not found'),
                    ],404);
            }
            flash(('User not found'))->error();
            return redirect()->back();
        } catch(ValidationException $e){
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message'=> ('Invalid data provided'),
                    'errors' => $e->validator->errors()->all(),
                ],422);
            }
            flash(('Invalid data provided'))->error();
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch(\Exception $e){
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'=> false,
                    'message'=> ('Something went wrong while updating the user'),
                ],500);
            }
            flash(('Something went wrong while updating the user'))->error();
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request, $id)
    {
        User::destroy($id);
        flash(('Customer has been deleted successfully'))->success();
        return redirect()->route('customers.index', ['page' => $request->input('page')]);
    }

    public function bulk_customer_delete(Request $request) {
        if($request->id) {
            foreach ($request->id as $customer_id) {
                $this->destroy($request, $customer_id);
            }
        }

        return 1;
    }

    public function login($id)
    {
        $user = User::findOrFail(decrypt($id));

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(100);
        $token->save();

        return redirect()->away(to_frontend('/login').'?token='.$tokenResult->accessToken);

        // auth()->login($user, true);
        // return redirect()->route('dashboard');
    }

    public function ban($id) {
        $user = User::findOrFail($id);

        if($user->banned == 1) {
            $user->banned = 0;
            flash(('Customer UnBanned Successfully'))->success();
        } else {
            $user->banned = 1;
            flash(('Customer Banned Successfully'))->success();
        }

        $user->save();

        return back();
    }

    public function details(Request $request, $id)
    {
        $customer = User::findOrFail($id);
        $wishlists = Wishlist::where('user_id', $customer->id)->paginate(9);
        if($customer) {
            $user_id = $customer->id;
            if($request->session()->get('temp_user_id')) {
                Cart::where('user_id', $user_id)->delete();
                Cart::where('temp_user_id', $request->session()->get('temp_user_id'))->update(['user_id' => $user_id,'temp_user_id' => null]);
                Session::forget('temp_user_id');
            }
            $carts = Cart::where('user_id', $user_id)->get();
            $cartsgroup = Cart::where('user_id', $user_id)->groupBy('product_id')->sum('quantity');
            $totalcartamount = get_total_cart_amount_check($user_id);
        } else {
            $totalcartamount = 0;
            $temp_user_id = $request->session()->get('temp_user_id');
            // $carts = Cart::where('temp_user_id', $temp_user_id)->get();
            $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->get() : [] ;
        }

        $date = $request->date;
        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $orders = Order::where('user_id', $customer->id)->orderBy('id', 'desc');

        if ($request->payment_type != null) {
            $orders = $orders->where('payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }
        if ($date != null) {
            $orders = $orders->whereDate('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->whereDate('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }

        $orders = $orders->paginate(15);

        $rewardPointLogs = RewardPointLog::where('user_id', $id)->select(['activity_type', 'earned','spent','activity_str', 'created_at'])->latest()->get();

        return view('backend.customer.customers.details', compact('customer', 'orders', 'payment_status', 'delivery_status', 'sort_search', 'date', 'wishlists', 'carts', 'rewardPointLogs'));
    }

    public function getUserInfoById(Request $request)
    {
        return cache()->remember('getUserInfoById_'.$request->id, 86400, function () use ($request) {
            if($request->id == "" || $request->id == null){
                $data = [];
                return array(
                    'result' => false,
                    'message' => 'Customer not found',
                    'view' => view('firebase-message.customer_info', compact('data'))->render()
                );
            }
            $user = User::where('id', $request->id)->first();
            if (!$user) {
                $data = [];
                return array(
                    'result' => false,
                    'message' => 'Customer not found',
                    'view' => view('firebase-message.customer_info', compact('data'))->render()
                );
            }
            $user_group =  Customeringroup::where('user_id','=',$user->id)->where('status', '=', 1)->first();
            $order = Order::with('orderDetails')->where('user_id', $user->id)->orderBy('id', 'desc')->first();
            $order_products = isset($order) ? $order->orderDetails->toArray() : [];
            $products = Http::get(url('/').'/api/v3/products?page=1&orderby=rand&limit=18')->collect();

            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'date_of_birth' => $user->date_of_birth,
                'gender'=>$user->gender,
                'group'=>[
                    'id'=>@$user_group->group->id,
                    'name'=>@$user_group->group->group_name,
                    'icon'=>@$user_group->group->group_icon,
                    'image'=>@$user_group->group->group_image
                ],
                'avatar_original' => api_asset($user->avatar_original),
                'phone' => $user->phone,
                'orders' => $user->orders,
                'order_products' => $order_products,
                'products' => $products ?? []
            ];

            return array(
                'result' => true,
                'view' => view('firebase-message.customer_info', compact('data'))->render()
            );
        });
    }
}
