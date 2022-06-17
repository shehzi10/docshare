<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use App\Models\GroupDocument;
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
