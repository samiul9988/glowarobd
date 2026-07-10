@extends('backend.layouts.app')
@php
    $giftOfferItems = $giftOffer->items->map(function($item) {
        return [
            'id' => $item->product_id,
            'name' => $item->product->name ?? 'Unknown',
            'qty' => $item->product->current_stock ?? 0,
            'price' => $item->product->unit_price ?? 0,
            'image_url' => uploaded_asset($item->product->thumbnail_img) ?? static_asset('assets/img/placeholder.jpg'),
            'available_qty' => $item->available_qty,
            'offer_price' => $item->offer_price ?? 0,
            'used_qty' => $item->used_qty,
        ];
    })->toArray();

    $giftOfferConditions = $giftOffer->conditions->where('condition_type', 'product')->map(function($condition) {
        return [
            'id' => $condition->item_id,
            'name' => $condition->product->name ?? 'Unknown',
            'min_qty' => $condition->min_qty,
        ];
    })->toArray();

    // dd($giftOfferConditions);
@endphp
@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">Edit Gift Offer</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.gift_offers.update', $giftOffer->id) }}" method="POST" id="gift_offer_form">
                    @csrf

                    {{-- Title --}}
                    <div class="form-group row">
                        <label class="col-sm-3 control-label" for="title">Title <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="Enter offer title" id="title" name="title" class="form-control" value="{{ old('title', $giftOffer->title) }}" required>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="form-group row">
                        <label class="col-sm-3 control-label" for="description">Description</label>
                        <div class="col-sm-9">
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter description (optional)">{{ old('description', $giftOffer->description) }}</textarea>
                        </div>
                    </div>

                    {{-- Offer Type --}}
                    <div class="form-group row">
                        <label class="col-sm-3 control-label" for="offer_type">Offer Type <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select name="offer_type" id="offer_type" class="form-control aiz-selectpicker" required onchange="toggleConditionFields()">
                                <option value="">Select Offer Type</option>
                                <option value="product" {{ $giftOffer->offer_type == 'product' ? 'selected' : '' }}>Product Wise</option>
                                <option value="cart" {{ $giftOffer->offer_type == 'cart' ? 'selected' : '' }}>Cart Amount</option>
                            </select>
                        </div>
                    </div>

                    {{-- Cart Amount Conditions (for cart_amount type) --}}
                    <div class="form-group row" id="cart_amount_section" style="{{ $giftOffer->offer_type !== 'cart' ? 'display: none;' : '' }}">
                        <label class="col-sm-3 control-label">Cart Amount Range</label>
                        <div class="col-sm-9">
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Minimum Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="min_cart_amount" id="min_cart_amount" class="form-control" placeholder="0" step="1" min="0" value="{{ old('min_cart_amount', $giftOffer->min_cart_amount) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Product Conditions (for product type) --}}
                    <div class="form-group row" id="product_condition_section" style="{{ $giftOffer->offer_type !== 'product' ? 'display: none;' : '' }}">
                        <label class="col-sm-3 control-label">Condition Products <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select id="condition_products" class="form-control aiz-selectpicker" multiple data-placeholder="Search and select products..." data-live-search="true" data-selected-text-format="count > 2">
                            </select>
                            <small class="text-muted">Select products that must be in cart to qualify for this offer</small>
                            <div id="condition_products_table" class="mt-3"></div>
                        </div>
                    </div>

                    <hr>

                    {{-- Date Range --}}
                    <div class="form-group row">
                        <label class="col-sm-3 control-label" for="date_range">Date Range <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            @php
                                $startDate = $giftOffer->start_date ? date('d-m-Y H:i:s', $giftOffer->start_date) : '';
                                $endDate = $giftOffer->end_date ? date('d-m-Y H:i:s', $giftOffer->end_date) : '';
                                $dateRange = ($startDate && $endDate) ? $startDate . ' to ' . $endDate : '';
                            @endphp
                            <input type="text" class="form-control aiz-date-range" name="date_range" placeholder="Select Date Range" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off" value="{{ $dateRange }}" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 control-label">Max Gift Selection</label>
                        <div class="col-sm-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>
                                        Max Gift Item Per Order <span class="text-danger">*</span>
                                        @include('components.tooltip', [
                                            'title' => 'Maximum number of gift products a customer can select from this offer per order'
                                        ])
                                    </label>
                                    <input type="number" name="max_item_per_order" id="max_item_per_order" class="form-control" value="{{ old('max_item_per_order', $giftOffer->max_item_per_order) }}" min="1" required>
                                </div>
                                <div class="col-md-6">
                                    <label>
                                        Max Gift Qty Per Order <span class="text-danger">*</span>
                                        @include('components.tooltip', [
                                            'title' => 'Maximum quantity of gift products a customer can select from this offer per order'
                                        ])
                                    </label>
                                    <input type="number" name="max_qty_per_order" id="max_qty_per_order" class="form-control" value="{{ old('max_qty_per_order', $giftOffer->max_qty_per_order) }}" min="1" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Gift Products Selection --}}
                    <div class="form-group row mb-3">
                        <label class="col-sm-3 control-label" for="gift_products_select">Gift Products <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select id="gift_products_select" class="form-control aiz-selectpicker" multiple data-placeholder="Search and select gift products..." data-live-search="true" data-selected-text-format="count > 2">
                            </select>
                            <small class="text-muted">These are the FREE products customers can choose from</small>
                        </div>
                    </div>

                    {{-- Alert --}}
                    <div class="alert alert-info">
                        <i class="las la-info-circle"></i>
                        Gift products can be FREE (offer price = 0) or discounted (offer price > 0). The offer price affects cart total and shipping calculation.
                    </div>

                    {{-- Gift Products Table --}}
                    <div class="form-group mt-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="25%">Gift Product</th>
                                    <th width="8%" class="text-center">Stock</th>
                                    <th width="10%" class="text-center">
                                        Allocate Qty
                                    </th>
                                    <th width="8%" class="text-center">Used</th>
                                    <th width="12%" class="text-center">Original Price</th>
                                    <th width="12%" class="text-center">Offer Price</th>
                                    <th width="10%" class="text-center">Discount</th>
                                    <th width="10%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="gift_products_table"></tbody>
                        </table>
                    </div>

                    {{-- Submit Button --}}
                    <div class="form-group mb-0 text-right">
                        <a href="{{ route('admin.gift_offers.index') }}" class="btn btn-secondary">{{ 'Cancel' }}</a>
                        <button type="submit" class="btn btn-primary">{{ 'Update Gift Offer' }}</button>
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

        const CONFIG = {
            ajaxSearchUrl: '{{ route('ajax.products.search') }}',
            searchDelay: 300,
            pageSize: 20,
            minSearchLength: 2
        };

        const state = {
            selectedGiftProducts: new Map(),
            selectedConditionProducts: new Map(),
            // selectedConditionProducts: new Map(@json($giftOfferConditions)),
            availableProducts: new Map(),
            searchTimeout: null
        };

        const DOM = {
            giftProductsSelect: null,
            giftProductsTable: null,
            conditionProductsSelect: null,
            conditionProductsTable: null,
            giftQuantitiesInputs: null
        };

        // Existing gift items data
        const existingGiftItems = @json($giftOfferItems);

        // Existing condition products data
        const existingConditionProducts = @json($giftOfferConditions);

        function initDOM() {
            DOM.giftProductsSelect = $('#gift_products_select');
            DOM.giftProductsTable = $('#gift_products_table');
            DOM.conditionProductsSelect = $('#condition_products');
            DOM.conditionProductsTable = $('#condition_products_table');
            DOM.giftQuantitiesInputs = $('.gift-quantities');
        }

        function handleQuantityChange(e) {
            const $input = $(e.target);
            const currentStock = parseInt($input.attr('max')) || 0;
            const newQty = parseInt($input.val()) || 0;

            if (newQty > currentStock) {
                $input.val(currentStock);
                Swal.fire({
                    title: "Insufficient Stock!",
                    text: `You cannot set quantity more than current stock (${currentStock}).`,
                    icon: "error",
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        }

        function handleQuantityFocusOut(e) {
            const $input = $(e.target);
            const currentStock = parseInt($input.attr('max')) || 0;

            if ($input.val() == '') {
                $input.val(currentStock);
            }
        }

        // Search products via AJAX
        function searchProducts(query, selectElement) {
            $.ajax({
                url: CONFIG.ajaxSearchUrl,
                type: 'GET',
                data: { q: query, limit: CONFIG.pageSize },
                success: function(response) {
                    selectElement.empty();

                    const products = response.products || [];

                    if (products.length > 0) {
                        products.forEach(product => {
                            state.availableProducts.set(product.id.toString(), product);

                            const option = new Option(
                                product.name + ' (Stock: ' + (product.qty || 0) + ')',
                                product.id,
                                false,
                                false
                            );
                            $(option).data('product', product);
                            selectElement.append(option);
                        });
                    } else {
                        const option = new Option('No products found', '', true, false);
                        $(option).prop('disabled', true);
                        selectElement.append(option);
                    }

                    selectElement.selectpicker('refresh');
                },
                error: function(xhr, status, error) {
                    console.error('Error searching products:', error);
                }
            });
        }

        // Initialize product search with AJAX
        function initProductSearch(selectElement, onSelectCallback) {
            selectElement.selectpicker({
                liveSearch: true,
                liveSearchPlaceholder: 'Type to search products...',
                noneResultsText: 'Type to search...',
                selectedTextFormat: 'count > 2'
            });

            // Load initial products
            searchProducts('', selectElement);

            selectElement.on('shown.bs.select', function() {
                const dropdown = $(this).parent().find('.dropdown-menu');
                const searchInput = dropdown.find('.bs-searchbox input');

                searchInput.off('input.giftsearch').on('input.giftsearch', function() {
                    const query = $(this).val().trim();

                    clearTimeout(state.searchTimeout);

                    state.searchTimeout = setTimeout(() => {
                        searchProducts(query, selectElement);
                    }, CONFIG.searchDelay);
                });
            });

            selectElement.on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
                if (isSelected && clickedIndex !== null) {
                    const selectedOption = $(this).find('option').eq(clickedIndex);
                    const productId = selectedOption.val();

                    if (productId) {
                        const productData = state.availableProducts.get(productId.toString());

                        if (productData && onSelectCallback) {
                            onSelectCallback(productId.toString(), productData);
                        }

                        selectElement.val([]);
                        selectElement.selectpicker('refresh');
                    }
                }
            });
        }

        function addGiftProduct(productId, product) {
            if (state.selectedGiftProducts.has(productId)) {
                AIZ.plugins.notify('warning', 'Product already added');
                return;
            }

            state.selectedGiftProducts.set(productId, {
                ...product,
                available_qty: product.available_qty || Math.min(product.qty || 10, 10),
                offer_price: product.offer_price !== undefined ? product.offer_price : 0,
                used_qty: product.used_qty || 0
            });
            renderGiftProductsTable();
            AIZ.plugins.notify('success', 'Product added');
        }

        function removeGiftProduct(productId) {
            state.selectedGiftProducts.delete(productId);
            renderGiftProductsTable();
        }

        function renderGiftProductsTable() {
            DOM.giftProductsTable.empty();

            if (state.selectedGiftProducts.size === 0) {
                DOM.giftProductsTable.append(`
                    <tr id="no_gift_products">
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="las la-gift fs-30"></i><br>
                            {{ 'No gift products added yet. Search and select products above.' }}
                        </td>
                    </tr>
                `);
                return;
            }

            state.selectedGiftProducts.forEach((product, productId) => {
                const currentStock = product.qty || product.stock_qty || 0;
                const thumbnail = product.image_url;
                const originalPrice = product.price || 0;
                const availableQty = product.available_qty || 0;
                const offerPrice = product.offer_price !== undefined ? product.offer_price : 0;
                const usedQty = product.used_qty || 0;

                DOM.giftProductsTable.append(`
                    <tr data-id="${productId}">
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${thumbnail}" class="size-50px img-fit mr-2" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                <span class="text-truncate-2">${product.name}</span>
                            </div>
                            <input type="hidden" name="gift_products[]" value="${productId}">
                        </td>
                        <td class="text-center">
                            <span class="badge badge-inline badge-soft-info">${currentStock}</span>
                        </td>
                        <td class="text-center">
                            <input type="number" name="gift_quantities[]" class="form-control form-control-sm text-center gift-quantities" value="${availableQty}" min="1" max="${currentStock}" required>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-soft-warning">${usedQty}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-muted original-price" data-price="${originalPrice}">${product.formatted_price || formatPrice(originalPrice)}</span>
                        </td>
                        <td class="text-center">
                            <input type="number" name="gift_offer_prices[]" class="form-control form-control-sm text-center offer-price-input" value="${offerPrice}" min="0" max="${originalPrice}" step="1" data-product-id="${productId}" required>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-inline badge-soft-success discount-badge" data-product-id="${productId}">${calculateDiscount(originalPrice, offerPrice)}</span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-soft-danger btn-icon btn-circle btn-sm" onclick="removeGiftProductHandler('${productId}')">
                                <i class="las la-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            // Bind offer price change event
            bindOfferPriceEvents();
        }

        function calculateDiscount(originalPrice, offerPrice) {
            if (originalPrice <= 0) return '0%';
            const discount = ((originalPrice - offerPrice) / originalPrice) * 100;
            if (offerPrice == 0) return 'FREE';
            return discount.toFixed(0) + '% OFF';
        }

        function bindOfferPriceEvents() {
            $('.offer-price-input').off('input').on('input', function() {
                const productId = $(this).data('product-id');
                const offerPrice = parseFloat($(this).val()) || 0;
                const originalPrice = parseFloat($(this).closest('tr').find('.original-price').data('price')) || 0;

                // Update discount badge
                const discountBadge = $(`.discount-badge[data-product-id="${productId}"]`);
                discountBadge.text(calculateDiscount(originalPrice, offerPrice));

                // Update product state
                const product = state.selectedGiftProducts.get(productId.toString());
                if (product) {
                    product.offer_price = offerPrice;
                }
            });
        }

        function formatPrice(price) {
            return '৳' + parseFloat(price).toFixed(2);
        }

        window.toggleConditionFields = function() {
            const offerType = $('#offer_type').val();

            $('#cart_amount_section, #product_condition_section, #brand_condition_section, #category_condition_section').hide();

            switch(offerType) {
                case 'cart':
                    $('#cart_amount_section').show();
                    break;
                case 'product':
                    $('#product_condition_section').show();
                    break;
                case 'brand':
                    $('#brand_condition_section').show();
                    break;
                case 'category':
                    $('#category_condition_section').show();
                    break;
            }
        };

        window.removeGiftProductHandler = function(productId) {
            removeGiftProduct(productId);
        };

        function addConditionProduct(productId, product) {
            if (state.selectedConditionProducts.has(productId)) {
                AIZ.plugins.notify('warning', 'Product already added');
                return;
            }

            state.selectedConditionProducts.set(productId, {
                ...product,
                min_qty: 1
            });
            renderConditionProductsTable();
            AIZ.plugins.notify('success', 'Condition product added');
        }

        function loadExistingData() {
            // Load existing gift items
            existingGiftItems.forEach(item => {
                state.selectedGiftProducts.set(String(item.id), {
                    id: item.id,
                    name: item.name,
                    qty: item.qty,
                    price: item.price,
                    image_url: item.image_url,
                    available_qty: item.available_qty,
                    offer_price: item.offer_price,
                    used_qty: item.used_qty
                });
            });

            // Load existing condition products
            existingConditionProducts.forEach(item => {
                state.selectedConditionProducts.set(String(item.id), {
                    id: item.id,
                    name: item.name,
                    min_qty: item.min_qty
                });
            });
        }

        function renderConditionProductsTable() {
            if (!DOM.conditionProductsTable) return;
            DOM.conditionProductsTable.empty();

            if (state.selectedConditionProducts.size === 0) {
                return;
            }

            let html = '<table class="table table-sm table-bordered"><thead><tr><th>Product</th><th width="100">Min Qty</th><th width="60">Action</th></tr></thead><tbody>';

            state.selectedConditionProducts.forEach((product, productId) => {
                html += `
                    <tr>
                        <td>
                            ${product.name}
                            <input type="hidden" name="condition_products[]" value="${productId}">
                        </td>
                        <td>
                            <input type="number" name="condition_min_qty[]" class="form-control form-control-sm" value="${product.min_qty || 1}" min="1">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-soft-danger btn-icon btn-circle btn-sm" onclick="removeConditionProduct('${productId}')">
                                <i class="las la-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            DOM.conditionProductsTable.html(html);
        }

        window.removeConditionProduct = function(productId) {
            state.selectedConditionProducts.delete(productId);
            renderConditionProductsTable();
        };

        $(document).ready(function() {
            initDOM();
            DOM.giftProductsTable.on('input', '.gift-quantities', handleQuantityChange);
            DOM.giftProductsTable.on('focusout', '.gift-quantities', handleQuantityFocusOut);
            loadExistingData();

            initProductSearch(DOM.giftProductsSelect, addGiftProduct);
            initProductSearch(DOM.conditionProductsSelect, addConditionProduct);

            renderGiftProductsTable();
            renderConditionProductsTable();
            toggleConditionFields();
        });
    })();
</script>
@endsection
