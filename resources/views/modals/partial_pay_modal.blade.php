@php
    $banks = Cache::remember('banks', now()->addHours(6), function () {
        return \App\Models\ACCBank::all();
    });
    $jsBanks = $banks->toJson();
@endphp
<!-- Pay Modal -->
<div id="partial-pay-modal" class="modal fade" data-backdrop="static"
     x-data="partialPayModal()"
     x-on:set-amount.window="payRows.amount = $event.detail"
     x-cloak>
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ ('Proceed Payment')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body text-center" x-data="payRows">
                <div class="form-group row gutters-5">
                    <div :class="payRows.showBankType ? 'col-4' : 'col-6'">
                        <label class="float-left col-form-label">{{ ('Select Payment Method')}} <span class="text-danger">*</span>:</label>
                        <select x-on:change="toggleShowBankType($event.target)" class="form-control aiz-selectpicker col-12" x-model="payRows.method" name="method">
                            <option value="" class="text-capitalize">Select Payment Method</option>
                            <option value="bank" class="text-capitalize">Bank</option>
                            @if (@$from === 'pos')
                                <option value="card">Card</option>
                            @endif
                            <option value="bKash" class="text-capitalize">bKash</option>
                            <option value="cash" class="text-capitalize">Cash In Hand</option>
                        </select>
                    </div>
                    <div class="col-4" x-show="payRows.showBankType">
                        <label class="float-left col-form-label">{{ ('Select Bank')}} <span class="text-danger">*</span>:</label>
                        <select class="form-control col-12" x-model="payRows.bank" name="bank">
                            <option value="" class="text-capitalize">Select Bank Account</option>
                            <template x-for="bank in payRows.filteredBanks">
                                <option :value="bank.id" x-text="bank.bank_name" class="text-capitalize"></option>
                            </template>
                        </select>
                    </div>
                    <div :class="payRows.showBankType ? 'col-4' : 'col-6'">
                        <label class="float-left col-form-label text-left w-100">{{ ('Amount')}} <span class="text-danger">*</span>:</label>
                        <div class="d-flex gutters-5 align-items-center">
                            <input type="number" class="form-control col-11 pay_amount" x-model="payRows.amount" name="amount" placeholder="Enter amount" />
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="float-left col-form-label text-left w-100">{{ ('Note')}}</label>
                        <textarea rows="3" class="form-control" x-model="payRows.note" name="note" placeholder="Enter note"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link mt-2" data-dismiss="modal">{{ ('Cancel')}}</button>
                <button type="button" @click="savePayments($event.target)" :disabled="buttonDisabled" id="partial-payment-link" class="btn btn-primary mt-2">{{ ('Save & Proceed')}}</button>
            </div>
        </div>
    </div>
</div>

<script>
    function partialPayModal(){
        const banks = JSON.parse('{!! addslashes($jsBanks) !!}');
        return {
            hasBalance: false,
            showModal: false,
            banks: banks,
            buttonDisabled: false,
            payRows: {
                method: '',
                bank_type: 'General Bank',
                bank: '',
                note: '',
                amount: 0,
                showBankType: false,
                showBank: false,
                filteredBanks: banks.filter((bank) => bank.type == 'General Bank')
            },
            toggleShowBankType(el) {
                let method = el.value;
                this.payRows.showBankType = (method == 'bank') ? true : false;
            },
            async savePayments(el) {
                let paymentMethod = this.payRows.method;
                let bankType = this.payRows.bank_type;
                let bank = this.payRows.bank;
                let amount = parseFloat(this.payRows.amount || 0);
                if(paymentMethod == '') {
                    AIZ.plugins.notify('danger', "{{ ('Please select a payment method') }}");
                    return;
                }
                if(paymentMethod == 'bank' && bank == '') {
                    AIZ.plugins.notify('danger', "{{ ('Please select a bank') }}");
                    return;
                }
                if(amount < 10){
                    AIZ.plugins.notify('danger', "{{ ('Minimum amount is 10') }}");
                    return;
                }
                if(amount > calculateTotal()){
                    AIZ.plugins.notify('danger', "{{ ('Request payment amount exceed total amount') }}");
                    return;
                }
                let orderID = '{{ $order->id ?? 0 }}';
                let url = '';
                let payload = {};
                if(orderID == 0){
                    url = "{{ route('pos.partial-pay') }}";
                    payload = {
                        _token: '{{ csrf_token() }}',
                        method: paymentMethod,
                        bank_type: paymentMethod == 'bank' ? bankType : null,
                        bank: bank,
                        amount: amount,
                        note: this.payRows.note,
                    };
                }else{
                    url = "{{ route('invoice.partial-pay') }}";
                    payload = {
                        _token: '{{ csrf_token() }}',
                        method: paymentMethod,
                        bank_type: paymentMethod == 'bank' ? bankType : null,
                        bank: bank,
                        amount: amount,
                        orderId: orderID,
                        note: this.payRows.note,
                    };
                }
                const button = this;
                button.buttonDisabled = true;
                await $.ajax({
                    url: url,
                    type: 'POST',
                    data: payload,
                    success: function (data) {
                        button.buttonDisabled = false;
                        $("#partial-pay-modal").modal("hide");

                        if(data.success){
                            console.log('Button disabled:', button.buttonDisabled);
                            AIZ.plugins.notify('success', data.message);
                            if(orderID == 0){
                                $('#order-confirmation').html(data.view);
                                // $('#cart-details').html(data.cart_view);
                            }else{
                                window.location.reload();
                            }
                        }else{
                            AIZ.plugins.notify('danger', data.message);
                        }
                    },
                    error: function (data) {
                        button.buttonDisabled = false;
                        AIZ.plugins.notify('danger', "{{ ('Something went wrong') }}");
                    }
                });
            },
            getBankName(id){
                let findbank = this.banks.filter((bank) => bank.id == parseInt(id));
                return findbank.bank_name;
            }
        }
    }

    function getPayments(){
        return partialPayModal().payRows;
    }
</script>
