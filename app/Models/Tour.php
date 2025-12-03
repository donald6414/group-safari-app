<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    protected $fillable = [
        "userId",
        "title",
        "numberOfVehicle",
        "numberOfSeat",
        "noOfDays",
        "startDate",
        "endDate",
        "status",
        "language"
    ];

    public function tourVehicles()
    {
        return $this->hasMany(TourVehicle::class, 'tourId');
    }

    public function tourVehicleSeats()
    {
        return $this->hasMany(TourVehicleSeat::class, 'tourVehicleId');
    }
}
