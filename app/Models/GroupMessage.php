<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Group;
use App\Models\User;
use App\Models\GroupDocument;

class GroupMessage extends Model
{
    use HasFactory;

    public function group()
	{
        return $this->belongsTo(Group::class);
	}

    public function user()
	{
        return $this->belongsTo(User::class);
	}

    public function groupDocuments()
	{
        return $this->hasMany(GroupDocument::class);
	}
}
