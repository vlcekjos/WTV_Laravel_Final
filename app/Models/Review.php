<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'pub_id',
        'rating',
        'comment',
    ];

    public function pub()
    {
        return $this->belongsTo(Pub::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
