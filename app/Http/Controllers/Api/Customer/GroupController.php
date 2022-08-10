<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Notification;
use App\Models\User;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupMemberResource;
use Auth;
use Carbon\Carbon;
use File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use App\CustomClasses\ColectionPaginate;


class GroupController extends Controller
{
    public function index(Request $request){
        if($request->filled('search')){
            $groups = GroupMember::where('user_id', Auth::user()->id)->where('status',1)->with(['group'=> function($q) use ($request){
                $q->where('name','like','%'.$request->search.'%')->get();
            }])->paginate(10);
            if($groups){
                foreach($groups as $key => $group){
                    if($group->group == null){
                        unset($groups[$key]);
                    }
                }
                $data = GroupResource::collection($groups)->response()->getData(true);
                return apiresponse(true, 'Groups found', $data);
            }else{
                $data = null;
                return apiresponse(true, 'Groups not found', $data);
            } 
           
            
        }else{
            //$groups = GroupMember::where('user_id', Auth::user()->id)->where('status',1)->with(['group'])->paginate(10);
            $groups = GroupMember::where('user_id', Auth::user()->id)->where('status',1)->with(['group'])->paginate(10);
            // return $groups;
            $data = GroupResource::collection($groups)->response()->getData(true);  
            return apiresponse(true, 'Groups found', $data);
        }
       
        // $filtered->all();
        // return apiresponse(true, 'Groups found', $filtered->all());  
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name'         => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $group = new Group();
        $group->name = $request->name;
        $group->description = $request->description;
        $group->user_id = Auth::user()->id;
        if($request->hasFile('image')){
            $filename = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('images'), $filename);
            $group->image = $filename;
        }
        if($group->save()){
            $member = new GroupMember();
            $member->group_id = $group->id;
            $member->user_id = Auth::user()->id;
            $member->is_admin = 1;
            $member->status = 1;
            $member->save();
            if($request->has('members')){
                foreach($request->members as $member){
                    $memberAdd = new GroupMember();
                    $memberAdd->group_id = $group->id;
                    $memberAdd->user_id  = $member['user_id'];
                    $memberAdd->is_admin = $member['is_admin'];
                    $memberAdd->status = 1;
                    $memberAdd->save();
                    $title = 'You were added to '.$memberAdd->group->name;
                    $body = 'You were added to '.$memberAdd->group->name;
                    SendNotification($memberAdd->user->device_id, $title, $body);
                    $notification = new Notification();
                    $notification->sender_id                =   Auth::user()->id;
                    $notification->reciever_id              =   $memberAdd->user_id;
                    $notification->title                    =   $title;
                    $notification->body                     =   $body;
                    $notification->type                     =   'message';
                    $notification->content_id               =   $memberAdd->id;
                    $notification->save();
                }
                if($member){
                    return apiresponse(true, 'Group created successfully');
                }else{
                    return apiresponse(false, 'something went wrong');
                }
            }else{
                return apiresponse(true, 'Group created successfully');
            }
        }else{
            return apiresponse(false, 'something went wrong');
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'group_id'         => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $group = Group::findorfail($request->group_id);
        $group->name = $request->name;
        $group->description = $request->description;
        if($request->hasFile('image')){
            $previousFileName = public_path('images/'.$group->image);
            if(file_exists($previousFileName)){
                File::delete($previousFileName);
            }
            $filename = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('images'), $filename);
            $group->image = $filename;
        }
        if($request->filled('members')){
            foreach($request->members as $member){
                if($member['user_id'] != null){
                    $memberAdd = new GroupMember();
                    $memberAdd->group_id = $group->id;
                    $memberAdd->user_id  = $member['user_id'];
                    $memberAdd->is_admin = $member['is_admin'];
                    $memberAdd->status = 1;
                    $memberAdd->save();   
                    $title = 'You were added to '.$memberAdd->group->name;
                    $body = 'You were added to '.$memberAdd->group->name;
                    SendNotification($memberAdd->user->device_id, $title, $body);
                    $notification = new Notification();
                    $notification->sender_id                =   Auth::user()->id;
                    $notification->reciever_id              =   $memberAdd->user_id;
                    $notification->title                    =   $title;
                    $notification->body                     =   $body;
                    $notification->type                     =   'message';
                    $notification->content_id               =   $memberAdd->id;
                    $notification->save();
                }
            }
        }
        if($group->save()){
            return apiresponse(true, 'Group updated successfully');
        }else{
            return apiresponse(false, 'something went wrong');
        }
    }

    public function groupMembers(Request $request){
        $validator = Validator::make($request->all(), [
            'group_id'   => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $group = Group::findorfail($request->group_id);
        $members = GroupMemberResource::collection($group->members);
        if($members){
            return apiresponse(true, "Group members found", $members);
        }else{
            return apiresponse(false, 'Something went wrong');
        }
    }

    public function removeMember(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id'         => 'required',
            'group_id'   => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $user = User::findorfail($request->user_id);
        $member = GroupMember::where('user_id',$request->user_id)->where('group_id',$request->group_id)->first();
        if($member){
            $member->status = 0;
            $member->updated_at = Carbon::now();
            $member->save();
            $title = Auth::user()->username.' removed you from'.$member->group->name;
            $body = Auth::user()->username.' removed you from'.$member->group->name;
            SendNotification($member->user->device_id, $title, $body);
            $notification = new Notification();
            $notification->sender_id                =   Auth::user()->id;
            $notification->reciever_id              =   $member->user_id;
            $notification->title                    =   $title;
            $notification->body                     =   $body;
            $notification->type                     =   'message';
            $notification->content_id               =   $member->id;
            $notification->save();
            return apiresponse(true, "Removed from group", $user->username);
        }else{
            return apiresponse(false, "Something went wrong");
        }
    }

    public function leaveGroup(Request $request){
        $validator = Validator::make($request->all(), [
            'group_id'   => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        Auth::user()->id;
        $group = Group::find($request->group_id);
        if($group != null){
            $groupMemberAdmin = GroupMember::where('group_id',$group->id)->where('user_id',Auth::user()->id)->where('is_admin',1)->where('status',1)->first();
            if($groupMemberAdmin){
                $groupMemberAdmin->is_admin = 0;
                $groupMemberAdmin->status = 0;
                if($groupMemberAdmin->save()){
                    $groupMember = GroupMember::where('group_id',$group->id)->where('is_admin',1)->where('status',1)->first();
                    if(!$groupMember){
                        $groupMember = GroupMember::where('group_id',$group->id)->where('status',1)->first();
                        $groupMember->is_admin = 1;
                        if($groupMember->save()){
                            $title = Auth::user()->username.' makes you group admin of '.$groupMember->group->name;
                            $body = Auth::user()->username.' makes you group admin of '.$groupMember->group->name;
                            SendNotification($groupMember->user->device_id, $title, $body);
                            $notification = new Notification();
                            $notification->sender_id                =   Auth::user()->id;
                            $notification->reciever_id              =   $groupMember->user_id;
                            $notification->title                    =   $title;
                            $notification->body                     =   $body;
                            $notification->type                     =   'message';
                            $notification->content_id               =   $groupMember->id;
                            $notification->save();
                            return apiresponse(true, "Group leaved");
                        }
                    }else{
                        return apiresponse(true, "Group leaved");
                    }
                }
                
            }
        }else{
            return apiresponse(false, "Group not found");
        }
    }

    public function deleteGroup(Request $request){
        $validator = Validator::make($request->all(), [
            'group_id'   => 'required',
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all())); 
        $group = Group::find($request->group_id);
        if(!$group->members){
            foreach($group->documents as $document){
                $filename = public_path('images/'.$document->name);
                File::delete($filename);
                $document->delete();
            }
            $group->delete();
            return apiresponse(true, "Group deleted");
        }else{
            $groupMember = GroupMember::where('group_id',$request->group_id)->where('user_id',Auth::user()->id)
            ->first();
            if($groupMember){
                $groupMember->delete();
                return apiresponse(true, "Group deleted");
            }
            
        }
    }
}
