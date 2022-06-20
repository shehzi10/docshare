<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use App\Models\User;

class Group extends Model
{
    use HasFactory;

    public function members()
	{
        return $this->hasMany(GroupMember::class);
	}
    public function messages()
	{
        return $this->hasMany(GroupMessage::class);
	}
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
