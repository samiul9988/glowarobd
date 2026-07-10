<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Models\CombinedOrder;
use App\Models\BusinessSetting;
use App\Models\LiveConversation;
use App\Models\LiveMessage;
use App\Models\Seller;
use App\Models\User;
use Session;
use Auth;
use Mail;
use Validator;

class LiveConversationController extends Controller{
    public function getList(Request $request, $id)
    {
        $findIdentifier = LiveMessage::where("identifier", "adminId-".$id)->first();
        if(!$findIdentifier){
            return response()->json(['status'=>"success", "data"=>[]]);
        }else{
            $data = LiveConversation::where("live_messages_id", $findIdentifier->id)->orderBy('created_at', 'desc')->paginate(10);
            return response()->json(['status'=>"success", "data"=>$data]);
        }
    }

    public function chatHistory(Request $request)
    {
        $users = LiveMessage::with('user')->orderBy('updated_at', 'desc')->paginate(10);
        if(count($users)>0){
            return response()->json(['status'=>"success", "data"=>$users]);            
        }else{
            return response()->json(['status'=>"success", "data"=>[]]);
        }
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'from'=>"required",
            'to'=>"required",
            'content'=>"required",
            'identifier'=>"required"
        ]);

        if($validator->fails()){
            return response()->json(['status'=>"error", 'errors'=>$validator->errors()]);
        }else{
            $findMessages = LiveMessage::where("identifier", $request->identifier)->first();
            if(!$findMessages){
                $getIdentifierArr = explode("-",$request->identifier);
                $messages = new LiveMessage();
                $messages->identifier = $request->identifier;
                $messages->user_id = $getIdentifierArr[1];
                $messages->save();
                $msgId = $messages->id;
            }else{
                $msgId = $findMessages->id;
            }

            $conversation = new LiveConversation();
            $conversation->live_messages_id = $msgId;
            $conversation->msg_from = $request->from;
            $conversation->msg_to = $request->to;
            $conversation->content = $request->content;

            if($conversation->save()){
                return response()->json(["status"=>"success", "message"=>"Message send successfully."]);
            }

        }
    }
}