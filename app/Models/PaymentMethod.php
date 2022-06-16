<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'card_brand', 'stripe_source_id', 'card_end_number', 'default_card'];


    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
