<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourVehicle extends Model
{
    protected $fillable = [
        "tourId",
        "vehicleType",
        "numberOfVehicle",
        "numberOfSeat",
        "status"
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tourId');
    }

    public function tourVehicleSeats()
    {
        return $this->hasMany(TourVehicleSeat::class, 'tourVehicleId');
    }
}
