@extends('backend.layouts.app')
@php
    $skinConcerns = Cache::remember('skin_concern', now()->addMinutes(60), function () {
        $metaObject = \App\Models\MetaObject::with('items')->where('name', 'Skin Concern')->first();
        $skin_concerns = $metaObject ? $metaObject->items->pluck('title')->toArray() : [];
        if (count($skin_concerns) == 0) {
            $skin_concerns = ['Acne', 'Dark Spots', 'Black Heads', 'Wrinkles', 'Dullness', 'Sensitivity'];
        }
        return $skin_concerns;
    });
    $skinTypes = ['Oily', 'Dry', 'Combination', 'Sensitive', 'Normal'];

    // Cache::forget('coupons_for_' . Auth::id());
    $coupons = Cache::remember('coupons_for_' . Auth::id(), now()->addMinutes(60), function () {
        return \App\Models\Coupon::valid()
            ->forUser(Auth::user())
            ->orderBy('end_date', 'asc')
            ->get();
    });
    $customerPhone = $customer->phone ?? null;
@endphp
@section('content')
<style>
    th{
        text-align: left;
        padding-right: 5px;
    }
    td{
        text-align: left;
    }
</style>
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="d-flex justify-content-between gutters-10">
        <div class="col-md-6">
            <div class="align-items-center">
                <div class="d-flex">
                    <div class="mr-2">
                        @if ($customer->avatar_original != null)
                            <img src="{{ uploaded_asset($customer->avatar_original) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';" width="40">
                        @else
                            <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="image rounded-circle" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';" width="40">
                        @endif
                    </div>
                    <h3 class="m-0 my-1 mr-2 name">{{ ucwords($customer->name) }}</h3>
                    @if($customer->customer_group != NULL)
                    <div class="mt-1 text-center text-success justify-content-center" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="{{ $customer->customer_group->group->group_name }}" data-content="{{ @$customer->customer_group->group->message }}">
                        @if($customer->customer_group->group->group_image != '')
                            <img width="30" title="{{ $customer->customer_group->group->group_name }}" src="{{ uploaded_asset($customer->customer_group->group->group_image)}}"  alt="{{ $customer->customer_group->group->group_name }}">
                        @else
                            {!! $customer->customer_group->group->group_icon !!}
                        @endif
                    </div>
                    @else
                    <div class="mt-1 text-center text-success justify-content-center" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="{{ $customer->customer_group->group->group_name }}" data-content="{{ @$customer->customer_group->group->message }}">
                    @if($customer->customer_group->group->group_image != '')
                            <img width="30" src="{{ uploaded_asset(@$customer->customer_group->group->group_image)}}" title="{{ $customer->customer_group->group->group_name }}" alt="{{ $customer->customer_group->group->group_name }}">
                        @else
                            {!! $customer->customer_group->group->group_icon !!}
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @php
                $address = null;
                if($customer->addresses && $customer->addresses->isNotEmpty()) {
                    $address = $customer->addresses->where('set_default', 1)->first() ?? $customer->addresses->first();
                }
                if (!$address) {
                    $shipping_address = \App\Models\Order::where('user_id', $customer->id)->whereNotNull('shipping_address')->latest()->first()?->shipping_address;
                    $address = $shipping_address ? json_decode($shipping_address, true) : null;
                }
            @endphp
            @if($address != null)
                <ul class="mt-2 list-unstyled mb-0">
                    <li><span>Address : {{ data_get($address, 'address', 'N/A') }}</span></li>
                    <li><span>State : {{ data_get($address, 'state.name') ?: data_get($address, 'state', 'N/A') }}</span></li>
                    <li><span>City : {{ data_get($address, 'city.name') ?: data_get($address, 'city', 'N/A') }}</span></li>
                    <li><span>Area : {{ data_get($address, 'area.name') ?: data_get($address, 'area', 'N/A') }}</span></li>
                    <li><span>Phone : {{ data_get($address, 'phone', 'N/A') }}</span></li>
                </ul>
                @php
                    $customerPhone ??= data_get($address, 'phone');
                @endphp
            @endif
        </div>
        <div class="col-md-6 d-flex justify-content-end">
            <table>
                <tbody>
                    <tr class="align-items-center mb-2">
                        <th><h5 class="mb-0">Profile</h5></th>
                        <td>
                            <button id="edit-profile" class="btn btn-soft-primary btn-sm px-3 py-1" data-toggle="tooltip" data-placement="bottom" title="{{ ('Edit Profile') }}">
                                <i class="las la-edit"></i>
                            </button>
                            <button id="assign-coupon" class="btn btn-soft-success btn-sm px-3 py-1" data-toggle="tooltip" data-placement="bottom" title="{{ ('Assign Coupon') }}">
                                <i class="las la-ticket-alt"></i>
                            </button>
                            <button id="fallback-sms" class="btn btn-soft-info btn-sm px-3 py-1" data-toggle="tooltip" data-placement="bottom" title="Fallback SMS">
                                <i class="las la-sms"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th>Gender: </th>
                        <td class="gender">{{ ucfirst($customer->gender ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <th>Email: </th>
                        <td class="email">{{ isset($customer->email) ? $customer->email : 'N/A'}}</td>
                    </tr>
                    <tr>
                        <th>Date of birth: </th>
                        <td class="date_of_birth">
                            {{ is_null($customer->date_of_birth) ? 'N/A' : \Carbon\Carbon::parse($customer->date_of_birth)->format('j M, Y') }}
                        </td>
                    </tr>
                    <tr>
                        <th>Balance: </th>
                        <td>{{single_price($customer->balance)}}</td>
                    </tr>
                    <tr>
                        <th>Recent Login: </th>
                        <td>
                            @php
                            if($customer->recent_login != NULL){
                                $mydate = $customer->recent_login;
                                $result = Carbon::createFromFormat('Y-m-d H:i:s', $mydate)->diffForHumans('now');
                                echo $result;
                            }
                            @endphp
                        </td>
                    </tr>
                    <tr>
                        <th>Customer Group: </th>
                        <td>{{ $customer->customer_group->group->group_name }}</td>
                    </tr>
                    @if($customer->meta('customer_label'))
                        <tr>
                            <th>Customer Label: </th>
                            <td>
                                {{-- {{ \App\Enums\CustomerLabels::getLabel($label) }} --}}
                                @foreach ($customer->meta('customer_label') ?? [] as $label)
                                    <span data-toggle="tooltip" data-placement="bottom" title="{{ \App\Enums\CustomerLabels::getLabel($label) }}" class="badge badge-{{ \App\Enums\CustomerLabels::getLabelGroup($label) }} badge-inline">{{ \App\Enums\CustomerLabels::getLabel($label) }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row gutters-10">
    <div class="col-6 col-md-3">
        <div class="bg-grad-1 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                <div class="h3 fw-700">
                    {{ $customer->delivered_orders_count }} {{ $customer->delivered_orders_count > 1 ? translate('Orders') : translate('Order') }}
                </div>
                <div class="opacity-50">{{ ('Delivered') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">

                <div class="h3 fw-700">
                    {{ $customer->cancelled_orders_count }} {{ $customer->cancelled_orders_count > 1 ? translate('Orders') : translate('Order') }}
                </div>
                <div class="opacity-50">{{ ('Cancelled') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                <div class="h3 fw-700">
                    {{ $customer->returned_orders_count }} {{ $customer->returned_orders_count > 1 ? translate('Orders') : translate('Order') }}
                </div>
                <div class="opacity-50">{{ ('Returned') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="bg-grad-1 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                <div class="h3 fw-700">
                    {{ $customer->others_orders_count }} {{ $customer->others_orders_count > 1 ? translate('Orders') : translate('Order') }}
                </div>
                <div class="opacity-50">{{ ('In Queue') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row gutters-10">
    <div class="col-md-4" onclick="products();" style="cursor: pointer;">
        <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                <div class="h3 fw-700"><span id="ordered_products_count">--</span> {{ ('Product(s)') }}</div>
                <div class="d-flex justify-content-between">
                    <div class="opacity-50">{{ ('ordered') }}</div>
                    <div class="opacity-50">{{ ('View Details') }} <i class="las la-arrow-circle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4" onclick="wishlists();" style="cursor: pointer;">
        <div class="bg-grad-1 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                <div class="h3 fw-700"><span id="wishlist_products_count">--</span> {{ ('Product(s)') }}</div>
                <div class="d-flex justify-content-between">
                    <div class="opacity-50">{{ ('in wishlist') }}</div>
                    <div class="opacity-50">{{ ('View Details') }} <i class="las la-arrow-circle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4" onclick="carts();" style="cursor: pointer;">
        <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                <div class="h3 fw-700">
                    <span id="cart_products_count">--</span> {{ ('Product(s)') }}
                </div>
                <div class="d-flex justify-content-between">
                    <div class="opacity-50">{{ ('in cart') }}</div>
                    <div class="opacity-50">{{ ('View Details') }} <i class="las la-arrow-circle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    @if(get_setting('reward_point_system') == 1)
        <div class="col-md-4" onclick="reward_point();" style="cursor: pointer;">
            <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
                <div class="p-3">

                    @if($customer->point_balance > 0)
                        <div class="h3 fw-700">
                            {{ number_format($customer->point_balance) }} {{ ('Points') }}
                        </div>
                    @else
                        <div class="h3 fw-700">
                            0 {{ ('point') }}
                        </div>
                    @endif
                    <div class="d-flex justify-content-between">
                        <div class="opacity-50">{{ ('left') }}</div>
                        <div class="opacity-50">{{ ('View Details') }} <i class="las la-arrow-circle-right"></i></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>


<div class="row gutters-5">
    <div class="col-md-8">
        <div class="card">
            <form class="" action="" method="GET">
                <div class="card-header row gutters-5">
                    <div class="col text-center text-md-left">
                        <h5 class="mb-md-0 h6">{{ ('Purchase History') }}</h5>
                    </div>
                </div>
            </form>

            <div class="card-body accordion" id="accordionExample">
                @include('backend.components.recent-orders-list-preloader')
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Call Logs') }}</h5>
                <div class="ml-auto">
                    <span id="call-duration">00:00:00</span>
                    <a role="button" id="start-call-timer" class="btn btn-soft-secondary btn-icon btn-sm btn-circle" title="{{ ('Start Call') }}">
                        <i class="las la-tty"></i>
                    </a>
                    <a role="button" id="end-call-timer" class="btn btn-soft-danger btn-icon btn-sm btn-circle" title="{{ ('End Call') }}" style="display: none;">
                        <i class="las la-phone-volume"></i>
                    </a>
                    <a role="button" id="create-call-log" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ ('Add Call Log') }}">
                        <i class="las la-plus"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12 table-responsive" id="call-log-list">
                        {{-- Loading animation --}}
                        <div class="text-center my-2">
                            <i class="las la-spinner la-spin la-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Wishlists Modal -->
<div class="modal fade" id="wishlists">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6">{{ ('Wishlist')}}</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
            <div class="row gutters-5" id="wishlist_products">
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">{{ ('Loading...')}}</span>
                    </div>
                </div>
            </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- // Cart Modal -->
<div class="modal fade" id="carts">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6">{{ ('Cart')}}</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th data-breakpoints="lg">{{ ('Product')}}</th>
                            <th data-breakpoints="lg">{{ ('Tax')}}</th>
                            <th data-breakpoints="lg">{{ ('Qty.')}}</th>
                            <th data-breakpoints="lg">{{ ('Price')}}</th>
                            <th data-breakpoints="lg">{{ ('Total')}}</th>
                        </tr>
                    </thead>
                    <tbody id="cart_products">
                        <tr>
                            <td colspan="5" class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">{{ ('Loading...')}}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="px-3 py-3 mb-2 border-top d-flex justify-content-between">
                    <span class="opacity-60 fs-15">{{ ('Subtotal')}}</span>
                    <span class="fw-600 fs-17" id="cart_total_amount">--</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- // Products Modal -->
<div class="modal fade" id="products">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6">{{ ('Ordered Products')}}</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th data-breakpoints="lg">{{ ('Order Date')}}</th>
                            <th data-breakpoints="lg">{{ ('Order Code')}}</th>
                            <th data-breakpoints="lg">{{ ('Product')}}</th>
                            <th data-breakpoints="lg">{{ ('Qty.')}}</th>
                        </tr>
                    </thead>
                    <tbody id="order_products">
                        <tr>
                            <td colspan="4" class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">{{ ('Loading...')}}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
            </div>
        </div>
    </div>
</div>

{{-- Profile Modal --}}
<div class="modal fade" id="profile-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6">{{ ('Profile')}}</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="profile-form" method="get">
                    <fieldset>
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        <div class="row gutters-5">
                            <div class="form-group col-md-6">
                                <label for="name">{{ ('Name')}}</label>
                                <input type="text" class="form-control form-control-sm" name="name" id="name" value="{{ $customer->name }}" placeholder="{{ ('Name') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">{{ ('Email')}}</label>
                                <input type="email" class="form-control form-control-sm" name="email" id="email" value="{{ $customer->email }}" placeholder="{{ ('Email') }}" {{ filled($customer->email) ? 'disabled' : '' }}>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="phone">{{ ('Phone')}}</label>
                                <input type="text" class="form-control form-control-sm" name="phone" id="phone" value="{{ $customer->phone }}" placeholder="{{ ('Phone') }}" {{ filled($customer->phone) ? 'disabled' : '' }}>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="gender">{{ ('Gender') }}</label>
                                <select class="form-control form-control-sm aiz-selectpicker" name="gender" id="gender">
                                    <option value="">{{ ('Select Gender') }}</option>
                                    <option value="male" {{ strtolower($customer->gender) == 'male' ? 'selected' : '' }}>{{ ('Male') }}</option>
                                    <option value="female" {{ strtolower($customer->gender) == 'female' ? 'selected' : '' }}>{{ ('Female') }}</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="date_of_birth">{{ ('Date of Birth') }}</label>
                                <input type="date" class="form-control form-control-sm" name="date_of_birth" id="date_of_birth" value="{{ !is_null($customer->date_of_birth) ? \Carbon\Carbon::parse($customer->date_of_birth)->format('Y-m-d') : '' }}" max="{{ now()->format('Y-m-d') }}" placeholder="{{ ('Date of Birth') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="skin_type">{{ ('Skin Type') }}</label>
                                <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" id="skin_type" name="skin_type">
                                    <option value="">{{ ('Select Skin Type') }}</option>
                                    @foreach ($skinTypes as $type)
                                        <option value="{{ $type }}" {{ strtolower($customer->meta('skin_type') ?? '') === strtolower($type) ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="skin_concern">{{ ('Skin Concern') }}</label>
                                <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" id="skin_concern" name="skin_concern[]" multiple title="{{ ('Select Skin Concern') }}">
                                    <option value="" disabled>{{ ('Select Skin Concern') }}</option>
                                    @foreach ($skinConcerns as $concern)
                                        <option value="{{ $concern }}" {{ in_array($concern, $customer->meta('skin_concern') ?? []) ? 'selected' : '' }}>{{ $concern }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                @php
                                    $labelDetails = '';
                                    foreach (\App\Enums\CustomerLabels::getLabels() as $index => $label) {
                                        $labelDetails .= '<span class="text-' . \App\Enums\CustomerLabels::getLabelGroup($index) . '"><strong>' . $label['name'] . ': </strong></span>' . $label['details'] . '<br>';
                                    }
                                @endphp
                                <label for="customer_label" class="d-flex justify-content-start align-items-center">{{ ('Customer Label') }} <i id="labelDetails" class="las la-info-circle text-info ml-1 font-weight-bold fs-18" title="Details" data-details="{{ $labelDetails }}" role="button"></i></label>
                                <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" id="customer_label" name="customer_label[]" title="{{ ('Select Customer Label') }}" multiple>
                                    <option value="" disabled>{{ ('Select Customer Label') }}</option>
                                    @foreach (\App\Enums\CustomerLabels::getLabels() as $index => $label)
                                        <option value="{{ $index }}" {{ in_array($index, $customer->meta('customer_label') ?? []) ? 'selected' : '' }}>{{ $label['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
                <button type="button" class="btn btn-sm btn-success" id="update-profile">{{ ('Update')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- // reward point Modal -->
<div class="modal fade" id="reward_point">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6">{{ ('Reward point history')}}</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if( count(@$rewardPointLogs ?? []) > 0 )
                <div class="row">
                    <div class="col-12 mx-auto">
                        @foreach (@$rewardPointLogs ?? [] as $rewardPointLog)

                            <div class="point-lister">
                                <div style="padding: 15px; @if($loop->odd) background:#ddd; @else  background:#f2f2f2; @endif">
                                    <div class="text">{{$rewardPointLog->activity_str}}</div>
                                    <div class="sub-text"><i>{{$rewardPointLog->created_at->diffForHumans()}}</i></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @else
                    <div class="row">
                        <div class="col-xl-8 mx-auto">
                            <div class="shadow-sm bg-white p-4 rounded">
                                <div class="text-center p-3">
                                    <i class="las la-frown la-3x opacity-60 mb-3"></i>
                                    <h3 class="h4 fw-700">{{ ('Your Reward points log is empty now')}}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
            </div>
        </div>
    </div>
</div>

<style>
    .text-yellow {
        color: #ffa707 !important;
    }
</style>
@endsection

@section('modal')
    @include('modals.delete_modal')

    {{-- Call Log Create Modal Start --}}
    <div id="call-log-create-modal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ ('New Call Log') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <form id="call-logs-create-form" action="{{ route('call-logs.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="reference" value="customer">
                        <input type="hidden" name="reference_id" value="{{ $customer->id }}">
                        <div class="form-group mb-3">
                            <label for="status">{{ ('Status') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-control aiz-selectpicker" name="status" id="status">
                                    <option value="">{{ ('Select Status') }}</option>
                                    @foreach (\App\Enums\CallStatus::getStatus('crm') as $value => $text)
                                        <option value="{{ $value }}">{{ (ucwords($text)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="text-danger" style="display: none" id="status_error"></div>
                        </div>
                        <div class="form-group mb-3" id="reschedule" style="display: none;">
                            <label for="rescheduled_at">{{ ('Reschedule At') }}</label>
                            <div class="input-group">
                                <input type="datetime-local" min="{{ now()->format('Y-m-d\TH:i') }}" class="form-control" name="rescheduled_at" id="rescheduled_at">
                            </div>
                            <div class="text-danger" style="display: none" id="rescheduled_at_error"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="note">{{ ('Note') }}</label>
                            <div class="input-group">
                                <textarea type="text" class="form-control" name="note" id="note" placeholder="{{ ('Note') }}" rows="3" required></textarea>
                            </div>
                            <div class="text-danger" style="display: none" id="note_error"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="duration">{{ ('Call Duration') }}</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="duration" id="duration" placeholder="{{ ('Duration in minutes e.g. 5 or 8.2') }}">
                            </div>
                            <div class="text-danger" style="display: none" id="duration_error"></div>
                        </div>
                    </form>
                    <div class="form-group mb-3 text-right">
                        <button id="clear-btn" class="btn btn-sm btn-secondary" onclick="resetForm()">{{ ('Clear') }}</button>
                        <button id="submit-btn" type="submit" class="btn btn-sm btn-success">{{ ('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Call Log Create Modal End --}}

    {{-- Feedback Create Modal Start --}}
    <div id="feedback-create-modal" class="modal fade" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ ('New Feedback') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <form id="feedback-create-form" action="{{ route('orders.feedback.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="feedback_order_id" id="feedback_order_id" value="">
                        <input type="hidden" name="rider_behavior_note" id="rider_behavior_note" value="">
                        <input type="hidden" name="packaging_note" id="packaging_note" value="">
                        <input type="hidden" name="cs_behavior_note" id="cs_behavior_note" value="">
                        <input type="hidden" name="delivery_time_note" id="delivery_time_note" value="">
                        <input type="hidden" name="product_quality_note" id="product_quality_note" value="">
                        <div class="row">
                            <div class="col-5">
                                <div class="form-group mb-3">
                                    <label for="rider_behavior">{{ ('Rider Behavior') }} <span class="text-danger">*</span> <span title="Add Note" data-target="rider_behavior_note" class="add-note d-inline-flex justify-content-center align-items-center btn btn-sm btn-soft-info btn-icon btn-circle" style="padding:5px;height:22px;width:22px;"><i class="las la-plus fs-12 font-weight-bold"></i></span>
                                    </label>

                                    @include('backend.components.rating-input', ['name' => 'rider_behavior', 'class' => 'feedback-rating'])

                                    <div class="text-danger" style="display: none" id="rider_behavior_error"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="packaging">Packaging <span class="text-danger">*</span> <span title="Add Note" data-target="packaging_note" class="add-note d-inline-flex justify-content-center align-items-center btn btn-sm btn-soft-info btn-icon btn-circle" style="padding:5px;height:22px;width:22px;"><i class="las la-plus fs-12 font-weight-bold"></i></span></label>

                                    @include('backend.components.rating-input', ['name' => 'packaging', 'class' => 'feedback-rating'])

                                    <div class="text-danger" style="display: none" id="packaging_error"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="cs_behavior">{{ ('CS Behavior') }} <span class="text-danger">*</span> <span title="Add Note" data-target="cs_behavior_note" class="add-note d-inline-flex justify-content-center align-items-center btn btn-sm btn-soft-info btn-icon btn-circle" style="padding:5px;height:22px;width:22px;"><i class="las la-plus fs-12 font-weight-bold"></i></span></label>

                                    @include('backend.components.rating-input', ['name' => 'cs_behavior', 'class' => 'feedback-rating'])

                                    <div class="text-danger" style="display: none" id="cs_behavior_error"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="delivery_time">{{ ('Delivery Time') }} <span class="text-danger">*</span> <span title="Add Note" data-target="delivery_time_note" class="add-note d-inline-flex justify-content-center align-items-center btn btn-sm btn-soft-info btn-icon btn-circle" style="padding:5px;height:22px;width:22px;"><i class="las la-plus fs-12 font-weight-bold"></i></span></label>

                                    @include('backend.components.rating-input', ['name' => 'delivery_time', 'class' => 'feedback-rating'])

                                    <div class="text-danger" style="display: none" id="delivery_time_error"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="feedback_note">{{ ('Note') }}</label>
                                    <div class="input-group">
                                        <textarea type="text" class="form-control form-control-sm" name="feedback_note" id="feedback_note" placeholder="{{ ('Any note or suggestions') }}" rows="3"></textarea>
                                    </div>
                                    <div class="text-danger" style="display: none" id="feedback_note_error"></div>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="form-group mb-0 feedback-rating">
                                    <label>{{ ('Product Quality') }} <span title="Add Note" data-target="product_quality_note" class="add-note d-inline-flex justify-content-center align-items-center btn btn-sm btn-soft-info btn-icon btn-circle" style="padding:5px;height:22px;width:22px;"><i class="las la-plus fs-12 font-weight-bold"></i></span></label>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row gutters-5" id="product-quality-ratings">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="form-group mb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="alert alert-info py-2 font-weight-bold">
                                Rating: <span id="feedback-rating-value">0</span>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-secondary" onclick="resetFeedbackForm()">{{ ('Clear') }}</button>
                            <button id="submit-feedback" type="submit" class="btn btn-sm btn-success">{{ ('Save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Feedback Create Modal End --}}

    {{-- Call Log Details Modal Start --}}
    <div id="call-log-details-modal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ ('Call Log Details') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    {{-- Content --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Label Details Modal Start --}}
    <div id="label-details-modal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="min-height: 45px !important;">
                    <h4 class="modal-title h6">{{ ('Label Details') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body" id="label-details-content">
                </div>
            </div>
        </div>
    </div>

    <div id="fallback-sms-modal" class="modal fade">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">Fallback SMS Templates</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-3">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                                aria-orientation="vertical">
                                @foreach (\App\Enums\FallbackTemplates::getLabels() as $key => $label)
                                    <a class="nav-link fallback-option {{ $loop->first ? 'active' : '' }}" id="{{ $key }}-tab" data-toggle="pill" data-id="{{ $key }}" href="#{{ $key }}" role="tab" aria-controls="{{ $key }}" aria-selected="false">{{ $label }}</a>
                                @endforeach
                                <a class="nav-link fallback-option" id="custom-messages-tab" data-toggle="pill" href="#custom-messages" role="tab" aria-controls="custom-messages" aria-selected="false">Custom</a>
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="tab-content" id="v-pills-tabContent">
                                @foreach (\App\Enums\FallbackTemplates::getTemplates() as $key => $template)
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $key }}" role="tabpanel" aria-labelledby="{{ $key }}-tab">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="border p-3 rounded">
                                                    <div class="row">
                                                        @php
                                                            $content = \App\Enums\FallbackTemplates::replacePlaceholder($key, $customer);
                                                        @endphp
                                                        <div class="col-11" id="fallback-content-{{ $key }}" data-template="{{ $content }}">
                                                            {{ $content }}
                                                        </div>
                                                        <div class="col-1 d-flex justify-content-end">
                                                            <i class="las la-copy text-primary btn btn-sm btn-icon btn-circle copy-template" data-template="{{ $content }}" title="Copy Template" role="button"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="tab-pane fade" id="custom-messages" role="tabpanel"
                                    aria-labelledby="custom-messages-tab">
                                    <div class="row">
                                        <div class="col-12">
                                            <textarea name="custom_message" id="fallback-custom-message" class="form-control" rows="3" placeholder="Write custom message here ..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        @if (filled($customerPhone))
                            <button type="button" class="btn btn-sm btn-soft-info ml-2 fallback-submit" data-type="sms" data-toggle="tooltip" data-placement="bottom" title="Send SMS">
                                <i class="las la-sms"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-success ml-2 fallback-submit" data-type="whatsapp" data-toggle="tooltip" data-placement="bottom" title="Send WhatsApp Message">
                                <i class="lab la-whatsapp"></i>
                            </button>
                        @endif
                        @if (filled($customer->email))
                            <button type="button" class="btn btn-sm btn-soft-primary ml-2 fallback-submit" data-type="email" data-toggle="tooltip" data-placement="bottom" title="Send Email">
                                <i class="las la-envelope"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Coupon Assign Modal Start --}}
    <div id="assign-coupon-modal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">
                        {{ ('Assign Coupon') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body" style="min-height: 290px;">
                    <div class="align-items-center mb-2" id="assigned-coupons">
                        @foreach ($customer->usableCoupons as $usableCoupon)
                            <span id="coupon-{{ $usableCoupon->id }}" class="badge badge-soft-success badge-inline font-weight-bold mr-2" title="Expire At {{ \Carbon\Carbon::parse($usableCoupon->expire_date)->format('d F Y') }}">
                                {{ $usableCoupon->coupon->code }} - OFF
                                @if($usableCoupon->coupon->discount_type === 'amount')
                                    {{ single_price($usableCoupon->coupon->discount) }}
                                @elseif($usableCoupon->coupon->discount_type === 'percent')
                                    {{ $usableCoupon->coupon->discount }}%
                                @endif
                                <span role="button" class="text-danger ml-2 remove-coupon" data-id="{{ $usableCoupon->id }}" title="{{ ('Remove Coupon') }}">
                                    <i class="las la-times font-weight-bolder"></i>
                                </span>
                            </span>
                        @endforeach
                    </div>
                    <form id="coupon-assign-form">
                        <fieldset>
                            <div class="form-group">
                                <label for="coupon">{{ ('Select Coupon') }} <span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" id="coupon" name="coupon">
                                    <option value="">{{ ('Select Coupon') }}</option>
                                    @foreach ($coupons as $coupon)
                                        <option value="{{ $coupon->id }}">
                                            {{ $coupon->code }} -
                                            <span class="badge badge-soft-success badge-inline font-weight-bold">
                                                OFF
                                                @if($coupon->discount_type === 'amount')
                                                    {{ single_price($coupon->discount) }}
                                                @elseif($coupon->discount_type === 'percent')
                                                    {{ $coupon->discount }}%
                                                @endif
                                            </span>
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger" id="coupon-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="expiry_date">{{ ('Set Expire Date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" name="expiry_date" id="expiry_date" value="" placeholder="{{ ('Expiry Date') }}" min="{{ now()->format('Y-m-d') }}">
                                <span class="text-danger" id="expiry_date-error"></span>
                            </div>
                        </fieldset>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
                    <button type="button" class="btn btn-sm btn-success" id="save-coupon">{{ ('Assign')}}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        let callDuration = {{ old('duration', 0) }}; // Initialize call duration variable
        let checkInterval;
        let timerInterval;
        let callDurationInterval;
        let feedbackProducts = [];
        let feedbackRating = 0;
        let fallbackMessageTarget = '';
        $(document).ready(function() {
            $('#fallback-sms').on('click', function() {
                $('#fallback-sms-modal').modal('show');
            });
            $(document).on('click', '.fallback-submit', function() {
                let target = $('.fallback-option.active').data('id') || '';
                let type = $(this).data('type') || '';
                let message = '';
                if (target) {
                    message = $(`#fallback-content-${target}`).data('template') || '';
                } else {
                    message = $('#fallback-custom-message').val() || '';
                }
                if (message === '') {
                    AIZ.plugins.notify('danger', 'Message content is empty');
                    return;
                }

                if (type === 'whatsapp') {
                    window.open(`https://api.whatsapp.com/send?phone={{ $customer->phone }}&text=${encodeURIComponent(message)}`, '_blank');
                    AIZ.plugins.notify('success', 'Message sent via WhatsApp');
                    $('#fallback-sms-modal').modal('hide');
                    return;
                } else if (type === 'sms' || type === 'email') {
                    $.ajax({
                        type: 'POST',
                        url: `{{ route('customers.send-fallback-message') }}`,
                        data: {
                            _token: '{{ csrf_token() }}',
                            type: type,
                            customer: '{{ $customer->id }}',
                            message: message,
                        },
                        success: function(response) {
                            if (response.success) {
                                AIZ.plugins.notify('success', response.message);
                                $('#fallback-sms-modal').modal('hide');
                            } else {
                                AIZ.plugins.notify('danger', response.message);
                            }
                        },
                        error: function(xhr) {
                            AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                        }
                    });
                } else {
                    AIZ.plugins.notify('danger', `Invalid fallback channel: ${type}`);
                }
            });
            $(document).on('click', '.copy-template', function() {
                let template = $(this).data('template');
                let tempTextArea = $('<textarea>');
                $('body').append(tempTextArea);

                // Set the template content to the textarea
                tempTextArea.val(template).select();
                try{
                    document.execCommand("copy");
                    AIZ.plugins.notify('success', '{{ ('Template copied to clipboard') }}');
                } catch (err) {
                    AIZ.plugins.notify('danger', '{{ ('Failed to copy template') }}');
                } finally {
                    tempTextArea.remove();
                }
            });
            $('#expiry_date').click(function(){
                this.showPicker();
            });
            getOrdersByCustomer();
            getCartsAndWishlists();
            getCallLogs();

            async function getOrdersByCustomer() {
                await $.ajax({
                    type: 'GET',
                    url: `{{ route('customers.orders', $customer->id) }}`,
                    success: function(response) {
                        if (response.success) {
                            $('#accordionExample').html(response.view);
                            $('#order_products').html('');
                            $('#ordered_products_count').text(response.products.length || 0);
                            if(response.products.length === 0) {
                                $('#order_products').append(`
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <div class="text-muted">
                                                No products found
                                            </div>
                                        </td>
                                    </tr>
                                `);
                            } else {
                                response.products.forEach(product => {
                                    $('#order_products').append(`
                                        <tr>
                                            <td class="py-3"> ${product.order_date} </td>
                                            <td class="py-3"> ${product.order_code} </td>
                                            <td class="py-3">${product.name}</td>
                                            <td class="py-3">${product.quantity}</td>
                                        </tr>
                                    `);
                                });
                            }
                        } else {
                            AIZ.plugins.notify('danger', response.message);
                        }
                    },
                    error: function(xhr) {
                        AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                    }
                });
            }

            async function getCartsAndWishlists() {
                await $.ajax({
                    type: 'GET',
                    url: `{{ route('customers.carts_and_wishlists', $customer->id) }}`,
                    success: function(response) {
                        if (response.success) {
                            $('#wishlist_products_count').text(response.wishlist_products.length || 0);
                            $('#cart_products_count').text(response.cart_products.length || 0);

                            $('#cart_total_amount').text(response.cart_total_amount || 0);
                            renderCartProducts(response.cart_products);
                            renderWishlistProducts(response.wishlist_products);
                        } else {
                            AIZ.plugins.notify('danger', response.message);
                        }
                    },
                    error: function(xhr) {
                        AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                    }
                });
            }

            async function getCallLogs() {
                await $.ajax({
                    url: '{{ route('customers.call-logs', $customer->id) }}',
                    type: 'GET',
                    success: function(response) {
                        $('#call-log-list').html(response.view || '');
                    },
                    error: function() {
                        console.error('Error fetching call logs');
                    }
                });
            }

            function renderCartProducts(cartProducts) {
                $('#cart_products').empty();
                if (cartProducts.length > 0) {
                    cartProducts.forEach(product => {
                        $('#cart_products').append(`
                            <tr>
                                <td>
                                    <a href="${product.url}" class="text-reset" target="_blank">
                                        <div class="d-flex align-items-center">
                                            <img src="${product.image}" class="img-fit size-60px rounded cart_product_img" alt="${product.name}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                            <span class="ml-2">${product.name}</span>
                                        </div>
                                    </a>
                                </td>
                                <td>${product.tax}</td>
                                <td>${product.quantity}</td>
                                <td>${product.price}</td>
                                <td>${product.total}</td>
                            </tr>
                        `);
                    });
                } else {
                    $('#cart_products').append(`
                        <tr>
                            <td colspan="5" class="text-center">
                                <div class="text-muted">
                                    {{ ('No products found in cart') }}
                                </div>
                            </td>
                        </tr>
                    `);
                }
            }

            function renderWishlistProducts(wishlistProducts) {
                $('#wishlist_products').empty();
                if (wishlistProducts.length > 0) {
                    wishlistProducts.forEach(product => {
                        $('#wishlist_products').append(`<div class="col-md-4 col-6" id="wishlist_${product.id}">
                            <div class="card mb-2 shadow-sm">
                                <div class="card-body">
                                    <a href="${product.url}" class="d-block mb-3" target="_blank">
                                        <img src="${product.image}" class="img-fit h-140px h-md-200px" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                    </a>
                                    <h5 class="fs-14 mb-0 lh-1-5 fw-600 text-truncate-2">
                                        <a href="${product.url}" class="text-reset" target="_blank">${product.name}</a>
                                    </h5>
                                    <div class="rating rating-sm mb-1">
                                        ${product.rating}
                                    </div>
                                    <div class=" fs-14">
                                        <del class="opacity-60 mr-1">${product.base_price}</del>
                                        <span class="fw-600 text-primary">${product.discount_price}</span>
                                    </div>
                                </div>
                            </div>
                        </div>`);
                    });
                } else {
                    $('#wishlist_products').append(`
                        <div class="col">
                            <div class="text-center bg-white p-4 rounded shadow">
                                <img class="mw-100 h-200px" src="{{ static_asset('assets/img/nothing.svg') }}" alt="Image">
                                <h5 class="mb-0 h5 mt-3">{{ ("There isn't anything added yet")}}</h5>
                            </div>
                        </div>
                    `);
                }
            }

            $('#assign-coupon').on('click', function() {
                $('#coupon').val('');
                $('#coupon-error').text('');
                $('#expiry_date').val('');
                $('#expiry_date-error').text('');

                AIZ.plugins.bootstrapSelect('refresh');
                $('#assign-coupon-modal').modal('show');
            });

            $('#assigned-coupons').on('click', '.remove-coupon', function() {
                const assignedId = $(this).data('id');
                const url = `{{ route('customers.remove_coupon', ':id') }}`.replace(':id', assignedId);
                Swal.fire({
                    title: '{{ ('Are You Sure?') }}',
                    text: '{{ ('You want to remove this coupon?') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{ ('Yes, Remove It!') }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'DELETE',
                            url: url,
                            success: function(response) {
                                if (response.success) {
                                    AIZ.plugins.notify('success', response.message);
                                    $('#assigned-coupons').find(`#coupon-${assignedId}`).remove();
                                } else {
                                    AIZ.plugins.notify('danger', response.message);
                                }
                            },
                            error: function(xhr) {
                                AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                            }
                        });
                    }
                });
            });

            $('#save-coupon').on('click', function() {
                const couponId = $('#coupon').val();
                const expiryDate = $('#expiry_date').val();
                let valid = true;
                if (!couponId) {
                    $('#coupon-error').text('{{ ('Please select a coupon') }}');
                    valid = false;
                }else{
                    $('#coupon-error').text('');
                }
                if(!expiryDate) {
                    $('#expiry_date-error').text('{{ ('Please set an expiry date') }}');
                    valid = false;
                } else {
                    $('#expiry_date-error').text('');
                }

                if (!valid) {
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: '{{ route('customers.assign_coupon', $customer->id) }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        coupon_id: couponId,
                        expiry_date: expiryDate
                    },
                    success: function(response) {
                        if (response.success) {
                            AIZ.plugins.notify('success', response.message);
                            $('#assign-coupon-modal').modal('hide');
                            $('#assigned-coupons').append(`
                                <span id="coupon-${response.data.id}" class="badge badge-soft-success badge-inline font-weight-bold mr-2" title="Expire At ${response.data.expire_date}">
                                    ${response.data.coupon} - OFF ${response.data.discount}
                                    <span role="button" class="text-danger ml-2 remove-coupon" data-id="${response.data.id}" title="{{ ('Remove Coupon') }}">
                                        <i class="las la-times font-weight-bolder"></i>
                                    </span>
                                </span>
                            `);
                        } else {
                            AIZ.plugins.notify('danger', response.message);
                        }
                    },
                    error: function(xhr) {
                        AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                    }
                });
            });

            $('.add-note').on('mouseenter', function() {
                const target = $(this).data('target');
                let value = $(`#${target}`).val() || '';
                if (value.trim() === '') {
                    $(this).attr('title', '{{ ('Add Note') }}');
                } else {
                    $(this).attr('title', value);
                }
            });

            $('.add-note').on('click', function() {
                const target = $(this).data('target');
                const oldValue = $(`#${target}`).val();
                // Take Input Note
                Swal.fire({
                    title: 'Note',
                    input: 'textarea',
                    inputValue: oldValue,
                    inputPlaceholder: 'Type your note here...',
                    showCancelButton: true,
                    confirmButtonText: 'Save',
                    cancelButtonText: 'Cancel',
                    allowOutsideClick: false, // prevent click outside
                    allowEscapeKey: false,    // prevent Esc key
                    preConfirm: (note) => {
                        $(`#${target}`).val(note);
                    }
                });
            });

            $('#call-log-list').on('click', '.delete-call-log', function (e) {
                e.preventDefault();
                const url = $(this).data('href');

                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You won\'t be able to revert this!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Delete It!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'GET',
                            url: url,
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire('Deleted', 'Your file has been deleted.', 'success');
                                    getCallLogs();
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('Error!', '{{ ('Something went wrong') }}', 'error');
                            }
                        });
                    }
                });
            });

            $('#accordionExample').on('click', '.load-more-order', async function(e) {
                e.preventDefault();
                const $this = $(this);
                $this.html('<i class="las la-spinner la-spin"></i>');
                var url = $this.data('href');
                await $.ajax({
                    type: 'GET',
                    url: url,
                    success: function(response) {
                        if (response.success) {
                            $this.remove();
                            $('#accordionExample').append(response.view);
                        } else {
                            AIZ.plugins.notify('danger', response.message);
                        }
                    },
                    error: function(xhr) {
                        AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                    }
                });
            });

            $('#accordionExample').on('click', '.feedback-btn', async function(e) {
                e.preventDefault();
                resetFeedbackForm();
                const orderId = $(this).data('order');
                feedbackProducts = $(this).data('products');
                await renderProductQualityRating(feedbackProducts);
                $('#feedback_order_id').val(orderId);
                $('#feedback-create-modal').modal('show');
            });

            $('#labelDetails').on('click', function() {
                const details = $(this).data('details');
                $('#label-details-content').html(details);
                $('#label-details-modal').modal('show');
            });

            async function renderProductQualityRating(products) {
                const $container = $('#product-quality-ratings');
                $container.empty();

                products.forEach(product => {
                    let ratingHtml = generateRating(`feedback_product_rating_${product.id}`, 24);

                    $container.append(`
                        <div class="col-7 mb-2">
                            <div class="input-group">
                                <select class="form-control form-control-sm" name="feedback_products[]">
                                    <option value="${product.id}">${product.name}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-5 mb-2 d-flex justify-content-center align-items-center">
                            ${ratingHtml}
                        </div>
                    `);
                });

                bindRatingEvents(); // attach the hover/click behavior
            }


            function generateRating(name, size = 24) {
                let html = '<div class="rating rating-input d-flex">';
                for (let i = 1; i <= 5; i++) {
                    html += `
                        <label class="m-0 p-0">
                            <input type="radio" class="feedback_product_ratings d-none" name="${name}" value="${i}">
                            <i class="las la-star fs-${size}" data-value="${i}" data-name="${name}"></i>
                        </label>`;
                }
                html += '</div>';
                return html;
            }

            function bindRatingEvents() {
                // Hover effect
                $(document).off('mouseenter', '.rating-input i').on('mouseenter', '.rating-input i', function () {
                    const value = $(this).data('value');
                    const name = $(this).data('name');

                    // highlight all stars up to hovered one
                    $(`.rating-input i[data-name="${name}"]`).each(function () {
                        $(this).toggleClass('text-yellow', $(this).data('value') <= value);
                    });
                });

                // Restore state on mouseleave
                $(document).off('mouseleave', '.rating-input').on('mouseleave', '.rating-input', function () {
                    const name = $(this).find('i').data('name');
                    const checkedVal = $(`input[name="${name}"]:checked`).val() || 0;

                    $(`.rating-input i[data-name="${name}"]`).each(function () {
                        $(this).toggleClass('text-yellow', $(this).data('value') <= checkedVal);
                    });
                });

                // Click to select
                $(document).off('click', '.rating-input i').on('click', '.rating-input i', function () {
                    const value = $(this).data('value');
                    const name = $(this).data('name');

                    // set radio value
                    $(`input[name="${name}"][value="${value}"]`).prop('checked', true).trigger('change');

                    // keep highlight
                    $(`.rating-input i[data-name="${name}"]`).each(function () {
                        $(this).toggleClass('text-yellow', $(this).data('value') <= value);
                    });
                });
            }

            // Use event delegation for static elements
            $(document).on('change', '.feedback-rating', function() {
                calculateRating();
            });

            // Use event delegation for dynamic elements
            $(document).on('change', '.feedback_product_ratings', function() {
                calculateRating();
            });

            function calculateRating(){
                feedbackRating = 0;
                let count = 5;
                let rating = 0;
                let total = 0;

                let productRating = 0;
                let riderBehavior = parseInt($('input[name="rider_behavior"]:checked').val() || 0);
                let packaging = parseInt($('input[name="packaging"]:checked').val() || 0);
                let csBehavior = parseInt($('input[name="cs_behavior"]:checked').val() || 0);
                let deliveryTime = parseInt($('input[name="delivery_time"]:checked').val() || 0);

                $('input[name^="feedback_product_rating_"]:checked').each(function() {
                    const value = parseInt($(this).val() || 0);
                    if (!isNaN(value) && value > 0) { // Only count values > 0
                        productRating += value;
                    }
                });

                let avgProductRating = feedbackProducts.length > 0 ? productRating / feedbackProducts.length : 0;

                total = riderBehavior + packaging + csBehavior + deliveryTime + avgProductRating;


                rating = count > 0 ? (total / count) : 0;


                $('#feedback-rating-value').text(Math.round(rating));
                feedbackRating = Math.round(rating);
            }

            $('#submit-feedback').on('click', function(e){
                if(feedbackRating <= 0) {
                    e.preventDefault();
                    AIZ.plugins.notify('danger', '{{ ('Please provide a rating') }}');
                    return;
                }
                let data = $('#feedback-create-form').serializeArray();
                data.push({ name: 'rating', value: feedbackRating });
                data.push({ name: 'user_id', value: {{ $customer->id }} });
                $.ajax({
                    type: 'POST',
                    url: '{{ route('orders.feedback.store') }}',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            AIZ.plugins.notify('success', response.message);
                            $('#feedback-create-modal').modal('hide');
                            $('#feedback-btn-' + response.data.order_id).remove();
                        } else {
                            AIZ.plugins.notify('danger', response.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                AIZ.plugins.notify('danger', value);
                            });
                        } else {
                            AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                        }
                    }
                });
            })

            $('#edit-profile').on('click', function() {
                $('#profile-modal').modal('show');
            });

            $('#update-profile').on('click', async function() {
                var form = $('#profile-form');
                await $.ajax({
                    type: 'PUT',
                    url: `{{ route('customers.update', $customer->id) }}`,
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            AIZ.plugins.notify('success', response.message);
                            $('.name').text(response.data.name);
                            $('.email').text(response.data.email);
                            $('.phone').text(response.data.phone);
                            $('.date_of_birth').text(response.data.date_of_birth);
                            $('.gender').text(response.data.gender);

                            $('#profile-modal').modal('hide');
                        } else {
                            AIZ.plugins.notify('danger', response.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                AIZ.plugins.notify('danger', value);
                            });
                        } else {
                            AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                        }
                    }
                });
            });

            // Start Call button click handler
            $('#start-call-timer').click(function() {
                $(this).hide();
                $('#end-call-timer').show();

                let totalSeconds = 0;
                callDurationInterval = setInterval(function() {
                    totalSeconds++;

                    // Format as HH:MM:SS
                    const hours = Math.floor(totalSeconds / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    // Display formatted time
                    const formattedTime =
                        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    $('#call-duration').text(formattedTime);
                }, 1000);
            });

            // End Call button click handler
            $('#end-call-timer').click(function() {
                clearInterval(callDurationInterval);  // Stop the timer

                // Parse the displayed time (HH:MM:SS)
                const timeText = $('#call-duration').text();
                const [hours, minutes, seconds] = timeText.split(':').map(Number);

                // Calculate total minutes in decimal format (e.g., 1.12 for 01:12, 115.22 for 01:55:22)
                const totalMinutes = (hours * 60) + minutes + (seconds / 100);
                callDuration = parseFloat(totalMinutes.toFixed(2));  // Store as float (e.g., 1.12, 115.22)

                // Reset UI
                $('#call-duration').text('00:00:00');
                $(this).hide();
                $('#start-call-timer').show();

                // console.log('Call duration stored:', callDuration);  // Example: 1.12, 0.06, 115.22
            });

            $('#create-call-log').on('click', function(){
                resetForm();
                if(!$('#end-call-timer').is(':hidden')){
                    $('#end-call-timer').click();
                }
                $('#duration').val(callDuration);
                var modal = $('#call-log-create-modal');
                modal.modal('show');
            });

            $('#submit-btn').on('click', async function(e){
                const $this = $(this);
                var status = $('#status').val();
                if(status == ''){
                    $('#status_error').html('{{ ('Status is required') }}').show();
                    return;
                }
                $this.prop('disabled', true);
                const data = $('#call-logs-create-form').serializeArray();
                data.push({ name: 'user_id', value: {{ $customer->id }} });
                data.push({ name: 'type', value: 'feedback' });
                await $.ajax({
                    type: 'POST',
                    url: '{{ route('call-logs.store') }}',
                    data: data,
                    success: function (response) {
                        if (response.success) {
                            $('#call-log-create-modal').modal('hide');
                            Swal.fire('Success', 'Call log has been created.', 'success');
                            getCallLogs();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                        $this.prop('disabled', false);
                    },
                    error: function () {
                        Swal.fire('Error!', '{{ ('Something went wrong') }}', 'error');
                        $this.prop('disabled', false);
                    }
                });
            });

            $(document).on('click', '.view-btn', function(){
                // alert('clicked');
                var log = $(this).data('log');
                var modal = $('#call-log-details-modal');
                let html = `<p><strong>{{ ('Status') }}:</strong> ${log.status}</p>`;
                if(log.rescheduled_at) {
                    html += `<p><strong>{{ ('Rescheduled At') }}:</strong> ${log.rescheduled_at}</p>`;
                }
                modal.find('.modal-body').html(`
                    ${html}
                    <p><strong>{{ ('Note') }}:</strong> ${log.note}</p>
                    <p><strong>{{ ('Duration') }}:</strong> ${log.duration}</p>
                    <p><strong>{{ ('Called By') }}:</strong> ${log.user} at ${log.created_at}</p>
                `);
                modal.modal('show');
            });
        });

        function resetForm(){
            $('#status').val('');
            $('#reschedule').hide();
            $('#note').val('');
            $('#duration').val('0');
            $('#note_error').val('');
            if (window.AIZ && AIZ.plugins && AIZ.plugins.bootstrapSelect) {
                AIZ.plugins.bootstrapSelect('refresh');
            } else {
                $('.aiz-selectpicker').selectpicker('refresh');
            }
        }

        function resetFeedbackForm() {
            $('.feedback-rating').prop('checked', false);
            $('.feedback_product_ratings').prop('checked', false);
            $('.rating-input i').removeClass('active');
            $('.rating-input i').removeClass('text-yellow');
            $('#feedback_note').val('');
            $('#rider_behavior_note').val('');
            $('#packaging_note').val('');
            $('#cs_behavior_note').val('');
            $('#delivery_time_note').val('');
            $('#product_quality_note').val('');
            feedbackRating = 0;
            $('#feedback-rating-value').text(0);
        }

        $('#status').on('change', function() {
            let status = $(this).val();
            if(status === 're_schedule'){
                $('#reschedule').show();
            } else {
                $('#reschedule').hide();
            }
        });

        $(function() {
            $('[data-toggle="popover"]').popover()
        })
        function wishlists()
        {
            $('#wishlists').modal('show');
        }
        function carts()
        {
            $('#carts').modal('show');
        }
        function products()
        {
            $('#products').modal('show');
        }
        function reward_point()
        {
            $('#reward_point').modal('show');
        }
    </script>
@endsection
