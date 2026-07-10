<!-- Get All Categories -->
<div class="endpoint section" id="all-categories">
    <h2><span class="section-count">3</span>. Get All Categories</h2>
    <div class="description">
        <p>
            <strong>Description:</strong> This API retrieves a list
            of all product categories available in the system.
        </p>
        <p>
            <strong>Usage:</strong> Include the <code>App-ID</code> and <code>Secret-Key</code> in the request headers for authentication with each request. The response will include a paginated list of categories.
        </p>
        <p>
            <strong>Note:</strong> Pagination is supported. Use
            query parameters like <code>page</code> and
            <code>per_page</code> to control the response size. Maximum response size is 100.
        </p>
    </div>
    <p>
        <strong>Endpoint:</strong>
        <code><span class="fw-bold get">GET</span> <?='{{Base-Url}}'?>/categories</code>
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
                <tr>
                    <td><code>Content-Type</code></td>
                    <td>String</td>
                    <td>
                        <code>application/json</code>
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
    "message": "Category fetched successfully",
    "data": [
        {
            "id": 100,
            "name": "Beauty & Skincare",
            "slug": "beauty-skincareproducts-bangladesh",
            "sub_category": [
                {
                    "id": 77,
                    "name": "Bath & Body",
                    "slug": "cleanse-barsoap-zkfhl",
                    "sub_sub_category": [
                        {
                            "id": 24,
                            "name": "Body Lotion/Oil",
                            "slug": "body-lotion-dzkym",
                            "sub_sub_category": []
                        }
                    ]
                }
            ]
        },
        {
            "id": 24,
            "name": "Body Lotion/Oil",
            "slug": "best-body-lotion-oil-moisturized-glowing-skin",
            "sub_category": []
        }
    ],
    "pagination": {
        "total": 2,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1
    }
}</pre>
    </div>
</div>
