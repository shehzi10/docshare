<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Validator;
use App\src\RtcTokenBuilder;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;

class VideoCallController extends Controller
{
    public function generateToken(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'id'        =>      'required',
            'is_group'  =>      'required',
        ]);
        if ($validator->fails()){
            return apiresponse(false, implode("\n", $validator->errors()->all()), null, 400);
        }
        if($request->is_group == 1){
            $group = Group::where('id', $request->id)->with(['members' => function($q)use ($request) {
                $q->where('user_id', '<>', $request->user()->id);
                }])->first();
            if (!$group) {
                return apiresponse(true, 'Video Call', "Group not found");
            }
            $res = $this->sendVideoCallNotification(1, $group, 1);
            $data = [
                'to_user' => $group->members,
                'token' => $res['token'],
                'channel' => $res['channel'],
                'from_call' => $group,
                'is_group' => 1,
            ];
            foreach($group->members as $member){
                $title  =   "Incoming Video Call From ";
                $body   =   "You have a video call from " . $group->name;
                $user = $member->user;
                SendNotification($user->device_id, $title, $body, $data);
                $notification = new Notification();
                $notification->sender_id                    =   $group->id;
                $notification->reciever_id                  =   $user->id;
                $notification->title                        =   $title;
                $notification->body                         =   $body;
                $notification->content_id                   =   $group->id;
                $notification->type                         =   "video_call";
                $notification->save();
            }
            if($res){
                return apiresponse(true, 'Video Call', ['token' => $res['token'], 'channel' => $res['channel'],'from' => $group ]);
            }
        }else{
            $user = User::where('id', $request->id)->first();
            if (!$user) {
                return apiresponse(true, 'Video Call', "User not found");
            }
            $res = $this->sendVideoCallNotification($user->id, $request->user(), 0);
            $data = [
                'to_user' => $user,
                'token' => $res['token'],
                'channel' => $res['channel'],
                'from_call' => request()->user(),
                'is_group' => 0,
            ];
            $title  =   "Incoming Video Call";
            $body   =   "You have a video call from " . $request->user()->username;
            SendNotification($user->device_id, $title, $body, $data);
            $notification = new Notification();
            $notification->sender_id                    =   auth()->user()->id;
            $notification->reciever_id                  =   $user->id;
            $notification->title                        =   $title;
            $notification->body                         =   $body;
            $notification->content_id                   =   auth()->user()->id;
            $notification->type                         =   "video_call";
            $notification->save();
            return apiresponse(true, 'Video Call', ['token' => $res['token'], 'channel' => $res['channel'],'from' => $res['from'] ]);
        }
    }


    private function sendVideoCallNotification($id = 1, $from, $is_group){
        $user = User::where('id', $id)->first();
        // if (!$user) {
        //     return false;
        // }
        $appID = "ac7d15a624f648e3b96bb1829c8d7275";
        $appCertificate = "5ed734fbc1854db58730304d40e48f0d";
        $id = $is_group == 1 ? $from->id : $user->id;
        $channelName = "docshare" . $id; 
        $role = RtcTokenBuilder::RolePublisher;
        $expireTimeInSeconds = 360000;
        $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
        $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, "", $role, $privilegeExpiredTs);
        $data = [
            "token" => $token,
            'channel' => $channelName,
            'from' => $from,
        ];
        return  $data;
    }

    public function declineCall(Request $request)
    {
        if($request->is_group == 1){
            $group = Group::where('id', $request->id)->first();
            foreach($group->members as $member){
                $title  =   "Call Declined";
                $body   =   "";
                $user = $member->user;
                SendNotification($user->device_id, $title, $body);
                $notification = new Notification();
                $notification->sender_id                    =   $group->id;
                $notification->reciever_id                  =   $user->id;
                $notification->title                        =   $title;
                $notification->body                         =   $body;
                $notification->content_id                   =   $group->id;
                $notification->type                         =   "video_call";
                $notification->save();
            }
            return apiresponse(true, 'Video Call', "Call Declined");
        }else{
            $user = User::where('id', $request->id)->first();
            $title  =  "Call Declined";
            $body = "";
            SendNotification($user->device_id, $title, $body);
            $notification = new Notification();
            $notification->sender_id                    =   auth()->user()->id;
            $notification->reciever_id                  =   $user->id;
            $notification->title                        =   $title;
            $notification->body                         =   $body;
            $notification->content_id                   =   auth()->user()->id;
            $notification->type                         =   "video_call";
            $notification->save();
            return apiresponse(true, 'Video Call', "Call Declined");
        }
    }
}
