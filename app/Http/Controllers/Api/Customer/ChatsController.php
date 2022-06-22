<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Chatlist;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ChatsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $user = request()->user();

        $chatlist = Chatlist::with(['to_user', 'from_user'])
            ->select(['chatlists.*', 'fromuser.username as fromusername', 'touser.username as tousername'])
            ->leftjoin('users as fromuser', 'fromuser.id', 'chatlists.from_user_id')->leftjoin('users as touser', 'touser.id', 'chatlists.to_user_id');
        if ($search) {
            $chatlist = $chatlist->where(DB::raw("(fromuser.username LIKE '%" . $search . "%' OR touser.username LIKE '%" . $search . "%') AND (from_user_id = '" . $user->id . "' OR to_user_id = '" . $user->id . "')"), ">", DB::raw("0"))->simplePaginate(10);
        } else {
            $chatlist = $chatlist->where(function ($q) use ($user) {
                $q->where('from_user_type', 'App\Models\User')
                    ->where('from_user_id', $user->id)->orderBy('created_at', 'DESC')->simplePaginate(10);
            })
                ->orWhere(function ($q) use ($user) {
                    $q->where('to_user_type', 'App\Models\User')
                        ->where('to_user_id', $user->id);
                })->orderBy('created_at', 'DESC')->simplePaginate(10);
        }
        //        $data = [];
        //        foreach ($chatlist as $v) {
        //            $msgsCount = Message::where('chatlist_id', '=', $v->id)->count();
        //            if ($msgsCount > 0) {
        //                $v["msg_count"]=$msgsCount;
        //                $data[] = $v;
        //            }
        //        }
        foreach ($chatlist as $key => $chat) {
            if ($chat->from_user_id == Auth::user()->id) {
                $mine = $chatlist[$key]['fromusername'];
                $mineimg = $chatlist[$key]['from_user']->profile_pic;
                $chatlist[$key]['fromusername'] = $chat->tousername;
                $chatlist[$key]['tousername'] = $mine;
                // $chatlist[$key]['from_user']->profile_pic = $chat->to_user->profile_pic;
                // $chatlist[$key]['to_user']->profile_pic = $mineimg;
            }
        }
        // return $chatlist;
        return apiresponse(true, 'Chatlist', $chatlist);
    }


    public function sendMessage(Request $request){

        $user = request()->user();
        $validator = Validator::make($request->all(),[
            'user_id'       =>      'required|exists:users,id',
            'type'          =>      'required|in:text,image,media',
            'message'       =>      [Rule::requiredIf($request->type == "text")],
            'media'         =>      [Rule::requiredIf($request->type == "media")]
        ]);
        if ($validator->fails())
        return apiresponse(false, implode("\n", $validator->errors()->all()), null, 400);

        $user_id = $request->user_id;

        $chatlist = Chatlist::where(function ($q) use ($user_id, $user) {
            $q->where('from_user_type', 'App\Models\User')
                ->where('from_user_id', $user_id)
                ->where('to_user_type', 'App\Models\User')
                ->where('to_user_id', $user->id);
        })->orWhere(function ($q) use ($user_id, $user) {
            $q->where('to_user_type', 'App\Models\User')
                ->where('to_user_id', $user_id)
                ->where('from_user_type', 'App\Models\User')
                ->where('from_user_id', $user->id);
        })->first();
        if (!$chatlist) {
            $chatlist = Chatlist::create([
                'from_user_type' => 'App\Models\User',
                'from_user_id' => $user->id,
                'to_user_type' => 'App\Models\User',
                'to_user_id' => $user_id
            ]);
        }
        $messageData = [
            'chatlist_id' => $chatlist->id,
            'type' => $request->type,
            'sent_from_type' => 'App\Models\User',
            'sent_from_id' => $user->id,
            'image'     =>  $request->image,
        ];
        if ($chatlist->from_user_type == "App\Models\User" && $chatlist->from_user_id == $user->id) {
            $messageData['sent_to_type'] = $chatlist->to_user_type;
            $messageData['sent_to_id'] = $chatlist->to_user_id;
        } else if ($chatlist->to_user_type == "App\Models\User" && $chatlist->to_user_id == $user->id) {
            $messageData['sent_to_type'] = $chatlist->from_user_type;
            $messageData['sent_to_id'] = $chatlist->from_user_id;
        }
        if ($request->type == "media" and $request->hasFile('media')) {
            $fileName = time() . '.' . $request->file('media')->getClientOriginalExtension();
            $request->file('media')->move(public_path('images'), $fileName);
            $messageData['media'] = $fileName;
        } else {
            $messageData['message'] = $request->message;
        }

        if ($request->type == "audio" and $request->hasFile('audio')) {
            $fileName = time() . '.' . $request->file('media')->getClientOriginalExtension();
            $request->file('audio')->move(public_path('images'), $fileName);
            $messageData['audio'] = $fileName;
        } else {
            $messageData['message'] = $request->message; 
        }

        $message = Message::create($messageData);
        $message = Message::find($message->id);
        broadcast(new \App\Events\Message($user, $message,false))->toOthers();

        $title = 'You have a new message from ' . $request->user()->username;
        $body = $message->message;

        SendNotification($message->sent_to->device_id, $title, $body);


        $notification = new Notification();

        $notification->sender_id                =   request()->user()->id;
        $notification->reciever_id              =   $message->sent_to_id;
        $notification->title                    =   $title;
        $notification->body                     =   $body;
        $notification->type                     =   'message';
        $notification->content_id               =   request()->user()->id;

        $notification->save();

        return apiresponse(true, 'Message Sent', $message);

    }

    public function show($id)
    {
        $messages = Message::where(['chatlist_id' => $id])->orderBy('created_at', 'DESC')->simplePaginate(10);
        if ($messages) {
            return apiresponse(true, 'Messages Found', $messages);
        } else {
            return apiresponse(false, 'Messages Not Found');
        }
    }

    public function checkSessionBeforeMessage(Request $request)
    {
        $chathead = Chatlist::where(DB::raw("(from_user_id  =  " . Auth::user()->id . " AND to_user_id  = $request->id) or (from_user_id  = $request->id AND to_user_id  = " . Auth::user()->id . ")"), '>', DB::raw('0'))
            ->first();

            // return Auth::user()->id;
            if (empty($chathead)) {

            $chathead = Chatlist::create([
                "from_user_id" => Auth::user()->id,
                "to_user_id" => $request->id,
                'from_user_type'    =>  'App\Models\User',
                'to_user_type'      =>  'App\Models\User',
            ]);
        } else {

            $chathead = Chatlist::where('id', $chathead->id)->update([
                "from_user_id" => Auth::user()->id,
                "to_user_id" => $request->id,

            ]);

            $chathead = Chatlist::where(DB::raw("(from_user_id  =  " . Auth::user()->id . " AND to_user_id  = $request->id) or (from_user_id  = $request->id AND to_user_id  = " . Auth::user()->id . ")"), '>', DB::raw('0'))
                ->first();
        }
        $chathead->user =    User::find($request->id);
        return apiresponse(true, 'Chatlist', $chathead);
    }
}
