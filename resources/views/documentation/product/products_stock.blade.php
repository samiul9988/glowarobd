<!-- Get Products Stock -->
<div class="endpoint section" id="products-stock">
    <h2><span class="section-count">6</span>. Get Products Stock</h2>
    <div class="description">
        <p>
            <strong>Description:</strong> This API retrieves a list
            of products current stock.
        </p>
        <p>
            <strong>Usage:</strong> Include the <code>App-ID</code> and <code>Secret-Key</code> in the request headers. The response will include a paginated list of products stocks.
        </p>

        <p>
            <strong>Note:</strong> Pagination is supported. Use
            query parameters like <code>page</code> and
            <code>per_page</code> to control the response size.
        </p>
    </div>
    <p>
        <strong>Endpoint:</strong>
        <code><span class="fw-bold get">GET</span> <?='{{base_url}}'?>/stocks</code>
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
                <tr>
                    <td><code>category_slugs</code></td>
                    <td>Array</td>
                    <td>Array of category slugs. (e.g., ['best-body-lotion-oil-moisturized-glowing-skin','best-cream-moisturizers-soft-hydrated-skin'])</td>
                    <td>No</td>
                </tr>
                <tr>
                    <td><code>product_ids</code></td>
                    <td>Array</td>
                    <td>Array of product IDs. Must be integer. (e.g., [1,2])</td>
                    <td>No</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p>
        <strong>Sample Request:</strong>
        <code><span class="fw-bold get">GET</span> <?='{{base_url}}'?>/stocks?category_slugs[]=best-body-lotion-oil-moisturized-glowing-skin&category_slugs[]=best-cream-moisturizers-soft-hydrated-skin&product_ids[]=14&product_ids[]=30</code>
    </p>
    <p><strong>Response:</strong></p>
    <div class="response"  style="max-height: 400px; overflow-y:scroll;">
        <pre>
{
    "success": true,
    "message": "Products stock fetched successfully",
    "data": [
        {
            "id": 14,
            "stock": 15
        },
        {
            "id": 30,
            "stock": 10
        }
    ],
    "pagination": {
        "total": 2,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1
    }
}
</pre>
    </div>
</div>
