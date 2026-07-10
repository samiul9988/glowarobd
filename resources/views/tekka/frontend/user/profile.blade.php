@extends(config('app.theme').'frontend.layouts.user_panel')

@section('meta')
<x-seo />
@endsection

@section('panel_content')
    <!-- <div class="aiz-titlebar mt-2 mb-4">
      <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ ('Manage Profile') }}</h1>
        </div>
      </div>
    </div> -->
    <div class="user-profile row bg-white py-2 rounded-sm align-items-center m-0 mb-3 px-2">
        <div class=" p-0 col-12 col-md-6 py-2 py-md-0">
            <p class="m-0 fw-500 fs-22 text-capitalize  text-lg-left text-start px-2">
                <span class="fw-700 " style="color:#FA7E16">Welcome, </span> {{ Auth::user()->name }}
            </p>
        </div>
        <div class="col-6 p-0 align-items-center justify-content-center justify-content-md-end pr-1 pr-md-4 d-none d-md-flex">
                <span class="avatar avatar-md pr-2 pr-md-0">
                    @if (Auth::user()->avatar_original != null)
                        <img class="show-user-avatar" src="{{ uploaded_asset(Auth::user()->avatar_original) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                    @else
                        <img id="user-avatar-default" src="{{ static_asset('assets/img/avatar-place.png') }}" class="image rounded-circle" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                    @endif

                </span>
                @php

                    @$user_group = $currentlyAuthenticatedUser->customeringroup;

                @endphp
                <div>
                    <h4 class="h5 fs-16 fw-500 text-capitalize mb-1">
                        {{ Auth::user()->name }}
                    </h4>
                    @if(Auth::user()->phone != null)
                        <div class="text-truncate opacity-60 fs-14">{{ Auth::user()->phone }}</div>
                    @else
                        <div class="text-truncate opacity-60 fs-14">{{ Auth::user()->email }}</div>
                    @endif
                </div>
            </div>
    </div>
    <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <!-- Basic Info-->
        <div class="card manage-profile  border-0 shadow-none">
            <!-- <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Basic Info')}}</h5>
            </div> -->
            <div class="card-body">
                <div class="form-group row mx-0">
                    <div class="file-preview-img box sm">
                       <div class="profile-box">
                        @if (Auth::user()->avatar_original != null)
                            <img class="show-user-avatar" src="{{ uploaded_asset(Auth::user()->avatar_original) }}" data-src="{{ uploaded_asset(Auth::user()->avatar_original) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                        @else
                            <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="image rounded-circle" data-src="nUlL" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                        @endif
                            <span class="remove-profile" id="remove-avatar">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2.25 4.5H3.75H15.75" stroke="#FF5252" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14.25 4.49974V14.9997C14.25 15.3976 14.092 15.7791 13.8107 16.0604C13.5294 16.3417 13.1478 16.4997 12.75 16.4997H5.25C4.85218 16.4997 4.47064 16.3417 4.18934 16.0604C3.90804 15.7791 3.75 15.3976 3.75 14.9997V4.49974M6 4.49974V2.99974C6 2.60192 6.15804 2.22038 6.43934 1.93908C6.72064 1.65778 7.10218 1.49974 7.5 1.49974H10.5C10.8978 1.49974 11.2794 1.65778 11.5607 1.93908C11.842 2.22038 12 2.60192 12 2.99974V4.49974" stroke="#FF5252" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                       </div>
                        <div class="upload-profile">
                            <p>At least 256 * 256px PNG or JPG File</p>
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text  font-weight-medium">
                                        <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9.00076 19.9985H6.75076C6.00623 19.9978 5.27037 19.8387 4.59202 19.5319C3.91367 19.225 3.30835 18.7774 2.81623 18.2187C2.32412 17.66 1.95646 17.003 1.73767 16.2913C1.51888 15.5797 1.45395 14.8296 1.5472 14.091C1.64045 13.3523 1.88975 12.6419 2.27855 12.007C2.66734 11.372 3.18674 10.827 3.80227 10.4082C4.41779 9.9893 5.11537 9.70615 5.8487 9.5775C6.58203 9.44886 7.33434 9.47765 8.05569 9.66199" stroke="#1F55BF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M7.5 12.4985C7.50012 11.3102 7.78261 10.1389 8.3242 9.08113C8.86579 8.02338 9.65097 7.10946 10.615 6.41468C11.5791 5.71991 12.6945 5.26416 13.8693 5.085C15.044 4.90584 16.2445 5.00839 17.3719 5.3842C18.4992 5.76001 19.5211 6.39833 20.3534 7.24656C21.1857 8.09478 21.8045 9.12863 22.1588 10.2629C22.5132 11.3972 22.5929 12.5995 22.3915 13.7706C22.19 14.9417 21.7132 16.0483 21.0002 16.999" stroke="#1F55BF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M11.0684 15.6806L14.2503 12.4985L17.4323 15.6806" stroke="#1F55BF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M14.25 19.9985V12.4985" stroke="#1F55BF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <div class="form-control file-amount">{{ ('Upload Photo') }}</div>
                                    <input id="user-avatar" type="hidden" name="photo" value="{{ Auth::user()->avatar_original ?? '' }}" class="selected-files">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-form-label">{{ ('Name') }}</label>
                    <div class="">
                        <input type="text" class="form-control" placeholder="{{ ('Your Name') }}" name="name" value="{{ Auth::user()->name }}" minlength="3" required>
                    </div>
                </div>

                <div class="form-group row">
                   <div class="col-12 col-sm-6 pr-sm-0 pb-2 pb-md-0">
                        <label class="col-form-label pb-1 pt-0">{{ ('Phone') }}</label>
                        <div class="">
                            <input type="text" class="form-control" placeholder="{{ ('Your Phone')}}" name="phone" value="{{ Auth::user()->phone }}">
                        </div>
                   </div>
                   <div class="col-12 col-sm-6 ">
                        <label class="pb-1 mb-0 col-form-label pt-0">Gender</label>
                        <div class="">
                            <select class="form-control" name="gender" id="exampleFormControlSelect1">
                                <option value="male"  @if(Auth::user()->gender == 'male') selected @endif>Male</option>
                                <option value="female" @if(Auth::user()->gender == 'female') selected @endif>Female</option>
                                <!-- <option value="trans" @if(Auth::user()->gender == 'trans') selected @endif>Trans.</option> -->
                            </select>
                        </div>
                   </div>
                </div>
                <?php
                    $user_birth = !is_null(Auth::user()->date_of_birth) ? \Carbon\Carbon::parse(Auth::user()->date_of_birth)->format('Y-m-d') : '';
                ?>
                <div class="form-group row">

                    <div class="col-12 col-sm-6 pr-sm-0">
                        <div class="">
                            <label class="col-form-label">{{ ('Email') }}</label>
                        </div>
                        <div class="">
                            <div class="input-group mb-3">
                                <input type="email" class="form-control" placeholder="{{ ('Your Email')}}" name="email" value="{{ Auth::user()->email }}" />
                                <!-- <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary new-email-verification">
                                        <span class="d-none loading">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>{{ ('Sending Email...') }}
                                        </span>
                                        <span class="default">{{ ('Verify') }}</span>
                                    </button>
                                </div> -->
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class=" col-form-label">{{ ('Date of Birth') }}</label>
                        <div class="date-of-birth">
                            <input type="date" class="form-control" value="@if($user_birth != NULL){{$user_birth}}@endif" name="date_of_birth"/>
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary">{{ ('Update')}}</button>
                </div>
            </div>
        </div>

        @if(Auth::user()->seller)
            <!-- Payment System -->
            <div class="card shadow-sm border-0">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Payment Setting')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <label class="col-md-3 col-form-label">{{ ('Cash Payment') }}</label>
                    <div class="col-md-9">
                        <label class="aiz-switch aiz-switch-success mb-3">
                            <input value="1" name="cash_on_delivery_status" type="checkbox" @if (Auth::user()->seller->cash_on_delivery_status == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label">{{ ('Bank Payment') }}</label>
                    <div class="col-md-9">
                        <label class="aiz-switch aiz-switch-success mb-3">
                            <input value="1" name="bank_payment_status" type="checkbox" @if (Auth::user()->seller->bank_payment_status == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label">{{ ('Bank Name') }}</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control mb-3" placeholder="{{ ('Bank Name')}}" value="{{ Auth::user()->seller->bank_name }}" name="bank_name">
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label">{{ ('Bank Account Name') }}</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control mb-3" placeholder="{{ ('Bank Account Name')}}" value="{{ Auth::user()->seller->bank_acc_name }}" name="bank_acc_name">
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label">{{ ('Bank Account Number') }}</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control mb-3" placeholder="{{ ('Bank Account Number')}}" value="{{ Auth::user()->seller->bank_acc_no }}" name="bank_acc_no">
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label">{{ ('Bank Routing Number') }}</label>
                    <div class="col-md-9">
                        <input type="number" lang="en" class="form-control mb-3" placeholder="{{ ('Bank Routing Number')}}" value="{{ Auth::user()->seller->bank_routing_no }}" name="bank_routing_no">
                    </div>
                </div>
            </div>
            </div>
        @endif

        <!-- <div class="form-group mb-0 text-right">
            <button type="submit" class="btn btn-primary">{{ ('Update Profile')}}</button>
        </div> -->
    </form>

    <br>


    <!-- Change Password -->
    <form action="{{ route('user.password.update') }}" method="POST">
        @csrf
        <div class="card shadow-none border-0 email-verification manage-profile"  >
          <div class="card-header border-0">
              <h5 class="mb-0 h6">{{ ('Change your password')}}</h5>
          </div>
          <div class="card-body py-2">
            <div class="row">
                <div class="col-md-12">
                  <div class="form-group row">
                      <label class="col-md-2 col-form-label">{{ ('New Password') }}</label>
                      <div class="col-md-10">
                          <input type="password" class="form-control" placeholder="{{ ('New Password') }}" name="new_password" value="{{ old('new_password') }}" required>
                      </div>
                  </div>
                  <div class="form-group row">
                      <label class="col-md-2 col-form-label">{{ ('Confirm Password') }}</label>
                      <div class="col-md-10">
                          <input type="password" class="form-control" placeholder="{{ ('Confirm Password') }}" name="confirm_password" value="{{ old('confirm_password') }}" required>
                      </div>
                  </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{ ('Change Password')}}</button>
                    </div>
                </div>
            </div>
          </div>
        </div>
    </form>


    <!-- Address -->
    <div class="card shadow-sm border-0">
        <div class="card-header border-0 pb-0" >
            <h5 class="mb-0 h6 fs-20">{{ ('Address')}}</h5>
        </div>
        <div class="card-body ">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="m-0 pl-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="address-container">
                @foreach (Auth::user()->addresses as $key => $address)
                    <div class="address-box-wrapper  position-relative">
                        <div class="address-box">
                            <p class="fs-18 fw-500">{{ auth()->user()->name }}</p>
                            <div class="dropdown ">
                                @if ($address->address_type == 'Office')
                                    <span class="office-or-home">Office</span>
                                @elseif($address->address_type == 'Home')
                                    <span class="office-or-home">Home</span>
                                @elseif($address->address_type == 'Other')
                                <span class="office-or-home">
                                    Others
                                </span>
                                @endif
                            </div>
                            <p>{{ $address->phone }}</p>
                            <p>
                                <span> {{ $address->address }}</span>,
                                @if(!empty($address->area))
                                <span>{{ optional($address->area)->name }}</span>,
                                @endif
                                @if(!empty($address->city))
                                <span>{{ optional($address->city)->name }}</span>,
                                @endif
                                @if(!empty($address->state))
                                <span>{{ optional($address->state)->name }}</span>
                                @endif
                                @if(!empty($address->postal_code))
                                <span>{{ $address->postal_code }}</span>,
                                @endif
                                @if(!empty($address->country))
                                {{-- <span>{{ optional($address->country)->name }}</span> --}}
                                @endif
                            </p>
                        </div>
                        <div class="edit-wrapper position-absolute right-0 top-0">
                            <div class="dropdown">
                                <button class="btn bg-gray px-2" type="button" data-toggle="dropdown">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 20.25H4.5C4.30109 20.25 4.11032 20.171 3.96967 20.0303C3.82902 19.8897 3.75 19.6989 3.75 19.5V15.3107C3.75 15.2122 3.7694 15.1147 3.80709 15.0237C3.84478 14.9327 3.90003 14.85 3.96967 14.7803L15.2197 3.53034C15.3603 3.38969 15.5511 3.31067 15.75 3.31067C15.9489 3.31067 16.1397 3.38969 16.2803 3.53034L20.4697 7.71968C20.6103 7.86033 20.6893 8.0511 20.6893 8.25001C20.6893 8.44892 20.6103 8.63969 20.4697 8.78034L9 20.25Z" stroke="#1F2029" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12.75 6L18 11.25" stroke="#1F2029" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>

                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" onclick="edit_address('{{$address->id}}')">
                                        {{ ('Edit') }}
                                    </a>
                                    @if (!$address->set_default)
                                        <a class="dropdown-item" href="{{ route('addresses.set_default', $address->id) }}">{{ ('Make This Default') }}</a>
                                    @endif
                                    <a class="dropdown-item" href="{{ route('addresses.destroy', $address->id) }}">{{ ('Delete') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
            <div class="row mx-auto add-new-address" onclick="add_new_address()">
                <div class="c-pointer text-center d-flex justify-content-center align-items-center flex-column w-100" >
                    <p class="fw-400 fs-16">Add your new address before order</p>
                    <div class="alpha-7"><i class="fas fa-plus"></i> {{ ('Add Address') }}</div>
                </div>
            </div>
        </div>
    </div>


    <!-- Change Email -->
    <form action="{{ route('user.change.email') }}" method="POST">
        @csrf
        <div class="card shadow-none border-0 email-verification"  >
          <div class="card-header border-0">
              <h5 class="mb-0 h6">{{ ('Verify Your Email')}}</h5>
          </div>
          <div class="card-body py-2">
              <div class="row mx-0 flex-column">
                  <div class="">
                      <label>{{ ('Email') }}</label>
                  </div>
                  <div class="">
                      <div class="input-group mb-3 d-flex align-items-center">
                        <input type="email" class="form-control rounded-sm" placeholder="{{ ('Your Email')}}" name="email" value="{{ auth()->user()->email }}" />
                        <div class="input-group-append">
                           <button type="button" class="btn btn-outline-secondary new-email-verification">
                               <span class="d-none loading">
                                   <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>{{ ('Sending Email...') }}
                               </span>
                               <span class="default">{{ ('Verify') }}</span>
                           </button>
                        </div>
                      </div>
                      <!-- <div class="form-group mb-0 text-right">
                          <button type="submit" class="btn btn-primary">{{ ('Update Email')}}</button>
                      </div> -->
                  </div>
              </div>
          </div>
        </div>
    </form>

@endsection

@section('modal')
    @include(config('app.theme').'frontend.partials.address_modal')
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('remove-avatar').addEventListener('click', function() {
        console.log('remove');
        document.getElementById('user-avatar').value = '';
        document.querySelectorAll('.show-user-avatar').forEach(element => {
            element.src = '{{ static_asset('assets/img/avatar-place.png') }}';
        });
    });
});

</script>
@section('script')
    <script type="text/javascript">
        // $('.remove-profile').on('click', function(){
        //     $('.profile-box').addClass('d-none');
        //     $('.upload-profile').removeClass('d-none');
        //     $('#user-avatar').val('');
        // });
        $('.new-email-verification').on('click', function() {
            $(this).find('.loading').removeClass('d-none');
            $(this).find('.default').addClass('d-none');
            var email = $("input[name=email]").val();

            $.post('{{ route('user.new.verify') }}', {_token:'{{ csrf_token() }}', email: email}, function(data){
                data = JSON.parse(data);
                $('.default').removeClass('d-none');
                $('.loading').addClass('d-none');
                if(data.status == 2)
                    AIZ.plugins.notify('warning', data.message);
                else if(data.status == 1)
                    AIZ.plugins.notify('success', data.message);
                else
                    AIZ.plugins.notify('danger', data.message);
            });
        });
    </script>

    @if (get_setting('google_map') == 1)

        @include(config('app.theme').'frontend.partials.google_map')

    @endif

@endsection
