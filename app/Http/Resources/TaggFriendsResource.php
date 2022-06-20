<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;


class TaggFriendsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request->user);
        return [
            'id' => $this->user->id,
            'username' => $this->user->username,
            'email' => $this->user->email,
            'device_id' => $this->user->device_id,
            'phone_number' => $this->user->phone_number,
            'profile_pic' => $this->user->profile_pic,
        ];
    }
}
