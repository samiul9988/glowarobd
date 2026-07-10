@extends('backend.layouts.app')
@php
    $coupon = $coupon ?? null;
    $groupIds = implode(',', $coupon->group_ids ?? []);
@endphp
@section('content')
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6">{{ 'Coupon Information Update' }}</h3>
            </div>
            <form id="coupon-edit-form" action="{{ route('coupon.update', $coupon->id) }}" method="POST">
                <input name="_method" type="hidden" value="PATCH">
                @csrf
                <div class="card-body">
                    <input type="hidden" name="id" value="{{ $coupon->id }}" id="id">
                    <div class="row gutters-5">
                        <div class="form-group col-md-6">
                            <label for="coupon_type">Coupon Type</label>
                            <select name="coupon_type" id="coupon_type" class="form-control aiz-selectpicker" required>
                                @if ($coupon->type == 'product_base')
                                    <option value="product_base" selected>For Products</option>
                                @elseif ($coupon->type == 'cart_base')
                                    <option value="cart_base" selected>For Total Orders</option>
                                @elseif ($coupon->type == 'shipping_charge')
                                    <option value="shipping_charge" selected>For Shipping Charge</option>
                                @endif
                            </select>
                            <span class="input-error text-danger fs-10" id="coupon_type_error"></span>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="coupon_code">Coupon Usage Limit</label>
                            <select class="form-control aiz-selectpicker" name="usage_limit">
                                <option value="single" @if ($coupon->usage_limit == 'single') selected @endif>Single
                                </option>
                                <option value="multiple" @if ($coupon->usage_limit == 'multiple') selected @endif>
                                    Multiple</option>
                            </select>
                        </div>
                        @if (Auth::user()->user_type == 'admin')
                            <div class="form-group col-md-6">
                                <label for="coupon_for">Coupon For</label>
                                <select class="form-control aiz-selectpicker" name="coupon_for" id="coupon_for">
                                    <option value="" selected>All</option>
                                    <option value="crm" @if (filled($coupon->assigned_to) && !$coupon->is_affiliate) selected @endif>
                                        CRM</option>
                                    <option value="affiliates" @if (filled($coupon->assigned_to) && $coupon->is_affiliate) selected @endif>
                                        Affiliates</option>
                                    <option value="customer_group" @if (! empty($groupIds)) selected @endif>
                                        Customer Group
                                    </option>
                                </select>
                                <span class="input-error text-danger fs-10" id="coupon_for_error"></span>
                            </div>
                            <div class="form-group col-md-6" id="assign_to_div" style="display: none;">
                                <label for="assign_to">Assign To</label>
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="assign_to"
                                    id="assign_to">
                                    <option value="">Loading...</option>
                                </select>
                                <span class="input-error text-danger fs-10" id="assign_to_error"></span>
                            </div>
                            <div class="form-group col-md-6" id="group_id_div" style="display: none;">
                                <label for="group_ids">Select Group</label>
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="group_ids[]"
                                    id="group_ids" multiple>
                                    <option value="">Loading...</option>
                                </select>
                                <span class="input-error text-danger fs-10" id="group_ids_error"></span>
                            </div>
                        @endif
                        <div class="col-12">
                            <div class="row gutters-5">
                                <div class="form-group col-md-6">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" name="description" id="description" rows="3" placeholder="Describe coupon usage guidelines">{{ $coupon->description }}</textarea>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-label text-muted font-weight-bold">Checklists</label>
                                    <div class="form-group row mb-2">
                                        <label class="col-7 col-from-label" for="force_apply">Force Apply Coupon</label>
                                        <div class="col-5">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="checkbox" name="force_apply" value="1" id="force_apply"
                                                    @if ($coupon->force_apply) checked @endif>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-7 col-from-label" for="only_for_app">Only For App</label>
                                        <div class="col-5">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="checkbox" name="only_for_app" value="1" id="only_for_app"
                                                    @if ($coupon->only_for_app) checked @endif>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-7 col-from-label" for="featured">
                                            Featured
                                            @include('components.tooltip', [
                                                'title' => 'Featured coupons are visible to customers as available coupons.',
                                                'position' => 'top'
                                            ])
                                        </label>
                                        <div class="col-5">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="checkbox" name="featured" value="1" id="featured"
                                                    @if ($coupon->featured) checked @endif>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="coupon_form">
                        @if ($coupon->type == 'product_base')
                            <div id="product_base_coupon">
                                @include('partials.coupons.product_base_coupon_edit', [
                                    'coupon' => $coupon,
                                ])
                            </div>
                        @else
                            <div id="cart_base_coupon">
                                @include('partials.coupons.cart_base_coupon_edit', ['coupon' => $coupon])
                            </div>
                        @endif
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{ 'Save' }}</button>
                    </div>
            </form>

        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        let cs_users = '';
        let affiliate_users = '';
        let group_users = '';

        $('#coupon_form').on('input', '.coupon_code', function() {
            var coupon_code = $(this).val();
            $(this).val(coupon_code.replace(/\s+/g, '').toUpperCase()); // Remove spaces and convert to uppercase
        });

        $('#coupon_for').on('change', async function() {
            var coupon_for = $(this).val();
            var selected = $('#assign_to').val() || '{{ $coupon->assigned_to }}';
            var selectedGroup = $('#group_ids').val();
            if (!selectedGroup || selectedGroup.length === 0) {
                selectedGroup = '{{ $groupIds }}';
            }
            if (coupon_for) {
                if (coupon_for == 'customer_group') {
                    $('#assign_to_div').hide();
                    $('#group_id_div').show();
                    $('#group_ids').html('<option value="">{{ 'Loading...' }}</option>');
                    if (group_users.length > 0) {
                        $('#group_ids').html(group_users);
                    } else {
                        await $.get('{{ route('coupon.get_assignee') }}', {
                            type: coupon_for,
                            selected: selectedGroup
                        }, function(data) {
                            if (data.success) {
                                $('#group_ids').html(data.options);
                                group_users = data.options;
                            } else {
                                $('#group_ids').html('<option value="" disabled>{{ 'No groups found' }}</option>');
                            }
                        });
                    }
                } else {
                    $('#group_id_div').hide();
                    $('#assign_to_div').show();
                    $('#assign_to').html('<option value="">{{ 'Loading...' }}</option>');
                    if (coupon_for == 'crm' && cs_users.length > 0) {
                        $('#assign_to').html(cs_users);
                    } else if (coupon_for == 'affiliates' && affiliate_users.length > 0) {
                        $('#assign_to').html(affiliate_users);
                    } else {
                        await $.get('{{ route('coupon.get_assignee') }}', {
                            type: coupon_for,
                            selected: selected
                        }, function(data) {
                            if (data.success) {
                                $('#assign_to').html(data.options);
                                if (coupon_for == 'crm') {
                                    cs_users = data.options;
                                } else if (coupon_for == 'affiliates') {
                                    affiliate_users = data.options;
                                }
                            } else {
                                $('#assign_to').html('<option value="">{{ 'No users found' }}</option>');
                            }
                        });
                    }
                }

                AIZ.plugins.bootstrapSelect('refresh');
            } else {
                $('#assign_to_div').hide();
                $('#group_id_div').hide();
            }
        });

        $('#coupon-edit-form').on('submit', function(e) {
            let form = this;

            let valid = true;
            let coupon_type = $('#coupon_type').val();
            if (!coupon_type) {
                $('#coupon_type_error').text('The coupon type field is required.');
                valid = false;
            } else {
                $('#coupon_type_error').text('');
            }
            let coupon_for = $('#coupon_for').val();

            if (coupon_for == 'crm' || coupon_for == 'affiliates') {
                let assign_to = $('#assign_to').val();
                if (!assign_to) {
                    $('#assign_to_error').text('Please select an assignee.');
                    valid = false;
                } else {
                    $('#assign_to_error').text('');
                }
            } else if (coupon_for == 'customer_group') {
                let group_ids = $('#group_ids').val();
                if (!group_ids) {
                    $('#group_ids_error').text('Please select a group.');
                    valid = false;
                } else {
                    $('#group_ids_error').text('');
                }
            }

            if (!valid) {
                e.preventDefault();
            }
        });

        $(document).ready(function() {
            $('#coupon_for').trigger('change');
            $('.aiz-selectpicker').selectpicker();
            $('.aiz-date-range').daterangepicker();
            AIZ.plugins.bootstrapSelect('refresh');
        });
    </script>
@endsection
