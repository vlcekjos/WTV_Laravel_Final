<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pub extends Model
{
    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude',
        'street',
        'city',
        'postcode',
        'image',
        'website',
        'phone',
        'overpass_node_id',
    ];

    public function reviews(){
        return $this->hasMany(Review::class);
    }
}
