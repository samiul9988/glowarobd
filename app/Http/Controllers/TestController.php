<?php
namespace App\Http\Controllers;

use App\Events\ProductStockAffected;
use App\Jobs\CourierSuccessRateJob;
use App\Mail\InvoiceEmailManager;
use App\Models\ACCBank;
use App\Models\Address;
use App\Models\Area;
use App\Models\City;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\ProductStock;
use App\Models\State;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Exception;
use FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Modules\Waitlist\Services\WaitlistService;
use PDF;

class TestController extends Controller
{
    public function search(Request $request)
    {
        $key      = config('scout.meilisearch.key');
        $host     = config('scout.meilisearch.host') . '/indexes/';
        $headers  = [
            'Authorization' => 'Bearer ' . $key,
        ];
        $indexes = [
            'brands' => $host . config('scout.prefix') . 'brands_index',
            'categories' => $host . config('scout.prefix') . 'categories_index',
            'products' => $host . config('scout.prefix') . 'products_index',
        ];

        $params = [
            'q' => $request->q,
            // 'page' => 1,
            // 'hitsPerPage' => 10,
        ];

        // Check all indexes
        $response = Http::withHeaders($headers)->get($host);
        dd($response->json());

        // Search in brands index
        $response = Http::withHeaders($headers)->post($indexes['brands'] . '/search', $params);
        dd($response->json());

        // Search in categories index
        $response = Http::withHeaders($headers)->post($indexes['categories'] . '/search', $params);
        dd($response->json());

        // Search in products index
        $response = Http::withHeaders($headers)->post($indexes['products'] . '/search', $params);
        dd($response->json());

        // Get searchable attributes for brands index
        $response = Http::withHeaders($headers)->get($indexes['brands'] . '/settings/searchable-attributes');
        dd($response->json());
    }

    public function test(WaitlistService $waitlistService)
    {
        $request  = request();
        $products = \App\Models\Product::query()
            ->when(! $request->boolean('show_stock_out', true), function ($query) {
                $query->availableInStock();
            })
            ->whereIn('id', [1692, 551, 1978, 198, 601, 2236, 430, 1740, 423, 800, 2051, 1686, 1283, 590, 2798, 2901, 1389, 1847, 715, 2471])->get();

        $ids = $products->pluck('id')->toArray();
        foreach ($products as $key => $product) {
            echo $key + 1 . '. ' . $product->name . " - " . ($product->stocks?->first()?->qty ?? 0 > 0 ? 'In Stock' : 'Out of Stock') . "<br>";
        }

        echo implode(', ', $ids) . "<br><br>";
        return;
        $service = $waitlistService->getNotifiables(7, 'oldest');

        $successful = [];
        $failed     = [];
        foreach ($needToRun as $migration) {
            try {
                Artisan::call('migrate', [
                    '--path'  => 'database/migrations/' . $migration . '.php',
                    '--force' => true,
                ]);
                $successful[] = $migration;
                Log::channel('custom')->info('Migration ' . $migration . ' ran successfully.');
            } catch (Exception $e) {
                Log::channel('custom')->info('Migration Transaction Error: ' . $e->getMessage());
                $failed[] = $migration;
            }
        }

        Storage::disk('public')->put('migration_successful.txt', json_encode($successful));
        Storage::disk('public')->put('migration_failed.txt', json_encode($failed));

        return response()->json([
            'data' => [
                'success' => $successful,
                'failed'  => $failed,
            ],
        ]);
        dd(json_decode($migrations));

        return to_frontend(route('products.brand', [
            'brand_slug' => 'sample-product',
            'q1'         => 'value1',
            'q2'         => 'value2',
        ]), 'brand');
    }

    public function test2()
    {
        FFMpeg::fromDisk('public')
            ->open('video.webm')
            ->export()
            ->toDisk('public')
            ->inFormat(
                (new X264('aac'))->setKiloBitrate(1200) // compress to around 800 Kbps, good quality
            )
            ->addFilter('-vf', 'scale=1280:-2') // keep aspect ratio automatically
                                            // ->addFilter('-preset', 'medium')    // balance between speed & quality
            ->addFilter('-preset', 'slow')      // better compression, but slower
            ->save('compressed1200.mp4');

        return 'Video compressed successfully';
    }

    public function event()
    {
        broadcast(new \App\Events\TestEvent('Hello from Laravel Reverb!'));
        return 'TestEvent broadcasted!';
    }

