<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFriend extends Model
{
    use HasFactory;


    protected $fillable = ['user_id', 'requested_user_id', 'is_followed', 'followed_back', 'status', 'accepted_date'];



    public function requestedUser(){
        return $this->belongsTo(User::class, 'requested_user_id', 'id');
    }

    public function recieverUser(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
