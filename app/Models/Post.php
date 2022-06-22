<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PostDocument;
use App\Models\Taggedfriend;
use App\Models\User;

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

    public function user()
	{
        return $this->belongsTo(User::class);
	}
}
