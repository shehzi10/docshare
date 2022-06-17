<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupMember;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupMemberResource;
use Auth;
use Carbon\Carbon;
use File;
use Illuminate\Support\Facades\Validator;



class GroupController extends Controller
{
    public function index(){
        $groups = GroupMember::where('user_id', Auth::user()->id)->with(['group'])->get();
        $data = GroupResource::collection($groups);  
        return apiresponse(true, 'Groups found',  $data);  
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name'         => 'required',
            'description'   => 'required',
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
            return apiresponse(true, "Removed from group", $user->username);
        }
    }
}
