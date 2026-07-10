<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Models\CombinedOrder;
use App\Models\Order;
use Illuminate\Support\Facades\Session;

class BkashController extends Controller
{
    private $base_url;
    public function __construct()
    {
        if(get_setting('bkash_sandbox', 1)){
            $this->base_url = "https://checkout.sandbox.bka.sh/v1.2.0-beta/";
        }
        else {
            $this->base_url = "https://checkout.pay.bka.sh/v1.2.0-beta/";
        }
    }

    public function pay(){
        $amount = 0;
        if(Session::has('payment_type')){
            if(Session::get('payment_type') == 'cart_payment'){
                $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                $amount = round($combined_order->grand_total);
            }
            elseif (Session::get('payment_type') == 'wallet_payment') {
                $amount = round(Session::get('payment_data')['amount']);
            }
            elseif (Session::get('payment_type') == 'customer_package_payment') {
                $customer_package = CustomerPackage::findOrFail(Session::get('payment_data')['customer_package_id']);
                $amount = round($customer_package->amount);
            }
            elseif (Session::get('payment_type') == 'seller_package_payment') {
                $seller_package = SellerPackage::findOrFail(Session::get('payment_data')['seller_package_id']);
                $amount = round($seller_package->amount);
            }
        }

        /**
         * Generate token if the token is not set
         * or the token is expired
         * else use the existing token
         */
        if(env('BKASH_CHECKOUT_TOKEN') == null || env('BKASH_CHECKOUT_TOKEN_EXPIRE') < strtotime('now')){
            $request_data = array('app_key'=> env('BKASH_CHECKOUT_APP_KEY'), 'app_secret'=>env('BKASH_CHECKOUT_APP_SECRET'));

            $url = curl_init($this->base_url.'checkout/token/grant');
            $request_data_json=json_encode($request_data);

            $header = [
                'Content-Type:application/json',
                'username:'.env('BKASH_CHECKOUT_USER_NAME'),
                'password:'.env('BKASH_CHECKOUT_PASSWORD')
            ];
            curl_setopt($url,CURLOPT_HTTPHEADER, $header);
            curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url,CURLOPT_POSTFIELDS, $request_data_json);
            curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

            $resultdata = curl_exec($url);
            curl_close($url);

            // dd(json_decode($resultdata));
            $token = json_decode($resultdata)->id_token;

            write_env([
                'BKASH_CHECKOUT_TOKEN' => $token,
                'BKASH_CHECKOUT_TOKEN_EXPIRE' => strtotime('+1 hour')
            ]);
        } else{
            $token = env('BKASH_CHECKOUT_TOKEN');
        }

        Session::put('bkash_token', $token);
        Session::put('payment_amount', $amount);

