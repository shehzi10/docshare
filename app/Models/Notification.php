<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'reciever_id', 'type', 'content_id', 'title', 'body','is_read'];


    public function from(){
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
    public function group(){
        return $this->belongsTo(Group::class, 'sender_id', 'id');
    }

    public function to(){
        return $this->belongsTo(User::class, 'reciever_id', 'id');
    }
}
