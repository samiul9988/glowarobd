@extends('backend.layouts.app')
@section('meta_title'){{ 'Voucher Entry' }}@stop
@section('content')
@php
    $heads = \App\Models\AccHead::select('id', 'head', 'reference_id', 'reference_type')->withCount(['transactions as balance' => function ($query) {
        $query->select(\DB::raw('coalesce(sum(debit), 0) - coalesce(sum(credit), 0)'));
    }])->whereNotNull('sub_head')->groupBy('id')->get();
    $jsHeads = $heads->toJson();
@endphp
<style>
.loading-overlay2 {
  display: flex;
  background: rgba(0, 0, 0, 0.6);
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  top: 0;
  z-index: 9998;
  align-items: center;
  justify-content: center;
}
select option:disabled{
    color: lightgrey;
}
</style>
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{ ('New Voucher Entry')}}</h5>
</div>
<div x-data="voucherEntry" x-init="$refs.dateInput.value = entry_date">
    <form class="form form-horizontal mar-top" action="#" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Type & Date')}} <small>(<small class="text-capitalize text-sm" x-text="voucher_type"></small>)</small></h5>
            </div>
            <div class="card-body">
                <div class="row gutters-5">
                    <div class="col-12">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ ('Voucher Type')}}</label>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" x-model="voucher_type" data-live-search="true" required>
                                    <option value="general" selected>General voucher</option>
                                    <option value="payment">Payment voucher</option>
                                    <option value="receipt" disabled>Receipt voucher</option>
                                    <option value="journal" disabled>Journal Voucher</option>
                                    <option value="contra">Contra voucher</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 control-label" for="start_date">{{ ('Date')}}</label>
                            <div class="col-sm-8">
                            <input type="date" class="form-control" x-model="entry_date" name="entry_date" placeholder="{{ ('Select Date')}}" autocomplete="off" x-ref="dateInput">
                            </div>
                        </div>

                        <table class="table table-bordered table-striped mobile_no_border" style="margin-bottom: 5px;margin-top: 15px;">
                            <thead>
                                <tr>
                                    <th>Debit/Credit</th>
                                    <th>Particulars</th>
                                    <th>Naration</th>
                                    <th>Debit Amount</th>
                                    <th>Credit Amount</th>
                                    {{--<th>Actions</th>--}}
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(transaction, index) in transactions" :key="index">
                                    <tr>
                                        <td>
                                            <select class="form-control aiz-selectpicker" x-model="transaction.type" required>
                                                <option value="debit" x-bind:disabled="voucher_type == 'payment' && alreadyHaveDebitTransaction().length > 0">Debit</option>
                                                <option value="credit">Credit</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control aiz-selectpicker" x-model="transaction.particular" data-live-search="true" required>
                                                <option value="">Select Particular/Ledger</option>
                                                <template x-for="(head, i) in heads" :key="i">
                                                    <option :value="head.id" x-text="head.head + ' (Balance: ' + head.balance + ')'"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" x-model="transaction.naration" placeholder="{{ ('Single naration')}}" />
                                        </td>
                                        <td>
                                            <input x-show="transaction.type === 'debit'" type="number" class="form-control" x-model="transaction.debit" placeholder="{{ ('Enter debit amount')}}" />
                                        </td>
                                        <td>
                                            <input x-show="transaction.type === 'credit'" type="number" class="form-control" x-model="transaction.credit" placeholder="{{ ('Enter credit amount')}}" />
                                        </td>
                                        {{--<td>
                                            <a href="javascript:void(0)" class="btn btn-soft-info btn-icon btn-circle btn-sm" @click="addRow(transaction.type)" title="{{ ('Add') }}">
                                                <i class="las la-plus"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" @click="removeRow(index)" title="{{ ('Delete') }}">
                                                <i class="las la-trash"></i>
                                            </a>
                                        </td>--}}
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3"></th>
                                    <th>Total Debit: <input type="number" x-model="total_debit" :value="getDebitTotal()" class="border-0 fw-600" readonly /></th>
                                    <th>Total Credit: <input type="number" x-model="total_credit" :value="getCreditTotal()" class="border-0 fw-600" readonly /></th>
                                </tr>
                            </tfoot>
                        </table>
                        <br />
                        <div class="form-group">
						    <label for="attachement">{{ ('Attachement')}}</label>
                            <div class="input-group" data-toggle="aizuploader">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" x-model="attachement" name="attachement" id="vattachement" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-12 control-label">{{ ('Note')}}</label>
                            <div class="col-12">
                                <textarea class="form-control" x-model="note" name="note" placeholder="{{ ('Note')}}" autocomplete="off"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="btn-toolbar float-right mb-3" role="toolbar" aria-label="Toolbar with button groups">
                <div class="btn-group" role="group" aria-label="Second group">
                    <button type="button" @click="savePayments($event.target)" x-bind:disabled="buttonDisabled" class="btn btn-success">{{ ('Save Voucher') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('script')

<script type="text/javascript">

    function voucherEntry(){
        // console.log(JSON.parse('{!! addslashes($jsHeads) !!}'));
        return {
            voucher_type: 'general',
            entry_date: '{{ now()->toDateString() }}',
            heads: JSON.parse('{!! addslashes($jsHeads) !!}'),
            buttonDisabled: false,
            total_debit: 0,
            total_credit: 0,
            loading: false,
            attachement: null,
            transactions: [{
                type: 'debit',
                particular: '',
                naration: '',
                debit: 0,
                credit: 0,
            },{
                type: 'credit',
                particular: '',
                naration: '',
                debit: 0,
                credit: 0,
            }],
            note: '',
            addRow(type) {
                if(this.voucher_type == 'payment' && type == 'debit'){
                    AIZ.plugins.notify('warning', '{{ ("Only One Debit Transaction Can Be Created For Payment Voucher Type") }}');
                    return;
                }
                this.transactions.push({
                    type: type,
                    particular: '',
                    naration: '',
                    debit: 0,
                    credit: 0,
                });
            },
            removeRow(index) {
                if (this.transactions.length > 1) {
                    this.transactions.splice(index, 1);
                } else {
                    return;
                }
            },
            async savePayments(el) {

                this.loading = true;
                this.buttonDisabled = true;

                if (this.getDebitTotal() !== this.getCreditTotal()) {
                    AIZ.plugins.notify('warning', '{{ ("Debit & Credit Should Be Equal") }}');
                    this.loading = false;
                    this.buttonDisabled = false;
                    return;
                }else{
                    if (this.getDebitTotal() == 0 || this.getCreditTotal() == 0) {
                        AIZ.plugins.notify('warning', '{{ ("Debit or Credit should not be 0") }}');
                        this.loading = false;
                        this.buttonDisabled = false;
                        return;
                    }
                    // Property to check (e.g., 'particular' in this case)
                    let propertyToCheck = 'particular';

                    // Check if any object has an empty string as the value of the specified property
                    let hasEmptyStringValue = this.transactions.some(obj => obj[propertyToCheck] === '');
                    if(hasEmptyStringValue){
                        AIZ.plugins.notify('warning', '{{ ("Particular(s) need to be selected") }}');
                        this.loading = false;
                        this.buttonDisabled = false;
                        return;
                    }else{
                        // console.log('this.transactions:', this.transactions);
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: "{{route('accounts.voucher.save')}}",
                            type: 'POST',
                            data: {
                                voucher_type: this.voucher_type,
                                entry_date: this.entry_date,
                                transactions: this.transactions,
                                note: this.note,
                                attachement: $('#vattachement').val()
                            },
                            success: function(data, textStatus, jqXHR) {
                                AIZ.plugins.notify('success', 'Voucher has been created');
                                location.reload();
                            },
                            error: function(data){
                                AIZ.plugins.notify('warning', data.data.message);
                                this.loading = false;
                                this.buttonDisabled = false;
                            }
                        });
                    }
                }
            },
            getDebitTotal(){
                return this.transactions.filter((item) => item.type == 'debit').reduce((sum, row) => sum + parseFloat(row.debit || 0), 0);
            },
            getCreditTotal(){
                return this.transactions.filter((item) => item.type == 'credit').reduce((sum, row) => sum + parseFloat(row.credit || 0), 0);
            },
            alreadyHaveDebitTransaction(){
                let trans = this.transactions.filter((item) => item.type == 'debit');
                return trans;
            }
        }
    }

</script>

@endsection
