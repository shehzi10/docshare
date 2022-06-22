<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Group,GroupMember,GroupMessage,GroupDocument,Notification};
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\GroupMessageResource;
use Auth;
use File;



class GroupMessageController extends Controller
{
    public function index(Request $request){
        $validator = Validator::make($request->all(), [
            'group_id'         => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        
        $member = GroupMember::where('group_id',$request->group_id)->where('user_id',Auth::user()->id)->first();
        if($member->status == 0){
            $messages = GroupMessage::where('group_id',$request->group_id)->where('created_at','<',$member->updated_at)->get();
        }else{
            $messages = GroupMessage::where('group_id',$request->group_id)->get();
        }
        $messages = GroupMessageResource::collection($messages);
        return apiresponse(true, 'Messages found', $messages);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'message'         => 'required',
            'group_id'         => 'required',
            'user_id'         => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $groupMessage = new GroupMessage();
        $groupMessage->message = $request->message;
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
            $message = GroupMessage::where('id',$groupMessage->id)->with('groupDocuments','user','group')->first();
            broadcast(new \App\Events\Message(Auth::user(), $message, true))->toOthers();
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
            return apiresponse(true, 'Messages sent');
        }else{
            return apiresponse(false, 'Something went wrong');
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
