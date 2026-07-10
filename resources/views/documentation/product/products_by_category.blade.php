<!-- Get Products by Category -->
<div class="endpoint section" id="products-by-category">
    <h2><span class="section-count">5</span>. Get Products by Category</h2>
    <div class="description">
        <p>
            <strong>Description:</strong> This API retrieves a list
            of products belonging to a specific category.
        </p>
        <p>
            <strong>Usage:</strong> Include the <code>App-ID</code> and <code>Secret-Key</code> in the request headers and specify the
            category slug in the URL. The response will include a paginated list of products.
        </p>

        <p>
            <strong>Note:</strong> Pagination is supported. Use
            query parameters like <code>page</code> and
            <code>per_page</code> to control the response size.
        </p>
    </div>
    <p>
        <strong>Endpoint:</strong>
        <code><span class="fw-bold get">GET</span> <?='{{base_url}}'?>/categories/{slug}</code>
    </p>
    <div class="parameters">
        <h5>Request Headers:</h5>
        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Required</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>App-ID</code></td>
                    <td>String</td>
                    <td>
                        <code>{{ $user->app_id }}</code>
                    </td>
                    <td>Yes</td>
                </tr>
                <tr>
                    <td><code>Secret-Key</code></td>
                    <td>String</td>
                    <td>
                        <code>{{ limit_text($user->app_key) }}</code>
                    </td>
                    <td>Yes</td>
                </tr>
            </tbody>
        </table>
        <pre>

        </pre>
        <h5>URL Parameters:</h5>
        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Required</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>slug</code></td>
                    <td>String</td>
                    <td>
                        Category slug (e.g.,
                        <code>beauty-skincareproducts-bangladesh</code>)
                    </td>
                    <td>Yes</td>
                </tr>
            </tbody>
        </table>
        <pre>

        </pre>
        <h5>Query Parameters:</h5>
        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Required</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>page</code></td>
                    <td>Integer</td>
                    <td>Page number (e.g., 1)</td>
                    <td>No</td>
                </tr>
                <tr>
                    <td><code>per_page</code></td>
                    <td>Integer</td>
                    <td>Number of items per page (e.g., 10)</td>
                    <td>No</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p><strong>Response:</strong></p>
    <div class="response"  style="max-height: 400px; overflow-y:scroll;">
        <pre>
{
    "success": true,
    "message": "Products retrieved for category `Health & Fitness`",
    "data": [
        {
            "id": 957,
            "name": "Axe Brand Universal Oil for Quick Relief of Cold & Headache",
            "added_by": "admin",
            "user_id": 9,
            "category_id": 74,
            "brand_id": 163,
            "photos": "3794",
            "thumbnail_img": "3794",
            "video_provider": "youtube",
            "video_link": null,
            "video_aspect_ratio": null,
            "tags": "",
            "description": null,
            "unit_price": 295,
            "purchase_price": null,
            "variant_product": 0,
            "attributes": "[]",
            "choice_options": "[]",
            "colors": "[]",
            "variations": null,
            "todays_deal": 0,
            "published": 1,
            "approved": 1,
            "stock_visibility_state": "quantity",
            "cash_on_delivery": 1,
            "featured": 0,
            "seller_featured": 0,
            "current_stock": 0,
            "unit": "Pcs",
            "min_qty": 1,
            "max_qty": 0,
            "low_stock_quantity": 1,
            "discount": 0,
            "min_order_amount": 500,
            "discount_type": "amount",
            "discount_start_date": null,
            "discount_end_date": null,
            "tax": null,
            "tax_type": null,
            "shipping_type": null,
            "shipping_cost": null,
            "is_quantity_multiplied": 0,
            "est_shipping_days": null,
            "num_of_sale": 0,
            "meta_title": "Axe Brand Universal Oil for Quick Relief of Cold & Headache",
            "meta_description": "",
            "meta_img": "3794",
            "pdf": null,
            "slug": "axe-brand-universal-oil-for-quick-relief-of-cold--headache",
            "rating": 0,
            "barcode": null,
            "digital": 0,
            "auction_product": 0,
            "file_name": null,
            "file_path": null,
            "external_link": null,
            "external_link_btn": null,
            "wholesale_product": 0,
            "created_at": "2024-01-28T11:00:01.000000Z",
            "updated_at": "2024-01-29T07:01:48.000000Z",
            "short_description": null,
            "subscription": 0,
            "pre_order": 1,
            "preorder_start_date": null,
            "preorder_end_date": null,
            "preorder_max_qty": 1,
            "note": null,
            "app_discount": 10,
            "app_discount_type": "amount",
            "app_discount_start_date": 1704045600,
            "app_discount_end_date": 1735667940,
            "app_price": 295,
            "web_price": 295,
            "product_translations": [
                {
                    "id": 991,
                    "product_id": 957,
                    "name": "Axe Brand Universal Oil for Quick Relief of Cold & Headache",
                    "unit": "Pcs",
                    "description": null,
                    "lang": "en",
                    "created_at": "2024-01-28T11:00:02.000000Z",
                    "updated_at": "2024-01-28T11:00:02.000000Z",
                    "short_description": null
                }
            ],
            "taxes": [],
            "stocks": [
                {
                    "id": 3224,
                    "product_id": 957,
                    "variant": "",
                    "sku": null,
                    "price": 295,
                    "qty": 0,
                    "image": null,
                    "created_at": "2024-01-28T11:00:02.000000Z",
                    "updated_at": "2024-01-29T07:04:54.000000Z"
                }
            ]
        }
    ],
    "pagination": {
        "total": 1,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1
    }
}
// or
{
    "success": false,
    "message": "Category not found",
    "errors": null
}</pre>
    </div>
</div>
