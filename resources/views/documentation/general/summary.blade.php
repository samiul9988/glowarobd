<!-- Summary -->
<div class="endpoint" id="summary-token">
    <h2>Summary</h2>

    <p>This API uses an <strong>App-ID</strong> and <strong>Secret-Key</strong> for authentication and authorization. These credentials must be included in the request headers for secure access to the API.</p>

    <ul>
        <li><strong>Authorization Method:</strong> Every request must include a valid <strong>App-ID</strong> and <strong>Secret-Key</strong> in the headers for authentication.</li>
    </ul>

    <div class="parameters">
        <h5>Sandbox/Test Environment Credentials</h5>
        <table>
            <thead>
                <tr>
                    <th>Field name</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Base-Url</td>
                    <td>{{ config('app.url') }}/api/merchant/v1</td>
                </tr>
                <tr>
                    <td>App-ID</td>
                    <td><code data-value="{{ $user->app_id }}">{{ $user->app_id }} <span class="fw-bold copy" role="button" title="Copy App-ID">Copy</span></code></td>
                </tr>
                <tr>
                    <td>Secret-Key</td>
                    <td><code data-value="{{ $user->app_key }}">{{ limit_text($user->app_key) }} <span class="fw-bold copy" role="button" title="Copy Secret-Key">Copy</span></code></td>
                </tr>

            </tbody>
        </table>
    </div>
</div>
