<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Post,PostDocumentComment};

class PostDocument extends Model
{
    use HasFactory;
    
    public function post()
	{
        return $this->belongsTo(Post::class);
	}

    public function documentComments()
	{
        return $this->hasMany(PostDocumentComment::class);
	}

    public function groupSharedDocument()
	{
        return $this->hasOne(GroupSharedDocument::class);
	}
}
