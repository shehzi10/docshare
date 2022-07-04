<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;


class GroupMessageResource extends JsonResource
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
            'message' => $this->message,
            'type' => $this->type, 
            'messageFrom' => $this->user,
            'documents' => $this->groupDocuments, 
            'location' => $this->grouplocation,
            'group' =>  $this->group,
            'created_at' => $this->created_at, 
        ];
    }
}
