<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pub extends Model
{
    public function reviews(){
        return $this->hasMany(Review::class);
    }
}