    public function create_payment()
    {
        $reference = [
            "amount"        => "9701.84",
            "end_datetime"  => "2021-12-31",
            "custom_fields" => [
                "invoice" => "2018/0333",
            ],
        ];

        $data = json_encode($reference);

        $curl = curl_init();

        $httpHeader = [
            "Authorization: " . "Token " . "im5lqr34fwt37vougnpe4nuizu6exzlf",
            "Accept: application/vnd.proxypay.v2+json",
            "Content-Type: application/json",
            "Content-Length: " . strlen($data),
        ];

        $opts = [
            CURLOPT_URL            => "https://api.sandbox.proxypay.co.ao/references/904800000",
            CURLOPT_CUSTOMREQUEST  => "PUT",
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $httpHeader,
            CURLOPT_POSTFIELDS     => $data,
        ];

        curl_setopt_array($curl, $opts);

        $response = curl_exec($curl);
        $err      = curl_error($curl);

        curl_close($curl);
    }

    public function payments()
    {
        $curl = curl_init();

        $httpHeader = [
            "Authorization: " . "Token " . "im5lqr34fwt37vougnpe4nuizu6exzlf",
            "Accept: application/vnd.proxypay.v2+json",
        ];

        $opts = [
            CURLOPT_URL            => "https://api.sandbox.proxypay.co.ao/payments",
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $httpHeader,
        ];

        curl_setopt_array($curl, $opts);

        $response = curl_exec($curl);
        $err      = curl_error($curl);

        dd(json_decode($response), $err);
        curl_close($curl);
    }

    public function mock_payment()
    {
        $payment = [
            "reference_id" => 574850000,
            "amount"       => "380.44",
        ];

        $data = json_encode($payment);

        $curl = curl_init();

        $httpHeader = [
            "Authorization: " . "Token " . "im5lqr34fwt37vougnpe4nuizu6exzlf",
            "Accept: application/vnd.proxypay.v2+json",
            "Content-Type: application/json",
            "Content-Length: " . strlen($data),
        ];

        $opts = [
            CURLOPT_URL            => "https://api.sandbox.proxypay.co.ao/payments",
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $httpHeader,
            CURLOPT_POSTFIELDS     => $data,
        ];

        curl_setopt_array($curl, $opts);

        $response = curl_exec($curl);
        $err      = curl_error($curl);

        dd($response, $err);

        curl_close($curl);
    }

    public function reference_ids()
    {
        $curl = curl_init();

        $httpHeader = [
            "Authorization: " . "Token " . "im5lqr34fwt37vougnpe4nuizu6exzlf",
            "Accept: application/vnd.proxypay.v2+json",
        ];

        $opts = [
            CURLOPT_URL            => "https://api.sandbox.proxypay.co.ao/reference_ids",
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $httpHeader,
        ];

        curl_setopt_array($curl, $opts);

        $response = curl_exec($curl);

        dd($response);
        $err = curl_error($curl);

        curl_close($curl);
    }

