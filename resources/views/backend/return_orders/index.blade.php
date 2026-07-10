@extends('backend.layouts.app')

@section('content')
<div class="row  gutters-5 mb-2">
    <div class="col-md-6  ">
        <div class="card h-100" >
            <div  class=" mx-auto" style="max-width:320px;width:100%;">
                <div class="card-header justify-content-center">
                    <h6 class="mb-0 fs-14">Ratio by Status</h6>
                </div>
                <div class="card-body">
                    <div id="return-ratio-by-status-loading" class="d-flex justify-content-center align-items-center"
                        style="height: 305px !important; font-size: 20px !important;">
                        <i class="las la-circle-notch la-spin la-3x text-info"></i>
                    </div>

                    <canvas id="return-ratio-by-status" class="d-none" height="305"></canvas>
                </div>

            </div>
        </div>
    </div>
    <div class="col-md-6 mx-auto justify-content-center ">
        <div class="card h-100">
            <div class=" mx-auto" style="max-width:360px;width:100%;">
                <div class="card-header justify-content-center">
                    <h6 class="mb-0 fs-14">Ratio by Reasons</h6>
                </div>
                <div class="card-body">
                    <div id="return-ratio-by-reasons-loading" class="d-flex justify-content-center align-items-center"
                        style="height: 305px !important; font-size: 20px !important;">
                        <i class="las la-circle-notch la-spin la-3x text-info"></i>
                    </div>

                    <canvas id="return-ratio-by-reasons" class="d-none" height="305"></canvas>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('Order Return Requests') }}</h5>
            </div>

            @if($status == 'pending')
                <div class="dropdown mb-2 mb-md-0">
                    <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                        {{ ('Bulk Action')}}
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#exampleModal">
                            <i class="las la-sync-alt"></i>
                            {{ ('Change Status')}}
                        </a>
                    </div>
                </div>

                {{-- Change Status Modal --}}
                <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">
                                    {{ ('Choose a status')}}
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" style="min-height: 400px">
                                <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity" id="update_status">
                                    <option value="">Select Status</option>
                                    <option value="approved" selected>Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="bulk-update-status">Save changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ request()->date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" value="{{ request()->search }}" placeholder="{{ ('Type Order code & customer') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group btn-sm" role="group" aria-label="Basic example">
                    <a href="{{ route('return-orders.index', array_merge(request()->input(), ['status' => 'pending'])) }}" class="btn btn-secondary btn-sm {{ $status == 'pending' ? 'active' : '' }}">
                        Pending <span class="w-auto badge badge-primary ml-1" id="pending-count">{{ $statusCounts['pending'] ?? 0 }}</span>
                    </a>

                    <a href="{{ route('return-orders.index', array_merge(request()->input(), ['status' => 'processing'])) }}" class="btn btn-secondary btn-sm {{ $status == 'processing' ? 'active' : '' }}">
                        Processing <span class="w-auto badge badge-primary ml-1" id="processing-count">{{ $statusCounts['processing'] ?? 0 }}</span>
                    </a>

                    <a href="{{ route('return-orders.index', array_merge(request()->input(), ['status' => 'approved'])) }}" class="btn btn-secondary btn-sm {{ $status == 'approved' ? 'active' : '' }}">
                        Approved <span class="w-auto badge badge-primary ml-1" id="approved-count">{{ $statusCounts['approved'] ?? 0 }}</span>
                    </a>
                </div>
                <div>
                    <a href="{{ route('return-orders.create') }}" class="btn btn-primary btn-sm">Create New Return</a>
                </div>
            </div>

            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>Order ID</th>
                        <th class="text-center" width="10%">Return Type</th>
                        <th class="text-center">Status</th>
                        @if($status == 'approved')
                            <th width="20%">Approved By</th>
                        @endif
                        <th>Reason</th>
                        <th class="text-center" width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($returnRequests as $key => $returnRequest)
                    <tr>
                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{ $returnRequest->id }}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('all_orders.show', encrypt($returnRequest->order_id)) }}" target="_blank">
                                #{{ $returnRequest->order->code }}
                            </a>
                        </td>
                        <td class="text-center">
                            @if ($returnRequest->is_partial)
                                <span class="badge badge-inline badge-info">Partial</span>
                            @else
                                <span class="badge badge-inline badge-success">Full</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($returnRequest->status == 'pending')
                                <span class="badge badge-inline badge-info">Pending</span>
                            @elseif ($returnRequest->status == 'approved')
                                <span class="badge badge-inline badge-success">Approved</span>
                            @elseif ($returnRequest->status == 'processing')
                                <span class="badge badge-inline badge-warning">Processing</span>
                            @endif
                        </td>
                        @if($status == 'approved' && $returnRequest->approved_by)
                            <td>
                                <span class="d-block text-muted">- by <strong class="text-info">{{ $returnRequest->approver->name }}</strong> at {{ $returnRequest->approved_at->format('d-m-Y h:i A') }}</span>
                            </td>
                        @endif
                        <td>
                            <span class="d-block">{{ ucwords($returnRequest->reason) }}</span>
                            <span class="d-block text-muted">- by <strong class="text-info">{{ $returnRequest->user_id === 0 ? 'Pathao' : $returnRequest->user->name }}</strong> at {{ $returnRequest->created_at->format('d-m-Y h:i A') }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('return-orders.show', encrypt($returnRequest->id)) }}" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ ('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $returnRequests->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            $(document).on("change", ".check-all", function() {
                if(this.checked) {
                    $('.check-one:checkbox').each(function() {
                        this.checked = true;
                    });
                } else {
                    $('.check-one:checkbox').each(function() {
                        this.checked = false;
                    });
                }
            });
            $('#bulk-update-status').on('click', function() {
                var ids = [];
                $('input[name="id[]"]:checked').each(function() {
                    ids.push($(this).val());
                });
                var status = $('#update_status').val();
                if(ids.length > 0 && status) {
                    $.ajax({
                        url: "{{ route('return-orders.bulk-update-status') }}",
                        type: "POST",
                        data: {
                            ids: ids,
                            status: status,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            $('#exampleModal').modal('hide');
                            if(response.success) {
                                showAlert('success', response.message, window.location.href);
                            } else {
                                showAlert('danger', response.message || 'An error occurred');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log(error);
                            showAlert('danger', xhr.responseJSON.message || 'Something went wrong!');
                        }
                    });
                } else {
                    alert('Please select at least one order and a status.');
                }
            });
        });

        getRatioCharts();
        function getRatioCharts(){
            const dateRange = $('input[name=date]').val();
            $('#cancel-ratio-by-user-types-loading').removeClass('d-none');
            $('#cancel-ratio-by-reasons-loading').removeClass('d-none');
            $('#cancel-ratio-by-user-types').addClass('d-none');
            $('#cancel-ratio-by-reasons').addClass('d-none');

            $.ajax({
                url: '{{ route("return-orders.ratio") }}',
                type: 'GET',
                data: {
                    date: dateRange
                },
                success: function(response) {
                    if (response.success) {
                        console.log(response);
                        renderGraph('ratioByStatus', response.ratioByStatus);
                        renderGraph('ratioByReasons', response.ratioByReasons);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('An error occurred while fetching the data.');
                }
            });
        }

        function renderGraph(type, data) {
            let chartContainerId = '';
            if (type === 'ratioByStatus') {
                chartContainerId = 'return-ratio-by-status';
            } else {
                chartContainerId = 'return-ratio-by-reasons';
            }

            const labels = Object.keys(data);
            const values = Object.values(data);
            const backgroundColors = [
                "#fd3995",
                "#34bfa3",
                "#5d78ff",
                "#fdcb6e",
                "#d35400",
                "#8e44ad",
                "#006442",
                "#4D8FAC",
                "#CA6924",
                "#C91F37"
            ];

            // Destroy previous chart if exists
            if (window[`${chartContainerId}Chart`]) {
                window[`${chartContainerId}Chart`].destroy();
            }

            const ctx = document.getElementById(chartContainerId).getContext('2d');
            window[`${chartContainerId}Chart`] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: backgroundColors.slice(0, labels.length)
                    }]
                },
                options: {
                    cutoutPercentage: 70,
                    legend: {
                        labels: {
                            fontFamily: 'Montserrat',
                            boxWidth: 10,
                            usePointStyle: true
                        },
                        onClick: function() {
                            return '';
                        },
                        position: 'bottom'
                    }
                }
            });

            // Toggle loading/visibility
            $(`#${chartContainerId}-loading`).addClass('d-none').removeClass('d-flex');
            $(`#${chartContainerId}`).removeClass('d-none');
        }
    </script>
@endsection
