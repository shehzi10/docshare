<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserFriend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserResource;
use Carbon;
use Auth;

use function GuzzleHttp\Promise\all;

class FriendListController extends Controller
{

    public function getAllFriendsRequests()
    {
        $user = request()->user();

        $requests = UserFriend::where('requested_user_id', $user->id)->orderBy('created_at', 'DESC')->simplePaginate(10);
        return apiresponse(true, 'All friends requestes', $requests);
    }

    public function sendRequest(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'requested_user_id'     =>      'required',
        ]);

        if ($validator->fails()) {
            return apiresponse(false, implode("\n", $validator->errors()->all()));
        }

        $user_id                    =   auth()->user()->id;
        $requested_user_id          =   $request->requested_user_id;
        $friendRequest = new UserFriend();

        $requestUser = User::findOrFail($request->requested_user_id);

        $friendRequest->user_id = $user_id;
        $friendRequest->requested_user_id = $requested_user_id;
        $friendRequest->save();

        $title = "New Friend Request";
        $body = auth()->user()->username . " has sent you a friend request";

        SendNotification($requestUser->device_id, $title, $body);

        $notification = new Notification();

        $notification->sender_id                    =   auth()->user()->id;
        $notification->reciever_id                  =   $request->requested_user_id;
        $notification->title                        =   $title;
        $notification->body                         =   $body;
        $notification->type                         =   "friend_request";
        $notification->content_id                   =   auth()->user()->id;
        $notification->save();

        if ($friendRequest) {
            return apiresponse(true, 'Friend request has been sent successfully', $friendRequest);
        } else {
            return apiresponse(false, 'Some error occurred, please try again');
        }
    }

    public function acceptFriendRequest(Request $request)
    {

        $accept =  UserFriend::findOrFail($request->id);

        $accept->followed_back      =       1;
        $accept->status             =       'approved';
        $accept->accepted_date      =       Carbon\Carbon::now();

        $accept->save();

        $requested_user = User::where('id', $accept->user_id)->first();

        $title  =   "Friend Request Accepted";
        $body   =   auth()->user()->username . " has accepted your friend request";

        SendNotification($requested_user->device_id, $title, $body);

        $notification = new Notification();

        $notification->sender_id                    =   auth()->user()->id;
        $notification->reciever_id                  =   $requested_user->id;
        $notification->title                        =   $title;
        $notification->body                         =   $body;
        $notification->content_id                   =   auth()->user()->id;
        $notification->type                         =   "friend_request";

        $notification->save();

        if ($accept) {
            return apiresponse(true, 'Friend request has been accepted', $accept);
        } else {
            return apiresponse(false, 'Some error occurred, please try again');
        }
    }





    public function rejectFriendRequest(Request $request)
    {
        $accept =  UserFriend::findOrFail($request->id);

        $accept->is_followed        =       0;
        $accept->status             =       'rejected';
        $accept->accepted_date      =       Carbon\Carbon::now();

        $accept->save();

        $requested_user = User::where('id', $accept->user_id)->first();

        $title  =   "Friend Request Rejected";
        $body   =   auth()->user()->username . " has rejected your friend request";

        SendNotification($requested_user->device_id, $title, $body);

        $notification = new Notification();

        $notification->sender_id                    =   auth()->user()->id;
        $notification->reciever_id                  =   $requested_user->id;
        $notification->title                        =   $title;
        $notification->body                         =   $body;
        $notification->content_id                   =   auth()->user()->id;
        $notification->type                         =   "friend_request";

        $notification->save();

        if ($accept) {
            return apiresponse(true, 'Friend request has been rejected', $accept);
        } else {
            return apiresponse(false, 'Some error occurred, please try again');
        }
    }

    public function unFriendUser($id)
    {
        $request = UserFriend::where('user_id',Auth::user()->id)->where('requested_user_id',$id)->first();
        if ($request->delete()) {
            return apiresponse(true, 'Unfriended Successfully');
        }else{
            return apiresponse(false, 'User not found');
        }
    }

    public function getFriendsList(Request $request)
    {
        $user = Auth::user();
        if($request->search != null){
            $friends = UserFriend::where('user_id',$user->id)->where('status', 'approved')
            ->with(['requestedUser' => function($q)use ($request) {
            $q->where('username', 'LIKE', '%' .$request->search . '%');
            }])->get();
        }else{
            $friends = UserFriend::where('user_id',$user->id)->where('status', 'approved')
            ->with('requestedUser')->get();
        }
        $array = array();
        if($friends){
            foreach($friends as $key => $friend){
                if($friend->requestedUser != null){
                    $array [] = $friend->requestedUser;
                }
            }
            return apiresponse(true, 'Record found', $array);
        }else{
            return apiresponse(false, 'friends not  found');
        }
        
        
    }

    public function friendsListSearch(Request $request)
    {
        $user = Auth::user();
        
        // $friends = UserFriend::where('user_id',$user->id)->where('status', 'approved')
        // ->with('requestedUser')->get();
        // return $friends;
        $array = array();
        if($friends){
            foreach($friends as $friend){
                $array [] = $friend->requestedUser;   
            }
            return apiresponse(true, 'Record found', $array);
        }else{
            return apiresponse(false, 'friends not  found');
        }        
    }
}
