<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\UserNotificationCollection;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\UserNotification;
use App\Models\UserNotificationRead;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function notifications(Request $request)
    {
        // $notifications = UserNotification::paginate(2);
        // return response()->json([
        //     'data' => $notifications,
        //     'success' => false,
        //     'status' => 404,
        //     'message' => 'User not found'
        // ]);
        $notification_list = [];
        $where_cond = 'id !=""';
        $sort = 'desc';
        $notifications = DB::table('user_notifications')
        ->select(DB::raw('*'))
        ->whereRaw($where_cond)
        ->orderBy('id',$sort)->get();

        $totalcount = $notifications->count();
        $perPage = $request->get('per_page', 10);
        $currentPage = $request->get('page', 1);
        $pagedData = $notifications->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $notifications = new \Illuminate\Pagination\LengthAwarePaginator($pagedData, count($notifications), $perPage);
        $totalPages = ceil($totalcount / $perPage);

        if(count($notifications) > 0):
            foreach($notifications as $row):
                $read_status = UserNotification::getreadstatus($row->id);
                $notification_list[] = [
                    'id' => intval($row->id),
                    'title' => $row->title,
                    'message' => $row->message,
                    'isread' => $read_status,
                    'image' => $row->image!=null?api_asset($row->image):null,
                    'type' => $row->type,
                    'url' => intval($row->url),
                    'created_at_date' => date('Y-m-d',strtotime($row->created_at)),
                    'created_at_time' => date('h:i A',strtotime($row->created_at)),
                    //'updated_at' => date('Y-m-d H:i:s',strtotime($row->updated_at))
                ];
            endforeach;
        endif;

        return response()->json([
            'success' => true,
            'data' =>  $notification_list,
            'total' => $totalcount,
            'per_page' => intval($perPage),
            'current_page' => intval($currentPage),
            'last_page' => $totalPages
        ]);
    }

    public function details(Request $request)
    {
        $notifications = UserNotification::where("id",intval($request->id))->get();
        if($notifications):
            if(!UserNotificationRead::where('user_id',Auth::guard('api')->id())->where('notification_id',intval($request->id))->exists()){
            $notification_read = new UserNotificationRead();
            $notification_read->user_id = Auth::guard('api')->id();
            $notification_read->notification_id = intval($request->id);
            $notification_read->save();
            }
            return new UserNotificationCollection($notifications);
            // return response()->json([
            //     'data' => $notifications,
            //     'success' => true,
            //     'status' => 200
            // ]);
        else:
            return response()->json([
                'data' => null,
                'success' => false,
                'status' => 404,
                'message' => 'Notification not found'
            ]);
        endif;
        //$notifications = UserNotification::paginate(2);

    }
    public function notification_count(Request $request)
    {
            return response()->json([
                'count' => unread_notification(Auth::guard('api')->id()),
                'success' => true,
                'status' => 200
            ]);
        //$notifications = UserNotification::paginate(2);

    }
}