    public function order_store(Request $request)
    {
        // dd($request->all());
        try {
            DB::beginTransaction();
            $payments = collect(Session::get('pos.payments', []));
            $carts    = Session::get('pos.cart', []);
            if (count($carts) > 0) {
                $order = new Order;

                if ($request->user_id == null) {
                    $order->guest_id     = mt_rand(100000, 999999);
                    $data['name']        = $request->name;
                    $data['email']       = $request->email;
                    $data['address']     = $request->address;
                    $data['country']     = Country::find($request->country)?->name;
                    $data['state']       = State::find($request->state)?->name;
                    $data['city']        = City::find($request->city)?->name;
                    $data['area']        = Area::find($request->area)?->name;
                    $data['postal_code'] = $request->postal_code;
                    $data['phone']       = $request->phone;

                    if (! isset($data['address']) || ! isset($data['country']) || ! isset($data['state']) || ! isset($data['city']) || ! isset($data['area']) || ! isset($data['phone'])) {
                        return ['success' => 0, 'message' => ("Please add name, address, country, state, city, area and phone")];
                    }

                    $order->address_id = isset($request->area) ? intval($request->area) : 0;

                } else {
                    $order->user_id = $request->user_id;

                    $user          = User::findOrFail($request->user_id);
                    $data['name']  = $user->name;
                    $data['email'] = $user->email;

                    if ($request->shipping_address != null) {
                        $address_data        = Address::findOrFail($request->shipping_address);
                        $data['address']     = $address_data->address;
                        $data['country']     = $address_data->country?->name;
                        $data['state']       = $address_data->state?->name;
                        $data['city']        = $address_data->city?->name;
                        $data['area']        = $address_data->area?->name;
                        $data['postal_code'] = $address_data->postal_code;
                        $data['phone']       = $address_data->phone;
                    }

                    $order->address_id = isset($address_data) ? $address_data->area?->id : intval($request->area) ?? 0;
                }

                $order->shipping_address = json_encode($data);

                $order->payment_type          = $request->payment_type;
                $order->delivery_viewed       = '0';
                $order->payment_status_viewed = '0';
                $order->code                  = config('app.order_no_prefix') . date('YmdHis') . rand(10, 99);
                $order->date                  = strtotime('now');
                $order->payment_status        = 'unpaid';
                $order->payment_details       = $request->payment_type;
                $order->delivery_status       = strtolower($request->delivery_status ?? 'pending');
                $order->order_source          = strtoupper($request->order_source ?? 'POS');
                if (strtolower($request->order_source) === 'showroom') {
                    $order->delivery_fee = 0;
                }

                $shipping_info = Session::get('pos.shipping_info');
                // dd(Session::get('pos'));
                if ($order->save()) {
                    $subtotal = 0;
                    $tax      = 0;
                    foreach (Session::get('pos.cart') as $key => $cartItem) {
                        $product_stock     = ProductStock::with('product')->find($cartItem['stock_id']);
                        $product           = $product_stock->product;
                        $product_variation = $product_stock->variant;

                        $subtotal += $cartItem['price'] * $cartItem['quantity'];
                        $tax      += $cartItem['tax'] * $cartItem['quantity'];

                        $lastPurchaseItem = $product->getLastPurchaseOrderItemByVariant($product_variation);
                        if ($lastPurchaseItem) {
                            $lastPurchasePrice = $lastPurchaseItem->price;
                        } else {
                            $lastPurchasePrice = 0;
                        }

                        if (intval($cartItem['quantity']) > $product_stock->qty) {
                            $order->delete();
                            return ['success' => 0, 'message' => $product->name . ' (' . $product_variation . ') ' . (" just stock outs.")];
                        } else {
                            $product_stock->qty = floatval($product_stock->qty) - floatval($cartItem['quantity']);
                            if ($product_stock->save()) {
                                // Log::info('Stock Updated-'.$product_stock->qty);
                            } else {
                                Log::info('Stock Not Updated-' . $product_stock->qty);
                            }

                            $isAddition = false;
                            // Store Stock Transaction
                            $transaction = [
                                'product_id'    => (int) $product->id,
                                'variant'       => empty($product_stock->variant) ? null : $product_stock->variant,
                                'sku'           => $product_stock->sku ?? null,
                                'qty'           => abs($cartItem['quantity']),
                                'isAddition'    => ($isAddition) ? 1 : 0,
                                'isSubtraction' => ($isAddition) ? 0 : 1,
                                'purpose'       => 'sales',
                                'purpose_id'    => $order->id,
                                'note'          => 'New POS Sales, Ref. ID = ' . $order->code ?? 'Unknown' . '',
                            ];
                            // Trigger The Event
                            event(new ProductStockAffected($transaction));
                        }

                        $order_detail                  = new OrderDetail;
                        $order_detail->order_id        = $order->id;
                        $order_detail->seller_id       = $product->user_id;
                        $order_detail->product_id      = $product->id;
                        $order_detail->payment_status  = 'unpaid';
                        $order_detail->variation       = empty($product_variation) ? null : $product_variation;
                        $order_detail->price           = $cartItem['price'] * $cartItem['quantity'];
                        $order_detail->tax             = $cartItem['tax'] * $cartItem['quantity'];
                        $order_detail->quantity        = $cartItem['quantity'];
                        $order_detail->shipping_type   = $request->shipping_type;
                        $order_detail->shipping_method = $request->shipping_method;

                        if (Session::get('pos.shipping', 0) >= 0 && count(Session::get('pos.cart') ?? []) > 0) {
                            $order_detail->shipping_cost = Session::get('pos.shipping', 0) / count(Session::get('pos.cart'));
                        } else {
                            $order_detail->shipping_cost = 0;
                        }

                        $order_detail->last_purchase_price = $lastPurchasePrice > 0 ? $lastPurchasePrice : $cartItem['price'];

                        $order_detail->save();

                        $product->num_of_sale++;
                        $product->save();
                    }

                    $order->grand_total = $subtotal + $tax + Session::get('pos.shipping', 0);

                    if (Session::has('pos.discount')) {
                        $order->grand_total     -= Session::get('pos.discount');
                        $order->coupon_discount  = Session::get('pos.discount');
                    }

                    $order->payment_status = 'unpaid';
                    $order->seller_id      = $product->user_id;
                    $order->save();

                    $array['view']    = 'emails.invoice';
                    $array['subject'] = 'Your order has been placed - ' . $order->code;
                    $array['from']    = env('MAIL_FROM_ADDRESS');
                    $array['order']   = $order;

                    $admin_products  = [];
                    $seller_products = [];

                    foreach ($order->orderDetails as $key => $orderDetail) {
                        if ($orderDetail->product->added_by == 'admin') {
                            array_push($admin_products, $orderDetail->product->id);
                        } else {
                            $product_ids = [];
                            if (array_key_exists($orderDetail->product->user_id, $seller_products)) {
                                $product_ids = $seller_products[$orderDetail->product->user_id];
                            }
                            array_push($product_ids, $orderDetail->product->id);
                            $seller_products[$orderDetail->product->user_id] = $product_ids;
                        }
                    }

                    foreach ($seller_products as $key => $seller_product) {
                        try {
                            Mail::to(User::find($key)->email)->queue(new InvoiceEmailManager($array));
                        } catch (\Exception $e) {

                        }
                    }

                    //sends email to customer with the invoice pdf attached
                    $toEmail = Session::get('pos.shipping_info')['email'] ?? null;
                    if ($toEmail) {
                        try {
                            Mail::to($toEmail)->queue(new InvoiceEmailManager($array));
                        } catch (\Exception $e) {

                        }
                    }

                    // if($request->user_id != NULL){
                    //     if (Addon::where('unique_identifier', 'club_point')->first() != null && Addon::where('unique_identifier', 'club_point')->first()->activated) {
                    //         $clubpointController = new ClubPointController;
                    //         $clubpointController->processClubPoints($order);
                    //     }
                    // }

                    foreach ($payments as $payable) {
                        $bank_info = ACCBank::find($payable['bank']);

                        $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
                        $pinv         = "PAY-" . date('Ymd') . ($inv_counting + 1);

                        $pdetails = [
                            'payment_method' => $payable['method'],
                            'bank_type'      => $payable['bank_type'],
                            'bank_info'      => $bank_info['bank_name'] ?? null,
                            'payment_amount' => $payable['amount'],
                        ];

                        $payment                  = new Payment;
                        $payment->invoice_no      = $pinv;
                        $payment->date            = date('Y-m-d');
                        $payment->payable_id      = $order->user_id ?? $order->guest_id;
                        $payment->payable_type    = User::class;
                        $payment->reference_id    = $order->id;
                        $payment->reference_type  = Order::class;
                        $payment->seller_id       = null;
                        $payment->amount          = $payable['amount'];
                        $payment->payment_details = json_encode($pdetails);
                        $payment->payment_method  = $payable['method'];
                        $payment->txn_code        = null;
                        $payment->user_id         = auth()->user()?->id ?? null;
                        $payment->remarks         = $payable['note'];
                        $payment->save();
                    }
                    if ($payments->isNotEmpty()) {
                        $totalAmountPaid = $payments->sum('amount') ?? 0;
                        $grand_total     = get_order_grand_total($order);
                        if ($totalAmountPaid > 0) {
                            $order->payment_status = $totalAmountPaid < $grand_total ? 'partial' : 'paid';
                        } else {
                            $order->payment_status = 'unpaid';
                        }
                        $order->due_amount = max(0, $grand_total - $totalAmountPaid); // Ensure due amount is not negative
                        $order->save();
                    }

                    // Store Call Logs
                    $callLog = Session::get('pos.call_log', []);
                    if (! empty($callLog)) {
                        $order->addCallLog([
                            'status'    => $callLog['status'] ?? 'unknown',
                            'note'      => $callLog['note'] ?? '',
                            'called_by' => auth()->user()->id,
                            'duration'  => $callLog['duration'] ?? 0,
                        ]);
                    }
                    calculateCommissionAffilationClubPoint($order);

                    DB::commit();

                    (new OrderController)->sendEmail($order);
                    if (json_decode($order->shipping_address)?->phone ?? false) {
                        CourierSuccessRateJob::dispatch(json_decode($order->shipping_address)?->phone ?? '');
                    }
                    Session::forget('pos.shipping_info');
                    Session::forget('pos.shipping');
                    Session::forget('pos.discount');
                    Session::forget('pos.cart');
                    Session::forget('pos.payments');
                    Session::forget('pos.total_paid');
                    Session::forget('pos.success_rate');
                    Session::forget('pos.phoneNumber');
                    Session::forget('pos.call_log');
                    Session::forget('pos.last_order_date');

                    logOrder($order, 'created');
                    return ['success' => 1, 'message' => ('Order Completed Successfully.'), 'order_id' => $order->id];
                } else {
                    DB::rollback();
                    return ['success' => 0, 'message' => ('Please input customer information.')];
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('Error:' . $e->getMessage());
            return ['success' => 0, 'message' => $e->getMessage()];
        }

        return ['success' => 0, 'message' => ("Please select a product.")];
    }
}
