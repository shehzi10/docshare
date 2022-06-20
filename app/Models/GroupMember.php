<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Group;
use App\Models\User;

class GroupMember extends Model
{
    use HasFactory;
    protected $fillable = ['group_id','user_id','is_admin','status'];

    public function group()
	{
        return $this->belongsTo(Group::class);
	}

    public function user()
	{
        return $this->belongsTo(User::class);
	}
}
