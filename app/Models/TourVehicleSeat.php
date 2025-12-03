<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourVehicleSeat extends Model
{
    protected $fillable = [
        "tourVehicleId",
        "seatNumber",
        "status"
    ];

    public function tourVehicle()
    {
        return $this->belongsTo(TourVehicle::class, 'tourVehicleId');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'tourVehicleSeatId');
    }
}
