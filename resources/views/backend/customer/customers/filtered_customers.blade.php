@extends('backend.layouts.app')
@php
    $groups = Cache::remember('crm_customer_groups', now()->addMinutes(60), function () {
        return App\Models\Customergroup::all();
    });
    $states = Cache::remember('crm_states', now()->addMinutes(60), function () {
        return App\Models\State::active()->orderBy('name', 'asc')->pluck('name', 'id');
    });
    $cities = Cache::remember('crm_cities', now()->addMinutes(60), function () {
        return App\Models\City::active()->orderBy('name', 'asc')->select('id', 'name', 'state_id')->get();
    });
    $areas = Cache::remember('crm_areas', now()->addMinutes(60), function () {
        return App\Models\Area::active()->orderBy('name', 'asc')->select('id', 'name', 'city_id')->get();
    });
    $products = Cache::remember('crm_products', now()->addHours(3), function () {
        return App\Models\Product::published()->pluck('name', 'id');
    });
    $skinConcerns = Cache::remember('crm_skin_concern', now()->addMinutes(60), function () {
        $metaObject = \App\Models\MetaObject::with('items')->where('name', 'Skin Concern')->first();
        $skin_concerns = $metaObject ? $metaObject->items->pluck('title')->toArray() : [];
        if (count($skin_concerns) == 0) {
            $skin_concerns = ['Acne', 'Dark Spots', 'Black Heads', 'Wrinkles', 'Dullness', 'Sensitivity'];
        }
        return $skin_concerns;
    });
    $skinTypes = ['Oily', 'Dry', 'Combination', 'Sensitive', 'Normal'];
