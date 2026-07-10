<!-- Regenerate Token -->
<div class="endpoint section" id="regenerate-token">
    <h2><span class="section-count">2</span>. Regenerate Token</h2>
    <div class="description">
        <p>
            <strong>Description:</strong> This API is used to
            regenerate an authentication token. It is useful when
            the current token is about to expire or has already
            expired.
        </p>
        <p>
            <strong>Usage:</strong> Include the current valid token
            in the <code>Authorization</code> header. A new token
            will be returned.
        </p>
        <p>
            <strong>Note:</strong> The new token will also have an
            expiration time. Ensure you update your application with
            the new token.
        </p>
    </div>
    <p>
        <strong>Endpoint:</strong>
        <code><span class="fw-bold post">POST</span> /api/merchant/v1/regenerate-token</code>
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
                    <td><code>Authorization</code></td>
                    <td>String</td>
                    <td>
                        Bearer token (e.g.,
                        <code>eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...</code>)
                    </td>
                    <td>Yes</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p><strong>Response:</strong></p>
    <div class="response">
        <pre>
{
"success": true,
"message": "Token regenerated successfully."
}
// or
{
"success": false,
"message": "Invalid token",
"errors": null
}</pre>
    </div>
</div>
