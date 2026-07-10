@extends(config('app.theme').'frontend.layouts.user_panel')

@section('meta')
<x-seo />
@endsection

@section('panel_content')
    <!-- <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">{{ ('Support Ticket') }}</h1>
            </div>
        </div>
    </div> -->
    <!-- <div class="row">
        <div class="col-md-4 mx-auto mb-3" >

        </div>
    </div> -->
    <div class="user-profile row bg-white py-2 rounded-sm align-items-center m-0 mb-3 px-2">
        <div class=" p-0 col-12 col-md-6 py-2 py-md-0">
            <p class="m-0 fw-500 fs-24 text-capitalize  text-lg-left text-start ">
                <span class="fw-700 " style="color:#FA7E16">Welcome, </span> {{ Auth::user()->name }}
            </p>
        </div>
        <div class="col-6 p-0 align-items-center justify-content-center justify-content-md-end pr-1 pr-md-4 d-none d-md-flex">
                <span class="avatar avatar-md pr-2 pr-md-0">
                    @if (Auth::user()->avatar_original != null)
                        <img src="{{ uploaded_asset(Auth::user()->avatar_original) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                    @else
                        <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="image rounded-circle" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
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
    <div class="card support-ticket shadow-none border-0">
        <div class=" py-3  py-md-4  border-bottom-0">
            <div class="row justify-content-between w-100 align-items-center m-0">
                <div class="col-6 ">
                    <h5 class="mb-0 h6 fs-20 fw-500">{{ ('Support Ticket')}}</h5>
                </div>
                <div class="col-6 d-flex justify-content-end ">
                    <div class=" c-pointer text-center d-flex align-items-center justify-content-center create-ticket-wrapper" data-toggle="modal" data-target="#ticket_modal">
                        <span class="d-flex align-items-center justify-content-center ">
                        <i class="fas fa-plus fw-500"></i>
                        </span>
                        <div class="">{{ ('Create a Ticket') }}</div>
                    </div>
                </div>
            </div>

        </div>
          <div class="card-body p-0">
              <table class="table aiz-table mb-0">
                  <thead>
                      <tr>
                          <th data-breakpoints="lg">{{ ('Ticket ID') }}</th>
                          <th data-breakpoints="lg">{{ ('Sending Date') }}</th>
                          <th>{{ ('Subject')}}</th>
                          <th class="status-heading">{{ ('Status')}}</th>
                          <th data-breakpoints="lg " class="action-heading">{{ ('Action')}}</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach ($tickets as $key => $ticket)
                          <tr class="fs-14">
                              <td>#{{ $ticket->code }}</td>
                              <td>{{ $ticket->created_at }}</td>
                              <td>{{ $ticket->subject }}</td>
                              <td>
                                  @if ($ticket->status == 'pending')
                                      <span class="badge badge-inline text-danger fs-14">{{ ('Pending')}}</span>
                                  @elseif ($ticket->status == 'open')
                                      <span class="badge badge-inline text-secondary fs-14">{{ ('Open')}}</span>
                                  @else
                                      <span class="badge badge-inline text-success fs-14">{{ ('Solved')}}</span>
                                  @endif
                              </td>
                              <td>
                                  <a href="{{route('support_ticket.show', encrypt($ticket->id))}}" class="   icon-anim ">
                                      {{ ('Details')}}
                                      <!-- <i class="la la-angle-right text-sm"></i> -->
                                  </a>
                              </td>
                          </tr>
                      @endforeach
                  </tbody>
              </table>
              <div class="aiz-pagination">
                  {{ $tickets->links() }}
              </div>
          </div>
    </div>
@endsection

@section('modal')
<div class="modal fade" id="ticket_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                  <h5 class="modal-title strong-600 heading-5">{{ ('Create a Ticket')}}</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body px-3 pt-3">
                  <form class="" action="{{ route('tickets.store') }}" method="post" enctype="multipart/form-data">
                      @csrf
                      <div class="row">
                          <div class="col-md-2">
                              <label>{{ ('Subject')}}</label>
                          </div>
                          <div class="col-md-10">
                              <input type="text" class="form-control mb-3" placeholder="{{ ('Subject')}}" name="subject" required>
                          </div>
                      </div>

                      <div class="row">
                          <div class="col-md-2">
                              <label>{{ ('Description')}}</label>
                          </div>
                          <div class="col-md-10">
                              <textarea type="text" class="form-control mb-3" rows="3" name="details" placeholder="{{ ('Type your reply')}}" data-buttons="bold,underline,italic,|,ul,ol,|,paragraph,|,undo,redo" required></textarea>
                          </div>
                      </div>
                      <div class="form-group row">
                          <label class="col-md-2 col-form-label">{{ ('Photo') }}</label>
                          <div class="col-md-10">
                              <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                  <div class="input-group-prepend">
                                      <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                  </div>
                                  <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                  <input type="hidden" name="attachments" class="selected-files">
                              </div>
                              <div class="file-preview box sm">
                              </div>
                          </div>
                      </div>
                      <div class="text-right mt-4">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ ('cancel')}}</button>
                          <button type="submit" class="btn btn-primary">{{ ('Send Ticket')}}</button>
                      </div>
                  </form>
              </div>
            </div>
        </div>
    </div>
@endsection
