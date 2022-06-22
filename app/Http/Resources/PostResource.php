<?php

namespace App\Http\Resources;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\TaggFriendsResource;



use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
        
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'user' => $this->user,
            'documents' => DocumentResource::collection($this->documents),
            'tagged_friends' => TaggFriendsResource::collection($this->taggedFriends),
        ];
    }
}