        return view('frontend.bkash.index');
    }

    public function checkout(Request $request){
        $auth = Session::get('bkash_token');
        $combined_order = Order::findOrFail(Session::get('combined_order_id'));
        $merchantInvoiceNumber = isset($combined_order) && isset($combined_order->code) ? strval($combined_order->code) : strval(uniqid());

        $requestbody = array(
            'amount' => Session::get('payment_amount'),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $merchantInvoiceNumber,
        );
        $url = curl_init($this->base_url.'checkout/payment/create');
        $requestbodyJson = json_encode($requestbody);

        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $auth,
            'X-APP-Key:'.env('BKASH_CHECKOUT_APP_KEY')
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

    public function excecute(Request $request){
        $paymentID = $request->paymentID;
        $auth = Session::get('bkash_token');

        $url = curl_init($this->base_url.'checkout/payment/execute/'.$paymentID);
        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $auth,
            'X-APP-Key:'.env('BKASH_CHECKOUT_APP_KEY')
        );

        curl_setopt($url,CURLOPT_HTTPHEADER, $header);
        curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
        $resultdata = curl_exec($url);
        curl_close($url);

        return $resultdata;
    }

    public function success(Request $request){
        $payment_type = Session::get('payment_type');

        if ($payment_type == 'cart_payment') {
            $checkoutController = new CheckoutController;
            return $checkoutController->checkout_done(Session::get('combined_order_id'), $request->payment_details);
        }

        if ($payment_type == 'wallet_payment') {
            $walletController = new WalletController;
            return $walletController->wallet_payment_done(Session::get('payment_data'), $request->payment_details);
        }

        if ($payment_type == 'customer_package_payment') {
            $customer_package_controller = new CustomerPackageController;
            return $customer_package_controller->purchase_payment_done(Session::get('payment_data'), $request->payment_details);
        }
        // if($payment_type == 'seller_package_payment') {
        //     $seller_package_controller = new SellerPackageController;
        //     return $seller_package_controller->purchase_payment_done(Session::get('payment_data'), $request->payment_details);
        // }
    }

    public function refund(Request $request){
        $request_data = array('app_key'=> env('BKASH_CHECKOUT_APP_KEY'), 'app_secret'=>env('BKASH_CHECKOUT_APP_SECRET'));

        $url = curl_init($this->base_url.'checkout/token/grant');
        $request_data_json=json_encode($request_data);

        $header = array(
                'Content-Type:application/json',
                'username:'.env('BKASH_CHECKOUT_USER_NAME'),
                'password:'.env('BKASH_CHECKOUT_PASSWORD')
                );
        curl_setopt($url,CURLOPT_HTTPHEADER, $header);
        curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url,CURLOPT_POSTFIELDS, $request_data_json);
        curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $resultdata = curl_exec($url);
        curl_close($url);

        $auth = json_decode($resultdata)->id_token;

        $order = Order::findOrFail($request->order_id);
        $paymentData = json_decode($order->payment_details);

        $requestbody = array(
            "paymentID" => $paymentData->paymentID,
            "amount" => $paymentData->amount,
            "trxID" => $paymentData->trxID,
            "sku" => "KTA-0019",
            "reason" => "Product fault or damaged",
        );
        $url = curl_init($this->base_url.'checkout/payment/refund');
        $requestbodyJson = json_encode($requestbody);

        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $auth,
            'X-APP-Key:'.env('BKASH_CHECKOUT_APP_KEY')
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

    public function refundStatus(Request $request){
        $auth = 'eyJraWQiOiJmalhJQmwxclFUXC9hM215MG9ScXpEdVZZWk5KXC9qRTNJOFBaeGZUY3hlamc9IiwiYWxnIjoiUlMyNTYifQ.eyJzdWIiOiI4ZGU4ZjBlMC1mY2RjLTQyNzMtYjY4YS1iNDAwOWNjZjc3ZDEiLCJhdWQiOiI2NmEwdGZpYTZvc2tkYjRhMDRyY24wNjNhOSIsImV2ZW50X2lkIjoiNWFiNDBhZTgtM2YwOS00OGU2LWE1YmItZjk2NDZkOGRjYTk1IiwidG9rZW5fdXNlIjoiaWQiLCJhdXRoX3RpbWUiOjE2NjI2MTcyNDgsImlzcyI6Imh0dHBzOlwvXC9jb2duaXRvLWlkcC5hcC1zb3V0aGVhc3QtMS5hbWF6b25hd3MuY29tXC9hcC1zb3V0aGVhc3QtMV9rZjVCU05vUGUiLCJjb2duaXRvOnVzZXJuYW1lIjoidGVzdGRlbW8iLCJleHAiOjE2NjI2MjA4NDgsImlhdCI6MTY2MjYxNzI0OH0.BVWdrSSNHMcEhfnlBCGaJRej5NIV286dGZzcuvfV3mxXeVNR6VZdxSHoynWDvE09Cr114Yu8dWXdy5R0sRp36iqHv8JZDUkvllA1Pr4AKnfftKJW3nPX4Am0YMA0agtRbb1Q_orIvl1-rszbs2P0YVf3MHsFPbSnyqmFN3zTd64vg2ZzcVvpGB7PWpbHOhIuwrXUr9F7i82pgNK7CAimnCheALOKwsX2uucFhNb1aAOYFUnrwZ1CgBsf1hdVyRZhLph4EDDUwIJFi_XUo9FTTLIvXOXWJLpIsDfZf-LHhX-KaxCX5h65kG3vUh8NzgJtI9VUCQ07j7c3sMAe6L2kUw';

        $requestbody = array(
            "paymentID" => "I8XLZ4N1662617556419",
            "trxID" => "9I8207PHK8",
        );
        $url = curl_init($this->base_url.'checkout/payment/refund');
        $requestbodyJson = json_encode($requestbody);

        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $auth,
            'X-APP-Key:'.env('BKASH_CHECKOUT_APP_KEY')
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

    public function queryPayment(){

        $paymentID = 'I8XLZ4N1662617556419';
        $auth = 'eyJraWQiOiJmalhJQmwxclFUXC9hM215MG9ScXpEdVZZWk5KXC9qRTNJOFBaeGZUY3hlamc9IiwiYWxnIjoiUlMyNTYifQ.eyJzdWIiOiI4ZGU4ZjBlMC1mY2RjLTQyNzMtYjY4YS1iNDAwOWNjZjc3ZDEiLCJhdWQiOiI2NmEwdGZpYTZvc2tkYjRhMDRyY24wNjNhOSIsImV2ZW50X2lkIjoiNWFiNDBhZTgtM2YwOS00OGU2LWE1YmItZjk2NDZkOGRjYTk1IiwidG9rZW5fdXNlIjoiaWQiLCJhdXRoX3RpbWUiOjE2NjI2MTcyNDgsImlzcyI6Imh0dHBzOlwvXC9jb2duaXRvLWlkcC5hcC1zb3V0aGVhc3QtMS5hbWF6b25hd3MuY29tXC9hcC1zb3V0aGVhc3QtMV9rZjVCU05vUGUiLCJjb2duaXRvOnVzZXJuYW1lIjoidGVzdGRlbW8iLCJleHAiOjE2NjI2MjA4NDgsImlhdCI6MTY2MjYxNzI0OH0.BVWdrSSNHMcEhfnlBCGaJRej5NIV286dGZzcuvfV3mxXeVNR6VZdxSHoynWDvE09Cr114Yu8dWXdy5R0sRp36iqHv8JZDUkvllA1Pr4AKnfftKJW3nPX4Am0YMA0agtRbb1Q_orIvl1-rszbs2P0YVf3MHsFPbSnyqmFN3zTd64vg2ZzcVvpGB7PWpbHOhIuwrXUr9F7i82pgNK7CAimnCheALOKwsX2uucFhNb1aAOYFUnrwZ1CgBsf1hdVyRZhLph4EDDUwIJFi_XUo9FTTLIvXOXWJLpIsDfZf-LHhX-KaxCX5h65kG3vUh8NzgJtI9VUCQ07j7c3sMAe6L2kUw';

        $url = curl_init($this->base_url.'checkout/payment/query/'.$paymentID);

        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $auth,
            'X-APP-Key:'.env('BKASH_CHECKOUT_APP_KEY')
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        curl_close($url);

        return $resultdata;
    }

    public function search(){

        $trxID = '9I8207PHK8';
        $auth = 'eyJraWQiOiJmalhJQmwxclFUXC9hM215MG9ScXpEdVZZWk5KXC9qRTNJOFBaeGZUY3hlamc9IiwiYWxnIjoiUlMyNTYifQ.eyJzdWIiOiI4ZGU4ZjBlMC1mY2RjLTQyNzMtYjY4YS1iNDAwOWNjZjc3ZDEiLCJhdWQiOiI2NmEwdGZpYTZvc2tkYjRhMDRyY24wNjNhOSIsImV2ZW50X2lkIjoiNWFiNDBhZTgtM2YwOS00OGU2LWE1YmItZjk2NDZkOGRjYTk1IiwidG9rZW5fdXNlIjoiaWQiLCJhdXRoX3RpbWUiOjE2NjI2MTcyNDgsImlzcyI6Imh0dHBzOlwvXC9jb2duaXRvLWlkcC5hcC1zb3V0aGVhc3QtMS5hbWF6b25hd3MuY29tXC9hcC1zb3V0aGVhc3QtMV9rZjVCU05vUGUiLCJjb2duaXRvOnVzZXJuYW1lIjoidGVzdGRlbW8iLCJleHAiOjE2NjI2MjA4NDgsImlhdCI6MTY2MjYxNzI0OH0.BVWdrSSNHMcEhfnlBCGaJRej5NIV286dGZzcuvfV3mxXeVNR6VZdxSHoynWDvE09Cr114Yu8dWXdy5R0sRp36iqHv8JZDUkvllA1Pr4AKnfftKJW3nPX4Am0YMA0agtRbb1Q_orIvl1-rszbs2P0YVf3MHsFPbSnyqmFN3zTd64vg2ZzcVvpGB7PWpbHOhIuwrXUr9F7i82pgNK7CAimnCheALOKwsX2uucFhNb1aAOYFUnrwZ1CgBsf1hdVyRZhLph4EDDUwIJFi_XUo9FTTLIvXOXWJLpIsDfZf-LHhX-KaxCX5h65kG3vUh8NzgJtI9VUCQ07j7c3sMAe6L2kUw';

        $url = curl_init($this->base_url.'checkout/payment/search/'.$trxID);

        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $auth,
            'X-APP-Key:'.env('BKASH_CHECKOUT_APP_KEY')
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        curl_close($url);

        return $resultdata;
    }
}
