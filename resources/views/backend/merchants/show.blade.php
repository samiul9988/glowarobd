@extends('backend.layouts.app')

@section('content')
<style>
    .expandable-row {
        cursor: pointer;
    }
    .expandable-content {
        display: none;
        padding: 10px;
        background-color: #f9f9f9;
        border-top: 1px solid #ddd;
    }
    .tab-content {
        padding: 10px;
        border: 1px solid #ddd;
        border-top: none;
    }
    .nav-tabs .nav-link {
        cursor: pointer;
    }
</style>

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{ ('Api Logs')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<h1 class="h3">{{ $merchant->name }}</h1>
			<h1 class="h3">{{ $merchant->email }}</h1>
		</div>
	</div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ ('Merchants')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Method</th>
                    <th>URL</th>
                    <th>Response Code</th>
                    <th>Response Time (ms)</th> 
                    <th>IP</th>
                    <th>User Agent</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $key => $log)
                    <tr class="expandable-row" data-id="{{ $log->id }}">
                        <td>{{ ($key+1) + ($logs->currentPage() - 1)*$logs->perPage() }}</td>
                        <td>{{ $log->method }}</td>
                        <td>{{ $log->url }}</td>
                        <td>{{ $log->response_code }}</td>
                        <td>{{ $log->response_time }}</td>
                        <td>{{ $log->ip }}</td>
                        <td>{{ $log->user_agent }}</td>
                        <td>{{ $log->created_at }}</td>
                    </tr>
                    <tr class="expandable-content" id="content-{{ $log->id }}">
                        <td colspan="7">
                            <!-- Tabs for Payload and Response -->
                            <ul class="nav nav-tabs" id="logTabs-{{ $log->id }}">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#payload-{{ $log->id }}">Payload</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#response-{{ $log->id }}">Response</a>
                                </li>
                            </ul>
                            <!-- Tab Content -->
                            <div class="tab-content">
                                <div id="payload-{{ $log->id }}" class="tab-pane fade show active">
                                    <pre>{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                                <div id="response-{{ $log->id }}" class="tab-pane fade">
                                    <pre>{{ json_encode($log->response, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $logs->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
<script>
    // Handle row expansion
    document.querySelectorAll('.expandable-row').forEach(row => {
        row.addEventListener('click', function () {
            const contentId = `content-${this.getAttribute('data-id')}`;
            const contentRow = document.getElementById(contentId);
            contentRow.style.display = contentRow.style.display === 'none' ? 'table-row' : 'none';
        });
    });

    // Initialize Bootstrap tabs
    document.querySelectorAll('.nav-tabs .nav-link').forEach(tab => {
        tab.addEventListener('click', function (e) {
            e.preventDefault(); // Prevent default anchor behavior
            const target = this.getAttribute('href'); // Get the target tab content ID
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active'); // Hide all panes
            });
            document.querySelector(target).classList.add('show', 'active'); // Show the target pane

            // Activate the clicked tab
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
</script>
@endsection