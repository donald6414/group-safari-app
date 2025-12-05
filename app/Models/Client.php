<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        "userId",
        "name",
        "email",
        "phone",
        "gender",
        "dateOfBirth",
        "age",
        "nationality",
        "language",
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'clientId', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }
}
