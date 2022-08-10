<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
            'package_name' => $this->package_name,
            'plan_id' => $this->plan_id,
            'price' => $this->price,
            'description' => $this->description,
            'features' => explode('*|*|*',$this->features),
            'is_subscribed' => $this->is_subscribed,
            'created_at' => $this->created_at,
        ];
    }
}
