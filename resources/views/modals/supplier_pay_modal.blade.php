<!-- Pay Modal -->
<div id="supplier-pay-modal" class="modal fade" data-backdrop="static" x-data="supplierPayModal()" x-cloak>
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ ('Proceed Payment')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body text-center" x-data="payRows" x-on:payrows-load.window="payRows = $event.detail.items; purId = $event.detail.purId; payableId = $event.detail.payableId">
                <template x-for="(row, index) in payRows" :key="index">
                    <div class="form-group row gutters-5">
                        <div :class="row.showBankType ? 'col-3' : 'col-6'">
                            <label class="float-left col-form-label">{{ ('Select Payment Method')}} :</label>
                            <select x-on:change="toggleShowBankType($event.target, index)" class="form-control aiz-selectpicker col-12" x-model="row.method" name="method[]">
                                <option value="" class="text-capitalize">Select Payment Method</option>
                                <option value="bank" class="text-capitalize">Bank</option>
                                <option value="cash" class="text-capitalize">Cash In Hand({{ $cashbalance ?? 0 }})</option>
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
                                <input type="number" class="form-control col-11 pay_amount" x-model="row.amount" name="amount[]" placeholder="Enter amount" />
                                <button x-show="index > 0" type="button" class="ml-auto border-0 justify-content-center items-center bg-danger" style="width: 30px; height: 30px; border-radius: 100%;" @click="removeRow(index)"><span style="line-height: 1.2;">x</span></button>
                            </div>
                        </div>
                    </div>
                </template>
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
                <input type="hidden" class="form-control col-11 due_amount" name="due_amount" />
                <input type="hidden" class="form-control col-11 purId" name="purId" />
                <button type="button" class="btn btn-sm btn-primary" @click="addRow()">Add Another Method</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link mt-2" data-dismiss="modal">{{ ('Cancel')}}</button>
                <button type="button" @click="savePayments($event.target)" x-bind:disabled="buttonDisabled" id="supplier-payment-link" class="btn btn-primary mt-2">{{ ('Save & Proceed')}}</button>
            </div>
        </div>
    </div>
</div><!-- /.modal -->