<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
    public function user(){
        return $this->belongsTo(User::class);

    }
    public function orderItems(){
        return $this->hasMany(orderItem::class);
    }
    public function transaction(){
        return $this->hasOne(transaction::class);
    }
}
