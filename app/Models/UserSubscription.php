<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'plan_id', 'price', 'payment_method_id'];

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'plan_id', 'id');
    }
}
