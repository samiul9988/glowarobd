@extends('backend.layouts.app')
@php
    $permissions = json_decode($role->permissions);
    // dd($permissions);
@endphp
@section('content')
    <div class="col-lg-7 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Role Information') }}</h5>
            </div>
            <form action="{{ route('roles.update', $role->id) }}" method="POST">
                <input name="_method" type="hidden" value="PATCH">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label" for="name">{{ ('Name') }}</label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{ ('Name') }}" id="name" name="name"
                                class="form-control" value="{{ $role->getTranslation('name', $lang) }}" required>
                        </div>
                    </div>
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Permissions') }}</h5>
                    </div>
                    <br>
                    <div class="form-group row">
                        <div class="col">
                            <div class="mb-3">
                                {{-- Dashboards --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Dashboards:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Admin Dashboard') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="form-control demo-sw" value="admin_dashboard"
                                                                @php if(in_array('admin_dashboard', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Customer Care Dashboard') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="form-control demo-sw" value="customer_care_dashboard"
                                                                @php if(in_array('customer_care_dashboard', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Packaging Dashboard') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="form-control demo-sw" value="packaging_dashboard"
                                                                @php if(in_array('packaging_dashboard', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Account & Inventory Dashboard') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="form-control demo-sw"
                                                                value="account_inventory_dashboard"
                                                                @php if(in_array('account_inventory_dashboard', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Products --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Products:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('All') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox"
                                                                class="check-all-products form-control demo-sw">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Products') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-product form-control demo-sw" value="2"
                                                                @php if(in_array('2', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Brands') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-product form-control demo-sw" value="brands"
                                                                @php if(in_array('brands', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Categories') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-product form-control demo-sw"
                                                                value="categories"
                                                                @php if(in_array('categories', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Attributes') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-product form-control demo-sw"
                                                                value="attributes"
                                                                @php if(in_array('attributes', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Colors') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-product form-control demo-sw" value="colors"
                                                                @php if(in_array('colors', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Product Reviews') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-product form-control demo-sw"
                                                                value="product_reviews"
                                                                @php if(in_array('product_reviews', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Product Custom Fields') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-product form-control demo-sw"
                                                                value="product_custom_fields"
                                                                @php if(in_array('product_custom_fields', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Meta Objects') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-product form-control demo-sw" value="27"
                                                                @php if(in_array('27', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Marketing') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-other form-control demo-sw" value="11"
                                                                @php if(in_array('11', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Orders --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Orders:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('All') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="form-control demo-sw check-all-orders"
                                                                value="3"
                                                                @php if(in_array('3', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                @if (addon_is_activated('pos_system'))
                                                    <div class="row">
                                                        <div class="col-md-10">
                                                            <label class="col-from-label">{{ ('POS') }}</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                                <input type="checkbox" name="permissions[]"
                                                                    class="sub-order form-control demo-sw" value="1"
                                                                    @php if(in_array('1', $permissions)) echo "checked"; @endphp>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Pending') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-order form-control demo-sw"
                                                                value="pending_orders"
                                                                @php if(in_array('pending_orders', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Processing') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-order form-control demo-sw"
                                                                value="processing_orders"
                                                                @php if(in_array('processing_orders', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Hold') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-order form-control demo-sw" value="hold_orders"
                                                                @php if(in_array('hold_orders', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Confirmed') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-order form-control demo-sw"
                                                                value="confirmed_orders"
                                                                @php if(in_array('confirmed_orders', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Packaging') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-order form-control demo-sw"
                                                                value="packaging_orders"
                                                                @php if(in_array('packaging_orders', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Picked Up') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-order form-control demo-sw"
                                                                value="picked_up_orders"
                                                                @php if(in_array('picked_up_orders', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('On The Way') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-order form-control demo-sw"
                                                                value="on_the_way_orders"
                                                                @php if(in_array('on_the_way_orders', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Delivered') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-order form-control demo-sw"
                                                                value="delivered_orders"
                                                                @php if(in_array('delivered_orders', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Returned') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-order form-control demo-sw"
                                                                value="returned_orders"
                                                                @php if(in_array('returned_orders', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Cancelled') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-order form-control demo-sw"
                                                                value="cancelled_orders"
                                                                @php if(in_array('cancelled_orders', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Inhouse orders') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-order form-control demo-sw" value="4"
                                                                @php if(in_array('4', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Seller Orders') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-order form-control demo-sw" value="5"
                                                                @php if(in_array('5', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Pick-up Point Order') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-order form-control demo-sw" value="6"
                                                                @php if(in_array('6', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- CRM --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">CRM:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('All') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="form-control demo-sw check-all-crm" value="manage_crm">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Customers') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-crm form-control demo-sw" value="crm_customers" @php if(in_array('crm_customers', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Modules --}}
                                @php
                                    $activeModules = Module::collections();
                                @endphp
                                @if ($activeModules->count() > 0)
                                    <div class="col-sm-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="mb-2">
                                                    <h6 class="text-muted fw-600">Modules:</h6>
                                                    <div class="row">
                                                        <div class="col-md-10">
                                                            <label class="col-from-label">{{ ('All') }}</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                                <input type="checkbox" name="permissions[]" class="form-control demo-sw check-all-modules" value="manage_modules">
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    @foreach ($activeModules as $module)
                                                        @php
                                                            $value = 'manage_module_' . strtolower(str_replace(' ', '_', $module->getName()));
                                                        @endphp
                                                        <div class="row">
                                                            <div class="col-md-10">
                                                                <label class="col-from-label">{{ $module->getName() }}</label>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="aiz-switch aiz-switch-success mb-0">
                                                                    <input type="checkbox" name="permissions[]" class="sub-module form-control demo-sw" value="{{ $value }}" @php if(in_array($value, $permissions)) echo "checked"; @endphp>
                                                                    <span class="slider round"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                {{-- Inventories --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Inventory:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('All') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox"
                                                                class="check-all-inventories form-control demo-sw">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Purchase Order') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-inventory form-control demo-sw" value="25"
                                                                @php if(in_array('25', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Stock Adjust') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-inventory form-control demo-sw"
                                                                value="stock_adjust"
                                                                @php if(in_array('stock_adjust', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Stock Report') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-inventory form-control demo-sw"
                                                                value="stock_report"
                                                                @php if(in_array('stock_report', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Return Suppliers') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-inventory form-control demo-sw" value="return_supplier" @if(in_array('return_supplier', $permissions)) checked @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Suppliers') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-inventory form-control demo-sw"
                                                                value="suppliers"
                                                                @php if(in_array('suppliers', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Create Order Return Request</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-inventory form-control demo-sw" value="create_order_return_request" @if(in_array('create_order_return_request', $permissions)) checked @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Manage Order Return Request</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-inventory form-control demo-sw" value="manage_order_return_request" @if(in_array('manage_order_return_request', $permissions)) checked @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Reports --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Reports:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('All') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="check-all-reports form-control demo-sw"
                                                                value="10"
                                                                @php if(in_array('10', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Purchase Report') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-report form-control demo-sw"
                                                                value="purchase_report"
                                                                @php if(in_array('purchase_report', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Top Selling Products') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-report form-control demo-sw"
                                                                value="top_selling_products_report"
                                                                @php if(in_array('top_selling_products_report', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Not Selling Products') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-report form-control demo-sw"
                                                                value="not_selling_products_report"
                                                                @php if(in_array('not_selling_products_report', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Expire Products') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-report form-control demo-sw"
                                                                value="expire_products_report"
                                                                @php if(in_array('expire_products_report', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">Sales Report</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-report form-control demo-sw"
                                                                value="sales_report"
                                                                @php if(in_array('sales_report', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Showroom Sales Report</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-report form-control demo-sw"
                                                                value="showroom_sales_report"
                                                                @php if(in_array('showroom_sales_report', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                @if(get_setting('enable_crm_module') == 1)
                                                    <div class="row">
                                                        <div class="col-md-10">
                                                            <label class="col-from-label">Sales Contribution Report</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                                <input type="checkbox" name="permissions[]" class="sub-report form-control demo-sw" value="sales_contribution_report"
                                                                @php if(in_array('sales_contribution_report', $permissions)) echo "checked"; @endphp>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">Seller Products Sales</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-report form-control demo-sw"
                                                                value="seller_products_sales_report"
                                                                @php if(in_array('seller_products_sales_report', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">Commision History</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-report form-control demo-sw"
                                                                value="comission_history_report"
                                                                @php if(in_array('comission_history_report', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">User Searches</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-report form-control demo-sw"
                                                                value="user_searches_report"
                                                                @php if(in_array('user_searches_report', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">Scanning Log Report</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-report form-control demo-sw"
                                                                value="scanning_log_report"
                                                                @php if(in_array('scanning_log_report', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Order Cancellation Report</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-report form-control demo-sw" value="order_cancellation_report" @if(in_array('order_cancellation_report', $permissions)) checked @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Expense Report</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-report form-control demo-sw" value="expense_report" @if(in_array('expense_report', $permissions)) checked @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Order Loss/Profit Report</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-report form-control demo-sw" value="order_loss_profit_report" @if(in_array('order_loss_profit_report', $permissions)) checked @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Product Visits Report</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-report form-control demo-sw" value="product_visits_report" @if(in_array('product_visits_report', $permissions)) checked @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Order Tracking Report</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-report form-control demo-sw" value="order_tracking_report" @if(in_array('order_tracking_report', $permissions)) checked @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">SMS Log Report</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-report form-control demo-sw" value="sms_log_report" @if(in_array('sms_log_report', $permissions)) checked @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Coupon Usage Report</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-report form-control demo-sw" value="coupon_usage_report" @if(in_array('coupon_usage_report', $permissions)) checked @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Notices --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Notice:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('All') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" class="check-all-notices form-control demo-sw">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Categories') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-notice form-control demo-sw" value="notice_categories" @php if(in_array('notice_categories', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Notices') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-notice form-control demo-sw" value="notices" @php if(in_array('notices', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Users --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Users:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('All') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox"
                                                                class="check-all-users form-control demo-sw">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Customers') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-user form-control demo-sw" value="8"
                                                                @php if(in_array('8', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Sellers') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-user form-control demo-sw" value="9"
                                                                @php if(in_array('9', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Merchants') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-user form-control demo-sw" value="merchants"
                                                                @php if(in_array('merchants', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Role & Permissions') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-user form-control demo-sw"
                                                                value="role_permissions"
                                                                @php if(in_array('role_permissions', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Staffs --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Staffs:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">All</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" class="check-all-staffs form-control demo-sw">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Create New Staff</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-staff form-control demo-sw" value="create_staff" @php if(in_array('create_staff', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Edit Staff Profile</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-staff form-control demo-sw" value="edit_staff" @php if(in_array('edit_staff', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">View Staff Profile</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-staff form-control demo-sw" value="view_staff" @php if(in_array('view_staff', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">View Staffs Report</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-staff form-control demo-sw" value="staffs_report" @php if(in_array('staffs_report', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Holidays --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Holidays:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">All</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" class="check-all-holidays form-control demo-sw">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">View Holidays</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-holidays form-control demo-sw" value="view_holidays" @php if(in_array('view_holidays', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Manage Holidays</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-holidays form-control demo-sw" value="manage_holidays" @php if(in_array('manage_holidays', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Applications --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Applications:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">All</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" class="check-all-applications form-control demo-sw">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">View Applications</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-applications form-control demo-sw" value="view_applications" @php if(in_array('view_applications', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Manage Applications</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-applications form-control demo-sw" value="manage_applications" @php if(in_array('manage_applications', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Attendances --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Attendances:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">All</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" class="check-all-attendances form-control demo-sw">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">View Attendances</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-attendances form-control demo-sw" value="view_attendances" @php if(in_array('view_attendances', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Edit Attendances</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-attendances form-control demo-sw" value="edit_attendances" @php if(in_array('edit_attendances', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Salary Sheets --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Salary Sheets:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">All</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" class="check-all-salary-sheets form-control demo-sw">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">View Salary Sheets</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-salary-sheets form-control demo-sw" value="view_salary_sheets" @php if(in_array('view_salary_sheets', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Edit Salary Sheets</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-salary-sheets form-control demo-sw" value="edit_salary_sheets" @php if(in_array('edit_salary_sheets', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Tickets --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Tickets:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('All') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" class="check-all-tickets form-control demo-sw">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Categories') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-ticket form-control demo-sw" value="ticket_categories" @php if(in_array('ticket_categories', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Support Tickets') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-ticket form-control demo-sw" value="support_tickets" @php if(in_array('support_tickets', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Settings --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Settings:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('All') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox"
                                                                class="check-all-settings form-control demo-sw">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Website Setup') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-setting form-control demo-sw" value="13"
                                                                @php if(in_array('13', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Setup & Configurations') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-setting form-control demo-sw" value="14"
                                                                @php if(in_array('14', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                @if (addon_is_activated('affiliate_system'))
                                                    <div class="row">
                                                        <div class="col-md-10">
                                                            <label
                                                                class="col-from-label">{{ ('Affiliate System') }}</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                                <input type="checkbox" name="permissions[]"
                                                                    class="sub-setting form-control demo-sw"
                                                                    value="15"
                                                                    @php if(in_array('15', $permissions)) echo "checked"; @endphp>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (addon_is_activated('offline_payment'))
                                                    <div class="row">
                                                        <div class="col-md-10">
                                                            <label
                                                                class="col-from-label">{{ ('Offline Payment System') }}</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                                <input type="checkbox" name="permissions[]"
                                                                    class="sub-setting form-control demo-sw"
                                                                    value="16"
                                                                    @php if(in_array('16', $permissions)) echo "checked"; @endphp>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (addon_is_activated('paytm'))
                                                    <div class="row">
                                                        <div class="col-md-10">
                                                            <label
                                                                class="col-from-label">{{ ('Paytm Payment Gateway') }}</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                                <input type="checkbox" name="permissions[]"
                                                                    class="sub-setting form-control demo-sw"
                                                                    value="17"
                                                                    @php if(in_array('17', $permissions)) echo "checked"; @endphp>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (addon_is_activated('club_point'))
                                                    <div class="row">
                                                        <div class="col-md-10">
                                                            <label
                                                                class="col-from-label">{{ ('Club Point System') }}</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                                <input type="checkbox" name="permissions[]"
                                                                    class="sub-setting form-control demo-sw"
                                                                    value="18"
                                                                    @php if(in_array('18', $permissions)) echo "checked"; @endphp>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (addon_is_activated('otp_system'))
                                                    <div class="row">
                                                        <div class="col-md-10">
                                                            <label
                                                                class="col-from-label">{{ ('OTP System') }}</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                                <input type="checkbox" name="permissions[]"
                                                                    class="sub-setting form-control demo-sw"
                                                                    value="19"
                                                                    @php if(in_array('19', $permissions)) echo "checked"; @endphp>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Others --}}
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <h6 class="text-muted fw-600">Others:</h6>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('All') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox"
                                                                class="check-all-others form-control demo-sw">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Support') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-other form-control demo-sw" value="12"
                                                                @php if(in_array('12', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Addon Manager') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-other form-control demo-sw" value="21"
                                                                @php if(in_array('21', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Uploaded Files') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-other form-control demo-sw" value="22"
                                                                @php if(in_array('22', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Blog System') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-other form-control demo-sw" value="23"
                                                                @php if(in_array('23', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('System') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-other form-control demo-sw" value="24"
                                                                @php if(in_array('24', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Accounting') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-other form-control demo-sw" value="26"
                                                                @php if(in_array('26', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                @if (addon_is_activated('refund_request'))
                                                    <div class="row">
                                                        <div class="col-md-10">
                                                            <label class="col-from-label">{{ ('Refunds') }}</label>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                                <input type="checkbox" name="permissions[]" class="sub-other form-control demo-sw" value="7" @php if(in_array('7', $permissions)) echo "checked"; @endphp>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label
                                                            class="col-from-label">{{ ('Rewrite URL') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-other form-control demo-sw" value="28"
                                                                @php if(in_array('28', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Manage Highlighted Items</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-other form-control demo-sw" value="highlighted_items" @if(in_array('highlighted_items', $permissions)) checked @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('FAQ') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="sub-other form-control demo-sw" value="29"
                                                                @php if(in_array('29', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Manage Service') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-other form-control demo-sw" value="manage_service" @php if(in_array('manage_service', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">{{ ('Manage Templates') }}</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-other form-control demo-sw" value="manage_templates" @php if(in_array('manage_templates', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="col-from-label">Manage Videos</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input type="checkbox" name="permissions[]" class="sub-other form-control demo-sw" value="manage_videos" @php if(in_array('manage_videos', $permissions)) echo "checked"; @endphp>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{ ('Save') }}</button>
                    </div>
                </div>
                </from>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            if($('.sub-order:checked').length == $('.sub-order').length) {
                $('.check-all-orders').prop('checked', true);
            } else {
                $('.check-all-orders').prop('checked', false);
            }

            if($('.sub-crm:checked').length == $('.sub-crm').length) {
                $('.check-all-crm').prop('checked', true);
            } else {
                $('.check-all-crm').prop('checked', false);
            }

            if($('.sub-module:checked').length == $('.sub-module').length) {
                $('.check-all-modules').prop('checked', true);
            } else {
                $('.check-all-modules').prop('checked', false);
            }

            if($('.sub-report:checked').length == $('.sub-report').length) {
                $('.check-all-reports').prop('checked', true);
            } else {
                $('.check-all-reports').prop('checked', false);
            }

            if($('.sub-user:checked').length == $('.sub-user').length) {
                $('.check-all-users').prop('checked', true);
            } else {
                $('.check-all-users').prop('checked', false);
            }

            if($('.sub-notice:checked').length == $('.sub-notice').length) {
                $('.check-all-notices').prop('checked', true);
            } else {
                $('.check-all-notices').prop('checked', false);
            }

            if($('.sub-ticket:checked').length == $('.sub-ticket').length) {
                $('.check-all-tickets').prop('checked', true);
            } else {
                $('.check-all-tickets').prop('checked', false);
            }

            if($('.sub-product:checked').length == $('.sub-product').length) {
                $('.check-all-products').prop('checked', true);
            } else {
                $('.check-all-products').prop('checked', false);
            }

            if($('.sub-inventory:checked').length == $('.sub-inventory').length) {
                $('.check-all-inventories').prop('checked', true);
            } else {
                $('.check-all-inventories').prop('checked', false);
            }

            if($('.sub-setting:checked').length == $('.sub-setting').length) {
                $('.check-all-settings').prop('checked', true);
            } else {
                $('.check-all-settings').prop('checked', false);
            }

            if($('.sub-other:checked').length == $('.sub-other').length) {
                $('.check-all-others').prop('checked', true);
            } else {
                $('.check-all-others').prop('checked', false);
            }


            // Orders Group
            $('.check-all-orders').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-order').prop('checked', true);
                } else {
                    $('.sub-order').prop('checked', false);
                }
            });
            $('.sub-order').on('change', function() {
                if ($('.sub-order:checked').length == $('.sub-order').length) {
                    $('.check-all-orders').prop('checked', true);
                } else {
                    $('.check-all-orders').prop('checked', false);
                }
            });

            // Crm Group
            $('.check-all-crm').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-crm').prop('checked', true);
                } else {
                    $('.sub-crm').prop('checked', false);
                }
            });
            $('.sub-crm').on('change', function() {
                if ($('.sub-crm:checked').length == $('.sub-crm').length) {
                    $('.check-all-crm').prop('checked', true);
                } else {
                    $('.check-all-crm').prop('checked', false);
                }
            });

            // Module Group
            $('.check-all-modules').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-module').prop('checked', true);
                } else {
                    $('.sub-module').prop('checked', false);
                }
            });
            $('.sub-module').on('change', function() {
                if ($('.sub-module:checked').length == $('.sub-module').length) {
                    $('.check-all-modules').prop('checked', true);
                } else {
                    $('.check-all-modules').prop('checked', false);
                }
            });

            // Reports Group
            $('.check-all-reports').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-report').prop('checked', true);
                } else {
                    $('.sub-report').prop('checked', false);
                }
            });
            $('.sub-report').on('change', function() {
                if ($('.sub-report:checked').length == $('.sub-report').length) {
                    $('.check-all-reports').prop('checked', true);
                } else {
                    $('.check-all-reports').prop('checked', false);
                }
            });

            // Notices Group
            $('.check-all-notices').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-notice').prop('checked', true);
                } else {
                    $('.sub-notice').prop('checked', false);
                }
            });
            $('.sub-notice').on('change', function() {
                if ($('.sub-notice:checked').length == $('.sub-notice').length) {
                    $('.check-all-notices').prop('checked', true);
                } else {
                    $('.check-all-notices').prop('checked', false);
                }
            });

            // Tickets Group
            $('.check-all-tickets').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-ticket').prop('checked', true);
                } else {
                    $('.sub-ticket').prop('checked', false);
                }
            });
            $('.sub-ticket').on('change', function() {
                if ($('.sub-ticket:checked').length == $('.sub-ticket').length) {
                    $('.check-all-tickets').prop('checked', true);
                } else {
                    $('.check-all-tickets').prop('checked', false);
                }
            });

            // Users Group
            $('.check-all-users').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-user').prop('checked', true);
                } else {
                    $('.sub-user').prop('checked', false);
                }
            });
            $('.sub-user').on('change', function() {
                if ($('.sub-user:checked').length == $('.sub-user').length) {
                    $('.check-all-users').prop('checked', true);
                } else {
                    $('.check-all-users').prop('checked', false);
                }
            });

            // Staffs Group
            $('.check-all-staffs').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-staff').prop('checked', true);
                } else {
                    $('.sub-staff').prop('checked', false);
                }
            });
            $('.sub-staff').on('change', function() {
                if ($('.sub-staff:checked').length == $('.sub-staff').length) {
                    $('.check-all-staffs').prop('checked', true);
                } else {
                    $('.check-all-staffs').prop('checked', false);
                }
            });

            // Holidays Group
            $('.check-all-holidays').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-holidays').prop('checked', true);
                } else {
                    $('.sub-holidays').prop('checked', false);
                }
            });
            $('.sub-holidays').on('change', function() {
                if ($('.sub-holidays:checked').length == $('.sub-holidays').length) {
                    $('.check-all-holidays').prop('checked', true);
                } else {
                    $('.check-all-holidays').prop('checked', false);
                }
            });

            // Application Group
            $('.check-all-applications').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-applications').prop('checked', true);
                } else {
                    $('.sub-applications').prop('checked', false);
                }
            });
            $('.sub-applications').on('change', function() {
                if ($('.sub-applications:checked').length == $('.sub-applications').length) {
                    $('.check-all-applications').prop('checked', true);
                } else {
                    $('.check-all-applications').prop('checked', false);
                }
            });

            // Attendance Group
            $('.check-all-attendances').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-attendances').prop('checked', true);
                } else {
                    $('.sub-attendances').prop('checked', false);
                }
            });
            $('.sub-attendances').on('change', function() {
                if ($('.sub-attendances:checked').length == $('.sub-attendances').length) {
                    $('.check-all-attendances').prop('checked', true);
                } else {
                    $('.check-all-attendances').prop('checked', false);
                }
            });

            // Salary Sheets Group
            $('.check-all-salary-sheets').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-salary-sheets').prop('checked', true);
                } else {
                    $('.sub-salary-sheets').prop('checked', false);
                }
            });
            $('.sub-salary-sheets').on('change', function() {
                if ($('.sub-salary-sheets:checked').length == $('.sub-salary-sheets').length) {
                    $('.check-all-salary-sheets').prop('checked', true);
                } else {
                    $('.check-all-salary-sheets').prop('checked', false);
                }
            });

            // Products Group
            $('.check-all-products').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-product').prop('checked', true);
                } else {
                    $('.sub-product').prop('checked', false);
                }
            });
            $('.sub-product').on('change', function() {
                if ($('.sub-product:checked').length == $('.sub-product').length) {
                    $('.check-all-products').prop('checked', true);
                } else {
                    $('.check-all-products').prop('checked', false);
                }
            });

            // Inventory Group
            $('.check-all-inventories').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-inventory').prop('checked', true);
                } else {
                    $('.sub-inventory').prop('checked', false);
                }
            });
            $('.sub-inventory').on('change', function() {
                if ($('.sub-inventory:checked').length == $('.sub-inventory').length) {
                    $('.check-all-inventories').prop('checked', true);
                } else {
                    $('.check-all-inventories').prop('checked', false);
                }
            });

            // Settings Group
            $('.check-all-settings').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-setting').prop('checked', true);
                } else {
                    $('.sub-setting').prop('checked', false);
                }
            });
            $('.sub-setting').on('change', function() {
                if ($('.sub-setting:checked').length == $('.sub-setting').length) {
                    $('.check-all-settings').prop('checked', true);
                } else {
                    $('.check-all-settings').prop('checked', false);
                }
            });

            // Others Group
            $('.check-all-others').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.sub-other').prop('checked', true);
                } else {
                    $('.sub-other').prop('checked', false);
                }
            });
            $('.sub-other').on('change', function() {
                if ($('.sub-other:checked').length == $('.sub-other').length) {
                    $('.check-all-others').prop('checked', true);
                } else {
                    $('.check-all-others').prop('checked', false);
                }
            });


            // Hover Style
            $('.aiz-switch').hover(
                function () {
                    // On hover in
                    $(this).closest('.row').find('.col-from-label').first().addClass('text-primary fw-600');
                },
                function () {
                    // On hover out
                    $(this).closest('.row').find('.col-from-label').first().removeClass('text-primary fw-600');
                }
            );
        });
    </script>
@endsection
