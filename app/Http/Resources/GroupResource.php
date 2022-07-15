<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\GroupMemberResource;
use App\Models\GroupMember;
use Auth;
use App\Models\GroupMessage;


class GroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request); 
        $last_message = GroupMessage::where('group_id',$this->group->id)->orderBy('created_at','desc')->first();
        $member = GroupMember::where('user_id',Auth::user()->id)->where('group_id',$this->group->id)->where('status',1)->first();
        if($member){
            $count = GroupMessage::where('group_id',$this->group->id)->where('created_at','>',$member->updated_at)->count();
        }
        return [
            'id' => $this->group->id,
            'name' => $this->group->name,
            'description' => $this->group->description,
            'image' => $this->group->image,
            'is_admin' => $this->is_admin,
            'status' => $this->status,
            'is_group' => 1,
            'created_at' => isset($last_message->created_at)?$last_message->created_at:$this->group->created_at,
            'message_count' => $count,
            'last_message' => $last_message,
            'members' => GroupMemberResource::collection($this->group->members),
        ];
    }
}
