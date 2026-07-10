@extends('backend.layouts.app')

@section('content')

<div class="col-lg-7 mx-auto">
    <div class="card" x-data="app()" x-cloak>
        <div class="card-header">
            <h5 class="mb-0 h6">{{ ('Update Bank')}}</h5>
        </div>
        <form action="{{ route('banks.update', $bank->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="card-body">
                <div class="form-group row gutters-5">
                    <div class="col-6">
                        <label for="bank_type" class="col-form-label">{{ ('Select Bank Type')}} :</label>
                        <select x-on:change="get_subheads()" id="bank_type" class="form-control aiz-selectpicker col-12 @error('type') is-invalid @enderror" name="type" required>
                            <option value="General Bank" class="text-capitalize" @if($bank->type == 'General Bank') selected @endif>General Banking</option>
                            <option value="Mobile Bank" class="text-capitalize" @if($bank->type == 'Mobile Bank') selected @endif>Mobile Banking</option>
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
                        <input type="text" class="form-control @error('acc_no') is-invalid @enderror" id="acc_no" name="acc_no" placeholder="Enter bank account number" x-model="acc_no">
                        @error('acc_no')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="form-group row gutters-5 mb-3">
                    <div class="col-6">
                        <label for="address">Address:</label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" placeholder="Enter address" x-model="address">
                        @error('address')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label for="contact_no">Account holder contact number:</label>
                        <input type="text" class="form-control @error('contact_no') is-invalid @enderror" id="contact_no" name="contact_no" placeholder="Enter account holder contact number" x-model="contact_no">
                        @error('contact_no')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-sm btn-primary">{{ ('Update')}}</button>
                </div>
            </div>
        </from>
        <script type="text/javascript">
            function app() {
                return {
                    bank_name: '{{ $bank->bank_name }}',
                    acc_name: '{{ $bank->acc_name }}',
                    acc_no: '{{ $bank->acc_no }}',
                    address: '{{ $bank->address }}',
                    contact_no: '{{ $bank->contact_no }}'
                }
            }
        </script>
    </div>
</div>
@endsection
