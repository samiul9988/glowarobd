<?php


namespace App\Http\Controllers\Api\V3;

use App\Models\User;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\CombinedOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BkashController extends Controller
{

    public function begin(Request $request)
    {
        Session::put('payment_method', 'bkash');
        $payment_type = $request->payment_type;
        $combined_order_id = $request->combined_order_id;
        $amount = round($request->amount, 2);
        $user_id = $request->user_id;

        $base_url = "https://checkout.pay.bka.sh/v1.2.0-beta/";
        if(get_setting('bkash_sandbox', 1)){
            $base_url = "https://checkout.sandbox.bka.sh/v1.2.0-beta/";
        }
        else {
            $base_url = "https://checkout.pay.bka.sh/v1.2.0-beta/";
        }


        try {
            $request_data = array('app_key' => env('BKASH_CHECKOUT_APP_KEY'), 'app_secret' => env('BKASH_CHECKOUT_APP_SECRET'));

            $url = curl_init($base_url . 'checkout/token/grant');
            $request_data_json = json_encode($request_data);

            $header = array(
                'Content-Type:application/json',
                'username:' . env('BKASH_CHECKOUT_USER_NAME'),
                'password:' . env('BKASH_CHECKOUT_PASSWORD')
            );
            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url, CURLOPT_POSTFIELDS, $request_data_json);
            curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

            $resultdata = curl_exec($url);
            curl_close($url);
            $token = json_decode($resultdata)->id_token;

            $orderId = $request->order_id ?? null;
            if($payment_type == 'cart_payment'){
                $combined_order = CombinedOrder::find($combined_order_id);
                $amount = $combined_order->grand_total;
                $orderId = Order::where('combined_order_id', $combined_order_id)->first()->id;
            }


            return response()->json([
                'token' => $token,
                'result' => true,
                'url' => route('api.bkash.webpage', ["token" => $token, "amount" => $amount, "order_id" => $orderId]),
                'message' => ('Payment page is found')
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'token' => '',
                'result' => false,
                'url' => '',
                'message' => $exception->getMessage()
            ]);
        }


    }

    public function webpage($token, $amount, $order_id = null)
    {
        $amount = round($amount, 2);
        return view('frontend.payment.bkash_app', compact('token', 'amount', 'order_id'));
    }

    public function checkout($token,$amount, $order_id = null)
    {
        $auth = $token;

        $callbackURL = route('home');

        $requestbody = array(
            'amount' => round($amount, 2),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => strval(uniqid()),
            'orderId' => $order_id
        );

        $url = '';
        if(get_setting('bkash_sandbox', 1)){
            $url = curl_init('https://checkout.sandbox.bka.sh/v1.2.0-beta/checkout/payment/create');
        }else {
            $url = curl_init('https://checkout.pay.bka.sh/v1.2.0-beta/checkout/payment/create');
        }

        $requestbodyJson = json_encode($requestbody);

        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $auth,
            'X-APP-Key:' . env('BKASH_CHECKOUT_APP_KEY')
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        curl_close($url);

        return $resultdata;
    }

    public function execute($token, Request $request)
    {
        $paymentID = $request->paymentID;
        $auth = $token;

        $url = '';
        if(get_setting('bkash_sandbox', 1)){
            $url = curl_init('https://checkout.sandbox.bka.sh/v1.2.0-beta/checkout/payment/execute/' . $paymentID);
        }else {
            $url = curl_init('https://checkout.pay.bka.sh/v1.2.0-beta/checkout/payment/execute/' . $paymentID);
        }

        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $auth,
            'X-APP-Key:' . env('BKASH_CHECKOUT_APP_KEY')
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        $resultdata = curl_exec($url);
        curl_close($url);

        return $resultdata;
    }

    public function process(Request $request)
    {
        try {

            $payment_type = $request->payment_type;

            if ($payment_type == 'cart_payment') {
                $payment = is_array($request->payment_details) ? $request->payment_details : json_decode($request->payment_details, true);
                $payment['method'] = 'sslcommerz';
                $payment['bank_type'] = 'SslCommerz Payment';
                $payment['user_id'] = Auth::guard('api')->id() ?? null;
                $payment = json_encode($payment);
                checkout_done($request->combined_order_id, $payment);
            }

            if ($payment_type == 'wallet_payment') {
                wallet_payment_done($request->user_id, $request->amount, 'Bkash', $request->payment_details, $request->combined_order_id);
            }

            $orderId = $request->oid;
            if($orderId) {
                return redirect()->away(env('FRONTEND_URL')."/order-success/{$orderId}");
            }
            return response()->json(['result' => true, 'message' => ("Payment is successful")]);


        } catch (\Exception $e) {
            $orderId = $request->oid;
            if($orderId) {
                $this->managePayment($request);
                return redirect()->away(env('FRONTEND_URL')."/order-success/{$orderId}");
            }
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }

    public function success(Request $request)
    {
        $orderId = $request->oid;
        if($orderId) {
            $this->managePayment($request);
            return redirect()->away(env('FRONTEND_URL')."/order-success/{$orderId}");
        }
        return response()->json([
            'result' => true,
            'message' => ('Payment Success'),
            'payment_details' => $request->payment_details
        ]);

    }

    private function managePayment(Request $request)
    {
        try {
            Log::channel('custom')->info('Bkash Payment Success Callback', ['request' => $request->all()]);
            $order = Order::find($request->oid);
            if($order && $order->payment_status == 'paid'){
                Log::channel('custom')->info('Bkash Payment Success Callback: Order already paid', ['order_id' => $order->id]);
                return;
            }
            $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
            $pinv = "PAY-" . date('Ymd') . ($inv_counting + 1);

            $payment_details = json_decode($request->payment_details, true);

            $amount = $payment_details['amount'] ?? 0;

            $pdetails = [
                'payment_method' => 'bkash',
                'bank_type' => 'Bkash Payment',
                'bank_info' => 'Bkash Payment',
                'payment_amount' => (float) $amount,
            ];

            DB::beginTransaction();
            $payment = new Payment;
            $payment->invoice_no = $pinv;
            $payment->date = date('Y-m-d');
            $payment->payable_id = $order->user_id ?? $order->guest_id;
            $payment->payable_type = User::class;
            $payment->reference_id = $order->id;
            $payment->reference_type = Order::class;
            $payment->seller_id = null;
            $payment->amount = (float) $amount;
            $payment->payment_details = json_encode($pdetails);
            $payment->payment_method = 'bkash';
            $payment->txn_code = null;
            $payment->user_id = Auth::guard('api')->id() ?? $order->user_id ?? null;
            $payment->remarks = $request->note ?? null;
            $payment->save();

            $order->payment_status = 'paid';
            $order->due_amount = 0;
            $order->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bkash Payment Success Handling Error: ' . $e->getMessage());
        }
    }

    public function fail(Request $request)
    {
        $orderId = $request->oid;
        if($orderId) {
            return redirect()->away(env('FRONTEND_URL')."/order-success/{$orderId}");
        }
        return response()->json([
            'result' => false,
            'message' => ('Payment Failed'),
            'payment_details' => $request->payment_details
        ]);
    }

}
