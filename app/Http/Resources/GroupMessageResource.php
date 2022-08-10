<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\SharedDocumentResource;


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
        if($this->groupSharedDocument){
            $res = $this->groupSharedDocument->postDocument;
        }else{
            $res = null;
        }
        return [
            'id' => $this->id,
            'message' => $this->message,
            'type' => $this->type, 
            'messageFrom' => $this->user,
            'documents' => $this->groupDocuments, 
            'shared_document' => $res, 
            'location' => $this->grouplocation,
            'group' =>  $this->group,
            'created_at' => $this->created_at, 
        ];
    }
}
