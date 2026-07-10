@extends('backend.layouts.app')

@section('content')
@php
    $banks = \App\Models\ACCBank::get();
    $jsBanks = $banks->toJson();
    $beforedebts = \App\Models\AccTransaction::where('head', 'like', '%Cash In Hand%')->sum('debit');
    $beforecreds = \App\Models\AccTransaction::where('head', 'like', '%Cash In Hand%')->sum('credit');
    $cashbalance = $beforedebts - $beforecreds;

    $admins = Cache::remember('admins_for_purchase_order', now()->addDay(), function () {
        return \App\Models\User::where('user_type', 'admin')->pluck('id')->toArray();
    });

    $suppliers = Cache::remember('suppliers_purchase_order', now()->addDay(), function () use ($admins) {
        return App\Models\Supplier::whereIn('user_id', $admins)->get();
    });
@endphp
<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('All Purchase Order') }}</h5>
            </div>

            <div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="user_id" name="user_id" data-live-search="true" onchange="sort_products()">
                    <option value="">{{ ('All Suppliers') }}</option>
                    @foreach ($suppliers as $key => $supplier)
                        <option value="{{ $supplier->id }}"  @if ($supplier->id == $supplier_id) selected @endif>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ ('Purchase Order Number') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th data-breakpoints="md">{{ ('Date') }}</th>
                        <th>{{ ('Purchase Order Number') }}</th>
                        <th data-breakpoints="md">{{ ('Seller') }}</th>
                        <th data-breakpoints="md">{{ ('Supplier') }}</th>
                        <th data-breakpoints="md">{{ ('Num. of Products') }}</th>
                        <th data-breakpoints="md">{{ ('Total Qty') }}</th>
                        <th data-breakpoints="md">{{ ('Amount') }}</th>
                        <th data-breakpoints="md">{{ ('Status') }}</th>
                        {{-- <th data-breakpoints="md">{{ ('Payment Method')}}</th>
                        <th data-breakpoints="md">{{ ('Payment Status') }}</th> --}}

                        <th class="text-right" width="15%">{{ ('options')}}</th>
                    </tr>
                </thead>
                <tbody x-data="supplierPayModal()">
                    @foreach ($purchaseorders as $key => $purchaseorder)
                    <tr>
                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{$purchaseorder->id}}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td>{{ date('d-m-Y', $purchaseorder->purchase_date )}}</td>
                        <td>
                            <a href="{{route('purchaseorder.show', encrypt($purchaseorder->id))}}" title="{{ ('See Invoice') }}">{{ $purchaseorder->po_number }}</a>
                        </td>
                        <td>
                            {{@$purchaseorder->sellername->name}}
                        </td>
                        <td>
                            {{ @$purchaseorder->supplier->name }}<br>
                            {{ @$purchaseorder->supplier->contact_number }}
                        </td>
                        <td>
                            {{ count($purchaseorder->purchaseOrderDetails) }}
                        </td>
                        <td>
                            {{ $purchaseorder->purchaseOrderDetails->sum('qty') }}
                        </td>

                        <td>
                            <strong>{{ single_price($purchaseorder->grand_total) }}</strong> <br />
                            @if($purchaseorder->total_due > 0)
                            <span class="text-sm text-secondary">Due: {{single_price($purchaseorder->grand_total - $purchaseorder->total_payment)}}</span>
                            @endif
                        </td>

                        <td>
                            @if($purchaseorder->total_payment <= 0)
                                <span class="w-100 badge badge-pill badge-secondary">Unpaid</span>
                            @elseif($purchaseorder->total_payment > 0 && $purchaseorder->total_payment < $purchaseorder->grand_total)
                                <span class="w-100 badge badge-pill badge-warning">Partial</span>
                            @elseif($purchaseorder->total_payment >= $purchaseorder->grand_total)
                                <span class="w-100 badge badge-pill badge-success">Paid</span>
                            @else
                                <span class="w-100 badge badge-pill badge-secondary">Unpaid</span>
                            @endif
                        </td>

                        <td class="@if($purchaseorder->total_payment < $purchaseorder->grand_total) d-flex justify-content-between @else text-right @endif">
                            @if($purchaseorder->total_payment < $purchaseorder->grand_total)
                            <a class="" href="javascript:void(0);" @click="paynow('{{$purchaseorder->supplier->id}}', '{{$purchaseorder->po_number}}', '{{$purchaseorder->grand_total - $purchaseorder->total_payment}}')" title="{{ ('Record Payment') }}">
                                <i class="las la-plus-circle"></i> {{ ('Record Payment') }}
                            </a>
                            @endif
                            @if(get_setting('enable_product_expire_date') == 1 && $purchaseorder->items_count > 0)
                                <a class="btn btn-soft-success btn-icon btn-circle btn-sm mr-2" href="{{route('purchaseorder.print_barcode', encrypt($purchaseorder->id))}}" title="{{ ('Print Barcode') }}">
                                    <i class="las la-print"></i>
                                </a>
                            @endif
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{route('purchaseorder.show', encrypt($purchaseorder->id))}}" title="{{ ('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                            {{-- <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('purchaseorder.edit', ['id'=>$purchaseorder->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ ('Edit') }}">
                                <i class="las la-edit"></i>
                            </a> --}}
                            {{-- <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ route('invoice.download', $purchaseorder->id) }}" title="{{ ('Download Invoice') }}" target="_blank">
                                <i class="las la-download"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('orders.destroy', $purchaseorder->id)}}" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a> --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $purchaseorders->appends(request()->input())->links() }}
            </div>

        </div>
    </form>
