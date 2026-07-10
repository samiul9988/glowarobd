<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class UserNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notifications = UserNotification::orderBy('id', 'desc')->paginate(10);
        return view('backend.notification.user_notification.index', compact('notifications'));
    }
    public function notifications()
    {
        $notifications = UserNotification::orderBy('id','desc')->paginate(10);
        return view('frontend.user.notifications.notifications', compact('notifications'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // echo unread_notification();exit;
        // $userNames = User::pluck('device_token')->chunk(100)->toArray();
        // dd($userNames);
        $products = Product::where('published',1)->select('id','name')->get();
        $categories = Category::where('parent_id', 0)->where('digital', 0)->with('childrenCategories')->get();
        $brands = Brand::select(['id', 'name'])->get();
        return view('backend.notification.user_notification.create',compact(['products', 'categories', 'brands']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_device_token = User::where('device_token','<>','')->get()->pluck('device_token')->toArray();
        $notification_settings = get_setting('notification_status');
        $one_signal = get_setting('onesignal');
        $google_firebase = get_setting('google_firebase');
        $request->validate([
            'type' => 'required',
            'title' => 'required',
            'message' => 'required',
        ]);
    if($notification_settings == 'on'){
        $notification = new UserNotification();

        $notification->title = $request->input('title');
        $notification->message = $request->input('message');
        $notification->type = $request->input('type');
        $notification->url = $request->input('url');
        $notification->image = $request->input('image');

        if($notification->save()){

            if($one_signal == 1){
                    try {
                        if($notification->type == 'product'){
                            $product = Product::find(intval($notification->url));
                            $link = to_frontend(route('product', $product->slug));
                        }elseif($notification->type == 'brand'){
                            $brand = Brand::find(intval($notification->url));
                            $link = to_frontend(route('products.brand', $brand->slug), 'brand');
                        }elseif($notification->type == 'category'){
                            $category = Category::find(intval($notification->url));
                            $link = to_frontend(route('products.category', $category->slug), 'category');
                        }else{
                            $link = env('APP_URL');
                        }
                        $image_url='';
                        if($notification->image!=null){
                        $image_url = uploaded_asset($notification->image);
                        }
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://onesignal.com/api/v1/notifications',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS =>'{
                        "app_id": "'.env('ONE_SIGNAL_APP_ID').'",
                        "included_segments": ["All"],
                        "headings": {"en": "'.$notification->title.'"},
                        "web_url": "'.$link.'",
                        "chrome_web_image": "'.$image_url.'",
                        "contents": {"en": "'.$notification->message.'"}
                        }
                        ',
                        CURLOPT_HTTPHEADER => array(
                            'Authorization: Basic '.env('ONE_SIGNAL_API_KEY'),
                            'Content-Type: application/json'
                        ),
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        //echo $response;
                        // flash(('Notification sent successfully'))->success();
                        // return redirect()->route('user-notification.index');
                        } catch (\Exception $e) {
                            // Failed to send notification
                            //return response()->json(['error' => $e->getMessage()], 500);
                            // flash(('Notification not send!'))->error();
                            // return redirect()->route('user-notification.create');
                        }
                }
                if($google_firebase == 1){
                        try {
                            $url = 'https://fcm.googleapis.com/fcm/send';

                            $fields = array
                            (
                                'registration_ids' => $user_device_token,
                                'notification' => [
                                    'body' => $notification->message,
                                    'title' => $notification->title,
                                    'sound' => 'default' /*Default sound*/
                                ],
                                'data' => [
                                    'type' => $notification->type,
                                    'type_id' => $notification->url,
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                                ],
                                "direct_boot_ok"=> true,
                                "time_to_live"=> 60
                            );

                            //$fields = json_encode($arrayToSend);
                            $headers = array(
                                'Authorization: key=' . env('FCM_SERVER_KEY'),
                                'Content-Type: application/json'
                            );

                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

                            $result = curl_exec($ch);
                            curl_close($ch);
                            } catch (\Exception $e) {
                                // Failed to send notification
                                //return response()->json(['error' => $e->getMessage()], 500);
                                // flash(('Notification not send!'))->error();
                                // return redirect()->route('user-notification.create');
                            }
                    }
                flash(('Notification sent successfully'))->success();
                return redirect()->route('user-notification.index');
        }else{
            flash(('Notification not send!'))->error();
            return redirect()->route('user-notification.create');
        }
    }else{
        flash(('Your notification settings is currently disable'))->error();
        return redirect()->route('user-notification.create');
    }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function details(Request $request)
    {
        $user_id = Auth::guard('web')->id();
        $user = User::find($user_id);
        $tokenResult = $user->createToken('Personal Access Token');

        return $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$tokenResult->accessToken,
            'Accept' => 'application/json',
        ])
            ->get(url('/') . '/api/v2/notifications/details?id='.$request->id)->body();
            // return json_encode([
            //     'name'=>'roy',
            //     'id'=>12
            // ]);

        // return $products = $response->json()['data'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
