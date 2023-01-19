<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chatlist extends Model
{
    use HasFactory;

    protected $table = "chatlists";

    protected $fillable = ['from_user_type', 'from_user_id', 'to_user_type', 'to_user_id', 'updated_at'];

    protected $appends = [
        'talking_to',
        'message_count',
        'last_message'
    ];

    public function from_user()
    {
        return $this->morphTo();
    }

    public function to_user()
    {
        return $this->morphTo();
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'chatlist_id', 'id')->orderBy('created_at', 'DESC');
    }

    public function getMessageCountAttribute()
    {
        return Message::where(['chatlist_id' => $this->id])->where("read",'=','0')->count();
    }

    public function getLastMessageAttribute()
     {
         $last_message = ["message"=>"No Message","time"=>""];
         $message = Message::where(['chatlist_id' => $this->id])->orderBy('created_at', 'DESC')->first();
         if ($message) {
             $last_message =["message"=>$message->message,"time"=>$message->created_at] ;
         }
         return $last_message;
     }


    public function getTalkingToAttribute()
    {
        if (request()->user()) {
            $myself = request()->user();
            if ($myself->getTable() == "realtor" and $this->from_user_type == "App\Models\User") {
                return $this->to_user();
            } else if ($myself->getTable() == "realtor" and $this->to_user_type == "App\Models\User") {
                return $this->from_user();
            } else if ($myself->getTable() == "realtor" and $this->from_user_type == "App\Models\User") {
                return $this->to_user();
            } else if ($myself->getTable() == "realtor" and $this->to_user_type == "App\Models\User") {
                return $this->from_user();
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
