<!-- Update Order Status -->
<div class="endpoint section" id="update-order-status">
    <h2><span class="section-count">8</span>. Update Order Status</h2>
    <div class="description">
        <p>
            <strong>Description:</strong> This API allows you to cancel an existing order.
        </p>
        <p>
            <strong>Usage:</strong> Include the <code>App-ID</code> and <code>Secret-Key</code> in the request headers. The response will include the order details.
        </p>
    </div>
    <p>
        <strong>Endpoint:</strong>
        <code><span class="fw-bold post">PUT</span> <?='{{base_url}}'?>/orders/update-status</code>
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
        <code><span class="fw-bold post">PUT</span> <?='{{base_url}}'?>/orders/update-status</code>
    </p>
    <p><strong>Payload:</strong></p>
    <div class="response"  style="max-height: 400px; overflow-y:scroll;">
        <pre>
{
    "platform_order_id": 244068,
    "delivery_status": "cancelled"
}
</pre>
    </div>

    <p class="mt-3"><strong>Response:</strong></p>
    <div class="response"  style="max-height: 400px; overflow-y:scroll;">
        <pre>
{
    "success": true,
    "message": "Order has been cancelled",
    "order_id": 12345
}
</pre>
    </div>

    <p class="mt-3">
        <strong>Status Codes:</strong>
        <ul>
            <li><code>200</code> - Order has been cancelled.</li>
            <li><code>401</code> - Unauthorized.</li>
            <li><code>404</code> - Order not found.</li>
            <li><code>422</code> - Order can not be cancelled.</li>
            <li><code>500</code> - Internal server error.</li>
        </ul>
    </p>
</div>
