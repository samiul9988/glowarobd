<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Models\CombinedOrder;
use App\Models\BusinessSetting;
use App\Models\Seller;
use App\Models\User;
use Session;
use Auth;
use Mail;

class AamarpayController extends Controller
{
    // These methods are merged from aamarpay setup branch
    public function payment_init(Request $request)
    {
        if (get_setting('aamarpay') == 1) {
            return response()->json([
                'result'=>true,
                'cancelUrl'=> "https://www.glowarobd.com/payment/cancel",
                'successUrl'=> "https://www.glowarobd.com/payment/confirm",
                'failUrl'=>  "https://www.glowarobd.com/payment/fail",
                'signature'=> env("AAMARPAY_SIGNATURE_KEY"),
                'storeID'=> env("AAMARPAY_STORE_ID"),
                'isSandBox'=> boolval(get_setting("aamarpay_sandbox"))
            ]);
        }
        return response()->json([
            'result' => false,
            'message' => ('Payment Init Failed')
        ]);
    }

    public function payment_success(Request $request)
    {
        $payment = json_encode($request->all());

        if (isset($request->value_c)) {

            try {
                if ($request->value_c == 'cart_payment') {
                    checkout_done($request->value_b, $payment);
                }

                return response()->json(['result' => true, 'message' => ("Payment is successful")]);
            } catch (\Exception $e) {
                return response()->json(['result' => false, 'message' => $e->getMessage()]);
            }
        }

        return response()->json([
            'result' => false,
            'message' => ('Payment Failed')
        ]);
    }

    public function payment_fail(Request $request)
    {
        /*return response()->json([
            'result' => false,
            'message' => ('Payment Failed'),
            'payment_details' => $request
        ]);*/

        $info = [
            'result' => true,
            'message' => ('Payment Success'),
            'payment_details' => $request
        ];

        Mail::send('testmail', $info, function ($message)
        {
            $message->to('rrakhmit@gmail.com', 'Rajib')
                ->subject('Aamar Pay Payment Fail');
        });
    }

    public function payment_cancel(Request $request)
    {
        // return response()->json([
        //     'result' => false,
        //     'message' => ('Payment Canceled'),
        //     'payment_details' => $request
        // ]);

        $info = [
            'result' => true,
            'message' => ('Payment Success'),
            'payment_details' => $request
        ];

        Mail::send('testmail', $info, function ($message)
        {
            $message->to('rrakhmit@gmail.com', 'Rajib')
                ->subject('Aamar Pay Payment Canceled');
        });
    }

    // These methods are for aamapay webview screens
    public function index(Request $request){
        $user = User::findOrFail($request->user_id);
        if ($user->phone == null) {
            flash('Please add phone number to your profile')->warning();
            return redirect()->route('profile');
        }

        if ($user->email == null) {
            $email = 'customer@exmaple.com';
        }
        else{
            $email = $user->email;
        }

        if (get_setting('aamarpay_sandbox') == 1) {
            $url = 'https://sandbox.aamarpay.com/request.php'; // live url https://secure.aamarpay.com/request.php
        }
        else {
            $url = 'https://secure.aamarpay.com/request.php';
        }

        $amount = 0;
        if($request->has('payment_type')){
            if($request->payment_type == 'cart_payment'){
                $combined_order = CombinedOrder::findOrFail($request->order_id);
                $amount = round($combined_order->grand_total);
            }
            // elseif ($request->payment_type == 'wallet_payment') {
            //     $amount = round(Session::get('payment_data')['amount']);
            // }
            // elseif ($request->payment_type == 'customer_package_payment') {
            //     $customer_package = CustomerPackage::findOrFail(Session::get('payment_data')['customer_package_id']);
            //     $amount = round($customer_package->amount);
            // }
            // elseif ($request->payment_type == 'seller_package_payment') {
            //     $seller_package = SellerPackage::findOrFail(Session::get('payment_data')['seller_package_id']);
            //     $amount = round($seller_package->amount);
            // }
        }

        $fields = array(
            'store_id' => env('AAMARPAY_STORE_ID'), //store id will be aamarpay,  contact integration@aamarpay.com for test/live id
            'amount' => $amount, //transaction amount
            'payment_type' => 'VISA', //no need to change
            'currency' => 'BDT',  //currenct will be USD/BDT
            'tran_id' => rand(1111111,9999999), //transaction id must be unique from your end
            'cus_name' => $user->name,  //customer name
            'cus_email' => $email, //customer email address
            'cus_add1' => '',  //customer address
            'cus_add2' => '', //customer address
            'cus_city' => '',  //customer city
            'cus_state' => '',  //state
            'cus_postcode' => '', //postcode or zipcode
            'cus_country' => 'Bangladesh',  //country
            'cus_phone' => $user->phone, //customer phone number
            'cus_fax' => 'Not¬Applicable',  //fax
            'ship_name' => '', //ship name
            'ship_add1' => '',  //ship address
            'ship_add2' => '',
            'ship_city' => '',
            'ship_state' => '',
            'ship_postcode' => '',
            'ship_country' => 'Bangladesh',
            'desc' => env('APP_NAME').' payment',
            'success_url' => route('api.aamarpay.success'), //your success route
            'fail_url' => route('aamarpay.fail'), //your fail route
            'cancel_url' => route('cart'), //your cancel url
            'opt_a' => $request->payment_type,  //optional paramter
            'opt_b' => $request->order_id,
            'opt_c' => '',
            'opt_d' => '',
            'signature_key' => env('AAMARPAY_SIGNATURE_KEY') //signature key will provided aamarpay, contact integration@aamarpay.com for test/live signature key
        );

        $fields_string = http_build_query($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $url_forward = str_replace('"', '', stripslashes(curl_exec($ch)));
        curl_close($ch);

        $this->redirect_to_merchant($url_forward);
    }

    function redirect_to_merchant($url) {
        if (get_setting('aamarpay_sandbox') == 1) {
            $base_url = 'https://sandbox.aamarpay.com/';
        }
        else {
            $base_url = 'https://secure.aamarpay.com/';
        }

        header('Location: '.$base_url.$url.'');
        exit;
    }


    public function success(Request $request){
        $payment_type = $request->opt_a;

        if ($payment_type == 'cart_payment') {
            $checkoutController = new CheckoutController;
            return $checkoutController->checkout_done($request->opt_b, json_encode($request->all()));
        }

        // if ($payment_type == 'wallet_payment') {
        //     $walletController = new WalletController;
        //     return $walletController->wallet_payment_done(json_decode($request->opt_c), json_encode($request->all()));
        // }

        // if ($payment_type == 'customer_package_payment') {
        //     $customer_package_controller = new CustomerPackageController;
        //     return $customer_package_controller->purchase_payment_done(json_decode($request->opt_c), json_encode($request->all()));
        // }
        // if($payment_type == 'seller_package_payment') {
        //     $seller_package_controller = new SellerPackageController;
        //     return $seller_package_controller->purchase_payment_done(json_decode($request->opt_c), json_encode($request->all()));
        // }
    }

    public function fail(Request $request){
        flash(('Payment failed'))->error();
    	return redirect()->route('cart');
    }
}
