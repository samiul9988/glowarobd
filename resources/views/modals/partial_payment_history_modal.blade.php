<!-- Pay Modal -->
<div id="partial-payment-history-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ ('Payment Histroy')}} of {{ $order->code }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body text-center">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th data-breakpoints="md">{{ ('Invoice No.')}}</th>
                            <th data-breakpoints="md">{{ ('Date') }}</th>
                            <th data-breakpoints="md">{{ ('Starting Due') }}</th>
                            <th data-breakpoints="md">{{ ('Paid Amount') }}</th>
                            <th data-breakpoints="md">{{ ('Remaining Due') }}</th>
                            <th data-breakpoints="md">{{ ('Method')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $startAmount = get_order_grand_total($order);
                            $endAmount = get_order_grand_total($order);
                        @endphp
                        @foreach ($payments as $key => $payment)
                        @php
                            $pdata = json_decode($payment->payment_details);
                            $binfo = $pdata->payment_method ?? 'N/A';
                            if(!empty($pdata->bank_info) && $pdata->payment_method !== 'cash'){
                                // $binfo = $pdata->payment_method.':'.$pdata->bank_info->bank_name.'-'.$pdata->bank_info->acc_no;
                            }
                        @endphp
                        @if($loop->first)
                        <tr>
                            <td> <a href="#" title="{{ ('See Invoice') }}">{{ $payment->invoice_no }}</a> </td>
                            <td>{{ $payment->date }}</td>
                            <td>{{ get_order_grand_total($order) }}</td>
                            <td>{{ $payment->amount }}</td>
                            <td>{{ get_order_grand_total($order) - $payment->amount }}</td>
                            <td><span class="text-capitalize">{{$binfo}}</span></td>
                        </tr>
                        @else
                        <tr>
                            <td> <a href="#" title="{{ ('See Invoice') }}">{{ $payment->invoice_no }}</a> </td>
                            <td>{{ $payment->date }}</td>
                            <td>{{ $endAmount }}</td>
                            <td>{{ $payment->amount }}</td>
                            <td>{{ $endAmount - $payment->amount }} </td>
                            <td><span class="text-capitalize">{{$binfo}}</td>
                        </tr>
                        @endif
                        @php
                            if($loop->first){
                                $endAmount = $startAmount - $payment->amount;
                            }else{
                                $endAmount = $endAmount - $payment->amount;
                            }
                        @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td class="fw-600 text-right">Total</td>
                            <td class="fw-600">{{ $payments->sum('amount'); }}</td>
                            {{-- <td class="fw-600">{{ $endAmount }}</td> --}}
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-primary mt-2" data-dismiss="modal">{{ ('Close')}}</button>
            </div>
        </div>
    </div>
</div><!-- /.modal -->
