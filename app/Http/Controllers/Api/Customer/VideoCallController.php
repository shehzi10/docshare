<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\src\RtcTokenBuilder;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;

class VideoCallController extends Controller
{
    public function generateToken(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        if (!$user) {
            return apiresponse(true, 'Video Call', "User not found");
        }
        $appID = "e485b040bdfd42639b508c6555f801fe";
        $appCertificate = "219b1ad85c5e46cdb25681fc7f632c29";
        $channelName = "healthconet" . $user->id;

        $role = RtcTokenBuilder::RolePublisher;
        $expireTimeInSeconds = 360000;
        $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
        $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, "", $role, $privilegeExpiredTs);
        
        $token = $token;
        $title  =   "Incoming Video Call";
        $body   =   "You have a video call from " . $request->user()->username;
        $data = [
            'to_user' => $user,
            "token" => $token,
            'from_call' => request()->user(),
        ];

        SendNotification($user->device_id, $title, $body, $data);

        $notification = new Notification();
        $notification->sender_id                    =   auth()->user()->id;
        $notification->reciever_id                  =   $user->id;
        $notification->title                        =   $title;
        $notification->body                         =   $body;
        $notification->content_id                   =   auth()->user()->id;
        $notification->type                         =   "video_call";
        $notification->save();

        return apiresponse(true, 'Video Call', [$token, $channelName]);
    }

    public function declineCall(Request $request)
    {
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
