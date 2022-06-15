<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PostDocument;

class Post extends Model
{
    use HasFactory;

    public function documents()
	{
        return $this->hasMany(PostDocument::class);
	}
}
