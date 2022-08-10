<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MessageLocation;


class Message extends Model
{
    use HasFactory;
    protected $table = "messages";
    protected $fillable = [
        'chatlist_id', 'sent_from_type', 'sent_from_id', 'sent_to_type', 'sent_to_id', 'type', 'message','media', 'read', 'image', 'audio','post_document_id'
    ];

    protected $appends = [
        'sent_from',
        'sent_to',
    ];

    public function chatlist()
    {
        return $this->belongsTo(Chatlist::class, 'chatlist_id', 'id');
    }

    public function sharedDocument()
    {
        return $this->belongsTo(PostDocument::class, 'post_document_id', 'id');
    }

    public function getSentFromAttribute()
    {
        return User::where(['id'=>$this->sent_from_id])->first();
    }

    public function getSentToAttribute()
    {
        return User::where(['id'=>$this->sent_to_id])->first();
    }

    public function location()
	{
        return $this->hasOne(MessageLocation::class);
	}
}
