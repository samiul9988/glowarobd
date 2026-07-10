<!-- Place Order -->
<div class="endpoint section" id="place-order">
    <h2><span class="section-count">7</span>. Place Order</h2>
    <div class="description">
        <p>
            <strong>Description:</strong> This API allows you to place a new order.
        </p>
        <p>
            <strong>Usage:</strong> Include the <code>App-ID</code> and <code>Secret-Key</code> in the request headers. The response will include the order details.
        </p>
    </div>
    <p>
        <strong>Endpoint:</strong>
        <code><span class="fw-bold post">POST</span> <?='{{base_url}}'?>/orders/store</code>
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
    </div>
    <p>
        <strong>Sample Request:</strong>
        <code><span class="fw-bold post">POST</span> <?='{{base_url}}'?>/orders/store</code>
    </p>
    <p><strong>Payload:</strong></p>
    <div class="response"  style="max-height: 400px; overflow-y:scroll;">
        <pre>
{
    "platform_order_id": 244068,
    "order_source": "app",
    "platform_source": "__merchant_name__",
    "shipping_address": {
        "name": "keya",
        "email": null,
        "address": "Halishahar.,Ananda Bazar. budha Gazi moshjid 4215",
        "city_id": 2,
        "state": "Chittagong",
        "zone_id": 1027,
        "city": "Bondor",
        "area_id": 0,
        "area": null,
        "phone": null
    },
    "payment_type": "cash_on_delivery",
    "delivery_status": "processing",
    "date": "{{ now()->format('Y-m-d H:i:s') }}",
    "note": null,
    "shipping_cost": 60,
    "order_details": [
        {
            "supplier": "glowaro",
            "supplier_product_id": 401,
            "quantity": 1,
            "wholesale_price": 2200,
            "mrp_price": 3100
        }
    ],
    "grand_total": 2260
}
</pre>
    </div>

    <p class="mt-3"><strong>Response:</strong></p>
    <div class="response"  style="max-height: 400px; overflow-y:scroll;">
        <pre>
{
    "success": true,
    "message": "Order created successfully",
    "order_id": 12345
}
</pre>
    </div>

    <p class="mt-3">
        <strong>Status Codes:</strong>
        <ul>
            <li><code>201</code> - Order created successfully.</li>
            <li><code>401</code> - Unauthorized.</li>
            <li><code>406</code> - Requested quantity is not available for one or more products.</li>
            <li><code>409</code> - Order already exists.</li>
            <li><code>422</code> - Order details not found.</li>
            <li><code>500</code> - Internal server error.</li>
        </ul>
    </p>
</div>
