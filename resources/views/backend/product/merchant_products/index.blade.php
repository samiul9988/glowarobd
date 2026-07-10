@extends('backend.layouts.app')
@php
    $categories = Cache::remember('filter_categories', now()->addDay(), function () {
        return \App\Models\Category::pluck('name', 'id')->toArray();
    });
    $brands = Cache::remember('filter_brands', now()->addDay(), function () {
        return \App\Models\Brand::pluck('name', 'id')->toArray();
    });
@endphp
@section('content')
    <div class="card">
        <form class="" id="sort_products" action="" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-md-0 h6">{{ 'All Products' }}</h5>
                </div>

                {{-- <div class="dropdown mb-2 mb-md-0">
                    <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                        Other Actions
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="javascript:;" id="bulk-update-btn">Bulk Update Price</a>
                        <a class="dropdown-item" href="javascript:;" id="import-btn">Import Merchant Products</a>
                    </div>
                </div> --}}

                <div class="col-md-2 ml-auto">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="brand_id"
                        name="brand_id" onchange="sort_products()" data-live-search="true">
                        <option value="">{{ 'All Brands' }}</option>
                        @foreach ($brands as $id => $name)
                            <option value="{{ $id }}" @if ($id == request()->brand_id) selected @endif>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 ml-auto">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="category_id"
                        name="category_id" onchange="sort_products()" data-live-search="true">
                        <option value="">{{ 'All Categories' }}</option>
                        @foreach ($categories as $id => $name)
                            <option value="{{ $id }}" @if ($id == request()->category_id) selected @endif>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control form-control-sm" id="search" name="search"
                            value="{{ request()->search }}" placeholder="Type & Enter">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="reset_filters()">Reset</button>
                    <div class="dropdown mb-2 mb-md-0 d-inline-block">
                        <button type="button" data-toggle="dropdown" class="btn btn-light btn-sm p-0 py-1">
                            <i class="las la-ellipsis-v font-weight-bold fs-24"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item btn-item" href="javascript:;" id="bulk-update-btn">
                                <i class="las la-money-bill text-success"></i> Bulk Update Price
                            </a>
                            <a class="dropdown-item btn-item" href="javascript:;" id="bulk-product-push-btn">
                                <i class="las la-cubes text-success"></i> Bulk Product Push
                            </a>
                            <a class="dropdown-item btn-item" href="javascript:;" id="import-btn">
                                <i class="las la-upload text-success"></i> Import Merchant Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body">
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
                        <th>Name</th>
                        <th>Price Info</th>
                        <th>Merchant Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $key => $product)
                        @php
                            $merchantProduct = $product->merchantProducts
                                ?->where('merchant_id', \App\Services\RokomariService::getMerchantId())
                                ->first();
                            if ($merchantProduct) {
                                $merchant_price = $merchantProduct->last_price;
                            } else {
                                $merchant_price = $product->unit_price;
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="form-group d-inline-block">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{ $product->id }}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="row gutters-5 w-200px w-md-300px mw-100">
                                    <div class="col-auto">
                                        <img src="{{ uploaded_asset($product->thumbnail_img) }}" alt="Image"
                                            class="size-50px img-fit"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                    </div>
                                    <div class="col">
                                        <span class="text-muted text-truncate-2">{{ $product->name }} </span>
                                    </div>
                                </div>
                                @if ($merchantProduct?->pushed_at)
                                    <small class="text-info">
                                        Pushed At: {{ $merchantProduct->pushed_at->diffForHumans() }}<br>
                                    </small>
                                @endif
                            </td>
                            <td>
                                <span class="d-block">
                                    <strong>Last P.Price:</strong> {{ single_price($product->lastPurchaseOrderItem?->price ?? 0) }}
                                </span>
                                <span class="d-block">
                                    <strong>Base Price:</strong> {{ single_price($product->unit_price) }}
                                </span>
                            </td>
                            <td>
                                <div class="row gutters-5">
                                    <div class="col-10">
                                        <input type="text" class="form-control form-control-sm new-price"
                                            id="product-{{ $product->id }}" data-min="{{ $product->lastPurchaseOrderItem?->price ?? 0 }}" data-max="{{ $product->unit_price }}" data-price="{{ $merchant_price }}" value="{{ $merchant_price }}" style="border-radius: 5px;">
                                    </div>
                                    <div class="col w-80px">
                                        <div class="d-flex align-items-center h-100">
                                            <button class="btn btn-outline-none p-0" type="button"
                                                id="status-{{ $product->id }}" style="display: none;" disabled>
                                                <i class="la la-spinner la-spin fs-18"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @if ($merchantProduct?->price_updated_at)
                                    <small class="text-info">
                                        Last updated: {{ $merchantProduct->price_updated_at->diffForHumans() }}<br>
                                    </small>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $products->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection

