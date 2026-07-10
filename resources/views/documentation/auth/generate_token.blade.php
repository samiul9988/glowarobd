
        <!-- Generate Token -->
        <div class="endpoint" id="generate-token">
            <h2><span class="section-count">1</span>. Generate Token</h2>
            <div class="description">
                <p>
                    <strong>Description:</strong> This API is used to
                    generate an authentication token for a merchant. The
                    token is required to access other protected endpoints.
                </p>
                <p>
                    <strong>Usage:</strong> Provide the merchant's email and
                    password in the request body. If the credentials are
                    valid, a token will be returned via response.
                </p>

            </div>
            <p>
                <strong>Endpoint:</strong>
                <code><span class="fw-bold post">POST</span> <?='{{base_url}}'?>/generate-token</code>
            </p>
            <div class="parameters">
                <h5>Request Body Parameters:</h5>
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
                            <td><code>email</code></td>
                            <td>String</td>
                            <td>Merchant's email address</td>
                            <td>Yes</td>
                        </tr>
                        <tr>
                            <td><code>password</code></td>
                            <td>String</td>
                            <td>Merchant's password</td>
                            <td>Yes</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p><strong>Request Body:</strong></p>
            <pre>
{
    "email": "merchant@gmail.com",
    "password": "password"
}</pre>
            <p><strong>Response:</strong></p>
            <div class="response">
                <pre>
{
    "success": true,
    "token": {
        "app_id": "{{ $user->app_id }}",
        "app_key": "{{ limit_text($user->app_key) }}"
    },
    "message": "Token generated successfully."
}
// or
{
    "success": false,
    "message": "Invalid credentials",
    "errors": null
}</pre>
            </div>
        </div>
