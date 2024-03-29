<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Laravel\Cashier\Billable;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\GroupMember;
use App\Models\Post;
use App\Models\UserFriend;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'device_id',
        'stripe_customer_id',
        'phone_number',
        'profile_pic',
        'dob',
        'address',
        'country',
        'city',
        'state',
        'zip',
        'lat',
        'lng',
        'is_notify',
        'status',
        'confirmation_code'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function groups()
	{
        return $this->hasMany(Group::class);
	}

    public function groupMessages()
	{
        return $this->hasMany(GroupMessage::class);
	}

    public function groupMembers()
	{
        return $this->hasMany(GroupMember::class);
	}

    public function posts()
	{
        return $this->hasMany(Post::class);
	}

    public function friends()
	{
        return $this->hasMany(UserFriend::class,'requested_user_id', 'id');
	}
}