@section('modal')
    <div class="modal fade" id="bulkUpdateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Bulk Update Prices</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="price">Price</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="amount" name="amount" placeholder="Enter amount">

                            <div class="input-group-append">
                                <select class="form-control" id="price_type" name="price_type">
                                    <option value="flat">Flat</option>
                                    <option value="percentage">Percentage</option>
                                </select>
                            </div>
                        </div>
                        <span id="amount-error" class="text-danger"></span>
                    </div>
                    <div class="text-center">
                        <p id="bulk-update-info"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" id="confirm-bulk-update-btn">Update</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="productImportModal" tabindex="-1" role="dialog" aria-labelledby="productImportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productImportModalLabel">Import Merchant Products</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" id="import-form" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="attachment">Upload Excel/CSV</label>
                            <input type="file" class="form-control form-control-sm" id="attachment" name="attachment" accept=".xlsx,.csv">
                            <span id="attachment-error" class="text-danger"></span>
                        </div>
                    </form>
                    <div class="alert alert-info">
                        <i class="las la-info-circle fs-14" style="animation: heartbeat 1.5s infinite;"></i>
                        Uploaded file should have following columns: <strong>mrp, sku</strong> <strong><a id="sample-file-download" href="javascript:void(0);">Click Here</a></strong> to download sample file.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" id="confirm-import-btn">Import</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).on("change", ".check-all", function() {
            if (this.checked) {
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }
        });

        $('#search').on('keydown', function(e) {
            if (e.keyCode == 13) {
                e.preventDefault(); // Ensure it is only this code that runs
                sort_products();
            }
        });

        function sort_products(el) {
            $('#sort_products').submit();
        }

        function reset_filters() {
            window.location.href = '{{ route('merchant_products.index') }}';
        }

        $().ready(function() {
            const debouncedSearch = debounce(updatePrice, 700);
            $('#theTable').on('input', '.new-price', function() {
                const min_price = parseFloat($(this).data('min')) || 0;
                const max_price = parseFloat($(this).data('max')) || 0;
                const old_price = parseFloat($(this).data('price')) || 0;
                const new_price = parseFloat($(this).val()) || 0;
                // Skip if price is unchanged
                if (old_price === new_price || Math.abs(new_price - old_price) < 0.0001 || isNaN(new_price) || new_price == '') {
                    return;
                }
                const product_id = $(this).closest('tr').find('.check-one').val();
                debouncedSearch(product_id, new_price, old_price, min_price, max_price);
            });

            async function updatePrice(product_id, new_price, old_price, min_price, max_price) {
                console.log('New Price:', new_price, 'Old Price:', old_price, 'Min Price:', min_price, 'Max Price:', max_price);
                if (isNaN(new_price) || new_price == '') {
                    return;
                } else if(new_price < min_price){
                    AIZ.plugins.notify('danger', `Price cannot be less than purchase price`);
                    $('#product-' + product_id).val(old_price);
                    return;
                } else if(new_price > max_price){
                    AIZ.plugins.notify('danger', `Price cannot be greater than base price`);
                    $('#product-' + product_id).val(old_price);
                    return;
                }
                $('#product-' + product_id).prop('disabled', true);
                $('#status-' + product_id).fadeIn();
                await $.ajax({
                    url: '{{ route('merchant_products.update_price') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        product_id: product_id,
                        new_price: new_price
                    },
                    success: function(response) {
                        $('#status-' + product_id).fadeOut();
                        $('#product-' + product_id).prop('disabled', false);
                        if (response.success) {
                            $('#product-' + product_id).data('price', new_price);
                            AIZ.plugins.notify('success', response.message);
                        } else {
                            $('#product-' + product_id).val(old_price);
                            AIZ.plugins.notify('danger', response.message || 'Failed to update price.');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#status-' + product_id).fadeOut();
                        $('#product-' + product_id).prop('disabled', false);
                        $('#product-' + product_id).val(old_price);
                        AIZ.plugins.notify('danger', 'Failed to update price.');
                    }
                });
            }
        });

        $('#sample-file-download').on('click', function() {
            let aTag = document.createElement('a');
            aTag.href = '{{ static_asset('assets/sample_merchant_products_list.xlsx') }}';
            aTag.setAttribute('download', 'sample_merchant_products_list.xlsx');
            document.body.appendChild(aTag);
            aTag.click();
            document.body.removeChild(aTag);
        });

        $('#import-btn').on('click', function() {
            $('#attachment').val('');
            $('#attachment-error').text('');
            $('#productImportModal').modal('show');
        });

        $('#confirm-import-btn').on('click', async function() {
            const fileInput = $('#attachment')[0];
            if (fileInput.files.length === 0) {
                $('#attachment-error').text('Please select a file to upload.');
                return;
            }
            const formData = new FormData($('#import-form')[0]);
            $('#attachment-error').text('');

            $('#confirm-import-btn').prop('disabled', true).text('Importing...');
            await $.ajax({
                url: `{{ route('merchant_products.import') }}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#confirm-import-btn').prop('disabled', false).text('Import');
                    $('#productImportModal').modal('hide');
                    $('#attachment').val('');
                    $('#attachment-error').text('');
                    if (response.success) {
                        showAlert('success', response.message);
                    } else {
                        AIZ.plugins.notify('danger', response.message || 'Import failed.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#confirm-import-btn').prop('disabled', false).text('Import');
                    let errorMessage = 'Import failed.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).join(' ');
                    }
                    AIZ.plugins.notify('danger', errorMessage);
                }
            });
        });

        $('#bulk-product-push-btn').on('click', function() {
            let selectedProducts = [];
            $('.check-one:checked').each(function() {
                selectedProducts.push($(this).val());
            });

            Swal.fire({
                title: 'Are You Sure?',
                text: `You are about to push ${selectedProducts.length === 0 ? "all products" : selectedProducts.length + " selected products"} to the merchant panel.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Push Now!',
                cancelButtonText: 'No, Cancel!',
            }).then((result) => {
                if (result.isConfirmed) {
                    performBulkProductPush(selectedProducts);
                }
            });
        });

        function performBulkProductPush(productIds = []) {
            $('#bulk-product-push-btn').prop('disabled', true);
            $.ajax({
                url: `{{ route('merchant_products.bulk_push') }}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    product_ids: productIds,
                    brand_id: $('#brand_id').val(),
                    category_id: $('#category_id').val(),
                    search: $('#search').val()
                },
                success: function(response) {
                    $('#bulk-product-push-btn').prop('disabled', false);
                    if (response.success) {
                        showAlert('success', response.message);
                    } else {
                        AIZ.plugins.notify('danger', response.message || 'Bulk push failed.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#bulk-product-push-btn').prop('disabled', false);
                    AIZ.plugins.notify('danger', 'Bulk push failed.');
                }
            });
        }

        $('#bulk-update-btn').on('click', function() {
            $('#amount').val('');
            $('#price_type').val('flat');
            $('#amount-error').text('');

            let selectedCount = $('.check-one:checked').length;
            if (selectedCount === 0) {
                selectedCount = "{{ $products->total() }}";
            }

            $('#bulk-update-info').text(`** You are about to update prices for ${selectedCount} products **`);
            $('#bulkUpdateModal').modal('show');
        });

        $('#confirm-bulk-update-btn').on('click', async function() {
            const amount = parseFloat($('#amount').val());
            const priceType = $('#price_type').val();
            $('#amount-error').text('');

            if (priceType === 'flat') {
                if (isNaN(amount) || amount <= 0) {
                    $('#amount-error').text('Please enter a valid positive amount.');
                    return;
                }
            } else if (priceType === 'percentage') {
                if (isNaN(amount) || amount < 0 || amount > 100) {
                    $('#amount-error').text('Please enter a valid percentage between 0 and 100.');
                    return;
                }
            }

            let selectedProducts = [];
            $('.check-one:checked').each(function() {
                selectedProducts.push($(this).val());
            });

            $('#confirm-bulk-update-btn').prop('disabled', true).text('Updating...');

            await $.ajax({
                url: `{{ route('merchant_products.bulk_update_price') }}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    product_ids: selectedProducts,
                    amount: amount,
                    price_type: priceType,
                    brand_id: $('#brand_id').val(),
                    category_id: $('#category_id').val(),
                    search: $('#search').val()
                },
                success: function(response) {
                    $('#confirm-bulk-update-btn').prop('disabled', false).text('Update');
                    $('#bulkUpdateModal').modal('hide');
                    if (response.success) {
                        showAlert('success', response.message);
                    } else {
                        AIZ.plugins.notify('danger', response.message || 'Bulk update failed.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#confirm-bulk-update-btn').prop('disabled', false).text('Update');
                    AIZ.plugins.notify('danger', 'Bulk update failed.');
                }
            });
        });

        $('#price_type').on('change', function() {
            const priceType = $(this).val();
            $('#amount').attr('placeholder', priceType === 'flat' ? 'Enter a positive amount' : 'Enter percentage between 0-100');
        });

        $('#amount').on('input', function() {
            $('#amount-error').text('');
            const priceType = $('#price_type').val();
            const amount = parseFloat($(this).val());

            if (priceType === 'flat') {
                if (isNaN(amount) || amount <= 0) {
                    $('#amount-error').text('Please enter a valid positive amount.');
                }
            } else if (priceType === 'percentage') {
                if (isNaN(amount) || amount < 0 || amount > 100) {
                    $('#amount-error').text('Please enter a valid percentage between 0 and 100.');
                }
            }
        });
    </script>
@endsection
