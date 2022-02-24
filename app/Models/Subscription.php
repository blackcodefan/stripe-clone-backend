<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $guarded = [];
    // protected $with = ['price'];

    public function price()
    {
        return $this->hasOne(Price::class, 'stripe_id', 'stripe_price');
    }
}
