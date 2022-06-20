<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GroupMessage;

class GroupDocument extends Model
{
    use HasFactory;

    public function groupMessage()
	{
        return $this->belongsTo(GroupMessage::class);
	}
}
