@extends('backend.layouts.app')

@section('content')
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ 'Coupon Information Adding' }}</h5>
            </div>
            <div class="card-body">
                <form id="coupon-create-form" class="form-horizontal" action="{{ route('coupon.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row gutters-5">
                        <div class="form-group col-md-6">
                            <label for="coupon_type">{{ 'Coupon Type' }}</label>
                            <select name="coupon_type" id="coupon_type" class="form-control aiz-selectpicker"
                                onchange="coupon_form()" required>
                                <option value="">Select One</option>
                                <option value="product_base">For Products</option>
                                <option value="cart_base">For Total Orders</option>
                                <option value="shipping_charge">For Shipping Charge</option>
                            </select>
                            <span class="input-error text-danger fs-10" id="coupon_type_error"></span>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="coupon_code">{{ 'Coupon Usage Limit' }}</label>
                            <select class="form-control aiz-selectpicker" name="usage_limit">
                                <option value="single">{{ 'Single' }}</option>
                                <option value="multiple">{{ 'Multiple' }}</option>
                            </select>
                        </div>
                        @if (Auth::user()->user_type == 'admin')
                            <div class="form-group col-md-6">
                                <label for="coupon_for">{{ 'Coupon For' }}</label>
                                <select class="form-control aiz-selectpicker" name="coupon_for" id="coupon_for">
                                    <option value="" selected>{{ 'All' }}</option>
                                    <option value="crm">{{ 'CRM' }}</option>
                                    <option value="affiliates">{{ 'Affiliates' }}</option>
                                    <option value="customer_group">Customer Group</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6" id="assign_to_div" style="display: none;">
                                <label for="assign_to">{{ 'Assign To' }}</label>
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="assign_to"
                                    id="assign_to">
                                    <option value="">Loading...</option>
                                </select>
                                <span class="input-error text-danger fs-10" id="assign_to_error"></span>
                            </div>
                            <div class="form-group col-md-6" id="group_id_div" style="display: none;">
                                <label for="group_ids">{{ 'Select Group' }}</label>
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
                                    <textarea class="form-control" name="description" id="description" rows="3" placeholder="Describe coupon usage guidelines"></textarea>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-label text-muted font-weight-bold">Checklists</label>
                                    <div class="form-group row mb-2">
                                        <label class="col-7 col-from-label" for="force_apply">Force Apply Coupon</label>
                                        <div class="col-5">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="checkbox" name="force_apply" value="1" id="force_apply">
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-7 col-from-label" for="only_for_app">Only For App</label>
                                        <div class="col-5">
                                            <label class="aiz-switch aiz-switch-success mb-0">
                                                <input type="checkbox" name="only_for_app" value="1" id="only_for_app">
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
                                                <input type="checkbox" name="featured" value="1" id="featured">
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="coupon_form">
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{ 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        let cs_users = '';
        let affiliate_users = '';
        let group_users = '';

        function coupon_form() {
            let coupon_type = $('#coupon_type').val();

            if (coupon_type === 'product_base') {
                $('#coupon_form').html(`@include('partials.coupons.product_base_coupon')`)
            } else if (coupon_type === 'cart_base' || coupon_type === 'shipping_charge') {
                $('#coupon_form').html(`@include('partials.coupons.cart_base_coupon')`)
            } else {
                $('#coupon_form').html(``)
            }
            refresh();
            // $.post('{{ route('coupon.get_coupon_form') }}',{_token:'{{ csrf_token() }}', coupon_type:coupon_type}, function(data){
            //     $('#coupon_form').html(data);
            // });
        }

        $('#coupon_form').on('input', '.coupon_code', function() {
            var coupon_code = $(this).val();
            $(this).val(coupon_code.replace(/\s+/g, '').toUpperCase()); // Remove spaces and convert to uppercase
        });

        $('#coupon_for').on('change', async function() {
            var coupon_for = $(this).val();
            if (coupon_for) {
                $('#force_apply').prop('checked', true);
                if (coupon_for == 'customer_group') {
                    $('#assign_to_div').hide();
                    $('#group_id_div').show();
                    $('#group_ids').html('<option value="">{{ 'Loading...' }}</option>');
                    if (group_users.length > 0) {
                        $('#group_ids').html(group_users);
                    } else {
                        await $.get('{{ route('coupon.get_assignee') }}', {
                            type: coupon_for
                        }, function(data) {
                            if (data.success) {
                                $('#group_ids').html(data.options);
                                group_users = data.options;
                            } else {
                                $('#group_ids').html(
                                    '<option value="" disabled>{{ 'No groups found' }}</option>');
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
                            type: coupon_for
                        }, function(data) {
                            if (data.success) {
                                $('#assign_to').html(data.options);
                                if (coupon_for == 'crm') {
                                    cs_users = data.options;
                                } else if (coupon_for == 'affiliates') {
                                    affiliate_users = data.options;
                                }
                            } else {
                                $('#assign_to').html(
                                    '<option value="">{{ 'No users found' }}</option>');
                            }
                        });
                    }
                }
            } else {
                $('#force_apply').prop('checked', false);
                $('#assign_to').val('');
                $('#assign_to_div').hide();
                $('#group_id_div').hide();
            }
            AIZ.plugins.bootstrapSelect('refresh');
        });

        $('#coupon-create-form').on('submit', function(e) {
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
            refresh();
        });

        function refresh() {
            $('.aiz-selectpicker').selectpicker();
            $('.aiz-date-range').daterangepicker();
            AIZ.plugins.bootstrapSelect('refresh');
        }
    </script>
@endsection