@endphp
@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="align-items-center">
            <h1 class="h3">{{ 'All Customers' }}</h1>
        </div>
    </div>


    <div class="card">
        <form id="filter_customers">
            <div class="card-header row gutters-5 justify-content-start">
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="group" name="group">
                        <option value="">{{ 'Select Group' }}</option>
                        @foreach ($groups as $key => $group)
                            <option value="{{ $group->id }}" @if ($group->id == $group_id) selected @endif>
                                {{ $group->group_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" data-live-search="true"
                        id="division" name="division">
                        <option value="">{{ 'Select Division' }}</option>
                        @foreach ($states as $state_id => $state_name)
                            <option value="{{ $state_id }}" {{ $state == $state_id ? 'selected' : '' }}>
                                {{ $state_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" data-live-search="true"
                        id="city" name="city">
                        <option value="">{{ 'Select City' }}</option>
                        @foreach ($cities as $item)
                            <option value="{{ $item->id }}" data-division="{{ $item->state_id }}"
                                {{ $city == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" data-live-search="true"
                        id="area" name="area">
                        <option value="">{{ 'Select Area' }}</option>
                        @foreach ($areas as $item)
                            <option value="{{ $item->id }}" data-city="{{ $item->city_id }}"
                                {{ $area == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" data-live-search="true"
                        id="skin_type" name="skin_type">
                        <option value="">{{ 'Select Skin Type' }}</option>
                        @foreach ($skinTypes as $type)
                            <option value="{{ $type }}" {{ $skin_type == $type ? 'selected' : '' }}>
                                {{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" data-live-search="true"
                        id="skin_concern" name="skin_concern[]" multiple title="{{ 'Select Skin Concern' }}">
                        <option value="" disabled>{{ 'Select Skin Concern' }}</option>
                        @foreach ($skinConcerns as $concern)
                            <option value="{{ $concern }}" {{ in_array($concern, $skin_concern) ? 'selected' : '' }}>
                                {{ $concern }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="product" name="product"
                        data-live-search="true">
                        <option value="">{{ 'Select Product' }}</option>
                        @foreach ($products as $pid => $pname)
                            <option value="{{ $pid }}" {{ $product == $pid ? 'selected' : '' }}>
                                {{ $pname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="age"
                        name="age">
                        <option value="">{{ 'Select Age Group' }}</option>
                        <option value="<18" {{ $age === '<18' ? 'selected' : '' }}>{{ '<18' }}</option>
                        <option value="18-25" {{ $age === '18-25' ? 'selected' : '' }}>{{ '18-25' }}</option>
                        <option value="26-35" {{ $age === '26-35' ? 'selected' : '' }}>{{ '26-35' }}</option>
                        <option value="36-50" {{ $age === '36-50' ? 'selected' : '' }}>{{ '36-50' }}</option>
                        <option value="50+" {{ $age === '50+' ? 'selected' : '' }}>{{ '50+' }}</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="gender"
                        name="gender">
                        <option value="">{{ 'Select Gender' }}</option>
                        <option value="male" {{ $gender === 'male' ? 'selected' : '' }}>{{ 'Male' }}</option>
                        <option value="female" {{ $gender === 'female' ? 'selected' : '' }}>{{ 'Female' }}</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="amount"
                        name="amount">
                        <option value="">{{ 'Select Amount' }}</option>
                        <option value="<1000" {{ $amount === '<1000' ? 'selected' : '' }}>
                            <1000< /option>
                        <option value="1000-5000" {{ $amount === '1000-5000' ? 'selected' : '' }}>1000-5000</option>
                        <option value="5000-15000" {{ $amount === '5000-15000' ? 'selected' : '' }}>5000-15000</option>
                        <option value="15000+" {{ $amount === '15000+' ? 'selected' : '' }}>15000+</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <div class="form-group mb-2 mb-md-0">
                        <input type="text" class="aiz-date-range form-control-sm form-control"
                            value="{{ @$purchase_date }}" id="purchase_date" name="purchase_date"
                            placeholder="{{ 'Filter by purchase/added date' }}" data-format="DD-MM-Y"
                            data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <div class="form-group mb-2 mb-md-0">
                        <input type="text" class="aiz-date-range form-control-sm form-control"
                            value="{{ @$date }}" id="date" name="date"
                            placeholder="{{ 'Exclude Last Call Between' }}" data-format="DD-MM-Y"
                            data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="filter"
                        name="filter">
                        <option value="">{{ 'Filter In' }}</option>
                        <option value="orders" selected>{{ 'Orders' }}</option>
                        <option value="wishlists" {{ $filter_in === 'wishlists' ? 'selected' : '' }}>{{ 'Wishlists' }}
                        </option>
                        <option value="carts" {{ $filter_in === 'carts' ? 'selected' : '' }}>{{ 'Carts' }}
                        </option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="order_source"
                        name="order_source">
                        <option value="" selected>{{ 'Select Order Source' }}</option>
                        <option value="pos" @if ($order_source === 'pos') selected @endif>{{ 'POS' }}
                        </option>
                        <option value="website" @if ($order_source === 'website') selected @endif>{{ 'Website' }}
                        </option>
                        <option value="android" @if ($order_source === 'android') selected @endif>{{ 'Android' }}
                        </option>
                        <option value="ios" @if ($order_source === 'ios') selected @endif>{{ 'IOS' }}
                        </option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="sort_by"
                        name="sort_by">
                        <option value="" selected>{{ 'Sort By' }}</option>
                        <option value="purchase_date_asc" @if ($sort_by === 'purchase_date_asc') selected @endif>
                            {{ 'Purchase/Added Date (Asc)' }}</option>
                        <option value="purchase_date_desc" @if ($sort_by === 'purchase_date_desc') selected @endif>
                            {{ 'Purchase/Added Date (Desc)' }}</option>
                        <option value="reschedule_date_asc" @if ($sort_by === 'reschedule_date_asc') selected @endif>
                            {{ 'Reschedule Date (Asc)' }}</option>
                        <option value="reschedule_date_desc" @if ($sort_by === 'reschedule_date_desc') selected @endif>
                            {{ 'Reschedule Date (Desc)' }}</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="call_status"
                        name="call_status">
                        <option value="" selected>Filter By Call Status</option>
                        <option value="call_received" @if (@$call_status === 'call_received') selected @endif>Call Received
                        </option>
                        <option value="no_response" @if (@$call_status === 'no_response') selected @endif>No Response
                        </option>
                        <option value="re_schedule" @if (@$call_status === 're_schedule') selected @endif>Re-Schedule
                        </option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control form-control-sm" id="search" name="search"
                            value="{{ @$search }}" placeholder="{{ 'Search Customer...' }}">
                    </div>
                </div>

                <input type="hidden" name="reschedule" value="{{ $reschedule }}" id="reschedule">
                <input type="hidden" name="reschedule_date" value="{{ request('reschedule_date') }}" id="reschedule_date">

                <div class="col-auto mb-2">
                    <div class="form-group mb-0 mt-0">
                        <input type="hidden" name="submit" value="yes" />
                        <button type="submit" id="formSubmitButton" class="btn btn-sm btn-primary">{{ 'Filter' }}</button>
                        <button type="button" class="btn btn-sm btn-secondary reset-btn"
                            onclick="resetForm()">{{ 'Reset' }}</button>
                    </div>
                </div>
            </div>
        </form>
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div class="alert alert-info py-2 font-weight-bold">
                    {{ 'Filtered Customers: ' . $customers->total() }}
                </div>
                <div>
                    @if(filled($reschedule))
                        <input type="date" data-toggle="tooltip" data-position="top" title="Filter By Reschedule Date" id="filterRescheduleDate" class="form-control form-control-sm d-inline-block" min="{{ now()->format('Y-m-d') }}" style="width: 200px;" value="{{ request('reschedule_date') }}" placeholder="Filter by Reschedule Date">
                    @endif
                    <a href="{{ route('customers.filtered', ['birthday' => 'today']) }}" type="button"
                        class="btn btn-secondary btn-sm">
                        <i class="las la-birthday-cake"></i> {{ 'Birthday' . ' (' . $birthDayCount . ')' }}
                    </a>
                    <a href="{{ route('customers.filtered', ['reschedule' => 'all']) }}" type="button"
                        class="btn btn-secondary btn-sm">
                        <i class="las la-calendar-check"></i> {{ 'Reschedule' . ' (' . $rescheduledCount . ')' }}
                    </a>
                </div>
            </div>
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>{{ 'Customer' }}</th>
                        <th class="text-center">{{ 'Birthday' }}</th>
                        <th class="text-center">{{ 'Satisfaction' }}</th>
                        <th class="text-center">{{ 'Order Summary' }}</th>
                        <th class="text-center">{{ 'Total Purchase Amount' }}</th>
                        <th class="text-center">{{ 'Last Call' }}</th>
                        <th class="text-center">{{ 'Last Call Note' }}</th>
                        <th class="text-center">{{ 'Options' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $key => $user)
                        @php
                            if (!is_null($user['dob']) && Carbon\Carbon::parse($user['dob'])->isToday()) {
                                $bgClass = 'bg-soft-success';
                            } elseif (
                                !is_null(data_get($user, 'latest_call.rescheduled_at', null)) &&
                                Carbon\Carbon::parse(data_get($user, 'latest_call.rescheduled_at'))->isToday()
                            ) {
                                $bgClass = 'bg-soft-warning';
                            } else {
                                $bgClass = '';
                            }
                        @endphp
                        <tr class="{{ $bgClass }}">
                            <td>
                                @if ($user['banned'] == 1)
                                    <i class="fa fa-ban text-danger" aria-hidden="true"></i>
                                @endif
                                <span class="d-block font-weight-bold"
                                    title="{{ $user['verified'] ? 'Verified' : 'Not Verified' }}">
                                    {{ ucwords($user['name']) }}
                                    @if ($user['verified'])
                                        <i class="las la-check-circle text-success font-weight-bold"></i>
                                    @else
                                        <i class="las la-times-circle text-danger font-weight-bold"></i>
                                    @endif
                                    @if ($user['group'])
                                        @php
                                            $class = match ($user['group']) {
                                                'New User' => 'text-secondary',
                                                'Regular User' => 'text-info',
                                                'Premium User' => 'text-success',
                                                'Platinam User' => 'text-primary',
                                                default => 'text-muted',
                                            };
                                        @endphp
                                        <span class="{{ $class }} fs-10"> ({{ $user['group'] }})</span>
                                    @endif
                                </span>
                                @if (filled($user['email']))
                                    <span class="d-block">
                                        {{ $user['email'] }}
                                    </span>
                                @endif
                                @if (filled($user['phone']))
                                    <span class="d-block">{{ $user['phone'] }}</span>
                                @endif
                                @if (filled($user['labels']) && is_array($user['labels']))
                                    <span class="d-block mt-1">
                                        @foreach ($user['labels'] as $label)
                                            <span
                                                class="badge badge-{{ \App\Enums\CustomerLabels::getLabelGroup($label) }} badge-inline">{{ \App\Enums\CustomerLabels::getLabel($label) }}</span>
                                        @endforeach
                                    </span>
                                @endif
                            </td>
                            <td
                                class="text-center {{ !is_null($user['dob']) && Carbon\Carbon::parse($user['dob'])->isToday() ? 'd-flex align-items-end justify-content-center' : '' }}">
                                @if (!is_null($user['dob']) && Carbon\Carbon::parse($user['dob'])->isToday())
                                    <img height="40" width="40"
                                        src="{{ static_asset('assets/img/birthday.gif') }}" alt="">
                                @endif
                                {{ is_null($user['dob']) ? 'N/A' : $user['dob'] }}
                            </td>
                            <td class="text-center">
                                @if ($user['satisfaction'] != null)
                                    <span class="badge badge-success p-2 w-auto font-weight-bold">
                                        {{ $user['satisfaction'] }}%
                                    </span>
                                @else
                                    <span class="badge badge-secondary p-2 w-auto font-weight-bold">
                                        {{ 'Not Rated' }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="d-block">
                                    <span class="badge badge-info p-2 w-auto font-weight-bold mb-1">
                                        In Queue: {{ data_get($user, 'order_summary.others', 0) }}
                                    </span>
                                    <span class="badge badge-success p-2 w-auto font-weight-bold mb-1">
                                        Delivered: {{ data_get($user, 'order_summary.delivered', 0) }}
                                    </span>
                                </span>
                                <span class="d-block">
                                    <span class="badge badge-danger p-2 w-auto font-weight-bold">
                                        Cancelled: {{ data_get($user, 'order_summary.cancelled', 0) }}
                                    </span>
                                    <span class="badge badge-secondary p-2 w-auto font-weight-bold">
                                        Returned: {{ data_get($user, 'order_summary.returned', 0) }}
                                    </span>
                                </span>
                            </td>
                            <td class="text-center">
                                {{ single_price(data_get($user, 'order_summary.grand_total', 0)) }}
                            </td>
                            <td class="text-center">
                                @if (filled($user['latest_call'] ?? null))
                                    <span class="d-block">By
                                        <strong>{{ data_get($user, 'latest_call.caller.name', 'N/A') }}</strong>
                                    </span>
                                    <span class="d-block">At <strong
                                            class="text-success">{{ data_get($user, 'latest_call.created_at')->diffForHumans() }}</strong>
                                    </span>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="text-center">
                                @if (filled(data_get($user, 'latest_call.note', '')) || filled(data_get($user, 'latest_call.status', '')))
                                    <span class="d-block">
                                        {{ ucwords(str_replace('_', ' ', data_get($user, 'latest_call.status'))) }}
                                    </span>
                                    @if (filled(data_get($user, 'latest_call.note', '')))
                                        <span class="d-block">
                                            <strong>Note: </strong>{{ ucfirst(Str::limit(data_get($user, 'latest_call.note'), 30)) }}
                                            @if (strlen(data_get($user, 'latest_call.note')) > 30)
                                                @include('components.tooltip', [
                                                    'title' => ucfirst(data_get($user, 'latest_call.note')),
                                                ])
                                            @endif
                                        </span>
                                    @endif
                                @else
                                    N/A
                                @endif
                                @if (data_get($user, 'latest_call.rescheduled_at'))
                                    <span class="d-block">At <strong
                                            class="text-danger">{{ data_get($user, 'latest_call.rescheduled_at')->format('d M h:i A') }}</strong></span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('customers.filtered.details', $user['id']) }}" target="_blank"
                                    class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                    title="{{ 'See Customer Details' }}">
                                    <i class="las la-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $customers->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        let state = '{{ $state }}';
        let city = '{{ $city }}';
        let area = '{{ $area }}';
        $(document).ready(function() {
            // Initialize AIZ Select Picker
            $('.aiz-selectpicker').selectpicker();

            if (state.length > 0) {
                filterCity(state);
            }
            if (city.length > 0) {
                filterArea(city);
            }

            // Handle Division Change
            $('#division').on('change', function() {
                var divisionId = $(this).val();
                $('#city option').each(function() {
                    if ($(this).data('division') == divisionId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                $('#city').val('').selectpicker('refresh');
                $('#area').val('').selectpicker('refresh');
            });

            // Handle City Change
            $('#city').on('change', function() {
                var cityId = $(this).val();
                $('#area option').each(function() {
                    if ($(this).data('city') == cityId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                $('#area').val('').selectpicker('refresh');
            });

            $('#filterRescheduleDate').on('change', function() {
                var rescheduleDate = $(this).val();
                $('#reschedule_date').val(rescheduleDate);
                $('#formSubmitButton').click();
            });

            function filterCity(stateId) {
                $('#city option').each(function() {
                    if ($(this).data('division') == stateId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                $('#city').val(city).selectpicker('refresh');
            }

            function filterArea(cityId) {
                $('#area option').each(function() {
                    if ($(this).data('city') == cityId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                $('#area').val(area).selectpicker('refresh');
            }
        });

        function resetForm() {
            window.location.href = '{{ route('customers.filtered') }}';
        }
    </script>
@endsection
