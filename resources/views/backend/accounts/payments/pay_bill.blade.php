@extends('backend.layouts.app')
@section('meta_title'){{ 'Bill Payments' }}@stop
@section('content')
@php
    $banks = \App\Models\ACCBank::get();
    $jsBanks = $banks->toJson();
    $sid = request('sid', 0);
    $beforedebts = \App\Models\AccTransaction::where('head', 'like', '%Cash In Hand%')->sum('debit');
    $beforecreds = \App\Models\AccTransaction::where('head', 'like', '%Cash In Hand%')->sum('credit');
    $cashbalance = $beforedebts - $beforecreds;
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
    <h5 class="mb-0 h6">{{ ('Create New Payment')}}</h5>
</div>
<div x-data="supplierPay" x-init="$refs.dateInput.value = payment_date">
    <form class="form form-horizontal mar-top" action="#" method="POST" enctype="multipart/form-data" id="choice_form">
        @csrf
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Supplier & Date')}}</h5>
            </div>
            <div class="card-body">
                <div class="row gutters-5">
                    <div class="col-5">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Supplier')}}</label>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" x-model="supplier_id" @change="getUnpaidBills()" name="supplier_id" id="supplier_id" data-live-search="true" required>
                                    <option value="">Select supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" @if($sid == $supplier->id) selected @endif>{{ $supplier->name }} ({{ $supplier->contact_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 control-label" for="start_date">{{ ('Date')}}</label>
                            <div class="col-sm-8">
                            <input type="date" class="form-control" x-model="payment_date" name="payment_date" placeholder="{{ ('Select Date')}}" autocomplete="off" x-ref="dateInput">
                            </div>
                        </div>
                        <div class="form-group row">
						    <label class="col-sm-3 control-label" for="attachement">{{ ('Attachement')}}</label>
                            <div class="col-sm-8">
                                <div class="input-group" data-toggle="aizuploader">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="attachement" id="vattachement" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-7" x-show="showPayOption">
                        <h6>Payment Method</h6>
                        <hr class="mb-0 mt-1" />
                        <div x-data="payRows">
                            <template x-for="(row, index) in payRows" :key="index">
                                <div class="form-group row gutters-5">
                                    <div :class="row.showBankType ? 'col-3' : 'col-6'">
                                        <label class="float-left col-form-label">{{ ('Select Payment Method')}} :</label>
                                        <select x-on:change="toggleShowBankType($event.target, index)" class="form-control aiz-selectpicker col-12" x-model="row.method" name="method[]">
                                            <option value="" class="text-capitalize">Select Payment Method</option>
                                            <option value="bank" class="text-capitalize">Bank</option>
                                            <option value="cash" class="text-capitalize">Cash In Hand ({{ $cashbalance ?? 0 }})</option>
                                        </select>
                                    </div>
                                    <div class="col-3" x-show="row.showBankType">
                                        <label class="float-left col-form-label">{{ ('Select Bank Type')}} :</label>
                                        <select @change="toggleShowBank($event.target, index)" class="form-control aiz-selectpicker col-12" x-model="row.bank_type" name="bank_type[]">
                                            <option value="" class="text-capitalize">Select Bank Type</option>
                                            <option value="General Bank" class="text-capitalize">General Bank</option>
                                            <option value="Mobile Bank" class="text-capitalize">Mobile Bank</option>
                                        </select>
                                    </div>
                                    <div class="col-3" x-show="row.showBankType">
                                        <label class="float-left col-form-label">{{ ('Select Bank')}} :</label>
                                        <select class="form-control col-12" x-model="row.bank" name="bank[]">
                                            <option value="" class="text-capitalize">Select Bank Account</option>
                                            <template x-for="bank in row.filteredBanks">
                                                <option :value="bank.id" x-text="bank.bank_name + ' (Balance: ' + bank.balance + ')'" class="text-capitalize"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div :class="row.showBankType ? 'col-3' : 'col-6'">
                                        <label class="float-left col-form-label text-left w-100">{{ ('Amount')}} :</label>
                                        <div class="d-flex gutters-5 align-items-center">
                                            <input type="number" class="form-control col-11 pay_amount" x-model="row.amount" @keyup="distributeAmount()" name="amount[]" placeholder="Enter amount" />
                                            <button x-show="index > 0" type="button" class="ml-auto border-0 justify-content-center items-center bg-danger" style="width: 30px; height: 30px; border-radius: 100%;" @click="removeRow(index)"><span style="line-height: 1.2;">x</span></button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            {{-- <button type="button" class="btn btn-sm btn-primary" @click="addRow()">Add Another Method</button> --}}
                        </div>
                        <span x-show="remainings < 0" class="text-danger">Your paying more than due amount to this supplier</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 h6">{{ ('Unpaid Purchases')}}</h5>
                <div>Total Due: <strong x-text="getTotalDueAmount()"></strong></div>
            </div>
            <div class="card-body position-relative">
                <div class="loading-overlay2" x-show="loading">
                    <span class="text-white" style="font-size: 50px;"><i class="las la-spinner"></i></span>
                </div>
                <span>Remaing due after pay will be: <strong x-text="remainings"></strong> </span>
                <table class="table table-bordered table-striped mobile_no_border" style="margin-bottom: 5px;margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Purchase Order Number</th>
                            <th>Purchase Amount</th>
                            <th>Due Amount</th>
                            <th>Payment Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(bill, index) in unpaidBills" :key="index" x-show="!showNullBills">
                            <tr>
                                <td x-text="new Date(bill.purchase_date * 1000).toISOString().slice(0, 10);"></td>
                                <td><a :href="'{{route('purchaseorder.show', '')}}'+ '/' + bill.id" x-text="bill.po_number"></a></td>
                                <td x-text="bill.grand_total"></td>
                                <td x-text="bill.total_due"></td>
                                <td class="text-lext">
                                    <input type="number" @keyup="setPaymentTotal()" :value="0" x-model="bill.total_payment" />
                                </td>
                            </tr>
                        </template>
                        <tr x-show="showNullBills">
                            <td colspan="5" class="text-center py-4">
                                <h4 x-text="showNullMSG"></h4>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-right">Total Payment:</th>
                            <th class="text-left" colspan="2">
                                <input type="number" :value="getPaymentTotal()" x-model="total_amount" name="total_amount" readonly />
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="col-12">
            <div class="btn-toolbar float-right mb-3" role="toolbar" aria-label="Toolbar with button groups">
                <div class="btn-group" role="group" aria-label="Second group">
                    <button type="button" @click="savePayments($event.target)" x-bind:disabled="buttonDisabled" class="btn btn-success">{{ ('Save Payment') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('script')

<script type="text/javascript">
    function supplierPay(){
        return {
            showPayOption: (parseInt('{{ $sid }}') !== 0) ? true : false,
            showNullBills: true,
            showNullMSG: 'Please select a supplier to see unpaid bills',
            supplier_id: parseInt('{{ $sid }}'),
            payment_date: '{{ now()->toDateString() }}',
            banks: JSON.parse('{!! addslashes($jsBanks) !!}'),
            buttonDisabled: false,
            total_amount: 0,
            remainings: 0,
            intotal_due: 0,
            loading: false,
            payRows: [{
                method: '',
                bank_type: '',
                bank: '',
                amount: 0,
                showBankType: false,
                showBank: false,
                filteredBanks: JSON.parse('{!! addslashes($jsBanks) !!}')
            }],
            unpaidBills: [],
            init() {
                if(this.supplier_id){
                    this.getUnpaidBills();
                }
            },
            addRow() {
                this.payRows.push({
                    method: '',
                    bank_type: '',
                    bank: '',
                    amount: 0,
                    showBankType: false,
                    showBank: false,
                    filteredBanks: this.banks
                });

                this.distributeAmount();
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
                    this.distributeAmount();
                } else {
                    return;
                }
            },
            async savePayments(el) {

                if (this.supplier_id == 0 && this.supplier_id == '') {
                    AIZ.plugins.notify('warning', '{{ ("Please select a supplier") }}');
                    return;
                }
                this.loading = true;
                this.buttonDisabled = true;

                let paytotal = this.getPaymentTotal();

                if (paytotal <= 0 || this.supplier_id == 0) {
                    AIZ.plugins.notify('warning', '{{ ("Payment amount can not be 0") }}');
                    this.loading = false;
                    this.buttonDisabled = false;
                    return;
                }else{
                    // Property to check (e.g., 'method' in this case)
                    let propertyToCheck = 'method';

                    // Check if any object has an empty string as the value of the specified property
                    let hasEmptyStringValue = this.payRows.some(obj => obj[propertyToCheck] === '');

                    if(hasEmptyStringValue){
                        AIZ.plugins.notify('warning', '{{ ("Payment method(s). need to be added") }}');
                        this.loading = false;
                        this.buttonDisabled = false;
                        return;
                    }else{
                        // console.log('this.unpaidBills:', this.unpaidBills);
                        // console.log('this.payRows:', this.payRows);
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: "{{route('accounts.payments.bulk.save')}}",
                            type: 'POST',
                            data: {
                                payment_date: this.payment_date,
                                payableId: this.supplier_id,
                                payments: this.payRows,
                                total_pay: paytotal,
                                bills: this.unpaidBills,
                                attachement: $('#vattachement').val()
                            },
                            success: function(data, textStatus, jqXHR) {
                                AIZ.plugins.notify('success', 'Payment has been created');
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
            getPaymentTotal(){
                return this.payRows.reduce((sum, row) => sum + parseFloat(row.amount || 0), 0);
            },
            setPaymentTotal(){
                let total = 0;
                this.unpaidBills.forEach(bill => {
                    total += parseFloat(bill.total_payment);
                });
                this.payRows[0].amount = total;
            },
            getUnpaidBills(){
                if(this.supplier_id == 0 && this.supplier_id == ''){
                    AIZ.plugins.notify('warning', '{{ ("Pleaase select a supplier") }}');
                }else{
                    this.showPayOption = true;
                    this.loading = true;
                    this.intotal_due = 0;
                    this.remainings = 0;
                    this.total_amount = 0;
                    this.payRows[0].amount = 0;

                    fetch(`{{route('accounts.payments.get_unpaid_bills')}}?id=${this.supplier_id}`, {
                        method: 'GET',
                        headers: { 'Content-Type': 'application/json' }
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        if(data && data.data.length > 0){
                            this.showNullBills = false;
                            this.showNullMSG = '';
                            this.unpaidBills = data.data.map(obj => {
                                return { ...obj, total_payment: 0, refId: obj.id};
                            });
                        }else{
                            this.showNullBills = true;
                            this.showNullMSG = 'No unpaid bills found for selected supplier';
                            this.unpaidBills = [];
                        }
                    })
                    .finally(() => {
                        this.loading = false;
                    })
                }
            },
            getCurrentDate() {
                const today = new Date();
                const year = today.getFullYear();
                const month = (today.getMonth() + 1).toString().padStart(2, '0');
                const day = today.getDate().toString().padStart(2, '0');
                return `${year}-${month}-${day}`;
            },
            distributeAmount() {

                let totalAmount =  this.getPaymentTotal();
                let remainingAmount = totalAmount;

                this.unpaidBills.forEach(candidate => {
                    if (remainingAmount >= candidate.total_due) {
                        candidate.total_payment = candidate.total_due;
                        remainingAmount -= candidate.total_due;
                    } else {
                        candidate.total_payment = remainingAmount;
                        remainingAmount = 0;
                    }
                });
            },
            getTotalDueAmount(){
                let totalDUe = 0;
                this.unpaidBills.forEach(candidate => {
                    totalDUe += parseFloat(candidate.total_due);
                });

                this.remainings = totalDUe - this.getPaymentTotal();
                this.total_due = totalDUe;

                return totalDUe.toFixed(2);
            }
        }
    }

</script>

@endsection
