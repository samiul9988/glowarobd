<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;

    public static function getreadstatus($id){
        $read_data = UserNotificationRead::where('notification_id',$id)->where('user_id',Auth::guard('api')->id());
        if($read_data->exists()){
            return 1;
        }else{
            return 0;
        }
    }
}
