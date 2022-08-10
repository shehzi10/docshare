<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupSharedDocument extends Model
{
    use HasFactory;

    public function groupMessage()
	{
        return $this->belongsTo(GroupMessage::class);
	}

    public function postDocument()
	{
        return $this->belongsTo(PostDocument::class);
	}


}
