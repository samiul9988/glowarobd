@extends('backend.layouts.app')
@section('meta_title'){{ 'Create Bank' }}@stop
@section('content')

<div class="col-lg-7 mx-auto">
    <div class="card" x-data="app()" x-cloak>
        <div class="card-header">
            <h5 class="mb-0 h6">{{ ('Create Bank')}}</h5>
        </div>
        <form action="{{ route('banks.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group row gutters-5">
                    <div class="col-6">
                        <label for="bank_type" class="col-form-label">{{ ('Select Bank Type')}} :</label>
                        <select x-on:change="get_subheads()" id="bank_type" class="form-control aiz-selectpicker col-12 @error('type') is-invalid @enderror" name="type" x-model="type" required>
                            <option value="General Bank" class="text-capitalize">General Banking</option>
                            <option value="Mobile Bank" class="text-capitalize">Mobile Banking</option>
                        </select>
                        @error('type')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label for="bank_name" class="col-form-label">{{ ('Bank Name')}} :</label>
                        <input type="text" class="form-control @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name" placeholder="Bank name" x-model="bank_name" required>
                        @error('head')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    @error('parent_head')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group row gutters-5 mb-3">
                    <div class="col-6">
                        <label for="acc_name">Account holder name:</label>
                        <input type="text" class="form-control @error('acc_name') is-invalid @enderror" id="acc_name" name="acc_name" placeholder="Enter bank account holder name" x-model="acc_name" required>
                        @error('acc_name')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label for="acc_no">Account number:</label>
                        <input type="text" class="form-control @error('acc_no') is-invalid @enderror" id="acc_no" name="acc_no" placeholder="Enter bank account number" x-model="acc_no" required>
                        @error('acc_no')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="form-group row gutters-5 mb-3">
                    <div class="col-6">
                        <label for="address">Account holder address:</label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" placeholder="Enter address" x-model="address">
                        @error('address')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label for="contact_no">Account holder contact number:</label>
                        <input type="text" class="form-control @error('contact_no') is-invalid @enderror" id="contact_no" name="contact_no" placeholder="Enter account holder name" x-model="contact_no">
                        @error('contact_no')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="form-group row gutters-5 mb-3">
                    <div class="col-13">
                        <label for="opening_balance">Opening Balance:</label>
                        <input type="number" class="form-control @error('opening_balance') is-invalid @enderror" id="opening_balance" name="opening_balance" step="0.01" placeholder="Enter opening balance" x-model="opening_balance">
                        @error('opening_balance')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                </div>
            </div>
        </from>
        <script type="text/javascript">
            function app() {
                return {
                    type: null,
                    bank_name: null,
                    acc_name: null,
                    acc_no: null,
                    address: null,
                    contact_no: null,
                    opening_balance: null
                }
            }
        </script>
    </div>
</div>
@endsection
