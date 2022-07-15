<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Group,GroupMember,GroupMessage,GroupDocument,Notification,GroupLocation};
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\GroupMessageResource;
use Auth;
use File;
use App\Events\Message;



class GroupMessageController extends Controller
{
    public function index(Request $request){
        $validator = Validator::make($request->all(), [
            'group_id'         => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $member = GroupMember::where('group_id',$request->group_id)->where('user_id',Auth::user()->id)->first();
        if($member->status == 0){
            $messages = GroupMessage::where('group_id',$request->group_id)->where('created_at','<',$member->updated_at)->orderBy('created_at','desc')->paginate(10);
        }else{
            $messages = GroupMessage::where('group_id',$request->group_id)->orderBy('created_at','desc')->paginate(10);
        }
        $member->updated_at = date('Y-m-d G:i:s');
        $member->save();
        $messages = GroupMessageResource::collection($messages)->response()->getData(true);
        return apiresponse(true, 'Messages found', $messages);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'group_id'         => 'required',
            'user_id'         => 'required',
            'type'         => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $groupMessage = new GroupMessage();
        $groupMessage->message = $request->message;
        $groupMessage->type = $request->type;
        $groupMessage->group_id  = $request->group_id;
        $groupMessage->user_id   = $request->user_id;
        if($groupMessage->save()){
            if($request->has('documents')){
                foreach ($request->documents as $value) {
                    $groupDocument = new GroupDocument();
                    $groupDocument->type = $value['type'];
                    $groupDocument->group_message_id  = $groupMessage->id;
                    $filename = time().'.'.$value['document']->getClientOriginalExtension();
                    $value['document']->move(public_path('images'), $filename);
                    $groupDocument->name = $filename;
                    $groupDocument->save();
                }
            }
            if($request->has('location')){
                $groupLocation= new GroupLocation();
                $groupLocation->lat  = $request->location['lat'];
                $groupLocation->long  = $request->location['long'];
                $groupLocation->group_message_id  = $groupMessage->id;
                $groupLocation->save();
            }
            $messages = GroupMessage::where('id',$groupMessage->id)->get();
            $message = GroupMessageResource::collection($messages);
            $message = $message->first();
           
            //dd(event(new Message(Auth::user(), $message->first(), true )));
            $title = 'You have a new message from ' . Auth::user()->username. 'in '.$message->group->name ;
            $body = $message->message;
            foreach($message->group->members as $member){
                SendNotification($member->user->device_id, $title, $body);
            }
            $notification = new Notification();
            $notification->sender_id                =   Auth::user()->id;
            $notification->reciever_id              =   $message->group_id;
            $notification->title                    =   $title;
            $notification->body                     =   $body;
            $notification->type                     =   'group_message';
            $notification->content_id               =   Auth::user()->id;
            $notification->save();
            $messages = GroupMessage::where('id',$groupMessage->id)->get();
            $message = GroupMessageResource::collection($messages);
            broadcast(new Message( json_decode( json_encode($message->first()) ) , true))->toOthers();
            return apiresponse(true, 'Messages sent',$message->first());
        }else{
            return apiresponse(false, 'Something went wrong',);
        }
    }

    public function destroy(Request $request){
        $validator = Validator::make($request->all(), [
            'message_id'         => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $groupMessage = GroupMessage::findorfail($request->message_id);
        if($groupMessage){
            foreach ($groupMessage->groupDocuments as $value) {
                $previousFileName = public_path('images/'.$value->name);
                if(file_exists($previousFileName)){
                    File::delete($previousFileName);
                }
                GroupDocument::findorfail($value->id)->delete();
            }
            $groupMessage->delete();
            return apiresponse(true, 'Message deleted');
        }else{
            return apiresponse(false, 'Something went wrong');
        }
    }
}
