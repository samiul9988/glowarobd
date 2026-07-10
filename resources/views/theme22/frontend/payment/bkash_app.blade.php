<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>bKash Payment</title>
    <link rel="stylesheet" href="{{ static_asset('assets/css/vendors.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/css/aiz-core.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/' . str_replace('.', '', config('app.theme')) . '/frontend/css/custom-style.css') }}">
    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }

        .payment-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }

        .payment-card {
            background: white;
            border-radius: 12px;
            padding: 40px 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .payment-logo {
            margin-bottom: 30px;
        }

        .payment-logo h2 {
            color: #e2136e;
            font-size: 28px;
            font-weight: bold;
            margin: 0;
        }

        .payment-info {
            margin-bottom: 30px;
        }

        .payment-info p {
            color: #666;
            font-size: 16px;
            margin: 10px 0;
        }

        .payment-amount {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
        }

        .loading-spinner {
            display: inline-block;
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #e2136e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px 0;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            color: #666;
            font-size: 16px;
            margin-top: 15px;
        }

        #bKash_button {
            display: none !important;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .payment-container {
                padding: 15px;
            }

            .payment-card {
                padding: 30px 15px;
                border-radius: 8px;
            }

            .payment-logo h2 {
                font-size: 24px;
            }

            .payment-info p {
                font-size: 14px;
            }

            .payment-amount {
                font-size: 28px;
            }

            .loading-spinner {
                width: 40px;
                height: 40px;
                border-width: 3px;
            }

            .loading-text {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .payment-card {
                padding: 25px 12px;
            }

            .payment-logo h2 {
                font-size: 20px;
            }

            .payment-amount {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="payment-container">
        <div class="payment-card">
            <div class="payment-logo">
                <h2>bKash Payment</h2>
            </div>
            <div class="payment-info">
                <p>Please wait while we process your payment</p>
                <div class="payment-amount">৳ {{ number_format($amount, 2) }}</div>
            </div>
            <div class="loading-spinner"></div>
            <div class="loading-text">Redirecting to bKash...</div>
        </div>
    </div>

    <button id="bKash_button" class="d-none">Pay With bKash</button>

    <!-- SCRIPTS -->
    <script src="{{ static_asset('assets/js/vendors.js') }}"></script>

    @if (get_setting('bkash_sandbox') == 1)
        <script src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js"></script>
    @else
        <script src="https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js"></script>
    @endif

    <script type="text/javascript">
        $(document).ready(function() {
            $('#bKash_button').trigger('click');
        });

        var paymentID = '';
        bKash.init({
            paymentMode: 'checkout', //fixed value ‘checkout’
            //paymentRequest format: {amount: AMOUNT, intent: INTENT}
            //intent options
            //1) ‘sale’ – immediate transaction (2 API calls)
            //2) ‘authorization’ – deferred transaction (3 API calls)
            paymentRequest: {
                amount: '{{ $amount }}', //max two decimal points allowed
                intent: 'sale'
            },
            createRequest: function(
            request) { //request object is basically the paymentRequest object, automatically pushed by the script in createRequest method
                $.ajax({
                    url: '{{ route('api.bkash.checkout', ['token' => $token, 'amount' => $amount, 'order_id' => $order_id ?? null]) }}',
                    type: 'POST',
                    contentType: 'application/json',
                    success: function(data) {
                        data = JSON.parse(data);
                        if (data && data.paymentID != null) {
                            paymentID = data.paymentID;
                            bKash.create().onSuccess(data); //pass the whole response data in bKash.create().onSucess() method as a parameter
                        } else {
                            // alert(data.errorMessage);
                            bKash.create().onError();
                        }
                    },
                    error: function() {
                        bKash.create().onError();
                    }
                });
            },
            executeRequestOnAuthorization: function() {
                let orderId = '{{ $order_id ?? '' }}';
                $.ajax({
                    url: '{{ route('api.bkash.execute', $token) }}',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        "paymentID": paymentID
                    }),
                    success: function(data) {
                        var result = JSON.parse(data);
                        if (result && result.paymentID != null) {
                            window.location.href = "{{ route('api.bkash.success') }}?payment_details=" + data + "&oid=" + orderId;
                        } else {
                            // alert(result.errorMessage);
                            bKash.execute().onError();
                            window.location.href = "{{ route('api.bkash.fail') }}?oid=" + orderId;
                        }
                    },
                    error: function() {
                        bKash.execute().onError();
                        window.location.href = "{{ route('api.bkash.fail') }}?oid=" + orderId;
                    }
                });
            },
            onClose: function() {
                let orderId = '{{ $order_id ?? '' }}';
                window.location.href = "{{ route('api.bkash.fail') }}?oid=" + orderId;
            }
        });
    </script>
</body>

</html>
