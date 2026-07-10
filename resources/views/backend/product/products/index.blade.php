@extends('backend.layouts.app')
@php
    $categories = Cache::remember('filter_categories', now()->addDay(), function () {
        return \App\Models\Category::pluck('name', 'id')->toArray();
    });
    $brands = Cache::remember('filter_brands', now()->addDay(), function () {
        return \App\Models\Brand::pluck('name', 'id')->toArray();
    });
    $sellers = Cache::remember('filter_sellers'.(@$type ?? ''), now()->addDay(), function () use ($type) {
        if(strtolower($type) === 'all'){
            return App\Models\User::where('user_type', '=', 'admin')->orWhere('user_type', '=', 'seller')->pluck('name', 'id')->toArray();
        }
        else{
            return App\Models\Seller::pluck('name', 'id')->toArray();
        }
    });
@endphp
@section('content')
<style>
    .badge-count{
        margin-left: 5px;
        width: auto;
    }
</style>
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ ('All products')}}</h1>
        </div>
        @if($type != 'Seller')
        <div class="col text-right">
            <a href="{{ route('products.create') }}" class="btn-sm btn btn-success">
                <span>{{ ('Add New Product')}}</span>
            </a>
        </div>
        @endif
    </div>
</div>
<br>

<div class="card">
    <form class="" id="sort_products" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{ ('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" onclick="bulk_delete_modal()"> {{ ('Delete selection')}}</a>
                </div>
            </div>

            @if($type == 'Seller')
            <div class="col-lg-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="user_id" name="user_id" onchange="sort_products()">
                    <option value="">{{ ('All Sellers') }}</option>
                    @foreach ($sellers as $key => $seller)
                        @if ($seller->user != null && $seller->user->shop != null)
                            <option value="{{ $seller->user->id }}" @if ($seller->user->id == $seller_id) selected @endif>{{ $seller->user->shop->name }} ({{ $seller->user->name }})</option>
                        @endif
                    @endforeach
                </select>
            </div>
            @endif



            @if($type == 'All')
            <div class="col-lg-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="user_id" name="user_id" onchange="sort_products()" data-live-search="true">
                    <option value="">{{ ('All Sellers') }}</option>
                    @foreach ($sellers as $id => $name)
                        <option value="{{ $id }}" @if ($id == $seller_id) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-lg-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="brand_id" name="brand_id" onchange="sort_products()" data-live-search="true">
                    <option value="">{{ ('All Brands') }}</option>
                    @foreach ($brands as $id => $name)
                        <option value="{{ $id }}" @if ($id == $brand_id) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="category_id" name="category_id" onchange="sort_products()" data-live-search="true">
                    <option value="">{{ ('All Categories') }}</option>
                    @foreach ($categories as $id => $name)
                        <option value="{{ $id }}" @if ($id == $category_id) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="type" id="type" onchange="sort_products()">
                    <option value="">{{ ('Sort By') }}</option>
                    <option value="rating,desc" @isset($col_name , $query) @if($col_name == 'rating' && $query == 'desc') selected @endif @endisset>{{ ('Rating (High > Low)')}}</option>
                    <option value="rating,asc" @isset($col_name , $query) @if($col_name == 'rating' && $query == 'asc') selected @endif @endisset>{{ ('Rating (Low > High)')}}</option>
                    <option value="num_of_sale,desc"@isset($col_name , $query) @if($col_name == 'num_of_sale' && $query == 'desc') selected @endif @endisset>{{ ('Num of Sale (High > Low)')}}</option>
                    <option value="num_of_sale,asc"@isset($col_name , $query) @if($col_name == 'num_of_sale' && $query == 'asc') selected @endif @endisset>{{ ('Num of Sale (Low > High)')}}</option>
                    <option value="unit_price,desc"@isset($col_name , $query) @if($col_name == 'unit_price' && $query == 'desc') selected @endif @endisset>{{ ('Base Price (High > Low)')}}</option>
                    <option value="unit_price,asc"@isset($col_name , $query) @if($col_name == 'unit_price' && $query == 'asc') selected @endif @endisset>{{ ('Base Price (Low > High)')}}</option>
                </select>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ ('Type & Enter') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="button" onclick="sort_products()" class="btn btn-sm btn-primary">Filter</button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Status Wise Product Tabs -->
            <div class="d-flex justify-content-between">
                <div class="btn-group btn-sm" role="group" aria-label="Status Tabs">
                    <a href="{{ route('all_products.status', 'published') }}" class="btn btn-sm btn-secondary @if($currentStatus=='published') active @endif">Published <span class="badge badge-light badge-count">{{ $productStatusCount['published'] }}</span></a>
                    <a href="{{ route('all_products.status', 'unpublished') }}" class="btn btn-sm btn-secondary @if($currentStatus=='unpublished') active @endif">Unpublished <span class="badge badge-light badge-count">{{ $productStatusCount['unpublished'] }}</span></a>
                    <a href="{{ route('all_products.status', 'outofstock') }}" class="btn btn-sm btn-secondary @if($currentStatus=='outofstock') active @endif">Out of Stock <span class="badge badge-light badge-count">{{ $productStatusCount['outofstock'] }}</span></a>
                    <a href="{{ route('all_products.status', 'lowstock') }}" class="btn btn-sm btn-secondary @if($currentStatus=='lowstock') active @endif">Low Stock <span class="badge badge-light badge-count">{{ $productStatusCount['lowstock'] }}</span></a>
                    <a href="{{ route('all_products.status', 'all') }}" class="btn btn-sm btn-secondary @if($currentStatus=='all') active @endif">All<span class="badge badge-light badge-count">{{ $productStatusCount['all'] }}</span></a>
                </div>
                <div>
                    <button id="btnExport" type="button" onclick="fnExcelReport(this);" class="btn btn-sm btn-soft-primary">
                        <i class="las la-file-excel"></i> Export
                    </button>
                </div>
            </div>

            <table class="table aiz-table mb-0" id="theTable">
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
                        <!--<th data-breakpoints="lg">#</th>-->
                        <th>{{ ('Name')}}</th>
                        <th data-breakpoints="lg">Added By</th>
                        <th data-breakpoints="sm">{{ ('Info')}}</th>
                        <th data-breakpoints="md">{{ ('Total Stock')}}</th>
                        <th data-breakpoints="lg">{{ ('Todays Deal')}}</th>
                        <th data-breakpoints="lg">{{ ('Published')}}</th>
                        @if(get_setting('product_approve_by_admin') == 1 && $type == 'Seller')
                            <th data-breakpoints="lg">{{ ('Approved')}}</th>
                        @endif
                        <th data-breakpoints="lg">{{ ('Featured')}}</th>
                        <th data-breakpoints="lg">{{ ('Subscription')}}</th>
                        <th data-breakpoints="sm" class="text-right">{{ ('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $key => $product)
                    <tr>
                        {{-- <td>{{ ($key+1) + ($products->currentPage() - 1)*$products->perPage() }}</td> --}}
                        <td>
                            <div class="form-group d-inline-block">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" class="check-one" name="id[]" value="{{$product->id}}">
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="row gutters-5 w-200px w-md-300px mw-100">
                                <div class="col-auto">
                                    <img src="{{ uploaded_asset($product->thumbnail_img) }}" alt="Image" class="size-50px img-fit">
                                </div>
                                <div class="col">
                                    <span class="text-muted text-truncate-2">{{ $product->getTranslation('name') }} </span>
                                </div>
                                @if(check_preorder_product($product))
                                    <div class="col">
                                        <span class="badge badge-sm rounded-pill bg-success text-dark" style="width: 50px">Preorder</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="font-weight-bold">
                            <span class="d-block fs-11 text-muted">
                                Created By: <strong>{{ $product->created_by ? $product->createdByUser->name : $product->user->name }}</strong>
                            </span>
                            <span class="d-block fs-10 text-success">
                                Created At: {{ $product->created_at->diffForHumans() }}
                            </span>
                            <span class="d-block fs-11 text-muted">
                                Updated By: <strong>{{ $product->updated_by ? $product->updatedByUser->name : $product->user->name }}</strong>
                            </span>
                            <span class="d-block fs-10 text-primary">
                                Last Update: {{ $product->updated_at->diffForHumans() }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-inline fs-11 badge-soft-info font-weight-bold mb-1">
                                <i class="las la-chart-bar mr-2"></i>Num Of Sale: {{ $product->num_of_sale }} {{ ('times')}}
                            </span> <br>
                            <span class="badge badge-inline fs-11 badge-soft-primary font-weight-bold mb-1">
                                <i class="las la-coins mr-2"></i>Base Price: {{ single_price($product->unit_price) }}
                            </span> <br>
                            <span class="badge badge-inline fs-11 badge-soft-danger font-weight-bold mb-1">
                                <i class="las la-star mr-2"></i>Rating: {{ $product->rating }}
                            </span>
                            <span class="badge badge-inline fs-11 badge-soft-success font-weight-bold mb-1">
                                <i class="las la-eye mr-2"></i>Views: {{ readableNumber($product->views_count ?? 0) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $qty = 0;
                                if($product->variant_product) {
                                    // dd($product);
                                    foreach ($product->stocks as $key => $stock) {
                                        $qty += $stock->qty;
                                        echo $stock->variant.' - '.$stock->qty.'<br>';
                                    }
                                }
                                else {
                                    //$qty = $product->current_stock;
                                    $qty = optional($product->stocks->first())->qty;
                                    echo $qty;
                                }
                            @endphp
                            @if($qty <= $product->low_stock_quantity)
                                <span class="badge badge-inline badge-danger">Low</span>
                            @endif
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_todays_deal(this)" value="{{ $product->id }}" type="checkbox" <?php if ($product->todays_deal == 1) echo "checked"; ?> >
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_published(this)" value="{{ $product->id }}" type="checkbox" <?php if ($product->published == 1) echo "checked"; ?> >
                                <span class="slider round"></span>
                            </label>
                        </td>
                        @if(get_setting('product_approve_by_admin') == 1 && $type == 'Seller')
                            <td>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input onchange="update_approved(this)" value="{{ $product->id }}" type="checkbox" <?php if ($product->approved == 1) echo "checked"; ?> >
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        @endif
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_featured(this)" value="{{ $product->id }}" type="checkbox" <?php if ($product->featured == 1) echo "checked"; ?> >
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_subscription(this)" value="{{ $product->id }}" type="checkbox" <?php if ($product->subscription == 1) echo "checked"; ?> >
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-right">
                            <a class="btn btn-soft-success btn-icon btn-circle btn-sm"  href="{{ to_frontend(route('product', $product->slug)) }}" target="_blank" title="{{ ('View') }}">
                                <i class="las la-eye"></i>
                            </a>

                            @if ($type == 'Seller')
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('products.seller.edit', ['id'=>$product->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ ('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            {{-- <a class="btn btn-soft-success btn-icon btn-circle btn-sm"  href="{{route('products.seller.stock', ['id'=>$product->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ ('Product Stock') }}">
                                <i class="las la-box"></i>
                            </a> --}}
                            @else
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('products.admin.edit', ['id'=>$product->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ ('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>

                            {{-- <a class="btn btn-soft-success btn-icon btn-circle btn-sm"  href="{{route('products.admin.stock', ['id'=>$product->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ ('Product Stock') }}">
                                <i class="las la-box"></i>
                            </a> --}}
                            @endif
                            <a class="btn btn-soft-warning btn-icon btn-circle btn-sm" href="{{route('products.duplicate', ['id'=>$product->id, 'type'=>$type]  )}}" title="{{ ('Duplicate') }}">
                                <i class="las la-copy"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('products.destroy', $product->id)}}" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $products->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
    @include('modals.bulk_delete_modal')
@endsection

@section('script')
    <script type="text/javascript">

        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });

        $(document).ready(function(){
            //$('#container').removeClass('mainnav-lg').addClass('mainnav-sm');
        });

        function update_todays_deal(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('products.todays_deal') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ ('Todays Deal updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }

        function update_published(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('products.published') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ ('Published products updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }

        function update_approved(el){
            if(el.checked){
                var approved = 1;
            }
            else{
                var approved = 0;
            }
            $.post('{{ route('products.approved') }}', {
                _token      :   '{{ csrf_token() }}',
                id          :   el.value,
                approved    :   approved
            }, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ ('Product approval update successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }

        function update_featured(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('products.featured') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ ('Featured products updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }

        function update_subscription(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('products.subscription') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ ('Subscription products updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }

        function sort_products(el){
            $('#sort_products').submit();
        }

        function bulk_delete() {
            var data = new FormData($('#sort_products')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-product-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        location.reload();
                    }
                }
            });
        }
        function bulk_delete_modal(){
            $('#bulk_delete-modal').modal('show');
        }

        function fnExcelReport(el){
            var formData = new FormData();
            formData.append('user_id', '{{$seller_id}}');
            formData.append('status', '{{$currentStatus}}');
            formData.append('brand_id', '{{$brand_id}}');
            formData.append('category_id', '{{$category_id}}');
            formData.append('search', '{{$sort_search}}');
            formData.append('type', $('#type').val());

            $(el).prop('disabled', true).html('<i class="las la-file-excel"></i> Exporting...');
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                xhrFields: {
                    responseType: 'blob',
                },
                url: "{{route('products.export')}}",
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false, // Important: ensures no manual content type
                processData: false, // Important: prevents jQuery from processing the data
                success: function(result, status, xhr) {
                    $(el).prop('disabled', false).html('<i class="las la-file-excel"></i> Export');
                    var disposition = xhr.getResponseHeader('content-disposition');
                    var matches = /"([^"]*)"/.exec(disposition);
                    var filename = 'products_' + new Date().toISOString().slice(0,19).replace(/[-:T]/g,"_") + '.xlsx';

                    // The actual download
                    var blob = new Blob([result], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },
                error: function(xhr, status, error) {
                    $(el).prop('disabled', false).html('<i class="las la-file-excel"></i> Export');
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });

        }
    </script>
@endsection
