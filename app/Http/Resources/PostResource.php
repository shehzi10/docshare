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
        $documents = 0;
        $images = 0;
        foreach ($this->documents as $key => $value) {
            if($value->type == 'image'){
                $images = $images+1;
            }
            if($value->type == 'document'){
                $documents = $documents+1;
            }
            
        }
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'user' => $this->user,
            'documents' => DocumentResource::collection($this->documents),
            'total_images' => $images,
            'total_documents' => $images,
            'tagged_friends' => TaggFriendsResource::collection($this->taggedFriends),
        ];
    }
}
