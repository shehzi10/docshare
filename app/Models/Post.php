<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PostDocument;
use App\Models\Taggedfriend;

class Post extends Model
{
    use HasFactory;

    public function documents()
	{
        return $this->hasMany(PostDocument::class);
	}

    public function taggedFriends()
	{
        return $this->hasMany(Taggedfriend::class);
	}
}
