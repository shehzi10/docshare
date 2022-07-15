<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Message;

class MessageLocation extends Model
{
    use HasFactory;

    public function groupMessage()
	{
        return $this->belongsTo(Message::class);
	}
}
