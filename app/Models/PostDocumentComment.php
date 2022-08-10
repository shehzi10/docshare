<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostDocumentComment extends Model
{
    use HasFactory;

    public function postDocument()
	{
        return $this->belongsTo(PostDocument::class);
	}

    public function user()
	{
        return $this->belongsTo(User::class);
	}

    public function message()
	{
        return $this->hasOne(Message::class);
	}
}
