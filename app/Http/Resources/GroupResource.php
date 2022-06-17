<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id' => $this->group->id,
            'name' => $this->group->name,
            'description' => $this->group->description,
            'image' => $this->group->image,
            'is_admin' => $this->is_admin,
            'status' => $this->status,
        ];
    }
}
