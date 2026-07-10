@extends('backend.layouts.app')
@section('meta_title'){{ 'Purchase Invoice - '.@$purchase_order->po_number }}@stop
@section('content')
@php
    $banks = \App\Models\ACCBank::all();
    $jsBanks = $banks->toJson();
    $beforedebts = \App\Models\AccTransaction::where('head', 'like', '%Cash In Hand%')->sum('debit');
    $beforecreds = \App\Models\AccTransaction::where('head', 'like', '%Cash In Hand%')->sum('credit');
    $cashbalance = $beforedebts - $beforecreds;
@endphp
<div>
    <div class="card">
        <div class="card-header">
            <h1 class="h2 fs-16 mb-0">{{ ('Purchase Order Details') }}</h1>
        </div>
        <div class="card-body bg-white" id="printArea">
            <div class="row gutters-5 mt-2">
                <div class="col text-center text-md-left">
                    {{-- <h5>Supplier</h5> --}}
                    <table class="float-left">
                        <tbody>
                            <tr class="text-left">
                                <td>
                                    <img
                                        src="{{ uploaded_asset($purchase_order->supplier->logo) }}"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/logoipsum.png') }}';"
                                        alt="{{ (@$purchase_order->supplier->name)}}"
                                        class="text-left max-w-100 h-50px mb-2"
                                        style="object-fit: contain;"
                                    />
                                </td>
                            </tr>
                            <tr class="text-left">
                                <td>
                                    <address>
                                        <strong class="text-main">{{@$purchase_order->supplier->name}}</strong><br>
                                        <a href="tel:">{{@$purchase_order->supplier->contact_number}}</a>
                                        <br>
                                        <p>{{@$purchase_order->supplier->address}}</p>
                                    </address>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col ml-auto">
                    <table class="float-right">
                        <tbody>
                            <tr>
                                <td class="text-main text-bold px-2">Purchase Order #</td>
                                <td class="text-right text-info text-bold mx-2">{{@$purchase_order->po_number}}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold px-2">Date:</td>
                                <td class="text-right text-bold mx-2">{{date('m-d-Y', @$purchase_order->purchase_date)}}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold px-2">Total Amount:</td>
                                <td class="text-right text-bold mx-2">{{single_price($purchase_order->grand_total)}}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold px-2">Total Payment:</td>
                                <td class="text-right text-bold mx-2">{{single_price($purchase_order->total_payment)}}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold px-2">Total Due:</td>
                                <td class="text-right text-bold mx-2">{{single_price($purchase_order->total_due)}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <hr class="new-section-sm bord-no">

            <div class="row">
                <div class="col-lg-12 table-responsive">
                    <table class="table table-bordered invoice-summary">
                        <thead>
                            <tr class="bg-trans-dark">
                                <th data-breakpoints="lg" class="min-col">#</th>
                                <th width="10%">{{ ('Photo')}}</th>
                                <th class="text-uppercase">{{ ('Description')}}</th>
                                <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{ ('Qty')}}</th>
                                @if(get_setting('enable_product_expire_date') == 1)
                                <th data-breakpoints="lg" class="min-col text-right text-uppercase">{{ ('Exp. Date')}}</th>
                                @endif
                                <th data-breakpoints="lg" class="min-col text-right text-uppercase">{{ ('Unit Price')}}</th>
                                <th data-breakpoints="lg" class="min-col text-right text-uppercase">{{ ('Total Price')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $grand_total = 0;
                            @endphp

                            @foreach ($purchase_order->purchaseOrderDetails as $key => $purchase_item)

                                @php
                                    $grand_total = $grand_total + ($purchase_item->price * $purchase_item->qty);
                                @endphp

                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td><img src="{{ uploaded_asset($purchase_item->product?->thumbnail_img)}}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';" alt="Image" class="size-50px img-fit"></td>
                                    <td>
                                        @if ($purchase_item->product)
                                            {{$purchase_item->product?->name }}
                                            @if(@$purchase_item->product_stock->variant != null)
                                                ({{@$purchase_item->product_stock->variant }})
                                            @endif
                                        @else
                                            <del>Product not found</del>
                                        @endif
                                    </td>
                                    <td class="text-center" >{{ $purchase_item->qty }}</td>
                                    @if(get_setting('enable_product_expire_date') == 1)
                                    <td class="text-right">{{ $purchase_item->expire_date ?? 'N/A' }}</td>
                                    @endif
                                    <td class="text-right">{{single_price($purchase_item->price) }}</td>
                                    <td class="text-right">{{single_price($purchase_item->price*$purchase_item->qty) }}</td>
                                </tr>

                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="text-right">
                                <th colspan="{{ get_setting('enable_product_expire_date') == 1 ? '6' : '5' }}">{{ ('Grand Total')}} :</th>
                                <th class="text-right">{{single_price($grand_total)}}</th>
                            </tr>
                            <tr class="text-right">
                                <th colspan="{{ get_setting('enable_product_expire_date') == 1 ? '6' : '5' }}">{{ ('Total Paid')}} :</th>
                                <th class="text-right">{{single_price($purchase_order->total_payment)}}</th>
                            </tr>
                            <tr class="text-right">
                                <th colspan="{{ get_setting('enable_product_expire_date') == 1 ? '6' : '5' }}">{{ ('Total Due')}} :</th>
                                <th class="text-right">{{single_price($grand_total - $purchase_order->total_payment)}}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <button class="btn btn-sm btn-secondary" onclick="printArea()"><i class="las la-print"></i> Print</button>
        @if($purchase_order->total_payment < $purchase_order->grand_total)
        <a class="" href="javascript:void(0);" onclick="paynow('{{$purchase_order->supplier->id}}', '{{$purchase_order->po_number}}', '{{$purchase_order->grand_total - $purchase_order->total_payment}}')" title="{{ ('Record Payment') }}">
            <i class="las la-plus-circle"></i> {{ ('Record Payment') }}
        </a>
        @endif
        <a href="javascript:void(0);" onclick="showPayHistory()"><i class="las la-eye"></i> View Payment Histroy</a>
        @if(!empty($purchase_order->attachement))
        <a href="{{ uploaded_asset($purchase_order->attachement) }}" target="_blank"><i class="las la-paperclip"></i> View Attachment</a>
        @endif
    </div>

    @include('modals.payment_history_modal', ['purchaseorder' => $purchase_order, 'purchasehistories' => $purchase_order->payments])

    <script>
        function printArea() {
            var printContents = document.getElementById("printArea").innerHTML;
            var originalContents = document.body.innerHTML;

            // Replace the current document content with the content of the printable div
            document.body.innerHTML = printContents;

            // Print the content
            window.print();

            // Restore the original document content
            document.body.innerHTML = originalContents;
        }

        function showPayHistory(){
            $("#payment-history-modal").modal("show");
        }
    </script>
</div>
@include('modals.supplier_pay_modal')
@endsection

@section('script')
    <script type="text/javascript">
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
