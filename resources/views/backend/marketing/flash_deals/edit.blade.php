@extends('backend.layouts.app')

@section('content')
    @php
        // Prepare existing products for JavaScript
        $existingProducts = $flashDeal->flash_deal_products->map(function ($fdp) {
            $currentStock = $fdp->product?->stocks?->first()?->qty ?? 0;
            $discount = $fdp->discount ?? 0;
            $discountType = $fdp->discount_type ?? 'amount';
            if ($discount <= 0) {
                $discount = $fdp->product?->discount ?? 0;
                $discountType = $fdp->product?->discount_type ?? 'amount';
            }
            return [
                'id' => $fdp->product_id,
                'name' => $fdp->product?->name ?? 'Unknown Product',
                'image_url' => uploaded_asset($fdp->product?->thumbnail_img),
                'price' => $fdp->product?->unit_price ?? 0,
                'formatted_price' => single_price($fdp->product?->unit_price ?? 0),
                'purchase_price' => $fdp->product?->lastPurchaseOrderItem?->price ?? 0,
                'formatted_purchase_price' => single_price($fdp->product?->lastPurchaseOrderItem?->price ?? 0),
                'discount' => $discount,
                'discount_type' => $discountType,
                'qty' => $fdp->quantity ?? 0,
                'stock_qty' => $currentStock
            ];
        })->toArray();
    @endphp

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ 'Edit Flash Deal' }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('flash_deals.update', $flashDeal->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Title --}}
                        <div class="form-group row">
                            <label class="col-sm-3 control-label" for="name">{{ 'Title' }}</label>
                            <div class="col-sm-9">
                                <input type="text" placeholder="{{ 'Title' }}" id="name" name="title"
                                    class="form-control" value="{{ $flashDeal->title }}" required>
                            </div>
                        </div>

                        {{-- Background Color --}}
                        <div class="form-group row">
                            <label class="col-sm-3 control-label" for="background_color">
                                {{ 'Background Color' }} <small>(Hexa-code)</small>
                            </label>
                            <div class="col-sm-9">
                                <input type="text" placeholder="{{ '#FFFFFF' }}" id="background_color"
                                    name="background_color" class="form-control"
                                    value="{{ $flashDeal->background_color }}" required>
                            </div>
                        </div>

                        {{-- Text Color --}}
                        <div class="form-group row">
                            <label class="col-lg-3 control-label" for="text_color">{{ 'Text Color' }}</label>
                            <div class="col-lg-9">
                                <select name="text_color" id="text_color" class="form-control aiz-selectpicker" required>
                                    <option value="">{{ 'Select One' }}</option>
                                    <option value="white" {{ $flashDeal->text_color == 'white' ? 'selected' : '' }}>
                                        {{ 'White' }}
                                    </option>
                                    <option value="dark" {{ $flashDeal->text_color == 'dark' ? 'selected' : '' }}>
                                        {{ 'Dark' }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{-- Banner --}}
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">
                                {{ 'Banner' }} <small>(1920x500)</small>
                            </label>
                            <div class="col-md-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ 'Browse' }}
                                        </div>
                                    </div>
                                    <div class="form-control file-amount">{{ 'Choose File' }}</div>
                                    <input type="hidden" name="banner" class="selected-files"
                                        value="{{ $flashDeal->banner }}">
                                </div>
                                <div class="file-preview box sm">
                                    @if($flashDeal->banner)
                                        <div class="d-flex justify-content-between align-items-center mt-2 file-preview-item"
                                            data-id="{{ $flashDeal->banner }}">
                                            <div class="align-items-center align-self-stretch d-flex justify-content-center thumb">
                                                <img src="{{ uploaded_asset($flashDeal->banner) }}" class="img-fit">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <span class="small text-muted">
                                    {{ 'This image is shown as cover banner in flash deal details page.' }}
                                </span>
                            </div>
                        </div>

                        {{-- Desktop Banner --}}
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">
                                {{ 'Desktop Banner' }} <small>(1920x500)</small>
                            </label>
                            <div class="col-md-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ 'Browse' }}
                                        </div>
                                    </div>
                                    <div class="form-control file-amount">{{ 'Choose File' }}</div>
                                    <input type="hidden" name="desktopBanner" class="selected-files"
                                        value="{{ $flashDeal->desktop_banner }}">
                                </div>
                                <div class="file-preview box sm">
                                    @if($flashDeal->desktop_banner)
                                        <div class="d-flex justify-content-between align-items-center mt-2 file-preview-item"
                                            data-id="{{ $flashDeal->desktop_banner }}">
                                            <div class="align-items-center align-self-stretch d-flex justify-content-center thumb">
                                                <img src="{{ uploaded_asset($flashDeal->desktop_banner) }}" class="img-fit">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <span class="small text-muted">
                                    {{ 'This image is shown as cover banner in flash deal details page in desktop view.' }}
                                </span>
                            </div>
                        </div>

                        {{-- Date Range --}}
                        <div class="form-group row">
                            <label class="col-sm-3 control-label" for="start_date">{{ 'Date' }}</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control aiz-date-range" name="date_range"
                                    placeholder="Select Date" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss"
                                    data-separator=" to " autocomplete="off"
                                    value="{{ date('d-m-Y H:i:s', $flashDeal->start_date) }} to {{ date('d-m-Y H:i:s', $flashDeal->end_date) }}"
                                    required>
                            </div>
                        </div>

                        {{-- Product Selection with AJAX Search --}}
                        <div class="form-group row mb-3">
                            <label class="col-sm-3 control-label" for="products">{{ 'Products' }}</label>
                            <div class="col-sm-9">
                                <select id="products" class="form-control aiz-selectpicker"
                                    multiple
                                    data-placeholder="{{ 'Search and select products...' }}"
                                    data-live-search="true"
                                    data-selected-text-format="count > 2">
                                </select>
                                <small class="text-muted">Type to search for products. You can select multiple products at once.</small>
                            </div>
                        </div>

                        {{-- Warning Alert --}}
                        <div class="alert alert-danger">
                            {{ 'If any product has discount or exists in another flash deal, the discount will be replaced by this discount & time limit.' }}
                        </div>

                        {{-- Products Table --}}
                        <div class="form-group mt-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="20%">Product</th>
                                        <th width="15%" class="text-center">Price</th>
                                        <th width="10%">Discount</th>
                                        <th width="10%">Discount Type</th>
                                        <th width="15%" class="text-center">Offer Price</th>
                                        <th width="10%" class="text-center">
                                            Quantity
                                            @include('components.tooltip', [
                                                'title' => 'Make sure the quantity does not exceed the current stock quantity.',
                                            ])
                                        </th>
                                        <th width="5%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="products_table"></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7" class="p-0">
                                            <span class="d-block alert alert-warning mb-0">
                                                <i class="las la-exclamation-triangle"></i>
                                                &nbsp;
                                                If any of those products added here has quantity more than current stock, then automatically the quantity will be adjusted to available stock or removed  if stock is insufficient.
                                            </span>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Submit Button --}}
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-primary">{{ 'Update' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        (function() {
            'use strict';

            // Configuration
            const CONFIG = {
                ajaxSearchUrl: '{{ route('ajax.products.search') }}',
                checkDealUrl: '{{ route('flash_deals.is_exist_in_any_deals', ':id') }}',
                searchDelay: 300,
                pageSize: 20,
                minSearchLength: 2,
                currentDealId: {{ $flashDeal->id }}
            };

            // Existing products from server
            const existingProducts = @json($existingProducts);

            // State Management
            const state = {
                selectedProducts: new Map(), // id => product data
                availableProducts: new Map(), // id => product data from search
                productsMap: {}, // id => product data
                searchTimeout: null,
                isProcessing: false
            };

            // DOM Cache
            const DOM = {
                productsSelect: null,
                productsTable: null
            };

            // Initialize DOM references
            function initDOM() {
                DOM.productsSelect = $('#products');
                DOM.productsTable = $('#products_table');
            }

            // Templates
            const templates = {
                loadingRow: () => `
                    <tr id="loading_row">
                        <td colspan="6" class="text-center py-4">
                            <i class="las la-spinner la-spin la-3x"></i>
                        </td>
                    </tr>`,

                emptyRow: () => `
                    <tr class="empty-row">
                        <td colspan="6" class="text-center py-4">
                            <span class="text-muted fs-18">
                                <i class="las la-frown"></i> No products selected.
                            </span>
                        </td>
                    </tr>`,

                productRow: (product) => `
                    <tr id="product-row-${product.id}" data-product-id='${product.id}'>
                        <input type="hidden" name="products[]" value="${product.id}">
                        <td>
                            <div class="from-group row">
                                <div class="col-auto">
                                    <img class="size-40px img-fit" src="${product.image_url}" alt="${product.name}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                </div>
                                <div class="col">
                                    <span>${product.name}</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="d-block text-info font-weight-bold">Purchase Price: ${product.formatted_purchase_price ?? product.purchase_price ?? 0}</span>
                            <span class="d-block base-price">Base Price: ${product.formatted_price ?? product.price ?? 0}</span>
                        </td>
                        <td>
                            <input type="number" name="discounts[]" value="${product.discount ?? 0}" min="0" step="1" class="form-control form-control-sm discount-input" required>
                        </td>
                        <td>
                            <select class="form-control form-control-sm discount-type-select" name="discount_types[]">
                                <option value="amount" ${product.discount_type === 'amount' ? 'selected' : ''}>Flat</option>
                                <option value="percent" ${product.discount_type === 'percent' ? 'selected' : ''}>Percent</option>
                            </select>
                        </td>
                        <td class="text-center offer-price-cell">
                            ${UI.getOfferPrice(product)}
                        </td>
                        <td>
                            <input type="number" name="quantities[]"
                                value="${product.qty ?? 0}" min="0" step="1" data-max="${Math.max((product.stock_qty ?? product.qty ?? 0), 0)}" class="form-control form-control-sm product-quantity ${(product.qty ?? 0) > (product.stock_qty ?? 0) ? 'border-danger text-danger' : ''}" required>
                            <small class="text-muted font-weight-bold ${(product.stock_qty ?? 0) <= 0 ? 'text-danger' : ''}">Current Stock: ${product.stock_qty ?? product.qty ?? 0}</small>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger btn-icon remove-product-btn"
                                data-product="${product.id}">
                                <i class="las la-trash"></i>
                            </button>
                        </td>
                        <input type="hidden" name="current_stocks[]" value="${Math.max((product.stock_qty ?? product.qty ?? 0), 0)}">
                    </tr>`
            };

            // UI Helpers
            const UI = {
                showTableLoading() {
                    DOM.productsTable.find('#loading_row').remove();
                    DOM.productsTable.append(templates.loadingRow());
                },

                hideTableLoading() {
                    DOM.productsTable.find('#loading_row').remove();
                },

                showEmptyState() {
                    this.hideTableLoading();
                    if (DOM.productsTable.find('tr:not(.empty-row)').length === 0) {
                        DOM.productsTable.html(templates.emptyRow());
                    }
                },

                updateSelectOptions(products) {
                    // Clear existing options except selected ones
                    const selectedIds = DOM.productsSelect.val() || [];
                    DOM.productsSelect.find('option').each(function() {
                        const optionId = $(this).val();
                        if (!selectedIds.includes(optionId)) {
                            $(this).remove();
                        }
                    });

                    // Add new options
                    products.forEach(product => {
                        // Skip if already in table or already in select
                        if (state.selectedProducts.has(product.id.toString()) ||
                            DOM.productsSelect.find(`option[value="${product.id}"]`).length > 0) {
                            return;
                        }

                        // Store product data
                        state.availableProducts.set(product.id.toString(), product);

                        const option = new Option(product.name + ` (Stock: ${product.qty})`, product.id, false, false);
                        DOM.productsSelect.append(option);
                    });

                    DOM.productsSelect.selectpicker('refresh');
                },

                disableSelect() {
                    DOM.productsSelect.prop('disabled', true).selectpicker('refresh');
                },

                enableSelect() {
                    DOM.productsSelect.prop('disabled', false).selectpicker('refresh');
                },

                getOfferPrice(product) {
                    let price = product.price ?? 0;
                    let purchasePrice = product.purchase_price ?? 0;
                    let profit = 0;
                    let profitInPercent = 0;

                    if (product.discount && product.discount_type) {
                        if (product.discount_type === 'amount') {
                            price = Math.max(price - product.discount, 0);
                        } else if (product.discount_type === 'percent') {
                            price = Math.max(price - (price * product.discount / 100), 0);
                        }
                    }

                    price = price.toFixed(2);
                    profit = (price - purchasePrice).toFixed(2);
                    profitInPercent = ((profit / purchasePrice) * 100).toFixed(2);
                    if (isNaN(profitInPercent) || !isFinite(profitInPercent)) {
                        profitInPercent = 0;
                    }
                    return `<span class="d-block font-weight-bold ${price < purchasePrice ? 'text-danger' : 'text-success'}">
                                Offer Price: ৳${price}
                            </span>
                            <span class="d-block font-weight-bold fs-10 ${price < purchasePrice ? 'text-danger' : 'text-success'}">
                                ${price < purchasePrice ? 'Loss' : 'Profit'}: ৳${profit} (${profitInPercent}%)
                            </span>`;
                }
            };

            // Alert Messages
            const alerts = {
                productInAnotherDeal(dealTitle, productName) {
                    return Swal.fire({
                        title: "Product Already in Another Deal!",
                        html: `<strong>${productName}</strong> is currently part of '<strong>${dealTitle}</strong>' deal. Adding it here will automatically remove it from the existing deal. Do you want to continue?`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, Continue!",
                        cancelButtonText: "Cancel"
                    });
                },

                productAlreadyAdded(productName) {
                    return Swal.fire({
                        title: "Already Added!",
                        text: `${productName} is already in the flash deal.`,
                        icon: "info",
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "OK"
                    });
                },

                insufficientStock(maxQty) {
                    return Swal.fire({
                        title: "Insufficient Stock!",
                        text: `You cannot set quantity more than current stock (${maxQty}).`,
                        icon: "error",
                        showConfirmButton: false,
                        timer: 2000
                    });
                },

                invalidDiscount() {
                    return Swal.fire({
                        title: "Invalid Discount!",
                        text: `Please enter a valid discount value.`,
                        icon: "error",
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            };

            // API Calls
            async function searchProducts(query, page = 1) {
                try {
                    const response = await $.ajax({
                        url: CONFIG.ajaxSearchUrl,
                        type: 'GET',
                        data: {
                            q: query,
                            limit: CONFIG.pageSize,
                            page: page
                        }
                    });

                    return {
                        products: response.products || [],
                        hasMore: !!response.next
                    };
                } catch (error) {
                    console.error('Error searching products:', error);
                    return { products: [], hasMore: false };
                }
            }

            async function checkProductInDeal(productId) {
                if (!productId) return false;

                try {
                    const response = await $.ajax({
                        url: CONFIG.checkDealUrl.replace(':id', productId),
                        type: 'GET',
                        data: {
                            exclude_deal_id: CONFIG.currentDealId // Exclude current deal from check
                        }
                    });

                    return response.success && response.exist ? response.title : false;
                } catch (error) {
                    console.error('Error checking deal existence:', error);
                    return false;
                }
            }

            // Product Management
            async function addProductToTable(product, animate = true) {
                if (state.selectedProducts.has(product.id.toString())) {
                    return false;
                }

                DOM.productsTable.find('.empty-row').remove();

                // store product data for later use (do not inject object into template)
                state.productsMap[product.id] = product;

                const $row = $(templates.productRow(product));

                if (animate) {
                    $row.hide();
                }

                DOM.productsTable.append($row);

                if (animate) {
                    await new Promise(resolve => {
                        $row.fadeIn(300, function() {
                            $(this).find('.aiz-selectpicker').selectpicker();
                            resolve();
                        });
                    });
                } else {
                    $row.find('.aiz-selectpicker').selectpicker();
                }

                state.selectedProducts.set(product.id.toString(), product);
                return true;
            }

            async function removeProductFromTable(productId) {
                const $row = $(`#product-row-${productId}`);

                await new Promise(resolve => {
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        state.selectedProducts.delete(productId.toString());
                        resolve();
                    });
                });

                if (DOM.productsTable.find('tr:not(#loading_row):not(.empty-row)').length === 0) {
                    UI.showEmptyState();
                }
            }

            // Event Handlers
            async function handleProductSelection(e) {
                if (state.isProcessing) return;

                const selectedIds = DOM.productsSelect.val() || [];
                const currentTableIds = Array.from(state.selectedProducts.keys());

                // Find newly selected and deselected
                const newlySelected = selectedIds.filter(id => !currentTableIds.includes(id));
                const deselected = currentTableIds.filter(id => !selectedIds.includes(id));

                // Handle deselection first
                if (deselected.length > 0) {
                    for (const id of deselected) {
                        await removeProductFromTable(id);
                    }
                    return;
                }

                // Handle new selection
                if (newlySelected.length === 0) return;

                state.isProcessing = true;
                UI.disableSelect();

                try {
                    // Process each product individually
                    for (const id of newlySelected) {
                        const product = state.availableProducts.get(id);

                        if (!product) {
                            console.warn(`Product ${id} not found in available products`);
                            continue;
                        }

                        // Check if this product exists in another deal (excluding current deal)
                        const dealTitle = await checkProductInDeal(id);

                        if (dealTitle) {
                            const result = await alerts.productInAnotherDeal(dealTitle, product.name);

                            if (!result.isConfirmed) {
                                // Remove this product from selection
                                const updatedSelection = selectedIds.filter(selectedId => selectedId !== id);
                                DOM.productsSelect.val(updatedSelection);
                                DOM.productsSelect.selectpicker('refresh');
                                continue;
                            }
                        }

                        // Add product to table
                        UI.showTableLoading();
                        await addProductToTable(product);
                        UI.hideTableLoading();

                        // Remove from available pool
                        state.availableProducts.delete(id);
                    }

                } finally {
                    state.isProcessing = false;
                    UI.enableSelect();
                }
            }

            async function handleProductRemoval(e) {
                const productId = $(e.currentTarget).data('product').toString();

                // Remove from table
                await removeProductFromTable(productId);

                // Remove from select
                const currentValues = DOM.productsSelect.val() || [];
                const newValues = currentValues.filter(id => id !== productId);
                DOM.productsSelect.val(newValues).selectpicker('refresh');
            }

            async function handleQuantityChange(e) {
                const $input = $(e.target);
                const max = parseInt($input.data('max'), 10);
                const value = parseInt($input.val(), 10);

                if (value > max) {
                    $input.val(max).removeClass('border-danger text-danger');
                    await alerts.insufficientStock(max);
                }
            }

            async function handleDiscountFocusOut(e) {
                const $input = $(e.target);
                const value = parseFloat($input.val());

                if (isNaN(value) || value < 0) {
                    $input.val(0);
                    await alerts.invalidDiscount();
                }
            }

            // Recalculate offer price when discount or discount type changes
            function handleDiscountChange(e) {
                const $el = $(e.target);
                const $row = $el.closest('tr');
                const productId = $row.data('product-id').toString();

                // Get original product data stored during row render
                const original = state.productsMap[productId];
                if (!original) return;

                // Read current discount values from inputs in the row
                const discountVal = parseFloat($row.find('.discount-input').val()) || 0;
                const discountType = $row.find('.discount-type-select').val() || 'amount';

                // Create a shallow copy and apply updated discount info for rendering
                const updated = Object.assign({}, original, {
                    discount: discountVal,
                    discount_type: discountType
                });

                // Recompute offer price HTML and update the cell
                const offerHtml = UI.getOfferPrice(updated);
                $row.find('.offer-price-cell').html(offerHtml);
            }

            async function handleSearch(e) {
                const query = $(e.target).val().trim();

                if (state.searchTimeout) {
                    clearTimeout(state.searchTimeout);
                }

                state.searchTimeout = setTimeout(async () => {
                    if (query.length < CONFIG.minSearchLength) {
                        // Load initial products if search is cleared
                        const result = await searchProducts('', 1);
                        UI.updateSelectOptions(result.products);
                        return;
                    }

                    const result = await searchProducts(query, 1);
                    UI.updateSelectOptions(result.products);
                }, CONFIG.searchDelay);
            }

            // Load existing products into table and select
            async function loadExistingProducts() {
                if (existingProducts.length === 0) {
                    UI.showEmptyState();
                    return;
                }

                UI.showTableLoading();

                // Add products to table
                for (const product of existingProducts) {
                    await addProductToTable(product, false);
                }

                UI.hideTableLoading();

                // Add existing products to select dropdown as selected options
                const selectedIds = [];
                existingProducts.forEach(product => {
                    const option = new Option(product.name + ` (Stock: ${product.qty})`, product.id, true, true);
                    DOM.productsSelect.append(option);
                    selectedIds.push(product.id.toString());

                    // Store in available products for reference
                    state.availableProducts.set(product.id.toString(), product);
                });

                // Set the selected values
                DOM.productsSelect.val(selectedIds);
            }

            // Initialize
            async function init() {
                initDOM();

                // Initialize select first (without products)
                DOM.productsSelect.selectpicker({
                    liveSearch: true,
                    liveSearchPlaceholder: 'Type to search products...',
                    noneResultsText: 'No products found. Try a different search term.',
                    selectedTextFormat: 'count > 2',
                    countSelectedText: function(numSelected) {
                        return numSelected + ' products selected';
                    }
                });

                // Load existing products (will add to table and select)
                await loadExistingProducts();

                // Refresh selectpicker to show selected products
                DOM.productsSelect.selectpicker('refresh');

                // Load initial search products
                const result = await searchProducts('', 1);
                UI.updateSelectOptions(result.products);

                // Listen to search input
                const $dropdown = DOM.productsSelect.parent().find('.dropdown-menu');
                $dropdown.on('input', '.bs-searchbox input', handleSearch);

                // Listen to selection changes
                DOM.productsSelect.on('changed.bs.select', handleProductSelection);

                // Event listeners
                DOM.productsTable.on('click', '.remove-product-btn', handleProductRemoval);

                // Quantity change listener
                DOM.productsTable.on('input', '.product-quantity', handleQuantityChange);

                // Discount change listener
                DOM.productsTable.on('focusout', '.discount-input', handleDiscountFocusOut);

                DOM.productsTable.on('input', '.discount-input', handleDiscountChange);
                DOM.productsTable.on('change', '.discount-type-select', handleDiscountChange);
            }

            // Run on document ready
            $(document).ready(init);
        })();
    </script>
@endsection
