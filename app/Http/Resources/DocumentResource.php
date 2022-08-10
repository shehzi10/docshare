<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Controllers\Controller;
use App\Models\ForumUserPosts;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\UserPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic;
use App\Photo;

class DocumentResource extends JsonResource
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
        $extension = explode(".", $this->name);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'extension' => $extension[count($extension)-1],
            'url' => url('public/images/'.$this->name),
            'is_protected' => $this->is_protected,
        ];
    }
}
