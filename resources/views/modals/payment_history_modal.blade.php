<!-- Pay Modal -->
<div id="payment-history-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ ('Payment Histroy')}} of {{ $purchaseorder->po_number }}</h4>
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
                            $startAmount = $purchaseorder->grand_total;
                            $endAmount = $purchaseorder->grand_total;
                        @endphp
                        @foreach ($purchasehistories as $key => $history)
                        @php 
                            $pdata = json_decode($history->payment_details);
                            $binfo = $pdata->payment_method;
                            if(!empty($pdata->bank_info) && $pdata->payment_method !== 'cash'){
                                $binfo = $pdata->payment_method.':'.$pdata->bank_info->bank_name.'-'.$pdata->bank_info->acc_no;
                            }
                        @endphp
                        @if($loop->first)
                        <tr>
                            <td> <a href="#" title="{{ ('See Invoice') }}">{{ $history->invoice_no }}</a> </td>
                            <td>{{ $history->date }}</td>
                            <td>{{ $purchaseorder->grand_total }}</td>
                            <td>{{ $history->amount }}</td>
                            <td>{{ $purchaseorder->grand_total - $history->amount }}</td>
                            <td><span class="text-capitalize">{{$binfo}}</span></td>
                        </tr>
                        @else
                        <tr>
                            <td> <a href="#" title="{{ ('See Invoice') }}">{{ $history->invoice_no }}</a> </td>
                            <td>{{ $history->date }}</td>
                            <td>{{ $endAmount }}</td>
                            <td>{{ $history->amount }}</td>
                            <td>{{ $endAmount - $history->amount }} </td>
                            <td><span class="text-capitalize">{{$binfo}}</td>
                        </tr>
                        @endif
                        @php
                            if($loop->first){
                                $endAmount = $startAmount - $history->amount;
                            }else{
                                $endAmount = $endAmount - $history->amount;
                            }
                        @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td class="fw-600 text-right">Total</td>
                            <td class="fw-600">{{ $purchasehistories->sum('amount'); }}</td>
                            <td class="fw-600">{{ $endAmount }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link mt-2" data-dismiss="modal">{{ ('Close')}}</button>
            </div>
        </div>
    </div>
</div><!-- /.modal -->