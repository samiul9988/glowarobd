@extends('backend.layouts.app')
@section('meta_title'){{ 'Voucher Details - '. ($voucher->first()?->vno ?? '') }}@stop
@section('content')
<div>
    <div class="card">
        <div class="card-header">
            <h1 class="h2 fs-16 mb-0">{{ ('Voucher Details') }}</h1>
        </div>
        <div class="card-body bg-white" id="printArea">
            <div class="row gutters-5 mt-2">
                <div class="col text-center text-md-left">
                    <table class="float-left">
                        <tbody>
                            <tr class="text-left">
                                <td>
                                    <img
                                        src="{{ uploaded_asset(get_setting('system_logo_white')) }}"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/logoipsum.png') }}';"
                                        alt="{{get_setting('site_name')}}"
                                        class="border text-left max-w-100 h-50px mb-3"
                                        style="object-fit: contain; border-radius: 25px;"
                                    />
                                </td>
                            </tr>
                            <tr class="text-left">
                                <td>
                                    <address>
                                        <strong class="text-main">{{ get_setting('site_name') }}</strong><br>
                                        Contact: <a href="tel:{{ get_setting('contact_phone') }}">{{ get_setting('contact_phone') }}</a>
                                        <br>
                                        <p>{{get_setting('contact_address')}}</p>
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
                                <td class="text-main text-bold px-2">Voucher Entry #</td>
                                <td class="text-right text-info text-bold mx-2">{{ $voucher->first()?->vno ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold px-2">Date:</td>
                                <td class="text-right text-bold mx-2">{{ date('d-m-Y', strtotime($voucher->first()?->date ?? '')) }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold px-2">Voucher Type:</td>
                                <td class="text-capitalize text-right text-bold mx-2">{{ $voucher->first()?->voucher_type ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold px-2">Issued By:</td>
                                <td class="text-right text-bold mx-2">{{ $voucher->first()?->user?->name ?? '' }}</td>
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
                                <th class="text-uppercase">Debit/Credit</th>
                                <th data-breakpoints="lg" class="min-col text-center text-uppercase">Particular</th>
                                <th data-breakpoints="lg" class="min-col text-right text-uppercase">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($voucher as $key => $v)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{ $v->entry_type }}</td>
                                    <td>
                                        @if(empty($v->particular->head))
                                            @php
                                                $head = \App\Models\AccHead::where('id', $v->particular_id)->where('reference_type',$v->particular_type)->first();
                                            @endphp
                                            {{ @$head->head }}
                                        @else
                                            {{ @$v->particular->head }}
                                        @endif
                                    </td>
                                    <td class="text-right">@if($v->entry_type == "credit") {{single_price($v->credit)}} @else {{ single_price($v->debit) }} @endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                        {{--<tfoot>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Total</strong></td>
                                <td class="text-right">{{ $voucher->sum('credit') + $voucher->sum('debit') }}</td>
                            </tr>
                        </tfoot>--}}
                    </table>
                    @if(!empty($voucher->first()?->note))
                        <div><strong>Note:</strong> <span>{{$voucher->first()?->note ?? ''}} </span></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <button class="btn btn-sm btn-secondary" onclick="printArea()"><i class="las la-print"></i> Print</button>
        @if(!empty($purchase_order->attachement))
        <a href="{{ uploaded_asset($purchase_order->attachement) }}" target="_blank"><i class="las la-paperclip"></i> View Attachment</a>
        @endif
    </div>

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
@endsection