</div>

@include('modals.supplier_pay_modal')

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });

       function change_status() {
           var data = new FormData($('#sort_orders')[0]);
           data.append('status', $('#update_delivery_status').val());
           $.ajax({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               url: "{{route('bulk-order-status')}}",
               type: 'POST',
               data: data,
               cache: false,
               contentType: false,
               processData: false,
               success: function (response) {
                   if(response == 1) {
                       location.reload();
                   }
               }
           });
       }

        function bulk_delete() {
            var data = new FormData($('#sort_orders')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-order-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        location.reload();
                    }
                }
            });
        }

        function paynow(sid, pid, dueamount) {
            $("#supplier-pay-modal").modal("show");
            $("#supplier-payment-link").attr("data-id", pid);
            $("#supplier-payment-link").attr("data-due", dueamount);
            $(".pay_amount:first").val(dueamount);
            $(".due_amount:first").val(dueamount);
            $(".purId:first").val(pid);
            let event = new CustomEvent("payrows-load", {
                detail: {
                    purId: pid,
                    payableId: sid,
                    items: [{
                        method: '',
                        bank_type: '',
                        bank: '',
                        amount: parseFloat(dueamount),
                        showBankType: false,
                        showBank: false,
                        filteredBanks: JSON.parse('{!! addslashes($jsBanks) !!}')
                    }]
                }
            });
            window.dispatchEvent(event);
        }

        function supplierPayModal(){
            return {
                purId: '',
                payableId: 0,
                hasBalance: false,
                showModal: false,
                banks: JSON.parse('{!! addslashes($jsBanks) !!}'),
                buttonDisabled: false,
                payRows: [{
                    method: '',
                    bank_type: '',
                    bank: '',
                    amount: 0,
                    showBankType: false,
                    showBank: false,
                    filteredBanks: this.banks
                }],
                paytotal: 0,
                duetotal: 0,
                addRow(amount) {
                    this.payRows.push({
                        method: '',
                        bank_type: '',
                        bank: '',
                        amount: amount ? amount : 0,
                        showBankType: false,
                        showBank: false,
                        filteredBanks: this.banks
                    });
                },
                toggleShowBankType(el, index) {
                    let method = el.value;
                    this.payRows[index].showBankType = (method == 'bank') ? true : false;
                },
                toggleShowBank(el, index) {
                    let type = el.value;
                    this.payRows[index].showBank = this.payRows[index].showBankType;
                    if (type == 'General Bank') {
                        this.payRows[index].filteredBanks = this.banks.filter((bank) => bank.type == 'General Bank');
                    } else if (type == 'Mobile Bank') {
                        this.payRows[index].filteredBanks = this.banks.filter((bank) => bank.type == 'Mobile Bank');
                    } else {
                        this.payRows[index].filteredBanks = this.banks;
                    }
                },

                removeRow(index) {
                    if (this.payRows.length > 1) {
                        this.payRows.splice(index, 1);
                    } else {
                        return;
                    }
                },

                async savePayments(el) {
                    this.buttonDisabled = true;
                    // const sum = this.payRows.reduce((sum, row) => sum + parseFloat(row.amount || 0), 0);
                    await setTimeout(() => {
                        this.buttonDisabled = false;
                        this.purId = $(".purId:first").val();
                        this.paytotal = this.getPaymentTotal();
                        this.duetotal = this.getDueTotal();

                        if (this.paytotal < 0 && this.purId == '') {
                            AIZ.plugins.notify('warning', '{{ ("Minimum 1 payemnt need to be add.") }}');
                            $("#supplier-pay-modal").modal("hide");
                            return;
                        }else{
                            // Property to check (e.g., 'method' in this case)
                            let propertyToCheck = 'method';

                            // Check if any object has an empty string as the value of the specified property
                            let hasEmptyStringValue = this.payRows.some(obj => obj[propertyToCheck] === '');

                            if(hasEmptyStringValue){
                                AIZ.plugins.notify('warning', '{{ ("Payment method(s). need to be added") }}');
                                return;
                            }else{
                                $.ajax({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    url: "{{route('accounts.payments.save')}}",
                                    type: 'POST',
                                    data: {
                                        payableType: "App\\Models\\Supplier",
                                        payableId: this.payableId,
                                        refId: this.purId,
                                        payments: this.payRows,
                                        total_pay: this.paytotal,
                                        attachement: $('#vattachement').val()
                                    },
                                    success: function(data, textStatus, jqXHR) {
                                        $("#supplier-pay-modal").modal("hide");
                                        AIZ.plugins.notify('success', 'Payment has been created');
                                        location.reload();
                                    },
                                    error: function(data){
                                        $("#supplier-pay-modal").modal("hide");
                                        AIZ.plugins.notify('warning', data.data.message);
                                    }
                                });
                            }
                        }

                        $("#supplier-pay-modal").modal("hide");
                    }, [1000]);
                },

                getBankName(id){
                    let findbank = this.banks.filter((bank) => bank.id == parseInt(id));
                    return findbank.bank_name;
                },

                getPaymentTotal(){
                    return this.payRows.reduce((sum, row) => sum + parseFloat(row.amount || 0), 0);
                },

                getDueTotal(){
                    let total = parseFloat($(".due_amount").val() - this.getPaymentTotal());
                    if(total < 0){
                        this.hasBalance = true;
                        return 0;
                    }else{
                        this.hasBalance = false;
                        return total;
                    }
                }
            }
        }
    </script>
@endsection
