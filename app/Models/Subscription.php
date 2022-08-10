<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = ['package_name', 'plan_id', 'price', 'description'];

    public function friend()
	{
        return $this->hasMany(UserSubscription::class,'user_id', 'id');
	}
}
